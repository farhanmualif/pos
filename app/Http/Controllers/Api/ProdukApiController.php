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
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function store(Request $request)
    {

        $akses =  Auth::user()->akses == 1 || Auth::user()->akses == 2;
        if (!$akses) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melakukan permintaan ini.',
            ], 403);
        }

        $messages = [
            'namaProduk.required' => 'Nama produk harus diisi.',
            'namaProduk.string' => 'Nama produk harus berupa teks.',
            'namaProduk.min' => 'Panjang nama produk minimal :min karakter.',
            'namaProduk.max' => 'Panjang nama produk maksimal :max karakter.',
            'kategori.required' => 'Kategori harus diisi.',
            'kategori.string' => 'Kategori harus berupa teks.',
            'harga.required' => 'Harga harus diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga minimal adalah :min.',
            'harga.max' => 'Harga maksimal adalah :max.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpeg, png, jpg.',
            'foto.max' => 'Ukuran foto maksimal 2048 KB.',
        ];

        $request->validate([
            'namaProduk' => 'required|string|min:3|max:100',
            'kategori' => 'required|',
            'harga' => 'required|numeric|min:500|max:100000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $messages);

        try {

            DB::beginTransaction();

            if ($request->hasFile('foto')) {
                $imageExtension = $request->file('foto')->getClientOriginalExtension();
                $newImageName = 'thumbnail_' . (count(File::files(public_path('produk_thumbnail'))) + 1) . '.' . $imageExtension;
                $imagePath = 'produk_thumbnail/' . $newImageName;

                $request->file('foto')->move(public_path('produk_thumbnail'), $newImageName);
            }


            $mitraId = Auth::user()->mitra->id;

            $this->produk->create([
                'namaProduk' => $request->namaProduk,
                'slugProduk' => Str::slug($request->namaProduk),
                'kategori' => $request->kategori,
                'hargaProduk' => $request->harga,
                'fotoProduk' => $imagePath ?? null,
                'status' => '0',
                'mitraId' => $mitraId,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil Menambah Data Produk',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
