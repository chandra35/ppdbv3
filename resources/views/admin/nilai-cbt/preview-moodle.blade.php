@extends('adminlte::page')

@section('title', 'Preview Nilai CBT dari Moodle')

@section('css')
<style>
    .cbt-preview-table { font-size: .86rem; }
    .cbt-preview-table td, .cbt-preview-table th { vertical-align: top; }
    .quiz-pill { display:inline-block; margin:.15rem .2rem .15rem 0; padding:.2rem .5rem; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:.75rem; }
    .course-card { border:1px solid #e5e7eb; border-radius:12px; padding:.75rem; background:#fff; margin-bottom:.65rem; }
    .course-card.ready { border-color:#bfdbfe; background:#eff6ff; }
    .course-card.unmapped { border-color:#fde68a; background:#fffbeb; }
    .status-ready { color:#166534; font-weight:700; }
    .status-partial { color:#b45309; font-weight:700; }
    .status-empty { color:#6b7280; font-weight:700; }
    .cbt-save-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(5px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 1rem;
    }
    .cbt-save-overlay.show { display:flex; }
    .cbt-save-card {
        width:min(560px,100%);
        background:#fff;
        border-radius:18px;
        box-shadow:0 28px 90px rgba(15,23,42,.32);
        overflow:hidden;
    }
    .cbt-save-head {
        padding:1rem 1.25rem;
        background:linear-gradient(135deg,#166534 0%,#059669 50%,#0f766e 100%);
        color:#fff;
    }
    .cbt-save-body { padding:1.25rem; }
    .cbt-save-progress {
        height:12px;
        background:#e2e8f0;
        border-radius:999px;
        overflow:hidden;
    }
    .cbt-save-progress span {
        display:block;
        width:42%;
        height:100%;
        background:linear-gradient(90deg,#16a34a,#14b8a6,#16a34a);
        background-size:220% 100%;
        animation: cbtSaveBar 1.4s linear infinite, cbtSaveWidth 1.7s ease-in-out infinite;
    }
    @keyframes cbtSaveBar {
        0% { background-position:0% 0; }
        100% { background-position:220% 0; }
    }
    @keyframes cbtSaveWidth {
        0% { width:30%; }
        50% { width:88%; }
        100% { width:36%; }
    }
    .cbt-save-statusline {
        margin-top:1rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        font-size:.9rem;
        color:#475569;
    }
    .cbt-save-chip {
        display:inline-flex;
        align-items:center;
        gap:.4rem;
        padding:.35rem .75rem;
        border-radius:999px;
        background:#ecfdf5;
        color:#166534;
        font-weight:700;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-cloud-download-alt mr-2"></i>Preview Nilai CBT dari Moodle</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-cbt.index', $returnContext) }}">Nilai CBT</a></li>
            <li class="breadcrumb-item active">Preview Moodle</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Preview Moodle memakai konteks:
        Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
        Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
        Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $summary['total_candidates'] }}</h3><p>Total Pendaftar</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $summary['matched_users'] }}</h3><p>User Moodle Ditemukan</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $summary['with_quiz_data'] }}</h3><p>Punya Data Quiz</p></div>
                <div class="icon"><i class="fas fa-poll"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $summary['ready_to_save'] }}</h3><p>Siap Disimpan</p></div>
                <div class="icon"><i class="fas fa-save"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sitemap mr-2"></i>Sumber Moodle</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Mapping aktif:</strong> {{ $mapping?->keterangan ?: 'Default/global' }}</div>
                <div class="col-md-8">
                    <strong>Quiz yang terdeteksi:</strong>
                    @forelse($quizzes as $quiz)
                        <span class="quiz-pill">{{ $quiz['course_fullname'] ?: ('Course '.$quiz['courseid']) }} - {{ $quiz['name'] }} (#{{ $quiz['id'] }})</span>
                    @empty
                        <span class="text-muted">Belum ada quiz yang terbaca dari mapping course/category ini.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.nilai-cbt.moodle-scan.confirm') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnContext['tahun_pelajaran_id'] }}">
        <input type="hidden" name="jalur_id" value="{{ $returnContext['jalur_id'] }}">
        <input type="hidden" name="gelombang_id" value="{{ $returnContext['gelombang_id'] }}">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table mr-2"></i>Preview Kandidat & Quiz Moodle</h3>
                <div class="card-tools">
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" class="custom-control-input" id="overwrite_existing" name="overwrite_existing" value="1">
                        <label class="custom-control-label" for="overwrite_existing">Timpa nilai CBT yang sudah ada</label>
                    </div>
                </div>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table table-bordered table-striped cbt-preview-table mb-0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center"><input type="checkbox" id="toggleAllRows"></th>
                            <th>Peserta</th>
                            <th>Moodle</th>
                            <th width="180">Nilai Ringkas</th>
                            <th>Detail Quiz</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php
                                $statusClass = match($row['status']) {
                                    'ready' => 'status-ready',
                                    'partial' => 'status-partial',
                                    default => 'status-empty',
                                };
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="row-check" name="candidate_ids[]" value="{{ $row['candidate_id'] }}" {{ $row['status'] === 'ready' ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <strong>{{ $row['nama_lengkap'] }}</strong><br>
                                    <small>NISN: <code>{{ $row['nisn'] }}</code></small><br>
                                    <small>No Reg: {{ $row['nomor_registrasi'] ?: '-' }}</small><br>
                                    <small>No Tes: {{ $row['nomor_tes'] ?: '-' }}</small>
                                </td>
                                <td>
                                    <div class="{{ $statusClass }}">{{ strtoupper($row['status']) }}</div>
                                    <small>Username: <code>{{ $row['moodle_username'] }}</code></small><br>
                                    <small>User ID: {{ $row['moodle_user_id'] ?: '-' }}</small><br>
                                    <small>Status user: {{ $row['moodle_exists'] ? 'Ditemukan di Moodle' : 'Belum ditemukan' }}</small><br>
                                    <small>Jumlah quiz: {{ $row['quiz_count'] }}</small>
                                </td>
                                <td>
                                    <div><strong>Baru dari Moodle</strong></div>
                                    <small>MTK: {{ $row['derived']['nilai_mtk'] ?? '-' }}</small><br>
                                    <small>IPA: {{ $row['derived']['nilai_ipa'] ?? '-' }}</small><br>
                                    <small>IPS: {{ $row['derived']['nilai_ips'] ?? '-' }}</small><br>
                                    <small>Inggris: {{ $row['derived']['nilai_bahasa_inggris'] ?? '-' }}</small><br>
                                    <small>Total: {{ $row['derived']['total_nilai'] ?? '-' }}</small><br>
                                    <small>Rata-rata: {{ $row['derived']['rata_rata'] ?? '-' }}</small>
                                    @if($row['existing'])
                                        <hr class="my-2">
                                        <div><strong>Data CBT Saat Ini</strong></div>
                                        <small>MTK: {{ $row['existing']['nilai_mtk'] ?? '-' }}</small><br>
                                        <small>IPA: {{ $row['existing']['nilai_ipa'] ?? '-' }}</small><br>
                                        <small>IPS: {{ $row['existing']['nilai_ips'] ?? '-' }}</small><br>
                                        <small>Inggris: {{ $row['existing']['nilai_bahasa_inggris'] ?? '-' }}</small><br>
                                        <small>Rata-rata: {{ $row['existing']['rata_rata'] ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    @forelse($row['course_summaries'] as $course)
                                        <div class="course-card {{ $course['mapped_field'] ? 'ready' : 'unmapped' }}">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $course['course_name'] }}</strong>
                                                <span class="badge badge-{{ $course['mapped_field'] ? 'primary' : 'warning' }}">
                                                    {{ $course['mapped_field'] ?: 'Belum dipetakan' }}
                                                </span>
                                            </div>
                                            <small>Rata-rata course: {{ $course['average'] ?? '-' }}</small>
                                            <div class="mt-2">
                                                @foreach($course['quizzes'] as $quiz)
                                                    <div>
                                                        <small>
                                                            <strong>{{ $quiz['quiz_name'] }}</strong>
                                                            -> {{ $quiz['score_percent'] !== null ? number_format($quiz['score_percent'], 2) : '-' }}
                                                            ({{ $quiz['state'] ?: 'unknown' }}, attempt {{ $quiz['attempt'] }})
                                                        </small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted">Belum ada data quiz Moodle.</span>
                                    @endforelse
                                </td>
                                <td>
                                    <small>{{ $row['issue'] }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.nilai-cbt.index', $returnContext) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i>Simpan Nilai CBT dari Preview Ini
                </button>
            </div>
        </div>
    </form>
</div>

<div class="cbt-save-overlay" id="cbtSaveOverlay" aria-hidden="true">
    <div class="cbt-save-card">
        <div class="cbt-save-head">
            <h4 class="mb-1"><i class="fas fa-save mr-2"></i>Menyimpan Nilai CBT dari Preview</h4>
            <div class="small text-white-50">Mohon tunggu, sistem sedang memproses data hasil scan Moodle.</div>
        </div>
        <div class="cbt-save-body">
            <div class="cbt-save-progress mb-3"><span></span></div>
            <div class="cbt-save-statusline">
                <span id="cbtSaveLiveText">Menyimpan nilai CBT hasil scan Moodle ke database PPDB...</span>
                <span class="cbt-save-chip"><i class="fas fa-check-double"></i><span id="cbtSavePercentText">0%</span></span>
            </div>
            <div class="text-muted small">
                Nilai akan disimpan ke tabel CBT setelah melewati data preview yang Anda pilih.
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('toggleAllRows')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach((checkbox) => {
        checkbox.checked = this.checked;
    });
});

let cbtSaveTicker = null;
document.querySelector('form[action$="moodle-scan/confirm"]')?.addEventListener('submit', function () {
    const overlay = document.getElementById('cbtSaveOverlay');
    if (overlay) {
        overlay.classList.add('show');
    }
    const messages = [
        'Menyimpan nilai CBT hasil scan Moodle ke database PPDB...',
        'Memeriksa baris yang dipilih dan status overwrite...',
        'Menghitung total serta rata-rata nilai CBT...',
        'Menyelesaikan penyimpanan dan menyiapkan rekap terbaru...'
    ];
    let progress = 8;
    let messageIndex = 0;
    const percentEl = document.getElementById('cbtSavePercentText');
    const textEl = document.getElementById('cbtSaveLiveText');
    if (percentEl) percentEl.textContent = progress + '%';
    if (textEl) textEl.textContent = messages[0];
    if (cbtSaveTicker) {
        clearInterval(cbtSaveTicker);
    }
    cbtSaveTicker = setInterval(function () {
        progress = Math.min(progress + Math.floor(Math.random() * 10) + 6, 94);
        messageIndex = (messageIndex + 1) % messages.length;
        if (percentEl) percentEl.textContent = progress + '%';
        if (textEl) textEl.textContent = messages[messageIndex];
    }, 650);
});
</script>
@stop
