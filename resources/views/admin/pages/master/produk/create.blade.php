<div class="card custom-card">
    <div class="card-header">
        <h3 class="card-title">{{ $titleCreate }}</h3>
    </div>
    <form action="{{ url('admin/master/produk') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="card-body">
            @error('namaProduk')
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            @error('kategori')
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            @error('harga')
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            @error('foto')
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @enderror
            <div class="d-flex flex-column">
                <div class="form-group">
                    <label class="form-label">Nama Produk</label>
                    <input class="form-control" placeholder="Masukan Nama Produk" name="namaProduk" type="text">
                    @error('namaProduk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Kategori</label>
                    <select name="kategori" class="form-control select2 form-select" data-placeholder="Pilih"
                        id="kategori">
                        <option value="">-- Pilih --</option>
                        <option value="Makanan"> Makanan </option>
                        <option value="Minuman"> Minuman </option>
                    </select>
                    @error('kategori')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Harga</label>
                    <input id="hargaInput" class="form-control" placeholder="Masukan Harga Produk" name="harga"
                        type="text">
                    @error('harga')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="place-bottom-right" class="form-label">Foto</label>
                    <input type="file" name="foto" class="dropify" data-height="200">
                    @error('foto')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="reset" class="btn ripple btn-danger">Cancel</button>
            <button type="submit" class="btn ripple btn-primary">Submit</button>
        </div>
    </form>
</div>
