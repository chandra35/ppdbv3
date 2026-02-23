{{-- Reusable tables for a section --}}
{{-- Variable: $data (array from buildSectionStats) --}}
@php $total = $data['total']; @endphp

@if($total === 0)
    <div class="alert alert-secondary text-center"><i class="fas fa-info-circle mr-1"></i> Tidak ada data untuk bagian ini.</div>
@else
<div class="row">
    {{-- Jenis Kelamin --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm rincian-table mb-0">
                    <tbody>
                        <tr>
                            <td>Laki-laki</td>
                            <td class="text-center"><span class="badge badge-l">{{ $data['laki_laki'] }}</span></td>
                            <td class="text-right text-muted">{{ $total > 0 ? round($data['laki_laki'] / $total * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td>Perempuan</td>
                            <td class="text-center"><span class="badge badge-p">{{ $data['perempuan'] }}</span></td>
                            <td class="text-right text-muted">{{ $total > 0 ? round($data['perempuan'] / $total * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr class="row-total">
                            <td>Total</td>
                            <td class="text-center">{{ $total }}</td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if($total > 0)
            <div class="card-footer p-2">
                <div class="progress" style="height: 18px;">
                    <div class="progress-bar bg-primary" style="width: {{ round($data['laki_laki'] / $total * 100, 1) }}%">{{ $data['laki_laki'] }}</div>
                    <div class="progress-bar bg-danger" style="width: {{ round($data['perempuan'] / $total * 100, 1) }}%">{{ $data['perempuan'] }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Pilihan Program --}}
    <div class="col-md-4">
        <div class="card card-outline card-success mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-heart mr-1"></i> Pilihan Program</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm rincian-table mb-0">
                    <thead>
                        <tr class="row-header">
                            <th>Program</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Reguler</td>
                            <td class="text-center"><strong>{{ $data['reguler'] }}</strong></td>
                            <td class="text-center">{{ $data['reguler_l'] }}</td>
                            <td class="text-center">{{ $data['reguler_p'] }}</td>
                        </tr>
                        <tr>
                            <td>Asrama</td>
                            <td class="text-center"><strong>{{ $data['asrama'] }}</strong></td>
                            <td class="text-center">{{ $data['asrama_l'] }}</td>
                            <td class="text-center">{{ $data['asrama_p'] }}</td>
                        </tr>
                        <tr class="table-warning">
                            <td><em>Belum Memilih</em></td>
                            <td class="text-center"><strong>{{ $data['belum_memilih'] }}</strong></td>
                            <td class="text-center">{{ $data['belum_memilih_l'] }}</td>
                            <td class="text-center">{{ $data['belum_memilih_p'] }}</td>
                        </tr>
                        <tr class="row-total">
                            <td>Total</td>
                            <td class="text-center">{{ $total }}</td>
                            <td class="text-center">{{ $data['laki_laki'] }}</td>
                            <td class="text-center">{{ $data['perempuan'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Asal Sekolah --}}
    <div class="col-md-4">
        <div class="card card-outline card-warning mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-school mr-1"></i> Asal Sekolah</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm rincian-table mb-0">
                    <thead>
                        <tr class="row-header">
                            <th>Kategori</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; $grandL = 0; $grandP = 0; @endphp
                        @foreach($data['asal_sekolah'] as $cat => $as)
                        <tr>
                            <td>{{ $cat }}</td>
                            <td class="text-center"><strong>{{ $as['total'] }}</strong></td>
                            <td class="text-center">{{ $as['l'] }}</td>
                            <td class="text-center">{{ $as['p'] }}</td>
                        </tr>
                        @php $grandTotal += $as['total']; $grandL += $as['l']; $grandP += $as['p']; @endphp
                        @endforeach
                        <tr class="row-total">
                            <td>Total</td>
                            <td class="text-center">{{ $grandTotal }}</td>
                            <td class="text-center">{{ $grandL }}</td>
                            <td class="text-center">{{ $grandP }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
