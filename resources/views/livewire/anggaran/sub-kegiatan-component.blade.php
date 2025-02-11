<div>
    <div class="card">
        <div class="col-md-12">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                data-target="#subKegiatanModal">
                                <i class="fas fa-plus"></i> Tambah Sub Kegiatan
                            </button>

                        </div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th>Program</th>
                        <td>{{ $namaKegiatan->program->nama ?? 'kosong' }}</td>
                    </tr>
                    <tr>
                        <th>Kegiatan</th>
                        <td>{{ $namaKegiatan->nama ?? 'kosong' }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr class="thead-dark text-center">
                            <th>Kode Sub Kegiatan</th>
                            <th>Uraian Sub Kegiatan</th>
                            <th>Pagu Sub Kegiatan</th>
                            <th>PPTK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subKegiatans as $subKegiatan)
                            <tr>
                                <td>{{ $subKegiatan->kode }}</td>
                                <td>{{ $subKegiatan->nama }}</td>
                                <td class="text-right">
                                    {{ number_format($subKegiatan->Rka->sum('anggaran'), 2, ',', '.') }}
                                </td>
                                <td>
                                    @if ($subKegiatan->pptk_id)
                                        <!-- Jika PPTK ada, tampilkan nama PPTK -->
                                        {{ $subKegiatan->pptk->nama }} <button
                                            wire:click="modalPPTK({{ $subKegiatan->id }})"
                                            class="btn btn-outline-secondary btn-sm rounded-circle"
                                            data-toggle="tooltip" data-placement="top" title="Edit PPTK">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-outline-primary btn-sm"
                                            wire:click="modalPPTK({{ $subKegiatan->id }})">
                                            <i class="fas fa-plus"></i> PPTK
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $subKegiatan->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $subKegiatan->id }})"
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button wire:click="next({{ $subKegiatan->id }})" class="btn btn-primary btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Next">
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
    <!-- PPTK Modal -->
    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="pptkModal" tabindex="-1" role="dialog"
        aria-labelledby="pptkModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih PPTK</h5>
                    <button type="button" class="close" wire:click="closeModalPPTK" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="storePPTK">
                    <div class="modal-body">
                        <div class="form-group">
                            <select class="form-control @error('pptk_id') is-invalid @enderror" id="pptk"
                                wire:model="pptk_id">
                                <option value="">-- Pilih PPTK --</option>
                                @foreach ($pptks as $pptk)
                                    <option value="{{ $pptk->id }}">{{ $pptk->nama }}</option>
                                @endforeach
                            </select>
                            @error('pptk_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModalPPTK">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="subKegiatanModal" tabindex="-1"
        aria-labelledby="subKegiatanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subKegiatanModalLabel">
                        {{ $isEditMode ? 'Edit Sub Kegiatan' : 'Tambah Sub Kegiatan' }}
                    </h5>
                    <button wire:click="resetAndCloseModal" type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="form-group">
                            <label for="kode">Kode Sub Kegiatan</label>
                            <input type="text" class="form-control" id="kode" wire:model="kode">
                            @error('kode')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Sub Kegiatan</label>
                            <input type="text" class="form-control" id="nama" wire:model="nama">
                            @error('nama')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit"
                            class="btn btn-primary">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                        <button type="button" wire:click="resetAndCloseModal"
                            class="btn btn-secondary">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('pptkModal');
            var pptkInput = document.getElementById('pptk');

            myModal.addEventListener('shown.bs.modal', function() {
                if (pptkInput) {
                    pptkInput.focus();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('subKegiatanModal');
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
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
