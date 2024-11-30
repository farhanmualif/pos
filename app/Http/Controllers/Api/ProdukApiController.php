<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\StokProduk;
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

    protected $produk, $mitra, $stokProduk;

    public function __construct(Produk $produk, Mitra $mitra, StokProduk $stokProduk)
    {
        $this->produk = $produk;
        $this->mitra = $mitra;
        $this->stokProduk = $stokProduk;
    }
    public function getAll(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Retrieve the Mitra instance
            $user = Auth::user();
            $mitraId = $user->karyawan->mitra->id ?? $user->mitra->id ?? null;
            $mitra = $this->mitra->where('id', $mitraId)->first();

            $products = null;
            $products = ($mitraId != null) ? $this->produk::with('stokProduk')
                ->where('mitraId', $mitra->id)
                ->get() : $this->produk::with('stokProduk')
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
                        'qty' => $item->stokProduk->first()->qty ?? 0,
                        'status' => $item->stokProduk->first()->status ?? ""
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

            $produk = $this->produk->where('id', $id)->with('stokProduk')
                ->firstOrFail();

            $data = $produk->toArray();
            if (isset($data['stok_produk']) && count($data['stok_produk']) === 1) {
                $data['stok_produk'] = $data['stok_produk'][0];
            }

            return response()->json([
                'status' => true,
                'message' => 'Produk ditemukan',
                'data' => $data
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Produk tidak ditemukan: ' . $e->getMessage(),
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
            'stok.required' => 'Stok harus diisi.',
            'stok.numeric' => 'Stok harus berupa angka.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpeg, png, jpg.',
            'foto.max' => 'Ukuran foto maksimal 2048 KB.',
        ];

        $request->validate([
            'namaProduk' => 'required|string|min:3|max:100',
            'kategori' => 'required|',
            'harga' => 'required|numeric|min:500|max:100000',
            'stok' => 'required|numeric',
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

            $insertProduct =  $this->produk->create([
                'namaProduk' => $request->namaProduk,
                'slugProduk' => Str::slug($request->namaProduk),
                'kategori' => $request->kategori,
                'hargaProduk' => $request->harga,
                'fotoProduk' => $imagePath ?? null,
                'status' => '0',
                'mitraId' => $mitraId,
            ]);

            $this->stokProduk->create([
                "userId" => Auth::user()->id,
                "produkId" => $insertProduct->id,
                "tanggalTransaksi" => now(),
                "qty" => $request->stok,
                "status" => "0",
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

    public function update(Request $request, $id)
    {
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
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:500|max:100000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $messages);

        try {
            DB::beginTransaction();
            $produk = $this->produk::find($id);
            if ($request->hasFile('foto')) {
                if (File::exists(public_path($produk->fotoProduk))) {
                    File::delete(public_path($produk->fotoProduk));
                }
                $imageExtension = $request->file('foto')->getClientOriginalExtension();
                $newImageName = 'thumbnail_' . (count(File::files(public_path('produk_thumbnail'))) + 1) . '.' . $imageExtension;
                $imagePath = 'produk_thumbnail/' . $newImageName;
                $request->file('foto')->move(public_path('produk_thumbnail'), $newImageName);

                $produk->fotoProduk = $imagePath;
            }

            $produk->namaProduk = $request->namaProduk;
            $produk->slugProduk = Str::slug($request->namaProduk);
            $produk->kategori = $request->kategori;
            $produk->hargaProduk = $request->harga;
            $produk->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Produk Berhasil Diperbarui!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal Memperbarui Produk: ' . $e->getMessage(),
            ], 403);
        }
    }

    public function tambahQty(Request $request)
    {
        $stokProduk = $this->stokProduk->where('produkId', $request->produkId)->first();

        try {
            DB::beginTransaction();
            $qty = intval($request->qty);
            if ($stokProduk) {
                $stokProduk->qty += $qty;
                $stokProduk->save();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Stok Produk Tidak Ditemukan',
                ], 404);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Produk Berhasil Diperbarui!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal Memperbarui Produk: ' . $e->getMessage(),
            ], 403);
        }
    }

    public function changeStatus($id)
    {

        $akses =  Auth::user()->akses == 1 || Auth::user()->akses == 2;
        if (!$akses) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melakukan permintaan ini.',
            ], 403);
        }

        try {
            DB::beginTransaction();
            $produk = $this->produk::find($id);
            if ($produk) {
                $produk->status = $produk->status == '0' ? '1' : '0';
                $produk->save();
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil Menggubah Status Produk',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Produk Tidak Dietemukan',
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal Mengubah Status Produk. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeStatusStok($id)
    {
        $akses =  Auth::user()->akses == 1 || Auth::user()->akses == 2;
        if (!$akses) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melakukan permintaan ini.',
            ], 403);
        }

        try {
            $stokProduk = $this->stokProduk::where('produkId', $id)->first();
            DB::beginTransaction();
            if ($stokProduk) {
                $stokProduk->status = $stokProduk->status == '0' ? '1' : '0';
                $stokProduk->save();
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil Menggubah Status Stok Produk',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Stok Produk Tidak Ditemukan',
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal Mengubah Status Stok Produk' . $e->getMessage(),
            ], 500);
        }
    }
}
