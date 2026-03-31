@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPP-SPM GU</h3>
                    <p class="page-subtitle mb-0">Kelola data SPP-SPM Ganti Uang berdasarkan SPJ GU</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="openForm">
                        <i class="fas fa-plus mr-2"></i>Tambah SPP-SPM GU
                    </button>
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
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                        <div class="stat-description">Data SPP-SPM GU</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-label">Total Nominal</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        <div class="stat-description">Akumulasi Nilai</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                    <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
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
                <table class="table modern-table" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th style="width: 170px;">No Bukti (SPP)</th>
                            <th style="width: 200px;">No SPM SIPD</th>
                            <th style="width: 100px;">Tanggal</th>
                            <th>Uraian</th>
                            <th style="width: 160px;" class="text-right">Total Nilai</th>
                            <th style="width: 100px;" class="text-center">SPJ GU</th>
                            <th style="width: 160px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sppSpmGus as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $sppSpmGus->firstItem() }}</span></td>
                                <td>
                                    <span class="code-badge" style="white-space: nowrap; font-size: 11px;">
                                        SPP-{{ $row->no_bukti }}/Diskominfo/{{ $row->tahun_bukti ?? date('Y', strtotime($row->tanggal)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($row->no_spm_sipd)
                                        <span class="code-badge" style="font-size: 11px; word-break: break-all; display: inline-block; max-width: 100%;">{{ $row->no_spm_sipd }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td style="font-weight: 500; white-space: nowrap;">{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
                                <td class="text-left" style="overflow: hidden;">
                                    <span title="{{ $row->uraian }}" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                        {{ $row->uraian ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <span class="amount-badge">Rp {{ number_format($row->total_nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="showDetail({{ $row->id }})" class="btn btn-sm btn-outline-info" style="border-radius: 6px; font-size: 12px;">
                                        <i class="fas fa-eye mr-1"></i> {{ $row->spjGus->count() }} SPJ
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit">
                                            <i class="fas fa-pencil-alt text-white"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group ml-1">
                                        <button wire:click="printSppSpmGu({{ $row->id }})"
                                            class="btn btn-secondary btn-sm"
                                            style="border-radius: 6px 0 0 6px;"
                                            title="Cetak"
                                            wire:loading.attr="disabled"
                                            wire:target="printSppSpmGu({{ $row->id }})">
                                            <span wire:loading.remove wire:target="printSppSpmGu({{ $row->id }})">
                                                <i class="fas fa-print"></i>
                                            </span>
                                            <span wire:loading wire:target="printSppSpmGu({{ $row->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="downloadSppSpmGu({{ $row->id }})"
                                            class="btn btn-success btn-sm"
                                            style="border-radius: 0 6px 6px 0;"
                                            title="Download"
                                            wire:loading.attr="disabled"
                                            wire:target="downloadSppSpmGu({{ $row->id }})">
                                            <span wire:loading.remove wire:target="downloadSppSpmGu({{ $row->id }})">
                                                <i class="fas fa-download"></i>
                                            </span>
                                            <span wire:loading wire:target="downloadSppSpmGu({{ $row->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Belum ada data SPP-SPM GU
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $sppSpmGus->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form SPP-SPM GU -->
    <div wire:ignore.self class="modal fade" id="sppSpmGuModal" tabindex="-1" aria-labelledby="sppSpmGuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit SPP-SPM GU' : 'Tambah SPP-SPM GU' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- No Bukti (SPP) -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">No Bukti (SPP)</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="SPP-" readonly style="background-color: #f4f6f9;">
                            </div>
                            <div class="col-md-3">
                                <input wire:model="no_bukti" type="text" class="form-control" placeholder="0001">
                                @error('no_bukti') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="/Diskominfo/{{ $tahunTransaksi ?? date('Y') }}" readonly style="background-color: #f4f6f9;">
                            </div>
                        </div>

                        <!-- No SPM SIPD -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">No SPM SIPD</label>
                            <div class="col-md-9">
                                <input wire:model="no_spm_sipd" type="text" class="form-control" placeholder="Nomor sesuai dengan nomor SPM SIPD">
                                @error('no_spm_sipd') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Tanggal</label>
                            <div class="col-md-9">
                                <input wire:model.live="tanggal" type="date" class="form-control">
                                @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Total Nilai (readonly) -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Total Nilai</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control font-weight-bold"
                                    value="Rp {{ number_format($total_nilai, 2, ',', '.') }}"
                                    readonly style="background-color: #e8f0fe; color: #1a73e8;">
                            </div>
                        </div>

                        <!-- Uraian -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" placeholder="Masukkan uraian..." rows="3"></textarea>
                                @error('uraian') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Pilih SPJ GU -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                <i class="fas fa-file-signature mr-1"></i> Pilih SPJ GU
                            </label>
                            @error('selectedSpjGuIds') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror

                            @if($availableSpjGus->count() > 0 || count($selectedSpjGuIds) > 0)
                                <div class="card bg-light border-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0" style="background: white;">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="50" class="text-center">Pilih</th>
                                                        <th width="120">No SPJ</th>
                                                        <th width="110">Tanggal</th>
                                                        <th width="120">Periode</th>
                                                        <th>Keterangan</th>
                                                        <th width="80" class="text-center">Belanja</th>
                                                        <th width="150" class="text-right">Nilai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($availableSpjGus as $spjGu)
                                                        <tr class="{{ in_array($spjGu->id, $selectedSpjGuIds) ? 'table-primary' : '' }}">
                                                            <td class="text-center">
                                                                <input type="checkbox"
                                                                    wire:click="toggleSpjGu({{ $spjGu->id }})"
                                                                    {{ in_array($spjGu->id, $selectedSpjGuIds) ? 'checked' : '' }}
                                                                    style="width: 18px; height: 18px; cursor: pointer;">
                                                            </td>
                                                            <td><span class="code-badge">SPJ-{{ $spjGu->nomor_spj }}</span></td>
                                                            <td style="font-size: 12px;">{{ date('d-m-Y', strtotime($spjGu->tanggal_spj)) }}</td>
                                                            <td style="font-size: 11px;">
                                                                {{ date('d/m', strtotime($spjGu->periode_awal)) }} - {{ date('d/m/Y', strtotime($spjGu->periode_akhir)) }}
                                                            </td>
                                                            <td style="font-size: 12px;">{{ $spjGu->keterangan ?? '-' }}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-info">{{ $spjGu->belanjas->count() }}</span>
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="amount-badge" style="font-size: 12px;">
                                                                    Rp {{ number_format($spjGu->belanjas->sum('nilai'), 0, ',', '.') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                Semua SPJ GU sudah digunakan
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if(count($selectedSpjGuIds) > 0)
                                    <div class="alert alert-info mt-2 mb-0 d-flex justify-content-between align-items-center" style="border-radius: 8px;">
                                        <span><i class="fas fa-check-circle mr-1"></i> {{ count($selectedSpjGuIds) }} SPJ GU dipilih</span>
                                        <span class="font-weight-bold">Total: Rp {{ number_format($total_nilai, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning d-flex align-items-center" style="border-radius: 8px;">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Belum ada data SPJ GU yang tersedia. Buat SPJ GU terlebih dahulu.
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeForm" class="btn btn-secondary">Batal</button>
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail SPJ GU -->
    <div wire:ignore.self class="modal fade" id="detailSppSpmGuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Detail SPP-SPM GU
                        @if($detailSppSpmGu)
                            - SPP-{{ $detailSppSpmGu->no_bukti }}
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="closeDetail" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($detailSppSpmGu)
                        <!-- Info Header -->
                        <div class="card bg-light border-0 mb-3" style="border-radius: 10px;">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">No Bukti (SPP)</small>
                                        <div class="font-weight-bold">SPP-{{ $detailSppSpmGu->no_bukti }}/Diskominfo/{{ $detailSppSpmGu->tahun_bukti }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Tanggal</small>
                                        <div class="font-weight-bold">{{ date('d-m-Y', strtotime($detailSppSpmGu->tanggal)) }}</div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">Total Nilai</small>
                                        <div class="font-weight-bold text-primary" style="font-size: 1.1rem;">Rp {{ number_format($detailSppSpmGu->total_nilai, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Uraian</small>
                                        <div>{{ $detailSppSpmGu->uraian ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar SPJ GU -->
                        <h6 class="font-weight-bold mb-2"><i class="fas fa-list mr-1"></i> Daftar SPJ GU</h6>
                        @foreach($detailSppSpmGu->spjGus as $spjGu)
                            <div class="card border mb-2" style="border-radius: 8px;">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="code-badge">SPJ-{{ $spjGu->nomor_spj }}</span>
                                            <small class="text-muted ml-2">{{ date('d-m-Y', strtotime($spjGu->tanggal_spj)) }}</small>
                                            <small class="text-muted ml-2">
                                                ({{ date('d/m', strtotime($spjGu->periode_awal)) }} - {{ date('d/m/Y', strtotime($spjGu->periode_akhir)) }})
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge badge-info mr-1">{{ $spjGu->belanjas->count() }} belanja</span>
                                            <span class="amount-badge" style="font-size: 12px;">Rp {{ number_format($spjGu->belanjas->sum('nilai'), 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    @if($spjGu->keterangan)
                                        <small class="text-muted d-block mt-1">{{ $spjGu->keterangan }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDetail">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Print Modal -->
    <div wire:ignore.self class="modal fade" id="viewSppSpmGu" tabindex="-1" aria-labelledby="viewSppSpmGu" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Dokumen SPP-SPM GU</h5>
                    <button type="button" class="close" wire:click="closeModalPdf" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <embed src="{{ route('helper.show-picture', ['path' => 'public/reports/laporan_belanja_' . $pathpdf]) }}" class="col-12" height="600px" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="closeModalPdf">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('belanja', (event) => {
                $('#viewSppSpmGu').modal("show");
            });
        });
    </script>
@endpush
