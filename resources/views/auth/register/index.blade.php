@extends('auth.index_login')
@section('content')
    <div class="page-content">
        <div class="container text-center text-dark">
            <div class="row">
                <div class="col-lg-8     d-block mx-auto">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="text-center mb-6">
                                        <a class="header-brand1" href="index.html">
                                            <img src="{{ url('/assets') }}/images/brand/logo1.png"
                                                class="header-brand-img main-logo" alt="Sparic logo"
                                                style="width: 300px; height: auto;">
                                            <img src="{{ url('/assets') }}/images/brand/logo1.png"
                                                class="header-brand-img darklogo" alt="Sparic logo"
                                                style="width: 300px; height: auto;">
                                        </a>
                                    </div>
                                    <h3>Daftar</h3>
                                    <form action="{{ url('/register/proccess') }}" method="POST">
                                        @csrf
                                        <p class="text-muted">Daftar Akun Anda</p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-addon bg-white"><i
                                                            class="fa fa-user w-4 text-muted-dark"></i></span>
                                                    <input type="text" name="nama" class="form-control"
                                                        placeholder="Masukkan Nama">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-addon bg-white"><i
                                                            class="fa fa-envelope  text-muted-dark w-4"></i></span>
                                                    <input type="email" name="email" class="form-control"
                                                        placeholder="Masukkan Email">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-addon bg-white"><i
                                                            class="fa fa-bank  w-4 text-muted-dark"></i></span>
                                                    <input type="text" name="mitra" class="form-control"
                                                        placeholder="Masukkan Nama Mitra">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-addon bg-white"><i
                                                            class="fa fa-phone  text-muted-dark w-4"></i></span>
                                                    <input type="number" name="no_telp" class="form-control"
                                                        placeholder="Masukkan Nomor Hp">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-block px-4">Create a
                                                    new account</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
