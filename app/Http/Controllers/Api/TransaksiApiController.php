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

            if (!$keranjang) {
                return response()->json([
                    "status" => false,
                    "message" => "Keranjang tidak ditemukan",
                ]);
            }

            $keranjangDetail = $this->keranjangDetail->where("keranjangId", $keranjang["id"])
                ->get();


            $transaksi = $this->transaksi->create([
                "invoiceId" => "TRX-" . date("YmdHis"),
                "namaUser" => $request["nama_customer"],
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


            if ($request['metode_pembayaran'] == 'TRANSFER') {
                $createVa = VirtualAccounts::create([
                    'external_id' => $transaksi['invoiceId'],
                    'bank_code' => $request['code_bank'],
                    'name' => $request['nama_customer'],
                    'expected_amount' => $amount,
                    'is_closed' => true,
                    'is_single_use' => true,
                    'expiration_date' => Carbon::now()->addHours(24)->toIso8601String()
                ]);


                $this->transaksi->where("id", $transaksi["id"])->update([
                    "xenditId" => $createVa["id"]
                ]);

                DB::commit();

                return response()->json([
                    "stattus" => true,
                    "message" => "Berhasil Membuat virtual account",
                    "data" => $createVa
                ]);
            } else {
                $this->transaksi->where("id", $transaksi["id"])->update([
                    "statusOrder" => "PAID",
                ]);
                DB::commit();

                return response()->json([
                    "stattus" => true,
                    "message" => "Berhasil Membuat virtual account",
                    "data" => $this->transaksi
                ]);
            }
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
    {
        try {
            // Log semua informasi request dari Xendit
            Log::info('Xendit Callback Request Details', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            // Validate callback token
            $callbackToken = $request->header('x-callback-token');
            if (!$callbackToken || $callbackToken !== config('xendit.callback_token')) {
                Log::warning('Invalid VA callback token received', [
                    'token' => $callbackToken,
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid callback token'
                ], 401);
            }

            // Validate amount
            if (!$request->has('amount') || !is_numeric($request->amount)) {
                Log::warning('Invalid amount in VA callback', [
                    'amount' => $request->amount ?? null
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }

            DB::beginTransaction();

            // Log incoming VA payment
            Log::info('Virtual Account Payment Received', [
                'amount' => $request->amount,
                'payment_id' => $request->payment_id ?? null,
                'external_id' => $request->external_id
            ]);

            // Get transaction
            $transaksi = $this->transaksi->where('invoiceId', $request->external_id)
                ->where('totalHarga', $request->amount)
                ->first();

            if (!$transaksi) {
                DB::rollBack();
                Log::error('Transaction not found or amount mismatch', [
                    'received_amount' => $request->amount,
                    'external_id' => $request->external_id
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found or amount mismatch'
                ], 404);
            }

            // Prevent double payment
            if ($transaksi->statusOrder === 'PAID') {
                DB::rollBack();
                Log::warning('Duplicate payment callback received', [
                    'transaction_id' => $transaksi->id,
                    'external_id' => $request->external_id
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Transaction already paid',
                    'data' => $transaksi
                ]);
            }

            // Update transaction
            $transaksi->statusOrder = 'PAID';
            $transaksi->tanggalBayar = now();
            $transaksi->tipeTransaksi = 'TRANSFER';
            $transaksi->update();

            // Log successful payment
            Log::info('Virtual Account Payment Successful', [
                'transaction_id' => $transaksi->id,
                'amount' => $request->amount,
                'payment_time' => $transaksi->tanggalBayar
            ]);

            DB::commit();

            // Bisa ditambahkan trigger notifikasi ke user
            // $this->sendPaymentNotification($transaksi);

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => [
                    'transaction_id' => $transaksi->id,
                    'external_id' => $request->external_id,
                    'amount' => $request->amount,
                    'status' => 'PAID',
                    'payment_time' => $transaksi->tanggalBayar
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error processing VA payment callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error processing payment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    public function handleVirtualAccountUpdateToUnpaid(Request $request)
    {
        try {
            DB::beginTransaction();

            $reqHeaders = $request->header('x-callback-token');
            $xIncomingCallbackTokenHeader = $reqHeaders ?? "";

            Log::info("status pembayaran: " . $request->status);
            if ($xIncomingCallbackTokenHeader) {
                $transaksi = $this->transaksi->where("xenditId", $request->id)->first();

                // Check if the transaction exists
                if ($transaksi) {
                    Log::info("ambil data dari database: " . json_encode($transaksi));

                    // Update the transaction fields
                    // $transaksi->statusOrder = "UNPAID";
                    $transaksi->paymentChannel = $request->payment_channel;
                    $transaksi->save(); // Use save() to persist changes

                    Log::info("status data sesudah diupdate: " . json_encode($transaksi->statusOrder));
                } else {
                    Log::warning("Transaction not found for xenditId: " . $request->id);
                    return response()->json([
                        "status" => false,
                        "message" => "Transaksi tidak ditemukan",
                    ], 404);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Token tidak Valid",
                ], 401);
            }

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Berhasil mengubah status",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error updating transaction status: " . $e->getMessage());

            return response()->json([
                "status" => false,
                "message" => "Terjadi kesalahan saat mengubah status",
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
