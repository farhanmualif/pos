<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    protected $produk;

    public function __construct(Produk $produk)
    {
        $this->produk = $produk;
    }
    public function index()
    {
        $content  = [
            'breadcrumb' => 'Dashboard',
            'breadcrumb_active' => 'Produk',
            'title' => 'Produk',
            'titleCreate' => 'Tambah Produk',
        ];

        $data['produk'] = $this->produk::orderByDesc('namaProduk')->get();
        return view('super_admin.pages.master.produk.index', $content, $data);
    }
}
