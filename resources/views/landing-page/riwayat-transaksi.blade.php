@extends('landing-page.main')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.bootstrap4.css">
@endpush

@push('content-page')
    <div class="container mt-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white fw-bold text-uppercase">
                <div class="card-title">
                    Riwayat Transaksi
                </div>
            </div>
            <div class="card-body">
                <table id="example" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No.</th>
                            <th class="text-center">Invoice ID</th>
                            <th>Nama Customer</th>
                            <th class="text-center">Total Harga</th>
                            <th class="text-center">Tipe Transaksi</th>
                            <th class="text-center">Tanggal Order</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $nomer = 0;
                        @endphp
                        @foreach ($transaksi as $item)
                            <tr>
                                <td class="text-center">{{ ++$nomer }}.</td>
                                <td class="text-center">{{ $item->invoiceId }}</td>
                                <td>{{ $item->namaUser }}</td>
                                <td class="text-center">Rp. {{ number_format($item->totalHarga) }}</td>
                                <td class="text-center">
                                    @if ($item->tipeTransaksi == "TRANSFER")
                                        <span class="badge bg-success">
                                            TRANSFER
                                        </span>
                                    @elseif($item->tipeTransaksi == "CASH")
                                        <span class="badge bg-warning">
                                            CASH
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $tanggalCarbonNew = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item['tanggalBayar']);
                                        $tanggalFormatBaruNew = $tanggalCarbonNew->translatedFormat('d F Y H:i:s');
                                    @endphp
                                    {{ $tanggalFormatBaruNew }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('/transaksi/' . $item->invoiceId) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-search"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endpush

@push('javascript')

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap4.js"></script>
    <script type="text/javascript">
        new DataTable('#example');
    </script>
@endpush
