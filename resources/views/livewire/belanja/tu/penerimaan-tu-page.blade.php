@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <!-- Modal Penerimaan -->
    <div wire:ignore.self class="modal fade" id="formPenerimaanTuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $updateMode ? 'Edit Penerimaan' : 'Tambah Penerimaan' }}</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">Nama Penerima</label>
                                    <input wire:model="penerima_nama" type="text" class="form-control" placeholder="Nama Penerima">
                                    <span class="input-group-append">
                                        @if ($penerima_id)
                                            <button type="button" class="btn btn-danger btn-flat" wire:click='resetPenerima'><i class="fas fa-trash"></i></button>
                                        @else
                                            <button type="button" class="btn btn-primary btn-flat" data-toggle="modal" data-target="#modalPenerimaTu"><i class="fas fa-search"></i></button>
                                        @endif
                                    </span>
                                </div>
                                @error('penerima_nama') <span class="text-danger">{{ $message }}</span> @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">Bank</label>
                                    <input wire:model="penerima_bank" type="text" class="form-control" placeholder="Bank">
                                </div>

                                <div class="form-group d-flex align-items-center">
                                    <label class="mr-2" style="min-width: 150px;">No Rekening</label>
                                    <input wire:model="penerima_no_rekening" type="text" class="form-control" placeholder="No Rekening">
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

    <!-- Modal Pilih Penerima -->
    <div wire:ignore.self class="modal fade" id="modalPenerimaTu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Pilih Penerima</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Nama</th><th>Bank</th><th>No Rekening</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($penerimas as $p)
                                <tr>
                                    <td>{{ $p->nama }}</td>
                                    <td>{{ $p->bank }}</td>
                                    <td>{{ $p->no_rekening }}</td>
                                    <td><button wire:click="selectPenerima({{ $p->id }})" class="btn btn-primary btn-sm">Pilih</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Penerimaan - Belanja TU</h3>
                    <p class="page-subtitle mb-0">TBP-{{ $no_bukti }} | {{ $uraian }} | Rp {{ number_format($nilai, 0, ',', '.') }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-add" data-toggle="modal" data-target="#formPenerimaanTuModal">
                        <i class="fas fa-plus mr-2"></i>Tambah Penerimaan
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
                            <th>Penerima</th>
                            <th width="150" class="text-right">Nominal</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penerimaans as $idx => $row)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $row->uraian }}</td>
                                <td class="text-right"><span class="amount-badge">Rp {{ number_format($row->nominal, 0, ',', '.') }}</span></td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $row->id }})" class="btn btn-warning btn-sm"><i class="fas fa-pencil-alt text-white"></i></button>
                                    <button wire:click="delete_confirmation({{ $row->id }})" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada penerimaan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $penerimaans->links() }}</div>
        </div>
    </div>
</div>
