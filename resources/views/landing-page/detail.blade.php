<div class="modal-body">
    <div class="card shadow mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <img src="{{ URL::asset('produk_thumbnail/thumbnail_1.jpg') }}" alt="Gambar Produk" class="img-fluid"
                        style="height: 100%;">
                </div>
                <div class="col-md-8">
                    <span class="produk fw-bold">
                        {{ $produk['nama'] }}
                    </span>
                    <br>
                    <small>
                        {{ $produk['kategori'] }}
                    </small>
                    <p class="harga-produk">
                        Rp. {{ number_format($produk['hargaProduk']) }}
                    </p>
                    <form action="{{ url('/keranjang/' . $produk['id'] . '/detailQty') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-3">
                                    <input type="number" class="form-control" name="qty-produk" id="qty-produk"
                                        min="1" placeholder="0" max="{{ $produk['stok'] }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button type="reset" class="btn btn-danger btn-xs" style="margin-right: 10px">
                                        <i class="fa fa-times"></i> Batal
                                    </button>
                                    <button class="btn btn-primary btn-xs">
                                        <i class="fa fa-edit"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
