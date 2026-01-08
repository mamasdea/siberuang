<div>
    <!-- Modern Content -->
    <div class="content-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title">Rincian Belanja (Anggaran)</h2>
                <p class="section-subtitle">
                    @if($sub_kegiatan_id)
                        Program: {{ $namaProgram ?? 'Tidak Ditemukan' }} | Kegiatan: {{ $namaKegiatan->nama ?? 'Tidak Ditemukan' }} | Sub Kegiatan: {{ $namaSubKegiatan->nama ?? 'Tidak Ditemukan' }}
                    @else
                        Pilih sub kegiatan dari tab Sub Kegiatan
                    @endif
                </p>
            </div>
            <button class="btn-add-modern" data-toggle="modal" data-target="#rkaModal">
                <i class="fas fa-plus mr-2"></i> Tambah Rincian Belanja
            </button>
        </div>

        <table class="modern-table">
            <thead>
                <tr>
                    <th>Kode Belanja</th>
                    <th>Nama Belanja</th>
                    <th>Penetapan</th>
                    <th>Perubahan</th>
                    <th>Selisih</th>
                    <th>Anggaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rkas as $rka)
                    <tr>
                        <td>
                            <span class="code-badge">{{ $rka->kode_belanja }}</span>
                        </td>
                        <td>{{ $rka->nama_belanja }}</td>
                        <td>
                            <span class="amount-badge">Rp {{ number_format($rka->penetapan, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="amount-badge">Rp {{ number_format($rka->perubahan, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="amount-badge" style="background: {{ $rka->selisih < 0 ? '#fee2e2' : '#f0fdf4' }}; color: {{ $rka->selisih < 0 ? '#991b1b' : '#166534' }}; border-color: {{ $rka->selisih < 0 ? '#fca5a5' : '#bbf7d0' }};">
                                Rp {{ number_format($rka->selisih, 2, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <span class="amount-badge">Rp {{ number_format($rka->anggaran, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <button wire:click="edit({{ $rka->id }})" class="btn-action-edit"
                                data-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete_confirmation({{ $rka->id }})"
                                class="btn-action-delete" data-toggle="tooltip" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">
                                @if($sub_kegiatan_id)
                                    Belum ada data rincian belanja
                                @else
                                    Silakan pilih sub kegiatan dari tab Sub Kegiatan dengan klik tombol <i class="fas fa-arrow-right text-primary"></i> Next
                                @endif
                            </p>
                            @if($sub_kegiatan_id)
                                <small style="color: #94a3b8;">Klik tombol "Tambah Rincian Belanja" untuk menambah data</small>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
