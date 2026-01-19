@extends('adminlte::page')

@section('title', 'Detail Log Email')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-envelope mr-2"></i>Detail Log Email</h1>
        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i> Informasi Email
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Status</th>
                        <td>{!! $emailLog->status_badge !!}</td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        <td>{{ $emailLog->type_label }}</td>
                    </tr>
                    <tr>
                        <th>Penerima</th>
                        <td>
                            <strong>{{ $emailLog->to_name }}</strong><br>
                            <span class="text-muted">{{ $emailLog->to_email }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <td>{{ $emailLog->subject }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $emailLog->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Terkirim</th>
                        <td>
                            @if($emailLog->sent_at)
                                {{ \Carbon\Carbon::parse($emailLog->sent_at)->format('d M Y H:i:s') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @if($emailLog->error_message)
                    <tr>
                        <th>Error</th>
                        <td>
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $emailLog->error_message }}
                            </div>
                        </td>
                    </tr>
                    @endif
                    @if($emailLog->message_preview)
                    <tr>
                        <th>Preview</th>
                        <td>
                            <pre class="bg-light p-2 rounded" style="max-height: 300px; overflow-y: auto;">{{ $emailLog->message_preview }}</pre>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            @if($emailLog->status === 'failed')
            <div class="card-footer">
                <form action="{{ route('admin.email-logs.retry', $emailLog) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Kirim ulang email ini?')">
                        <i class="fas fa-redo mr-1"></i> Kirim Ulang
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        @if($emailLog->calonSiswa)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-1"></i> Data Pendaftar
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($emailLog->calonSiswa->dokumen && $emailLog->calonSiswa->dokumen->pas_foto)
                        <img src="{{ Storage::url($emailLog->calonSiswa->dokumen->pas_foto) }}" 
                             alt="Foto" class="img-thumbnail" style="max-height: 120px;">
                    @else
                        <img src="{{ asset('images/default-avatar.png') }}" 
                             alt="Default" class="img-thumbnail" style="max-height: 120px;">
                    @endif
                </div>
                <table class="table table-sm">
                    <tr>
                        <th>Nama</th>
                        <td>{{ $emailLog->calonSiswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <th>No. Registrasi</th>
                        <td>{{ $emailLog->calonSiswa->nomor_registrasi }}</td>
                    </tr>
                    @if($emailLog->calonSiswa->nomor_tes)
                    <tr>
                        <th>No. Tes</th>
                        <td><span class="badge badge-success">{{ $emailLog->calonSiswa->nomor_tes }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Email</th>
                        <td>{{ $emailLog->calonSiswa->email }}</td>
                    </tr>
                </table>
                <a href="{{ route('admin.pendaftar.show', $emailLog->calonSiswa) }}" class="btn btn-primary btn-block btn-sm">
                    <i class="fas fa-eye mr-1"></i> Lihat Detail Pendaftar
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@stop
