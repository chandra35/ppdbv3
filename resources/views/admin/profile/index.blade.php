@extends('adminlte::page')

@section('title', 'Profil Saya')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Profile Photo & Info -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <div class="profile-photo-container" onclick="document.getElementById('photoInput').click()">
                            @if($user->photo)
                                <img class="profile-user-img img-fluid img-circle" 
                                     id="profileImage"
                                     src="{{ asset('storage/' . $user->photo) }}" 
                                     alt="User profile picture">
                            @else
                                @php
                                    $initials = strtoupper(substr($user->name, 0, 1));
                                    $bgColor = '3c8dbc';
                                    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user->name) . 
                                                 "&background=" . $bgColor . 
                                                 "&color=fff&size=256&bold=true&format=svg";
                                @endphp
                                <img class="profile-user-img img-fluid img-circle" 
                                     id="profileImage"
                                     src="{{ $avatarUrl }}" 
                                     alt="User avatar"
                                     onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;256&quot; height=&quot;256&quot;><rect width=&quot;256&quot; height=&quot;256&quot; fill=&quot;%23{{ $bgColor }}&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; dominant-baseline=&quot;middle&quot; text-anchor=&quot;middle&quot; font-family=&quot;Arial, sans-serif&quot; font-size=&quot;100&quot; fill=&quot;white&quot; font-weight=&quot;bold&quot;>{{ $initials }}</text></svg>'">
                            @endif
                            <div class="photo-overlay">
                                <i class="fas fa-camera"></i>
                                <div class="overlay-text">Klik untuk ganti</div>
                            </div>
                        </div>
                        <input type="file" id="photoInput" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                    </div>

                    <h3 class="profile-username text-center mt-4">{{ $user->name }}</h3>

                    <p class="text-muted text-center mb-3">
                        @if($user->roles->isNotEmpty())
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ $role->display_name }}</span>
                            @endforeach
                        @else
                            <span class="badge badge-secondary">User</span>
                        @endif
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b><i class="fas fa-envelope mr-2 text-primary"></i> Email</b> 
                            <span class="float-right">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-phone mr-2 text-success"></i> No. HP</b> 
                            <span class="float-right">
                                @if($user->phone)
                                    {{ $user->phone }}
                                @else
                                    <span class="text-muted">Belum diisi</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b><i class="fas fa-calendar mr-2 text-info"></i> Bergabung</b> 
                            <span class="float-right">{{ $user->created_at->format('d M Y') }}</span>
                        </li>
                    </ul>
                    
                    @if($user->photo)
                    <button type="button" class="btn btn-danger btn-sm btn-block mt-3" onclick="deletePhoto()">
                        <i class="fas fa-trash-alt"></i> Hapus Foto Profil
                    </button>
                    @endif
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Klik foto di atas untuk mengganti
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Info & Password -->
        <div class="col-md-8">
            <!-- Edit Profile Info -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit"></i> Informasi Profil</h3>
                </div>
                <form action="{{ route('admin.profile.updateProfile') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                            Pastikan data yang Anda masukkan benar dan valid. Data ini akan digunakan untuk keperluan administrasi.
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" 
                                           placeholder="Masukkan nama lengkap"
                                           required>
                                </div>
                                @error('name')
                                    <span class="text-danger"><small>{{ $message }}</small></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
                                           placeholder="email@example.com"
                                           required>
                                </div>
                                @error('email')
                                    <span class="text-danger"><small>{{ $message }}</small></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="phone" class="col-sm-3 col-form-label">
                                No. HP
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           placeholder="08xxxxxxxxxx">
                                </div>
                                @error('phone')
                                    <span class="text-danger"><small>{{ $message }}</small></span>
                                @enderror
                                <small class="form-text text-muted">Contoh: 08123456789</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info float-right">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key"></i> Ubah Password</h3>
                </div>
                <form action="{{ route('admin.profile.updatePassword') }}" method="POST" id="passwordForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="callout callout-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Keamanan</h5>
                            Gunakan password yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan simbol. Minimal 8 karakter.
                        </div>

                        <div class="form-group row">
                            <label for="current_password" class="col-sm-3 col-form-label">
                                Password Lama <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           placeholder="Masukkan password lama"
                                           required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="icon_current_password"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('current_password')
                                    <span class="text-danger"><small>{{ $message }}</small></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-sm-3 col-form-label">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Masukkan password baru"
                                           required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="icon_password"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <span class="text-danger"><small>{{ $message }}</small></span>
                                @enderror
                                <small class="form-text text-muted">Minimal 8 karakter</small>
                                
                                <!-- Password Strength Indicator -->
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="passwordStrengthText" class="text-muted"></small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password_confirmation" class="col-sm-3 col-form-label">
                                Konfirmasi Password <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control"
                                           placeholder="Ulangi password baru"
                                           required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye" id="icon_password_confirmation"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="passwordMatch" class="form-text"></small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crop Image -->
