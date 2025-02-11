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
                    <h3 class="card-title">Pengelola Keuangan</h3>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" wire:click="resetInputFields" data-toggle="modal"
                            data-target="#pengelolaModal"><i class="fas fa-plus"></i> Tambah Rekanan</button>
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
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Bidang</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asu as $pengelola)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $pengelola->nama }}</td>
                                <td>{{ $pengelola->nip }}</td>
                                <td>{{ $pengelola->jabatan }}</td>
                                <td>{{ $pengelola->bidang }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $pengelola->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $pengelola->id }})"
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
        <div wire:ignore.self class="modal fade" id="pengelolaModal" tabindex="-1"
            aria-labelledby="pengelolaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pengelolaModalLabel">
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
                                <label for="nip">NIP</label>
                                <input type="text" class="form-control" id="nip" wire:model="nip">
                                @error('nip')
                                    <span class="error text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="jabatan">Jabatan</label>
                                <select wire:model="jabatan" class="form-control form-control-sm" id="jabatan">
                                    <option value="">Pilih</option>
                                    <option value="PENGGUNA ANGGARAN">PENGGUNA ANGGARAN</option>
                                    <option value="BENDAHARA PENGELUARAN">BENDAHARA PENGELUARAN</option>
                                    <option value="PPTK">PPTK</option>
                                    <option value="PENGURUS BARANG">PENGURUS BARANG</option>
                                </select>
                                @error('jabatan')
                                    <span class="error text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bidang">Bidang</label>
                                <select wire:model="bidang" class="form-control form-control-sm" id="bidang">
                                    <option value="">Pilih</option>
                                    <option value="SEKRETARIAT">SEKRETARIAT</option>
                                    <option value="INFORMATIKA">INFORMATIKA</option>
                                    <option value="INFORMASI DAN KOMUNIKASI PUBLIK">INFORMASI DAN KOMUNIKASI PUBLIK
                                    </option>
                                </select>
                                @error('bidang')
                                    <span class="error text-danger">{{ $message }}</span>
                                @enderror
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
        var myModal = document.getElementById('pengelolaModal');
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
