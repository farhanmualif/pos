<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KategoriBahan;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $user, $produk, $transaksi, $stokProduk, $mitra, $karyawan;

    public function __construct(User $user, Produk $produk, Transaksi $transaksi, StokProduk $stokProduk, Mitra $mitra, Karyawan $karyawan)
    {
        $this->user = $user;
        $this->produk = $produk;
        $this->transaksi = $transaksi;
        $this->stokProduk = $stokProduk;
        $this->mitra = $mitra;
        $this->karyawan = $karyawan;
    }
    public function super_admin()
    {
        $data = [
            'total_user' => $this->user->where('akses', '2')->count(),
            'total_produk' => $this->produk::count(),
            'total_transaksi' => $this->transaksi::count(),
            'total_mitra' => $this->mitra::count(),
        ];
        return view('super_admin.pages.dashboard.index', $data);
    }

    public function admin()
    {
        $mitraId = Auth::user()->mitra->id;
        $userId = Auth::user()->id;
        $data = [
            'total_karyawan' => $this->karyawan::where('mitraId', $mitraId)->count(),
            'total_transaksi' => $this->transaksi::where('mitraId', $mitraId)->count(),
            'total_produk' => $this->produk::where('mitraId', $mitraId)->count(),
            'total_stok' => $this->stokProduk::where('userId', $userId)->sum('qty'),
        ];
        return view('admin.pages.dashboard.index', $data);
    }


    public function karyawan()
    {
        $user = Auth::user();
        $mitraId = $user->karyawan->mitraId;
        $usernameKasir = $user->username;

        $data = [
            'totalProduk' => $this->produk::where('mitraId', $mitraId)->count(),
            'totalPenjualan' => $this->transaksi::where('usernameKasir', $usernameKasir)->count(),
            'stokProduk' => $this->produk::where('mitraId', $mitraId)->count(),
        ];

        return view('karyawan.pages.dashboard.index', $data);
    }
}
