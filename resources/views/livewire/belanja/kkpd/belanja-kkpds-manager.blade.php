@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Bukti Pengeluaran / Belanja KKPD</h3>
                    <p class="page-subtitle mb-0">Kelola data belanja ganti uang persediaan KKPD</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-add" wire:click="openForm" data-toggle="modal" data-target="#belanjaModal">
                        <i class="fas fa-plus mr-2"></i>Tambah Belanja
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
                        <div class="stat-description">Data Belanja KKPD</div>
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
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="d-flex align-items-center mr-3">
                        <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Bulan:</span>
                        <select wire:model.live="bulan" class="form-control custom-select-modern" style="width: 160px;">
                            <option value="">Bulan sekarang</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                     <div class="d-flex align-items-center">
                        <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                        <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
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
                            <th width="50">No</th>
                            <th width="120">No Bukti</th>
                            <th width="100">Tanggal</th>
                            <th>Uraian</th>
                            <th width="150" class="text-right">Nilai</th>
                            <th width="150" class="text-right">Penerimaan & Pajak</th>
                             @if (Auth::user()->role == 'admin')
                                <th width="50" class="text-center">Transfer</th>
                            @endif
                            <th width="50" class="text-center">SIPD</th>
                            <th width="50" class="text-center">Pj</th>
                            <th width="50" class="text-center">Pjk</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         @foreach ($belanjaKkpds as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $belanjaKkpds->firstItem() }}</span></td>
                                <td><span class="code-badge">TBP-{{ $row->no_bukti }}</span></td>
                                <td style="font-weight: 500;">{{ $row->tanggal }}</td>
                                <td class="text-left">{{ $row->uraian }}</td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($row->nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-right">
                                    <span
                                        class="badge {{ $row->nilai == ($row->total_penerimaan ?? 0) + ($row->total_pajak ?? 0) ? 'badge-success' : 'badge-danger' }}"
                                        data-toggle="tooltip"
                                        title="Penerimaan = {{ number_format($row->total_penerimaan ?? 0, 2) }} + Pajak = {{ number_format($row->total_pajak ?? 0, 2) }}"
                                        style="font-size: 12px; padding: 6px 10px; font-weight: 500;">
                                        Rp {{ number_format(($row->total_penerimaan ?? 0) + ($row->total_pajak ?? 0), 0, ',', '.') }}
                                    </span>
                                </td>
                                
                                @if (Auth::user()->role == 'admin')
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="transfer{{ $row->id }}"
                                                wire:click="toggleField({{ $row->id }}, 'is_transfer')"
                                                {{ $row->is_transfer ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="transfer{{ $row->id }}"></label>
                                        </div>
                                    </td>
                                @endif
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="sipd{{ $row->id }}"
                                            wire:click="toggleField({{ $row->id }}, 'is_sipd')"
                                            {{ $row->is_sipd ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="sipd{{ $row->id }}"></label>
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <button onclick="window.location.href='{{ route('penerimaan_kkpd', $row->id) }}'"
                                        class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button onclick="window.location.href='{{ route('pajak_kkpd', $row->id) }}'"
                                        class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <button wire:click="openPreview({{ $row->id }})" class="btn btn-primary btn-sm" style="border-radius: 6px 0 0 6px;" title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit">
                                            <i class="fas fa-pencil-alt text-white"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="btn-group ml-1">
                                        <button wire:click="printTai({{ $row->id }})" class="btn btn-secondary btn-sm" style="border-radius: 6px 0 0 6px;" title="Cetak">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button wire:click="downloadTai({{ $row->id }})" class="btn btn-success btn-sm" style="border-radius: 0 6px 6px 0;" title="Download">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
             <!-- Pagination -->
            <div class="mt-3">
                {{ $belanjaKkpds->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <div wire:ignore.self class="modal fade" id="previewBelanjaModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Belanja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closePreview" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <livewire:belanja.kkpd.belanja-preview-kkpd />
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="belanjaModal" tabindex="-1" aria-labelledby="belanjaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit Belanja' : 'Tambah Belanja' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group row mb-3">
                            <label for="tanggal" class="col-md-3 form-label" style="min-width: 150px;">Tanggal</label>
                            <div class="col-md-9">
                                <input wire:model="tanggal" type="date" class="form-control" id="tanggal">
                                @error('tanggal') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="rekening" class="col-md-3 form-label" style="min-width: 150px;">Rekening</label>
                            <div class="col-md-9">
                                <button type="button" wire:click='openModal' class="btn btn-primary">Pilih Rekening</button>
                            </div>
                        </div>
                        
                        @if ($rincian_subkegiatan)
                            <div class="form-group row mb-3">
                                <label class="col-md-3 form-label" style="min-width: 150px;">Sub Kegiatan</label>
                                <div class="col-md-9">
                                    <textarea class="form-control" readonly>{{ $rka && $rka->subKegiatan ? $rka->subKegiatan->kode . ' - ' . $rka->subKegiatan->nama : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 form-label" style="min-width: 150px;">Rekening Belanja</label>
                                <div class="col-md-9">
                                    <textarea class="form-control" readonly>{{ $rka ? $rka->kode_belanja . ' - ' . $rka->nama_belanja : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 form-label" style="min-width: 150px;">Sisa Anggaran</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" readonly value="{{ number_format($rka->sisaanggaran, 2, ',', '.') }}">
                                </div>
                            </div>
                        @endif

                        <div class="form-group row mb-3">
                            <label for="nilai" class="col-md-3 form-label" style="min-width: 150px;">Nilai</label>
                            <div class="col-md-5">
                                <input wire:model.live="nilai" type="number" class="form-control" id="nilai" placeholder="Enter Nilai">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="{{ number_format((float) ($nilai ?? 0), 2, ',', '.') }}" readonly>
                            </div>
                            @error('nilai') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group row mb-3">
                            <label for="uraian" class="col-md-3 form-label" style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" id="uraian"></textarea>
                                @error('uraian') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
                    <button type="button" wire:click="closeForm" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="subkegiatanModal" tabindex="-1" aria-labelledby="subkegiatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Program - Kegiatan - Sub Kegiatan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <livewire:program-hierarchy>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="viewBelanja" tabindex="-1" aria-labelledby="viewBelanja" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBelanja"></h5>
                    <button type="button" class="close" wire:click="closeModalPdf" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <embed src="{{ route('helper.show-picture', ['path' => 'public/reports/laporan_belanja_' . $pathpdf]) }}" class="col-12" height="600px" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="closeModalPdf">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
@push('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('belanja', (event) => {
                $('#viewBelanja').modal("show");
            });
        });
    </script>
@endpush
