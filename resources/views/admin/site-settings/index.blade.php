@extends('adminlte::page')

@section('title', 'Pengaturan Website')

@section('content_header')
    <h1 class="m-0"><i class="fas fa-cogs mr-2"></i>Pengaturan Website</h1>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-3">
            {{-- Navigation Tabs --}}
            <div class="card">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Menu</h3>
                </div>
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist">
                        <a class="nav-link {{ request('tab', 'general') == 'general' ? 'active' : '' }}" 
                           id="general-tab" data-toggle="pill" href="#general" role="tab">
                            <i class="fas fa-school mr-2"></i> Informasi Umum
                        </a>
                        <a class="nav-link {{ request('tab') == 'social' ? 'active' : '' }}" 
                           id="social-tab" data-toggle="pill" href="#social" role="tab">
                            <i class="fas fa-share-alt mr-2"></i> Media Sosial
                        </a>
                        <a class="nav-link {{ request('tab') == 'landing' ? 'active' : '' }}" 
                           id="landing-tab" data-toggle="pill" href="#landing" role="tab">
                            <i class="fas fa-home mr-2"></i> Halaman Landing
                        </a>
                        <a class="nav-link {{ request('tab') == 'seo' ? 'active' : '' }}" 
                           id="seo-tab" data-toggle="pill" href="#seo" role="tab">
                            <i class="fas fa-search mr-2"></i> SEO
                        </a>
                        <a class="nav-link {{ request('tab') == 'theme' ? 'active' : '' }}" 
                           id="theme-tab" data-toggle="pill" href="#theme" role="tab">
                            <i class="fas fa-palette mr-2"></i> Tema & Footer
                        </a>
                        <a class="nav-link {{ request('tab') == 'maps' ? 'active' : '' }}" 
                           id="maps-tab" data-toggle="pill" href="#maps" role="tab">
                            <i class="fas fa-map-marker-alt mr-2"></i> Lokasi & Peta
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-link mr-1"></i> Link Cepat</h3>
                </div>
                <div class="card-body p-2">
                    <a href="{{ route('admin.settings.berita.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-1">
                        <i class="fas fa-newspaper mr-1"></i> Kelola Berita
                    </a>
                    <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-outline-info btn-sm btn-block mb-1">
                        <i class="fas fa-images mr-1"></i> Kelola Slider
                    </a>
                    <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-outline-success btn-sm btn-block mb-1">
                        <i class="fas fa-calendar-alt mr-1"></i> Kelola Jadwal
                    </a>
                    <a href="{{ route('ppdb.landing') }}" target="_blank" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-external-link-alt mr-1"></i> Lihat Website
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content" id="settings-tabContent">
                {{-- General Tab --}}
                <div class="tab-pane fade {{ request('tab', 'general') == 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.general') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-school mr-1"></i> Informasi Sekolah</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Sekolah <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_sekolah" class="form-control" 
                                                   value="{{ old('nama_sekolah', $settings->nama_sekolah) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Slogan</label>
                                            <input type="text" name="slogan" class="form-control" 
                                                   value="{{ old('slogan', $settings->slogan) }}" placeholder="Tagline sekolah...">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Alamat</label>
                                    <textarea name="alamat" rows="2" class="form-control" placeholder="Alamat lengkap sekolah...">{{ old('alamat', $settings->alamat) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Telepon</label>
                                            <input type="text" name="telepon" class="form-control" 
                                                   value="{{ old('telepon', $settings->telepon) }}" placeholder="021-xxxxxxx">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" 
                                                   value="{{ old('email', $settings->email) }}" placeholder="info@sekolah.sch.id">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="url" name="website" class="form-control" 
                                                   value="{{ old('website', $settings->website) }}" placeholder="https://sekolah.sch.id">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Logo Sekolah</label>
                                            @if($settings->logo)
                                                <div class="mb-2">
                                                    <img src="{{ $settings->logo_url }}" class="img-thumbnail" style="max-height: 80px;">
                                                </div>
                                            @endif
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                                                <label class="custom-file-label" for="logo">{{ $settings->logo ? 'Ganti logo...' : 'Pilih logo...' }}</label>
                                            </div>
                                            <small class="text-muted">Rekomendasi: PNG dengan background transparan</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Favicon</label>
                                            @if($settings->favicon)
                                                <div class="mb-2">
                                                    <img src="{{ $settings->favicon_url }}" class="img-thumbnail" style="max-height: 32px;">
                                                </div>
                                            @endif
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="favicon" name="favicon" accept="image/*,.ico">
                                                <label class="custom-file-label" for="favicon">{{ $settings->favicon ? 'Ganti favicon...' : 'Pilih favicon...' }}</label>
                                            </div>
                                            <small class="text-muted">Rekomendasi: ICO atau PNG 32x32px</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Social Media Tab --}}
                <div class="tab-pane fade {{ request('tab') == 'social' ? 'show active' : '' }}" id="social" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.social') }}" method="POST">
                        @csrf
                        <div class="card card-outline card-info">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-share-alt mr-1"></i> Media Sosial</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-facebook text-primary mr-1"></i> URL Facebook</label>
                                            <input type="url" name="facebook_url" class="form-control" 
                                                   value="{{ old('facebook_url', $settings->facebook_url) }}" 
                                                   placeholder="https://facebook.com/sekolah">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-instagram text-danger mr-1"></i> URL Instagram</label>
                                            <input type="url" name="instagram_url" class="form-control" 
                                                   value="{{ old('instagram_url', $settings->instagram_url) }}" 
                                                   placeholder="https://instagram.com/sekolah">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-twitter text-info mr-1"></i> URL Twitter/X</label>
                                            <input type="url" name="twitter_url" class="form-control" 
                                                   value="{{ old('twitter_url', $settings->twitter_url) }}" 
                                                   placeholder="https://twitter.com/sekolah">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-youtube text-danger mr-1"></i> URL YouTube</label>
                                            <input type="url" name="youtube_url" class="form-control" 
                                                   value="{{ old('youtube_url', $settings->youtube_url) }}" 
                                                   placeholder="https://youtube.com/@sekolah">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-tiktok mr-1"></i> URL TikTok</label>
                                            <input type="url" name="tiktok_url" class="form-control" 
                                                   value="{{ old('tiktok_url', $settings->tiktok_url) }}" 
                                                   placeholder="https://tiktok.com/@sekolah">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fab fa-whatsapp text-success mr-1"></i> Nomor WhatsApp</label>
                                            <input type="text" name="whatsapp_number" class="form-control" 
                                                   value="{{ old('whatsapp_number', $settings->whatsapp_number) }}" 
                                                   placeholder="628123456789 (tanpa +)">
                                            <small class="text-muted">Format: 628xxx tanpa tanda + atau spasi</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h5><i class="fab fa-facebook-f text-primary mr-1"></i> Integrasi Facebook Posting</h5>
                                <p class="text-muted small">Untuk dapat memposting berita ke Facebook secara otomatis</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Facebook Page ID</label>
                                            <input type="text" name="facebook_page_id" class="form-control" 
                                                   value="{{ old('facebook_page_id', $settings->facebook_page_id) }}" 
                                                   placeholder="123456789">
                                            <small class="text-muted">ID halaman Facebook yang akan diposting</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Facebook Access Token</label>
                                            <input type="password" name="facebook_access_token" class="form-control" 
                                                   value="{{ old('facebook_access_token', $settings->facebook_access_token) }}" 
                                                   placeholder="Page Access Token">
                                            <small class="text-muted">Token akses dari Facebook Developer</small>
                                        </div>
                                    </div>
                                </div>
                                @if($facebookConfigured)
                                <div class="alert alert-success py-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Facebook terkonfigurasi!</strong>
                                    @if($facebookPageInfo)
                                        Terhubung ke: {{ $facebookPageInfo['name'] ?? 'Unknown' }}
                                    @endif
                                    <form action="{{ route('admin.settings.halaman.verify-facebook') }}" method="POST" class="d-inline ml-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-sync mr-1"></i> Verifikasi Token
                                        </button>
                                    </form>
                                </div>
                                @else
                                <div class="alert alert-warning py-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Facebook belum dikonfigurasi.</strong> Isi Page ID dan Access Token di atas.
                                </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Landing Page Tab --}}
                <div class="tab-pane fade {{ request('tab') == 'landing' ? 'show active' : '' }}" id="landing" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.landing') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card card-outline card-success">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-home mr-1"></i> Halaman Landing / Beranda</h3>
                            </div>
                            <div class="card-body">
                                <h5><i class="fas fa-flag mr-1"></i> Hero Section</h5>
                                <div class="form-group">
                                    <label>Judul Hero</label>
                                    <input type="text" name="hero_title" class="form-control" 
                                           value="{{ old('hero_title', $settings->hero_title) }}" 
                                           placeholder="Selamat Datang di PPDB">
                                </div>
                                <div class="form-group">
                                    <label>Subtitle Hero</label>
                                    <textarea name="hero_subtitle" rows="2" class="form-control" 
                                              placeholder="Deskripsi singkat tentang PPDB...">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Gambar Hero/Banner</label>
                                    @if($settings->hero_image)
                                        <div class="mb-2">
                                            <img src="{{ $settings->hero_image_url }}" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="hero_image" name="hero_image" accept="image/*">
                                        <label class="custom-file-label" for="hero_image">{{ $settings->hero_image ? 'Ganti gambar...' : 'Pilih gambar...' }}</label>
                                    </div>
                                    <small class="text-muted">Rekomendasi: 1920x600 pixel</small>
                                </div>
                                <hr>
                                <h5><i class="fas fa-info-circle mr-1"></i> Tentang Kami Section</h5>
                                <div class="form-group">
                                    <label>Konten Tentang Kami</label>
                                    <textarea name="about_content" rows="5" class="form-control" 
                                              placeholder="Ceritakan tentang sekolah...">{{ old('about_content', $settings->about_content) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Gambar Tentang Kami</label>
                                    @if($settings->about_image)
                                        <div class="mb-2">
                                            <img src="{{ $settings->about_image_url }}" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="about_image" name="about_image" accept="image/*">
                                        <label class="custom-file-label" for="about_image">{{ $settings->about_image ? 'Ganti gambar...' : 'Pilih gambar...' }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- SEO Tab --}}
                <div class="tab-pane fade {{ request('tab') == 'seo' ? 'show active' : '' }}" id="seo" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.seo') }}" method="POST">
                        @csrf
                        <div class="card card-outline card-warning">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-search mr-1"></i> Search Engine Optimization</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control" 
                                           value="{{ old('meta_title', $settings->meta_title) }}" 
                                           placeholder="PPDB Online | Nama Sekolah" maxlength="60">
                                    <small class="text-muted">Maks 60 karakter. Tampil di tab browser dan hasil pencarian Google.</small>
                                </div>
                                <div class="form-group">
                                    <label>Meta Description</label>
                                    <textarea name="meta_description" rows="2" class="form-control" 
                                              placeholder="Pendaftaran Peserta Didik Baru (PPDB) Online..." maxlength="160">{{ old('meta_description', $settings->meta_description) }}</textarea>
                                    <small class="text-muted">Maks 160 karakter. Tampil di hasil pencarian Google.</small>
                                </div>
                                <div class="form-group">
                                    <label>Meta Keywords</label>
                                    <input type="text" name="meta_keywords" class="form-control" 
                                           value="{{ old('meta_keywords', $settings->meta_keywords) }}" 
                                           placeholder="ppdb, pendaftaran, sekolah, penerimaan siswa baru">
                                    <small class="text-muted">Pisahkan dengan koma</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Theme Tab --}}
                <div class="tab-pane fade {{ request('tab') == 'theme' ? 'show active' : '' }}" id="theme" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.theme') }}" method="POST">
                        @csrf
                        <div class="card card-outline card-purple">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Tema & Footer</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Warna Primer</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="primary_color_picker" 
                                                       value="{{ old('primary_color', $settings->primary_color) ?: '#007bff' }}"
                                                       style="height: 38px; width: 60px; padding: 2px;">
                                                <input type="text" name="primary_color" id="primary_color" 
                                                       class="form-control" 
                                                       value="{{ old('primary_color', $settings->primary_color) }}" 
                                                       placeholder="#007bff">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Warna Sekunder</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="secondary_color_picker" 
                                                       value="{{ old('secondary_color', $settings->secondary_color) ?: '#6c757d' }}"
                                                       style="height: 38px; width: 60px; padding: 2px;">
                                                <input type="text" name="secondary_color" id="secondary_color" 
                                                       class="form-control" 
                                                       value="{{ old('secondary_color', $settings->secondary_color) }}" 
                                                       placeholder="#6c757d">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label>Teks Footer</label>
                                    <textarea name="footer_text" rows="2" class="form-control" 
                                              placeholder="Teks tambahan di footer...">{{ old('footer_text', $settings->footer_text) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Copyright Text</label>
                                    <input type="text" name="copyright_text" class="form-control" 
                                           value="{{ old('copyright_text', $settings->copyright_text) }}" 
                                           placeholder="&copy; 2024 Nama Sekolah. All rights reserved.">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Maps Tab --}}
                <div class="tab-pane fade {{ request('tab') == 'maps' ? 'show active' : '' }}" id="maps" role="tabpanel">
                    <form action="{{ route('admin.settings.halaman.update.maps') }}" method="POST">
                        @csrf
                        <div class="card card-outline card-danger">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi & Peta</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Google Maps Embed Code</label>
                                    <textarea name="google_maps_embed" rows="4" class="form-control" 
                                              placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>'>{{ old('google_maps_embed', $settings->google_maps_embed) }}</textarea>
                                    <small class="text-muted">Copy embed code dari Google Maps (Bagikan > Sematkan peta)</small>
                                </div>
                                @if($settings->google_maps_embed)
                                <div class="mb-3">
                                    <label class="small">Preview:</label>
                                    <div class="embed-responsive embed-responsive-16by9">
                                        {!! $settings->google_maps_embed !!}
                                    </div>
                                </div>
                                @endif
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Latitude</label>
                                            <input type="text" name="latitude" class="form-control" 
                                                   value="{{ old('latitude', $settings->latitude) }}" 
                                                   placeholder="-6.2088">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Longitude</label>
                                            <input type="text" name="longitude" class="form-control" 
                                                   value="{{ old('longitude', $settings->longitude) }}" 
                                                   placeholder="106.8456">
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">Koordinat untuk integrasi dengan peta lainnya</small>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .nav-pills .nav-link {
        border-radius: 0;
        border-left: 3px solid transparent;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
        border-left-color: #0056b3;
    }
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@stop

@section('js')
<script>
    // Color picker sync
    document.getElementById('primary_color_picker').addEventListener('input', function(e) {
        document.getElementById('primary_color').value = e.target.value;
    });
    document.getElementById('secondary_color_picker').addEventListener('input', function(e) {
        document.getElementById('secondary_color').value = e.target.value;
    });

    // File input labels
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
            e.target.nextElementSibling.textContent = fileName;
        });
    });

    // Preserve tab on page load from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        const tabElement = document.querySelector(`#${tab}-tab`);
        if (tabElement) {
            tabElement.click();
        }
    }
</script>
@stop
