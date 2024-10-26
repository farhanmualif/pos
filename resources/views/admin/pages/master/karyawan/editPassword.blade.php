<form action="{{ url('admin/master/karyawan/updatePassword/' . $edit['id']) }}" enctype="multipart/form-data"
    method="POST">
    <div class="modal-body">
        @method('PUT')
        @csrf
        <div class="d-flex flex-column">
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input class="form-control" name="password" type="password" required>
                @error('password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input class="form-control" name="password_confirmation" type="password" required>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        @include('template.component.button.button_modal')
    </div>
</form>
