@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPP-SPM TU Nihil</h3>
                    <p class="page-subtitle mb-0">Setor sisa TU ke Kasda: SPP-{{ $sppSpmTu['no_bukti'] ?? '' }}</p>
                </div>
                <a href="{{ url('spp-spm-tu') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="content-card">
            <!-- Info -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="stat-label">Nilai TU</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($sppSpmTu['total_nilai'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-label">Total Belanja</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($sppSpmTu['total_belanja'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: #fef3c7; color: #d97706;"><i class="fas fa-undo"></i></div>
                        <div class="stat-label">Sisa Disetor</div>
                        <div class="stat-value" style="font-size: 16px;">Rp {{ number_format($nilai_setor, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon {{ ($sppSpmTu['has_spj'] ?? false) ? '' : '' }}" style="background: {{ ($sppSpmTu['has_spj'] ?? false) ? '#dcfce7' : '#fee2e2' }}; color: {{ ($sppSpmTu['has_spj'] ?? false) ? '#16a34a' : '#dc2626' }};">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="stat-label">SPJ TU</div>
                        <div class="stat-value" style="font-size: 14px;">{{ ($sppSpmTu['has_spj'] ?? false) ? 'Sudah' : 'Belum' }}</div>
                    </div>
                </div>
            </div>

            @if(!($sppSpmTu['has_spj'] ?? false))
                <div class="alert alert-warning d-flex align-items-center" style="border-radius: 8px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    SPJ TU harus dibuat terlebih dahulu sebelum membuat TU Nihil.
                </div>
            @elseif($nilai_setor <= 0)
                <div class="alert alert-success d-flex align-items-center" style="border-radius: 8px;">
                    <i class="fas fa-check-circle mr-2"></i>
                    Tidak ada sisa TU. Semua dana TU telah terealisasi.
                </div>
            @else
                <!-- Form / Data Nihil -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-white" style="border-radius: 12px 12px 0 0;">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-undo mr-2 text-warning"></i>Data SPP-SPM TU Nihil</h6>
                    </div>
                    <div class="card-body">
                        @if($nihil)
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>No Bukti:</strong><br>{{ $nihil['no_bukti'] }}</div>
                                <div class="col-md-3"><strong>Tanggal:</strong><br>{{ date('d-m-Y', strtotime($nihil['tanggal'])) }}</div>
                                <div class="col-md-3"><strong>Nilai Setor:</strong><br>Rp {{ number_format($nihil['nilai_setor'], 0, ',', '.') }}</div>
                                <div class="col-md-3"><strong>Uraian:</strong><br>{{ $nihil['uraian'] ?? '-' }}</div>
                            </div>
                            @if($nihil['no_sp2d'] ?? false)
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>SP2D: {{ $nihil['no_sp2d'] }} ({{ date('d/m/Y', strtotime($nihil['tanggal_sp2d'])) }})</span>
                            @else
                                <button wire:click="openSp2dModal" class="btn btn-sm btn-outline-warning" style="border-radius: 6px;">
                                    <i class="fas fa-file-export mr-1"></i> Input SP2D Nihil
                                </button>
                            @endif
                            <div class="mt-3">
                                <button wire:click="edit" class="btn btn-warning btn-sm"><i class="fas fa-pencil-alt mr-1"></i> Edit</button>
                                <button wire:click="delete_confirmation" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Hapus</button>
                            </div>
                        @else
                            <form>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="font-weight-bold small">No Bukti</label>
                                        <input wire:model="no_bukti" type="text" class="form-control" placeholder="0001">
                                        @error('no_bukti') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="font-weight-bold small">Tanggal</label>
                                        <input wire:model="tanggal" type="date" class="form-control">
                                        @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="font-weight-bold small">Nilai Setor</label>
                                        <input type="text" class="form-control font-weight-bold" value="Rp {{ number_format($nilai_setor, 2, ',', '.') }}" readonly style="background: #e8f0fe; color: #1a73e8;">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="font-weight-bold small">Uraian</label>
                                        <input wire:model="uraian" type="text" class="form-control" placeholder="Opsional">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" wire:click="store" class="btn btn-success">
                                        <i class="fas fa-save mr-1"></i> Simpan TU Nihil
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal SP2D Nihil -->
    <div wire:ignore.self class="modal fade" id="sp2dNihilModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-export mr-2 text-primary"></i>SP2D TU Nihil</h5>
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
                    <button type="button" wire:click="saveSp2d" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
