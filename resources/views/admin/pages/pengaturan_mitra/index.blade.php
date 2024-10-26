@extends('index')
@section('title', 'Pengaturan Mitra')
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
                                @if ($mitra['fotoMitra'] == null)
                                    <img src="{{ url('/assets/images/users/male/mitra.png') }}"
                                        style="height: 300px; width: auto; border-radius: 50%" alt="profile-user">
                                @else
                                    <img src="{{ asset($mitra['fotoMitra']) }}"
                                        style="height: 300px; width: auto; border-radius: 50%;" alt="profile-user">
                                @endif
                                <div class="form-group mt-5">
                                    <h1>{{ $mitra['namaMitra'] }}</h1>
                                </div>
                                <div class="form-group">
                                    @if ($mitra['statusMitra'] == '1')
                                        <h3>
                                            <span class="badge bg-success">Active</span>
                                        </h3>
                                    @elseif ($mitra['statusMitra'] == '2')
                                        <h3>
                                            <span class="badge bg-danger">Non Active</span>
                                        </h3>
                                    @endif
                                </div>
                                <button class="btn btn-primary btn-block mt-5" data-bs-toggle="modal"
                                    data-bs-target="#VerticallyGambarMitra"
                                    onclick="editModalGambarMitra('{{ $mitra['id'] }}')">
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
                    <form action="{{ url('/admin/pengaturan/mitra/' . $mitra['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="form-group">
                                <label for="place-bottom-right" class="form-label">Nama Mitra</label>
                                <input class="form-control" value="{{ $mitra['namaMitra'] }}" placeholder="Masukan Nama"
                                    name="nama" type="text">
                            </div>
                            <div class="form-group">
                                <label for="place-bottom-right" class="form-label">Nomor Hp Mitra</label>
                                <input class="form-control" value="{{ $mitra['nomorHp'] }}" placeholder="Masukan Email "
                                    name="no_telp" type="number">
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

    <div class="modal fade" id="VerticallyGambarMitra">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Gambar</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div id="modal-content-edit-gambar-mitra">


                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function editModalGambarMitra(id) {
            $.ajax({
                url: '/admin/pengaturan/mitra/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-gambar-mitra").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
    </script>
@endsection