<div class="modal fade" id="cropModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-crop-alt"></i> Crop & Edit Foto Profil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="img-container">
                            <img id="imageToCrop" src="" alt="Image to crop" style="max-width: 100%;">
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info" onclick="cropper.zoom(0.1)" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="cropper.zoom(-0.1)" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="cropper.rotate(-90)" title="Rotate Left">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="cropper.rotate(90)" title="Rotate Right">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="cropper.reset()" title="Reset">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        
                        <div class="btn-group btn-group-sm ml-2" role="group">
                            <button type="button" class="btn btn-secondary" onclick="setAspectRatio(1)" title="Square 1:1">
                                <i class="fas fa-square"></i> 1:1
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="setAspectRatio(4/3)" title="4:3">
                                <i class="fas fa-image"></i> 4:3
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="setAspectRatio(16/9)" title="16:9">
                                <i class="fas fa-image"></i> 16:9
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="setAspectRatio(NaN)" title="Free">
                                <i class="fas fa-expand"></i> Free
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="uploadCroppedImage()">
                    <i class="fas fa-upload"></i> Upload Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Mengupload foto...</p>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .profile-photo-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    
    .profile-photo-container:hover .photo-overlay {
        opacity: 1;
    }
    
    .photo-overlay {
        position: absolute;
        top: 3px;
        left: 3px;
        right: 3px;
        bottom: 3px;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .photo-overlay i {
        color: white;
        font-size: 2rem;
        margin-bottom: 5px;
    }
    
    .overlay-text {
        color: white;
        font-size: 0.75rem;
        font-weight: 500;
        text-align: center;
    }
    
    .img-container {
        max-height: 400px;
        overflow: hidden;
    }
    
    .img-container img {
        display: block;
        max-width: 100%;
    }
    
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .loading-spinner {
        text-align: center;
        color: white;
    }
    
    .loading-spinner i {
        font-size: 48px;
        color: #28a745;
    }
    
    .loading-spinner p {
        margin-top: 15px;
        font-size: 16px;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let cropper;

// Initialize photo upload
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.match('image.*')) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const image = document.getElementById('imageToCrop');
            image.src = event.target.result;
            
            // Show modal
            $('#cropModal').modal('show');
            
            // Initialize cropper after modal is shown
            $('#cropModal').on('shown.bs.modal', function () {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            });
        };
        reader.readAsDataURL(file);
    }
});

// Set aspect ratio
function setAspectRatio(ratio) {
    if (cropper) {
        cropper.setAspectRatio(ratio);
    }
}

// Upload cropped image
function uploadCroppedImage() {
    if (!cropper) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Cropper belum diinisialisasi!',
            confirmButtonColor: '#d33'
        });
        return;
    }
    
    // Show loading
    document.getElementById('loadingOverlay').style.display = 'flex';
    
    cropper.getCroppedCanvas().toBlob(function(blob) {
        const formData = new FormData();
        formData.append('photo', blob, 'profile.jpg');
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');
        
        fetch('{{ route("admin.profile.updatePhoto") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('loadingOverlay').style.display = 'none';
            
            if (data.success) {
                // Update profile image
                document.getElementById('profileImage').src = data.photo_url + '?t=' + new Date().getTime();
                
                // Close modal
                $('#cropModal').modal('hide');
                
                // Reset file input
                document.getElementById('photoInput').value = '';
                
                // Show success toast
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Gagal mengupload foto!',
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            document.getElementById('loadingOverlay').style.display = 'none';
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat mengupload foto!',
                confirmButtonColor: '#d33'
            });
        });
    });
}

// Delete photo
function deletePhoto() {
    Swal.fire({
        title: 'Hapus Foto Profil?',
        text: "Foto profil Anda akan dihapus dan diganti dengan avatar default.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            fetch('{{ route("admin.profile.deletePhoto") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('loadingOverlay').style.display = 'none';
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal menghapus foto!',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                document.getElementById('loadingOverlay').style.display = 'none';
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat menghapus foto!',
                    confirmButtonColor: '#d33'
                });
            });
        }
    });
}

// Show alert (for session messages)
function showAlert(type, message) {
    const icon = type === 'success' ? 'success' : 'error';
    Swal.fire({
        icon: icon,
        title: type === 'success' ? 'Berhasil!' : 'Gagal!',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('icon_' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let strength = 0;
    let text = '';
    let color = '';
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthText.textContent = '';
        return;
    }
    
    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Character type checks
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Set strength level
    switch(true) {
        case (strength <= 2):
            text = 'Lemah';
            color = 'bg-danger';
            break;
        case (strength <= 4):
            text = 'Sedang';
            color = 'bg-warning';
            break;
        case (strength <= 5):
            text = 'Kuat';
            color = 'bg-info';
            break;
        default:
            text = 'Sangat Kuat';
            color = 'bg-success';
    }
    
    strengthBar.style.width = (strength / 6 * 100) + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = 'Kekuatan Password: ' + text;
    strengthText.className = 'text-' + color.replace('bg-', '');
});

// Password match checker
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    const matchText = document.getElementById('passwordMatch');
    
    if (confirmation === '') {
        matchText.textContent = '';
        return;
    }
    
    if (password === confirmation) {
        matchText.textContent = '✓ Password cocok';
        matchText.className = 'form-text text-success';
    } else {
        matchText.textContent = '✗ Password tidak cocok';
        matchText.className = 'form-text text-danger';
    }
});

// Clean up cropper on modal close
$('#cropModal').on('hidden.bs.modal', function () {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

// Show session messages as toast on page load
$(document).ready(function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    @endif
    
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session("error") }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    @endif
});
</script>
@stop
