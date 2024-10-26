@extends('index')
@section('title', 'Dashboard Admin')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row ">
        <div class="col-lg-12">
            <div class="card ">
                <div class="card-body p-4 text-dark">
                    <div class="statistics-info">
                        <div class="row text-center">
                            <div class="col-lg-3 col-md-6 mt-4 mb-4">
                                <a href="{{ url('/admin/master/karyawan') }}" class="counter-status">
                                    <div class="counter-icon text-danger">
                                        <i class="si si-people"></i>
                                    </div>
                                    <p class="mb-2">Karyawan</p>
                                    <h2 class="counter text-primary mb-0">{{ $total_karyawan }}</h2>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mt-4 mb-4">
                                <a href="{{ url('/admin/master/produk') }}" class="counter-status">
                                    <div class="counter-icon border-danger">
                                        <i class="si si-rocket text-danger"></i>
                                    </div>
                                    <p class="mb-2">Produk</p>
                                    <h2 class="counter text-danger mb-0">{{ $total_produk }}</h2>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6  mt-4 mb-4">
                                <a href="{{ url('/admin/transaksi/stok_produk') }}" class="counter-statuss">
                                    <div class="counter-icon border-success">
                                        <i class="si si-docs text-success"></i>
                                    </div>
                                    <p class="mb-2">Stok Produk</p>
                                    <h2 class="counter text-success mb-0">{{ $total_stok }}</h2>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mt-4 mb-4">
                                <a href="{{ url('/admin/transaksi/laporan/transaksi') }}" class="counter-status">
                                    <div class="counter-icon border-info">
                                        <i class="si si-graph text-info"></i>
                                    </div>
                                    <p class="mb-2">Transaksi</p>
                                    <h2 class="counter text-info mb-0">{{ $total_transaksi }}</h2>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')

@endsection
