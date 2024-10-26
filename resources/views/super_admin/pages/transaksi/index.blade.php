@php
    use Carbon\Carbon;
@endphp
@extends('index')
@section('title', 'Transaksi')
@section('content')
    <div class="page-header d-sm-flex d-block align-items-center">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/super_admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card">
                <div class="card-header border-bottom ">
                    <h3 class="card-title mb-0">{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-12">
                        <form action="{{ url('/super_admin/transaksi/filterByStatus') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <label class="control-label col-sm-1" style="margin-top: 5px;"> Filter : </label>
                                    <div class="col-md-3">
                                        <select name="filterBayar" class="form-control form-select select2  "
                                            id="filterBayar" required>
                                            <option value="">- Pilih -</option>
                                            <option value="PAID" {{ session('status') == 'PAID' ? 'selected' : '' }}>
                                                Sudah Bayar
                                            </option>
                                            <option value="UNPAID" {{ session('status') == 'UNPAID' ? 'selected' : '' }}>
                                                Belum Bayar
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="submit" class="btn btn-pill btn-primary" value="FILTER">
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table id="data-table" class="table border p-0 text-nowrap mb-0 mt-5">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th class="fw-semibold fs-14 border-bottom-0 w-5">
                                        Invoice</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Tanggal Order</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Nama Pembeli</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Mitra</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Total</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Status</th>
                                    <th class="fw-semibold fs-14 border-bottom-0">
                                        Transaksi</th>
                                    <th class="fw-semibold fs-14 border-bottom-0 text-center">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (empty(session('filterBayar')))
                                    @foreach ($transaksi as $data)
                                        <tr class="border-bottom">
                                            <td>
                                                <h6 class="mb-0 mt-1 fs-13 text-dark fw-semibold">
                                                    {{ $data['invoiceId'] }}
                                                </h6>
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">
                                                @php
                                                    $tanggalCarbon = Carbon::createFromFormat(
                                                        'Y-m-d H:i:s',
                                                        $data['tanggalOrder'],
                                                    );
                                                    $tanggalFormatBaru = $tanggalCarbon->translatedFormat(
                                                        'd F Y H:i:s',
                                                    );
                                                    $tanggalFormatBaru = str_replace(
                                                        ' 00:00:00',
                                                        '',
                                                        $tanggalFormatBaru,
                                                    );
                                                @endphp
                                                <i class="fe fe-calendar me-2"></i>{{ $tanggalFormatBaru }}
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">{{ $data['namaUser'] }}</td>
                                            <td>
                                                <div>
                                                    <span
                                                        class="text-muted fs-12 fw-semibold">{{ $data['usernameKasir'] }}</span>
                                                    <h6 class="mb-0 mt-1 fs-13 text-dark fw-semibold">
                                                        {{ $data['namaMitra'] }}
                                                    </h6>
                                                </div>
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">
                                                {{ 'Rp ' . number_format($data['totalHarga'], 0, ',', '.') }}</td>
                                            <td class="fs-15 fw-semibold">
                                                @if ($data['statusOrder'] == 'UNPAID')
                                                    <span class="badge text-white bg-danger fw-semibold fs-11">Belum
                                                        Bayar</span>
                                                @elseif ($data['statusOrder'] == 'PAID')
                                                    <span class="badge text-white bg-success fw-semibold fs-11">Sudah
                                                        Bayar</span>
                                                @endif
                                            </td>
                                            <td class="fs-15 fw-semibold">
                                                @if ($data['tipeTransaksi'] == 'CASH')
                                                    <span
                                                        class="badge rounded-pill badge-gradient-success me-1 my-1">Cash</span>
                                                @elseif ($data['tipeTransaksi'] == 'TRANSFER')
                                                    <span class="badge rounded-pill badge-gradient-primary me-1 my-1">Online
                                                        Payment</span>
                                                @elseif ($data['tipeTransaksi'] == '')
                                                    <span class="badge rounded-pill badge-gradient-danger me-1 my-1">Belum
                                                        Bayar</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn br-7 btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#Vertically{{ $data['id'] }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @foreach (session('filterBayar') as $filter)
                                        <tr class="border-bottom">
                                            <td>
                                                <h6 class="mb-0 mt-1 fs-13 text-dark fw-semibold">
                                                    {{ $filter['invoiceId'] }}
                                                </h6>
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">
                                                @php
                                                    $tanggalCarbon = Carbon::createFromFormat(
                                                        'Y-m-d H:i:s',
                                                        $filter['tanggalOrder'],
                                                    );
                                                    $tanggalFormatBaru = $tanggalCarbon->translatedFormat(
                                                        'd F Y H:i:s',
                                                    );
                                                    $tanggalFormatBaru = str_replace(
                                                        ' 00:00:00',
                                                        '',
                                                        $tanggalFormatBaru,
                                                    );
                                                @endphp
                                                <i class="fe fe-calendar me-2"></i>{{ $tanggalFormatBaru }}
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">{{ $filter['namaUser'] }}</td>
                                            <td>
                                                <div>
                                                    <span
                                                        class="text-muted fs-12 fw-semibold">{{ $filter['usernameKasir'] }}</span>
                                                    <h6 class="mb-0 mt-1 fs-13 text-dark fw-semibold">
                                                        {{ $filter['namaMitra'] }}
                                                    </h6>
                                                </div>
                                            </td>
                                            <td class="fs-13 fw-semibold text-dark">
                                                {{ 'Rp ' . number_format($filter['totalHarga'], 0, ',', '.') }}</td>
                                            <td class="fs-15 fw-semibold">
                                                @if ($filter['statusOrder'] == 'UNPAID')
                                                    <span class="badge text-white bg-danger fw-semibold fs-11">Belum
                                                        Bayar</span>
                                                @elseif ($filter['statusOrder'] == 'PAID')
                                                    <span class="badge text-white bg-success fw-semibold fs-11">Sudah
                                                        Bayar</span>
                                                @endif
                                            </td>
                                            <td class="fs-15 fw-semibold">
                                                @if ($filter['tipeTransaksi'] == 'CASH')
                                                    <span
                                                        class="badge rounded-pill badge-gradient-success me-1 my-1">Cash</span>
                                                @elseif ($filter['tipeTransaksi'] == 'TRANSFER')
                                                    <span class="badge rounded-pill badge-gradient-primary me-1 my-1">Online
                                                        Payment</span>
                                                @elseif ($filter['tipeTransaksi'] == '')
                                                    <span class="badge rounded-pill badge-gradient-danger me-1 my-1">Belum
                                                        Bayar</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn br-7 btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#Vertically{{ $filter['id'] }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($transaksi as $data)
        <div class="modal fade" id="Vertically{{ $data->id }}">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Detail Transaksi</h6>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Foto Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detailTransaksi[$data->id] as $item)
                                    <tr>
                                        <td class="text-center"><img src="{{ url('/assets') }}/images/users/male/33.jpg"
                                                class="avatar avatar-md brround" alt="person-image"></td>
                                        <td>{{ $item->namaProduk }}</td>
                                        <td>{{ $item->qtyProduk }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
