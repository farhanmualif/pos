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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap border-bottom" id="responsive-datatable">
                            <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">No</th>
                                    <th class="wd-15p border-bottom-0">Kategori</th>
                                    <th class="wd-15p border-bottom-0">Harga</th>
                                    <th class="wd-15p border-bottom-0">Mitra</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produk as $data)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $data->kategori }}</td>
                                        <td>Rp. {{ number_format($data->hargaProduk, 0, ',', '.') }}</td>
                                        <td>{{ $data->mitra->namaMitra }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            @foreach ($produk as $item)
                $('#exampleModal3{{ $item->id }}').on('shown.bs.modal', function() {
                    let hargaFormatted = formatRupiah($('#editHargaInput').val());
                    $('#editHargaInput').val(hargaFormatted);
                });

                $('#editHargaInput').on('input', function() {
                    let hargaInput = $(this).val();
                    let harga = hargaInput.replace(/\D/g, '');
                    let hargaFormatted = formatRupiah(harga);
                    $(this).val(hargaFormatted);
                });

                $('#exampleModal3{{ $item->id }} form').submit(function(event) {
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
                url: '/super_admin/master/produk/' + id + '/edit',
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
