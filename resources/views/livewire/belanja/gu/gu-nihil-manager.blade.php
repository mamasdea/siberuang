@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPP-SPM GU Nihil</h3>
                    <p class="page-subtitle mb-0">Setor sisa belanja GU ke Kasda: SPJ-{{ $spjGu['nomor_spj'] ?? '' }}</p>
                </div>
                <a href="{{ url('gu-nihil') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="content-card">
            <!-- Info SPJ GU -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue"><i class="fas fa-file-signature"></i></div>
                        <div class="stat-label">SPJ GU</div>
                        <div class="stat-value" style="font-size: 16px;">SPJ-{{ $spjGu['nomor_spj'] ?? '' }}</div>
                        <div class="stat-description">{{ date('d/m', strtotime($spjGu['periode_awal'] ?? now())) }} - {{ date('d/m/Y', strtotime($spjGu['periode_akhir'] ?? now())) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-label">Total Belanja</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($spjGu['total_belanja'] ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-description">{{ $spjGu['jumlah_belanja'] ?? 0 }} transaksi</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-receipt"></i></div>
                        <div class="stat-label">Total Pajak</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($spjGu['total_pajak'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-undo"></i></div>
                        <div class="stat-label">Nilai Setor</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($nilai_setor, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Form / Data Nihil -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white" style="border-radius: 12px 12px 0 0; padding: 16px 20px;">
                    <h5 class="font-weight-bold mb-0" style="font-size: 16px;"><i class="fas fa-undo mr-2 text-warning"></i>Data SPP-SPM GU Nihil</h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    @if($nihil && !$isEdit)
                        {{-- Tampilan Data --}}
                        <div class="row mb-3" style="font-size: 14px;">
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">No SPP</small><strong>{{ $nihil['no_spp'] }}</strong></div>
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">No STS</small><strong>{{ $nihil['no_sts'] ?? '-' }}</strong></div>
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">No SPM Nihil SIPD</small><strong>{{ $nihil['no_spm_gu_nihil_sipd'] ?? '-' }}</strong></div>
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">Tanggal</small><strong>{{ date('d-m-Y', strtotime($nihil['tanggal'])) }}</strong></div>
                        </div>
                        <div class="row mb-3" style="font-size: 14px;">
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">Nilai Setor</small><strong>Rp {{ number_format($nihil['nilai_setor'], 0, ',', '.') }}</strong></div>
                            <div class="col-md-3 mb-2"><small class="text-muted d-block">Uraian</small><strong>{{ $nihil['uraian'] ?? '-' }}</strong></div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">Bukti Setor</small>
                                @if($nihil['bukti_setor'] ?? false)
                                    <a href="{{ Storage::disk('gcs')->url($nihil['bukti_setor']) }}" target="_blank" class="btn btn-sm btn-outline-info" style="border-radius: 6px;">
                                        <i class="fas fa-file-pdf mr-1"></i> Lihat Bukti Setor
                                    </a>
                                @else
                                    <span class="text-muted">Belum diupload</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($nihil['no_sp2d'] ?? false)
                                <span class="badge badge-success mr-2"><i class="fas fa-check-circle mr-1"></i>SP2D: {{ $nihil['no_sp2d'] }} ({{ date('d/m/Y', strtotime($nihil['tanggal_sp2d'])) }})</span>
                            @else
                                <button wire:click="openSp2dModal" class="btn btn-sm btn-outline-warning mr-2" style="border-radius: 6px;">
                                    <i class="fas fa-file-export mr-1"></i> Input SP2D
                                </button>
                            @endif
                            <button wire:click="edit" class="btn btn-warning btn-sm mr-1" style="border-radius: 6px;"><i class="fas fa-pencil-alt mr-1 text-white"></i> Edit</button>
                            <button wire:click="delete_confirmation" class="btn btn-danger btn-sm" style="border-radius: 6px;"><i class="fas fa-trash-alt mr-1"></i> Hapus</button>
                        </div>
                    @else
                        {{-- Form --}}
                        <form>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">No SPP</label>
                                    <input wire:model="no_spp" type="text" class="form-control" placeholder="0001">
                                    @error('no_spp') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">No STS</label>
                                    <input wire:model="no_sts" type="text" class="form-control" placeholder="Nomor STS">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">No SPM Nihil SIPD</label>
                                    <input wire:model="no_spm_gu_nihil_sipd" type="text" class="form-control" placeholder="Nomor SPM SIPD">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">Tanggal</label>
                                    <input wire:model="tanggal" type="date" class="form-control">
                                    @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">Nilai Setor</label>
                                    <input type="text" class="form-control font-weight-bold" value="Rp {{ number_format($nilai_setor, 2, ',', '.') }}" readonly style="background: #e8f0fe; color: #1a73e8;">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="font-weight-bold small">Uraian</label>
                                    <input wire:model="uraian" type="text" class="form-control" placeholder="Opsional">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold small">Upload Bukti Setor (PDF)</label>
                                    <input type="file" wire:model="fileBuktiSetor" class="form-control" accept=".pdf" style="height: auto; padding: 8px;">
                                    <div wire:loading wire:target="fileBuktiSetor" class="text-info small mt-1"><i class="fas fa-spinner fa-spin mr-1"></i> Uploading...</div>
                                    @if($existingBuktiSetor)
                                        <small class="text-success mt-1 d-block"><i class="fas fa-check-circle mr-1"></i> Bukti setor sudah ada</small>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2">
                                @if($isEdit)
                                    <button type="button" wire:click="update" class="btn btn-success" style="border-radius: 8px;"><i class="fas fa-save mr-1"></i> Update</button>
                                    <button type="button" wire:click="$set('isEdit', false)" class="btn btn-light" style="border-radius: 8px;">Batal</button>
                                @else
                                    <button type="button" wire:click="store" class="btn btn-success" style="border-radius: 8px;"><i class="fas fa-save mr-1"></i> Simpan GU Nihil</button>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal SP2D -->
    <div wire:ignore.self class="modal fade" id="sp2dGuNihilModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-export mr-2 text-primary"></i>SP2D GU Nihil</h5>
                    <button type="button" class="close" wire:click="closeSp2dModal"><span>&times;</span></button>
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
                    <button type="button" wire:click="saveSp2d" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
