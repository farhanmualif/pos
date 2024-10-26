<form action="{{ url('admin/master/karyawan/' . $edit['id']) }}" enctype="multipart/form-data" method="POST">
    <div class="modal-body">
        @method('PUT')
        @csrf
        <div class="d-flex flex-column">
            <div class="form-group">
                <label class="form-label">Nama Karyawan</label>
                <input class="form-control" value="{{ $edit['user']['nama'] }}" name="namaKaryawan" type="text"
                    required>
                @error('namaKaryawan')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="form-control" value="{{ $edit['user']['email'] }}" name="emailKaryawan" type="email"
                    required>
                @error('emailKaryawan')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">No Telepon Aktif</label>
                <input class="form-control" value="{{ $edit['nomorHpAktif'] }}" name="noTelpKaryawan" type="number"
                    required>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <input class="form-control" value="{{ $edit['alamat'] }}" name="alamatKaryawan" type="text" required>
            </div>
            <div class="form-group">
                <label for="place-bottom-right" class="form-label">Foto Karyawan</label>
                <input type="file" name="fotoKaryawan" class="dropify" data-height="200">
                <label class="form-label mt-5">Foto</label>
                <img src="{{ asset('' . $edit['user']['foto']) }}" style="width:100px;height:100">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        @include('template.component.button.button_modal')
    </div>
</form>

<script src="{{ url('/assets') }}/plugins/fileuploads/js/fileupload.js"></script>
<script src="{{ url('/assets') }}/plugins/fileuploads/js/file-upload.js"></script>
