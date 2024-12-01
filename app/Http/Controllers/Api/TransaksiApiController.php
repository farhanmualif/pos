<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\KeranjangDetail;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Xendit\EWallets;
use Xendit\QRCode;
use Xendit\VirtualAccounts;
use Xendit\Xendit;

class TransaksiApiController extends Controller
{
    protected $keranjang, $keranjangDetail, $produk, $transaksi, $stokProduk, $transaksiDetail, $serverKey, $mitra;

    public function __construct()
    {
        $this->serverKey = config("xendit.xendit_key");
        $this->keranjang = new Keranjang();
        $this->keranjangDetail = new KeranjangDetail();
        $this->produk = new Produk();
        $this->transaksi = new Transaksi();
        $this->transaksiDetail = new TransaksiDetail();
        $this->stokProduk = new StokProduk();
        $this->mitra = new Mitra();
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
                    "message" => "Keranjang Kosong",
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
                "tipeTransaksi" => $request['metode_pembayaran'],
                "nomorHpAktif" => $request['nomor_hp_aktif'],
                "paymentChannel" => $request['code_bank'],
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
                if ($request['tipe_pembayaran'] == 'VA') {
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
                        "xenditId" => $createVa["id"],
                    ]);



                    DB::commit();

                    return response()->json([
                        "status" => true,
                        "message" => "Berhasil Membuat virtual account",
                        "data" => $createVa
                    ]);
                } else if ($request['tipe_pembayaran'] == 'EWALLET') {
                    // Proses e-wallet
                    $ewalletType = strtoupper($request['code_bank']);

                    $channelProperties = [
                        "success_redirect_url" => "https://redirect.me/payment"
                    ];
                    $ewalletTypeMap = [
                        'OVO' => 'ID_OVO',
                        'DANA' => 'ID_DANA',
                        'LINKAJA' => 'ID_LINKAJA',
                        'SHOPEEPAY' => 'ID_SHOPEEPAY'
                    ];

                    if (!isset($ewalletTypeMap[$ewalletType])) {
                        return response()->json([
                            "status" => false,
                            "message" => "Tipe e-wallet tidak didukung",
                        ], 422);
                    }

                    switch ($ewalletType) {
                        case 'OVO':
                            if (!$request->has('phone_number')) {
                                return response()->json([
                                    "status" => false,
                                    "message" => "phone_number diperlukan untuk pembayaran OVO",
                                ], 422);
                            }
                            $channelProperties = [
                                'mobile_number' => $request->phone_number,
                            ];
                            break;

                        case 'DANA':
                            if (!$request->has('success_redirect_url')) {
                                return response()->json([
                                    "status" => false,
                                    "message" => "success_redirect_url diperlukan untuk pembayaran OVO",
                                ], 422);
                            }
                            $channelProperties = [
                                'success_redirect_url' => $request->success_redirect_url,
                            ];
                            break;
                        case 'LINKAJA':
                        case 'SHOPEEPAY':
                            if (!$request->has('success_redirect_url')) {
                                return response()->json([
                                    "status" => false,
                                    "message" => "success_redirect_url diperlukan untuk pembayaran " . $ewalletType,
                                ], 422);
                            }
                            $channelProperties = [
                                'success_redirect_url' => $request->success_redirect_url,
                                'failure_redirect_url' => $request->failure_redirect_url ?? $request->success_redirect_url,
                            ];
                            break;
                        default:
                            return response()->json([
                                "status" => false,
                                "message" => "code_back Tidak Valid ",
                            ], 422);
                    }

                    $params = [
                        'reference_id' => $transaksi['invoiceId'],
                        'currency' => 'IDR',
                        'amount' => $amount,
                        'checkout_method' => 'ONE_TIME_PAYMENT',
                        'channel_code' => $ewalletTypeMap[$ewalletType],
                        'channel_properties' => $channelProperties,
                        'metadata' => [
                            "branch_area" => "PLUIT",
                            "branch_city" => "JAKARTA"
                        ]
                    ];

                    // Tambahkan customer info jika tersedia
                    if ($request->has('nama_customer')) {
                        $params['customer'] = [
                            'given_names' => $request['nama_customer'],
                            'email' => Auth::user()->email,
                            'mobile_number' => $request->phone_number ?? '',
                        ];
                    }

                    try {
                        // Tambahkan ewallet_type ke params sesuai dengan mapping
                        $createEwallet = EWallets::createEWalletCharge($params);

                        // Update transaksi dengan ID dari Xendit
                        $this->transaksi->where("id", $transaksi["id"])->update([
                            "xenditId" => $createEwallet["id"],
                            "paymentChannel" => "EWALLET_" . $ewalletType
                        ]);

                        DB::commit();

                        // Menyiapkan response
                        $response = [
                            'status' => true,
                            'message' => 'Berhasil membuat pembayaran e-wallet',
                            'data' => [
                                'transaction_id' => $transaksi['id'],
                                'invoice_id' => $transaksi['invoiceId'],
                                'payment_status' => $createEwallet['status'] ?? 'PENDING',
                                'amount' => $amount,
                                'payment_method' => "EWALLET_$ewalletType",
                                'payment_details' => $createEwallet
                            ]
                        ];

                        return response()->json($response);
                    } catch (ClientException $e) {
                        DB::rollBack();
                        Log::error('Xendit E-Wallet Error: ' . $e->getMessage());
                        $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

                        return response()->json([
                            "status" => false,
                            "message" => "Gagal membuat pembayaran e-wallet",
                            "error" => $errorResponse
                        ], 500);
                    }
                } elseif ($request['tipe_pembayaran'] == 'QRIS') {
                    try {
                        $response = Http::withBasicAuth($this->serverKey, '')
                            ->withHeaders([
                                'api-version' => '2022-07-31',
                                'Content-Type' => 'application/json'
                            ])->withOptions([
                                'verify' => false  // Menonaktifkan verifikasi SSL
                            ])
                            ->post('https://api.xendit.co/qr_codes', [
                                'reference_id' => $transaksi['invoiceId'],
                                'type' => 'DYNAMIC',
                                'currency' => 'IDR',
                                'amount' => $amount,
                                'expires_at' => Carbon::now()->addHours(24)->toIso8601String()
                            ]);

                        if ($response->successful()) {
                            $qrisData = $response->json();

                            // Update data transaksi
                            $this->transaksi->where("id", $transaksi["id"])->update([
                                "xenditId" => $qrisData["id"],
                                "paymentChannel" => "QRIS"
                            ]);

                            DB::commit();

                            return response()->json([
                                "status" => true,
                                "message" => "Berhasil membuat QRIS",
                                "data" => $qrisData
                            ]);
                        }

                        // Jika response tidak successful
                        DB::rollBack();
                        return response()->json([
                            "status" => false,
                            "message" => "Gagal membuat QRIS",
                            "error" => $response->json()
                        ], $response->status());
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Xendit QRIS Error: ' . $e->getMessage());

                        return response()->json([
                            "status" => false,
                            "message" => "Gagal membuat pembayaran QRIS",
                            "error" => $e->getMessage()
                        ], 500);
                    }
                } else {
                    DB::rollBack();
                    Log::error('Gagal membuat pembayaran e-wallet, Tipe Pembayaran tidak valid');

                    return response()->json([
                        "status" => false,
                        "message" => "Gagal membuat pembayaran e-wallet",
                        "error" => "Tipe Pembayaran Tidak Valid"
                    ], 500);
                }
            } else {
                $this->transaksi->where("id", $transaksi["id"])->update([
                    "statusOrder" => "PAID",
                    "tipeTransaksi" => 'CASH',
                    "tanggalBayar" => now(),

                ]);

                DB::commit();
                return response()->json([
                    "status" => true,
                    "message" => "Berhasil Melakukan Pembayaran",
                    "data" => $this->transaksi->find($transaksi['id'])
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => true,
                "message" => "Gagal Membuat virtual account",
                "data" => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ]);
        }
    }

    public function handleQRCodeCallback(Request $request)
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
            if (!$request->has('data.amount') || !is_numeric($request->data['amount'])) {
                Log::warning('Invalid amount in VA callback', [
                    'amount' => $request->data['amount'] ?? null
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }

            DB::beginTransaction();

            // Log incoming VA payment
            Log::info('Virtual Account Payment Received', [
                'amount' => $request->data['amount'],
                'payment_id' => $request->payment_id ?? null,
                'reference_id' => $request->reference_id,
                'status' => $request->status
            ]);

            // Get transaction
            $transaksi = $this->transaksi->where('invoiceId', $request->data['reference_id'])
                ->where('totalHarga', $request->data['amount'])
                ->first();

            if (!$transaksi) {
                DB::rollBack();
                Log::error('Transaction not found or amount mismatch', [
                    'received_amount' => $request->data['amount'],
                    'external_id' => $request->data['reference_id']
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
                    'reference_id' => $request->reference_id
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
            Log::info('QR CODE Payment Successful', [
                'transaction_id' => $transaksi->id,
                'amount' => $request->amount,
                'payment_time' => $transaksi->tanggalBayar
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => [
                    'transaction_id' => $transaksi->id,
                    'reference_id' => $request->reference_id,
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
    public function handleVirtualAccountCallback(Request $request)
    {
        try {
            Log::info('VA Callback received:', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

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

            DB::beginTransaction();

            // Get transaction
            $transaksi = $this->transaksi->where('invoiceId', $request->external_id)
                ->where('totalHarga', $request->amount)
                ->first();

            if (!$transaksi) {
                Log::error('Transaction not found:', [
                    'external_id' => $request->external_id,
                    'amount' => $request->amount
                ]);
                throw new \Exception('Transaction not found or amount mismatch');
            }

            Log::info('Found transaction:', [
                'transaksiId' => $transaksi->id,
                'invoiceId' => $transaksi->invoiceId
            ]);

            // Prevent double payment
            if ($transaksi->statusOrder === 'PAID') {
                Log::warning('Duplicate payment detected:', [
                    'transaksiId' => $transaksi->id,
                    'invoiceId' => $transaksi->invoiceId
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Transaction already paid'
                ]);
            }

            // Get transaction details and update stock
            $transaksiDetails = $this->transaksiDetail->where('transaksiId', $transaksi->id)->get();

            Log::info('Processing transaction details:', [
                'count' => $transaksiDetails->count(),
                'details' => $transaksiDetails->toArray()
            ]);

            foreach ($transaksiDetails as $detail) {
                $produk = Produk::with('stokProduk')->find($detail->idProduk);
                if ($produk && $produk->stokProduk->isNotEmpty()) {
                    $stokProduk = $produk->stokProduk->first();
                    $oldStock = is_numeric($stokProduk->qty) ? (int)$stokProduk->qty : 0;
                    $qtyToReduce = is_numeric($detail->qtyProduk) ? (int)$detail->qtyProduk : 0;

                    Log::info('Stock values before calculation:', [
                        'productId' => $produk->id,
                        'stokProduk_id' => $stokProduk->id,
                        'current_qty' => $oldStock,
                        'qty_to_reduce' => $qtyToReduce
                    ]);

                    $newStock = $oldStock - $qtyToReduce;

                    if ($newStock >= 0) {
                        $stokProduk->qty = $newStock;
                        $result = $stokProduk->save();

                        Log::info('Stock update completed:', [
                            'success' => $result,
                            'finalStock' => $stokProduk->fresh()->qty
                        ]);
                    } else {
                        Log::warning('Invalid stock calculation:', [
                            'oldStock' => $oldStock,
                            'qtyToReduce' => $qtyToReduce,
                            'newStock' => $newStock
                        ]);
                    }
                } else {
                    Log::warning('Product or stock not found:', [
                        'productId' => $detail->idProduk
                    ]);
                }
            }

            // Update transaction
            $transaksi->statusOrder = 'PAID';
            $transaksi->tanggalBayar = now();
            $transaksi->tipeTransaksi = 'TRANSFER';
            $transaksi->save();

            Log::info('Transaction updated successfully:', [
                'transaksiId' => $transaksi->id,
                'status' => $transaksi->statusOrder,
                'paymentTime' => $transaksi->tanggalBayar
            ]);

            DB::commit();

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
            Log::error('VA Callback processing failed:', [
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
                    $transaksi->paymentChannel = $request->bank_code;
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

    public function handleEwalletCallback(Request $request)
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
                Log::warning('Invalid callback token received', [
                    'token' => $callbackToken,
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid callback token'
                ], 401);
            }

            // Get callback data
            $callbackData = $request->all();

            // Validate required fields
            if (!isset($callbackData['data'])) {
                Log::warning('Invalid callback data structure', [
                    'payload' => $callbackData
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid callback data structure'
                ], 400);
            }

            $data = $callbackData['data'];

            // Validate amount
            if (!isset($data['charge_amount']) || !is_numeric($data['charge_amount'])) {
                Log::warning('Invalid amount in callback', [
                    'charge_amount' => $data['charge_amount'] ?? null
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Get transaction
                $transaksi = $this->transaksi
                    ->where('xenditId', $data['id'])
                    ->where('totalHarga', $data['charge_amount'])
                    ->lockForUpdate()
                    ->first();

                if (!$transaksi) {
                    throw new \Exception('Transaction not found or amount mismatch');
                }

                // Prevent double payment
                if ($transaksi->statusOrder === 'PAID') {
                    DB::commit();
                    Log::warning('Duplicate payment callback received', [
                        'transaction_id' => $transaksi->id,
                        'xendit_id' => $data['id']
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Transaksi sudah dibayar',
                        'data' => $transaksi
                    ]);
                }

                // Get transaction details manually since there's no relationship
                $detailTransaksi = TransaksiDetail::where('transaksiId', $transaksi->id)
                    ->lockForUpdate()
                    ->get();

                if ($detailTransaksi->isEmpty()) {
                    throw new \Exception("No transaction details found for transaction: {$transaksi->id}");
                }

                // Process each transaction detail
                foreach ($detailTransaksi as $detail) {
                    // Get product with its stock
                    $produk = Produk::where('id', $detail->idProduk)
                        ->lockForUpdate()
                        ->first();

                    if (!$produk) {
                        throw new \Exception("Product not found: {$detail->idProduk}");
                    }

                    // Get product stock manually
                    $stokProduk = $this->stokProduk::where('produkId', $produk->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stokProduk) {
                        throw new \Exception("Stock not found for product: {$produk->id}");
                    }

                    // Validate sufficient stock
                    if ($stokProduk->qty < $detail->qtyProduk) {
                        throw new \Exception(
                            "Insufficient stock for product: {$produk->id}. " .
                                "Required: {$detail->qtyProduk}, Available: {$stokProduk->qty}"
                        );
                    }

                    $newStok = $stokProduk->qty -= $detail->qtyProduk;

                    Log::info("stok baru", [
                        "stok" => $stokProduk->qty -= $detail->qtyProduk
                    ]);

                    // Update stock
                    $stokProduk->qty = $newStok;
                    $stokProduk->save();

                    Log::info('Stock updated', [
                        'produk_id' => $produk->id,
                        'detail_id' => $detail->id,
                        'quantity_reduced' => $detail->quantity,
                        'old_qty' => $stokProduk->qty + $detail->quantity,
                        'new_qty' => $stokProduk->qty
                    ]);
                }

                // Update transaction status
                $transaksi->statusOrder = 'PAID';
                $transaksi->tanggalBayar = now();
                $transaksi->tipeTransaksi = 'TRANSFER';
                $transaksi->save();

                // Log successful payment
                Log::info('E-Wallet Payment Successful', [
                    'transaction_id' => $transaksi->id,
                    'amount' => $data['charge_amount'],
                    'payment_time' => $transaksi->tanggalBayar
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful',
                    'data' => [
                        'transaction_id' => $transaksi->id,
                        'amount' => $data['charge_amount'],
                        'status' => $transaksi->statusOrder,
                        'payment_time' => $transaksi->tanggalBayar
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing E-Wallet payment callback', [
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

    // Endpoint untuk cek status pembayaran
    public function checkPaymentStatus($invoiceId)
    {
        try {
            $ewalletChannel = ['EWALLET_DANA', 'EWALLET_OVO', 'EWALLET_LINKAJA', 'EWALLET_SHOPEEPAY'];
            $VAChannel = ['PERMATA', 'BRI', 'BNI', 'MANDIRI'];

            $transaksi = $this->transaksi
                ->where(function ($query) use ($invoiceId) {
                    $query->where('xenditId', $invoiceId)
                        ->orWhere('invoiceId', $invoiceId);
                })
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            if (empty($transaksi->paymentChannel)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment channel tidak ditemukan',
                ], 400);
            }

            $payment = null;

            // Cek pembayaran E-Wallet
            if (in_array($transaksi->paymentChannel, $ewalletChannel)) {
                $xenditApiKey = config('xendit.xendit_key'); // Sesuaikan dengan konfigurasi Anda
                $baseUrl = 'https://api.xendit.co';

                $client = new \GuzzleHttp\Client([
                    'base_uri' => $baseUrl,
                    'auth' => [$xenditApiKey, ''],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'verify' => false
                ]);

                $response = $client->get("/ewallets/charges/{$invoiceId}");
                $payment = json_decode($response->getBody(), true);
            }
            // Cek pembayaran Virtual Account
            elseif (in_array($transaksi->paymentChannel, $VAChannel)) {
                if (empty($transaksi->xenditId)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Xendit ID tidak ditemukan',
                    ], 400);
                }
                $payment = VirtualAccounts::retrieve($transaksi->xenditId);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Metode pembayaran tidak valid',
                ], 400);
            }

            if (!$payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Status pembayaran tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Status Pembayaran Ditemukan',
                'data' => $payment
            ]);
        } catch (ClientException $e) {
            Log::error('Xendit API Error: ' . $e->getMessage());
            $response = $e->getResponse();
            $errorBody = json_decode($response->getBody(), true);

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengecek status pembayaran',
                'error' => $errorBody['message'] ?? $e->getMessage()
            ], $response->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Payment Status Check Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengecek status pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getPendingTransaction()
    {
        try {
            $ewalletChannel = ['EWALLET_DANA', 'EWALLET_OVO', 'EWALLET_LINKAJA', 'EWALLET_SHOPEEPAY'];
            $VAChannel = ['PERMATA', 'BRI', 'BNI', 'MANDIRI'];

            $user = Auth::user();
            $mitraId = $user->karyawan->mitra->id ?? $user->mitra->id;
            $mitra = $this->mitra->where('id', $mitraId)->first();

            if (!$mitra) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra tidak ditemukan'
                ], 404);
            }

            $payment = $this->transaksi
                ->with('transaksiDetail')
                ->where('mitraId', $mitra->id)
                ->where('usernameKasir', $user->username)
                ->where(column: function ($query) {
                    $query->where('statusOrder', 'UNPAID')
                        ->orWhere('statusOrder', 'PENDING')
                        ->orWhereNull('statusOrder');
                })
                ->first();

            if (isset($payment->xenditId) && in_array($payment->paymentChannel, $VAChannel)) {
                $paymentStatus = VirtualAccounts::retrieve($payment->xenditId);
                $payment->va_payment_status = $paymentStatus;
            } else if (isset($payment->invoiceId) && in_array($payment->paymentChannel, $ewalletChannel)) {
                $paymentStatus = EWallets::getEWalletChargeStatus($payment->xenditId);
                $payment->ewallet_payment_status = $paymentStatus;
            } else if (isset($payment->invoiceId) && $payment->paymentChannel == "QRIS") {
                $paymentStatus =  QRCode::get($payment->xenditId);
                $payment->qris_payment_status = $paymentStatus;
            }

            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi Ditemukan',
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
    public function riwayatTransaksi()
    {
        try {
            $user = Auth::user();

            // Menggunakan nama tabel yang benar dari gambar
            // transaksiId di tabel detail terlihat sebagai 'id' di screenshot kedua
            $transaksi = $this->transaksi
                ->select([
                    'transaksi.*',
                    'transaksi_detail.qtyProduk',  // Mengubah nama tabel
                    'produk.namaProduk',
                    'produk.hargaProduk',
                    'produk.kategori'
                ])
                ->join('transaksi_detail', function ($join) {  // Mengubah nama tabel
                    $join->on('transaksi.id', '=', 'transaksi_detail.transaksiId');
                })
                ->join('produk', 'transaksi_detail.idProduk', '=', 'produk.id')
                ->where('transaksi.usernameKasir', $user->username)
                ->orderBy('transaksi.invoiceId', 'desc')
                ->get();

            // Group the results by invoice for better organization
            $groupedTransaksi = $transaksi->groupBy('invoiceId')->map(function ($group) {
                return [
                    'invoiceId' => $group[0]->invoiceId,
                    'xendId' => $group[0]->xenditId,
                    'totalHarga' => $group[0]->totalHarga,
                    'namaUser' => $group[0]->namaUser,
                    'tanggal' => $group[0]->tanggalBayar,
                    'statusOrder' => $group[0]->statusOrder,
                    'nomorHpAktif' => $group[0]->nomorHpAktif,
                    'paymentChannel' => $group[0]->paymentChannel,
                    'items' => $group->map(function ($item) {
                        return [
                            'namaProduk' => $item->namaProduk,
                            'kategori' => $item->kategori,
                            'hargaProduk' => $item->hargaProduk,
                            'qtyProduk' => $item->qtyProduk,
                            'fotoProduk' => $item->fotoProduk,
                            'subtotal' => $item->hargaProduk * $item->qtyProduk
                        ];
                    })
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi Ditemukan',
                'data' => $groupedTransaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function simulateVAPayment($externalId, Request $request)
    {
        try {
            // Validasi request body
            $request->validate([
                'amount' => 'required|numeric|min:1'
            ], [
                'amount.required' => 'Jumlah pembayaran harus diisi',
                'amount.numeric' => 'Jumlah pembayaran harus berupa angka',
                'amount.min' => 'Jumlah pembayaran minimal 1'
            ]);

            // Siapkan request ke Xendit
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->withOptions([
                    'verify' => false  // Menonaktifkan verifikasi SSL
                ])
                ->post("https://api.xendit.co/callback_virtual_accounts/external_id={$externalId}/simulate_payment", [
                    'amount' => $request->amount
                ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Simulasi pembayaran berhasil',
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan simulasi pembayaran',
                'error' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Simulasi VA Payment Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat simulasi pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPendingTransactionByInvoice($invoiceId)
    {
        try {
            $ewalletChannel = ['EWALLET_DANA', 'EWALLET_OVO', 'EWALLET_LINKAJA', 'EWALLET_SHOPEEPAY'];
            $VAChannel = ['PERMATA', 'BRI', 'BNI', 'MANDIRI'];

            $user = Auth::user();
            $mitraId = $user->karyawan->mitra->id ?? $user->mitra->id;
            $mitra = $this->mitra->where('id', $mitraId)->first();

            if (!$mitra) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra tidak ditemukan'
                ], 404);
            }

            $payment = $this->transaksi
                ->with('transaksiDetail')
                ->where('invoiceId', $invoiceId)
                ->where(column: function ($query) {
                    $query->where('statusOrder', 'UNPAID')
                        ->orWhere('statusOrder', 'PENDING')
                        ->orWhereNull('statusOrder');
                })
                ->first();

            if (isset($payment->xenditId) && in_array($payment->paymentChannel, $VAChannel)) {
                $paymentStatus = VirtualAccounts::retrieve($payment->xenditId);
                $payment->va_payment_status = $paymentStatus;
            } else if (isset($payment->invoiceId) && in_array($payment->paymentChannel, $ewalletChannel)) {
                $paymentStatus = EWallets::getEWalletChargeStatus($payment->xenditId);
                $payment->ewallet_payment_status = $paymentStatus;
            } else if (isset($payment->invoiceId) && $payment->paymentChannel == "QRIS") {
                $paymentStatus =  QRCode::get($payment->xenditId);
                $payment->qris_payment_status = $paymentStatus;
            }

            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi Ditemukan',
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
