<div class="modal fade" id="Vertically">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{ $titleCreate }}</h6><button aria-label="Close" class="btn-close"
                    data-bs-dismiss="modal" type="button"></button>
            </div>
            <form action="{{ url('admin/master/karyawan') }}" enctype="multipart/form-data" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="d-flex flex-column">
                        <div class="form-group">
                            <label class="form-label">Nama Karyawan</label>
                            <input class="form-control" placeholder="Masukan Nama Karyawan" name="namaKaryawan"
                                type="text" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input class="form-control" placeholder="Masukan Email Karyawan" name="emailKaryawan"
                                type="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input class="form-control" placeholder="Masukan Password Karyawan" name="password"
                                type="password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No Telepon Aktif</label>
                            <input class="form-control" placeholder="Masukan Nomor Karyawan" name="noTelpKaryawan"
                                type="number" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <input class="form-control" placeholder="Masukan Alamat Karyawan" name="alamatKaryawan"
                                type="text" required>
                        </div>
                        <div class="form-group">
                            <label for="place-bottom-right" class="form-label">Foto Karyawan</label>
                            <input type="file" name="fotoKaryawan" class="dropify" data-height="200">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    @include('template.component.button.button_modal')
                </div>
            </form>
        </div>
    </div>
</div>
