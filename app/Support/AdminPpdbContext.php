<?php

namespace App\Support;

use App\Models\GelombangPendaftaran;
use App\Models\JalurPendaftaran;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class AdminPpdbContext
{
    public static function resolve(
        mixed $tahunId = null,
        mixed $jalurId = null,
        mixed $gelombangId = null
    ): array {
        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

        $selectedTahun = $tahunPelajarans->firstWhere('id', $tahunId)
            ?? $tahunPelajarans->firstWhere('is_active', true)
            ?? $tahunPelajarans->first();

        $jalurs = $selectedTahun
            ? JalurPendaftaran::where('tahun_pelajaran_id', $selectedTahun->id)
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get()
            : new EloquentCollection();

        $allGelombangs = $jalurs->isNotEmpty()
            ? GelombangPendaftaran::whereIn('jalur_id', $jalurs->pluck('id'))
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get()
            : new EloquentCollection();

        $explicitAllJalur = (string) $jalurId === 'all';
        $explicitAllGelombang = (string) $gelombangId === 'all';

        $selectedGelombang = null;
        if (!$explicitAllGelombang && filled($gelombangId)) {
            $selectedGelombang = $allGelombangs->firstWhere('id', $gelombangId);
        }

        $selectedJalur = null;
        if (!$explicitAllJalur && filled($jalurId)) {
            $selectedJalur = $jalurs->firstWhere('id', $jalurId);
        }

        if (!$selectedJalur && $selectedGelombang) {
            $selectedJalur = $jalurs->firstWhere('id', $selectedGelombang->jalur_id);
        }

        if (!$selectedJalur && !$explicitAllJalur) {
            $selectedJalur = self::pickDefaultJalur($jalurs);
        }

        $gelombangs = $selectedJalur
            ? $allGelombangs->where('jalur_id', $selectedJalur->id)->values()
            : $allGelombangs->values();

        if (!$selectedGelombang && !$explicitAllGelombang && $selectedJalur) {
            $selectedGelombang = self::pickDefaultGelombang($gelombangs);
        }

        if ($selectedGelombang && $selectedJalur && $selectedGelombang->jalur_id !== $selectedJalur->id) {
            $selectedGelombang = null;
        }

        return [
            'tahunPelajarans' => $tahunPelajarans,
            'selectedTahun' => $selectedTahun,
            'jalurs' => $jalurs,
            'allGelombangs' => $allGelombangs,
            'gelombangs' => $gelombangs,
            'selectedJalur' => $selectedJalur,
            'selectedGelombang' => $selectedGelombang,
            'selectedTahunIdInput' => $selectedTahun?->id,
            'selectedJalurIdInput' => $explicitAllJalur ? 'all' : ($selectedJalur?->id),
            'selectedGelombangIdInput' => $explicitAllGelombang ? 'all' : ($selectedGelombang?->id),
            'jalurFilterId' => $explicitAllJalur ? null : ($selectedJalur?->id),
            'gelombangFilterId' => $explicitAllGelombang ? null : ($selectedGelombang?->id),
            'isDefaultJalur' => !filled($jalurId) && !$explicitAllJalur,
            'isDefaultGelombang' => !filled($gelombangId) && !$explicitAllGelombang,
        ];
    }

    private static function pickDefaultJalur(Collection $jalurs): ?JalurPendaftaran
    {
        if ($jalurs->isEmpty()) {
            return null;
        }

        return $jalurs->first(function ($jalur) {
            return (bool) $jalur->is_active;
        }) ?? $jalurs->sortByDesc(function ($jalur) {
            return sprintf(
                '%s-%05d-%s',
                optional($jalur->updated_at)->format('YmdHis') ?? '00000000000000',
                (int) ($jalur->urutan ?? 0),
                optional($jalur->created_at)->format('YmdHis') ?? '00000000000000'
            );
        })->first();
    }

    private static function pickDefaultGelombang(Collection $gelombangs): ?GelombangPendaftaran
    {
        if ($gelombangs->isEmpty()) {
            return null;
        }

        return $gelombangs->first(function ($gelombang) {
            return (bool) $gelombang->is_active;
        }) ?? $gelombangs->sortByDesc(function ($gelombang) {
            return sprintf(
                '%s-%05d-%s',
                self::gelombangSortTime($gelombang),
                (int) ($gelombang->urutan ?? 0),
                optional($gelombang->created_at)->format('YmdHis') ?? '00000000000000'
            );
        })->first();
    }

    private static function gelombangSortTime(GelombangPendaftaran $gelombang): string
    {
        $tanggal = $gelombang->tanggal_tutup ?? $gelombang->tanggal_buka;
        $waktu = $gelombang->waktu_tutup ?? $gelombang->waktu_buka ?? '00:00:00';

        if (!$tanggal) {
            return optional($gelombang->updated_at)->format('YmdHis') ?? '00000000000000';
        }

        return sprintf('%s%s', str_replace('-', '', (string) $tanggal), str_replace(':', '', (string) $waktu));
    }
}
