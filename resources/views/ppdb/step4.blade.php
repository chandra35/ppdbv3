@extends('layouts.app')

@section('title', 'Step 4: Upload Dokumen')

@section('content')
<div style="max-width: 800px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 1rem;">Langkah 4: Upload Dokumen</h2>
        
        {{-- Progress Bar --}}
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #667eea; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
        </div>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Upload dokumen-dokumen yang diperlukan. Format: PDF, JPG, PNG (max 5MB per file)</p>
        </div>

        <form method="POST" action="{{ route('ppdb.register.step4.store') }}" enctype="multipart/form-data" id="formStep4">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                @foreach($requiredDocs as $key => $doc)
                <div class="form-group" style="border: 1px solid #e9ecef; padding: 1rem; border-radius: 8px; background: {{ $doc['required'] ? '#fff8e6' : '#f8f9fa' }};">
                    <label for="{{ $key }}" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>
                            {{ $doc['label'] }}
                            @if($doc['required'])
                            <span style="color: red;">*</span>
                            @else
                            <small style="color: #888;">(Opsional)</small>
                            @endif
                        </span>
                    </label>
                    <input type="file" 
                           id="{{ $key }}" 
                           name="{{ $key }}" 
                           accept="{{ $key === 'foto' ? '.jpg,.jpeg,.png' : '.pdf,.jpg,.jpeg,.png' }}"
                           {{ $doc['required'] ? 'required' : '' }}
                           style="margin-top: 0.5rem;">
                    <small style="color: #666; display: block; margin-top: 0.25rem;">
                        @if($key === 'foto')
                        JPG/PNG, max 2MB
                        @else
                        PDF/JPG/PNG, max 5MB
                        @endif
                    </small>
                    @error($key) <small style="color: red;">{{ $message }}</small> @enderror
                </div>
                @endforeach
            </div>

            <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 2rem 0; border: 1px solid #ffc107;">
                <p style="margin: 0; color: #856404;">
                    <strong><i class="fas fa-info-circle"></i> Catatan:</strong><br>
                    • Dokumen dengan tanda <span style="color: red;">*</span> wajib diunggah<br>
                    • Pastikan dokumen yang diunggah jelas dan dapat dibaca<br>
                    • Pas foto harus berlatar belakang polos (merah atau biru)
                </p>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step3') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Lanjut ke Review <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview uploaded files
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = input.name === 'foto' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert(`File ${file.name} terlalu besar. Maksimal ${input.name === 'foto' ? '2MB' : '5MB'}`);
                    input.value = '';
                    return;
                }
                
                // Show file name
                const label = input.previousElementSibling;
                const existingInfo = label.querySelector('.file-info');
                if (existingInfo) existingInfo.remove();
                
                const fileInfo = document.createElement('span');
                fileInfo.className = 'file-info';
                fileInfo.style.cssText = 'display: block; font-size: 0.8rem; color: #28a745; margin-top: 0.25rem;';
                fileInfo.innerHTML = `<i class="fas fa-check-circle"></i> ${file.name}`;
                label.appendChild(fileInfo);
            }
        });
    });
});
</script>
@endsection
