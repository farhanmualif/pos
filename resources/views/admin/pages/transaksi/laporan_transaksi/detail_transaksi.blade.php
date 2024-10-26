@extends('index')
@section('title', 'Detail Transaksi')
@section('content')
    <div class="page-header d-sm-flex d-block align-items-center">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/admin/laporan/transaksi/') }}">{{ $breadcrumb_1 }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-12">
            <div class="card cart">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Detail Transaksi</h3>
                </div>
                @foreach ($transaksi as $data)
                    @foreach ($detailTransaksi[$data->id] as $item)
                        <div class="card-body">
                            <div class="d-flex">
                                @if ($item['fotoProduk'] != null)
                                    <img class="avatar-xl p-0 br-7" src="{{ asset('' . $item['fotoProduk']) }}"
                                        alt="img">
                                @else
                                    <img class="avatar-xl p-0 br-7" src="{{ url('/assets') }}/images/pngs/12.png"
                                        alt="img">
                                @endif
                                <div class="ms-3">
                                    <h4 class="mb-1 fw-semibold fs-14"><a
                                            href="shop-description.html">{{ $item['namaProduk'] }}</a></h4>
                                    <div class="text-warning fs-14">
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star"></i>
                                        <i class="fa fa-star-half-o"></i>
                                        <i class="fa fa-star-o"></i>
                                    </div>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur.</p>
                                </div>
                                <div class="ms-auto">
                                    <span class="me-4 fs-16 fw-semibold lh-1">
                                        Rp.{{ number_format($item['hargaProduk']) }}
                                        x {{ $item['qtyProduk'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
                <table class="table mt-5 table-bordered">
                    <tr class="your-order">
                        <td>Kode Transaksi</td>
                        <td class="fw-bold">
                            {{ $transaksi[0]['invoiceId'] }}
                        </td>
                    </tr>
                    <tr class="your-order">
                        <td>Nama Customer</td>
                        <td class="fw-bold">
                            {{ $transaksi[0]['namaUser'] }}
                        </td>
                    </tr>
                    @if ($transaksi[0]['tipeTransaksi'] == 'TRANSFER')
                        <tr class="your-order">
                            <td>Jenis Pembayaran</td>
                            <td>{{ $transaksi[0]['tipeTransaksi'] }}</td>
                        </tr>
                        <tr class="your-order">
                            <td>Metode Pembayaran</td>
                            <td>{{ $transaksi[0]['paymentChannel'] }}</td>
                        </tr>
                    @elseif ($transaksi[0]['tipeTransaksi'] == 'CASH')
                        <tr class="your-order">
                            <td>Jenis Pembayaran</td>
                            <td>{{ $transaksi[0]['tipeTransaksi'] }}</td>
                        </tr>
                    @endif
                    <tr class="your-order">
                        <td>Status Pembayaran</td>
                        <td>{{ $transaksi[0]['statusOrder'] }}</td>
                    </tr>
                    <tr class="your-order">
                        <td class="fs-18">Total</td>
                        <td class="fs-18 fw-semibold">
                            Rp.{{ number_format($transaksi[0]['totalHarga'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer ">
            </div>
        </div>
    </div>
@endsection
