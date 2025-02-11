<div>
    <!-- Nav tabs, jika ada -->
    <div class="card">
        <div class="col-md-12">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                data-target="#kegiatanModal"> <i class="fas fa-plus"></i> Tambah Kegiatan
                                RKA</button>

                        </div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th>Program</th>
                        <td>{{ $namaProgram }}</td>
                    </tr>
                </table>
            </div>

            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr class="thead-dark text-center">
                            <th>Kode Kegiatan</th>
                            <th>Nama Kegiatan</th>
                            <th>Pagu Kegiatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kegiatans as $kegiatan)
                            <tr>
                                <td>{{ $kegiatan->kode }}</td>
                                <td>{{ $kegiatan->nama }}</td>
                                <td class="text-right">{{ number_format($kegiatan->total, 2, ',', '.') }}</td>

                                <td class="text-center">
                                    <button wire:click="edit({{ $kegiatan->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $kegiatan->id }})"
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button wire:click="next({{ $kegiatan->id }})" class="btn btn-primary btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Next">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </td>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="kegiatanModal" tabindex="-1" aria-labelledby="kegiatanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kegiatanModalLabel">
                        {{ $isEditMode ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}</h5> <button type="button"
                        wire:click="resetAndCloseModal" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="form-group">
                            <label for="kode">Kode Kegiatan</label> ({{ $program_id }})
                            <input type="text" class="form-control" id="kode" wire:model="kode">
                            @error('kode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Kegiatan</label>
                            <input type="text" class="form-control" id="nama" wire:model="nama">
                            @error('nama')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                        <button type="button" wire:click="resetAndCloseModal" class="btn btn-secondary">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('kegiatanModal');
            var kodeInput = document.getElementById('kode');
            var namaInput = document.getElementById('nama');

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
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endpush
