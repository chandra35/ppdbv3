<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - PPDB {{ config('app.name') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    @stack('styles')
    
    <style>
        .content-wrapper {
            background: #f4f6f9;
        }
        .brand-link {
            border-bottom: 1px solid #4b545c;
        }
        .nav-sidebar .nav-link.active {
            background-color: #007bff !important;
            color: white !important;
        }
        .progress-card {
            transition: transform 0.2s;
        }
        .progress-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('pendaftar.dashboard') }}" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    <span class="d-none d-md-inline ml-1">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="{{ route('pendaftar.profile') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profil
                    </a>
                    <a href="{{ route('pendaftar.password') }}" class="dropdown-item">
                        <i class="fas fa-key mr-2"></i> Ubah Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('pendaftar.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('pendaftar.dashboard') }}" class="brand-link">
            <img src="https://via.placeholder.com/33x33/007bff/ffffff?text=P" alt="Logo" class="brand-image img-circle elevation-3">
            <span class="brand-text font-weight-light">PPDB Dashboard</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=007bff&color=fff" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('pendaftar.profile') }}" class="d-block">{{ Str::limit(Auth::user()->name, 20) }}</a>
                    @php
                        $calonSiswa = Auth::user()->calonSiswa;
                    @endphp
                    @if($calonSiswa)
                        <small class="text-muted">{{ $calonSiswa->nomor_registrasi }}</small>
                    @endif
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.dashboard') }}" class="nav-link {{ request()->routeIs('pendaftar.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Data Pendaftaran -->
                    <li class="nav-header">DATA PENDAFTARAN</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.data-pribadi') }}" class="nav-link {{ request()->routeIs('pendaftar.data-pribadi') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Data Pribadi</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('pendaftar.data-ortu') }}" class="nav-link {{ request()->routeIs('pendaftar.data-ortu') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data Orang Tua</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('pendaftar.dokumen') }}" class="nav-link {{ request()->routeIs('pendaftar.dokumen') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-upload"></i>
                            <p>Dokumen</p>
                        </a>
                    </li>

                    <!-- Cetak -->
                    @if($calonSiswa && $calonSiswa->is_finalisasi)
                    <li class="nav-header">CETAK</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.cetak-bukti-registrasi') }}" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-file-pdf"></i>
                            <p>Bukti Registrasi</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.cetak-kartu-ujian') }}" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-id-card"></i>
                            <p>Kartu Ujian</p>
                        </a>
                    </li>
                    @endif

                    <!-- Other -->
                    <li class="nav-header">PENGATURAN</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.profile') }}" class="nav-link {{ request()->routeIs('pendaftar.profile') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-circle"></i>
                            <p>Profil Saya</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('pendaftar.password') }}" class="nav-link {{ request()->routeIs('pendaftar.password') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Ubah Password</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                            <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                            <p>Logout</p>
                        </a>
                        <form id="logout-form-sidebar" action="{{ route('pendaftar.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ config('app.name') }}</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>PPDB</b> v1.0
        </div>
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // Toastr Config
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Show session messages
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
        toastr.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
        toastr.info("{{ session('info') }}");
    @endif
</script>

@stack('scripts')

</body>
</html>
