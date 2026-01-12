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
                        <a href="#" class="nav-link" data-toggle="modal" data-target="#kartuUjianModal">
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

{{-- Modal Preview Kartu Ujian --}}
@if(isset($calonSiswa) && $calonSiswa && $calonSiswa->is_finalisasi)
@php
    $sekolahSettings = \App\Models\SekolahSettings::first();
    $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
    $fotoUrl = $fotoDokumen ? asset('storage/' . $fotoDokumen->file_path) : null;
    $password = $calonSiswa->user->plain_password ?? '********';
@endphp
<div class="modal fade" id="kartuUjianModal" tabindex="-1" role="dialog" aria-labelledby="kartuUjianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="kartuUjianModalLabel">
                    <i class="fas fa-id-card mr-2"></i>Preview Kartu Ujian
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="background: #f5f5f5; padding: 20px;">
                <div id="kartuUjianContent">
                    <div class="card" style="width: 340px; height: 220px; margin: 0 auto; background: #fff; border: 1px solid #999; border-radius: 8px; overflow: hidden; position: relative;">
                        {{-- Watermark --}}
                        @if($sekolahSettings && $sekolahSettings->logo)
                        <div class="watermark" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100px; height: 100px; opacity: 0.12;">
                            <img src="{{ asset('storage/' . $sekolahSettings->logo) }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Logo">
                        </div>
                        @endif
                        
                        {{-- Header --}}
                        <div class="card-header" style="border-bottom: 1px solid #ccc; padding: 8px 12px; background: #fff;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td class="school-name" style="color: #333; font-size: 11px; font-weight: bold; text-transform: uppercase;">{{ Str::limit($sekolahSettings->nama_sekolah ?? config('app.name'), 30) }}</td>
                                    <td style="text-align: right;"><span class="card-type" style="color: #666; font-size: 9px; border: 1px solid #999; padding: 2px 6px; border-radius: 3px;">KARTU TES PPDB</span></td>
                                </tr>
                            </table>
                        </div>
                        
                        {{-- Body --}}
                        <div class="card-body" style="padding: 10px 12px;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td class="photo-cell" style="width: 80px; vertical-align: top; padding-right: 10px;">
                                        <div class="photo-box" style="width: 75px; height: 100px; border: 1px solid #999; border-radius: 4px; overflow: hidden; background: #fff;">
                                            @if($fotoUrl)
                                                <img src="{{ $fotoUrl }}" style="width: 75px; height: 100px; object-fit: cover;" alt="Foto">
                                            @else
                                                <div class="no-photo" style="color: #999; font-size: 10px; text-align: center; padding-top: 35px;">Pas Foto</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="info-cell" style="vertical-align: top;">
                                        {{-- Nomor Tes --}}
                                        <div class="nomor-tes-box" style="border: 1px solid #999; border-radius: 4px; padding: 5px; text-align: center; margin-bottom: 8px;">
                                            <div class="nomor-tes-label" style="color: #666; font-size: 8px; text-transform: uppercase; letter-spacing: 1px;">Nomor Tes</div>
                                            <div class="nomor-tes-value" style="color: #333; font-size: 16px; font-weight: bold; letter-spacing: 1px;">{{ $calonSiswa->nomor_tes }}</div>
                                        </div>
                                        
                                        {{-- Data --}}
                                        <table class="data-table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 8px;">
                                            <tr>
                                                <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">Nama</td>
                                                <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                                                <td class="data-value nama-value" style="font-weight: bold; color: #333; font-size: 9px; text-transform: uppercase; text-align: left;">{{ $calonSiswa->nama_lengkap }}</td>
                                            </tr>
                                            <tr>
                                                <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">NISN</td>
                                                <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                                                <td class="data-value" style="font-weight: bold; color: #333; font-size: 9px; text-align: left;">{{ $calonSiswa->nisn }}</td>
                                            </tr>
                                            <tr>
                                                <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">TTL</td>
                                                <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                                                <td class="data-value" style="font-weight: bold; color: #333; font-size: 9px; text-align: left;">{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($calonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                        </table>
                                        
                                        {{-- Password --}}
                                        <div class="password-box" style="border: 1px dashed #999; border-radius: 4px; padding: 5px 8px;">
                                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                                <tr>
                                                    <td class="password-label" style="color: #666; font-size: 9px;">🔑 Password:</td>
                                                    <td class="password-value" style="color: #c0392b; font-size: 12px; font-weight: bold; letter-spacing: 2px; font-family: Consolas, monospace; text-align: right;">{{ $password }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        {{-- Footer --}}
                        <div class="card-footer" style="position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #ccc; padding: 6px 12px; background: #fff;">
                            <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td><span class="year-badge" style="border: 1px solid #999; color: #333; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold;">{{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ (($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1) }}</span></td>
                                    <td class="footer-center" style="text-align: center; color: #666; font-size: 9px;">{{ $calonSiswa->jalurPendaftaran->nama ?? 'Jalur Umum' }}</td>
                                    <td class="footer-right" style="text-align: right; color: #999; font-size: 8px;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0" style="font-size: 12px;">✂️ Gunting mengikuti tepi kartu setelah dicetak</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-info" onclick="printKartuUjian()">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
                <a href="{{ route('pendaftar.cetak-kartu-ujian') }}" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Print Kartu Ujian from modal
function printKartuUjian() {
    var printContent = document.getElementById('kartuUjianContent').innerHTML;
    var printWindow = window.open('', '_blank', 'width=500,height=400');
    printWindow.document.write('<html><head><title>Kartu Tes - {{ $calonSiswa->nomor_tes }}</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }');
    printWindow.document.write('.card { width: 340px; height: 220px; margin: 0 auto; background: #fff; border: 1px solid #999; border-radius: 8px; overflow: hidden; position: relative; }');
    printWindow.document.write('.watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100px; height: 100px; opacity: 0.12; }');
    printWindow.document.write('.watermark img { width: 100%; height: 100%; object-fit: contain; }');
    printWindow.document.write('.card-header { border-bottom: 1px solid #ccc; padding: 8px 12px; background: #fff; }');
    printWindow.document.write('.card-header table { width: 100%; }');
    printWindow.document.write('.school-name { color: #333; font-size: 11px; font-weight: bold; text-transform: uppercase; }');
    printWindow.document.write('.card-type { color: #666; font-size: 9px; border: 1px solid #999; padding: 2px 6px; border-radius: 3px; }');
    printWindow.document.write('.card-body { padding: 10px 12px; }');
    printWindow.document.write('.card-body table { width: 100%; }');
    printWindow.document.write('.photo-cell { width: 80px; vertical-align: top; padding-right: 10px; }');
    printWindow.document.write('.photo-box { width: 75px; height: 100px; border: 1px solid #999; border-radius: 4px; overflow: hidden; background: #fff; }');
    printWindow.document.write('.photo-box img { width: 75px; height: 100px; object-fit: cover; }');
    printWindow.document.write('.no-photo { color: #999; font-size: 10px; text-align: center; padding-top: 35px; }');
    printWindow.document.write('.info-cell { vertical-align: top; }');
    printWindow.document.write('.nomor-tes-box { border: 1px solid #999; border-radius: 4px; padding: 5px; text-align: center; margin-bottom: 8px; }');
    printWindow.document.write('.nomor-tes-label { color: #666; font-size: 8px; text-transform: uppercase; letter-spacing: 1px; }');
    printWindow.document.write('.nomor-tes-value { color: #333; font-size: 16px; font-weight: bold; letter-spacing: 1px; }');
    printWindow.document.write('.data-table { width: 100%; margin-bottom: 8px; }');
    printWindow.document.write('.data-table td { padding: 2px 0; font-size: 10px; color: #333; vertical-align: top; }');
    printWindow.document.write('.data-label { width: 45px; color: #666; }');
    printWindow.document.write('.data-separator { width: 10px; color: #666; }');
    printWindow.document.write('.data-value { font-weight: bold; color: #333; }');
    printWindow.document.write('.nama-value { font-size: 11px; text-transform: uppercase; }');
    printWindow.document.write('.password-box { border: 1px dashed #999; border-radius: 4px; padding: 5px 8px; }');
    printWindow.document.write('.password-box table { width: 100%; }');
    printWindow.document.write('.password-label { color: #666; font-size: 9px; }');
    printWindow.document.write('.password-value { color: #c0392b; font-size: 12px; font-weight: bold; letter-spacing: 2px; font-family: Consolas, monospace; text-align: right; }');
    printWindow.document.write('.card-footer { position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #ccc; padding: 6px 12px; background: #fff; }');
    printWindow.document.write('.card-footer table { width: 100%; }');
    printWindow.document.write('.card-footer td { color: #666; font-size: 9px; }');
    printWindow.document.write('.year-badge { border: 1px solid #999; color: #333; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }');
    printWindow.document.write('.footer-center { text-align: center; color: #666; }');
    printWindow.document.write('.footer-right { text-align: right; color: #999; font-size: 8px; }');
    printWindow.document.write('@media print { @page { size: A4; margin: 15mm; } }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() { printWindow.print(); }, 250);
}
</script>
@endif

@stack('scripts')

</body>
</html>
