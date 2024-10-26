<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="Sparic– Creative Admin Multipurpose Responsive Bootstrap5 Dashboard HTML Template" name="description">
    <meta content="Spruko Technologies Private Limited" name="author">
    <meta name="keywords"
        content="html admin template, bootstrap admin template premium, premium responsive admin template, admin dashboard template bootstrap, bootstrap simple admin template premium, web admin template, bootstrap admin template, premium admin template html5, best bootstrap admin template, premium admin panel template, admin template">

    <!-- Favicon -->
    <link rel="icon" href="{{ url('/assets') }}/images/brand/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('/assets') }}/images/brand/favicon.ico">

    <!-- Title -->
    <title>{{ config('app.name') }} | @yield('title')</title>

    <!--Bootstrap.min css-->
    <link id="style" rel="stylesheet" href="{{ url('/assets') }}/plugins/bootstrap/css/bootstrap.min.css">

    <!-- Style css -->
    <link href="{{ url('/assets') }}/css/style.css" rel="stylesheet">

    <!-- Plugin css -->
    <link href="{{ url('/assets') }}/css/plugins.css" rel="stylesheet">

    <!-- Animate css -->
    <link href="{{ url('/assets') }}/css/animated.css" rel="stylesheet">

    <!---Icons css-->
    <link href="{{ url('/assets') }}/css/icons.css" rel="stylesheet">

    <!-- Switcher css -->
    <link href="{{ url('/assets') }}/switcher/css/switcher.css" rel="stylesheet" id="switcher-css" type="text/css"
        media="all">
    <link href="{{ url('/assets') }}/switcher/demo.css" rel="stylesheet">


</head>

<body class="bg-account error-loging-img">

    <!--Global-Loader-->
    <div id="global-loader">
        <img src="{{ url('/assets') }}/images/loader.svg" alt="loader">
    </div>

    <!-- page -->
    <div class="page h-100">

        <!-- page-content -->
        <div class="page-content">
            <div class="container text-center text-dark">
                <div class="display-1  text-dark">@yield('code')</div>
                <p class="h5 fw-normal mb-6 leading-normal">@yield('message')</p>
                @auth
                    @if (Auth::user()->akses == 1)
                        <a class="btn btn-primary" href="{{ url('/super_admin/dashboard') }}">
                            Back To Home
                        </a>
                    @elseif (Auth::user()->akses == 2)
                        <a class="btn btn-primary" href="{{ url('/pelanggan/dashboard') }}">
                            Back To Home
                        </a>
                    @endif
                @endauth
            </div>
        </div>
        <!-- page-content end -->
    </div>
    <!-- page End-->

    <!-- Jquery js-->
    <script src="{{ url('/assets') }}/js/vendors/jquery.min.js"></script>

    <!--Bootstrap.min js-->
    <script src="{{ url('/assets') }}/plugins/bootstrap/popper.min.js"></script>
    <script src="{{ url('/assets') }}/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!--Moment js-->
    <script src="{{ url('/assets') }}/plugins/moment/moment.min.js"></script>

    <!-- Color Theme js -->
    <script src="{{ url('/assets') }}/js/themeColors.js"></script>

    <!-- Sticky js -->
    <script src="{{ url('/assets') }}/js/sticky.js"></script>

    <!-- Custom js-->
    <script src="{{ url('/assets') }}/js/custom.js"></script>

</body>

</html>
