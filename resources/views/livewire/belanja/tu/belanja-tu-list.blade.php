@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Belanja TU</h3>
            <p class="page-subtitle mb-0">Daftar SPP-SPM TU yang sudah ber-SP2D — pilih untuk entri belanja</p>
        </div>

        <div class="content-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                    <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" wire:model.live.debounce.300ms="search" placeholder="Cari No Bukti, Uraian...">
                    @if($search)
                        <button type="button" class="clear-search" wire:click="$set('search', '')"><i class="fas fa-times"></i></button>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table" style="font-size: 12.5px;">
                    <thead>
                        <tr>
                            <th style="width: 35px;">No</th>
                            <th style="width: 130px;">No Bukti</th>
                            <th style="width: 90px;">Tanggal</th>
                            <th>Uraian</th>
                            <th style="width: 120px;" class="text-right">Nilai TU</th>
                            <th style="width: 120px;" class="text-right">Terbelanja</th>
                            <th style="width: 120px;" class="text-right">Sisa</th>
                            <th style="width: 65px;" class="text-center">Status</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sppSpmTus as $row)
                            @php
                                $totalBelanja = $row->belanjaTus->sum('nilai');
                                $sisa = $row->total_nilai - $totalBelanja;
                                $hasSpj = $row->spjTu !== null;
                            @endphp
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $sppSpmTus->firstItem() }}</span></td>
                                <td><span class="code-badge" style="font-size: 10px; white-space: nowrap;">SPP-{{ $row->no_bukti }}</span></td>
                                <td style="white-space: nowrap;">{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
                                <td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px;">{{ $row->uraian ?? '-' }}</td>
                                <td class="text-right" style="white-space: nowrap;">{{ number_format($row->total_nilai, 0, ',', '.') }}</td>
                                <td class="text-right" style="white-space: nowrap;">{{ number_format($totalBelanja, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold" style="white-space: nowrap; color: {{ $sisa > 0 ? '#2563eb' : '#16a34a' }};">{{ number_format($sisa, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($hasSpj)
                                        <span class="badge badge-info" style="font-size: 9px;">SPJ</span>
                                    @else
                                        <span class="badge badge-warning" style="font-size: 9px;">Proses</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('belanja-tu/' . $row->id) }}" class="btn btn-sm btn-primary" style="border-radius: 6px; font-size: 11px; padding: 4px 10px;">
                                        <i class="fas fa-shopping-bag"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada SPP-SPM TU yang ber-SP2D</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $sppSpmTus->links() }}</div>
        </div>
    </div>
</div>
