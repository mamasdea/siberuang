@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPP-SPM TU</h3>
                    <p class="page-subtitle mb-0">Kelola data SPP-SPM Tambahan Uang Persediaan</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="openForm">
                        <i class="fas fa-plus mr-2"></i>Tambah SPP-SPM TU
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                        <div class="stat-description">Data SPP-SPM TU</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-label">Total Nominal</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        <div class="stat-description">Akumulasi Nilai TU</div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
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

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 160px;">No Bukti (SPP)</th>
                            <th style="width: 100px;">Tanggal</th>
                            <th>Uraian</th>
                            <th style="width: 140px;" class="text-right">Total Nilai</th>
                            <th style="width: 90px;" class="text-center">Status</th>
                            <th style="width: 320px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sppSpmTus as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $sppSpmTus->firstItem() }}</span></td>
                                <td>
                                    <span class="code-badge" style="white-space: nowrap; font-size: 11px;">
                                        SPP-{{ $row->no_bukti }}/Diskominfo/{{ $row->tahun_bukti }}
                                    </span>
                                </td>
                                <td style="font-weight: 500; white-space: nowrap;">
                                    {{ date('d-m-Y', strtotime($row->tanggal)) }}
                                    @if($row->tanggal_sp2d)
                                        <br><span class="badge badge-success" style="font-size: 9px;">SP2D {{ date('d/m/Y', strtotime($row->tanggal_sp2d)) }}</span>
                                    @endif
                                </td>
                                <td style="overflow: hidden;">
                                    <span style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $row->uraian ?? '-' }}</span>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <span class="amount-badge">Rp {{ number_format($row->total_nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($row->nihil)
                                        <span class="badge badge-dark" style="font-size: 10px;">Nihil</span>
                                    @elseif($row->spjTu)
                                        <span class="badge badge-info" style="font-size: 10px;">SPJ</span>
                                    @elseif($row->tanggal_sp2d)
                                        <span class="badge badge-success" style="font-size: 10px;">SP2D</span>
                                    @else
                                        <span class="badge badge-warning" style="font-size: 10px;">Proses</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- SP2D --}}
                                    @if($row->tanggal_sp2d && $row->no_sp2d)
                                        <button wire:click="openSp2dModal({{ $row->id }})" class="btn btn-sm btn-success" style="border-radius: 6px; font-size: 10px;" title="SP2D terbit">
                                            <i class="fas fa-check-circle"></i> SP2D
                                        </button>
                                    @else
                                        <button wire:click="openSp2dModal({{ $row->id }})" class="btn btn-sm btn-outline-warning" style="border-radius: 6px; font-size: 10px;" title="Input SP2D">
                                            <i class="fas fa-file-export"></i> SP2D
                                        </button>
                                    @endif

                                    {{-- Belanja (hanya jika sudah SP2D) --}}
                                    @if($row->tanggal_sp2d)
                                        <a href="{{ url('belanja-tu/' . $row->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 10px;" title="Belanja TU">
                                            <i class="fas fa-shopping-bag"></i> Belanja
                                        </a>
                                    @endif

                                    {{-- SPJ --}}
                                    @if($row->belanjaTus->count() > 0)
                                        <a href="{{ url('spj-tu/' . $row->id) }}" class="btn btn-sm btn-outline-info" style="border-radius: 6px; font-size: 10px;" title="SPJ TU">
                                            <i class="fas fa-clipboard-check"></i> SPJ
                                        </a>
                                    @endif

                                    {{-- Nihil --}}
                                    @if($row->spjTu && $row->total_nilai > $row->belanjaTus->sum('nilai'))
                                        <a href="{{ url('tu-nihil/' . $row->id) }}" class="btn btn-sm btn-outline-dark" style="border-radius: 6px; font-size: 10px;" title="TU Nihil">
                                            <i class="fas fa-undo"></i> Nihil
                                        </a>
                                    @endif

                                    {{-- Edit & Hapus --}}
                                    <div class="btn-group ml-1">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit"><i class="fas fa-pencil-alt text-white"></i></button>
                                        <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada data SPP-SPM TU
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $sppSpmTus->links() }}</div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="sppSpmTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit SPP-SPM TU' : 'Tambah SPP-SPM TU' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- No Bukti -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">No Bukti (SPP)</label>
                            <div class="col-md-2"><input type="text" class="form-control" value="SPP-" readonly style="background-color: #f4f6f9;"></div>
                            <div class="col-md-3">
                                <input wire:model="no_bukti" type="text" class="form-control" placeholder="0001">
                                @error('no_bukti') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4"><input type="text" class="form-control" value="/Diskominfo/{{ $tahunTransaksi ?? date('Y') }}" readonly style="background-color: #f4f6f9;"></div>
                        </div>

                        <!-- No SPM SIPD -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">No SPM SIPD</label>
                            <div class="col-md-9">
                                <input wire:model="no_spm_sipd" type="text" class="form-control" placeholder="Nomor sesuai SPM SIPD">
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

                        <!-- Sub Kegiatan -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Sub Kegiatan</label>
                            <div class="col-md-7">
                                @if ($sub_kegiatan_id)
                                    <input type="text" class="form-control" readonly value="{{ $sub_kegiatan_kode }} - {{ $sub_kegiatan_nama }}">
                                @else
                                    <button type="button" class="btn btn-outline-primary btn-block" wire:click="openModal">
                                        <i class="fas fa-search mr-1"></i> Pilih Sub Kegiatan
                                    </button>
                                @endif
                                @error('sub_kegiatan_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                @if ($sub_kegiatan_id)
                                    <button type="button" class="btn btn-warning btn-block" wire:click="openModal"><i class="fas fa-edit"></i> Ubah</button>
                                @endif
                            </div>
                        </div>

                        <!-- Total Nilai -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Total Nilai</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control font-weight-bold" value="Rp {{ number_format(collect($rkas)->sum('nilai'), 2, ',', '.') }}" readonly style="background-color: #e8f0fe; color: #1a73e8;">
                            </div>
                        </div>

                        <!-- Detail Rekening -->
                        @if ($sub_kegiatan_id)
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" style="background: white;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Nama Belanja</th>
                                                    <th width="180">Sisa Anggaran</th>
                                                    <th width="200">Nilai TU</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($rkas as $index => $rkaItem)
                                                    <tr>
                                                        <td>
                                                            <small class="text-muted d-block">{{ $rkaItem['kode_belanja'] }}</small>
                                                            {{ $rkaItem['nama_belanja'] }}
                                                        </td>
                                                        <td class="text-right">{{ number_format($rkaItem['initial_sisa'] - $rkaItem['nilai'], 0, ',', '.') }}</td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm text-right" wire:model.live="rkas.{{ $index }}.nilai" placeholder="0">
                                                            @if ($rkaItem['nilai'] > $rkaItem['initial_sisa'])
                                                                <small class="text-danger d-block mt-1">Max: {{ number_format($rkaItem['initial_sisa'], 0, ',', '.') }}</small>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center">Data RKAS tidak tersedia.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-soft-info d-flex align-items-center mb-3">
                                <i class="fas fa-info-circle mr-2"></i> Pilih Sub Kegiatan terlebih dahulu.
                            </div>
                        @endif

                        <!-- Uraian -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 form-label" style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" placeholder="Keperluan TU..." rows="3"></textarea>
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

    <!-- Modal Sub Kegiatan -->
    <div wire:ignore.self class="modal fade" id="subkegiatanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Sub Kegiatan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <livewire:sub-kegiatan-hierarchy />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal SP2D -->
    <div wire:ignore.self class="modal fade" id="sp2dTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-export mr-2 text-primary"></i>Input SP2D TU</h5>
                    <button type="button" class="close" wire:click="closeSp2dModal" aria-label="Close"><span>&times;</span></button>
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
                    <button type="button" wire:click="saveSp2d" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan SP2D</button>
                </div>
            </div>
        </div>
    </div>
</div>
