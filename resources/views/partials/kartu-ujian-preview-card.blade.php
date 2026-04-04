<div class="card" style="width: 400px; height: 250px; margin: 0 auto; background: #fff; border: 1px solid #999; border-radius: 8px; overflow: hidden; position: relative;">
    @if($sekolahSettings && $sekolahSettings->logo)
    <div class="watermark" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100px; height: 100px; opacity: 0.12;">
        <img src="{{ asset('storage/' . $sekolahSettings->logo) }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Logo">
    </div>
    @endif

    <div class="card-header" style="border-bottom: 1px solid #ccc; padding: 8px 12px; background: #fff;">
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td class="school-name" style="color: #333; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                    {{ \Illuminate\Support\Str::limit($sekolahSettings->nama_sekolah ?? config('app.name'), 30) }}
                </td>
                <td style="text-align: right;">
                    <span class="card-type" style="color: #666; font-size: 9px; border: 1px solid #999; padding: 2px 6px; border-radius: 3px;">KARTU TES PPDB</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="card-body" style="padding: 10px 12px;">
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td class="photo-cell" style="width: 80px; vertical-align: top; padding-right: 10px;">
                    <div class="photo-box" style="width: 75px; height: 100px; border: 1px solid #999; border-radius: 4px; overflow: hidden; background: #fff;">
                        @if($fotoUrl)
                            <img src="{{ $fotoUrl }}" style="width: 75px; height: 100px; object-fit: cover;" alt="Foto">
                        @else
                            <div class="no-photo" style="color: #999; font-size: 10px; text-align: center; padding-top: 35px;">Pas Foto</div>
                        @endif
                    </div>
                </td>
                <td class="info-cell" style="vertical-align: top;">
                    <div class="nomor-tes-box" style="border: 1px solid #999; border-radius: 4px; padding: 5px; text-align: center; margin-bottom: 8px;">
                        <div class="nomor-tes-label" style="color: #666; font-size: 8px; text-transform: uppercase; letter-spacing: 1px;">Nomor Tes</div>
                        <div class="nomor-tes-value" style="color: #333; font-size: 16px; font-weight: bold; letter-spacing: 1px;">{{ $previewCalonSiswa->nomor_tes ?: '-' }}</div>
                    </div>

                    <table class="data-table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 8px;">
                        <tr>
                            <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">Nama</td>
                            <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                            <td class="data-value nama-value" style="font-weight: bold; color: #333; font-size: 9px; text-transform: uppercase; text-align: left;">{{ $previewCalonSiswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">NISN</td>
                            <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                            <td class="data-value" style="font-weight: bold; color: #333; font-size: 9px; text-align: left;">{{ $previewCalonSiswa->nisn }}</td>
                        </tr>
                        <tr>
                            <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">TTL</td>
                            <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                            <td class="data-value" style="font-weight: bold; color: #333; font-size: 9px; text-align: left;">
                                {{ $previewCalonSiswa->tempat_lahir ?? '-' }}, {{ $previewCalonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($previewCalonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                        @if($previewCalonSiswa->jalurPendaftaran?->pilihan_program_aktif && $previewCalonSiswa->pilihan_program)
                        <tr>
                            <td class="data-label" style="width: 40px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">Program</td>
                            <td class="data-separator" style="width: 8px; color: #666; font-size: 9px; vertical-align: top; text-align: left;">:</td>
                            <td class="data-value" style="font-weight: bold; color: #333; font-size: 9px; text-align: left;">{{ $previewCalonSiswa->pilihan_program }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="password-box" style="border: 1px dashed #999; border-radius: 4px; padding: 5px 8px; display: inline-block;">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="password-label" style="color: #666; font-size: 9px; padding-right: 8px;">Password:</td>
                                <td class="password-value" style="color: #c0392b; font-size: 12px; font-weight: bold; letter-spacing: 2px; font-family: Consolas, monospace;">{{ $password }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="card-footer" style="position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #ccc; padding: 6px 12px; background: #fff;">
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr>
                <td>
                    <span class="year-badge" style="border: 1px solid #999; color: #333; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold;">
                        {{ $previewCalonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ (($previewCalonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1) }}
                    </span>
                </td>
                <td class="footer-center" style="text-align: center; color: #666; font-size: 9px;">{{ $previewCalonSiswa->jalurPendaftaran->nama ?? 'Jalur Umum' }}</td>
                <td class="footer-right" style="text-align: right; color: #999; font-size: 8px;">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    @php
        $hash = $previewCalonSiswa->getOrGenerateHash();
        $verifyUrl = route('verify.bukti', $hash);
    @endphp
    <div style="position: absolute; bottom: 28px; left: 8px; width: 65px; height: 65px; background: #fff; border: 1px solid #ccc; border-radius: 3px; padding: 3px; z-index: 10;">
        {!! QrCode::format('svg')->size(60)->margin(0)->generate($verifyUrl) !!}
    </div>
</div>
