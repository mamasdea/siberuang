@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Belanja TU</h3>
                    <p class="page-subtitle mb-0">Realisasi belanja dari SPP-SPM TU: SPP-{{ $sppSpmTu['no_bukti'] ?? '' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ url('spp-spm-tu') }}" class="btn btn-outline-secondary mr-2" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    @if(!($sppSpmTu['has_spj'] ?? false))
                        <button type="button" class="btn btn-modern-add" wire:click="openForm" data-toggle="modal" data-target="#belanjaTuModal">
                            <i class="fas fa-plus mr-2"></i>Tambah Belanja
                        </button>
                    @else
                        <span class="badge badge-info" style="font-size: 13px; padding: 8px 16px;"><i class="fas fa-lock mr-1"></i> Sudah di-SPJ-kan</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Info SPP-SPM TU -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="stat-label">Nilai TU</div>
                        <div class="stat-value" style="font-size: 18px;">Rp {{ number_format($sppSpmTu['total_nilai'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card h-100">
                        <div class="stat-icon green"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-label">Total Belanja</div>
                        <div class="stat-value" style="font-size: 18px;">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card h-100">
                        <div class="stat-icon {{ $sisaTu > 0 ? 'blue' : '' }}" style="{{ $sisaTu <= 0 ? 'background: #fee2e2; color: #dc2626;' : '' }}">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-label">Sisa TU</div>
                        <div class="stat-value" style="font-size: 18px;">Rp {{ number_format($sisaTu, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="d-flex align-items-center mr-3">
                        <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Bulan:</span>
                        <select wire:model.live="bulan" class="form-control custom-select-modern" style="width: 160px;">
                            <option value="">Semua</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
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
                    <input type="text" class="form-control search-input" wire:model.live.debounce.300ms="search" placeholder="Cari No Bukti, Uraian...">
                    @if($search)
                        <button type="button" class="clear-search" wire:click="$set('search', '')"><i class="fas fa-times"></i></button>
                    @endif
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr class="text-center">
                            <th width="40">No</th>
                            <th width="100">No Bukti</th>
                            <th width="100">Tanggal</th>
                            <th>Uraian</th>
                            <th width="140" class="text-right">Nilai</th>
                            <th width="120" class="text-right">Penerimaan & Pajak</th>
                            @if (Auth::user()->role == 'admin')
                                <th width="50" class="text-center">Transfer</th>
                            @endif
                            <th width="50" class="text-center">SIPD</th>
                            <th width="50" class="text-center">Penerimaan</th>
                            <th width="50" class="text-center">Pajak</th>
                            <th width="50" class="text-center">Arsip</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($belanjaTus as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $belanjaTus->firstItem() }}</span></td>
                                <td><span class="code-badge">TBP-{{ $row->no_bukti }}</span></td>
                                <td style="font-weight: 500;">{{ $row->tanggal }}</td>
                                <td class="text-left">{{ $row->uraian }}</td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($row->nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-right">
                                    @php
                                        $totalPen = $row->penerimaanTus->sum('nominal');
                                        $totalPaj = $row->pajakTus->sum('nominal');
                                    @endphp
                                    <span class="badge {{ $row->nilai == $totalPen + $totalPaj ? 'badge-success' : 'badge-danger' }}"
                                        data-toggle="tooltip"
                                        title="Penerimaan = {{ number_format($totalPen, 2) }} + Pajak = {{ number_format($totalPaj, 2) }}"
                                        style="font-size: 12px; padding: 6px 10px; font-weight: 500;">
                                        Rp {{ number_format($totalPen + $totalPaj, 0, ',', '.') }}
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
                                    <a href="{{ url('penerimaan-tu/' . $row->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                                        <i class="fas fa-coins"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('pajak-tu/' . $row->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                                        <i class="fas fa-receipt"></i>
                                    </a>
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
                                        @if($sppSpmTu['has_spj'] ?? false)
                                            <button class="btn btn-secondary btn-sm" disabled title="Sudah di-SPJ-kan"><i class="fas fa-pencil-alt"></i></button>
                                            <button class="btn btn-secondary btn-sm" disabled title="Sudah di-SPJ-kan"><i class="fas fa-trash-alt"></i></button>
                                        @else
                                            <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit"><i class="fas fa-pencil-alt text-white"></i></button>
                                            <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada belanja TU
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $belanjaTus->links() }}</div>
        </div>
    </div>

    <!-- Modal Form Belanja TU (seperti GU) -->
    <div wire:ignore.self class="modal fade" id="belanjaTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-card border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white">
                    <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.25rem;">
                        {{ $isEdit ? 'Edit Belanja TU' : 'Tambah Belanja TU' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <form>
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Tanggal Transaksi</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                </div>
                                <input wire:model="tanggal" type="date" class="form-control bg-light border-0 text-dark font-weight-500" style="height: 48px;">
                            </div>
                            @error('tanggal') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Rekening Belanja</label>
                            <div class="card bg-light border-0 p-3" style="border-radius: 12px;">
                                <select wire:model="rka_id" class="form-control bg-white border-0" style="height: 48px; border-radius: 8px;">
                                    <option value="">-- Pilih Rekening Belanja --</option>
                                    @foreach($availableRkas as $rka)
                                        <option value="{{ $rka['id'] }}">
                                            {{ $rka['kode_belanja'] }} - {{ $rka['nama_belanja'] }}
                                            (Sisa: Rp {{ number_format($rka['sisa'], 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($rka_id)
                                    @php $selectedRka = collect($availableRkas)->firstWhere('id', $rka_id); @endphp
                                    @if($selectedRka)
                                        <div class="mt-2">
                                            <span class="badge badge-success px-2 py-1" style="font-weight: 600; font-size: 14px;">
                                                Sisa: Rp {{ number_format($selectedRka['sisa'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            @error('rka_id') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Nominal Belanja</label>
                            <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 font-weight-bold text-dark pl-3">Rp</span>
                                </div>
                                <input wire:model.live="nilai" type="number" class="form-control border-0 pl-1" placeholder="0" style="height: 50px; font-size: 1.25rem; font-weight: 600;">
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
                            <label class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">Uraian Belanja</label>
                            <textarea wire:model="uraian" class="form-control bg-light border-0" rows="3" style="border-radius: 12px; resize: none; padding: 16px;" placeholder="Tuliskan keterangan detail belanja disini..."></textarea>
                            @error('uraian') <span class="text-danger small mt-1 pl-1 d-block">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white d-flex justify-content-end">
                    <button type="button" wire:click="closeForm" class="btn btn-light text-secondary font-weight-600 mr-2 py-2 px-4" style="border-radius: 8px;">Batal</button>
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-primary font-weight-bold shadow-sm py-2 px-4" style="border-radius: 8px;">
                        <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Arsip -->
    <div wire:ignore.self class="modal fade" id="uploadArsipTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold">Upload Arsip</h5>
                    <button type="button" class="close" wire:click="closeUploadModal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary text-uppercase small mb-2">Pilih File PDF</label>
                        <input type="file" wire:model="fileArsip" class="form-control bg-light border-0" accept=".pdf" style="height: auto; padding: 12px; border-radius: 12px;">
                        <div wire:loading wire:target="fileArsip" class="text-info small mt-1"><i class="fas fa-spinner fa-spin mr-1"></i> Uploading...</div>
                        @error('fileArsip') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" wire:click="closeUploadModal" class="btn btn-light mr-2" style="border-radius: 8px;">Batal</button>
                        <button type="button" wire:click="saveArsip" class="btn btn-primary" style="border-radius: 8px;" wire:loading.attr="disabled">Upload</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Arsip -->
    <div wire:ignore.self class="modal fade" id="previewArsipTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold">Preview Arsip</h5>
                    <button type="button" class="close" wire:click="closeViewArsip"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    @if($previewArsipUrl)
                        <iframe src="{{ $previewArsipUrl }}" width="100%" height="700px" style="border-radius: 0 0 12px 12px; border: none;"></iframe>
                    @endif
                </div>
                <div class="modal-footer border-top-0 pt-2 pb-3 px-4 d-flex justify-content-between bg-light" style="border-radius: 0 0 12px 12px;">
                    <div class="d-flex align-items-center">
                        <div style="position: relative; overflow: hidden; display: inline-block;">
                            <button class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-exchange-alt mr-1"></i> Ganti File
                            </button>
                            <input type="file" wire:model="fileArsip" accept=".pdf" style="font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer;">
                        </div>
                        @if($fileArsip)
                            <span class="ml-2 badge badge-info">PDF Dipilih</span>
                            <button class="btn btn-success btn-sm ml-2" wire:click="updateArsipFromPreview" wire:loading.attr="disabled" style="border-radius: 6px;">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        @endif
                        <div wire:loading wire:target="fileArsip" class="ml-2 small text-muted"><i class="fas fa-spinner fa-spin"></i> Uploading...</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-light text-secondary font-weight-bold" wire:click="closeViewArsip" style="border-radius: 8px;">Tutup</button>
                        @if($previewArsipUrl)
                            <a href="{{ $previewArsipUrl }}" target="_blank" class="btn btn-primary ml-2" style="border-radius: 8px;">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
