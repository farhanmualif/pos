@extends('index')
@section('title', 'Mitra')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/super_admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
        <div class="ms-auto">
            <div>
                <button class="btn bg-primary-transparent" data-bs-toggle="modal" data-bs-target="#editUser">
                    <span>
                        <i class="fa fa-plus"></i>
                    </span>
                    {{ $button_create }}
                </button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped text-nowrap border-bottom" id="responsive-datatable">
                        <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">No</th>
                                <th class="wd-15p border-bottom-0">Nama</th>
                                <th class="wd-15p border-bottom-0">Nama Mitra</th>
                                <th class="wd-15p border-bottom-0">No Telepon</th>
                                <th class="wd-15p border-bottom-0">validasi mitra</th>
                                <th class="wd-15p border-bottom-0">status</th>
                                <th class="text-center wd-10p border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mitra as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex contact-image">
                                            @if ($data['fotoMitra'] == null)
                                                <img src="{{ url('/assets') }}/images/users/male/null.jpg"
                                                    class="avatar avatar-md brround" alt="person-image">
                                            @else
                                                <img src="{{ asset($data['fotoMitra']) }}" class="avatar avatar-md brround"
                                                    alt="person-image">
                                            @endif
                                            <div class="d-flex mt-1 flex-column ms-2">
                                                <h6 class="mb-0 fs-14 fw-semibold text-dark">
                                                    {{ $data['user']['nama'] }}</h6>
                                                <span class="fs-12 text-muted">{{ $data['user']['email'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $data['namaMitra'] }}</td>
                                    <td>{{ $data['nomorHp'] }}</td>
                                    <td>{{ $data['validasiMitraId'] }}</td>
                                    <td>
                                        @if ($data['statusMitra'] == '1')
                                            <form id="changeStatus{{ $data->id }}"
                                                action="{{ url('/super_admin/master/mitra/changeStatus/' . $data->id) }}"
                                                style="display: inline;" method="POST">
                                                @csrf
                                                <button type="button"
                                                    class="btn btn-outline-success mt-1 mb-1 me-3 changeStatusBtn"
                                                    data-id="{{ $data->id }}">
                                                    <span>Aktif</span>
                                                </button>
                                            </form>
                                        @elseif ($data['statusMitra'] == '0')
                                            <form id="changeStatus{{ $data['id'] }}"
                                                action="{{ url('/super_admin/master/mitra/changeStatus/' . $data['id']) }}"
                                                style="display: inline;" method="POST">
                                                @csrf
                                                <button type="button"
                                                    class="btn btn-outline-danger mt-1 mb-1 me-3 changeStatusBtn"
                                                    data-id="{{ $data['id'] }}">
                                                    <span>Nonaktif</span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn br-7 btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#VerticallyEdit" onclick="editModal('{{ $data['id'] }}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form id="deleteForm{{ $data['id'] }}"
                                            action="{{ url('/super_admin/master/mitra/' . $data['id']) }}"
                                            style="display: inline;" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button type="button" class="btn btn-danger deleteBtn"
                                                data-id="{{ $data['id'] }}"><i class="ti ti-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- modal tambah karyawan --}}
    @include('super_admin.pages.master.mitra.create')
    {{-- end modal tambah karyawan --}}

    {{-- modal edit karyawan --}}
    <div class="modal fade" id="VerticallyEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user" role="document">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-header">
                    <h6 class="modal-title">{{ $titleCreate }}</h6><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal" type="button"></button>
                </div>
                <div id="modal-content-edit">

                </div>
            </div>
        </div>
    </div>

    {{-- end modal edit karyawan --}}
@endsection

@section('script')
    <script>
        function editModal(id) {
            $.ajax({
                url: '/super_admin/master/mitra/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit").html(response)
                    console.log('data : ' + response);
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
        $('.deleteBtn').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var deleteForm = $('#deleteForm' + id);
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteForm.submit();
                }
            });
        });
        $('.changeStatusBtn').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var changeStatus = $('#changeStatus' + id);
            var statusText = $(this).text().trim() === 'Aktif' ? 'Status Mitra akan diubah menjadi Nonaktif!' :
                'Status Mitra akan diubah menjadi Aktif!';
            Swal.fire({
                title: 'Anda yakin?',
                text: statusText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    changeStatus.submit();
                }
            });
        });
    </script>
@endsection
