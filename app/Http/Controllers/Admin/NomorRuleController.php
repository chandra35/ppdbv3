<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GelombangPendaftaran;
use App\Models\JalurPendaftaran;
use App\Models\CalonSiswa;
use App\Models\NomorRule;
use App\Models\PpdbSettings;
use App\Models\TahunPelajaran;
use App\Services\NomorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NomorRuleController extends Controller
{
    public function __construct(private readonly NomorService $nomorService)
    {
    }

    public function index()
    {
        $rules = NomorRule::with(['sequence', 'sourceRule', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->orderBy('jenis_nomor')
            ->orderBy('scope_type')
            ->orderBy('nama_rule')
            ->get();

        $tahunPelajaranList = TahunPelajaran::orderByDesc('is_active')->orderByDesc('nama')->get();
        $jalurList = JalurPendaftaran::with('tahunPelajaran')->orderBy('urutan')->get();
        $gelombangList = GelombangPendaftaran::with('jalur.tahunPelajaran')->orderBy('urutan')->get();
        $availableSourceRules = NomorRule::with('sequence')->orderBy('jenis_nomor')->orderBy('nama_rule')->get();

        return view('admin.settings.nomor-rules.index', compact(
            'rules',
            'tahunPelajaranList',
            'jalurList',
            'gelombangList',
            'availableSourceRules'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRule($request);

        NomorRule::create($validated);

        return redirect()->route('admin.settings.nomor-rules.index')
            ->with('success', 'Rule penomoran berhasil ditambahkan.');
    }

    public function suggest(Request $request)
    {
        $validated = $request->validate([
            'jenis_nomor' => ['required', Rule::in([NomorRule::JENIS_REGISTRASI, NomorRule::JENIS_TES])],
            'scope_type' => ['required', Rule::in([
                NomorRule::SCOPE_GLOBAL,
                NomorRule::SCOPE_TAHUN,
                NomorRule::SCOPE_JALUR,
                NomorRule::SCOPE_GELOMBANG,
            ])],
            'scope_id' => 'nullable|uuid',
            'rule_id' => 'nullable|uuid',
        ]);

        $target = $this->resolveTargetContext($validated['scope_type'], $validated['scope_id'] ?? null);
        $defaults = $this->resolveDefaults($validated['jenis_nomor'], $target);
        $currentRuleId = $validated['rule_id'] ?? null;
        $suggestion = $this->buildSuggestion($validated['jenis_nomor'], $validated['scope_type'], $target, $currentRuleId);

        return response()->json([
            'success' => true,
            'data' => array_merge($defaults, $suggestion),
        ]);
    }

    public function update(Request $request, NomorRule $nomorRule)
    {
        $validated = $this->validateRule($request, $nomorRule);

        $nomorRule->update($validated);

        return redirect()->route('admin.settings.nomor-rules.index')
            ->with('success', 'Rule penomoran berhasil diperbarui.');
    }

    public function destroy(NomorRule $nomorRule)
    {
        $nomorRule->delete();

        return redirect()->route('admin.settings.nomor-rules.index')
            ->with('success', 'Rule penomoran berhasil dihapus.');
    }

    protected function validateRule(Request $request, ?NomorRule $rule = null): array
    {
        $validated = $request->validate([
            'nama_rule' => 'required|string|max:100',
            'jenis_nomor' => ['required', Rule::in([NomorRule::JENIS_REGISTRASI, NomorRule::JENIS_TES])],
            'scope_type' => ['required', Rule::in([
                NomorRule::SCOPE_GLOBAL,
                NomorRule::SCOPE_TAHUN,
                NomorRule::SCOPE_JALUR,
                NomorRule::SCOPE_GELOMBANG,
            ])],
            'scope_id' => 'nullable|uuid',
            'prefix' => 'nullable|string|max:30',
            'format' => 'required|string|max:150',
            'digit' => 'required|integer|min:1|max:8',
            'nomor_awal' => 'required|integer|min:1|max:99999999',
            'nomor_akhir' => 'nullable|integer|gte:nomor_awal|max:99999999',
            'mode_counter' => ['required', Rule::in([
                NomorRule::MODE_RESET,
                NomorRule::MODE_MANUAL,
                NomorRule::MODE_LANJUT,
            ])],
            'source_rule_id' => 'nullable|uuid|different:id',
            'keterangan' => 'nullable|string',
        ]);

        if ($validated['scope_type'] === NomorRule::SCOPE_GLOBAL) {
            $validated['scope_id'] = null;
        }

        if ($validated['mode_counter'] !== NomorRule::MODE_LANJUT) {
            $validated['source_rule_id'] = null;
        }

        $validated['is_active'] = $request->has('is_active');

        return $validated;
    }

    protected function resolveTargetContext(string $scopeType, ?string $scopeId): array
    {
        $tahun = null;
        $jalur = null;
        $gelombang = null;

        if ($scopeType === NomorRule::SCOPE_TAHUN && $scopeId) {
            $tahun = TahunPelajaran::find($scopeId);
        }

        if ($scopeType === NomorRule::SCOPE_JALUR && $scopeId) {
            $jalur = JalurPendaftaran::with('tahunPelajaran')->find($scopeId);
            $tahun = $jalur?->tahunPelajaran;
        }

        if ($scopeType === NomorRule::SCOPE_GELOMBANG && $scopeId) {
            $gelombang = GelombangPendaftaran::with('jalur.tahunPelajaran')->find($scopeId);
            $jalur = $gelombang?->jalur;
            $tahun = $jalur?->tahunPelajaran;
        }

        if ($scopeType === NomorRule::SCOPE_GLOBAL) {
            $tahun = TahunPelajaran::getAktif();
        }

        return compact('tahun', 'jalur', 'gelombang');
    }

    protected function resolveDefaults(string $jenisNomor, array $target): array
    {
        $settings = PpdbSettings::getActive();
        $jalur = $target['jalur'];
        $gelombang = $target['gelombang'];

        $jalurCode = $jalur?->kode ?: ($jalur ? strtoupper(substr($jalur->nama, 0, 3)) : null);
        $prefix = $gelombang?->prefix_nomor
            ?: $jalur?->prefix_nomor
            ?: ($jenisNomor === NomorRule::JENIS_TES
                ? ($settings->nomor_tes_prefix ?? 'TES')
                : ($settings->nomor_registrasi_prefix ?? 'REG'));

        $format = $jenisNomor === NomorRule::JENIS_TES
            ? ($settings->nomor_tes_format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}')
            : '{PREFIX}-{JALUR}-{TAHUN}-{NOMOR}';

        return [
            'prefix' => strtoupper((string) $prefix),
            'digit' => $jenisNomor === NomorRule::JENIS_TES ? (int) ($settings->nomor_tes_digit ?? 4) : 4,
            'format' => $format,
            'jalur_code' => strtoupper((string) $jalurCode),
        ];
    }

    protected function buildSuggestion(string $jenisNomor, string $scopeType, array $target, ?string $currentRuleId = null): array
    {
        $tahun = $target['tahun'];
        $jalur = $target['jalur'];
        $gelombang = $target['gelombang'];

        $candidateRules = NomorRule::with(['sequence', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('jenis_nomor', $jenisNomor)
            ->when($currentRuleId, fn ($q) => $q->where('id', '!=', $currentRuleId))
            ->get();

        $relevantRules = $candidateRules->filter(function (NomorRule $rule) use ($tahun, $jalur, $gelombang) {
            return match ($rule->scope_type) {
                NomorRule::SCOPE_GLOBAL => true,
                NomorRule::SCOPE_TAHUN => $tahun && $rule->scope_id === $tahun->id,
                NomorRule::SCOPE_JALUR => $jalur && $rule->scope_id === $jalur->id,
                NomorRule::SCOPE_GELOMBANG => $gelombang && $rule->scope_id === $gelombang->id,
                default => false,
            };
        });

        $sameScopeRule = $relevantRules
            ->first(fn (NomorRule $rule) => $rule->scope_type === $scopeType);

        $allCounters = collect();

        foreach ($candidateRules as $rule) {
            $allCounters->push((int) ($rule->sequence?->last_number ?? ($rule->nomor_awal - 1)));
        }

        if ($jenisNomor === NomorRule::JENIS_REGISTRASI) {
            if ($tahun) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahun->id)->pluck('id');
                $allCounters->push((int) JalurPendaftaran::whereIn('id', $jalurIds)->max('counter_nomor'));
                $allCounters->push((int) GelombangPendaftaran::whereIn('jalur_id', $jalurIds)->max('counter_nomor'));
                $allCounters->push((int) CalonSiswa::where('tahun_pelajaran_id', $tahun->id)->count());
            }

            if ($jalur) {
                $allCounters->push((int) $jalur->counter_nomor);
            }

            if ($gelombang) {
                $allCounters->push((int) $gelombang->counter_nomor);
            }

            $settings = PpdbSettings::getActive();
            $allCounters->push((int) ($settings->nomor_registrasi_counter ?? 0));
        } else {
            $settings = PpdbSettings::getActive();
            $tesCounters = collect($settings->nomor_tes_counter ?? []);
            $allCounters = $allCounters->merge($tesCounters->map(fn ($value) => (int) $value));

            if ($tahun) {
                $allCounters->push((int) CalonSiswa::where('tahun_pelajaran_id', $tahun->id)->whereNotNull('nomor_tes')->count());
            }
        }

        $lastNumber = $allCounters->filter(fn ($value) => is_numeric($value) && $value >= 0)->max() ?? 0;
        $nomorAwal = max(1, $lastNumber + 1);

        $suggestedSourceRule = $candidateRules
            ->filter(function (NomorRule $rule) use ($tahun, $jalur, $gelombang) {
                if ($gelombang && $rule->scope_type === NomorRule::SCOPE_GELOMBANG && $rule->gelombangPendaftaran?->jalur_id === $gelombang->jalur_id) {
                    return true;
                }

                if ($jalur && $rule->scope_type === NomorRule::SCOPE_JALUR && $rule->jalurPendaftaran?->tahun_pelajaran_id === $jalur->tahun_pelajaran_id) {
                    return true;
                }

                if ($tahun && $rule->scope_type === NomorRule::SCOPE_TAHUN && $rule->scope_id === $tahun->id) {
                    return true;
                }

                return $rule->scope_type === NomorRule::SCOPE_GLOBAL;
            })
            ->sortByDesc(fn (NomorRule $rule) => $rule->sequence?->last_number ?? ($rule->nomor_awal - 1))
            ->first();

        $modeCounter = $suggestedSourceRule ? NomorRule::MODE_LANJUT : NomorRule::MODE_MANUAL;
        $sourceLabel = $suggestedSourceRule
            ? sprintf(
                '%s (counter %d)',
                $suggestedSourceRule->nama_rule,
                $suggestedSourceRule->sequence?->last_number ?? ($suggestedSourceRule->nomor_awal - 1)
            )
            : 'Counter tertinggi dari data pendaftar dan counter jalur/gelombang';

        return [
            'nomor_awal' => $nomorAwal,
            'counter_terakhir' => $lastNumber,
            'mode_counter' => $modeCounter,
            'source_rule_id' => $suggestedSourceRule?->id,
            'source_label' => $sourceLabel,
            'existing_rule' => $sameScopeRule ? [
                'id' => $sameScopeRule->id,
                'nama_rule' => $sameScopeRule->nama_rule,
                'last_number' => $sameScopeRule->sequence?->last_number ?? ($sameScopeRule->nomor_awal - 1),
            ] : null,
        ];
    }
}
