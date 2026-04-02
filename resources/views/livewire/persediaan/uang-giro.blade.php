@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Daftar SP2D - Uang Persediaan</h3>
                    <p class="page-subtitle mb-0">Daftar SP2D yang sudah terbit dari SPP-SPM UP dan GU</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-label">Total SP2D</div>
                        <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                        <div class="stat-description">SP2D terbit</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-label">Total Nominal</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        <div class="stat-description">Akumulasi Penerimaan</div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                    <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           class="form-control search-input"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari No Bukti, Uraian...">
                    @if($search)
                        <button type="button" class="clear-search" wire:click="$set('search', '')">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="45">No</th>
                            <th width="80">Tipe</th>
                            <th width="120">No Bukti</th>
                            <th width="110">Tanggal SP2D</th>
                            <th>Uraian</th>
                            <th width="160" class="text-right">Nominal</th>
                            <th width="120">Sumber</th>
                            <th width="60" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($uangGiros as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $uangGiros->firstItem() }}</span></td>
                                <td>
                                    @if($row->tipe == 'UP')
                                        <span class="badge badge-primary" style="font-size: 11px;">UP</span>
                                    @elseif($row->tipe == 'TU')
                                        <span class="badge badge-warning" style="font-size: 11px;">TU</span>
                                    @else
                                        <span class="badge badge-success" style="font-size: 11px;">GU</span>
                                    @endif
                                </td>
                                <td><span class="code-badge">{{ $row->no_bukti }}</span></td>
                                <td style="font-weight: 500;">{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
                                <td>{{ $row->uraian }}</td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($row->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if($row->spp_spm_up_id)
                                        <a href="{{ url('spp-spm-up') }}" class="badge badge-outline-primary" style="font-size: 10px; text-decoration: none;">
                                            SPP-SPM UP
                                        </a>
                                    @elseif($row->spp_spm_gu_id)
                                        <a href="{{ url('spp-spm-gu') }}" class="badge badge-outline-success" style="font-size: 10px; text-decoration: none;">
                                            SPP-SPM GU
                                        </a>
                                    @elseif($row->spp_spm_tu_id)
                                        <a href="{{ url('spp-spm-tu') }}" class="badge badge-outline-warning" style="font-size: 10px; text-decoration: none;">
                                            SPP-SPM TU
                                        </a>
                                    @else
                                        <span class="badge badge-light" style="font-size: 10px;">Manual</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$row->spp_spm_up_id && !$row->spp_spm_gu_id && !$row->spp_spm_tu_id)
                                        <button wire:click="deleteConfirmation({{ $row->id }})" class="btn btn-danger btn-sm" style="border-radius: 6px;" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Belum ada SP2D yang terbit.<br>
                                    <small>SP2D otomatis muncul saat Anda mengisi nomor & tanggal SP2D di menu SPP-SPM.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $uangGiros->links() }}
            </div>
        </div>
    </div>
</div>
