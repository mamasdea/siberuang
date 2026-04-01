@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPP-SPM UP</h3>
                    <p class="page-subtitle mb-0">Kelola data SPP-SPM Uang Persediaan</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="openForm">
                        <i class="fas fa-plus mr-2"></i>Tambah SPP-SPM UP
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
                        <div class="stat-description">Data SPP-SPM UP</div>
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
                            <th style="width: 120px;">Tanggal</th>
                            <th>Uraian</th>
                            <th style="width: 160px;" class="text-right">Total Nilai</th>
                            <th style="width: 250px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sppSpmUps as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $sppSpmUps->firstItem() }}</span></td>
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
                                <td style="font-weight: 500; white-space: nowrap;">
                                    {{ date('d-m-Y', strtotime($row->tanggal)) }}
                                    @if($row->tanggal_sp2d)
                                        <br><span class="badge badge-success" style="font-size: 9px;">SP2D {{ date('d/m/Y', strtotime($row->tanggal_sp2d)) }}</span>
                                    @endif
                                </td>
                                <td class="text-left" style="overflow: hidden;">
                                    <span title="{{ $row->uraian }}" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                        {{ $row->uraian ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <span class="amount-badge">Rp {{ number_format($row->total_nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($row->tanggal_sp2d && $row->no_sp2d)
                                        <button wire:click="openSp2dModal({{ $row->id }})" class="btn btn-sm btn-success" style="border-radius: 6px; font-size: 11px;" title="SP2D sudah terbit">
                                            <i class="fas fa-check-circle mr-1"></i> SP2D
                                        </button>
                                    @else
                                        <button wire:click="openSp2dModal({{ $row->id }})" class="btn btn-sm btn-outline-warning" style="border-radius: 6px; font-size: 11px;" title="Input SP2D">
                                            <i class="fas fa-file-export mr-1"></i> SP2D
                                        </button>
                                    @endif

                                    <div class="btn-group ml-1">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit">
                                            <i class="fas fa-pencil-alt text-white"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group ml-1">
                                        <button wire:click="printSppSpmUp({{ $row->id }})"
                                            class="btn btn-secondary btn-sm"
                                            style="border-radius: 6px 0 0 6px;"
                                            title="Cetak"
                                            wire:loading.attr="disabled"
                                            wire:target="printSppSpmUp({{ $row->id }})">
                                            <span wire:loading.remove wire:target="printSppSpmUp({{ $row->id }})">
                                                <i class="fas fa-print"></i>
                                            </span>
                                            <span wire:loading wire:target="printSppSpmUp({{ $row->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="downloadSppSpmUp({{ $row->id }})"
                                            class="btn btn-success btn-sm"
                                            style="border-radius: 0 6px 6px 0;"
                                            title="Download"
                                            wire:loading.attr="disabled"
                                            wire:target="downloadSppSpmUp({{ $row->id }})">
                                            <span wire:loading.remove wire:target="downloadSppSpmUp({{ $row->id }})">
                                                <i class="fas fa-download"></i>
                                            </span>
                                            <span wire:loading wire:target="downloadSppSpmUp({{ $row->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Belum ada data SPP-SPM UP
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $sppSpmUps->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form SPP-SPM UP -->
    <div wire:ignore.self class="modal fade" id="sppSpmUpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit SPP-SPM UP' : 'Tambah SPP-SPM UP' }}</h5>
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

                        <!-- Total Nilai -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Total Nilai</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-0 font-weight-bold text-dark pl-3">Rp</span>
                                    </div>
                                    <input wire:model="total_nilai" type="number" step="0.01" class="form-control" placeholder="0">
                                </div>
                                <small class="text-muted">Masukkan nilai sesuai ketetapan BPPKAD</small>
                                @error('total_nilai') <span class="text-danger small">{{ $message }}</span> @enderror
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

    <!-- View Print Modal -->
    <div wire:ignore.self class="modal fade" id="viewSppSpmUp" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Dokumen SPP-SPM UP</h5>
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

    <!-- Modal SP2D -->
    <div wire:ignore.self class="modal fade" id="sp2dUpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-export mr-2 text-primary"></i>Input SP2D</h5>
                    <button type="button" class="close" wire:click="closeSp2dModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary small text-uppercase">Nomor SP2D</label>
                        <input wire:model="sp2d_no" type="text" class="form-control" placeholder="Masukkan nomor SP2D">
                        @error('sp2d_no') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary small text-uppercase">Tanggal SP2D</label>
                        <input wire:model="sp2d_tanggal" type="date" class="form-control">
                        @error('sp2d_tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" wire:click="closeSp2dModal" class="btn btn-light">Batal</button>
                    <button type="button" wire:click="saveSp2d" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan SP2D
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('belanja', (event) => {
                $('#viewSppSpmUp').modal("show");
            });
        });
    </script>
@endpush
