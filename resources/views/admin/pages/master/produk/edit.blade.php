<form action="{{ url('admin/master/produk/' . $edit->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label for="recipient-name" class="form-control-label">Nama Produk</label>
            <input id="editHargaInput" class="form-control" value="{{ $edit->namaProduk }}" name="namaProduk_modal"
                type="text">
        </div>
        <div class="form-group">
            <label for="recipient-name" class="form-control-label">Nama Kategori</label>
            <select name="kategori_modal" class="form-control select2 form-select" id="kategori">
                <option value="">-- Pilih --</option>
                <option value="Makanan" {{ $edit->kategori == 'Makanan' ? 'selected' : '' }}>Makanan
                </option>
                <option value="Minuman" {{ $edit->kategori == 'Minuman' ? 'selected' : '' }}>Minuman
                </option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Harga</label>
            <input id="editHargaInput" class="form-control" value="{{ $edit->hargaProduk }}" name="harga_modal"
                type="text">
        </div>
        <div class="form-group">
            <label for="place-bottom-right" class="form-label">Foto</label>
            <input type="file" name="foto_modal" class="dropify" data-height="200">
            <label class="form-label mt-5">Foto</label>
            <img src="{{ asset('' . $edit->fotoProduk) }}" style="width:100px;height:100">
        </div>
    </div>
    <div class="modal-footer">
        @include('template.component.button.button_modal')
    </div>
</form>

<script src="{{ url('/assets') }}/plugins/fileuploads/js/fileupload.js"></script>
<script src="{{ url('/assets') }}/plugins/fileuploads/js/file-upload.js"></script>
