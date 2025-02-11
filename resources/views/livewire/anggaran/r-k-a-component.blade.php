<div>
    <div class="card">
        <div class="col-md-12">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#rkaModal">
                                <i class="fas fa-plus"></i> Tambah Rincian Belanja
                            </button>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th>Kegiatan</th>
                        <td>{{ $namaProgram ?? 'kosong' }}</td>
                    </tr>
                    <tr>
                        <th>Kegiatan</th>
                        <td>{{ $namaSubKegiatan->kegiatan->nama ?? 'kosong' }}</td>
                    </tr>
                    <tr>
                        <th>Sub Kegiatan</th>
                        <td>{{ $namaSubKegiatan->nama ?? 'kosong' }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr class="thead-dark text-center">
                            <th>Kode Belanja</th>
                            <th>Nama Belanja</th>
                            <th>Anggaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rkas as $rka)
                            <tr>
                                <td>{{ $rka->kode_belanja }}</td>
                                <td>{{ $rka->nama_belanja }}</td>
                                <td class="text-right">{{ number_format($rka->anggaran, 2) }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $rka->id }})" class="btn btn-warning btn-sm"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="delete_confirmation({{ $rka->id }})"
                                        class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
                                        title="Hapus">
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
                    <h5 class="modal-title" id="rkaModalLabel">{{ $isEditMode ? 'Edit RKA' : 'Tambah RKA' }}</h5>
                    <button wire:click="resetAndCloseModal" type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        {{-- <div class="mb-3">
                            <label for="kode_belanja" class="form-label">Kode Belanja</label>
                            <input type="text" class="form-control" id="kode_belanja" wire:model="kode_belanja">
                            @error('kode_belanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="nama_belanja" class="form-label">Nama Belanja</label>
                            <input type="text" class="form-control" id="nama_belanja" wire:model="nama_belanja">
                            @error('nama_belanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div> --}}
                        <div class="mb-3">
                            <label for="rekening_belanja" class="form-label">Kode & Nama Belanja</label>
                            <select id="rekening_belanja" class="form-control select2" wire:model="selectedRekeningBelanja">
                                <option value="">-- Pilih Kode Belanja --</option>
                                @foreach ($rekeningBelanjaList as $rekening)
                                    <option value="{{ $rekening->kode }}|{{ $rekening->uraian_belanja }}">
                                        {{ $rekening->kode }} - {{ $rekening->uraian_belanja }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedRekeningBelanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="anggaran" class="form-label">Anggaran</label>
                            <input type="text" class="form-control" id="anggaran" wire:model="anggaran">
                            @error('anggaran')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="submit"
                                class="btn btn-primary">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>

                            <button type="button" wire:click="resetAndCloseModal"
                                class="btn btn-secondary">Batal</button>
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
            var myModal = document.getElementById('rkaModal');
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
      <script>
        function initSelect2() {
            $('.select2').select2({
                theme: 'bootstrap4', // Sesuaikan dengan tema AdminLTE
                width: '100%'
            });

            $('.select2').on('change', function() {
                let selectedValue = $(this).val();
                Livewire.emit('updateSelectedRekeningBelanja', selectedValue);
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
    </script>


@endpush
