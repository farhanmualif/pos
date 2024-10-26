<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\KeranjangDetail;
use App\Models\Mitra;
use App\Models\Produk;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KeranjangApiController extends Controller
{
    protected $keranjang, $keranjangDetail, $mitra, $produk;

    public function __construct(Keranjang $keranjang, KeranjangDetail $keranjangDetail, Mitra $mitra, Produk $produk)
    {
        $this->keranjang = $keranjang;
        $this->mitra = $mitra;
        $this->keranjangDetail = $keranjangDetail;
        $this->produk = $produk;
    }

    public function getAll(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $mitra = $this->mitra->where("userId", Auth::user()->id)->first();
            $keranjang = $this->keranjang
                ->with(["keranjangDetails.produk"])
                ->where('mitraId', $mitra->id)
                ->get();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Mendapatkan Data Keranjang',
                'data' => $keranjang
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),

            ], 500);
        }
    }
    public function getDetail(string $id): JsonResponse
    {
        try {
            $keranjang = $this->keranjang
                ->with(['mitra', 'keranjangDetails.produk'])
                ->where('id', $id)
                ->whereHas('mitra', function ($query) {
                    $query->where('userId', Auth::id());
                })
                ->firstOrFail();

            $responseData = [
                'keranjang' => [
                    'id' => $keranjang->id,
                    'userId' => $keranjang->userId,
                    'tanggal' => $keranjang->tanggal,
                    'totalHarga' => $keranjang->totalHarga,
                    'mitraId' => $keranjang->mitraId,
                    'status' => $keranjang->status,
                ],
                'mitra' => $keranjang->mitra,
                'keranjang_details' => $keranjang->keranjangDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'keranjangId' => $detail->keranjangId,
                        'produkId' => $detail->produkId,
                        'qty' => $detail->qty,
                        'harga' => $detail->harga,
                        'produk' => $detail->produk,
                    ];
                }),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Mendapatkan Detail Data Keranjang',
                'data' => $responseData
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data Keranjang tidak ditemukan',
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
        try {
            // Validate the request
            $this->validate($request, [
                'tanggal' => 'required|date_format:d-m-Y',
                'produk' => 'required|array',
                'produk.*.id' => 'required|exists:produk,id',
                'produk.*.qty' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $e->validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Fetch the mitraId based on the authenticated user
            $mitra = $this->mitra->where("userId", Auth::id())->firstOrFail();

            // Format tanggal ke YYYY-MM-DD
            $formattedTanggal = Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d');

            // Cek apakah sudah ada keranjang untuk user dan mitra ini
            $existingKeranjang = $this->keranjang
                ->where('userId', Auth::id())
                ->where('mitraId', $mitra->id)
                ->where('status', '1') // Assuming '1' means active cart
                ->first();

            $keranjang = $existingKeranjang;
            $totalHarga = $existingKeranjang ? $existingKeranjang->totalHarga : 0;
            $newDetails = [];
            $updateDetails = [];

            foreach ($request->produk as $itemProduk) {
                $produk = $this->produk->findOrFail($itemProduk['id']);

                if ($existingKeranjang) {
                    // Cek apakah produk sudah ada di keranjang
                    $existingDetail = $this->keranjangDetail
                        ->where('keranjangId', $existingKeranjang->id)
                        ->where('produkId', $produk->id)
                        ->first();

                    if ($existingDetail) {
                        // Update qty jika produk sudah ada
                        $newQty = $existingDetail->qty + $itemProduk['qty'];
                        $updateDetails[] = [
                            'detail' => $existingDetail,
                            'newQty' => $newQty,
                            'harga' => $produk->hargaProduk
                        ];

                        // Update total harga
                        $totalHarga += ($produk->hargaProduk * $itemProduk['qty']);
                    } else {
                        // Tambah produk baru ke keranjang yang sudah ada
                        $newDetails[] = [
                            'id' => Str::uuid()->toString(),
                            'keranjangId' => $existingKeranjang->id,
                            'produkId' => $produk->id,
                            'qty' => $itemProduk['qty'],
                            'harga' => $produk->hargaProduk
                        ];
                        $totalHarga += ($produk->hargaProduk * $itemProduk['qty']);
                    }
                } else {
                    // Buat keranjang baru jika belum ada
                    if (!$keranjang) {
                        $keranjang = $this->keranjang->create([
                            'userId' => Auth::id(),
                            'tanggal' => $formattedTanggal,
                            'totalHarga' => 0, // Will be updated later
                            'mitraId' => $mitra->id,
                            'status' => '1'
                        ]);
                    }

                    // Tambah semua produk sebagai detail baru
                    $newDetails[] = [
                        'id' => Str::uuid()->toString(),
                        'keranjangId' => $keranjang->id,
                        'produkId' => $produk->id,
                        'qty' => $itemProduk['qty'],
                        'harga' => $produk->hargaProduk
                    ];
                    $totalHarga += ($produk->hargaProduk * $itemProduk['qty']);
                }
            }

            // Update existing details
            foreach ($updateDetails as $update) {
                $update['detail']->update([
                    'qty' => $update['newQty'],
                    'harga' => $update['harga']
                ]);
            }

            // Insert new details
            if (!empty($newDetails)) {
                $this->keranjangDetail->insert($newDetails);
            }

            // Update total harga
            $keranjang->update(['totalHarga' => $totalHarga]);

            DB::commit();

            // Fetch updated cart data
            $updatedKeranjang = $this->keranjang
                ->with(['keranjangDetails.produk'])
                ->find($keranjang->id);

            return response()->json([
                'status' => true,
                'message' => $existingKeranjang ? 'Berhasil Mengupdate Keranjang' : 'Berhasil Menambah Keranjang Baru',
                'data' => $updatedKeranjang,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateQty(Request $request, $idKeranjangDetail)
    {
        try {

            DB::beginTransaction();

            $keranjangDetail = $this->keranjangDetail->where("id", $idKeranjangDetail)->first();

            $hargaProduk = $keranjangDetail["produk"]["hargaProduk"];

            $keranjangDetail->update([
                "qty" => $request->qtyNew,
                "harga" => $request->qtyNew * $hargaProduk
            ]);

            $keranjangDetailAll = $this->keranjangDetail->where("keranjangId", $keranjangDetail["keranjang"]["id"])->get();

            $hargaKeranjangDetail = 0;

            foreach ($keranjangDetailAll as $item) {
                $hargaKeranjangDetail += $item["harga"];
            }

            $keranjang = $this->keranjang->where("id", $keranjangDetail["keranjang"]["id"])->first();

            $keranjang->update([
                "totalHarga" => $hargaKeranjangDetail
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Qty Berhasil di Simpan"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => true,
                "message" => "Gagal menyimpan Qty {$e->getMessage()}"
            ], 500);
        }
    }
}
