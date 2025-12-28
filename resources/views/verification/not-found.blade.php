<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Gagal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; background: white; border-radius: 15px; padding: 50px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="fas fa-exclamation-triangle fa-4x text-danger mb-4"></i>
        <h2 class="mb-3">Data Tidak Ditemukan</h2>
        <p class="text-muted mb-4">Dokumen yang Anda scan tidak ditemukan atau sudah tidak valid.</p>
        <a href="{{ route('ppdb.landing') }}" class="btn btn-primary">
            <i class="fas fa-home"></i> Kembali ke Halaman PPDB
        </a>
    </div>
</body>
</html>
