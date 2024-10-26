<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\KeranjangDetail;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Xendit\VirtualAccounts;
use Xendit\Xendit;

class TransaksiApiController extends Controller
{
    protected $keranjang, $keranjangDetail, $produk, $transaksi, $stokProduk, $transaksiDetail, $serverKey;

    public function __construct()
    {
        $this->serverKey = config("xendit.xendit_key");
        $this->keranjang = new Keranjang();
        $this->keranjangDetail = new KeranjangDetail();
        $this->produk = new Produk();
        $this->transaksi = new Transaksi();
        $this->transaksiDetail = new TransaksiDetail();
        $this->stokProduk = new StokProduk();
        Xendit::setApiKey(env('XENDIT_SECRET_KEY'));
    }

    public function checkout(Request $request)
    {
        try {

            DB::beginTransaction();

            Xendit::setApiKey($this->serverKey);

            $keranjang = $this->keranjang->where("userId", Auth::user()->id)
                ->where("status", 1)
                ->first();

            $keranjangDetail = $this->keranjangDetail->where("keranjangId", $keranjang["id"])
                ->get();

            $transaksi = $this->transaksi->create([
                "invoiceId" => "TRX-" . date("YmdHis"),
                "namaUser" => $request["nama-customer"],
                "totalHarga" => 0,
                "usernameKasir" => Auth::user()->username,
                "mitraId" => Auth::user()->karyawan->mitra->id,
                "namaMitra" => Auth::user()->karyawan->mitra->namaMitra,
                "tanggalOrder" => date("Y-m-d"),
                "kasirId" => Auth::user()->id,
                "status" => 1 // PENDING
            ]);

            foreach ($keranjangDetail as $item) {
                $this->transaksiDetail->create([
                    "transaksiId" => $transaksi["id"],
                    "idProduk" => $item["produk"]["id"],
                    "namaProduk" => $item["produk"]["namaProduk"],
                    "hargaProduk" => $item["produk"]["hargaProduk"],
                    "qtyProduk" => $item["qty"]
                ]);

                $item->delete();
            }

            $this->transaksi->where("id", $transaksi["id"])->update([
                "totalHarga" => $keranjang["totalHarga"]
            ]);

            $amount = $keranjang["totalHarga"];

            $keranjang->delete();


            $createVa = VirtualAccounts::create([
                'external_id' => $transaksi['invoiceId'],
                'bank_code' => $request['code_bank'],
                'name' => $request['nama_customer'],
                'expected_amount' => $amount,
                'is_closed' => false,
                'is_single_use' => true,
                'expiration_date' => Carbon::now()->addHours(24)->toIso8601String()
            ]);

            $this->transaksi->where("id", $transaksi["id"])->update([
                "statusOrder" => "UNPAID",
                "xenditId" => $createVa["id"]
            ]);

            DB::commit();

            return response()->json([
                "stattus" => true,
                "message" => "Gagal Membuat virtual account",
                "data" => $createVa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "stattus" => true,
                "message" => "Gagal Membuat virtual account",
                "data" => $e->getMessage()
            ]);
        }
    }

    public function handleVirtualAccountCallback(Request $request)
    { {
            try {
                DB::beginTransaction();

                $reqHeaders = $request->header('x-callback-token');
                $xIncomingCallbackTokenHeader = isset($reqHeaders) ? $reqHeaders : "";

                if ($xIncomingCallbackTokenHeader) {
                    if ($request->status == 'PAID' || $request->status == 'SETTLED') {
                        $transaksi = $this->transaksi->where("xenditId", $request->id)->first();

                        $transaksi["tipeTransaksi"] = "TRANSFER";
                        $transaksi["statusOrder"] = $request->status;
                        $transaksi["tanggalBayar"] = date("Y-m-d H:i:s");
                        $transaksi["paymentChannel"] = $request->payment_channel;
                        $transaksi->update();
                    }
                }

                DB::commit();

                return response()->json([
                    "status" => true,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    "status" => false
                ]);
            }
        }
    }
    public function paymentCallbackFailure(Request $request)
    {
        try {
            // Validasi callback token
            $callbackToken = $request->header('x-callback-token');
            if (!$callbackToken) {
                Log::error('Xendit Failure Callback: Missing callback token');
                return response()->json([
                    'status' => false,
                    'message' => 'Missing callback token'
                ], 403);
            }

            DB::beginTransaction();

            $transaksi = $this->transaksi->where("xenditId", $request->id)->first();

            if (!$transaksi) {
                Log::error('Xendit Failure Callback: Transaksi not found', ['xenditId' => $request->id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            // Set status berdasarkan failure reason
            $failureStatus = 'FAILED';
            $failureMessage = 'Pembayaran gagal';

            switch ($request->failure_reason ?? '') {
                case 'EXPIRED':
                    $failureStatus = 'EXPIRED';
                    $failureMessage = 'Pembayaran expired';
                    break;
                case 'DECLINED':
                    $failureStatus = 'DECLINED';
                    $failureMessage = 'Pembayaran ditolak oleh bank';
                    break;
                case 'INSUFFICIENT_BALANCE':
                    $failureStatus = 'FAILED';
                    $failureMessage = 'Saldo tidak mencukupi';
                    break;
                case 'INVALID_ACCOUNT':
                    $failureStatus = 'FAILED';
                    $failureMessage = 'Akun bank tidak valid';
                    break;
                default:
                    $failureStatus = 'FAILED';
                    $failureMessage = 'Pembayaran gagal: ' . ($request->failure_reason ?? 'Alasan tidak diketahui');
            }

            // Update transaksi
            $transaksi->update([
                "statusOrder" => $failureStatus,
                "keterangan" => $failureMessage
            ]);

            Log::info('Payment Failed', [
                'transaction_id' => $transaksi->id,
                'xendit_id' => $request->id,
                'status' => $failureStatus,
                'reason' => $request->failure_reason ?? 'Unknown',
                'amount' => $request->amount ?? 0
            ]);

            DB::commit();

            return response()->json([
                "status" => false,
                "message" => $failureMessage,
                "data" => [
                    "transaction_id" => $transaksi->id,
                    "status" => $failureStatus,
                    "failure_reason" => $request->failure_reason ?? 'Unknown',
                    "payment_channel" => $request->payment_channel ?? null
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Failure Callback Error: ' . $e->getMessage());

            return response()->json([
                "status" => false,
                "message" => "Gagal memproses notifikasi kegagalan pembayaran",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Endpoint untuk cek status pembayaran
    public function checkPaymentStatus($invoiceId)
    {
        try {

            $payment = VirtualAccounts::retrieve($invoiceId);

            return response()->json([
                'status' => true,
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengecek status pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
