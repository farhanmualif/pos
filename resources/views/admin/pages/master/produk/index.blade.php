@extends('index')
@section('title', 'Master Produk')
@section('content')
    <div class="page-header d-sm-flex d-block">
        <ol class="breadcrumb mb-sm-0 mb-3">
            <!-- breadcrumb -->
            <li class="breadcrumb-item"><a href="{{ url('/super_admin/dashboard') }}">{{ $breadcrumb }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb_active }}</li>
        </ol><!-- End breadcrumb -->
    </div>
    <div class="row">
        <div class="col-lg-4">
            @include('admin.pages.master.produk.create')
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @error('kategori_modal')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                @error('harga_modal')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                @error('foto_modal')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap border-bottom" id="responsive-datatable">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">No</th>
                                    <th class="wd-15p border-bottom-0">Nama Produk</th>
                                    <th class="wd-15p border-bottom-0">Kategori</th>
                                    <th class="wd-15p border-bottom-0">Harga</th>
                                    <th class="wd-15p border-bottom-0">Foto</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="text-center wd-10p border-bottom-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produk as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->namaProduk }}</td>
                                        <td>{{ $data->kategori }}</td>
                                        <td>Rp. {{ number_format($data->hargaProduk, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if (empty($data->fotoProduk))
                                            <strong>
                                                -
                                            </strong>
                                            @else
                                            <img src="{{ asset('' . $data->fotoProduk) }}" style="width:60px;height:60">
                                            @endif
                                        <td>
                                            @if ($data->status == '1')
                                                <form id="changeStatus{{ $data->id }}"
                                                    action="{{ url('/admin/master/produk/changeStatus/' . $data->id) }}"
                                                    style="display: inline;" method="POST">
                                                    @csrf
                                                    <button type="button"
                                                        class="btn btn-outline-success mt-1 mb-1 me-3 changeStatusBtn"
                                                        data-id="{{ $data->id }}">
                                                        <span>Aktif</span>
                                                    </button>
                                                </form>
                                            @elseif ($data->status == '0')
                                                <form id="changeStatus{{ $data->id }}"
                                                    action="{{ url('/admin/master/produk/changeStatus/' . $data->id) }}"
                                                    style="display: inline;" method="POST">
                                                    @csrf
                                                    <button type="button"
                                                        class="btn btn-outline-danger mt-1 mb-1 me-3 changeStatusBtn"
                                                        data-id="{{ $data->id }}">
                                                        <span>Nonaktif</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn br-7 btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal3" onclick="editModal('{{ $data['id'] }}')">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <form id="deleteForm{{ $data->id }}"
                                                action="{{ url('/admin/master/produk/' . $data->id) }}"
                                                style="display: inline;" method="POST">
                                                @method('DELETE')
                                                @csrf
                                                <button type="button" class="btn btn-danger deleteBtn"
                                                    data-id="{{ $data->id }}"><i class="ti ti-trash"></i></button>
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
    </div>

    {{-- start edit modal --}}
    <div class="modal fade" id="exampleModal3" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="example-Modal3">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div id="modal-content-edit">
                    {{-- isi dari content modal --}}
                </div>
            </div>
        </div>
    </div>
    {{-- end edit modal --}}
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            @foreach ($produk as $i)
                $('#exampleModal3{{ $i->id }}').on('shown.bs.modal', function() {
                    let hargaFormatted = formatRupiah($('#editHargaInput').val());
                    $('#editHargaInput').val(hargaFormatted);
                });

                $('#editHargaInput').on('input', function() {
                    let hargaInput = $(this).val();
                    let harga = hargaInput.replace(/\D/g, '');
                    let hargaFormatted = formatRupiah(harga);
                    $(this).val(hargaFormatted);
                });

                $('#exampleModal3{{ $i->id }} form').submit(function(event) {
                    let hargaInput = $('#editHargaInput').val();

                    let harga = hargaInput.replace(/\D/g, '');

                    $('#editHargaInput').val(harga);
                });
            @endforeach


            function formatRupiah(angka) {
                var reverse = angka.toString().split('').reverse().join(''),
                    ribuan = reverse.match(/\d{1,3}/g);
                ribuan = ribuan.join('.').split('').reverse().join('');
                return 'Rp.' + ribuan;
            }
            $('#hargaInput').on('input', function() {
                let harga = $(this).val();
                let hargaFormatted = formatRupiah(harga);
                $(this).val(hargaFormatted);
            });

            function formatRupiah(angka) {
                var number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{1,3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return 'Rp. ' + rupiah;
            }
            $('form').submit(function(event) {
                let hargaInput = $('#hargaInput').val();
                let harga = hargaInput.replace(/\D/g, '');
                $('#hargaInput').val(harga);
            });
        });

        function editModal(id) {
            $.ajax({
                url: '/admin/master/produk/' + id + '/edit',
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
        $('.changeStatusBtn').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var changeStatus = $('#changeStatus' + id);
            var statusText = $(this).text().trim() === 'Aktif' ? 'Data akan diubah menjadi Nonaktif!' :
                'Data akan diubah menjadi Aktif!';
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
