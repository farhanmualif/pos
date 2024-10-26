@extends('index')
@section('title', 'Dashboard Super Admin')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <a href="{{ url('/super_admin/master/karyawan') }}" class="card card-counter bg-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-users mb-0 text-white-transparent"></i>
                    </div>
                    <div class="mt-2 mb-0 text-white">
                        <h2 class="mb-0">{{ $total_user }}</h2>
                        <p class="text-white mt-1 mb-0">Karyawan</p>
                    </div>
                </div>
            </a>
        </div><!-- col end -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <a href="{{ url('/super_admin/master/produk') }}" class="card card-counter bg-secondary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <i class="si si-layers mb-0 text-white-transparent"></i>
                    </div>
                    <div class="mt-2 mb-0 text-white">
                        <h2 class="mb-0">{{ $total_produk }}</h2>
                        <p class="text-white mt-1 mb-0">Produk </p>
                    </div>
                </div>
            </a>
        </div><!-- col end -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <a href="{{ url('/super_admin/master/mitra') }}" class="card card-counter bg-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-hospital-o mt-3 mb-0 text-white-transparent"></i>
                    </div>
                    <div class="mt-2 mb-0 text-white">
                        <h2 class="mb-0">{{ $total_mitra }}</h2>
                        <p class="text-white mt-1 mb-0">Mitra</p>
                    </div>
                </div>
            </a>
        </div><!-- col end -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <a href="{{ url('/super_admin/transaksi') }}" class="card card-counter bg-warning">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fe fe-send mt-3 mb-0 text-white-transparent"></i>
                    </div>
                    <div class="mt-2 mb-0 text-white">
                        <h2 class="mb-0">{{ $total_transaksi }}</h2>
                        <p class="text-white mt-1 mb-0">Transaksi</p>
                    </div>
                </div>
            </a>
        </div><!-- col end -->
    </div>
@endsection

@section('script')
    <script>
        function redirectToRoute(route) {
            window.location.href = route;
        }
    </script>
@endsection
