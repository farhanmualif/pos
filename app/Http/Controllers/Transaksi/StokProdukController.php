<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class StokProdukController extends Controller
{
    protected $produk, $stokProduk;

    public function __construct(Produk $produk, StokProduk $stokProduk)
    {
        $this->produk = $produk;
        $this->stokProduk = $stokProduk;
    }
    public function index()
    {
        $content = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Stok Produk',
            'title' => 'Stok Produk',
        ];
        $data = [
            'produk' => $this->produk->where('mitraId', Auth::user()->mitra->id)->get(),
        ];
        return view('admin.pages.transaksi.stok_produk.index', $data, $content);
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
                $this->stokProduk->create([
                    'userId' => Auth::user()->id,
                    'produkId' => $request->produkId,
                    'tanggalTransaksi' => date('Y-m-d'),
                    'qty' => $qty,
                    'status' => 1,
                ]);
            }
            DB::commit();
            Alert::success('success', 'Success Qty Berhasil Ditambahkan!');
            return back()->with('success', 'Succes Qty Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('error', 'Terjadi Kesalahan Saat Menambahkan Qty!' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan Saat Menambahkan Qty!' . $e->getMessage());
        }
    }

    public function changeStatus($id)
    {
        $stokProduk = $this->stokProduk::where('produkId', $id)->first();
        try {
            DB::beginTransaction();
            if ($stokProduk) {
                $stokProduk->status = $stokProduk->status == '0' ? '1' : '0';
                $stokProduk->save();
                DB::commit();
                Alert::success('Berhasil', 'Status produk berhasil diubah.');
                return back()->with('success', 'Status produk berhasil diubah.');
            } else {
                throw new \Exception('Produk tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error('Error', 'Gagal mengubah status produk: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status produk: ' . $e->getMessage());
        }
    }
}
