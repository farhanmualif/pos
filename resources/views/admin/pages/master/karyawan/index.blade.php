@extends('index')
@section('title', 'Karyawan')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/super_admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
        <div class="ms-auto">
            <div>
                <button class="btn bg-primary-transparent" data-bs-toggle="modal" data-bs-target="#Vertically"
                    data-bs-tooltip="tooltip" title="Add New User" data-bs-placement="bottom">
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
                                <th class="wd-15p border-bottom-0">No Telepon</th>
                                <th class="wd-15p border-bottom-0">Mitra</th>
                                <th class="wd-15p border-bottom-0">Alamat</th>
                                <th class="wd-15p border-bottom-0">Status</th>
                                <th class="text-center wd-10p border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($karyawan as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex contact-image">
                                            @if (!empty($data['user']['foto']))
                                                <img src="{{ asset('' . $data['user']['foto']) }}"
                                                    class="avatar avatar-md brround" alt="person-image">
                                            @else
                                                <img src="{{ url('/assets') }}/images/users/male/profile.png"
                                                    class="avatar avatar-md brround" alt="person-image">
                                            @endif
                                            <div class="d-flex mt-1 flex-column ms-2">
                                                <h6 class="mb-0 fs-14 fw-semibold text-dark">
                                                    {{ $data['user']['nama'] }}</h6>
                                                <span class="fs-12 text-muted">{{ $data['user']['email'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $data['nomorHpAktif'] }}</td>
                                    <td>{{ $data['mitra']['namaMitra'] }}</td>
                                    <td>{{ $data['alamat'] }}</td>
                                    <td>
                                        @if ($data['user']['active'] == '1')
                                            <form
                                                action="{{ url('/admin/master/karyawan/changeStatus/' . $data['userId']) }}"
                                                style="display: inline;" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success mt-1 mb-1 me-3 ">
                                                    <span>Aktif</span>
                                                </button>
                                            </form>
                                        @elseif ($data['user']['active'] == '0')
                                            <form
                                                action="{{ url('/admin/master/karyawan/changeStatus/' . $data['userId']) }}"
                                                style="display: inline;" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger mt-1 mb-1 me-3 ">
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
                                        <button type="button" class="btn br-7 btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#editPasswordModalAdmin"
                                            onclick="editPasswordModalAdmin('{{ $data['id'] }}')">
                                            <i class="fa fa-lock"></i>
                                        </button>
                                        <form id="deleteForm{{ $data['id'] }}"
                                            action="{{ url('/admin/master/karyawan/' . $data['id']) }}"
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
    @include('admin.pages.master.karyawan.create')
    {{-- end modal tambah karyawan --}}

    {{-- modal edit karyawan --}}
    <div class="modal fade" id="VerticallyEdit">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
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

    {{-- edit Password Karyawan --}}
    <div class="modal fade" id="editPasswordModalAdmin">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Password Karyawan</h6><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal" type="button"></button>
                </div>
                <div id="modal-content-edit-passwordKaryawan">

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function editModal(id) {
            $.ajax({
                url: '/admin/master/karyawan/' + id + '/edit',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }

        function editPasswordModalAdmin(id) {
            $.ajax({
                url: '/admin/master/karyawan/' + id + '/editPassword',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    $("#modal-content-edit-passwordKaryawan").html(response)
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }
        $('.changeStatusBtn').on('click', function(e) {
            e.preventDefault();
            var userId = $(this).data('id'); // Mengambil userId dari atribut data-id
            var changeStatus = $('#changeStatus' + userId); // Menggunakan userId untuk mencari formulir
            var statusText = $(this).find('span').text().trim() === 'Aktif' ? 'Data akan diubah menjadi Nonaktif!' :
                'Data akan diubah menjadi Aktif!';


            console.log(userId);
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
    </script>
@endsection
