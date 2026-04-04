@php
    $currentRule = $rule ?? null;
    $formId = $currentRule?->id ?? 'new';
@endphp

<div class="nomor-rule-form" data-form-id="{{ $formId }}">
<input type="hidden" name="rule_id" value="{{ $currentRule->id ?? '' }}">
<div class="form-group">
    <label>Nama Rule</label>
    <input type="text" name="nama_rule" class="form-control" value="{{ old('nama_rule', $currentRule->nama_rule ?? '') }}" required>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Jenis Nomor</label>
        <select name="jenis_nomor" class="form-control rule-jenis-nomor" required>
            <option value="registrasi" {{ old('jenis_nomor', $currentRule->jenis_nomor ?? '') === 'registrasi' ? 'selected' : '' }}>Nomor Registrasi</option>
            <option value="tes" {{ old('jenis_nomor', $currentRule->jenis_nomor ?? '') === 'tes' ? 'selected' : '' }}>Nomor Tes</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Scope</label>
        <select name="scope_type" class="form-control rule-scope-type" required>
            <option value="global" {{ old('scope_type', $currentRule->scope_type ?? 'global') === 'global' ? 'selected' : '' }}>Global</option>
            <option value="tahun" {{ old('scope_type', $currentRule->scope_type ?? '') === 'tahun' ? 'selected' : '' }}>Tahun</option>
            <option value="jalur" {{ old('scope_type', $currentRule->scope_type ?? '') === 'jalur' ? 'selected' : '' }}>Jalur</option>
            <option value="gelombang" {{ old('scope_type', $currentRule->scope_type ?? '') === 'gelombang' ? 'selected' : '' }}>Gelombang</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label>Target Scope</label>
    <select name="scope_id" class="form-control rule-scope-id">
        <option value="" data-scope-type="global">Global / Tidak spesifik</option>
        <optgroup label="Tahun Pelajaran">
            @foreach($tahunPelajaranList as $tahun)
                <option value="{{ $tahun->id }}" data-scope-type="tahun" {{ old('scope_id', $currentRule->scope_id ?? '') === $tahun->id ? 'selected' : '' }}>
                    Tahun: {{ $tahun->nama }}
                </option>
            @endforeach
        </optgroup>
        <optgroup label="Jalur">
            @foreach($jalurList as $jalur)
                <option value="{{ $jalur->id }}" data-scope-type="jalur" {{ old('scope_id', $currentRule->scope_id ?? '') === $jalur->id ? 'selected' : '' }}>
                    Jalur: {{ $jalur->nama }} ({{ $jalur->tahunPelajaran?->nama ?? '-' }})
                </option>
            @endforeach
        </optgroup>
        <optgroup label="Gelombang">
            @foreach($gelombangList as $gelombang)
                <option value="{{ $gelombang->id }}" data-scope-type="gelombang" {{ old('scope_id', $currentRule->scope_id ?? '') === $gelombang->id ? 'selected' : '' }}>
                    Gelombang: {{ $gelombang->nama }} - {{ $gelombang->jalur?->nama ?? '-' }}
                </option>
            @endforeach
        </optgroup>
    </select>
    <small class="text-muted">Pilih scope sesuai level rule yang ingin dipakai.</small>
</div>

<div class="alert alert-light border suggestion-box d-none">
    <div><strong>Saran Otomatis</strong></div>
    <div class="small text-muted suggestion-summary">Pilih jenis nomor dan target scope untuk melihat saran otomatis.</div>
    <div class="small mt-1 suggestion-preview d-none">
        Nomor berikutnya yang akan terbit: <strong class="suggestion-nomor-awal"></strong>
        <span class="mx-1">|</span>
        Counter terakhir: <strong class="suggestion-counter-terakhir"></strong>
    </div>
    <div class="small suggestion-source d-none"></div>
    <div class="small suggestion-existing text-warning d-none"></div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Prefix</label>
        <input type="text" name="prefix" class="form-control rule-prefix" value="{{ old('prefix', $currentRule->prefix ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Digit</label>
        <input type="number" min="1" max="8" name="digit" class="form-control rule-digit" value="{{ old('digit', $currentRule->digit ?? 4) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Nomor Awal</label>
        <input type="number" min="1" name="nomor_awal" class="form-control rule-nomor-awal" value="{{ old('nomor_awal', $currentRule->nomor_awal ?? 1) }}" required>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nomor Akhir</label>
        <input type="number" min="1" name="nomor_akhir" class="form-control" value="{{ old('nomor_akhir', $currentRule->nomor_akhir ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>Mode Counter</label>
        <select name="mode_counter" class="form-control rule-mode-counter">
            <option value="reset" {{ old('mode_counter', $currentRule->mode_counter ?? 'reset') === 'reset' ? 'selected' : '' }}>Reset dari nomor awal</option>
            <option value="manual" {{ old('mode_counter', $currentRule->mode_counter ?? '') === 'manual' ? 'selected' : '' }}>Manual sesuai nomor awal</option>
            <option value="lanjut_rule_lain" {{ old('mode_counter', $currentRule->mode_counter ?? '') === 'lanjut_rule_lain' ? 'selected' : '' }}>Lanjut dari rule lain</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label>Source Rule</label>
    <select name="source_rule_id" class="form-control rule-source-rule">
        <option value="">Tidak ada</option>
        @foreach($availableSourceRules as $sourceRule)
            <option value="{{ $sourceRule->id }}" {{ old('source_rule_id', $currentRule->source_rule_id ?? '') === $sourceRule->id ? 'selected' : '' }}>
                {{ strtoupper($sourceRule->jenis_nomor) }} - {{ $sourceRule->nama_rule }} (Counter: {{ $sourceRule->sequence?->last_number ?? ($sourceRule->nomor_awal - 1) }})
            </option>
        @endforeach
    </select>
    <small class="text-muted">Dipakai jika mode counter adalah lanjut dari rule lain.</small>
</div>

<div class="form-group">
    <label>Format</label>
    <input type="text" name="format" class="form-control rule-format" value="{{ old('format', $currentRule->format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}') }}" required>
    <small class="text-muted">Placeholder: {PREFIX}, {TAHUN}, {TAHUN_SHORT}, {JALUR}, {GELOMBANG}, {NOMOR}</small>
</div>

<div class="form-group">
    <label>Keterangan</label>
    <textarea name="keterangan" rows="2" class="form-control">{{ old('keterangan', $currentRule->keterangan ?? '') }}</textarea>
</div>

<div class="form-group mb-0">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active_{{ $currentRule->id ?? 'new' }}" name="is_active" value="1" {{ old('is_active', $currentRule->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active_{{ $currentRule->id ?? 'new' }}">Rule aktif</label>
    </div>
</div>
</div>
