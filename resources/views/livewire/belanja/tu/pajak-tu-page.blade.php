@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <!-- Modal Pajak -->
    <div wire:ignore.self class="modal fade" id="formPajakTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $updateMode ? 'Edit Pajak' : 'Tambah Pajak' }}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">Jenis Pajak</label>
                                    <select wire:model="jenis_pajak" class="form-control">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="PPN">PPN</option>
                                        <option value="PPh 21">PPh 21</option>
                                        <option value="PPh 22">PPh 22</option>
                                        <option value="PPh 23">PPh 23</option>
                                        <option value="IWP 1%">IWP 1%</option>
                                        <option value="IWP 8%">IWP 8%</option>
                                        <option value="BPJS">BPJS</option>
                                    </select>
                                </div>
                                @error('jenis_pajak') <span class="text-danger">{{ $message }}</span> @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">ID Billing</label>
                                    <input wire:model="no_billing" type="text" class="form-control" placeholder="ID Billing">
                                </div>
                                @error('no_billing') <span class="text-danger">{{ $message }}</span> @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">NTPN</label>
                                    <input wire:model="ntpn" type="text" class="form-control" placeholder="NTPN (opsional)">
                                </div>

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">NTB</label>
                                    <input wire:model="ntb" type="text" class="form-control" placeholder="NTB (opsional)">
                                </div>

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">Nominal</label>
                                    <input wire:model="nominal" type="number" step="0.01" class="form-control" placeholder="0">
                                </div>
                                @error('nominal') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" wire:click="{{ $updateMode ? 'update' : 'store' }}" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> {{ $updateMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Pajak - Belanja TU</h3>
                    <p class="page-subtitle mb-0">TBP-{{ $no_bukti }} | {{ $uraian }} | Rp {{ number_format($nilai, 0, ',', '.') }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-add" data-toggle="modal" data-target="#formPajakTuModal">
                        <i class="fas fa-plus mr-2"></i>Tambah Pajak
                    </button>
                    <a href="{{ url('belanja-tu/' . $sppSpmTuId) }}" class="btn btn-outline-success ml-2" style="border-radius: 8px;">
                        <i class="fas fa-undo-alt mr-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Jenis Pajak</th>
                            <th>ID Billing</th>
                            <th>NTPN</th>
                            <th>NTB</th>
                            <th width="130" class="text-right">Nominal</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pajaks as $idx => $row)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $row->jenis_pajak }}</td>
                                <td>{{ $row->no_billing }}</td>
                                <td>{{ $row->ntpn ?? '-' }}</td>
                                <td>{{ $row->ntb ?? '-' }}</td>
                                <td class="text-right"><span class="amount-badge">Rp {{ number_format($row->nominal, 0, ',', '.') }}</span></td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $row->id }})" class="btn btn-warning btn-sm"><i class="fas fa-pencil-alt text-white"></i></button>
                                    <button wire:click="delete_confirmation({{ $row->id }})" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pajak</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $pajaks->links() }}</div>
        </div>
    </div>
</div>
