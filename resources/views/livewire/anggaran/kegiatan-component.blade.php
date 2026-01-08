<div>
    <!-- Modern Content -->
    <div class="content-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title">Kegiatan</h2>
                <p class="section-subtitle">Kelola data kegiatan untuk program {{ $namaProgram ?? 'Pilih program dari tab Program' }}</p>
            </div>
            <button class="btn-add-modern" data-toggle="modal" data-target="#kegiatanModal">
                <i class="fas fa-plus mr-2"></i> Tambah Kegiatan
            </button>
        </div>

        <table class="modern-table">
            <thead>
                <tr>
                    <th>Kode Kegiatan</th>
                    <th>Nama Kegiatan</th>
                    <th>Pagu Kegiatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kegiatans as $kegiatan)
                    <tr>
                        <td>
                            <span class="code-badge">{{ $kegiatan->kode }}</span>
                        </td>
                        <td>{{ $kegiatan->nama }}</td>
                        <td>
                            <span class="amount-badge">Rp {{ number_format($kegiatan->total, 2, ',', '.') }}</span>
                        </td>
                        <td>
                            <button wire:click="edit({{ $kegiatan->id }})" class="btn-action-edit"
                                data-toggle="tooltip" data-placement="top" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete_confirmation({{ $kegiatan->id }})"
                                class="btn-action-delete" data-toggle="tooltip" data-placement="top"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button wire:click="next({{ $kegiatan->id }})" class="btn-action-next"
                                data-toggle="tooltip" data-placement="top" title="Next">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">
                                @if($program_id)
                                    Belum ada data kegiatan
                                @else
                                    Silakan pilih program dari tab Program dengan klik tombol <i class="fas fa-arrow-right text-primary"></i> Next
                                @endif
                            </p>
                            @if($program_id)
                                <small style="color: #94a3b8;">Klik tombol "Tambah Kegiatan" untuk menambah data</small>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
