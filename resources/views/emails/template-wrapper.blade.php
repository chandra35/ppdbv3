<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PPDB Notification' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .school-name {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            margin: 0 0 15px 0;
        }
        .content ul, .content ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        .content li {
            margin-bottom: 8px;
        }
        .content blockquote {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            margin: 15px 0;
            padding: 15px;
            font-style: italic;
        }
        .content code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #d63384;
        }
        .content h2 {
            color: #007bff;
            text-align: center;
            margin: 20px 0;
        }
        .content strong {
            color: #333;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 15px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .divider {
            border-top: 1px solid #e9ecef;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $title ?? 'Notifikasi PPDB' }}</h1>
            <p class="school-name">{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</p>
        </div>
        
        <div class="content">
            {!! $body !!}
            
            <div class="divider"></div>
            
            <center>
                <a href="{{ route('pendaftar.login') }}" class="btn">Login ke Sistem PPDB</a>
            </center>
        </div>
        
        <div class="footer">
            @if($emailSettings?->footer_text)
                <p>{!! nl2br(e($emailSettings->footer_text)) !!}</p>
                <div class="divider"></div>
            @endif
            <p>
                Email ini dikirim otomatis oleh sistem PPDB<br>
                <strong>{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong>
            </p>
            @if($settings->telepon ?? null)
                <p>📞 {{ $settings->telepon }}</p>
            @endif
            <p style="margin-top: 15px; color: #999;">
                &copy; {{ date('Y') }} PPDB {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}
            </p>
        </div>
    </div>
</body>
</html>
