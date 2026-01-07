@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Bukti Pengeluaran / Belanja GU</h3>
                    <p class="page-subtitle mb-0">Kelola data belanja ganti uang persediaan</p>
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
                        <div class="stat-description">Data Belanja GU</div>
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
                            <th width="80" class="text-center">Arsip</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         @foreach ($belanja as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $belanja->firstItem() }}</span></td>
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
                                    <button onclick="window.location.href='{{ route('penerimaan', $row->id) }}'"
                                        class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button onclick="window.location.href='{{ route('pajak', $row->id) }}'"
                                        class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    @if ($row->arsip)
                                        <button wire:click="viewArsip({{ $row->id }})" class="btn btn-sm btn-outline-info" style="border-radius: 6px;" title="Lihat Arsip">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @else
                                        <button wire:click="openUploadModal({{ $row->id }})" class="btn btn-sm btn-outline-success" style="border-radius: 6px;" title="Upload Arsip">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </button>
                                    @endif
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
                {{ $belanja->links() }}
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
                    <livewire:belanja.gu.belanja-preview />
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="belanjaModal" tabindex="-1" aria-labelledby="belanjaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-card border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white">
                    <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.25rem;">
                        {{ $isEdit ? 'Edit Data Belanja' : 'Tambah Belanja Baru' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <form>
                        <div class="form-group mb-4">
                            <label for="tanggal" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Tanggal Transaksi</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                </div>
                                <input wire:model="tanggal" type="date" class="form-control bg-light border-0 text-dark font-weight-500" id="tanggal" style="height: 48px;">
                            </div>
                            @error('tanggal') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Rekening Belanja</label>
                            <div class="card bg-light border-0 p-3" style="border-radius: 12px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-start">
                                        <div class="mr-3 mt-1">
                                            <div class="bg-white rounded p-2 shadow-sm text-primary">
                                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            @if ($rincian_subkegiatan && $rka)
                                                <h5 class="font-weight-bold text-dark mb-1">{{ $rka->subKegiatan->nama }}</h5>
                                                <p class="mb-2 text-secondary font-weight-bold" style="font-size: 1rem;">{{ $rka->kode_belanja }} - {{ $rka->nama_belanja }}</p>
                                                <span class="badge badge-success px-2 py-1" style="font-weight: 600; font-size: 14px;">
                                                    Sisa Anggaran: Rp {{ number_format($rka->sisaanggaran, 2, ',', '.') }}
                                                </span>
                                            @else
                                                <div class="text-muted font-weight-500 mt-1">Belum ada rekening yang dipilih</div>
                                                <small class="text-secondary">Silakan pilih rekening belanja terlebih dahulu</small>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" wire:click='openModal' class="btn btn-primary shadow-sm px-3 py-2" style="border-radius: 8px; font-weight: 500; font-size: 13px;">
                                        <i class="fas fa-search mr-1"></i> {{ $rincian_subkegiatan ? 'Ganti' : 'Pilih' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="nilai" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Nominal Belanja</label>
                            <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 font-weight-bold text-dark pl-3">Rp</span>
                                </div>
                                <input wire:model.live="nilai" type="number" class="form-control border-0 pl-1" id="nilai" placeholder="0" style="height: 50px; font-size: 1.25rem; font-weight: 600;">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                <small class="text-muted">Masukkan nominal tanpa titik/koma</small>
                                <small class="text-dark font-weight-bold" style="font-size: 13px;">
                                    Terbilang: Rp {{ number_format((float) ($nilai ?? 0), 2, ',', '.') }}
                                </small>
                            </div>
                            @error('nilai') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="uraian" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Uraian Belanja</label>
                            <textarea wire:model="uraian" class="form-control bg-light border-0" id="uraian" rows="3" style="border-radius: 12px; resize: none; padding: 16px;" placeholder="Tuliskan keterangan detail belanja disini..."></textarea>
                            @error('uraian') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>


                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white d-flex justify-content-end">
                    <button type="button" wire:click="closeForm" class="btn btn-light text-secondary font-weight-600 mr-2 py-2 px-4" style="border-radius: 8px;">Batal</button>
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-primary font-weight-bold shadow-sm py-2 px-4" style="border-radius: 8px; background: var(--primary-color); border: none;">
                        <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="subkegiatanModal" tabindex="-1" aria-labelledby="subkegiatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded mr-3" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-sitemap fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold text-dark m-0">Pilih Rekening</h5>
                            <small class="text-muted">Pilih program, kegiatan, dan rekening belanja</small>
                        </div>
                    </div>
                    <button type="button" class="close" wire:click="closeModal" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-light">
                    <livewire:program-hierarchy>
                </div>
                <div class="modal-footer bg-white border-top p-3" style="border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-light font-weight-600 px-4 text-secondary" wire:click="closeModal" style="border-radius: 8px;">Tutup</button>

</div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="uploadArsipModal" tabindex="-1" aria-labelledby="uploadArsipLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="uploadArsipLabel">Upload Arsip</h5>
                    <button type="button" class="close" wire:click="closeUploadModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveArsip">
                        <div class="form-group">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2">Pilih File PDF</label>
                             <input type="file" wire:model="fileArsip" class="form-control bg-light border-0" accept=".pdf" style="height: auto; padding: 12px; border-radius: 12px;">
                             <div wire:loading wire:target="fileArsip" class="text-info small mt-1 pl-1">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Uploading...
                            </div>
                            @error('fileArsip') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" wire:click="closeUploadModal" class="btn btn-light mr-2" style="border-radius: 8px;">Batal</button>
                            <button type="submit" class="btn btn-primary" style="border-radius: 8px;" wire:loading.attr="disabled">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div wire:ignore.self class="modal fade" id="previewArsipModal" tabindex="-1" aria-labelledby="previewArsipLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="previewArsipLabel">Preview Arsip</h5>
                    <button type="button" class="close" wire:click="closeViewArsip" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    @if($previewArsipUrl)
                        <iframe src="{{ $previewArsipUrl }}" width="100%" height="700px" style="border-radius: 0 0 12px 12px; border: none;"></iframe>
                    @else
                        <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 pt-2 pb-3 px-4 d-flex justify-content-between align-items-center bg-light" style="border-radius: 0 0 12px 12px;">
                    <div class="d-flex align-items-center">
                         <div style="position: relative; overflow: hidden; display: inline-block;">
                            <button class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-exchange-alt mr-1"></i> Ganti File
                            </button>
                            <input type="file" wire:model="fileArsip" accept=".pdf" 
                                style="font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer;">
                         </div>
                         
                         @if($fileArsip)
                            <span class="ml-2 badge badge-info">PDF Dipilih</span>
                            <button class="btn btn-success btn-sm ml-2 shadow-sm" wire:click="updateArsipFromPreview" wire:loading.attr="disabled" style="border-radius: 6px;">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                         @endif
                         
                         <div wire:loading wire:target="fileArsip" class="ml-2 small text-muted">
                             <i class="fas fa-spinner fa-spin"></i> Uploading...
                         </div>
                         @error('fileArsip') <span class="text-danger small ml-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex">
                         <button type="button" class="btn btn-light text-secondary font-weight-bold" wire:click="closeViewArsip" style="border-radius: 8px;">Tutup</button>
                        @if($previewArsipUrl)
                            <a href="{{ $previewArsipUrl }}" target="_blank" class="btn btn-primary ml-2 shadow-sm" style="border-radius: 8px;">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        @endif
                    </div>
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
