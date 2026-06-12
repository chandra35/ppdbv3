@extends('adminlte::page')

@section('title', 'Data Registrasi')

@section('plugins.Select2', true)

@section('css')
<style>
    /* ===== Modal Tambah Registrasi — Tampilan Modern ===== */
    #modalTambahRegistrasi .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .28);
    }
    #modalTambahRegistrasi .modal-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border-bottom: none;
        padding: 1.4rem 1.6rem;
        position: relative;
    }
    #modalTambahRegistrasi .modal-header .modal-title {
        font-weight: 700;
        letter-spacing: .2px;
        display: flex;
        align-items: center;
        gap: .85rem;
    }
    #modalTambahRegistrasi .header-avatar {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .25);
    }
    #modalTambahRegistrasi .header-sub {
        font-size: .76rem;
        font-weight: 400;
        opacity: .85;
        margin-top: 2px;
    }
    #modalTambahRegistrasi .modal-header .close {
        opacity: .9;
        text-shadow: none;
        font-size: 1.6rem;
        transition: transform .2s ease;
    }
    #modalTambahRegistrasi .modal-header .close:hover { transform: rotate(90deg); opacity: 1; }
    #modalTambahRegistrasi .modal-body { padding: 1.5rem 1.6rem .5rem; }
    #modalTambahRegistrasi .info-banner {
        background: #eef3ff;
        border: 1px solid #d6e0ff;
        border-radius: 12px;
        padding: .65rem .9rem;
        font-size: .82rem;
        color: #2c3e75;
    }
    #modalTambahRegistrasi label.field-label {
        font-weight: 600;
        font-size: .82rem;
        color: #4a4a6a;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: .4rem;
    }
    #modalTambahRegistrasi .input-group-text {
        background: #f4f6fb;
        border-right: none;
        color: #4e73df;
    }
    #modalTambahRegistrasi .form-control { border-radius: 10px; }
    #modalTambahRegistrasi .input-group .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    #modalTambahRegistrasi .input-group-prepend .input-group-text {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    /* Select2 menyatu dengan tema */
    #modalTambahRegistrasi .select2-container--bootstrap4 .select2-selection {
        border-radius: 10px;
        min-height: calc(2.25rem + 2px);
        border-color: #ced4da;
    }
    #modalTambahRegistrasi .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #4e73df;
        box-shadow: 0 0 0 .2rem rgba(78, 115, 223, .25);
    }
    /* Kartu info pendaftar */
    #infoPendaftar {
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8faff 0%, #eef3ff 100%);
        box-shadow: 0 2px 10px rgba(78, 115, 223, .08);
        animation: fadeUp .35s ease;
    }
    #infoPendaftar .info-item { display: flex; flex-direction: column; gap: 2px; }
    #infoPendaftar .info-item .lbl {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #8a92b2;
        font-weight: 600;
    }
    #infoPendaftar .info-item .val { font-weight: 700; color: #2c3e75; font-size: .92rem; }
    #infoPendaftar .info-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: #4e73df; color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.05rem;
    }
    #warnPindahBox {
        background: #fff8e6;
        border: 1px solid #ffe2a8;
        border-radius: 12px;
        padding: .6rem .85rem;
        font-size: .82rem;
        color: #8a6d1a;
        animation: fadeUp .3s ease;
    }
    #modalTambahRegistrasi .modal-footer {
        border-top: 1px solid #eef0f5;
        padding: 1rem 1.6rem 1.3rem;
    }
    #modalTambahRegistrasi .btn-simpan {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        border-radius: 10px;
        padding: .5rem 1.4rem;
        font-weight: 600;
        box-shadow: 0 6px 16px rgba(34, 74, 190, .3);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    #modalTambahRegistrasi .btn-simpan:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(34, 74, 190, .4);
    }
    #modalTambahRegistrasi .btn-batal { border-radius: 10px; padding: .5rem 1.2rem; font-weight: 600; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

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
        <div class="modal-content">
            <form action="{{ route('admin.registrasi.store') }}" method="POST" id="formTambahRegistrasi">
                @csrf
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">

                <div class="modal-header text-white">
                    <h5 class="modal-title">
                        <span class="header-avatar"><i class="fas fa-user-plus"></i></span>
                        <span>
                            Tambah Registrasi Baru
                            <span class="d-block header-sub">Catat pendaftar yang melakukan daftar ulang</span>
                        </span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="info-banner mb-3">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Hanya menampilkan pendaftar yang <strong>belum registrasi</strong> — duplikasi otomatis dicegah.
                    </div>

                    <div class="form-group">
                        <label class="field-label"><i class="fas fa-search mr-1"></i> Pilih Pendaftar <span class="text-danger">*</span></label>
                        <select name="calon_siswa_id" id="selectPendaftar" class="form-control" style="width:100%;" required>
                            <option value=""></option>
                        </select>
                        <small class="form-text text-muted">Ketik nama atau nomor tes untuk mencari.</small>
                    </div>

                    {{-- Kartu info pendaftar terpilih --}}
                    <div id="infoPendaftar" class="card mb-3 d-none">
                        <div class="card-body py-3 px-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="info-icon mr-2"><i class="fas fa-id-card"></i></span>
                                <strong id="infoNama" class="text-dark">-</strong>
                            </div>
                            <div class="row">
                                <div class="col-4 info-item"><span class="lbl">No. Tes</span><span class="val" id="infoTes">-</span></div>
                                <div class="col-4 info-item"><span class="lbl">Gelombang</span><span class="val" id="infoGelombang">-</span></div>
                                <div class="col-4 info-item"><span class="lbl">Jurusan</span><span class="val" id="infoJurusan">-</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="field-label">Jurusan Final</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-graduation-cap"></i></span></div>
                                <input type="text" name="jurusan_final" id="inputJurusanFinal" class="form-control" placeholder="Asrama / Reguler">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="field-label">Bukti Bayar <span class="text-muted" style="text-transform:none;font-weight:400;">(opsional)</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-receipt"></i></span></div>
                                <input type="text" name="notes" id="inputNotes" class="form-control" maxlength="20" placeholder="Kode ref / ID transaksi">
                            </div>
                            <small class="form-text text-muted">Kode referensi dari struk ATM / bukti transfer, jika ada.</small>
                        </div>
                    </div>

                    <div id="warnPindahBox" class="d-none mb-2">
                        <i class="fas fa-exchange-alt mr-1"></i>
                        Jurusan final berbeda dari jurusan awal (<strong id="warnAwal">-</strong> &rarr; <strong id="warnFinal">-</strong>). Akan dicatat sebagai <strong>pindah jurusan</strong> di Activity Log.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-batal" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Batal</button>
                    <button type="submit" class="btn btn-primary btn-simpan text-white" id="btnSimpanTambah"><i class="fas fa-save mr-1"></i>Simpan Registrasi</button>
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
        $('#infoNama').text(d.nama_lengkap || '-');
        $('#infoTes').text(d.nomor_tes || '-');
        $('#infoGelombang').text(d.gelombang || '-');
        $('#infoJurusan').text(d.pilihan_program || '-');
        $('#infoPendaftar').removeClass('d-none');
        if (!$('#inputJurusanFinal').val()) {
            $('#inputJurusanFinal').val(d.pilihan_program || '');
        }
        checkPindah();
    });

    $select.on('select2:clear', function () {
        jurusanAwal = '';
        $('#infoPendaftar').addClass('d-none');
        $('#inputJurusanFinal').val('');
        $('#inputNotes').val('');
        $('#warnPindahBox').addClass('d-none');
    });

    function checkPindah() {
        var final = ($('#inputJurusanFinal').val() || '').trim();
        if (jurusanAwal && final && final.toLowerCase() !== jurusanAwal.toLowerCase()) {
            $('#warnAwal').text(jurusanAwal);
            $('#warnFinal').text(final);
            $('#warnPindahBox').removeClass('d-none');
        } else {
            $('#warnPindahBox').addClass('d-none');
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
        $('#warnPindahBox').addClass('d-none');
        $('#btnSimpanTambah').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan Registrasi');
    });
});
</script>
@endsection
