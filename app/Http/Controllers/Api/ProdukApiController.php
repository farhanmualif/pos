<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\Produk;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdukApiController extends Controller
{

    protected $produk, $mitra;

    public function __construct(Produk $produk, Mitra $mitra)
    {
        $this->produk = $produk;
        $this->mitra = $mitra;
    }
    public function getAll(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Retrieve the Mitra instance
            $mitra = $this->mitra->where("userId", Auth::user()->id)->first();

            // Check if Mitra is found
            if (!$mitra) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra tidak ditemukan',
                ], 404);
            }

            // Retrieve products with stokProduk relation
            $products = $this->produk::with('stokProduk')
                ->where('mitraId', $mitra->id)
                ->get();

            // Modify the response format
            $modifiedProducts = $products->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kategori' => $item->kategori,
                    'namaProduk' => $item->namaProduk,
                    'slugProduk' => $item->slugProduk,
                    'status' => $item->status,
                    'hargaProduk' => $item->hargaProduk,
                    'fotoProduk' => $item->fotoProduk,
                    'mitraId' => $item->mitraId,
                    'stok_produk' => [
                        'qty' => $item->stokProduk->first()->qty ?? 0
                    ]
                ];
            });

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Mendapatkan Data Produk',
                'data' => $modifiedProducts
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error in getAll: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getById(string $id): JsonResponse
    {
        try {
            $mitra = $this->mitra->where("userId", Auth::id())->firstOrFail();
            $produk = $this->produk->where('mitraId', $mitra->id)
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'status' => true,
                'message' => 'Produk ditemukan',
                'data' => $produk
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
