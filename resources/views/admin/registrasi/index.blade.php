@extends('adminlte::page')

@section('title', 'Data Registrasi')

@section('plugins.Select2', true)

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-clipboard-check mr-2"></i>Data Registrasi</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Registrasi</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Statistik --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $totalLulus }}</h3><p>Total Lulus</p></div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $totalRegistrasi }}</h3><p>Sudah Registrasi</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $belumRegistrasi }}</h3><p>Belum Registrasi</p></div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $totalKonflik }}</h3><p>Konflik Jurusan</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter & Aksi --}}
    <div class="card card-outline card-info">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.registrasi.index') }}" class="form-row align-items-end">
                <div class="form-group col-lg-3 col-md-6 mb-2">
                    <label class="mb-1 small font-weight-bold">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control form-control-sm">
                        @foreach($tahunPelajarans as $tahun)
                            <option value="{{ $tahun->id }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-2">
                    <label class="mb-1 small font-weight-bold">Status Match</label>
                    <select name="match_status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="matched_exact" {{ $filterStatus === 'matched_exact' ? 'selected' : '' }}>Cocok Persis</option>
                        <option value="matched_fuzzy" {{ $filterStatus === 'matched_fuzzy' ? 'selected' : '' }}>Mirip</option>
                        <option value="conflict_jurusan" {{ $filterStatus === 'conflict_jurusan' ? 'selected' : '' }}>Konflik Jurusan</option>
                        <option value="manual" {{ $filterStatus === 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="form-group col-lg-4 col-md-8 mb-2">
                    <label class="mb-1 small font-weight-bold">Cari (nama / no. tes / notes)</label>
                    <input type="text" name="q" value="{{ $searchQ }}" class="form-control form-control-sm" placeholder="Ketik kata kunci...">
                </div>
                <div class="form-group col-lg-2 col-md-4 mb-2">
                    <label class="mb-1 small font-weight-bold d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i>Terapkan</button>
                    <a href="{{ route('admin.registrasi.index') }}" class="btn btn-default btn-sm" title="Reset"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel data --}}
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-table mr-2"></i>Daftar Pendaftar Teregistrasi ({{ $registrasis->total() }})</h3>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahRegistrasi">
                    <i class="fas fa-plus mr-1"></i>Tambah
                </button>
                <a href="{{ route('admin.registrasi.export', ['tahun_pelajaran_id' => $selectedTahunIdInput, 'match_status' => $filterStatus, 'q' => $searchQ]) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-file-excel mr-1"></i>Export Excel
                </a>
                <a href="{{ route('admin.registrasi.upload', ['tahun_pelajaran_id' => $selectedTahunIdInput]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-import mr-1"></i>Import Excel
                </a>
            </div>
        </div>
        <div class="card-body p-0" style="overflow-x:auto;">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Notes</th>
                        <th>Nama Pendaftar</th>
                        <th>No. Tes</th>
                        <th>Gelombang</th>
                        <th>Jurusan</th>
                        <th class="text-center">Status</th>
                        <th>Tgl. Registrasi</th>
                        <th width="70" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrasis as $i => $reg)
                        @php
                            $badge = match($reg->match_status) {
                                'matched_exact' => ['success', 'Cocok'],
                                'matched_fuzzy' => ['info', 'Mirip'],
                                'conflict_jurusan' => ['warning', 'Konflik Jurusan'],
                                default => ['secondary', 'Manual'],
                            };
                        @endphp
                        <tr>
                            <td class="text-center">{{ $registrasis->firstItem() + $i }}</td>
                            <td><code>{{ $reg->notes ?: '-' }}</code></td>
                            <td>
                                {{ $reg->calonSiswa?->nama_lengkap ?? $reg->nama_excel ?? '-' }}
                                @if($reg->nama_excel && $reg->calonSiswa && strcasecmp($reg->nama_excel, $reg->calonSiswa->nama_lengkap) !== 0)
                                    <br><small class="text-muted">Excel: {{ $reg->nama_excel }}</small>
                                @endif
                            </td>
                            <td>{{ $reg->calonSiswa?->nomor_tes ?? '-' }}</td>
                            <td>{{ $reg->calonSiswa?->gelombangPendaftaran?->nama ?? '-' }}</td>
                            <td>
                                {{ $reg->jurusan_final ?: '-' }}
                                @if($reg->pindah_jurusan)
                                    <br><small class="text-warning"><i class="fas fa-exchange-alt"></i> dari {{ $reg->jurusan_awal ?: '-' }}</small>
                                @elseif($reg->match_status === 'conflict_jurusan' && $reg->jurusan_excel)
                                    <i class="fas fa-info-circle text-warning" title="Pindah jurusan dari hasil seleksi"></i>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge badge-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td>{{ $reg->tanggal_registrasi?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.registrasi.destroy', $reg->id) }}" method="POST" onsubmit="return confirm('Hapus data registrasi ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data registrasi. Silakan <a href="{{ route('admin.registrasi.upload') }}">import dari Excel</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registrasis->hasPages())
            <div class="card-footer">{{ $registrasis->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Tambah Registrasi --}}
<div class="modal fade" id="modalTambahRegistrasi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="{{ route('admin.registrasi.store') }}" method="POST" id="formTambahRegistrasi">
                @csrf
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah Registrasi Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Daftar hanya menampilkan pendaftar yang <strong>belum registrasi</strong> agar tidak terjadi duplikasi.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Pendaftar <span class="text-danger">*</span></label>
                        <select name="calon_siswa_id" id="selectPendaftar" class="form-control" style="width:100%;" required>
                            <option value=""></option>
                        </select>
                        <small class="form-text text-muted">Ketik nama atau nomor tes untuk mencari.</small>
                    </div>

                    {{-- Kartu info pendaftar terpilih --}}
                    <div id="infoPendaftar" class="card bg-light border mb-3 d-none">
                        <div class="card-body py-2 px-3">
                            <div class="row small">
                                <div class="col-6"><span class="text-muted">No. Tes:</span> <strong id="infoTes">-</strong></div>
                                <div class="col-6"><span class="text-muted">Gelombang:</span> <strong id="infoGelombang">-</strong></div>
                                <div class="col-12 mt-1"><span class="text-muted">Jurusan saat ini:</span> <strong id="infoJurusan">-</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Jurusan Final</label>
                            <input type="text" name="jurusan_final" id="inputJurusanFinal" class="form-control" placeholder="Mis. Asrama / Reguler">
                            <small class="form-text text-warning d-none" id="warnPindah"><i class="fas fa-exchange-alt"></i> Berbeda dari jurusan awal — akan dicatat sebagai pindah jurusan.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Bukti Bayar / Notes</label>
                            <input type="text" name="notes" id="inputNotes" class="form-control" maxlength="20" placeholder="4 digit terakhir no. tes">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanTambah"><i class="fas fa-save mr-1"></i>Simpan Registrasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    var $modal = $('#modalTambahRegistrasi');
    var jurusanAwal = '';

    var $select = $('#selectPendaftar').select2({
        theme: 'bootstrap4',
        dropdownParent: $modal,
        placeholder: 'Cari nama atau nomor tes...',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.registrasi.search-candidates') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    tahun_pelajaran_id: '{{ $selectedTahunIdInput }}',
                    exclude_registered: 1
                };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    $select.on('select2:select', function (e) {
        var d = e.params.data || {};
        jurusanAwal = d.pilihan_program || '';
        $('#infoTes').text(d.nomor_tes || '-');
        $('#infoGelombang').text(d.gelombang || '-');
        $('#infoJurusan').text(d.pilihan_program || '-');
        $('#infoPendaftar').removeClass('d-none');
        if (!$('#inputJurusanFinal').val()) {
            $('#inputJurusanFinal').val(d.pilihan_program || '');
        }
        if (!$('#inputNotes').val() && d.nomor_tes) {
            var digits = (d.nomor_tes + '').replace(/\D/g, '');
            $('#inputNotes').val(digits.slice(-4));
        }
        checkPindah();
    });

    $select.on('select2:clear', function () {
        jurusanAwal = '';
        $('#infoPendaftar').addClass('d-none');
        $('#inputJurusanFinal').val('');
        $('#inputNotes').val('');
        $('#warnPindah').addClass('d-none');
    });

    function checkPindah() {
        var final = ($('#inputJurusanFinal').val() || '').trim();
        if (jurusanAwal && final && final.toLowerCase() !== jurusanAwal.toLowerCase()) {
            $('#warnPindah').removeClass('d-none');
        } else {
            $('#warnPindah').addClass('d-none');
        }
    }
    $('#inputJurusanFinal').on('input', checkPindah);

    $('#formTambahRegistrasi').on('submit', function () {
        if (!$select.val()) {
            alert('Silakan pilih pendaftar terlebih dahulu.');
            return false;
        }
        $('#btnSimpanTambah').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        return true;
    });

    $modal.on('hidden.bs.modal', function () {
        $select.val(null).trigger('change');
        $('#infoPendaftar').addClass('d-none');
        $('#inputJurusanFinal').val('');
        $('#inputNotes').val('');
        $('#warnPindah').addClass('d-none');
        $('#btnSimpanTambah').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan Registrasi');
    });
});
</script>
@endsection
