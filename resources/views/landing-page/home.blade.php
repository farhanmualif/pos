@extends('landing-page.main')

@push('title', 'Home')

@push('content-page')

    <div class="container mt-3 mb-3">
        <div class="row">
            @forelse ($produk as $item)
                <div class="col-md-4 mb-3">
                    <div class="card shadow">
                        @if (empty($item['fotoProduk']))
                            <img src="{{ asset('produk_thumbnail/thumbnail_1.jpg') }}" class="card-img-top img-fluid"
                                style="width: 100%; height: auto;" alt="Gambar Produk">
                        @else
                            <img src="{{ asset($item['fotoProduk']) }}" class="card-img-top img-fluid"
                                style="width: 100%; height: auto;" alt="Gambar Produk">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $item['namaProduk'] }}
                            </h5>
                            <p class="card-text">
                                Rp. {{ number_format($item['hargaProduk']) }} -
                                @if ($item['kategori'] == 'Makanan')
                                    <span class="badge bg-success">
                                        Makanan
                                    </span>
                                @elseif($item['kategori'] == 'Minuman')
                                    <span class="badge bg-info">
                                        Minuman
                                    </span>
                                @endif
                            </p>
                            <span>
                                Stok :
                                <strong>
                                    {{ $item['stok'] }}
                                </strong>
                            </span>
                            <hr>
                            @if (empty(Auth::user()))
                                <a onclick="cekAuth()" class="btn btn-primary shadow">
                                    <i class="fa fa-edit"></i> Pesan
                                </a>
                                <a onclick="cekAuth()" class="btn btn-success shadow" style="float: right">
                                    <i class="fa fa-search"></i> Detail
                                </a>
                            @else
                                <a href="{{ url('/keranjang/' . $item['id']) }}" class="btn btn-primary shadow">
                                    <i class="fa fa-edit"></i> Pesan
                                </a>
                                <a onclick="detailProduk('{{ $item['id'] }}')" class="btn btn-success shadow"
                                    style="float: right" data-bs-toggle="modal" data-bs-target="#modalDetail">
                                    <i class="fa fa-search"></i> Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <p class="text-center">
                            Data Tidak Ada
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalDetailLabel">
                        <i class="fa fa-search"></i> Detail Produk
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="modal-content-detail">
                    <!-- Modal Content Detail -->
                </div>
            </div>
        </div>
    </div>
    <!-- End -->

@endpush

@push('javascript')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript">
        function cekAuth() {
            Swal.fire({
                title: "Anda Belum Login",
                text: "Silahkan Login Terlebih Dahulu ",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Iyaa, Saya Akan Login",
                cancelButtonText: "Tidak Ingin Login"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Berhasil!",
                        text: "Anda Akan Diarahkan Ke Halaman Login",
                        icon: "success"
                    }).then(() => {
                        window.location.href = "{{ url('/login') }}"
                    });
                }
            });
        }

        function detailProduk(idProduk) {
            $.ajax({
                url: '{{ url('/keranjang') }}' + "/" + idProduk + "/detail",
                type: "GET",
                success: function(response) {
                    $("#modal-content-detail").html(response);
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
    </script>

@endpush
