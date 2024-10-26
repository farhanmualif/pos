@extends('index')
@section('title', 'Dashboard karyawan')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row row-cards">
        <div class="col-sm-6 col-md-6 col-lg-3 col-xl-4">
            <div class="card">
                <div class="card-body text-center list-icons">
                    <i class="si si-briefcase fs-2 text-primary"></i>
                    <p class="card-text mt-3 mb-3">Total Produk</p>
                    <p class="h1 text-center  text-primary">{{ $totalProduk }}</p>
                </div>
            </div>
        </div><!-- col end -->
        <div class="col-sm-6 col-md-6 col-lg-3 col-xl-4">
            <div class="card">
                <div class="card-body text-center list-icons">
                    <i class="si si-basket-loaded fs-2 text-secondary"></i>
                    <p class="card-text mt-3 mb-3">Anda Sudah Melayani</p>
                    <p class="h1 text-center  text-secondary">{{ $totalPenjualan }} Transaksi</p>
                </div>
            </div>
        </div><!-- col end -->
        <div class="col-sm-6 col-md-6 col-lg-3 col-xl-4">
            <div class="card">
                <div class="card-body text-center list-icons">
                    <i class="si si-people fs-2 text-warning"></i>
                    <p class="card-text mt-3 mb-3">Total Stok Produk</p>
                    <p class="h1 text-center  text-warning">{{ $stokProduk }}</p>
                </div>
            </div>
        </div><!-- col end -->
    </div>
@endsection
