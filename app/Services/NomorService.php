<?php

namespace App\Services;

use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;
use App\Models\JalurPendaftaran;
use App\Models\NomorRule;
use App\Models\NomorSequence;
use App\Models\PpdbSettings;
use Illuminate\Support\Facades\DB;

class NomorService
{
    public function generateNomorRegistrasi(CalonSiswa $calonSiswa): string
    {
        if (!empty($calonSiswa->nomor_registrasi)) {
            return $calonSiswa->nomor_registrasi;
        }

        $rule = $this->resolveRule(NomorRule::JENIS_REGISTRASI, $calonSiswa);

        if (!$rule) {
            return $this->generateLegacyNomorRegistrasi($calonSiswa);
        }

        return $this->generateFromRule($rule, $calonSiswa);
    }

    public function generateNomorTes(CalonSiswa $calonSiswa): string
    {
        if (!empty($calonSiswa->nomor_tes)) {
            return $calonSiswa->nomor_tes;
        }

        $rule = $this->resolveRule(NomorRule::JENIS_TES, $calonSiswa);

        if (!$rule) {
            return $this->generateLegacyNomorTes($calonSiswa);
        }

        return $this->generateFromRule($rule, $calonSiswa);
    }

    public function preview(NomorRule $rule, ?CalonSiswa $calonSiswa = null): string
    {
        $nextNumber = max(1, (int) ($rule->nomor_awal ?: 1));
        $context = $this->buildContext($rule, $calonSiswa, $nextNumber);

        return strtr($rule->format, $context);
    }

