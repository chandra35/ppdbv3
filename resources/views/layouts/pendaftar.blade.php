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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a67d8;
            --secondary-color: #764ba2;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
        }
        
        .main-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        }
        
        .main-sidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .main-sidebar .nav-link:hover,
        .main-sidebar .nav-link.active {
            color: #fff !important;
            background-color: rgba(255,255,255,0.1) !important;
        }
        
        .main-sidebar .nav-icon {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.2) !important;
        }
        
        .brand-text {
            color: #fff !important;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(255,255,255,0.2) !important;
            color: #fff !important;
        }
        
        .content-wrapper {
            background-color: #f4f6f9;
        }
        
        .small-box {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .small-box.bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
        }
        
        .small-box.bg-gradient-success {
            background: linear-gradient(135deg, #38a169 0%, #48bb78 100%) !important;
        }
        
        .small-box.bg-gradient-warning {
            background: linear-gradient(135deg, #dd6b20 0%, #ed8936 100%) !important;
        }
        
        .small-box.bg-gradient-info {
            background: linear-gradient(135deg, #3182ce 0%, #4299e1 100%) !important;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border: none;
        }
        
        .card-header {
            border-bottom: 1px solid #eee;
            background: transparent;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #6b46a1 100%);
        }
        
        .progress {
            border-radius: 10px;
            height: 10px;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-verified {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-revision {
            background-color: #fecaca;
            color: #991b1b;
        }
        
        .status-diterima {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-ditolak {
            background-color: #fecaca;
            color: #991b1b;
        }
        
        .status-cadangan {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        
        .user-panel .info a {
            color: #fff !important;
        }
        
        .user-panel .image img {
            border: 2px solid rgba(255,255,255,0.5);
        }
        
        .nav-sidebar .nav-header {
            color: rgba(255,255,255,0.5) !important;
        }
        
        /* Quick Action Cards */
        .quick-action-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        .quick-action-card .icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
    </style>
    
    @yield('css')
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
                <span class="nav-link">
                    <i class="fas fa-graduation-cap text-primary mr-1"></i>
                    PPDB {{ config('app.name') }}
                </span>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('pendaftar.dashboard') }}" class="dropdown-item">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('pendaftar.logout') }}" class="dropdown-item text-danger"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('pendaftar.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('pendaftar.dashboard') }}" class="brand-link text-center">
            <span class="brand-text font-weight-bold">
                <i class="fas fa-graduation-cap"></i> PPDB Online
            </span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=667eea&color=fff" 
                         class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                    <small class="text-white-50">Calon Siswa</small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-header">MENU UTAMA</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.dashboard') }}" class="nav-link {{ request()->routeIs('pendaftar.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
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
                            <p>Upload Dokumen</p>
                        </a>
                    </li>
                    
                    <li class="nav-header">INFORMASI</li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.status') }}" class="nav-link {{ request()->routeIs('pendaftar.status') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-info-circle"></i>
                            <p>Status Pendaftaran</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('pendaftar.cetak-bukti') }}" class="nav-link {{ request()->routeIs('pendaftar.cetak-bukti') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-print"></i>
                            <p>Cetak Bukti</p>
                        </a>
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
                            <li class="breadcrumb-item"><a href="{{ route('pendaftar.dashboard') }}">Home</a></li>
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>&copy; {{ date('Y') }} PPDB {{ config('app.name') }}.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
</script>

@yield('js')
</body>
</html>
