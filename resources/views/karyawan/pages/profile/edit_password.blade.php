<form action="{{ url('/karyawan/profile/saya/update_password/' . $user['id']) }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="form-group">
            <label for="password">Password Lama</label>
            <input type="password" class="form-control" id="password" placeholder="Masukkan Password Lama">
        </div>
        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan Password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" name="konfirmasi_password" class="form-control" id="confirm_password"
                placeholder="Konfirmasi Password">
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn ripple btn-primary" type="submit">Simpan Perubahan</button>
        <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="reset">Batal</button>
    </div>
</form>
