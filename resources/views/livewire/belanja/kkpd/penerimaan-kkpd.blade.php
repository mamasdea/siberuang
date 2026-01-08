@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <!-- Modal Penerimaan -->
    <div wire:ignore.self class="modal fade" id="formPenerimaanModal" tabindex="-1" role="dialog"
        aria-labelledby="formPenerimaanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="formPenerimaanModalLabel">
                        {{ $updateMode ? 'Edit Penerimaan' : 'Tambah Penerimaan' }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="form-group d-flex align-items-center">
                                    <label for="penerima_nama" class="mr-2" style="min-width: 150px;">Nama
                                        Penerima</label>
                                    <input wire:model="penerima_nama" type="text" class="form-control"
                                        id="penerima_nama" placeholder="Enter Nama Penerima">
                                    <span class="input-group-append">
                                        @if ($penerima_id)
                                            <button type="button" class="btn btn-danger btn-flat"
                                                wire:click='resetPenerima'><i class="fas fa-trash"></i></button>
                                        @else
                                            <button type="button" class="btn btn-primary btn-flat"
                                                wire:click='openModalPenerima'><i class="fas fa-search"></i></button>
                                        @endif
                                    </span>
                                </div>
                                @error('penerima_nama')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label for="penerima_bank" class="mr-2" style="min-width: 150px;">Bank</label>
                                    <input wire:model="penerima_bank" type="text" class="form-control"
                                        id="penerima_bank" placeholder="Enter Bank">
                                </div>
                                @error('penerima_bank')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label for="penerima_no_rekening" class="mr-2" style="min-width: 150px;">No
                                        Rekening</label>
                                    <input wire:model="penerima_no_rekening" type="text" class="form-control"
                                        id="penerima_no_rekening" placeholder="Enter No Rekening">
                                </div>
                                @error('penerima_no_rekening')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <div class="form-group d-flex align-items-center">
                                    <label for="nominal" class="mr-2" style="min-width: 150px;">Nominal</label>
                                    <input type="text" inputmode="numeric" wire:model.live="nominal"
                                        class="form-control" id="nominal" placeholder="Enter Nominal"> <span
                                        class="mr-2 ml-2">/</span>
                                    <input type="text" inputmode="numeric" class="form-control" id="nominal"
                                        value="{{ number_format((float) ($nominal ?? 0), 2, ',', '.') }}" readonly>
                                </div>
                                @error('nominal')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeFormPenerimaan"
                        data-dismiss="modal">Batal</button>
                    <button type="button" wire:click.prevent="{{ $updateMode ? 'update' : 'store' }}"
                        class="btn btn-primary">{{ $updateMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal program -->
    <div wire:ignore.self class="modal fade" id="PenerimaanModal" tabindex="-1" role="dialog"
        aria-labelledby="PenerimaanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Penerima</h5>
                    <button type="button" class="btn-close" wire:click="closeModalPenerima" aria-label="Close"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <livewire:select-penerima-table />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModalPenerima">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Penerimaan -->
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Daftar Penerimaan KKPD</h3>
                    <p class="page-subtitle mb-0">Kelola data penerimaan untuk belanja KKPD</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="closeFormPenerimaan"
                        data-toggle="modal" data-target="#formPenerimaanModal">
                        <i class="fas fa-plus mr-2"></i>Tambah Penerimaan
                    </button>
                    <a href="{{ url('belanja-kkpd') }}" class="btn btn-outline-success ml-2">
                        <i class="fas fa-undo-alt mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Info Belanja -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">No Bukti</small>
                                    <span class="code-badge">TBP-{{ $no_bukti }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Uraian</small>
                                    <strong>{{ $uraian }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Nilai Belanja</small>
                                    <span class="amount-badge">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Penerima</th>
                            <th>Bank</th>
                            <th>No Rekening</th>
                            <th width="180" class="text-right">Nominal</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penerimaans as $penerimaan)
                            <tr>
                                <td><span class="code-badge">{{ $penerimaans->firstItem() + $loop->index }}</span></td>
                                <td style="font-weight: 500;">{{ $penerimaan->penerima->nama }}</td>
                                <td>{{ $penerimaan->penerima->bank }}</td>
                                <td>{{ $penerimaan->penerima->no_rekening }}</td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($penerimaan->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm text-white"
                                            wire:click="edit({{ $penerimaan->id }})" data-toggle="modal"
                                            data-target="#formPenerimaanModal" data-toggle="tooltip" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="delete_confirmation({{ $penerimaan->id }})" data-toggle="tooltip"
                                            title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                                    <p class="mb-0 mt-2 text-muted">Belum ada data penerimaan</p>
                                    <small class="text-muted">Klik tombol "Tambah Penerimaan" untuk menambah data</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $penerimaans->links() }}
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endpush
