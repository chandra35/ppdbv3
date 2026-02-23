{{-- Reusable PDF section: receives $sectionTitle, $data --}}
<div class="section">
    <div class="section-title">{{ $sectionTitle }} ({{ $data['total'] }} orang)</div>
    @include('admin.report._pdf_section_tables', ['data' => $data])
</div>