    public function resolveRule(string $jenisNomor, CalonSiswa $calonSiswa): ?NomorRule
    {
        $gelombangId = $jenisNomor === NomorRule::JENIS_TES
            ? ($calonSiswa->gelombang_nomor_tes_id ?: $calonSiswa->gelombang_pendaftaran_id)
            : $calonSiswa->gelombang_pendaftaran_id;

        $scopeCandidates = array_filter([
            [NomorRule::SCOPE_GELOMBANG, $gelombangId],
            [NomorRule::SCOPE_JALUR, $calonSiswa->jalur_pendaftaran_id],
            [NomorRule::SCOPE_TAHUN, $calonSiswa->tahun_pelajaran_id],
            [NomorRule::SCOPE_GLOBAL, null],
        ], fn ($candidate) => $candidate[0] === NomorRule::SCOPE_GLOBAL || !empty($candidate[1]));

        foreach ($scopeCandidates as [$scopeType, $scopeId]) {
            $rule = NomorRule::with(['sequence', 'sourceRule.sequence'])
                ->where('jenis_nomor', $jenisNomor)
                ->where('scope_type', $scopeType)
                ->where('scope_id', $scopeId)
                ->where('is_active', true)
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        return null;
    }

    protected function generateFromRule(NomorRule $rule, CalonSiswa $calonSiswa): string
    {
        return DB::transaction(function () use ($rule, $calonSiswa) {
            $lockedRule = NomorRule::with(['sequence', 'sourceRule.sequence'])
                ->lockForUpdate()
                ->findOrFail($rule->id);

            $sequence = NomorSequence::lockForUpdate()
                ->firstOrCreate(
                    ['nomor_rule_id' => $lockedRule->id],
                    ['last_number' => $this->initialLastNumber($lockedRule)]
                );

            $nextNumber = $sequence->last_number + 1;

            if ($lockedRule->nomor_akhir && $nextNumber > $lockedRule->nomor_akhir) {
                throw new \RuntimeException("Range nomor untuk rule {$lockedRule->nama_rule} sudah habis.");
            }

            $nomorHasil = strtr($lockedRule->format, $this->buildContext($lockedRule, $calonSiswa, $nextNumber));

            $sequence->update([
                'last_number' => $nextNumber,
                'last_generated_value' => $nomorHasil,
                'last_generated_at' => now(),
            ]);

            return $nomorHasil;
        });
    }

    protected function initialLastNumber(NomorRule $rule): int
    {
        if ($rule->mode_counter === NomorRule::MODE_LANJUT && $rule->sourceRule?->sequence) {
            return (int) $rule->sourceRule->sequence->last_number;
        }

        return max(0, ((int) $rule->nomor_awal) - 1);
    }

    protected function buildContext(NomorRule $rule, ?CalonSiswa $calonSiswa, int $number): array
    {
        $tahunNama = $calonSiswa?->tahunPelajaran?->nama ?? date('Y') . '/' . (date('Y') + 1);
        $tahun = explode('/', $tahunNama)[0] ?? date('Y');
        $jalur = $calonSiswa?->jalurPendaftaran?->kode
            ?? strtoupper(substr($calonSiswa?->jalurPendaftaran?->nama ?? 'REG', 0, 3));
        $gelombangModel = $rule->jenis_nomor === NomorRule::JENIS_TES
            ? ($calonSiswa?->gelombangNomorTes ?: $calonSiswa?->gelombangPendaftaran)
            : $calonSiswa?->gelombangPendaftaran;
        $gelombang = 'G' . ($gelombangModel?->urutan ?? 1);

        return [
            '{PREFIX}' => strtoupper((string) ($rule->prefix ?? 'NUM')),
            '{TAHUN}' => (string) $tahun,
            '{TAHUN_SHORT}' => substr((string) $tahun, -2),
            '{JALUR}' => strtoupper((string) $jalur),
            '{GELOMBANG}' => strtoupper((string) $gelombang),
            '{NOMOR}' => str_pad((string) $number, max(1, (int) $rule->digit), '0', STR_PAD_LEFT),
        ];
    }

    protected function generateLegacyNomorRegistrasi(CalonSiswa $calonSiswa): string
    {
        if ($calonSiswa->gelombangPendaftaran) {
            return $this->generateLegacyGelombangNomorRegistrasi($calonSiswa->gelombangPendaftaran);
        }

        if ($calonSiswa->jalurPendaftaran) {
            return $this->generateLegacyJalurNomorRegistrasi($calonSiswa->jalurPendaftaran);
        }

        $settings = PpdbSettings::lockForUpdate()->first();
        if ($settings) {
            $settings->increment('nomor_registrasi_counter');
            $settings->refresh();

            return sprintf(
                '%s-%s-%05d',
                $settings->nomor_registrasi_prefix ?? 'PPDB',
                date('Y'),
                $settings->nomor_registrasi_counter
            );
        }

        $sequence = CalonSiswa::whereYear('created_at', date('Y'))->count() + 1;

        return sprintf('PPDB-%s-%05d', date('Y'), $sequence);
    }

    protected function generateLegacyGelombangNomorRegistrasi(GelombangPendaftaran $gelombang): string
    {
        $gelombang = GelombangPendaftaran::lockForUpdate()->with('jalur.tahunPelajaran')->findOrFail($gelombang->id);
        $gelombang->increment('counter_nomor');
        $gelombang->increment('kuota_terisi');

        if ($gelombang->jalur) {
            $gelombang->jalur()->increment('kuota_terisi');
        }

        $counter = str_pad((string) $gelombang->counter_nomor, 4, '0', STR_PAD_LEFT);
        $tpNama = $gelombang->jalur?->tahunPelajaran?->nama ?? date('Y');
        $tahun = explode('/', $tpNama)[0] ?? date('Y');
        $kodeJalur = $gelombang->jalur ? strtoupper(substr($gelombang->jalur->kode, 0, 3)) : 'REG';
        $prefix = $gelombang->prefix_nomor ?: 'REG';

        return "{$prefix}-{$kodeJalur}-{$tahun}-{$counter}";
    }

    protected function generateLegacyJalurNomorRegistrasi(JalurPendaftaran $jalur): string
    {
        $jalur = JalurPendaftaran::lockForUpdate()->findOrFail($jalur->id);
        $jalur->increment('counter_nomor');
        $jalur->refresh();

        $prefix = $jalur->prefix_nomor ?: 'PPDB';
        $counter = str_pad((string) $jalur->counter_nomor, 5, '0', STR_PAD_LEFT);

        return "{$prefix}-" . date('Y') . "-{$counter}";
    }

    protected function generateLegacyNomorTes(CalonSiswa $calonSiswa): string
    {
        return DB::transaction(function () use ($calonSiswa) {
            $settings = PpdbSettings::lockForUpdate()->first();
            if (!$settings) {
                throw new \RuntimeException('Pengaturan PPDB belum tersedia untuk generate nomor tes.');
            }

            $tahunNama = $calonSiswa->tahunPelajaran?->nama ?? date('Y');
            $tahun = explode('/', (string) $tahunNama)[0] ?? date('Y');
            $jalurCode = strtoupper(substr($calonSiswa->jalurPendaftaran->nama ?? 'REG', 0, 3));
            $gelombang = $calonSiswa->gelombangNomorTes ?: $calonSiswa->gelombangPendaftaran;
            $gelombangCode = 'G' . ($gelombang?->urutan ?? 1);
            $counters = $settings->nomor_tes_counter ?? [];
            $jalurKey = (string) ($calonSiswa->gelombang_nomor_tes_id ?: $calonSiswa->jalur_pendaftaran_id);
            $counter = ((int) ($counters[$jalurKey] ?? 0)) + 1;

            $counters[$jalurKey] = $counter;
            $settings->update(['nomor_tes_counter' => $counters]);

            $format = $settings->nomor_tes_format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}';
            $nomor = str_pad((string) $counter, (int) ($settings->nomor_tes_digit ?? 4), '0', STR_PAD_LEFT);

            return str_replace(
                ['{PREFIX}', '{TAHUN}', '{JALUR}', '{GELOMBANG}', '{NOMOR}'],
                [$settings->nomor_tes_prefix ?? 'NTS', $tahun, $jalurCode, $gelombangCode, $nomor],
                $format
            );
        });
    }
}
