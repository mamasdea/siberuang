<div>
    <div class="card">
        <div class="col-md-12">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#rkaModal">
                        <i class="fas fa-plus"></i> Tambah Rincian Belanja
                    </button>
                </div>

                <table class="table table-bordered">
                    <tr>
                        <th>Program</th>
                        <td>{{ $namaProgram ?? 'Tidak Ditemukan' }}</td>
                    </tr>
                    <tr>
                        <th>Kegiatan</th>
                        <td>{{ $namaKegiatan->nama ?? 'Tidak Ditemukan' }}</td>
                    </tr>
                    <tr>
                        <th>Sub Kegiatan</th>
                        <td>{{ $namaSubKegiatan->nama ?? 'Tidak Ditemukan' }}</td>
                    </tr>
                </table>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered ">
                    <thead>
                        <tr class="thead-dark text-center">
                            <th width="100">Kode Belanja</th>
                            <th width="300">Nama Belanja</th>
                            <th width="150">Penetapan</th>
                            <th width="150">Perubahan</th>
                            <th width="150">Selisih</th>
                            <th width="150">Anggaran</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rkas as $rka)
                            <tr>
                                <td>{{ $rka->kode_belanja }}</td>
                                <td>{{ $rka->nama_belanja }}</td>
                                <td class="text-right">Rp {{ number_format($rka->penetapan, 2, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($rka->perubahan, 2, ',', '.') }}</td>
                                <td class="text-right {{ $rka->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($rka->selisih, 2, ',', '.') }}
                                </td>
                                <td class="text-right">Rp {{ number_format($rka->anggaran, 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $rka->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $rka->id }})"
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="rkaModal" tabindex="-1" aria-labelledby="rkaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rkaModalLabel">
                        {{ $isEditMode ? 'Edit RKA' : 'Tambah RKA' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        wire:click="resetAndCloseModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <label for="rekening_belanja" class="form-label">Kode & Nama Belanja</label>
                            <select id="rekening_belanja" class="form-control select2"
                                wire:model="selectedRekeningBelanja">
                                <option value="">-- Pilih Kode Belanja --</option>
                                @foreach ($rekeningBelanjaList as $rekening)
                                    <option value="{{ $rekening->kode }}">
                                        {{ $rekening->kode }} - {{ $rekening->uraian_belanja }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedRekeningBelanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="penetapan" class="form-label">Penetapan</label>
                            <input type="text" class="form-control" id="penetapan" wire:model="penetapan">
                            @error('penetapan')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="perubahan" class="form-label">Perubahan</label>
                            <input type="text" class="form-control" id="perubahan" wire:model="perubahan">
                            @error('perubahan')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="selisih" class="form-label">Selisih</label>
                            <input type="text" class="form-control" id="selisih" wire:model="selisih" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="anggaran" class="form-label">Anggaran</label>
                            <input type="text" class="form-control" id="anggaran" wire:model="anggaran" readonly>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                {{ $isEditMode ? 'Update' : 'Simpan' }}
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="resetAndCloseModal">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function initSelect2() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            }).on('change', function() {
                Livewire.emit('updateSelectedRekeningBelanja', $(this).val());
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initSelect2();
        });

        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                initSelect2();
            });
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('rkaModal');
            myModal.addEventListener('shown.bs.modal', function() {
                $('#rekening_belanja').select2('open');
            });
        });
    </script>
@endpush
