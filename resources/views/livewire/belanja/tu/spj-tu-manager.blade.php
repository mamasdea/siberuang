@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">SPJ TU</h3>
                    <p class="page-subtitle mb-0">Pertanggungjawaban TU: SPP-{{ $sppSpmTu['no_bukti'] ?? '' }}</p>
                </div>
                <a href="{{ url('spp-spm-tu') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="content-card">
            <!-- Info -->
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
                        <div class="stat-icon {{ $sisaTu > 0 ? 'blue' : '' }}" style="{{ $sisaTu <= 0 ? 'background: #dcfce7; color: #16a34a;' : '' }}">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-label">Sisa TU</div>
                        <div class="stat-value" style="font-size: 18px;">Rp {{ number_format($sisaTu, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Form / Data SPJ -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white" style="border-radius: 12px 12px 0 0; padding: 16px 20px;">
                    <h5 class="font-weight-bold mb-0" style="font-size: 16px;"><i class="fas fa-clipboard-check mr-2 text-primary"></i>Data SPJ TU</h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    @if($spjTu && !$isEdit)
                        {{-- Tampilan Data SPJ --}}
                        <div class="row" style="font-size: 14px;">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Nomor SPJ</small>
                                <strong style="font-size: 15px;">{{ $spjTu['nomor_spj'] }}</strong>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Tanggal SPJ</small>
                                <strong style="font-size: 15px;">{{ date('d-m-Y', strtotime($spjTu['tanggal_spj'])) }}</strong>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Keterangan</small>
                                <strong style="font-size: 15px;">{{ $spjTu['keterangan'] ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button wire:click="edit" class="btn btn-warning btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-pencil-alt mr-1 text-white"></i> Edit
                            </button>
                            <button wire:click="delete_confirmation" class="btn btn-danger btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                            </button>
                        </div>
                    @else
                        {{-- Form Input / Edit SPJ --}}
                        <form>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="font-weight-bold" style="font-size: 13px;">Tanggal SPJ</label>
                                    <input wire:model="tanggal_spj" type="date" class="form-control" style="height: 44px;">
                                    @error('tanggal_spj') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="font-weight-bold" style="font-size: 13px;">Keterangan</label>
                                    <input wire:model="keterangan" type="text" class="form-control" placeholder="Opsional" style="height: 44px;">
                                </div>
                                <div class="col-md-4 d-flex align-items-end mb-3">
                                    @if($isEdit)
                                        <button type="button" wire:click="update" class="btn btn-success mr-2" style="border-radius: 8px; height: 44px;">
                                            <i class="fas fa-save mr-1"></i> Update SPJ
                                        </button>
                                        <button type="button" wire:click="$set('isEdit', false)" class="btn btn-light" style="border-radius: 8px; height: 44px;">
                                            Batal
                                        </button>
                                    @else
                                        <button type="button" wire:click="store" class="btn btn-success" style="border-radius: 8px; height: 44px;">
                                            <i class="fas fa-save mr-1"></i> Simpan SPJ TU
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Daftar Belanja TU -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white" style="border-radius: 12px 12px 0 0; padding: 16px 20px;">
                    <h5 class="font-weight-bold mb-0" style="font-size: 16px;"><i class="fas fa-list mr-2 text-info"></i>Daftar Belanja TU</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th width="100">No Bukti</th>
                                    <th width="100">Tanggal</th>
                                    <th>Uraian</th>
                                    <th width="150">Rekening</th>
                                    <th width="140" class="text-right">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($belanjaTus as $idx => $b)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><span class="code-badge">TBP-{{ $b['no_bukti'] }}</span></td>
                                        <td>{{ date('d-m-Y', strtotime($b['tanggal'])) }}</td>
                                        <td>{{ $b['uraian'] }}</td>
                                        <td><small class="text-muted">{{ $b['rka']['kode_belanja'] ?? '' }}</small></td>
                                        <td class="text-right"><span class="amount-badge">Rp {{ number_format($b['nilai'], 0, ',', '.') }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4" style="font-size: 14px;">Belum ada belanja TU</td></tr>
                                @endforelse

                                @if(count($belanjaTus) > 0)
                                    <tr class="font-weight-bold" style="background: #f1f5f9; font-size: 13px;">
                                        <td colspan="5" class="text-right">Total Belanja</td>
                                        <td class="text-right">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="font-weight-bold" style="background: #fefce8; font-size: 13px;">
                                        <td colspan="5" class="text-right">Sisa TU (disetor ke Kasda)</td>
                                        <td class="text-right">Rp {{ number_format($sisaTu, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
