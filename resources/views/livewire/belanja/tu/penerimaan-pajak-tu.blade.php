@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Penerimaan & Pajak - Belanja TU</h3>
                    <p class="page-subtitle mb-0">TBP-{{ $belanjaTu['no_bukti'] ?? '' }} | {{ $belanjaTu['uraian'] ?? '' }} | Rp {{ number_format($belanjaTu['nilai'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <a href="{{ url('belanja-tu/' . ($belanjaTu['spp_spm_tu_id'] ?? '')) }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="content-card">
            <!-- PENERIMAAN -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0"><i class="fas fa-coins mr-2 text-primary"></i>Penerimaan</h6>
                    <button wire:click="openPenerimaanForm" class="btn btn-sm btn-primary" style="border-radius: 6px;"><i class="fas fa-plus mr-1"></i> Tambah</button>
                </div>
                <div class="card-body p-0">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Uraian</th>
                                <th width="150" class="text-right">Nominal</th>
                                <th width="80" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penerimaans as $idx => $p)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $p->uraian }}</td>
                                    <td class="text-right">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button wire:click="deletePenerimaan({{ $p->id }})" class="btn btn-danger btn-sm" style="border-radius: 6px;"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada penerimaan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAJAK -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0"><i class="fas fa-receipt mr-2 text-warning"></i>Pajak</h6>
                    <button wire:click="openPajakForm" class="btn btn-sm btn-warning text-white" style="border-radius: 6px;"><i class="fas fa-plus mr-1"></i> Tambah</button>
                </div>
                <div class="card-body p-0">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Jenis Pajak</th>
                                <th>No Billing</th>
                                <th>NTPN</th>
                                <th>NTB</th>
                                <th width="130" class="text-right">Nominal</th>
                                <th width="80" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pajaks as $idx => $p)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $p->jenis_pajak }}</td>
                                    <td>{{ $p->no_billing }}</td>
                                    <td>{{ $p->ntpn ?? '-' }}</td>
                                    <td>{{ $p->ntb ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button wire:click="deletePajak({{ $p->id }})" class="btn btn-danger btn-sm" style="border-radius: 6px;"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada pajak</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Penerimaan -->
    <div wire:ignore.self class="modal fade" id="penerimaanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0"><h5 class="modal-title font-weight-bold">Tambah Penerimaan</h5>
                    <button type="button" class="close" wire:click="closePenerimaanForm"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Uraian</label>
                        <input wire:model="pen_uraian" type="text" class="form-control" placeholder="Uraian penerimaan">
                        @error('pen_uraian') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nominal</label>
                        <input wire:model="pen_nominal" type="number" step="0.01" class="form-control" placeholder="0">
                        @error('pen_nominal') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button wire:click="closePenerimaanForm" class="btn btn-light">Batal</button>
                    <button wire:click="storePenerimaan" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pajak -->
    <div wire:ignore.self class="modal fade" id="pajakModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modern-card border-0" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0"><h5 class="modal-title font-weight-bold">Tambah Pajak</h5>
                    <button type="button" class="close" wire:click="closePajakForm"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Jenis Pajak</label>
                        <select wire:model="paj_jenis" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="PPN">PPN</option>
                            <option value="PPh 21">PPh 21</option>
                            <option value="PPh 22">PPh 22</option>
                            <option value="PPh 23">PPh 23</option>
                        </select>
                        @error('paj_jenis') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">No Billing</label>
                        <input wire:model="paj_no_billing" type="text" class="form-control">
                        @error('paj_no_billing') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">NTPN</label>
                        <input wire:model="paj_ntpn" type="text" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">NTB</label>
                        <input wire:model="paj_ntb" type="text" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold small">Nominal</label>
                        <input wire:model="paj_nominal" type="number" step="0.01" class="form-control" placeholder="0">
                        @error('paj_nominal') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button wire:click="closePajakForm" class="btn btn-light">Batal</button>
                    <button wire:click="storePajak" class="btn btn-warning text-white"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
