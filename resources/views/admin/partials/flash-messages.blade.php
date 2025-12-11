{{-- Admin Flash Messages Partial --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show py-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show py-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-info-circle"></i> {{ session('info') }}
    </div>
@endif
