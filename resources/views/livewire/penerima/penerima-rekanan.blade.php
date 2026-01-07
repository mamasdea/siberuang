@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div>
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="modern-card fade-in-up">
            <div class="card-header-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="page-title">Penerima / Rekanan</h3>
                        <p class="page-subtitle mb-0">Manajemen data penerima dan rekanan</p>
                    </div>
                    <button class="btn btn-modern-add" wire:click="resetInputFields" data-toggle="modal"
                        data-target="#penerimaModal">
                        <i class="fas fa-plus mr-1"></i> Tambah Rekanan
                    </button>
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
                                <th width="5%">No</th>
                                <th>Nama</th>
                                <th>No Rekening</th>
                                <th>Bank</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asu as $penerima)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $penerima->nama }}</td>
                                    <td><span class="code-badge">{{ $penerima->no_rekening }}</span></td>
                                    <td>{{ $penerima->bank }}</td>
                                    <td class="text-center">
                                        <button wire:click="edit({{ $penerima->id }})" class="btn btn-action-edit"
                                            data-toggle="modal" data-target="#penerimaModal" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="delete_confirmation({{ $penerima->id }})"
                                            class="btn btn-action-delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $asu->links('livewire::bootstrap') }}
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="penerimaModal" tabindex="-1" aria-labelledby="penerimaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="penerimaModalLabel">
                            {{ $isEdit ? 'Edit Penerima' : 'Tambah Penerima Baru' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="nama" class="font-weight-bold small text-secondary">Nama</label>
                                <input type="text" class="form-control" id="nama" wire:model="nama">
                                @error('nama')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="no_rekening" class="font-weight-bold small text-secondary">No Rekening</label>
                                <input type="text" class="form-control" id="no_rekening" wire:model="no_rekening">
                                @error('no_rekening')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bank" class="font-weight-bold small text-secondary">Bank</label>
                                <input type="text" class="form-control" id="bank" wire:model="bank">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-modern-add"
                            wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}">{{ $isEdit ? 'Update' : 'Simpan' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('penerimaModal');
            var rekeningInput = document.getElementById('no_rekening'); // Fixed ID
            var namaInput = document.getElementById('nama');
            var bankInput = document.getElementById('bank');

            if(myModal) {
                 myModal.addEventListener('shown.bs.modal', function() {
                    if (namaInput) {
                        namaInput.focus();
                    } else if (rekeningInput) {
                        rekeningInput.focus();
                    } else if (bankInput) {
                        bankInput.focus();
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
