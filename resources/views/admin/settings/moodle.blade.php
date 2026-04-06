@extends('adminlte::page')

@section('title', 'Integrasi Moodle')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
.moodle-label{font-size:.78rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#5b677a}
.moodle-hint{font-size:.82rem;color:#6c757d}
.moodle-sync-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:3000;padding:1rem}
.moodle-sync-overlay.show{display:flex}
.moodle-sync-card{width:min(640px,100%);background:#fff;border-radius:18px;box-shadow:0 25px 80px rgba(15,23,42,.28);overflow:hidden}
.moodle-sync-head{padding:1rem 1.25rem;background:linear-gradient(135deg,#0f766e,#2563eb);color:#fff}
.moodle-sync-body{padding:1.25rem}
.moodle-sync-log{max-height:220px;overflow:auto;background:#0f172a;color:#e2e8f0;border-radius:12px;padding:.9rem;font-size:.85rem}
.moodle-sync-log .ok{color:#86efac}
.moodle-sync-log .err{color:#fca5a5}
.moodle-sync-log .info{color:#93c5fd}
</style>
@stop

@php
    $statusClass = match($moodleStatus) {'ready' => 'success','warning' => 'warning','incomplete' => 'danger',default => 'secondary'};
    $defaultCourseIds = collect(old('moodle_default_course_ids', $settings->moodle_default_course_ids ?? array_filter([$settings->moodle_default_course_id])))->map(fn($id)=>(string)$id)->all();
    $courseNameMap = collect($moodleCoursesByCategory)->flatten(1)->keyBy('id');
    $cohortNameMap = collect($moodleCohorts ?? [])->keyBy('id');
    $passwordMode = old('moodle_password_mode', $settings->moodle_password_mode ?: 'account');
    $emailMode = old('moodle_email_mode', $settings->moodle_email_mode ?: 'account');
@endphp

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-graduation-cap"></i> Integrasi Moodle</h1></div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Pengaturan PPDB</a></li>
            <li class="breadcrumb-item active">Integrasi Moodle</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.settings.moodle.update') }}" method="POST">
            @csrf
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-plug"></i> Konfigurasi Sinkron Moodle</h3></div>
                <div class="card-body">
                    <div class="alert alert-{{ $statusClass }}">
                        <strong>Status:</strong> {{ strtoupper($moodleStatus === 'local' ? 'nonaktif' : $moodleStatus) }}<br>
                        <small>{{ $moodleStatusMessage }}</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="moodle_sync_enabled" name="moodle_sync_enabled" value="1" {{ old('moodle_sync_enabled', $settings->moodle_sync_enabled) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="moodle_sync_enabled">
                                <strong>Aktifkan Sinkron Moodle</strong>
                                <small class="text-muted d-block">Trigger bisa diatur: manual, registrasi, finalisasi, atau setelah nomor tes terbit.</small>
                            </label>
                        </div>
                    </div>
                    <div id="moodleConfigBox">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_connection_mode">Mode Koneksi Moodle</label>
                                    <select class="form-control" id="moodle_connection_mode" name="moodle_connection_mode">
                                        <option value="webservice" {{ old('moodle_connection_mode', $settings->moodle_connection_mode) === 'webservice' ? 'selected' : '' }}>Web Service Resmi Moodle</option>
                                        <option value="bridge" {{ old('moodle_connection_mode', $settings->moodle_connection_mode) === 'bridge' ? 'selected' : '' }}>Bridge Converter MAN 1 Metro</option>
                                    </select>
                                    <div class="moodle-hint mt-1">Pilih <strong>Bridge Converter</strong> jika menu web service Moodle bawaan sedang bermasalah, tetapi folder <code>/converter/ppdb</code> tersedia di server Moodle.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_sync_mode">Mode Trigger Sinkron</label>
                                    <select class="form-control" id="moodle_sync_mode" name="moodle_sync_mode">
                                        <option value="manual" {{ old('moodle_sync_mode', $settings->moodle_sync_mode) === 'manual' ? 'selected' : '' }}>Manual saja</option>
                                        <option value="on_register" {{ old('moodle_sync_mode', $settings->moodle_sync_mode) === 'on_register' ? 'selected' : '' }}>Saat registrasi</option>
                                        <option value="on_finalisasi" {{ old('moodle_sync_mode', $settings->moodle_sync_mode) === 'on_finalisasi' ? 'selected' : '' }}>Setelah finalisasi</option>
                                        <option value="on_nomor_tes" {{ old('moodle_sync_mode', $settings->moodle_sync_mode) === 'on_nomor_tes' ? 'selected' : '' }}>Setelah nomor tes terbit</option>
                                    </select>
                                    <div class="moodle-hint mt-1">Rekomendasi one day service: <strong>setelah nomor tes terbit</strong>.</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_course_role_id">Role ID Enrol</label>
                                    <input type="number" class="form-control" id="moodle_course_role_id" name="moodle_course_role_id" value="{{ old('moodle_course_role_id', $settings->moodle_course_role_id ?: 5) }}" min="1">
                                    <div class="moodle-hint mt-1">Role <strong>student</strong> di Moodle Anda terdeteksi sebagai ID <strong>5</strong>.</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="moodle-label" for="moodle_base_url">URL Moodle</label>
                            <input type="url" class="form-control" id="moodle_base_url" name="moodle_base_url" value="{{ old('moodle_base_url', $settings->moodle_base_url) }}" placeholder="https://elearning.man1metro.sch.id">
                        </div>
                        <div class="form-group">
                            <label class="moodle-label" for="moodle_webservice_token">Token / Secret Integrasi</label>
                            <textarea class="form-control" id="moodle_webservice_token" name="moodle_webservice_token" rows="3">{{ old('moodle_webservice_token', $settings->moodle_webservice_token) }}</textarea>
                            <div class="moodle-hint mt-1">Mode <strong>Web Service</strong> memakai token Moodle resmi. Mode <strong>Bridge Converter</strong> memakai secret token yang Anda simpan di <code>converter/ppdb/bridge-config.php</code>.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_lastname_template">Template Lastname</label>
                                    <input type="text" class="form-control" id="moodle_lastname_template" name="moodle_lastname_template" value="{{ old('moodle_lastname_template', $settings->moodle_lastname_template) }}" placeholder="PPDB {TAHUN} {GELOMBANG}">
                                    <div class="moodle-hint mt-1">Variabel: <code>{TAHUN}</code>, <code>{JALUR}</code>, <code>{GELOMBANG}</code>, <code>{NISN}</code>.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_password_mode">Strategi Password</label>
                                    <select class="form-control" id="moodle_password_mode" name="moodle_password_mode">
                                        <option value="account" {{ $passwordMode === 'account' ? 'selected' : '' }}>Pakai password akun pendaftar</option>
                                        <option value="custom" {{ $passwordMode === 'custom' ? 'selected' : '' }}>Password custom</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="moodle_password_custom" name="moodle_password_custom" value="{{ old('moodle_password_custom', $settings->moodle_password_custom) }}" placeholder="Contoh: man2026gel2">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_email_mode">Strategi Email</label>
                                    <select class="form-control" id="moodle_email_mode" name="moodle_email_mode">
                                        <option value="account" {{ $emailMode === 'account' ? 'selected' : '' }}>Ambil email akun pendaftar</option>
                                        <option value="domain" {{ $emailMode === 'domain' ? 'selected' : '' }}>Bangun dari domain email</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="moodle_email_domain" name="moodle_email_domain" value="{{ old('moodle_email_domain', $settings->moodle_email_domain) }}" placeholder="@man1metro.sch.id">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_default_cohort_id">Default Cohort ID</label>
                                    @if(!empty($moodleCohorts))
                                        <select class="form-control" id="moodle_default_cohort_id" name="moodle_default_cohort_id">
                                            <option value="">Pilih cohort default</option>
                                            @foreach($moodleCohorts as $cohort)
                                                <option value="{{ $cohort['id'] }}" {{ old('moodle_default_cohort_id', $settings->moodle_default_cohort_id) == $cohort['id'] ? 'selected' : '' }}>
                                                    {{ $cohort['name'] }} ({{ $cohort['id'] }}){{ !empty($cohort['membercount']) ? ' • '.$cohort['membercount'].' anggota' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control" id="moodle_default_cohort_id" name="moodle_default_cohort_id" value="{{ old('moodle_default_cohort_id', $settings->moodle_default_cohort_id) }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_default_category_id">Default Category</label>
                                    @if(!empty($moodleCategories))
                                        <select class="form-control moodle-category-select" id="moodle_default_category_id" name="moodle_default_category_id" data-course-target="#moodle_default_course_ids">
                                            <option value="">Pilih category default</option>
                                            @foreach($moodleCategoryOptions as $category)
                                                <option value="{{ $category['id'] }}" title="{{ $category['meta'] }}" {{ old('moodle_default_category_id', $settings->moodle_default_category_id) == $category['id'] ? 'selected' : '' }}>{{ $category['label'] }} ({{ $category['id'] }})</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control" id="moodle_default_category_id" name="moodle_default_category_id" value="{{ old('moodle_default_category_id', $settings->moodle_default_category_id) }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="moodle-label" for="moodle_default_course_ids">Default Course</label>
                                    <select class="form-control" id="moodle_default_course_ids" name="moodle_default_course_ids[]" multiple size="7">
                                        @foreach($moodleCoursesByCategory as $categoryId => $courses)
                                            @foreach($courses as $course)
                                                <option value="{{ $course['id'] }}" data-category="{{ $categoryId }}" {{ in_array((string) $course['id'], $defaultCourseIds, true) ? 'selected' : '' }}>{{ $course['fullname'] }} ({{ $course['id'] }})</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    <div class="moodle-hint mt-1">Bisa pilih lebih dari satu course.</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="moodle_assign_default_cohort" name="moodle_assign_default_cohort" value="1" {{ old('moodle_assign_default_cohort', $settings->moodle_assign_default_cohort) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="moodle_assign_default_cohort">Assign otomatis ke cohort default</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="moodle_enrol_default_course" name="moodle_enrol_default_course" value="1" {{ old('moodle_enrol_default_course', $settings->moodle_enrol_default_course) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="moodle_enrol_default_course">Enrol otomatis ke course default</label>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-0">
                            <strong>Username Moodle:</strong> NISN peserta<br>
                            <strong>Password Moodle:</strong> mengikuti strategi password yang dipilih<br>
                            <strong>Email Moodle:</strong> mengikuti email akun atau domain yang dipilih
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan Moodle</button>
                </div>
            </div>
        </form>

        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-project-diagram"></i> Mapping Tahun / Jalur / Gelombang</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.settings.moodle.index') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="moodle-label">Tahun Pelajaran</label>
                            <select class="form-control" name="tahun_pelajaran_id">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunPelajaranList as $tahun)
                                    <option value="{{ $tahun->id }}" {{ $selectedTahunId === $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Jalur</label>
                            <select class="form-control" name="jalur_id" id="filter_jalur_id">
                                <option value="">Semua Jalur</option>
                                @foreach($jalurList as $jalur)
                                    <option value="{{ $jalur->id }}" {{ $selectedJalurId === $jalur->id ? 'selected' : '' }}>{{ $jalur->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Gelombang</label>
                            <select class="form-control" name="gelombang_id" id="filter_gelombang_id">
                                <option value="">Semua Gelombang</option>
                                @foreach($gelombangList as $gelombang)
                                    <option value="{{ $gelombang->id }}" data-jalur="{{ $gelombang->jalur_pendaftaran_id ?? $gelombang->jalur_id }}" {{ $selectedGelombangId === $gelombang->id ? 'selected' : '' }}>{{ $gelombang->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-info"><i class="fas fa-filter"></i> Terapkan Filter</button>
                        <a href="{{ route('admin.settings.moodle.index') }}" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
                    </div>
                </form>
                <form action="{{ route('admin.settings.moodle.mappings.store') }}" method="POST" class="border rounded p-3 mb-4">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <label class="moodle-label">Tahun Pelajaran</label>
                            <select class="form-control" name="tahun_pelajaran_id">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunPelajaranList as $tahun)
                                    <option value="{{ $tahun->id }}" {{ request('tahun_pelajaran_id') === $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Jalur</label>
                            <select class="form-control" id="map_jalur_pendaftaran_id" name="jalur_pendaftaran_id">
                                <option value="">Semua Jalur</option>
                                @foreach($jalurList as $jalur)
                                    <option value="{{ $jalur->id }}" {{ request('jalur_id') === $jalur->id ? 'selected' : '' }}>{{ $jalur->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Gelombang</label>
                            <select class="form-control" id="map_gelombang_pendaftaran_id" name="gelombang_pendaftaran_id">
                                <option value="">Semua Gelombang</option>
                                @foreach($gelombangList as $gelombang)
                                    <option value="{{ $gelombang->id }}" data-jalur="{{ $gelombang->jalur_pendaftaran_id ?? $gelombang->jalur_id }}" {{ request('gelombang_id') === $gelombang->id ? 'selected' : '' }}>{{ $gelombang->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="moodle-label">Cohort ID</label>
                            @if(!empty($moodleCohorts))
                                <select class="form-control" name="moodle_cohort_id">
                                    <option value="">Pilih cohort</option>
                                    @foreach($moodleCohorts as $cohort)
                                        <option value="{{ $cohort['id'] }}">{{ $cohort['name'] }} ({{ $cohort['id'] }}){{ !empty($cohort['membercount']) ? ' • '.$cohort['membercount'].' anggota' : '' }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" name="moodle_cohort_id">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Category</label>
                            @if(!empty($moodleCategories))
                                <select class="form-control moodle-category-select" id="map_moodle_category_id" name="moodle_category_id" data-course-target="#map_moodle_course_ids">
                                    <option value="">Pilih category</option>
                                    @foreach($moodleCategoryOptions as $category)
                                        <option value="{{ $category['id'] }}" title="{{ $category['meta'] }}">{{ $category['label'] }} ({{ $category['id'] }})</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" id="map_moodle_category_id" name="moodle_category_id">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Course</label>
                            <select class="form-control" id="map_moodle_course_ids" name="moodle_course_ids[]" multiple size="6">
                                @foreach($moodleCoursesByCategory as $categoryId => $courses)
                                    @foreach($courses as $course)
                                        <option value="{{ $course['id'] }}" data-category="{{ $categoryId }}">{{ $course['fullname'] }} ({{ $course['id'] }})</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="moodle-label">Template Lastname</label>
                            <input type="text" class="form-control" name="moodle_lastname_template" placeholder="PPDB {TAHUN} {GELOMBANG}">
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Password</label>
                            <select class="form-control mb-2" name="moodle_password_mode">
                                <option value="">Ikuti default global</option>
                                <option value="account">Pakai password akun</option>
                                <option value="custom">Custom</option>
                            </select>
                            <input type="text" class="form-control" name="moodle_password_custom" placeholder="Kosong = ikut default global">
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Email</label>
                            <select class="form-control mb-2" name="moodle_email_mode">
                                <option value="">Ikuti default global</option>
                                <option value="account">Pakai email akun</option>
                                <option value="domain">Bangun dari domain</option>
                            </select>
                            <input type="text" class="form-control" name="moodle_email_domain" placeholder="@man1metro.sch.id">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-8">
                            <label class="moodle-label">Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" placeholder="Contoh: CBT REGULER Gelombang 2">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100">
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="map_is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="map_is_active">Aktif</label>
                                </div>
                                <button type="submit" class="btn btn-info btn-block"><i class="fas fa-plus"></i> Tambah Mapping</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Scope</th>
                                <th>Cohort</th>
                                <th>Category</th>
                                <th>Course</th>
                                <th>Profil User</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th style="width:140px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mappings as $mapping)
                                <tr>
                                    <td><strong>{{ $mapping->tahunPelajaran?->nama ?? 'Semua Tahun' }}</strong><br><small class="text-muted">{{ $mapping->jalurPendaftaran?->nama ?? 'Semua Jalur' }} / {{ $mapping->gelombangPendaftaran?->nama ?? 'Semua Gelombang' }}</small></td>
                                    <td>{{ $cohortNameMap->get((string) $mapping->moodle_cohort_id)['name'] ?? ($mapping->moodle_cohort_id ?: '-') }}</td>
                                    <td>{{ collect($moodleCategoryOptions)->firstWhere('id', (string) $mapping->moodle_category_id)['label'] ?? ($mapping->moodle_category_id ?: '-') }}</td>
                                    <td>@forelse($mapping->moodle_course_ids ?? [] as $courseId)<div><code>{{ $courseId }}</code> <small class="text-muted">{{ $courseNameMap->get((string) $courseId)['fullname'] ?? '' }}</small></div>@empty - @endforelse</td>
                                    <td>
                                        <div><small class="text-muted">Lastname:</small> {{ $mapping->moodle_lastname_template ?: 'ikut default' }}</div>
                                        <div><small class="text-muted">Password:</small> {{ $mapping->moodle_password_mode ?: 'ikut default' }}</div>
                                        <div><small class="text-muted">Email:</small> {{ $mapping->moodle_email_mode ?: 'ikut default' }}{{ $mapping->moodle_email_domain ? ' (' . $mapping->moodle_email_domain . ')' : '' }}</div>
                                    </td>
                                    <td><span class="badge badge-{{ $mapping->is_active ? 'success' : 'secondary' }}">{{ $mapping->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td>{{ $mapping->keterangan ?: '-' }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline-primary mb-1 btn-block btn-edit-mapping"
                                            data-id="{{ $mapping->id }}"
                                            data-tahun="{{ $mapping->tahun_pelajaran_id }}"
                                            data-jalur="{{ $mapping->jalur_pendaftaran_id }}"
                                            data-gelombang="{{ $mapping->gelombang_pendaftaran_id }}"
                                            data-cohort="{{ $mapping->moodle_cohort_id }}"
                                            data-category="{{ $mapping->moodle_category_id }}"
                                            data-course-ids="{{ implode(',', $mapping->moodle_course_ids ?? []) }}"
                                            data-lastname-template="{{ $mapping->moodle_lastname_template }}"
                                            data-password-mode="{{ $mapping->moodle_password_mode }}"
                                            data-password-custom="{{ $mapping->moodle_password_custom }}"
                                            data-email-mode="{{ $mapping->moodle_email_mode }}"
                                            data-email-domain="{{ $mapping->moodle_email_domain }}"
                                            data-keterangan="{{ $mapping->keterangan }}"
                                            data-active="{{ $mapping->is_active ? '1' : '0' }}"
                                        >Edit</button>
                                        <form action="{{ route('admin.settings.moodle.mappings.destroy', $mapping) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger btn-block" onclick="return confirm('Hapus mapping Moodle ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Belum ada mapping scope Moodle.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users-cog"></i> Manajemen Sync User Moodle</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="small-box bg-success mb-0">
                            <div class="inner">
                                <h4 class="mb-1">{{ $syncStatusSummary['synced'] }}</h4>
                                <p class="mb-0">Sudah Synced</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-secondary mb-0">
                            <div class="inner">
                                <h4 class="mb-1">{{ $syncStatusSummary['not_synced'] }}</h4>
                                <p class="mb-0">Belum Synced</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-clock"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-danger mb-0">
                            <div class="inner">
                                <h4 class="mb-1">{{ $syncStatusSummary['error'] }}</h4>
                                <p class="mb-0">Butuh Tindakan</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-info mb-0">
                            <div class="inner">
                                <h4 class="mb-1">{{ $syncStatusSummary['total'] }}</h4>
                                <p class="mb-0">Data Tampil</p>
                            </div>
                            <div class="icon"><i class="fas fa-filter"></i></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center mb-3">
                    <div class="mr-3 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="toggleAllSyncRows">
                            <label class="custom-control-label" for="toggleAllSyncRows">Pilih semua data tampil</label>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2" id="btnRefreshVisibleStatuses">
                        <i class="fas fa-search"></i> Cek Ulang Status Moodle
                    </button>
                    <button type="button" class="btn btn-success btn-sm mr-2 mb-2" id="btnSyncSelected">
                        <i class="fas fa-sync"></i> Sync Data Terpilih
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm mb-2" id="btnSyncAllVisible">
                        <i class="fas fa-layer-group"></i> Sync Semua Data Tampil
                    </button>
                </div>

                <div class="alert alert-light border">
                    <strong>Catatan kerja admin:</strong> status pada tabel ini dicek ke Moodle berdasarkan <code>username = NISN</code>. Jika username sudah ada di Moodle, sistem otomatis menandainya sebagai <strong>synced</strong>.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th style="width:36px"></th>
                                <th>Nama / NISN</th>
                                <th>Scope</th>
                                <th>Nomor Registrasi</th>
                                <th>Username Moodle</th>
                                <th>Status Sync</th>
                                <th>Keterangan</th>
                                <th style="width:110px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($syncCandidates as $candidate)
                                @php
                                    $syncStatus = $candidate->moodle_sync_status ?: 'not_synced';
                                    $syncBadgeClass = match($syncStatus) {
                                        'synced' => 'success',
                                        'error' => 'danger',
                                        default => 'secondary',
                                    };
                                    $syncLabel = match($syncStatus) {
                                        'synced' => 'Synced',
                                        'error' => 'Error',
                                        default => 'Not Synced',
                                    };
                                @endphp
                                <tr id="candidate-row-{{ $candidate->id }}">
                                    <td>
                                        <input type="checkbox" class="sync-candidate-checkbox" value="{{ $candidate->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $candidate->nama_lengkap }}</strong><br>
                                        <small class="text-muted">NISN: {{ $candidate->nisn ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $candidate->tahunPelajaran?->nama ?? '-' }}</div>
                                        <small class="text-muted">{{ $candidate->jalurPendaftaran?->nama ?? '-' }} / {{ $candidate->gelombangPendaftaran?->nama ?? '-' }}</small>
                                    </td>
                                    <td>{{ $candidate->nomor_registrasi ?: '-' }}</td>
                                    <td>
                                        <code class="candidate-moodle-username">{{ $candidate->moodle_username ?: ($candidate->nisn ?: '-') }}</code>
                                        @if($candidate->moodle_user_id)
                                            <div><small class="text-muted">ID: <span class="candidate-moodle-id">{{ $candidate->moodle_user_id }}</span></small></div>
                                        @else
                                            <div><small class="text-muted">ID: <span class="candidate-moodle-id">-</span></small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $syncBadgeClass }} candidate-sync-badge">{{ $syncLabel }}</span>
                                        <div><small class="text-muted candidate-sync-time">{{ optional($candidate->moodle_synced_at)->format('d/m/Y H:i') ?: '-' }}</small></div>
                                    </td>
                                    <td>
                                        <small class="candidate-sync-note text-muted">
                                            @if($candidate->moodle_sync_error)
                                                {{ $candidate->moodle_sync_error }}
                                            @elseif($syncStatus === 'synced')
                                                User Moodle sudah tersedia dan siap dikelola.
                                            @else
                                                Belum ditemukan di Moodle untuk username ini.
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-success btn-xs btn-block btn-sync-one" data-id="{{ $candidate->id }}" data-name="{{ e($candidate->nama_lengkap) }}">
                                            Sync Sekarang
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada pendaftar pada filter yang dipilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($syncCandidates, 'links'))
                    <div class="mt-3">
                        {{ $syncCandidates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Catatan</h3></div>
            <div class="card-body">
                <ul class="pl-3 text-muted mb-0">
                    <li>Menu Moodle sekarang muncul di sidebar <strong>Pengaturan PPDB</strong>.</li>
                    <li>Satu scope bisa diarahkan ke lebih dari satu course.</li>
                    <li>Jika category/course gagal dimuat, admin tetap bisa isi ID manual.</li>
                    <li>Mode <strong>Web Service</strong> memakai API resmi Moodle.</li>
                    <li>Mode <strong>Bridge Converter</strong> memakai endpoint kecil di folder <code>converter/ppdb</code>, tanpa menulis langsung ke database Moodle dari PPDB.</li>
                    <li>Password bisa memilih <strong>pakai password akun pendaftar</strong> atau <strong>custom</strong>.</li>
                    <li>Email bisa memilih <strong>pakai email akun pendaftar</strong> atau dibangun dari <strong>domain email</strong>.</li>
                </ul>
            </div>
        </div>
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-link"></i> Navigasi</h3></div>
            <div class="card-body">
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-sliders-h"></i> Kembali ke PPDB Settings</a>
                <a href="{{ route('admin.settings.storage.index') }}" class="btn btn-outline-dark btn-block"><i class="fab fa-google-drive"></i> Storage Dokumen</a>
            </div>
        </div>
    </div>
</div>

<div class="moodle-sync-overlay" id="moodleSyncOverlay">
    <div class="moodle-sync-card">
        <div class="moodle-sync-head">
            <h5 class="mb-1" id="moodleSyncOverlayTitle">Menyiapkan sinkronisasi Moodle</h5>
            <small id="moodleSyncOverlaySubtitle">Mohon tunggu, proses sedang berjalan.</small>
        </div>
        <div class="moodle-sync-body">
            <div class="progress mb-3" style="height: 14px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="moodleSyncOverlayBar" role="progressbar" style="width: 0%">0%</div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong id="moodleSyncOverlayStep">0 / 0 selesai</strong>
                <span class="badge badge-info" id="moodleSyncOverlayMode">Sync</span>
            </div>
            <div class="moodle-sync-log" id="moodleSyncOverlayLog"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="editMappingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editMappingForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mapping Moodle</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4"><label class="moodle-label">Tahun</label><select class="form-control" name="tahun_pelajaran_id" id="edit_tahun_pelajaran_id"><option value="">Semua Tahun</option>@foreach($tahunPelajaranList as $tahun)<option value="{{ $tahun->id }}">{{ $tahun->nama }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="moodle-label">Jalur</label><select class="form-control" name="jalur_pendaftaran_id" id="edit_jalur_pendaftaran_id"><option value="">Semua Jalur</option>@foreach($jalurList as $jalur)<option value="{{ $jalur->id }}">{{ $jalur->nama }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="moodle-label">Gelombang</label><select class="form-control" name="gelombang_pendaftaran_id" id="edit_gelombang_pendaftaran_id"><option value="">Semua Gelombang</option>@foreach($gelombangList as $gelombang)<option value="{{ $gelombang->id }}" data-jalur="{{ $gelombang->jalur_pendaftaran_id ?? $gelombang->jalur_id }}">{{ $gelombang->nama }}</option>@endforeach</select></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="moodle-label">Cohort ID</label>
                            @if(!empty($moodleCohorts))
                                <select class="form-control" name="moodle_cohort_id" id="edit_moodle_cohort_id">
                                    <option value="">Pilih cohort</option>
                                    @foreach($moodleCohorts as $cohort)
                                        <option value="{{ $cohort['id'] }}">{{ $cohort['name'] }} ({{ $cohort['id'] }}){{ !empty($cohort['membercount']) ? ' • '.$cohort['membercount'].' anggota' : '' }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input class="form-control" name="moodle_cohort_id" id="edit_moodle_cohort_id">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="moodle-label">Category</label>
                            @if(!empty($moodleCategories))
                                <select class="form-control moodle-category-select" name="moodle_category_id" id="edit_moodle_category_id" data-course-target="#edit_moodle_course_ids">
                                    <option value="">Pilih category</option>@foreach($moodleCategoryOptions as $category)<option value="{{ $category['id'] }}" title="{{ $category['meta'] }}">{{ $category['label'] }} ({{ $category['id'] }})</option>@endforeach
                                </select>
                            @else
                                <input class="form-control" name="moodle_category_id" id="edit_moodle_category_id">
                            @endif
                        </div>
                        <div class="col-md-4"><label class="moodle-label">Course</label><select class="form-control" name="moodle_course_ids[]" id="edit_moodle_course_ids" multiple size="6">@foreach($moodleCoursesByCategory as $categoryId => $courses)@foreach($courses as $course)<option value="{{ $course['id'] }}" data-category="{{ $categoryId }}">{{ $course['fullname'] }} ({{ $course['id'] }})</option>@endforeach @endforeach</select></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4"><label class="moodle-label">Template Lastname</label><input class="form-control" name="moodle_lastname_template" id="edit_moodle_lastname_template"></div>
                        <div class="col-md-4"><label class="moodle-label">Password</label><select class="form-control mb-2" name="moodle_password_mode" id="edit_moodle_password_mode"><option value="">Ikuti default global</option><option value="account">Pakai password akun</option><option value="custom">Custom</option></select><input class="form-control" name="moodle_password_custom" id="edit_moodle_password_custom"></div>
                        <div class="col-md-4"><label class="moodle-label">Email</label><select class="form-control mb-2" name="moodle_email_mode" id="edit_moodle_email_mode"><option value="">Ikuti default global</option><option value="account">Pakai email akun</option><option value="domain">Bangun dari domain</option></select><input class="form-control" name="moodle_email_domain" id="edit_moodle_email_domain"></div>
                    </div>
                    <div class="form-group mt-3"><label class="moodle-label">Keterangan</label><input class="form-control" name="keterangan" id="edit_keterangan"></div>
                    <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1"><label class="custom-control-label" for="edit_is_active">Aktif</label></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function() {
    const csrfToken = @json(csrf_token());
    const refreshStatusUrl = @json(route('admin.settings.moodle.sync.refresh-status'));
    const syncCandidateBaseUrl = @json(url('/admin/settings/moodle/sync/candidate'));
    const currentFilters = {
        tahun_pelajaran_id: @json($selectedTahunId),
        jalur_id: @json($selectedJalurId),
        gelombang_id: @json($selectedGelombangId),
    };

    @if(session('success')) toastr.success(@json(session('success'))); @endif
    @if(session('error')) toastr.error(@json(session('error'))); @endif
    @if($errors->any()) @foreach($errors->all() as $error) toastr.error(@json($error)); @endforeach @endif

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    function updateMoodleBox(){ $('#moodleConfigBox').toggle($('#moodle_sync_enabled').is(':checked')); }
    function filterGelombangByJalur(jalurSelector, gelombangSelector){ const jalurId = $(jalurSelector).val(); $(gelombangSelector).find('option').each(function(){ if(!this.value){ $(this).show(); return; } const visible = !jalurId || $(this).data('jalur') === jalurId; $(this).toggle(visible); if(!visible){ this.selected = false; } }); }
    function filterCoursesByCategory(categorySelector, courseSelector){ const categoryId = $(categorySelector).val(); $(courseSelector).find('option').each(function(){ const visible = !categoryId || String($(this).data('category')) === String(categoryId); $(this).toggle(visible); if(!visible){ this.selected = false; } }); }
    function getVisibleCandidateIds(){ return $('.sync-candidate-checkbox').map(function(){ return $(this).val(); }).get(); }
    function getSelectedCandidateIds(){ return $('.sync-candidate-checkbox:checked').map(function(){ return $(this).val(); }).get(); }
    function openOverlay(title, mode, total){
        $('#moodleSyncOverlayTitle').text(title);
        $('#moodleSyncOverlayMode').text(mode);
        $('#moodleSyncOverlaySubtitle').text('Mohon tunggu, proses sinkronisasi Moodle sedang berjalan.');
        $('#moodleSyncOverlayBar').css('width', '0%').text('0%');
        $('#moodleSyncOverlayStep').text('0 / ' + total + ' selesai');
        $('#moodleSyncOverlayLog').html('');
        $('#moodleSyncOverlay').addClass('show');
    }
    function appendOverlayLog(message, type){
        const cls = type || 'info';
        $('#moodleSyncOverlayLog').append('<div class="' + cls + '">' + message + '</div>');
        const logEl = document.getElementById('moodleSyncOverlayLog');
        logEl.scrollTop = logEl.scrollHeight;
    }
    function updateOverlayProgress(done, total){
        const percent = total > 0 ? Math.round((done / total) * 100) : 100;
        $('#moodleSyncOverlayBar').css('width', percent + '%').text(percent + '%');
        $('#moodleSyncOverlayStep').text(done + ' / ' + total + ' selesai');
    }
    function closeOverlay(delayMs){
        setTimeout(function(){ $('#moodleSyncOverlay').removeClass('show'); }, delayMs || 600);
    }
    function updateCandidateRow(candidateId, payload){
        const row = $('#candidate-row-' + candidateId);
        if(!row.length){ return; }
        const status = payload.status || 'not_synced';
        const labelMap = { synced: 'Synced', error: 'Error', not_synced: 'Not Synced' };
        const classMap = { synced: 'success', error: 'danger', not_synced: 'secondary' };
        row.find('.candidate-sync-badge')
            .removeClass('badge-success badge-danger badge-secondary')
            .addClass('badge-' + (classMap[status] || 'secondary'))
            .text(labelMap[status] || 'Not Synced');
        if(payload.moodle_user_id){
            row.find('.candidate-moodle-id').text(payload.moodle_user_id);
        }
        if(payload.username){
            row.find('.candidate-moodle-username').text(payload.username);
        }
        if(payload.synced_at){
            row.find('.candidate-sync-time').text(payload.synced_at);
        }
        if(payload.note){
            row.find('.candidate-sync-note').text(payload.note);
        } else if(status === 'synced') {
            row.find('.candidate-sync-note').text('User Moodle sudah tersedia dan siap dikelola.');
        } else if(status === 'not_synced') {
            row.find('.candidate-sync-note').text('Belum ditemukan di Moodle untuk username ini.');
        }
    }
    async function refreshStatuses(candidateIds){
        const ids = candidateIds && candidateIds.length ? candidateIds : getVisibleCandidateIds();
        if(!ids.length){
            toastr.warning('Tidak ada pendaftar yang bisa dicek.');
            return;
        }
        openOverlay('Cek Ulang Status Moodle', 'Refresh Status', ids.length);
        appendOverlayLog('Mengirim permintaan cek status ke Moodle...', 'info');
        try{
            const response = await $.post(refreshStatusUrl, {
                candidate_ids: ids,
                tahun_pelajaran_id: currentFilters.tahun_pelajaran_id,
                jalur_id: currentFilters.jalur_id,
                gelombang_id: currentFilters.gelombang_id
            });
            let done = 0;
            Object.keys(response.statuses || {}).forEach(function(candidateId){
                done += 1;
                const row = response.statuses[candidateId];
                updateCandidateRow(candidateId, {
                    status: row.status,
                    moodle_user_id: row.moodle_user_id,
                    username: row.username,
                });
                appendOverlayLog(row.username + ' -> ' + (row.exists ? 'sudah ada di Moodle' : 'belum ada di Moodle'), row.exists ? 'ok' : 'info');
                updateOverlayProgress(done, ids.length);
            });
            toastr.success(response.message || 'Status Moodle berhasil diperbarui.');
        } catch(error){
            appendOverlayLog((error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : 'Gagal mengecek status Moodle.', 'err');
            toastr.error((error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : 'Gagal mengecek status Moodle.');
        }
        closeOverlay(900);
    }
    async function syncCandidates(candidateIds){
        const ids = candidateIds && candidateIds.length ? candidateIds : getSelectedCandidateIds();
        if(!ids.length){
            toastr.warning('Pilih minimal satu pendaftar untuk disinkronkan.');
            return;
        }
        openOverlay('Sinkronisasi User Moodle', 'Bulk Sync', ids.length);
        let successCount = 0;
        let errorCount = 0;
        for(let index = 0; index < ids.length; index += 1){
            const candidateId = ids[index];
            const row = $('#candidate-row-' + candidateId);
            const candidateName = row.find('td:eq(1) strong').text().trim() || ('ID ' + candidateId);
            appendOverlayLog('Memproses ' + candidateName + '...', 'info');
            try{
                const response = await $.post(syncCandidateBaseUrl + '/' + candidateId, {});
                successCount += 1;
                updateCandidateRow(candidateId, {
                    status: 'synced',
                    moodle_user_id: response.result ? response.result.moodle_user_id : null,
                    username: response.result ? response.result.moodle_username : null,
                    synced_at: new Date().toLocaleString('id-ID'),
                    note: response.message
                });
                appendOverlayLog(candidateName + ' berhasil disinkronkan.', 'ok');
            } catch(error){
                errorCount += 1;
                const message = (error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : 'Gagal sinkron Moodle.';
                updateCandidateRow(candidateId, {
                    status: 'error',
                    note: message
                });
                appendOverlayLog(candidateName + ' gagal: ' + message, 'err');
            }
            updateOverlayProgress(index + 1, ids.length);
        }
        toastr.success('Sinkron selesai. Berhasil: ' + successCount + ', gagal: ' + errorCount + '.');
        closeOverlay(1200);
    }

    $('#moodle_sync_enabled').on('change', updateMoodleBox);
    $('#filter_jalur_id').on('change', function(){ filterGelombangByJalur('#filter_jalur_id', '#filter_gelombang_id'); });
    $('#map_jalur_pendaftaran_id').on('change', function(){ filterGelombangByJalur('#map_jalur_pendaftaran_id', '#map_gelombang_pendaftaran_id'); });
    $('#edit_jalur_pendaftaran_id').on('change', function(){ filterGelombangByJalur('#edit_jalur_pendaftaran_id', '#edit_gelombang_pendaftaran_id'); });
    $('.moodle-category-select').on('change', function(){ filterCoursesByCategory(this, $(this).data('course-target')); });
    $('#toggleAllSyncRows').on('change', function(){ $('.sync-candidate-checkbox').prop('checked', $(this).is(':checked')); });
    $('#btnRefreshVisibleStatuses').on('click', function(){ refreshStatuses(getSelectedCandidateIds().length ? getSelectedCandidateIds() : getVisibleCandidateIds()); });
    $('#btnSyncSelected').on('click', function(){ syncCandidates(getSelectedCandidateIds()); });
    $('#btnSyncAllVisible').on('click', function(){ syncCandidates(getVisibleCandidateIds()); });
    $('.btn-sync-one').on('click', function(){ syncCandidates([$(this).data('id')]); });
    $('.btn-edit-mapping').on('click', function(){
        const button = $(this);
        const courseIds = String(button.data('course-ids') || '')
            .split(',')
            .map(value => value.trim())
            .filter(Boolean);

        $('#editMappingForm').attr('action', '/admin/settings/moodle/mappings/' + button.data('id'));
        $('#edit_tahun_pelajaran_id').val(button.data('tahun') || '');
        $('#edit_jalur_pendaftaran_id').val(button.data('jalur') || '');
        filterGelombangByJalur('#edit_jalur_pendaftaran_id', '#edit_gelombang_pendaftaran_id');
        $('#edit_gelombang_pendaftaran_id').val(button.data('gelombang') || '');
        $('#edit_moodle_cohort_id').val(button.data('cohort') || '');
        $('#edit_moodle_category_id').val(button.data('category') || '').trigger('change');
        $('#edit_moodle_course_ids option').prop('selected', false);
        courseIds.forEach(function(courseId){ $('#edit_moodle_course_ids option[value=\"' + courseId + '\"]').prop('selected', true); });
        $('#edit_moodle_lastname_template').val(button.data('lastname-template') || '');
        $('#edit_moodle_password_mode').val(button.data('password-mode') || '');
        $('#edit_moodle_password_custom').val(button.data('password-custom') || '');
        $('#edit_moodle_email_mode').val(button.data('email-mode') || '');
        $('#edit_moodle_email_domain').val(button.data('email-domain') || '');
        $('#edit_keterangan').val(button.data('keterangan') || '');
        $('#edit_is_active').prop('checked', String(button.data('active')) === '1');
        $('#editMappingModal').modal('show');
    });
    filterGelombangByJalur('#filter_jalur_id', '#filter_gelombang_id'); filterGelombangByJalur('#map_jalur_pendaftaran_id', '#map_gelombang_pendaftaran_id'); filterGelombangByJalur('#edit_jalur_pendaftaran_id', '#edit_gelombang_pendaftaran_id'); filterCoursesByCategory('#moodle_default_category_id', '#moodle_default_course_ids'); filterCoursesByCategory('#map_moodle_category_id', '#map_moodle_course_ids'); filterCoursesByCategory('#edit_moodle_category_id', '#edit_moodle_course_ids'); updateMoodleBox();
});
</script>
@stop
