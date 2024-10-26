<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            @if (Auth::user()->akses == 1)
                <a class="header-brand1" href="{{ url('/super_admin/dashboard') }}">
                @elseif (Auth::user()->akses == 2)
                    <a class="header-brand1" href="{{ url('/admin/dashboard') }}">
                    @elseif (Auth::user()->akses == 3)
                        <a class="header-brand1" href="{{ url('/karyawan/dashboard') }}">
            @endif
            <img src="{{ url('/assets') }}/images/brand/logo.png" class="header-brand-img main-logo" alt="Sparic logo">
            <img src="{{ url('/assets') }}/images/brand/logo-light.png" class="header-brand-img darklogo"
                alt="Sparic logo">
            <img src="{{ url('/assets') }}/images/brand/icon.png" class="header-brand-img icon-logo" alt="Sparic logo">
            <img src="{{ url('/assets') }}/images/brand/icon2.png" class="header-brand-img icon-logo2"
                alt="Sparic logo">
            </a>
        </div>
        <!-- logo-->
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg></div>
            <ul class="side-menu">
                <li class="sub-category">
                    <h3>Main</h3>
                </li>
                @auth
                    @if (Auth::user()->akses == 1)
                        <li>
                            <a class="side-menu__item {{ Request::segment(2) == 'dashboard' ? 'active' : '' }}"
                                href="{{ url('/super_admin/dashboard') }}"><i class="side-menu__icon fa fa-home"></i><span
                                    class="side-menu__label">Dashboard</span></a>
                        </li>
                    @elseif(Auth::user()->akses == 2)
                        <li>
                            <a class="side-menu__item {{ Request::segment(2) == 'dashboard' ? 'active' : '' }}"
                                href="{{ url('/admin/dashboard') }}"><i class="side-menu__icon fa fa-home"></i><span
                                    class="side-menu__label">Dashboard</span></a>
                        </li>
                    @elseif(Auth::user()->akses == 3)
                        <li>
                            <a class="side-menu__item {{ Request::segment(2) == 'dashboard' ? 'active' : '' }}"
                                href="{{ url('/karyawan/dashboard') }}"><i class="side-menu__icon fa fa-home"></i><span
                                    class="side-menu__label">Dashboard</span></a>
                        </li>
                    @endif
                @endauth
                @can('super_admin')
                    <li class="sub-category">
                        <h3>Master Data</h3>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'produk' ? 'active' : '' }}"
                            href="{{ url('/super_admin/master/produk') }}"><i class="side-menu__icon fe fe-truck"></i><span
                                class="side-menu__label">Produk</span></a>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'mitra' ? 'active' : '' }}"
                            href="{{ url('/super_admin/master/mitra') }}"><i class="side-menu__icon fe fe-users"></i><span
                                class="side-menu__label">Akun Mitra</span></a>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'karyawan' ? 'active' : '' }}"
                            href="{{ url('/super_admin/master/karyawan') }}"><i
                                class="side-menu__icon fa fa-vcard-o"></i><span class="side-menu__label">Akun
                                Karyawan</span></a>
                    </li>
                    <li class="sub-category">
                        <h3>Transaksi</h3>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(2) == 'transaksi' ? 'active' : '' }}"
                            href="{{ url('/super_admin/transaksi') }}"><i
                                class="side-menu__icon fe fe-trending-up"></i><span class="side-menu__label">Transaksi
                            </span></a>
                    </li>
                @endcan
                @can('admin')
                    <li class="sub-category">
                        <h3>Master Data</h3>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'produk' ? 'active' : '' }}"
                            href="{{ url('/admin/master/produk') }}"><i class="side-menu__icon fe fe-truck"></i><span
                                class="side-menu__label">Produk</span></a>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'karyawan' ? 'active' : '' }}"
                            href="{{ url('/admin/master/karyawan') }}"><i class="side-menu__icon fe fe-users"></i><span
                                class="side-menu__label">Kelola Karyawan</span></a>
                    </li>
                    <li class="sub-category">
                        <h3>Transaksi</h3>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'stok_produk' ? 'active' : '' }}"
                            href="{{ url('/admin/transaksi/stok_produk') }}"><i
                                class="side-menu__icon fa fa-cube"></i><span class="side-menu__label">Stok
                                Produk</span></a>
                    </li>
                    <li class="sub-category">
                        <h3>Report</h3>
                    </li>
                    <li>
                        <a class="side-menu__item {{ Request::segment(4) == 'transaksi' ? 'active' : '' }}"
                            href="{{ url('/admin/transaksi/laporan/transaksi') }}"><i
                                class="side-menu__icon fe fe-file-text"></i><span class="side-menu__label">Laporan
                                Transaksi</span></a>
                    </li>
                @endcan
                @can('karyawan')
                    <li class="sub-category">
                        <h3>Penjualan</h3>
                    </li>
                    <li>
                        <a class="side-menu__item " href="{{ url('/') }}"><i
                                class="side-menu__icon fa fa-shopping-cart"></i><span class="side-menu__label">
                                Penjualan</span>
                        </a>
                    </li>
                @endcan
                <li class="sub-category">
                    <h3>Pengaturan</h3>
                </li>
                @if (Auth::user()->akses == 2)
                    <li>
                        <a class="side-menu__item {{ Request::segment(3) == 'mitra' ? 'active' : '' }}"
                            href="{{ url('/admin/pengaturan/mitra') }}"><i class="side-menu__icon fa fa-bank"></i><span
                                class="side-menu__label">
                                Pengaturan Mitra</span>
                        </a>
                    </li>
                @endif
                <li>
                    @if (Auth::user()->akses == 1)
                        <a class="side-menu__item {{ Request::segment(3) == 'user' ? 'active' : '' }}"
                            href="{{ url('/super_admin/profil/user') }}"><i
                                class="side-menu__icon fe fe-user"></i><span class="side-menu__label">Profile
                                Saya</span>
                        </a>
                    @elseif(Auth::user()->akses == 2)
                        <a class="side-menu__item {{ Request::segment(3) == 'user' ? 'active' : '' }}"
                            href="{{ url('/admin/profil_admin/user') }}"><i
                                class="side-menu__icon fe fe-user"></i><span class="side-menu__label">Profile
                                Saya</span>
                        </a>
                    @elseif (Auth::user()->akses == 3)
                        <a class="side-menu__item {{ Request::segment(3) == 'saya' ? 'active' : '' }}"
                            href="{{ url('/karyawan/profile/saya') }}"><i
                                class="side-menu__icon fe fe-user"></i><span class="side-menu__label">Profile
                                Saya</span>
                        </a>
                    @endif
                </li>
                <li>
                    <a class="side-menu__item" href="#" id="logoutBtn"><i
                            class="side-menu__icon fe fe-log-out"></i><span class="side-menu__label">Logout</span></a>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg></div>
        </div>
    </div>
</div>
