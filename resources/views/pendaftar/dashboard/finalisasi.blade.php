@extends('layouts.pendaftar')

@section('title', 'Finalisasi Pendaftaran')
@section('page-title', 'Finalisasi Pendaftaran')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pendaftar.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Finalisasi</li>
@endsection

@section('css')
<style>
    .finalized-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .warning-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .requirement-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: white;
    }

    .requirement-item.completed {
        border-left-color: #10b981;
        background: linear-gradient(90deg, #ecfdf5 0%, #f0fdf4 100%);
    }

    .requirement-item.incomplete {
        border-left-color: #ef4444;
        background: linear-gradient(90deg, #fef2f2 0%, #fee2e2 100%);
    }

    .requirement-icon {
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 1rem;
    }

    .requirement-icon.completed {
        background: #10b981;
        color: white;
    }

    .requirement-icon.incomplete {
        background: #ef4444;
        color: white;
    }

    .info-box-custom {
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .info-box-custom:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .nomor-tes-display {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 2px;
        color: #10b981;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .confirmation-checkbox {
        transform: scale(1.2);
        margin-right: 0.75rem;
    }

    .confirmation-label {
        line-height: 1.8;
        cursor: pointer;
    }

    .btn-finalize {
        padding: 1rem 2.5rem;
        font-size: 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-finalize:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .timeline-step {
        position: relative;
        padding-left: 2.5rem;
        padding-bottom: 1.5rem;
    }

    .timeline-step:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 2rem;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #10b981;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .finalized-header, .warning-header {
            padding: 1.5rem;
        }

        .nomor-tes-display {
            font-size: 1.4rem;
            letter-spacing: 1px;
        }

        .requirement-icon {
            width: 36px;
            height: 36px;
            font-size: 1.2rem;
        }

        .timeline-step {
            padding-left: 2rem;
        }

        .btn-finalize {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .requirement-item {
            padding: 0.75rem;
        }

        .requirement-icon {
            width: 32px;
            height: 32px;
            font-size: 1rem;
            margin-right: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if($calonSiswa->is_finalisasi)
        {{-- Already Finalized State --}}
        <div class="finalized-header">
            <div class="text-center">
                <i class="fas fa-check-circle fa-4x mb-3"></i>
                <h2 class="mb-2">Pendaftaran Berhasil Difinalisasi!</h2>
                <p class="mb-0">
                    <i class="far fa-calendar-alt mr-2"></i>
                    {{ $calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('d F Y, H:i') : '-' }} WIB
                </p>
            </div>
        </div>

        {{-- Nomor Tes Card --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <h5 class="text-muted mb-2">Nomor Tes Anda</h5>
                <div class="nomor-tes-display">{{ $calonSiswa->nomor_tes }}</div>
                <p class="text-muted mt-2 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>Simpan nomor ini untuk keperluan ujian
                </p>
            </div>
        </div>

        {{-- Status Cards --}}
        <div class="row">
            <div class="col-md-6 col-12 mb-3">
                <div class="info-box-custom bg-gradient-success">
                    <div class="info-box-content p-3 text-white text-center">
                        <i class="fas fa-id-card fa-2x mb-2"></i>
                        <h5 class="mb-1">Status Registrasi</h5>
                        <h3 class="mb-0">Terdaftar</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12 mb-3">
                <div class="info-box-custom bg-gradient-info">
                    <div class="info-box-content p-3 text-white text-center">
                        <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                        <h5 class="mb-1">Status Admisi</h5>
                        <h3 class="mb-0">
                            @if($calonSiswa->status_admisi === 'diterima')
                                Diterima
                            @elseif($calonSiswa->status_admisi === 'cadangan')
                                Cadangan
                            @elseif($calonSiswa->status_admisi === 'ditolak')
                                Ditolak
                            @else
                                Belum Diproses
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Important Info --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-warning">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Informasi Penting
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0 pl-4">
                    <li class="mb-2"><i class="fas fa-lock text-warning mr-2"></i>Data Anda telah dikunci dan tidak dapat diubah lagi</li>
                    <li class="mb-2"><i class="fas fa-save text-primary mr-2"></i>Simpan nomor tes Anda dengan baik</li>
                    <li class="mb-2"><i class="fas fa-print text-info mr-2"></i>Cetak kartu ujian untuk keperluan tes</li>
                    <li class="mb-2"><i class="fas fa-chart-line text-success mr-2"></i>Pantau status admisi Anda secara berkala</li>
                </ul>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="text-center mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-outline-primary btn-lg mb-2">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
            <a href="{{ route('pendaftar.cetak-bukti-registrasi.preview') }}" target="_blank" class="btn btn-info btn-lg mb-2">
                <i class="fas fa-file-pdf mr-2"></i>Cetak Bukti Registrasi
            </a>
            <button type="button" class="btn btn-success btn-lg mb-2" data-toggle="modal" data-target="#kartuUjianModal">
                <i class="fas fa-id-card mr-2"></i>Cetak Kartu Ujian
            </button>
        </div>

        @else
        {{-- Finalization Process --}}
        <div class="warning-header">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
                <h2 class="mb-2">Peringatan Finalisasi</h2>
                <p class="mb-0">Pastikan semua data sudah benar sebelum melakukan finalisasi</p>
            </div>
        </div>

        {{-- Warning Alert --}}
        <div class="alert alert-warning mb-4" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle mr-2"></i>Setelah Finalisasi:
            </h5>
            <ul class="mb-0 pl-4">
                <li><strong>Semua data akan dikunci</strong> dan tidak dapat diubah</li>
                <li>Anda akan mendapatkan <strong>Nomor Tes</strong> untuk ujian</li>
                <li>Data masuk ke proses seleksi</li>
                <li>Tidak dapat membatalkan finalisasi</li>
            </ul>
        </div>

        {{-- Requirements Checklist --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-tasks mr-2"></i>Kelengkapan Data
                </h5>
            </div>
            <div class="card-body">
                @foreach($requirements['requirements'] as $key => $req)
                <div class="requirement-item {{ $req['status'] ? 'completed' : 'incomplete' }}">
                    <div class="d-flex align-items-center">
                        <div class="requirement-icon {{ $req['status'] ? 'completed' : 'incomplete' }}">
                            <i class="fas fa-{{ $req['status'] ? 'check' : 'times' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $req['label'] }}</h6>
                        </div>
                        <div>
                            <span class="badge badge-{{ $req['status'] ? 'success' : 'danger' }} px-3 py-2">
                                {{ $req['status'] ? 'Lengkap' : 'Belum' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach

                @if(!$requirements['can_finalize'])
                <div class="alert alert-danger mt-3 mb-0" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-times-circle mr-2"></i>Belum Dapat Melakukan Finalisasi
                    </h6>
                    <p class="mb-2">Silakan lengkapi terlebih dahulu:</p>
                    <ul class="mb-0">
                        @foreach($requirements['missing'] as $missing)
                        <li>{{ $missing }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        @if($requirements['can_finalize'])
        {{-- Confirmation Form --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-check-double mr-2"></i>Konfirmasi Finalisasi
                </h5>
            </div>
            <div class="card-body">
                <form id="formFinalisasi">
                    @csrf
                    
                    <div class="custom-control custom-checkbox mb-4">
                        <input type="checkbox" class="custom-control-input confirmation-checkbox" id="confirmation" name="confirmation" value="1">
                        <label class="custom-control-label confirmation-label" for="confirmation">
                            <strong>Saya menyatakan bahwa:</strong>
                            <ul class="mt-2 mb-0">
                                <li>Semua data yang saya isi adalah <strong>benar dan dapat dipertanggungjawabkan</strong></li>
                                <li>Saya memahami bahwa data yang sudah difinalisasi <strong>tidak dapat diubah</strong></li>
                                <li>Saya siap mengikuti proses seleksi sesuai ketentuan yang berlaku</li>
                                <li>Saya bersedia menerima konsekuensi jika terdapat <strong>pemalsuan data</strong></li>
                            </ul>
                        </label>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-outline-secondary btn-lg mr-2 mb-2" onclick="window.location.href='{{ route('pendaftar.dashboard') }}'">
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success btn-finalize mb-2" id="btnFinalisasi">
                            <i class="fas fa-check mr-2"></i>Finalisasi Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="text-center mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
        @endif

        @endif
    </div>

    {{-- Sidebar (Desktop) --}}
    <div class="col-lg-4 d-none d-lg-block">
        @if($calonSiswa->is_finalisasi)
        {{-- Finalized Sidebar --}}
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="mb-0"><i class="fas fa-route mr-2"></i>Langkah Selanjutnya</h5>
            </div>
            <div class="card-body">
                <div class="timeline-step">
                    <div class="timeline-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h6 class="font-weight-bold">Finalisasi</h6>
                    <p class="text-muted small mb-0">Selesai - {{ $calonSiswa->tanggal_finalisasi->format('d M Y') }}</p>
                </div>
                <div class="timeline-step">
                    <div class="timeline-icon" style="background: #3b82f6;">
                        <i class="fas fa-print"></i>
                    </div>
                    <h6 class="font-weight-bold">Cetak Kartu Tes</h6>
                    <p class="text-muted small mb-0">Cetak dan bawa saat ujian</p>
                </div>
                <div class="timeline-step">
                    <div class="timeline-icon" style="background: #f59e0b;">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <h6 class="font-weight-bold">Ikuti Tes</h6>
                    <p class="text-muted small mb-0">Sesuai jadwal yang ditentukan</p>
                </div>
                <div class="timeline-step">
                    <div class="timeline-icon" style="background: #8b5cf6;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h6 class="font-weight-bold">Pengumuman</h6>
                    <p class="text-muted small mb-0">Tunggu hasil seleksi</p>
                </div>
            </div>
        </div>
        @else
        {{-- Pre-finalization Sidebar --}}
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-list mr-2"></i>Checklist</h5>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Sebelum Finalisasi:</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $progress['data_diri'] >= 100 ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Data Pribadi Lengkap
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $progress['data_ortu'] >= 100 ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Data Orang Tua Lengkap
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $progress['dokumen'] >= 100 ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Dokumen Terupload
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $progress['nilai_rapor'] >= 100 ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Nilai Rapor Terisi
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $calonSiswa->status_verifikasi === 'verified' ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Data Terverifikasi
                    </label>
                </div>
                @if(isset($progress['pilihan_program']))
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" disabled {{ $progress['pilihan_program'] >= 100 ? 'checked' : '' }}>
                    <label class="form-check-label">
                        Pilihan Program Dipilih
                    </label>
                </div>
                @endif

                <hr>

                <div class="alert alert-warning mb-0" role="alert">
                    <small>
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Penting:</strong> Periksa kembali semua data sebelum finalisasi
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#formFinalisasi').on('submit', function(e) {
        e.preventDefault();
        
        // Check confirmation
        if (!$('#confirmation').is(':checked')) {
            toastr.warning('Anda harus menyetujui pernyataan finalisasi');
            return;
        }

        // Confirm with SweetAlert
        Swal.fire({
            title: 'Konfirmasi Finalisasi',
            html: '<div class="text-left">' +
                  '<p class="mb-3">Apakah Anda yakin ingin melakukan finalisasi?</p>' +
                  '<div class="alert alert-danger mb-0">' +
                  '<strong><i class="fas fa-exclamation-triangle mr-2"></i>Peringatan:</strong><br>' +
                  'Data Anda akan dikunci dan tidak dapat diubah lagi.' +
                  '</div>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Finalisasi',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            reverseButtons: true,
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                processFinalisasi();
            }
        });
    });

    function processFinalisasi() {
        const btnFinalisasi = $('#btnFinalisasi');
        const originalText = btnFinalisasi.html();
        
        btnFinalisasi.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');

        $.ajax({
            url: "{{ route('pendaftar.finalisasi.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                confirmation: $('#confirmation').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        html: '<div class="text-center">' +
                              '<i class="fas fa-check-circle fa-4x text-success mb-3"></i>' +
                              '<p class="mb-3">' + response.message + '</p>' +
                              '<div class="alert alert-success">' +
                              '<h5 class="mb-2">Nomor Tes Anda:</h5>' +
                              '<h3 class="mb-0" style="color: #10b981; font-weight: 700; letter-spacing: 2px;">' + response.nomor_tes + '</h3>' +
                              '</div>' +
                              '</div>',
                        icon: 'success',
                        confirmButtonText: '<i class="fas fa-arrow-right mr-2"></i>Lanjutkan',
                        confirmButtonColor: '#10b981',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "{{ route('pendaftar.dashboard') }}";
                    });
                } else {
                    toastr.error(response.message || 'Terjadi kesalahan');
                    btnFinalisasi.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan saat finalisasi';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                
                Swal.fire({
                    title: 'Gagal!',
                    html: '<div class="alert alert-danger">' + errorMsg + '</div>',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                
                btnFinalisasi.prop('disabled', false).html(originalText);
            }
        });
    }
});
</script>
@endsection
