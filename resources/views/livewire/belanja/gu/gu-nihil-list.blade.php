@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">SPP-SPM GU Nihil</h3>
            <p class="page-subtitle mb-0">Daftar SPJ GU yang belum dibuatkan SPP-SPM GU — setor sisa ke Kasda</p>
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
                    <input type="text" class="form-control search-input" wire:model.live.debounce.300ms="search" placeholder="Cari No SPJ, Keterangan...">
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
                            <th style="width: 100px;">No SPJ</th>
                            <th style="width: 90px;">Tanggal SPJ</th>
                            <th style="width: 140px;">Periode</th>
                            <th>Keterangan</th>
                            <th style="width: 80px;" class="text-center">Belanja</th>
                            <th style="width: 120px;" class="text-right">Total Nilai</th>
                            <th style="width: 65px;" class="text-center">Nihil</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spjGus as $row)
                            @php
                                $totalNilai = $row->belanjas->sum('nilai');
                                $hasNihil = $row->nihil !== null;
                            @endphp
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $spjGus->firstItem() }}</span></td>
                                <td><span class="code-badge" style="font-size: 10px;">SPJ-{{ $row->nomor_spj }}</span></td>
                                <td style="white-space: nowrap;">{{ date('d-m-Y', strtotime($row->tanggal_spj)) }}</td>
                                <td style="font-size: 11px; white-space: nowrap;">{{ date('d/m', strtotime($row->periode_awal)) }} - {{ date('d/m/Y', strtotime($row->periode_akhir)) }}</td>
                                <td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;">{{ $row->keterangan ?? '-' }}</td>
                                <td class="text-center"><span class="badge badge-info">{{ $row->belanjas->count() }}</span></td>
                                <td class="text-right" style="white-space: nowrap;">{{ number_format($totalNilai, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($hasNihil)
                                        <span class="badge badge-dark" style="font-size: 9px;">Ya</span>
                                    @else
                                        <span class="badge badge-warning" style="font-size: 9px;">!</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('gu-nihil/' . $row->id) }}" class="btn btn-sm {{ $hasNihil ? 'btn-dark' : 'btn-warning' }}" style="border-radius: 6px; font-size: 10px; padding: 3px 8px;">
                                        {{ $hasNihil ? 'Lihat' : 'Nihil' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Semua SPJ GU sudah dibuatkan SPP-SPM GU</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $spjGus->links() }}</div>
        </div>
    </div>
</div>
