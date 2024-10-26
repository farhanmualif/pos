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
                                @if (!$user->foto)
                                    <img src="{{ url('/assets') }}/images/users/male/profile.png"
                                        style="height: 300px; width: auto;" alt="profile-user">
                                @else
                                    <img src="{{ asset('' . $user->foto) }}"
                                        style="height: 300px; width: auto; border-radius: 50%" alt="profile-user">
                                @endif
                                <div class="form-group mt-5">
                                    <h1>{{ $user->nama }}</h1>
                                </div>
                                <div class="form-group">
                                    @if ($user->akses == 1)
                                        <span class="badge bg-success">Super Admin</span>
                                    @elseif ($user->akses == 2)
                                        <span class="badge bg-warning">Pelanggan</span>
                                    @endif
                                </div>
                                @if ($user->akses == 1)
                                    <button class="btn btn-primary btn-block" data-bs-toggle="modal"
                                        data-bs-target="#Vertically" onclick="editModal('{{ $user['id'] }}')">
                                        <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Password
                                    </button>
                                    <button class="btn btn-success btn-block" data-bs-toggle="modal"
                                        data-bs-target="#VerticallyEdit" onclick="editModalGambar('{{ $user['id'] }}')">
                                        <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Gambar
                                    </button>
                                @elseif ($user->akses == 2)
                                    <button class="btn btn-primary btn-block" data-bs-toggle="modal"
                                        data-bs-target="#VerticallyAdmin" onclick="editModalAdmin('{{ $user['id'] }}')">
                                        <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Password
                                    </button>
                                    <button class="btn btn-success btn-block" data-bs-toggle="modal"
                                        data-bs-target="#VerticallyEditAdmin"
                                        onclick="editModalGambarAdmin('{{ $user['id'] }}')">
                                        <span class="mr-2"><i class="fa fa-edit"></i></span>Edit Gambar
                                    </button>
                                @endif
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
                    @if ($user->akses == 1)
                        <form action="{{ url('/super_admin/profil/user/' . $user->id) }}" method="POST"
                            enctype="multipart/form-data">
                        @elseif ($user->akses == 2)
                            <form action="{{ url('/admin/profil_admin/user/' . $user->id) }}" method="POST"
                                enctype="multipart/form-data">
                    @endif
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="place-bottom-right" class="form-label">Nama</label>
                                <input class="form-control" value="{{ $user->nama }}" placeholder="Masukan Nama Bahan"
                                    name="nama" type="text">
                            </div>
                            <div class="form-group">
                                <label for="place-bottom-right" class="form-label">Email</label>
                                <input class="form-control" value="{{ $user->email }}" placeholder="Masukan Nama Bahan"
                                    name="email" type="email">
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

    @if ($user->akses == 1)
        <div class="modal fade" id="Vertically">
        @elseif ($user->akses == 2)
            <div class="modal fade" id="VerticallyAdmin">
    @endif
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Edit Password</h6>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
            </div>
            @if ($user->akses == 1)
                <div id="modal-content-edit-password">
                @elseif ($user->akses == 2)
                    <div id="modal-content-edit-passwordAdmin">
            @endif

        </div>
    </div>
    </div>
    </div>


    @if ($user->akses == 1)
        <div class="modal fade" id="VerticallyEdit">
        @elseif ($user->akses == 2)
            <div class="modal fade" id="VerticallyEditAdmin">
    @endif
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Edit Gambar</h6>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
            </div>
            @if ($user->akses == 1)
                <div id="modal-content-edit-gambar">
                @elseif ($user->akses == 2)
                    <div id="modal-content-edit-gambarAdmin">
            @endif


        </div>
    </div>
    </div>
    </div>

@endsection

@section('script')
    <script>
        function editModal(id) {
            $.ajax({
                url: '/super_admin/profil/user/edit_password/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-password").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function editModalGambar(id) {
            $.ajax({
                url: '/super_admin/profil/user/edit_gambar/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-gambar").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function editModalAdmin(id) {
            $.ajax({
                url: '/admin/profil_admin/user/edit_password/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-passwordAdmin").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function editModalGambarAdmin(id) {
            $.ajax({
                url: '/admin/profil_admin/user/edit_gambar/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-gambarAdmin").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
    </script>
@endsection
