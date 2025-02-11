<div>
    <div>
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Penerima / Rekanan</h3>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" wire:click="resetInputFields" data-toggle="modal"
                            data-target="#penerimaModal"><i class="fas fa-plus"></i> Tambah Rekanan</button>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div>
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
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
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama</th>
                            <th>No Rekening</th>
                            <th>Bank</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asu as $penerima)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $penerima->nama }}</td>
                                <td>{{ $penerima->no_rekening }}</td>
                                <td>{{ $penerima->bank }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $penerima->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $penerima->id }})"
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="container mt-2 mb-2">
                    {{ $asu->links('livewire::bootstrap') }}
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="penerimaModal" tabindex="-1" aria-labelledby="penerimaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="penerimaModalLabel">
                            {{ $isEdit ? 'Edit Penerima' : 'Add New Penerima' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" class="form-control" id="nama" wire:model="nama">
                                @error('nama')
                                    <span class="error text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="no_rekening">No Rekening</label>
                                <input type="text" class="form-control" id="no_rekening" wire:model="no_rekening">
                                @error('no_rekening')
                                    <span class="error text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bank">Bank</label>
                                <input type="text" class="form-control" id="bank" wire:model="bank">
                                {{-- @error('bank') <span class="error text-danger">{{ the message }}</span> @enderror --}}
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary"
                            wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}">{{ $isEdit ? 'Update' : 'Save' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = document.getElementById('penerimaModal');
        var rekeningInput = document.getElementById('rekening');
        var namaInput = document.getElementById('nama');
        var namabankInput = document.getElementById('bank');

        myModal.addEventListener('shown.bs.modal', function() {
            if (rekeningInput) {
                rekeningInput.focus();
            } else if (namaInput) {
                namaInput.focus();
            } else if (bankInput) {
                bankInput.focus();
            }
        });
    });
</script>
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>
