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
use Illuminate\Support\Facades\Validator;

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

    public function getDetailKeranjangByProdukId(string $idProduk)
    {
        try {
            $findKeranjang = $this->keranjangDetail->where('produkId', $idProduk)->with('produk')->first();

            if (!$findKeranjang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Keranjang tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data Keranjang ditemukan',
                'data' => $findKeranjang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDetail(string $idProduk): JsonResponse
    {
        try {
            $keranjang = $this->keranjang
                ->with(['mitra', 'keranjangDetails.produk'])
                ->where('idProduk', $idProduk)
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

    public function addCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date_format:d-m-Y',
            'produk' => 'required|array',
            'produk.*.id' => 'required',
            'produk.*.qty' => 'required|numeric',
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date_format' => 'Format tanggal tidak valid',
            'produk.required' => 'Produk harus diisi',
            'produk.array' => 'Produk harus berupa array',
            'produk.*.id.required' => 'ID produk harus diisi',
            'produk.*.qty.required' => 'Jumlah produk harus diisi',
            'produk.*.qty.numeric' => 'Jumlah produk harus berupa angka',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $countKeranjang = $this->keranjang->where("userId", Auth::user()->id)
                ->where("status", 1)
                ->count();

            $mitra = $this->mitra->where('userId', Auth::user()->id)->first();
            if (!$mitra) {
                return response()->json([
                    "status" => false,
                    "message" => "Mitra tidak ditemukan"
                ], 404);
            }

            // Convert the date to the correct format
            $formattedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d');

            // Create or get existing cart
            $keranjang = $countKeranjang === 0 ? $this->keranjang->create([
                "userId" => Auth::user()->id,
                "tanggal" => $formattedDate,
                "mitraId" => $mitra->id,
                "totalHarga" => 0,
                "status" => 1
            ]) : $this->keranjang->where("userId", Auth::user()->id)
                ->where("status", 1)
                ->first();

            $totalHarga = 0;

            foreach ($request->produk as $item) {
                $produk = $this->produk->where("id", $item['id'])->first();

                if (!$produk) {
                    return response()->json([
                        "status" => false,
                        "message" => "Produk dengan ID {$item['id']} tidak ditemukan"
                    ], 404);
                }

                $keranjangDetail = $this->keranjangDetail->where("keranjangId", $keranjang->id)
                    ->where("produkId", $produk->id)
                    ->first();

                if ($keranjangDetail) {
                    $keranjangDetail->update([
                        "qty" => $keranjangDetail['qty'] + $item['qty'],
                        "harga" => $keranjangDetail['harga'] + ($produk->hargaProduk * $item['qty'])
                    ]);
                } else {
                    $this->keranjangDetail->create([
                        "keranjangId" => $keranjang->id,
                        "produkId" => $produk->id,
                        "qty" => $item['qty'],
                        "harga" => $produk->hargaProduk * $item['qty']
                    ]);
                }

                $totalHarga += $produk->hargaProduk * $item['qty'];
            }

            // Update total price of the cart
            $keranjang->update([
                "totalHarga" => $keranjang->totalHarga + $totalHarga
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Berhasil Menambah Produk Ke Keranjang"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => false,
                "message" => "Terjadi Kesalahan: {$e->getMessage()}"
            ], 500);
        }
    }

    public function updateQty(Request $request, string $produkId)
    {
        try {
            DB::beginTransaction();

            $keranjangDetail = $this->keranjangDetail->where("produkId", "=", $produkId)->first();

            if (!$keranjangDetail) {
                return response()->json([
                    "status" => false,
                    "message" => "Keranjang Detail Tidak ditemukan"
                ], 404);
            }

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
            if (!$keranjang) {
                return response()->json([
                    "status" => false,
                    "message" => "Keranjang Detail Tidak ditemukan"
                ], 404);
            }

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


    public function delete()
    {
        try {
            DB::beginTransaction();

            $mitra = $this->mitra->where("userId", Auth::user()->id)->first();

            if (!$mitra) {
                return response()->json([
                    "status" => false,
                    "message" => "Mitra tidak ditemukan"
                ], 404);
            }

            $keranjang = $this->keranjang->where('mitraId', $mitra->id)->first();

            if (!$keranjang) {
                return response()->json([
                    "status" => false,
                    "message" => "Keranjang tidak ditemukan"
                ], 404);
            }

            $detailKeranjang = $this->keranjangDetail->where('keranjangId', $keranjang->id);

            $detailKeranjang->delete();
            $keranjang->delete();

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Keranjang berhasil dihapus",
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "Keranjang tidak ditemukan"
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ], 500);
        }
    }
}
