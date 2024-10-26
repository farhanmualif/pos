<form action="{{ url('karyawan/profile/saya/update_gambar/' . $user['id']) }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label for="place-bottom-right" class="form-label">Foto</label>
            <input type="file" name="foto" class="dropify" data-height="200">
        </div>
    </div>
    <div class="modal-footer">
        @include('template.component.button.button_modal')
    </div>
</form>

<script src="{{ url('/assets') }}/plugins/fileuploads/js/fileupload.js"></script>
<script src="{{ url('/assets') }}/plugins/fileuploads/js/file-upload.js"></script>
