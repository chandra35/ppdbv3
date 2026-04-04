@extends('adminlte::page')

@section('title', 'Pengaturan Penomoran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-sort-numeric-up"></i> Pengaturan Penomoran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Pengaturan PPDB</a></li>
                <li class="breadcrumb-item active">Penomoran</li>
            </ol>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const suggestUrl = @json(route('admin.settings.nomor-rules.suggest'));
    const scopePlaceholders = {
        global: 'Global / Tidak spesifik',
        tahun: '-- Pilih Tahun Pelajaran --',
        jalur: '-- Pilih Jalur --',
        gelombang: '-- Pilih Gelombang --'
    };

    function syncScopeOptions(form) {
        const scopeType = form.querySelector('.rule-scope-type').value;
        const scopeSelect = form.querySelector('.rule-scope-id');
        const currentValue = scopeSelect.value;
        let hasVisibleSelected = false;

        Array.from(scopeSelect.options).forEach(function (option) {
            const optionScope = option.dataset.scopeType || '';
            const visible = scopeType === 'global'
                ? optionScope === 'global'
                : optionScope === scopeType;

            option.hidden = !visible;

            if (!visible && option.selected) {
                option.selected = false;
            }

            if (visible && option.value === currentValue) {
                hasVisibleSelected = true;
            }
        });

        const placeholder = scopeSelect.querySelector('option[value=""]');
        if (placeholder) {
            placeholder.textContent = scopePlaceholders[scopeType] || 'Pilih Target Scope';
            placeholder.hidden = false;
            if (!hasVisibleSelected) {
                scopeSelect.value = '';
            }
        }
    }

    function applySuggestion(form, payload) {
        const box = form.querySelector('.suggestion-box');
        const summary = form.querySelector('.suggestion-summary');
        const preview = form.querySelector('.suggestion-preview');
        const source = form.querySelector('.suggestion-source');
        const existing = form.querySelector('.suggestion-existing');

        form.querySelector('.rule-prefix').value = payload.prefix || form.querySelector('.rule-prefix').value;
        form.querySelector('.rule-digit').value = payload.digit || form.querySelector('.rule-digit').value;
        form.querySelector('.rule-format').value = payload.format || form.querySelector('.rule-format').value;
        form.querySelector('.rule-nomor-awal').value = payload.nomor_awal || form.querySelector('.rule-nomor-awal').value;

        if (payload.source_rule_id) {
            form.querySelector('.rule-mode-counter').value = payload.mode_counter;
            form.querySelector('.rule-source-rule').value = payload.source_rule_id;
        }

        box.classList.remove('d-none');
        summary.textContent = 'Form diisi dengan saran otomatis berdasarkan counter jalur, gelombang, rule, dan data pendaftar yang sudah ada.';
        preview.classList.remove('d-none');
        source.classList.remove('d-none');

        form.querySelector('.suggestion-nomor-awal').textContent = payload.nomor_awal;
        form.querySelector('.suggestion-counter-terakhir').textContent = payload.counter_terakhir;
        source.textContent = 'Acuan: ' + payload.source_label;

        if (payload.existing_rule) {
            existing.classList.remove('d-none');
            existing.textContent = 'Sudah ada rule pada scope ini: ' + payload.existing_rule.nama_rule + ' (counter ' + payload.existing_rule.last_number + ').';
        } else {
            existing.classList.add('d-none');
            existing.textContent = '';
        }
    }

    function requestSuggestion(form) {
        const jenis = form.querySelector('.rule-jenis-nomor').value;
        const scopeType = form.querySelector('.rule-scope-type').value;
        const scopeId = form.querySelector('.rule-scope-id').value;
        const currentRuleId = form.querySelector('input[name="rule_id"]').value;

        if (scopeType !== 'global' && !scopeId) {
            return;
        }

        const params = new URLSearchParams({
            jenis_nomor: jenis,
            scope_type: scopeType,
        });

        if (scopeId) {
            params.append('scope_id', scopeId);
        }

        if (currentRuleId) {
            params.append('rule_id', currentRuleId);
        }

        fetch(suggestUrl + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(response => {
            if (response.success && response.data) {
                applySuggestion(form, response.data);
            }
        })
        .catch(() => {});
    }

    document.querySelectorAll('.nomor-rule-form').forEach(function (form) {
        ['.rule-jenis-nomor', '.rule-scope-type', '.rule-scope-id'].forEach(function (selector) {
            const element = form.querySelector(selector);
            if (element) {
                element.addEventListener('change', function () {
                    if (selector === '.rule-scope-type') {
                        syncScopeOptions(form);
                    }
                    requestSuggestion(form);
                });
            }
        });

        syncScopeOptions(form);
        requestSuggestion(form);
    });
});
</script>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="alert alert-info">
        <strong>Catatan aman:</strong> rule penomoran baru hanya dipakai untuk data yang belum memiliki nomor.
        Nomor pendaftar lama tetap utuh dan tidak di-generate ulang.
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Daftar Rule Aktif / Tersimpan</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama Rule</th>
                                <th>Jenis</th>
                                <th>Scope</th>
                                <th>Format</th>
                                <th>Counter</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td>
                                        <strong>{{ $rule->nama_rule }}</strong>
                                        @if($rule->sourceRule)
                                            <div><small class="text-muted">Lanjut dari: {{ $rule->sourceRule->nama_rule }}</small></div>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($rule->jenis_nomor) }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ strtoupper($rule->scope_type) }}</span>
                                        <div><small>{{ $rule->scope_label }}</small></div>
                                    </td>
                                    <td>
                                        <code>{{ $rule->format }}</code>
                                        <div><small>Prefix: {{ $rule->prefix ?: '-' }}, Digit: {{ $rule->digit }}</small></div>
                                    </td>
                                    <td>
                                        <strong>{{ $rule->sequence?->last_number ?? ($rule->nomor_awal - 1) }}</strong>
                                        <div><small>{{ $rule->sequence?->last_generated_value ?? 'Belum ada' }}</small></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button type="button"
                                            class="btn btn-xs btn-primary"
                                            data-toggle="modal"
                                            data-target="#editRuleModal{{ $rule->id }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.settings.nomor-rules.destroy', $rule) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"
                                                onclick="return confirm('Hapus rule ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada rule penomoran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Tambah Rule Baru</h3>
                </div>
                <form action="{{ route('admin.settings.nomor-rules.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('admin.settings.nomor-rules.partials.form', ['rule' => null])
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Simpan Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($rules as $rule)
        <div class="modal fade" id="editRuleModal{{ $rule->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.settings.nomor-rules.update', $rule) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Rule: {{ $rule->nama_rule }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @include('admin.settings.nomor-rules.partials.form', ['rule' => $rule])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@stop
