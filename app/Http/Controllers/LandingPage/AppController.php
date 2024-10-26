<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\KeranjangDetail;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Xendit\Xendit;

class AppController extends Controller
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
    }

    public function home()
    {
        try {

            DB::beginTransaction();

            $data = [
                "produk" => $this->produk->where("mitraId", Auth::user()->karyawan->mitra->id)->where("status", 1)->get(),
            ];

            $stokProduk = $this->stokProduk->whereIn("produkId", $data["produk"]->pluck("id"))->get();

            $transaksis = $this->transaksiDetail->whereIn("idProduk", $data["produk"]->pluck("id"))->get();

            foreach ($data["produk"] as $key => $produk) {
                $produkStok = $stokProduk->where("produkId", $produk->id)->sum("qty");
                $transaksiStok = $transaksis->where("idProduk", $produk->id)->sum("qtyProduk");
                $produk->stok = $produkStok - $transaksiStok;

                if ($produk["stok"] <= 0) {
                    unset($data["produk"][$key]);
                }
            }

            if (!empty(Auth::user())) {
                $data["keranjang"] = $this->keranjang->where("userId", Auth::user()->id)->first();

                if (!empty($data["keranjang"])) {
                    $data["keranjangDetail"] = $this->keranjangDetail->where("keranjangId", $data["keranjang"]["id"])
                        ->get();

                    foreach ($data["keranjangDetail"] as $keranjangDetail) {
                        $produk = $data["produk"]->where("id", $keranjangDetail->produkId)->first();
                        if ($produk) {
                            $keranjangDetail->max_qty = $produk->stok - $keranjangDetail->qty;
                        }
                    }
                }
            }

            DB::commit();

            return view("landing-page.home", $data);
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->to("/")->with("error", $e->getMessage());
        }
    }

    public function landingPage()
    {
        return view("landing-page.main");
    }

    public function keranjang($idProduk)
    {
        try {
            DB::beginTransaction();

            $countKeranjang = $this->keranjang->where("userId", Auth::user()->id)
                ->where("status", 1)
                ->count();

            $produk = $this->produk->where("id", $idProduk)->first();

            if ($countKeranjang == 0) {
                $cart = $this->keranjang->create([
                    "userId" => Auth::user()->id,
                    "tanggal" => date("Y-m-d H:i:s"),
                    "totalHarga" => 0,
                    "mitraId" => Auth::user()->karyawan->mitra->id,
                    "status" => 1
                ]);

                $detailCart = $this->keranjangDetail->create([
                    "keranjangId" => $cart["id"],
                    "produkId" => $produk["id"],
                    "qty" => 1,
                    "harga" => $produk["hargaProduk"]
                ]);

                $cart->update([
                    "totalHarga" => $produk["hargaProduk"]
                ]);
            } else {

                $keranjangBelanja = $this->keranjang->where("userId", Auth::user()->id)
                    ->where("status", 1)
                    ->first();

                $keranjangDetail = $this->keranjangDetail->where("keranjangId", $keranjangBelanja["id"])
                    ->where("produkId", $produk["id"])
                    ->first();

                if ($keranjangDetail) {
                    $keranjangDetail->update([
                        "qty" => $keranjangDetail['qty'] + 1,
                        "harga" => $keranjangDetail['harga'] + $produk["hargaProduk"]
                    ]);

                    $keranjangBelanja->update([
                        "totalHarga" => $keranjangBelanja["totalHarga"] + $produk["hargaProduk"]
                    ]);
                } else {
                    $this->keranjangDetail->create([
                        "keranjangId" => $keranjangBelanja["id"],
                        "produkId" => $produk["id"],
                        "qty" => 1,
                        "harga" => $produk["hargaProduk"]
                    ]);

                    $keranjangBelanja->update([
                        "totalHarga" => $keranjangBelanja["totalHarga"] + $produk["hargaProduk"]
                    ]);
                }
            }

            DB::commit();

            return redirect()->to("/")->with("success", "Produk Sudah Masuk Keranjang");
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->to("/")->with("error", $e->getMessage());
        }
    }

    public function detailProduk($idProduk)
    {
        try {
            DB::beginTransaction();

            $data = [
                "produk" => $this->produk->where("id", $idProduk)->first()
            ];

            $produkStok = $this->stokProduk->where("produkId", $data["produk"]->id)->sum("qty");
            $transaksiStok = $this->transaksiDetail->where("idProduk", $data["produk"]->id)->sum("qtyProduk");
            $data["produk"]->stok = $produkStok - $transaksiStok;

            DB::commit();

            return view("landing-page.detail", $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with("error", $e->getMessage());
        }
    }

    public function detailQty(Request $request, $idProduk)
    {
        try {

            DB::beginTransaction();

            $countKeranjang = $this->keranjang->where("userId", Auth::user()->id)
                ->where("status", 1)
                ->count();

            $produk = $this->produk->where("id", $idProduk)->first();

            if ($countKeranjang == 0) {
                $cart = $this->keranjang->create([
                    "userId" => Auth::user()->id,
                    "tanggal" => date("Y-m-d"),
                    "totalHarga" => 0,
                    "status" => 1
                ]);

                $detailCart = $this->keranjangDetail->create([
                    "keranjangId" => $cart["id"],
                    "produkId" => $idProduk,
                    "qty" => $request["qty-produk"],
                    "harga" => $produk["hargaProduk"]
                ]);

                $cart->update([
                    "totalHarga" => $produk["hargaProduk"] * $request["qty-produk"]
                ]);
            } else {

                $keranjangBelanja = $this->keranjang->where("userId", Auth::user()->id)
                    ->where("status", 1)
                    ->first();

                $keranjangDetail = $this->keranjangDetail->where("keranjangId", $keranjangBelanja["id"])
                    ->where("produkId", $produk["id"])
                    ->first();

                if ($keranjangDetail) {
                    $keranjangDetail->update([
                        "qty" => $keranjangDetail['qty'] + $request["qty-produk"],
                        "harga" => $keranjangDetail['harga'] + ($produk["hargaProduk"] * $request["qty-produk"])
                    ]);

                    $keranjangBelanja->update([
                        "totalHarga" => $keranjangBelanja["totalHarga"] + ($produk["hargaProduk"] * $request["qty-produk"])
                    ]);
                } else {
                    $this->keranjangDetail->create([
                        "keranjangId" => $keranjangBelanja["id"],
                        "produkId" => $produk["id"],
                        "qty" => $request["qty-produk"],
                        "harga" => $produk["hargaProduk"]
                    ]);

                    $keranjangBelanja->update([
                        "totalHarga" => $keranjangBelanja["totalHarga"] + ($produk["hargaProduk"] * $request["qty-produk"])
                    ]);
                }
            }

            DB::commit();

            return redirect()->to("/")->with("success", "Produk Sudah Masuk Keranjang");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with("error", $e->getMessage());
        }
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

            $params = [
                "external_id" => $transaksi["invoiceId"],
                "amount" => $amount,
                "description" => "Pembayaran Transfer",
                "invoice_duration" => 1800,
                "currency" => "IDR",
                "success_redirect_url" => env("APP_URL") . "/riwayat-transaksi"
            ];

            $createInvoiceRequest = \Xendit\Invoice::create($params);

            $this->transaksi->where("id", $transaksi["id"])->update([
                "statusOrder" => "UNPAID",
                "xenditId" => $createInvoiceRequest["id"]
            ]);

            DB::commit();

            return redirect()->to("/transaksi/" . $transaksi["invoiceId"])->with("success", "Data Berhasil di Simpan");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->to("/")->with("error", $e->getMessage());
        }
    }

    public function invoice($no_invoice)
    {
        try {

            DB::beginTransaction();

            $data = [
                "invoice" => $this->transaksi->where("invoiceId", $no_invoice)
                    ->first()
            ];

            $data["transaksiDetail"] = $this->transaksiDetail->where("transaksiId", $data["invoice"]["id"])->get();

            DB::commit();

            return view("landing-page.invoice", $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with("error", $e->getMessage());
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

            return back()->with("error", $e->getMessage());
        }
    }

    public function riwayatTransaksi()
    {
        try {

            DB::beginTransaction();

            $data = [
                "transaksi" => $this->transaksi->where("mitraId", Auth::user()->karyawan->mitra->id)
                    ->where("tipeTransaksi", "!=", NULL)
                    ->orderBy("tanggalBayar", "DESC")
                    ->get()
            ];

            DB::commit();

            return view("landing-page.riwayat-transaksi", $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->to("/")->with("error", $e->getMessage());
        }
    }

    public function pembayaranCash($id)
    {
        try {

            DB::beginTransaction();

            $data["transaksi"] = $this->transaksi->where("id", $id)->first();

            DB::commit();

            return view("landing-page.amount-invoice", $data);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with("error", $e->getMessage());
        }
    }

    public function updateCashAmount(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $transaksi = $this->transaksi->where("id", $id)->first();

            $transaksi->update([
                "tipeTransaksi" => "CASH",
                "statusOrder" => "PAID",
                "tanggalBayar" => date("Y-m-d H:i:s"),
                "xenditId" => NULL
            ]);

            DB::commit();

            return redirect()->to("/riwayat-transaksi")->with("success", "Pembayaran Cash Berhasil dilakukan");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with("error", $e->getMessage());
        }
    }
}
