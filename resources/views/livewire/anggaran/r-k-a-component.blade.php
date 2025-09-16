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
                <table class="table table-striped table-bordered">
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
                        <div class="mb-3" wire:ignore>
                            <label for="rekening_belanja" class="form-label">Kode & Nama Belanja</label>
                            <select id="rekening_belanja" class="form-control select2" style="width: 100%;">
                                <option value="">-- Pilih Kode Belanja --</option>
                                @foreach ($rekeningBelanjaList as $rekening)
                                    <option value="{{ $rekening->kode }}"
                                        {{ $selectedRekeningBelanja == $rekening->kode ? 'selected' : '' }}>
                                        {{ $rekening->kode }} - {{ $rekening->uraian_belanja }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedRekeningBelanja')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror

                            <!-- Debug info (remove in production) -->
                            @if ($isEditMode)
                                <small class="text-muted">Selected: {{ $selectedRekeningBelanja }}</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="penetapan" class="form-label">Penetapan</label>
                            <input type="number" step="0.01" class="form-control" id="penetapan"
                                wire:model.live="penetapan" placeholder="0">
                            @error('penetapan')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="perubahan" class="form-label">Perubahan</label>
                            <input type="number" step="0.01" class="form-control" id="perubahan"
                                wire:model.live="perubahan" placeholder="0">
                            @error('perubahan')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="selisih" class="form-label">Selisih</label>
                            <input type="number" step="0.01" class="form-control" id="selisih"
                                wire:model="selisih" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="anggaran" class="form-label">Anggaran</label>
                            <input type="number" step="0.01" class="form-control" id="anggaran"
                                wire:model="anggaran" readonly>
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
        document.addEventListener('DOMContentLoaded', function() {
            let select2Initialized = false;
            let isEditMode = false;
            let editValue = '';

            function initializeSelect2() {
                if (!select2Initialized) {
                    $('#rekening_belanja').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#rkaModal'),
                        width: '100%',
                        placeholder: '-- Pilih Kode Belanja --',
                        allowClear: true
                    });

                    // Handle Select2 change event
                    $('#rekening_belanja').on('change', function(e) {
                        const selectedValue = $(this).val();
                        @this.call('updateSelect2Value', selectedValue);
                    });

                    select2Initialized = true;

                    // If edit mode, set the value
                    if (isEditMode && editValue) {
                        setTimeout(function() {
                            $('#rekening_belanja').val(editValue).trigger('change.select2');
                            isEditMode = false;
                            editValue = '';
                        }, 100);
                    }
                }
            }

            function destroySelect2() {
                if (select2Initialized) {
                    $('#rekening_belanja').select2('destroy');
                    select2Initialized = false;
                }
            }

            // Initialize Select2 when modal is shown
            $('#rkaModal').on('shown.bs.modal', function() {
                initializeSelect2();
            });

            // Clean up Select2 when modal is hidden
            $('#rkaModal').on('hidden.bs.modal', function() {
                destroySelect2();
                isEditMode = false;
                editValue = '';
            });

            // Listen for Livewire events
            window.addEventListener('setSelect2Value', function(e) {
                editValue = e.detail;
                isEditMode = true;

                if (select2Initialized) {
                    setTimeout(function() {
                        $('#rekening_belanja').val(editValue).trigger('change.select2');
                        isEditMode = false;
                        editValue = '';
                    }, 100);
                }
            });

            window.addEventListener('modalClosed', function(e) {
                setTimeout(function() {
                    if (select2Initialized) {
                        $('#rekening_belanja').val('').trigger('change.select2');
                    }
                }, 100);
            });

            window.addEventListener('valuesCalculated', function(e) {
                // Values are automatically updated via Livewire binding
                // This event can be used for additional UI updates if needed
            });

            // Listen to Livewire updates for edit mode
            document.addEventListener('livewire:updated', function() {
                // Check if we're in edit mode and modal is visible
                if ($('#rkaModal').hasClass('show') && @this.isEditMode && @this.selectedRekeningBelanja) {
                    setTimeout(function() {
                        if (select2Initialized) {
                            $('#rekening_belanja').val(@this.selectedRekeningBelanja).trigger(
                                'change.select2');
                        }
                    }, 50);
                }
            });
        });
    </script>
@endpush
