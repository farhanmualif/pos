<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CallbackController extends Controller
{
    protected $serverKey, $transaksi;

    public function __construct()
    {
        $this->serverKey = config("xendit.xendit_key");
        $this->transaksi = new Transaksi();
    }

    public function postCallback(Request $request)
    {
        try {
            Log::info('Xendit Callback received:', [
                'headers' => $request->header(),
                'payload' => $request->all()
            ]);

            DB::beginTransaction();

            $reqHeaders = $request->header('x-callback-token');
            $xIncomingCallbackTokenHeader = isset($reqHeaders) ? $reqHeaders : "";

            if ($xIncomingCallbackTokenHeader) {
                if ($request->status == 'PAID' || $request->status == 'SETTLED') {
                    $transaksi = $this->transaksi->where("xenditId", $request->id)->first();

                    if (!$transaksi) {
                        Log::error('Transaction not found for xenditId:', ['xenditId' => $request->id]);
                        throw new \Exception('Transaction not found');
                    }

                    Log::info('Found transaction:', [
                        'transaksiId' => $transaksi->id,
                        'xenditId' => $transaksi->xenditId
                    ]);

                    // Ambil detail transaksi
                    $transaksiDetails = TransaksiDetail::where('transaksiId', $transaksi->id)->get();

                    Log::info('Transaction details found:', [
                        'count' => $transaksiDetails->count(),
                        'details' => $transaksiDetails->toArray()
                    ]);

                    // Update stok untuk setiap produk
                    foreach ($transaksiDetails as $detail) {
                        $produk = Produk::find($detail->produkId);
                        if ($produk) {
                            $oldStock = $produk->stokProduk;
                            $newStock = $oldStock - $detail->qty;

                            Log::info('Updating stock for product:', [
                                'productId' => $produk->id,
                                'oldStock' => $oldStock,
                                'qty' => $detail->qty,
                                'newStock' => $newStock
                            ]);

                            $produk->stokProduk = $newStock;
                            $produk->save();

                            Log::info('Stock updated successfully:', [
                                'productId' => $produk->id,
                                'finalStock' => $produk->fresh()->stokProduk
                            ]);
                        } else {
                            Log::warning('Product not found:', [
                                'productId' => $detail->produkId
                            ]);
                        }
                    }

                    $transaksi["tipeTransaksi"] = "TRANSFER";
                    $transaksi["statusOrder"] = $request->status;
                    $transaksi["tanggalBayar"] = date("Y-m-d H:i:s");
                    $transaksi["paymentChannel"] = $request->payment_channel;
                    $transaksi->update();

                    Log::info('Transaction updated successfully:', [
                        'transaksiId' => $transaksi->id,
                        'status' => $transaksi->statusOrder
                    ]);
                }
            }

            DB::commit();
            Log::info('Callback processed successfully');

            return response()->json([
                "status" => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Callback processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                "status" => false
            ]);
        }
    }
}
