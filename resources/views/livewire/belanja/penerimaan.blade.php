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
    <div class="card">
        <div class="card">
            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="card-title">Daftar Penerimaan</h3>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" wire:click="closeFormPenerimaan"
                            data-toggle="modal" data-target="#formPenerimaanModal"><i class="fas fa-plus"></i> Tambah
                            Penerimaan</button>
                        <a href="{{ url('belanja') }}" class="btn btn-outline-success btn-sm ml-2"><i
                                class="fas fa-undo-alt"></i> Kembali</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>No Bukti</th>
                        <td>{{ $no_bukti }}</td>
                    </tr>
                    <tr>
                        <th>Uraian</th>
                        <td>{{ $uraian }}</td>
                    </tr>
                    <tr>
                        <th>Nilai</th>
                        <td><strong>Rp {{ number_format($nilai, 2, ',', '.') }}</strong></td>
                    </tr>
                </table>

            </div>
            <div class="card-body">
                <table class="table mt-4">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>No.</th>
                            <th>Penerima</th>
                            <th>Bank</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penerimaans as $penerimaan)
                            <tr>
                                <td>{{ $penerimaans->firstItem() + $loop->index }}</td>
                                <td>{{ $penerimaan->penerima->nama }}</td>
                                <td>{{ $penerimaan->penerima->bank }}</td>
                                <td>Rp {{ number_format($penerimaan->nominal, 2, ',', '.') }}</td>
                                <td class="text-center">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $penerimaans->links() }}
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
