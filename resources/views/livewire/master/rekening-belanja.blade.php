@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Rekening Belanja</h3>
                    <p class="page-subtitle mb-0">Manajemen data rekening belanja</p>
                </div>
                <div>
                    <button class="btn btn-action-edit mr-2" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-file-import mr-1"></i> Import
                    </button>
                    <button class="btn btn-modern-add" data-toggle="modal" data-target="#modalForm">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <label class="text-secondary mr-2 mb-0 small font-weight-bold">Show:</label>
                    <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 80px;">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" placeholder="Cari..."
                        wire:model.live="search">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">Kode</th>
                            <th>Uraian Belanja</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rek_belanja as $rekening)
                            <tr>
                                <td>{{ $loop->index + $rek_belanja->firstItem() }}</td>
                                <td><span class="code-badge">{{ $rekening->kode }}</span></td>
                                <td>{{ $rekening->uraian_belanja }}</td>
                                <td class="text-center">
                                    <button class="btn btn-action-edit"
                                        wire:click="edit({{ $rekening->id }})" data-toggle="modal"
                                        data-target="#modalForm" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-action-delete"
                                        wire:click="delete_confirmation({{ $rekening->id }})"
                                        title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $rek_belanja->links('livewire::bootstrap') }}
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="modalForm" tabindex="-1" role="dialog"
        aria-labelledby="modalFormLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalFormLabel">
                        {{ $isEdit ? 'Edit Rekening Belanja' : 'Tambah Rekening Belanja' }}</h5>
                    <button wire:click="resetAndCloseModal" type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'save' }}">
                        <div class="form-group">
                            <label for="kode" class="font-weight-bold small text-secondary">Kode</label>
                            <input type="text" id="kode"
                                class="form-control @error('kode') is-invalid @enderror" wire:model="kode">
                            @error('kode')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="uraian_belanja" class="font-weight-bold small text-secondary">Uraian Belanja</label>
                            <input type="text" id="uraian_belanja"
                                class="form-control @error('uraian_belanja') is-invalid @enderror"
                                wire:model="uraian_belanja">
                            @error('uraian_belanja')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary mr-2" wire:click="resetAndCloseModal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-modern-add">
                                {{ $isEdit ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
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
                    <h5 class="modal-title font-weight-bold" id="importModalLabel">Import Data Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="import">
                        <div class="form-group">
                            <label for="file" class="font-weight-bold small text-secondary">Pilih File Excel</label>
                            <input type="file" wire:model="file" id="file"
                                class="form-control @error('file') is-invalid @enderror">
                            @error('file')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-modern-add" wire:click="import">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('modalForm'); // Fixed ID
            var kodeInput = document.getElementById('kode');
            var namaInput = document.getElementById('uraian_belanja');

            if (myModal) { // Safety check
                myModal.addEventListener('shown.bs.modal', function() {
                    if (kodeInput) {
                        kodeInput.focus();
                    } else if (namaInput) {
                        namaInput.focus();
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('importModal');
            var kodeInput = document.getElementById('file');

            if(myModal) {
                 myModal.addEventListener('shown.bs.modal', function() {
                    if (kodeInput) {
                        kodeInput.focus();
                    }
                });
            }
        });
    </script>
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endpush
