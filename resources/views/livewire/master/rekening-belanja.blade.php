<div>
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title">Rekening Belanja</h3>
                            <div>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#importModal">
                                    <i class="fas fa-file-import"></i> Import
                                </button>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalForm">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="col-lg-2">
                                <div class="d-flex align-items-center">
                                    <label class="col-form-label mr-2">Show:</label>
                                    <select wire:model.live="paginate" class="form-control form-control-sm">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Search"
                                        wire:model.live="search">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th>No.</th>
                                    <th>Kode</th>
                                    <th>Uraian Belanja</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rek_belanja as $rekening)
                                    <tr>
                                        {{-- <td>{{ $rekening->id }}</td> --}}
                                        <td>{{ $loop->index + $rek_belanja->firstItem() }}</td>
                                        <td>{{ $rekening->kode }}</td>
                                        <td>{{ $rekening->uraian_belanja }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white"
                                                wire:click="edit({{ $rekening->id }})" data-toggle="modal"
                                                data-target="#modalForm" data-toggle="tooltip" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                wire:click="delete_confirmation({{ $rekening->id }})"
                                                data-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="container mt-2 mb-2">
                            {{ $rek_belanja->links('livewire::bootstrap') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="modalForm" tabindex="-1" role="dialog"
        aria-labelledby="modalFormLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormLabel">
                        {{ $isEdit ? 'Edit Rekening Belanja' : 'Tambah Rekening Belanja' }}</h5>
                    <button wire:click="resetAndCloseModal" type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'save' }}">
                        <div class="form-group">
                            <label for="kode">Kode:</label>
                            <input type="text" id="kode"
                                class="form-control @error('kode') is-invalid @enderror" wire:model="kode">
                            @error('kode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="uraian_belanja">Uraian Belanja:</label>
                            <input type="text" id="uraian_belanja"
                                class="form-control @error('uraian_belanja') is-invalid @enderror"
                                wire:model="uraian_belanja">
                            @error('uraian_belanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update' : 'Simpan' }}
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="resetAndCloseModal">
                            Batal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Import Excel-->
    <div wire:ignore.self class="modal fade" id="importModal" tabindex="-1" role="dialog"
        aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="import">
                        <div class="form-group">
                            <label for="file">Pilih File Excel</label>
                            <input type="file" wire:model="file" id="file"
                                class="form-control @error('file') is-invalid @enderror">
                            @error('file')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" wire:click="import">Import</button>
                </div>
            </div>
        </div>
    </div>


</div>
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('programModal');
            var kodeInput = document.getElementById('kode');
            var namaInput = document.getElementById('uraian_belanja');

            myModal.addEventListener('shown.bs.modal', function() {
                if (kodeInput) {
                    kodeInput.focus();
                } else if (namaInput) {
                    namaInput.focus();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('importModal');
            var kodeInput = document.getElementById('file');

            myModal.addEventListener('shown.bs.modal', function() {
                (kodeInput) {
                    kodeInput.focus();
                }
            });
        });
    </script>
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endpush
