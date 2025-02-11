<div>
    <div class="card">
        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="card-title">Data Rencana Kerja Anggaran Perangkat Daerah</h3>
                <div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal"
                            data-target="#importModalProgram">
                            <i class="fas fa-file-import"></i> Import
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nav tabs -->
        <ul class="nav nav-pills nav-fill p-4">
            <li class="nav-item">
                <a class="nav-link active" href="#dokumen" data-toggle="tab">Program</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#sasaran" data-toggle="tab" id="kegiatan">Kegiatan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#subKegiatan" id="subkegiatantab" data-toggle="tab">Sub Kegiatan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#rka" id="Rkatab" data-toggle="tab">Anggaran</a>
            </li>
        </ul>
        <!-- Tab content -->
        <div class="tab-content mt-2">
            <!-- Program Tab -->
            <div class="tab-pane active" id="dokumen">
                <!-- Konten untuk Dokumen RKA SKPD -->
                <div class="card-body">
                    <div class="card">
                        <div class="col-md-12">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div>
                                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                                data-target="#programModal"> <i class="fas fa-plus"></i> Tambah Program
                                                RKA</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="thead-dark">
                                        <tr class="thead-dark text-center">
                                            <th width="100">Kode Program</th>
                                            <th width="300">Nama Program</th>
                                            <th width="150">Pagu Program</th>
                                            <th width="100">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($programs as $program)
                                            <tr>
                                                <td>{{ $program->kode }}</td>
                                                <td>{{ $program->nama }}</td>
                                                <td class="text-right">{{ number_format($program->total, 2, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="edit({{ $program->id }})"
                                                        class="btn btn-warning btn-sm" data-toggle="tooltip"
                                                        data-placement="top" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="delete_confirmation({{ $program->id }})"
                                                        class="btn btn-danger btn-sm" data-toggle="tooltip"
                                                        data-placement="top" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button wire:click="next({{ $program->id }})"
                                                        class="btn btn-primary btn-sm" data-toggle="tooltip"
                                                        data-placement="top" title="Next">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kegiatan Tab -->
            <div class="tab-pane" id="sasaran">
                <!-- Konten untuk Sasaran -->
                <div class="card-body">
                    <livewire:anggaran.kegiatan-component :program_id="$programId" />
                </div>
            </div>

            <!-- Sub Kegiatan Tab -->
            <div class="tab-pane" id="subKegiatan">
                <!-- Konten untuk Sub Kegiatan -->
                <div class="card-body">
                    <livewire:anggaran.sub-kegiatan-component :kegiatan_id="$kegiatanId" />
                </div>
            </div>

            <!-- RKA Tab -->
            <div class="tab-pane" id="rka">
                <!-- Konten untuk RKA -->
                <div class="card-body">
                    <livewire:anggaran.r-k-a-component :sub_kegiatan_id="$subKegiatanId" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalLabel">
                        {{ $isEditMode ? 'Edit Program' : 'Tambah Program' }}
                    </h5>
                    <button type="button" wire:click="resetAndCloseModal" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="form-group">
                            <label for="kode">Kode Program</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror"
                                id="kode" wire:model="kode">
                            @error('kode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Program</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                id="nama" wire:model="nama">
                            @error('nama')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit"
                            class="btn btn-primary">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                        {{-- <button type="button" wire:click="resetInput" class="btn btn-secondary"
                            data-dismiss="modal">Batal</button> --}}
                        <button type="button" wire:click="resetAndCloseModal"
                            class="btn btn-secondary">Batal</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Import Excel-->
    <div wire:ignore.self class="modal fade" id="importModalProgram" tabindex="-1" role="dialog"
        aria-labelledby="importModalProgramLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalProgramLabel">Import Data Excel</h5>
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
