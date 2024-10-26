@php
    use Carbon\Carbon;
@endphp

@extends('landing-page.main')

@push('title', 'Invoice ' . $invoice['invoiceId'])

@push('content-page')

    <div class="container mt-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white fw-bold">
                Nomor Transaksi : {{ $invoice['invoiceId'] }}
            </div>
            <div class="card-body">
                <img src="{{ URL::asset('assets/images/brand/logo1.png') }}" alt="Image Logo"
                    style="width: 300px; height: auto">
                <table class="mt-3 mb-3">
                    <tbody>
                        <tr>
                            <td style="width: 150px;">Kasir</td>
                            <td style="width: 20px">:</td>
                            <td>
                                <strong>
                                    {{ $invoice['mitra']['user']['nama'] }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Nama Customer</td>
                            <td style="width: 20px">:</td>
                            <td>
                                <strong>
                                    {{ $invoice['namaUser'] }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Nomor HP</td>
                            <td style="width: 20px">:</td>
                            <td>
                                <strong>
                                    {{ $invoice['nomorHpAktif'] == null ? '-' : $invoice['nomorHpAktif'] }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Total Harga</td>
                            <td style="width: 20px">:</td>
                            <td>
                                <strong>
                                    Rp. {{ number_format($invoice['totalHarga']) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Status Transaksi</td>
                            <td style="width: 20px">:</td>
                            <td>
                                @if ($invoice['statusOrder'] == 'PAID' || $invoice['statusOrder'] == 'SETTLED')
                                    <span class="badge bg-success">
                                        <i class="fa fa-check"></i> Sudah Bayar
                                    </span>
                                @elseif($invoice['statusOrder'] == 'UNPAID')
                                    <span class="badge bg-danger">
                                        <i class="fa fa-times"></i> Belum Bayar
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 150px;">Tanggal Order</td>
                            <td style="width: 20px">:</td>
                            <td>
                                @php
                                    $tanggalCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $invoice['tanggalOrder']);
                                    $tanggalFormatBaru = $tanggalCarbon->translatedFormat('d F Y H:i:s');
                                @endphp

                                {{ $tanggalFormatBaru }}
                            </td>
                        </tr>
                        @if ($invoice['tipeTransaksi'] != null)

                            <tr>
                                <td style="width: 150px;">Tipe Transaksi</td>
                                <td style="width: 20px">:</td>
                                <td>
                                    <span class="fw-bold">
                                        {{ $invoice['tipeTransaksi'] }}
                                    </span>
                                </td>
                            </tr>

                            @if (!empty($invoice['paymentChannel']))
                                <tr>
                                    <td style="width: 150px;">Tanggal Bayar</td>
                                    <td style="width: 20px">:</td>
                                    <td>
                                        <span class="fw-bold">
                                            @php
                                                $tanggalCarbonNew = Carbon::createFromFormat(
                                                    'Y-m-d H:i:s',
                                                    $invoice['tanggalBayar'],
                                                );
                                                $tanggalFormatBaruNew = $tanggalCarbonNew->translatedFormat(
                                                    'd F Y H:i:s',
                                                );
                                            @endphp
                                            {{ $tanggalFormatBaruNew }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 150px;">Payment Channel</td>
                                    <td style="width: 20px">:</td>
                                    <td>
                                        <span class="fw-bold">
                                            {{ $invoice['paymentChannel'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">No.</th>
                            <th>Nama</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $nomor = 0;
                            $jumlahhQty = 0;
                            $totalQtyAll = 0;
                        @endphp
                        @foreach ($transaksiDetail as $item)
                            @php
                                $totalQtyAll += $item['qtyProduk'];
                            @endphp
                            <tr>
                                <td class="text-center">{{ ++$nomor }}.</td>
                                <td>{{ $item['namaProduk'] }}</td>
                                <td class="text-center">Rp. {{ number_format($item['hargaProduk']) }}</td>
                                <td class="text-center">{{ $item['qtyProduk'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-center fw-bold">Total Harga : </td>
                            <td class="text-center fw-bold">
                                Rp. {{ number_format($invoice['totalHarga']) }}
                            </td>
                            <td class="text-center fw-bold">{{ $totalQtyAll }}</td>
                        </tr>
                    </tfoot>
                </table>

                @if ($invoice['statusOrder'] == 'UNPAID')
                    <p class="fw-bold">
                        Pilih Metode Pembayaran :
                    </p>
                    <button class="btn btn-primary shadow mb-2" data-bs-toggle="modal" data-bs-target="#modalCash"
                        onclick="cashPayment(`{{ $invoice['id'] }}`)">
                        <i class="fa fa-edit"></i> Cash
                    </button>
                    <a href="{{ config('xendit.url') }}{{ $invoice['xenditId'] }}" class="btn btn-success shadow mb-2">
                        <i class="fa fa-book"></i> Transfer
                    </a>
                @elseif($invoice['statusOrder'] == 'PAID' || $invoice['statusOrder'] == 'SETTLED')
                    <div class="alert alert-success">
                        <div class="text-center">
                            Sudah Melakukan Pembayaran
                            @if ($invoice['paymentChannel'] !== null)
                                , Eksternal ID : <b>{{ $invoice['xenditId'] }}</b>
                            @endif
                        </div>
                    </div>
                @else
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Cash -->
    <div class="modal fade" id="modalCash" tabindex="-1" aria-labelledby="modalCashLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalCashLabel">
                        <i class="fa fa-edit"></i> Pembayaran Cash
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="modal-content-cash">
                    <!-- Modal Content Detail -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

@endpush

@push('javascript')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript">
        function cashPayment(id) {
            $.ajax({
                url: "{{ url('/pembayaran-cash') }}" + "/" + id,
                type: "GET",
                success: function(response) {
                    $("#modal-content-cash").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
    </script>
@endpush
