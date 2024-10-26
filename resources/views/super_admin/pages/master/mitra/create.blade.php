<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-header">
                <h6 class="modal-title">{{ $titleCreate }}</h6><button aria-label="Close" class="btn-close"
                    data-bs-dismiss="modal" type="button"></button>
            </div>
            <form action="{{ url('super_admin/master/mitra') }}" enctype="multipart/form-data" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="d-flex flex-column">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nama Pemilik Mitra</label>
                                    <input class="form-control" placeholder="Masukan Nama Pemilik Mitra"
                                        name="namaPemilikMitra" type="text" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Pemilik Mitra</label>
                                    <input class="form-control" placeholder="Masukan Email Mitra"
                                        name="emailPemilikMitra" type="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="place-bottom-right" class="form-label">Foto Pemilik Mitra</label>
                            <input type="file" name="fotoPemilikMitra" class="dropify" data-height="200">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nama Mitra</label>
                                    <input class="form-control" placeholder="Masukan Nama Mita" name="namaMitra"
                                        type="text" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">No.Hp Mitra</label>
                                    <input class="form-control" placeholder="Masukan Nomor Hp Mitra" name="noTelpMitra"
                                        type="number" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="place-bottom-right" class="form-label">Foto Mita</label>
                            <input type="file" name="fotoMitra" class="dropify" data-height="200">
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
