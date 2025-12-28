@extends('layouts.pendaftar')

@section('title', 'Finalisasi Pendaftaran')
@section('page-title', 'Finalisasi Pendaftaran')

@section('breadcrumb')
<li class="breadcrumb-item active">Finalisasi Pendaftaran</li>
@endsection

@section('css')
<style>
    .list-group-item {
        border-left: 4px solid transparent;
    }
    
    .list-group-item-success {
        border-left-color: #28a745;
    }
    
    .list-group-item-danger {
        border-left-color: #dc3545;
    }
    
    .custom-control-label ul {
        list-style-type: disc;
        padding-left: 20px;
    }
    
    .info-box-number {
        font-size: 1.2rem !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        @if($calonSiswa->is_finalisasi)
        {{-- Already Finalized --}}
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Pendaftaran Sudah Difinalisasi</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-check"></i> Selamat!</h5>
                    Data pendaftaran Anda telah difinalisasi pada:
                    <strong>{{ $calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('d F Y, H:i') : '-' }}</strong>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-id-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Nomor Tes</span>
                                <span class="info-box-number">{{ $calonSiswa->nomor_tes }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Status Admisi</span>
                                <span class="info-box-number">
                                    @if($calonSiswa->status_admisi === 'diterima')
                                        <span class="badge badge-success">Diterima</span>
                                    @elseif($calonSiswa->status_admisi === 'cadangan')
                                        <span class="badge badge-warning">Cadangan</span>
                                    @elseif($calonSiswa->status_admisi === 'ditolak')
                                        <span class="badge badge-danger">Ditolak</span>
                                    @else
                                        <span class="badge badge-secondary">Belum Diproses</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle"></i> <strong>Penting:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Data Anda telah dikunci dan tidak dapat diubah</li>
                        <li>Simpan nomor tes Anda untuk keperluan ujian</li>
                        <li>Anda dapat mencetak kartu ujian dari menu "Cetak Bukti"</li>
                        <li>Pantau status admisi Anda secara berkala</li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <a href="{{ route('pendaftar.cetak-bukti') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-print"></i> Cetak Kartu Ujian
                    </a>
                </div>
            </div>
        </div>

        @else
        {{-- Finalization Form --}}
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Peringatan Finalisasi</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    <p>Setelah Anda melakukan finalisasi:</p>
                    <ul>
                        <li><strong>Semua data akan dikunci</strong> dan tidak dapat diubah lagi</li>
                        <li>Anda akan mendapatkan <strong>Nomor Tes</strong> untuk ujian</li>
                        <li>Data Anda akan masuk ke dalam proses seleksi</li>
                        <li>Pastikan semua data yang Anda isi sudah <strong>benar dan lengkap</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Requirements Checklist --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Kelengkapan Data</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">Pastikan semua persyaratan berikut terpenuhi sebelum melakukan finalisasi:</p>
                
                <div class="list-group">
                    @foreach($requirements['requirements'] as $key => $req)
                    <div class="list-group-item {{ $req['status'] ? 'list-group-item-success' : 'list-group-item-danger' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                @if($req['status'])
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                                <strong>{{ $req['label'] }}</strong>
                            </span>
                            <span class="badge {{ $req['status'] ? 'badge-success' : 'badge-danger' }}">
                                {{ $req['status'] ? 'Lengkap' : 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(!$requirements['can_finalize'])
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-circle"></i> <strong>Belum Dapat Melakukan Finalisasi</strong>
                    <p class="mb-0 mt-2">Lengkapi terlebih dahulu:</p>
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
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-double"></i> Konfirmasi Finalisasi</h3>
            </div>
            <div class="card-body">
                <form id="formFinalisasi">
                    @csrf
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="confirmation" name="confirmation" value="1">
                            <label for="confirmation" class="custom-control-label">
                                <strong>Saya menyatakan bahwa:</strong>
                                <ul class="mt-2">
                                    <li>Semua data yang saya isi adalah <strong>benar dan dapat dipertanggungjawabkan</strong></li>
                                    <li>Saya memahami bahwa data yang sudah difinalisasi <strong>tidak dapat diubah</strong></li>
                                    <li>Saya siap mengikuti proses seleksi sesuai dengan ketentuan yang berlaku</li>
                                    <li>Saya bersedia menerima konsekuensi jika terdapat <strong>pemalsuan data</strong></li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="button" class="btn btn-secondary btn-lg mr-2" onclick="window.location.href='{{ route('pendaftar.dashboard') }}'">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-success btn-lg" id="btnFinalisasi">
                            <i class="fas fa-check"></i> Finalisasi Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="text-center mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        @endif

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
            html: '<p>Apakah Anda yakin ingin melakukan finalisasi?</p>' +
                  '<p class="text-danger"><strong>Data Anda akan dikunci dan tidak dapat diubah lagi.</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Finalisasi',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                processFinalisasi();
            }
        });
    });

    function processFinalisasi() {
        const btnFinalisasi = $('#btnFinalisasi');
        const originalText = btnFinalisasi.html();
        
        btnFinalisasi.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

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
                        html: '<p>' + response.message + '</p>' +
                              '<p class="mt-3">Nomor Tes Anda: <strong class="text-primary">' + response.nomor_tes + '</strong></p>',
                        icon: 'success',
                        confirmButtonText: 'OK'
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
                    html: errorMsg,
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
