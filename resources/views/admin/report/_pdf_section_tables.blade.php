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
            <table>
                <tr><th colspan="4" class="text-center" style="background:#34495e;color:#fff;">Pilihan Program</th></tr>
                <tr><th>Program</th><th class="text-center">Total</th><th class="text-center">L</th><th class="text-center">P</th></tr>
                <tr>
                    <td>Reguler</td>
                    <td class="text-center bold">{{ $data['reguler'] }}</td>
                    <td class="text-center">{{ $data['reguler_l'] }}</td>
                    <td class="text-center">{{ $data['reguler_p'] }}</td>
                </tr>
                <tr>
                    <td>Asrama</td>
                    <td class="text-center bold">{{ $data['asrama'] }}</td>
                    <td class="text-center">{{ $data['asrama_l'] }}</td>
                    <td class="text-center">{{ $data['asrama_p'] }}</td>
                </tr>
                @if(($data['belum_memilih'] ?? 0) > 0)
                <tr class="row-warning">
                    <td>Belum Memilih</td>
                    <td class="text-center bold">{{ $data['belum_memilih'] }}</td>
                    <td class="text-center">{{ $data['belum_memilih_l'] }}</td>
                    <td class="text-center">{{ $data['belum_memilih_p'] }}</td>
                </tr>
                @endif
            </table>
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
