{{-- Template Form Partial --}}
<div class="row">
    <div class="col-md-12 mb-3">
        <h5>
            <i class="fas fa-envelope mr-2"></i>Template {{ $label }}
            <button type="button" class="btn btn-info btn-sm ml-2" onclick="openTestModal('{{ $type }}')">
                <i class="fas fa-paper-plane mr-1"></i> Test Kirim
            </button>
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="subject_{{ $type }}">Subject Email</label>
            <input type="text" class="form-control" id="subject_{{ $type }}" name="subject_{{ $type }}"
                value="{{ old('subject_' . $type, $settings->{'subject_' . $type} ?? $defaultTemplates['subject_' . $type] ?? '') }}"
                placeholder="{{ $defaultTemplates['subject_' . $type] ?? '' }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="template_{{ $type }}">
                <i class="fas fa-file-alt mr-1"></i>Template Body
                <small class="text-muted">(Visual Editor)</small>
            </label>
            <textarea class="form-control summernote-editor" id="template_{{ $type }}" name="template_{{ $type }}">{{ old('template_' . $type, $settings->{'template_' . $type} ?? $defaultTemplates['template_' . $type] ?? '') }}</textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header">
                <strong><i class="fas fa-tags mr-2"></i>Placeholder yang Tersedia</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small">Klik placeholder untuk menyisipkan:</p>
                @foreach($placeholders as $ph)
                    <span class="badge badge-primary placeholder-badge" 
                        onclick="insertPlaceholder('{{ $type }}', '{{ $ph }}')"
                        title="Klik untuk menyisipkan">{{ $ph }}</span>
                @endforeach
                
                <hr>
                <p class="text-muted small mb-1"><strong>Keterangan:</strong></p>
                <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
                    @if(in_array('{nama_siswa}', $placeholders))
                        <li><code>{nama_siswa}</code> - Nama calon siswa</li>
                    @endif
                    @if(in_array('{nama_sekolah}', $placeholders))
                        <li><code>{nama_sekolah}</code> - Nama sekolah</li>
                    @endif
                    @if(in_array('{tahun_pelajaran}', $placeholders))
                        <li><code>{tahun_pelajaran}</code> - Tahun pelajaran</li>
                    @endif
                    @if(in_array('{nomor_registrasi}', $placeholders))
                        <li><code>{nomor_registrasi}</code> - Nomor registrasi</li>
                    @endif
                    @if(in_array('{username}', $placeholders))
                        <li><code>{username}</code> - Username login</li>
                    @endif
                    @if(in_array('{password}', $placeholders))
                        <li><code>{password}</code> - Password login</li>
                    @endif
                    @if(in_array('{url_login}', $placeholders))
                        <li><code>{url_login}</code> - URL halaman login</li>
                    @endif
                    @if(in_array('{nama_dokumen}', $placeholders))
                        <li><code>{nama_dokumen}</code> - Nama dokumen yang perlu direvisi</li>
                    @endif
                    @if(in_array('{catatan}', $placeholders))
                        <li><code>{catatan}</code> - Catatan revisi</li>
                    @endif
                    @if(in_array('{nomor_tes}', $placeholders))
                        <li><code>{nomor_tes}</code> - Nomor peserta tes</li>
                    @endif
                    @if(in_array('{jalur_pendaftaran}', $placeholders))
                        <li><code>{jalur_pendaftaran}</code> - Jalur pendaftaran</li>
                    @endif
                </ul>
            </div>
        </div>
        
        <div class="card bg-info mt-3">
            <div class="card-body py-2">
                <small class="text-white">
                    <i class="fas fa-lightbulb mr-1"></i>
                    <strong>Tips:</strong><br>
                    • Gunakan toolbar untuk format teks<br>
                    • Klik <code class="text-warning">&lt;/&gt;</code> untuk lihat HTML<br>
                    • Klik placeholder di atas untuk sisipkan
                </small>
            </div>
        </div>
    </div>
</div>
