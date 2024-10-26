@extends('auth.index_login')
@section('title', 'Login Page')
@section('content')
    <div class="page-content">
        <div class="container text-center text-dark">
            <div class="row">
                <div class="col-lg-4 d-block mx-auto">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="text-center mb-2">
                                        <a class="header-brand1" href="{{ url('/login') }}">
                                            <img src="{{ url('/assets') }}/images/brand/logo.png"
                                                class="header-brand-img main-logo" alt="Sparic logo">
                                            <img src="{{ url('/assets') }}/images/brand/logo-light.png"
                                                class="header-brand-img darklogo" alt="Sparic logo">
                                        </a>
                                    </div>
                                    <h3>Login</h3>
                                    <p class="text-muted">Masuk Ke Akun Anda</p>
                                    <form class='mb-3' action="{{ route('login.proccess') }}" method="POST">
                                        @csrf
                                        <div class="input-group mb-3">
                                            <span class="input-group-addon bg-white"><i
                                                    class="fa fa-user text-dark"></i></span>
                                            <input type="text" class="form-control" placeholder="Email" name="email"
                                                id="email" value="{{ old('email') }}">
                                        </div>
                                        <div class="input-group mb-4">
                                            <span class="input-group-addon bg-white"><i
                                                    class="fa fa-unlock-alt text-dark"></i></span>
                                            <input type="password" class="form-control" placeholder="Password"
                                                name="password" id="password">
                                        </div>
                                        <div class="row">
                                            <div>
                                                <button type="submit" class="btn btn-primary d-grid w-100">Log in</button>
                                            </div>
                                            <div class="col-12 mt-4">
                                                <p>Ingin Menjadi Mitra Kami?</p>
                                                <a href="{{ url('/register') }}"
                                                    class="btn btn-link box-shadow-0 px-0">Daftar
                                                    Sebagai Mitra</a>
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
