@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPJ GU (Surat Pertanggungjawaban Ganti Uang)</h3>
                    <p class="page-subtitle mb-0">Kelola pengelompokan belanja GU per periode sebagai dasar SPP dan SPM</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-add" wire:click="openForm">
                        <i class="fas fa-plus mr-2"></i>Buat SPJ GU
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
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="stat-label">Total SPJ GU</div>
                        <div class="stat-value">{{ number_format($totalSpj, 0, ',', '.') }}</div>
                        <div class="stat-description">Dokumen SPJ</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-label">Total Nilai SPJ</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNilaiSpj, 0, ',', '.') }}</div>
                        <div class="stat-description">Akumulasi Belanja</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                        <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                           class="form-control search-input"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari No SPJ, Keterangan...">
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
                        <tr class="text-center">
                            <th width="50">No</th>
                            <th width="130">No SPJ</th>
                            <th width="110">Tanggal SPJ</th>
                            <th width="200">Periode</th>
                            <th>Keterangan</th>
                            <th width="80" class="text-center">Jml Belanja</th>
                            <th width="160" class="text-right">Total Nilai</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($spjGus as $row)
                            <tr wire:key="spj-{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $spjGus->firstItem() }}</span></td>
                                <td><span class="code-badge">SPJ-{{ $row->nomor_spj }}</span></td>
                                <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($row->tanggal_spj)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-info" style="font-size: 12px; padding: 5px 10px;">
                                        {{ \Carbon\Carbon::parse($row->periode_awal)->format('d/m/Y') }}
                                        <i class="fas fa-arrow-right mx-1"></i>
                                        {{ \Carbon\Carbon::parse($row->periode_akhir)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>{{ $row->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary" style="font-size: 13px; padding: 5px 12px;">
                                        {{ $row->jumlah_belanja }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($row->total_nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button wire:click="showDetail({{ $row->id }})" class="btn btn-primary btn-sm" style="border-radius: 6px 0 0 6px;" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="edit({{ $row->id }})" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-pencil-alt text-white"></i>
                                        </button>
                                        <button wire:click="delete_confirmation({{ $row->id }})" class="btn btn-danger btn-sm" style="border-radius: 0 6px 6px 0;" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Belum ada data SPJ GU
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $spjGus->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form SPJ GU -->
    <div wire:ignore.self class="modal fade" id="spjGuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-card border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white">
                    <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.25rem;">
                        {{ $isEdit ? 'Edit SPJ GU' : 'Buat SPJ GU Baru' }}
                    </h5>
                    <button type="button" class="close" wire:click="closeForm" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <form>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Tanggal SPJ</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                        </div>
                                        <input wire:model="tanggal_spj" type="date" class="form-control bg-light border-0" style="height: 48px;">
                                    </div>
                                    @error('tanggal_spj') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Periode Awal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-calendar text-muted"></i></span>
                                        </div>
                                        <input wire:model.live="periode_awal" type="date" class="form-control bg-light border-0" style="height: 48px;">
                                    </div>
                                    @error('periode_awal') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Periode Akhir</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-0"><i class="fas fa-calendar text-muted"></i></span>
                                        </div>
                                        <input wire:model.live="periode_akhir" type="date" class="form-control bg-light border-0" style="height: 48px;">
                                    </div>
                                    @error('periode_akhir') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Keterangan</label>
                            <textarea wire:model="keterangan" class="form-control bg-light border-0" rows="2" style="border-radius: 12px; resize: none; padding: 12px;" placeholder="Keterangan SPJ GU (opsional)..."></textarea>
                        </div>

                        <!-- Belanja Selection -->
                        @if($periode_awal && $periode_akhir)
                            <div class="card bg-light border-0 p-3 mb-3" style="border-radius: 12px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-check-square text-primary mr-2"></i>
                                        Pilih Belanja GU
                                        <span class="badge badge-primary ml-2">{{ count($selectedBelanjaIds) }} dipilih</span>
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <input type="text" wire:model.live.debounce.300ms="searchBelanja" class="form-control form-control-sm bg-white border-0" placeholder="Cari belanja..." style="width: 200px; border-radius: 8px;">
                                        @if(count($availableBelanjas) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary ml-2" wire:click="selectAllBelanja({{ json_encode($availableBelanjas->pluck('id')) }})" style="border-radius: 6px;">
                                                <i class="fas fa-check-double mr-1"></i> Pilih Semua
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ml-1" wire:click="deselectAllBelanja" style="border-radius: 6px;">
                                                <i class="fas fa-times mr-1"></i> Batal Semua
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @error('selectedBelanjaIds') <div class="alert alert-danger py-2 mb-3" style="border-radius: 8px;">{{ $message }}</div> @enderror

                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-hover bg-white mb-0" style="border-radius: 8px;">
                                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr class="text-center">
                                                <th width="50">Pilih</th>
                                                <th width="100">No Bukti</th>
                                                <th width="100">Tanggal</th>
                                                <th>Uraian</th>
                                                <th width="150" class="text-right">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($availableBelanjas as $bel)
                                                <tr wire:key="bel-{{ $bel->id }}" class="{{ in_array($bel->id, $selectedBelanjaIds) ? 'table-primary' : '' }}" style="cursor: pointer;" wire:click="toggleBelanja({{ $bel->id }})">
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="bel{{ $bel->id }}"
                                                                {{ in_array($bel->id, $selectedBelanjaIds) ? 'checked' : '' }}
                                                                wire:click.stop="toggleBelanja({{ $bel->id }})">
                                                            <label class="custom-control-label" for="bel{{ $bel->id }}"></label>
                                                        </div>
                                                    </td>
                                                    <td><span class="code-badge">TBP-{{ $bel->no_bukti }}</span></td>
                                                    <td>{{ $bel->tanggal }}</td>
                                                    <td>{{ $bel->uraian }}</td>
                                                    <td class="text-right">
                                                        <span class="amount-badge">Rp {{ number_format($bel->nilai, 0, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">
                                                        Tidak ada belanja GU pada periode ini yang belum di-SPJ-kan
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(count($selectedBelanjaIds) > 0)
                                    <div class="mt-3 p-3 bg-white rounded" style="border-radius: 8px !important;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="font-weight-bold text-dark">
                                                <i class="fas fa-calculator text-primary mr-2"></i>
                                                Total Belanja Dipilih:
                                            </span>
                                            <span class="font-weight-bold text-success" style="font-size: 1.1rem;">
                                                Rp {{ number_format($availableBelanjas->whereIn('id', $selectedBelanjaIds)->sum('nilai'), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-info" style="border-radius: 12px;">
                                <i class="fas fa-info-circle mr-2"></i>
                                Pilih periode awal dan akhir terlebih dahulu untuk menampilkan daftar belanja GU yang tersedia.
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white d-flex justify-content-end">
                    <button type="button" wire:click="closeForm" class="btn btn-light text-secondary font-weight-600 mr-2 py-2 px-4" style="border-radius: 8px;">Batal</button>
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-primary font-weight-bold shadow-sm py-2 px-4" style="border-radius: 8px; background: var(--primary-color); border: none;">
                        <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan SPJ GU' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail SPJ GU -->
    <div wire:ignore.self class="modal fade" id="detailSpjGuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-card border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom pb-3 pt-4 px-4">
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark mb-1">
                            Detail SPJ GU
                            @if($detailSpjGu)
                                - <span class="text-primary">SPJ-{{ $detailSpjGu->nomor_spj }}</span>
                            @endif
                        </h5>
                        @if($detailSpjGu)
                            <small class="text-muted">
                                Periode: {{ \Carbon\Carbon::parse($detailSpjGu->periode_awal)->format('d/m/Y') }}
                                s/d {{ \Carbon\Carbon::parse($detailSpjGu->periode_akhir)->format('d/m/Y') }}
                            </small>
                        @endif
                    </div>
                    <button type="button" class="close" wire:click="closeDetail" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    @if($detailSpjGu)
                        <!-- Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light border-0 p-3 text-center" style="border-radius: 12px;">
                                    <small class="text-muted text-uppercase font-weight-bold">Tanggal SPJ</small>
                                    <div class="font-weight-bold mt-1">{{ \Carbon\Carbon::parse($detailSpjGu->tanggal_spj)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light border-0 p-3 text-center" style="border-radius: 12px;">
                                    <small class="text-muted text-uppercase font-weight-bold">Jumlah Belanja</small>
                                    <div class="font-weight-bold mt-1 text-primary">{{ $detailSpjGu->belanjas->count() }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light border-0 p-3 text-center" style="border-radius: 12px;">
                                    <small class="text-muted text-uppercase font-weight-bold">Total Nilai</small>
                                    <div class="font-weight-bold mt-1 text-success">Rp {{ number_format($detailSpjGu->total_nilai, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light border-0 p-3 text-center" style="border-radius: 12px;">
                                    <small class="text-muted text-uppercase font-weight-bold">Total Pajak</small>
                                    <div class="font-weight-bold mt-1 text-danger">Rp {{ number_format($detailSpjGu->total_pajak, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        @if($detailSpjGu->keterangan)
                            <div class="alert alert-light border mb-4" style="border-radius: 12px;">
                                <strong>Keterangan:</strong> {{ $detailSpjGu->keterangan }}
                            </div>
                        @endif

                        <!-- Detail Belanja -->
                        <div class="table-responsive">
                            <table class="table table-sm modern-table">
                                <thead>
                                    <tr class="text-center">
                                        <th width="40">No</th>
                                        <th width="100">No Bukti</th>
                                        <th width="100">Tanggal</th>
                                        <th>Uraian</th>
                                        <th>Sub Kegiatan</th>
                                        <th width="140" class="text-right">Nilai</th>
                                        <th width="120" class="text-right">Pajak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detailSpjGu->belanjas as $bel)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td><span class="code-badge">TBP-{{ $bel->no_bukti }}</span></td>
                                            <td>{{ $bel->tanggal }}</td>
                                            <td>{{ $bel->uraian }}</td>
                                            <td>
                                                <small>{{ $bel->rka->subKegiatan->nama ?? '-' }}</small>
                                            </td>
                                            <td class="text-right">
                                                <span class="amount-badge">Rp {{ number_format($bel->nilai, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-right">
                                                Rp {{ number_format($bel->pajak->sum('nominal'), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold" style="background: #f8fafc;">
                                        <td colspan="5" class="text-right">Total:</td>
                                        <td class="text-right text-success">Rp {{ number_format($detailSpjGu->total_nilai, 0, ',', '.') }}</td>
                                        <td class="text-right text-danger">Rp {{ number_format($detailSpjGu->total_pajak, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white">
                    <button type="button" wire:click="closeDetail" class="btn btn-light text-secondary font-weight-600 py-2 px-4" style="border-radius: 8px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
