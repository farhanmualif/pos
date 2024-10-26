@extends('index')
@section('title', 'Edit Profile')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/super_admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <center>
                                @if (!$user['user']['foto'])
                                    <img src="{{ url('/assets') }}/images/users/male/profile.png"
                                        style="height: 300px; width: auto;" alt="profile-user">
                                @else
                                    <img src="{{ asset('' . $user['user']['foto']) }}"
                                        style="height: 300px; width: auto; border-radius: 50%" alt="profile-user">
                                @endif
                                <div class="form-group mt-5">
                                    <h1>{{ $user['user']['nama'] }}</h1>
                                </div>
                                <div class="form-group">
                                    <h3><span class="badge bg-success">Karyawan</span></h3>
                                </div>
                                <div class="form-group">
                                    <span class="badge bg-info">{{ $user['mitra']['namaMitra'] }}</span>
                                </div>
                                <button class="btn btn-primary btn-block" data-bs-toggle="modal"
                                    data-bs-target="#VerticallyKaryawan"
                                    onclick="editPasswordKaryawan('{{ $user['id'] }}')">
                                    <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Password
                                </button>
                                <button class="btn btn-success btn-block" data-bs-toggle="modal"
                                    data-bs-target="#VerticallyGambarKaryawawn"
                                    onclick="editModalGambarKaryawan('{{ $user['id'] }}')">
                                    <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Gambar
                                </button>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ url('/karyawan/profile/saya/update/' . $user['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place-bottom-right" class="form-label">Nama</label>
                                    <input class="form-control" value="{{ $user['user']['nama'] }}"
                                        placeholder="Masukan Nama" name="nama" type="text">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place-bottom-right" class="form-label">Email</label>
                                    <input class="form-control" value="{{ $user['user']['email'] }}"
                                        placeholder="Masukan Email " name="email" type="email">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place-bottom-right" class="form-label">Nomor Hp</label>
                                    <input class="form-control" value="{{ $user['nomorHpAktif'] }}"
                                        placeholder="Masukan Nomor Hp" name="no_telp" type="number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place-bottom-right" class="form-label">Alamat</label>
                                    <input class="form-control" value="{{ $user['alamat'] }}" placeholder="Masukan Alamat"
                                        name="alamat" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary"><span
                                        class="fa fa-save ml-5"></span>Simpan</button>
                                <button type="reset" class="btn btn-danger"><span class="fa fa-times mr-5"></span>
                                    Batal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="VerticallyKaryawan">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Password</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div id="modal-content-edit-password-karyawan">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="VerticallyGambarKaryawawn">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Gambar</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div id="modal-content-edit-gambar-karyawan">


                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function editPasswordKaryawan(id) {
            $.ajax({
                url: '/karyawan/profile/saya/update_password/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-password-karyawan").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function editModalGambarKaryawan(id) {
            $.ajax({
                url: '/karyawan/profile/saya/update_gambar/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-gambar-karyawan").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
    </script>
@endsection
