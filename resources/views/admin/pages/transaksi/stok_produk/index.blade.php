@extends('index')
@section('title', 'Stok Produk')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/operator/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">No</th>
                                    <th class="wd-15p border-bottom-0">Nama Produk</th>
                                    <th class="wd-20p border-bottom-0">Kategori</th>
                                    <th class="wd-15p border-bottom-0">Harga Produk</th>
                                    <th class="wd-15p border-bottom-0 text-center">Qty</th>
                                    <th class="wd-15p border-bottom-0 text-center">Tambah Qty</th>
                                    <th class="wd-15p border-bottom-0 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produk as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->namaProduk }}</td>
                                        <td>{{ $data->kategori }}</td>
                                        <td>Rp. {{ number_format($data->hargaProduk, 0, ',', '.') }}</td>
                                        @php
                                            $qty = 0;
                                        @endphp
                                        @foreach ($data->stokProduk as $stok)
                                            @if ($stok->produkId == $data->id)
                                                @php
                                                    $qty = $stok->qty;
                                                @endphp
                                            @endif
                                        @endforeach
                                        <td class="text-center">{{ $qty }}</td>
                                        <td class="text-center">
                                            <form action="{{ url('/admin/transaksi/tambah_qty') }}" method="POST">
                                                @csrf
                                                <div class="row align-items-center justify-content-center m-0">
                                                    <div class="col-md-4 mb-3">
                                                        <input type="number" class="form-control text-center"
                                                            name="qty" min="1">
                                                        <input type="hidden" class="form-control" name="produkId"
                                                            value="{{ $data->id }}" min="1">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                                            <i class="fa fa-edit"></i> Simpan
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            @if ($data->stokProduk->isEmpty())
                                                <span
                                                    class="badge rounded-pill bg-warning-transparent me-1 my-1 fw-semibold">Belum
                                                    Ada
                                                    Stok</span>
                                            @else
                                                @if ($data->stokProduk->first()->status == '1')
                                                    <form id="changeStatus{{ $data->id }}"
                                                        action="{{ url('/admin/transaksi/changeStatus/' . $data->id) }}"
                                                        style="display: inline;" method="POST">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-success mt-1 mb-1 me-3 changeStatusBtn"
                                                            data-id="{{ $data->id }}">
                                                            <span>Aktif</span>
                                                        </button>
                                                    </form>
                                                @elseif ($data->stokProduk->first()->status == '0')
                                                    <form id="changeStatus{{ $data->id }}"
                                                        action="{{ url('/admin/transaksi/changeStatus/' . $data->id) }}"
                                                        style="display: inline;" method="POST">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-danger mt-1 mb-1 me-3 changeStatusBtn"
                                                            data-id="{{ $data->id }}">
                                                            <span>Nonaktif</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('.changeStatusBtn').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var changeStatus = $('#changeStatus' + id);
                var statusText = $(this).text().trim() === 'Aktif' ? 'Data akan diubah menjadi Nonaktif!' :
                    'Data akan diubah menjadi Aktif!';
                Swal.fire({
                    title: 'Anda yakin?',
                    text: statusText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, ubah!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        changeStatus.submit();
                    }
                });
            });
        });
    </script>
@endsection
