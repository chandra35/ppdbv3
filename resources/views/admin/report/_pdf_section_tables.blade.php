{{-- Reusable PDF tables for a section: receives $data --}}
<table class="three-col">
    <tr>
        {{-- Jenis Kelamin --}}
        <td>
            <table>
                <tr><th colspan="3" class="text-center" style="background:#34495e;color:#fff;">Jenis Kelamin</th></tr>
                <tr><th>Kategori</th><th class="text-center">Jumlah</th><th class="text-center">%</th></tr>
                <tr>
                    <td>Laki-laki</td>
                    <td class="text-center bold">{{ $data['laki_laki'] }}</td>
                    <td class="text-center">{{ $data['total'] > 0 ? round($data['laki_laki'] / $data['total'] * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Perempuan</td>
                    <td class="text-center bold">{{ $data['perempuan'] }}</td>
                    <td class="text-center">{{ $data['total'] > 0 ? round($data['perempuan'] / $data['total'] * 100, 1) : 0 }}%</td>
                </tr>
                <tr class="row-total">
                    <td>Total</td>
                    <td class="text-center">{{ $data['total'] }}</td>
                    <td class="text-center">100%</td>
                </tr>
            </table>
        </td>

        {{-- Pilihan Program --}}
        <td>
            @if(!($data['program_stats']['enabled'] ?? false))
            <table>
                <tr><th class="text-center" style="background:#34495e;color:#fff;">Pilihan Program</th></tr>
                <tr><td class="text-center">Tidak digunakan pada jalur di konteks ini.</td></tr>
            </table>
            @else
            <table>
                <tr><th colspan="4" class="text-center" style="background:#34495e;color:#fff;">Pilihan Program</th></tr>
                <tr><th>Program</th><th class="text-center">Total</th><th class="text-center">L</th><th class="text-center">P</th></tr>
                @foreach($data['program_stats']['items'] as $program)
                <tr class="{{ $program['label'] === 'Belum Memilih' ? 'row-warning' : '' }}">
                    <td>{{ $program['label'] }}</td>
                    <td class="text-center bold">{{ $program['total'] }}</td>
                    <td class="text-center">{{ $program['l'] }}</td>
                    <td class="text-center">{{ $program['p'] }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </td>

        {{-- Asal Sekolah --}}
        <td>
            <table>
                <tr><th colspan="4" class="text-center" style="background:#34495e;color:#fff;">Asal Sekolah</th></tr>
                <tr><th>Kategori</th><th class="text-center">Total</th><th class="text-center">L</th><th class="text-center">P</th></tr>
                @foreach($data['asal_sekolah'] as $kategori => $val)
                <tr>
                    <td>{{ $kategori }}</td>
                    <td class="text-center bold">{{ $val['total'] }}</td>
                    <td class="text-center">{{ $val['l'] }}</td>
                    <td class="text-center">{{ $val['p'] }}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
