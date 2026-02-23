{{-- Section detail card (collapsible) --}}
{{-- Variables: $sectionNumber, $sectionColor, $sectionIcon, $sectionTitle, $sectionId, $data, $collapsed --}}
<div class="card section-card">
    <div class="card-header" data-toggle="collapse" data-target="#collapse{{ $sectionId }}" aria-expanded="{{ $collapsed ? 'false' : 'true' }}">
        <h3 class="card-title">
            <span class="section-number" style="background-color: {{ $sectionColor }};">{{ $sectionNumber }}</span>
            <i class="{{ $sectionIcon }} mr-2"></i>
            <strong>{{ $sectionTitle }}</strong>
            <span class="badge badge-dark ml-2">{{ number_format($data['total']) }} orang</span>
        </h3>
        <div class="card-tools">
            <i class="fas {{ $collapsed ? 'fa-chevron-down' : 'fa-chevron-up' }}"></i>
        </div>
    </div>
    <div id="collapse{{ $sectionId }}" class="collapse {{ $collapsed ? '' : 'show' }}">
        <div class="card-body">
            @include('admin.report._section_tables', ['data' => $data])
        </div>
    </div>
</div>
