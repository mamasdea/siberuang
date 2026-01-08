<div>
    <!-- Modern Content -->
    <div class="content-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="section-title">Sub Kegiatan</h2>
                <p class="section-subtitle">
                    @if($kegiatan_id)
                        Program: {{ $namaKegiatan->program->nama ?? 'kosong' }} | Kegiatan: {{ $namaKegiatan->nama ?? 'kosong' }}
                    @else
                        Pilih kegiatan dari tab Kegiatan
                    @endif
                </p>
            </div>
            <button class="btn-add-modern" data-toggle="modal" data-target="#subKegiatanModal">
                <i class="fas fa-plus mr-2"></i> Tambah Sub Kegiatan
            </button>
        </div>

        <table class="modern-table">
            <thead>
                <tr>
                    <th>Kode Sub Kegiatan</th>
                    <th>Uraian Sub Kegiatan</th>
                    <th>Pagu Sub Kegiatan</th>
                    <th>PPTK</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subKegiatans as $subKegiatan)
                    <tr>
                        <td>
                            <span class="code-badge">{{ $subKegiatan->kode }}</span>
                        </td>
                        <td>{{ $subKegiatan->nama }}</td>
                        <td>
                            <span class="amount-badge">Rp {{ number_format($subKegiatan->Rka->sum('anggaran'), 2, ',', '.') }}</span>
                        </td>
                        <td>
                            @if ($subKegiatan->pptk_id)
                                {{ $subKegiatan->pptk->nama }}
                                <button wire:click="modalPPTK({{ $subKegiatan->id }})"
                                    class="btn-action-edit ml-2"
                                    data-toggle="tooltip" data-placement="top" title="Edit PPTK">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            @else
                                <button class="btn-add-modern" style="padding: 6px 12px; font-size: 12px;"
                                    wire:click="modalPPTK({{ $subKegiatan->id }})">
                                    <i class="fas fa-plus mr-1"></i> PPTK
                                </button>
                            @endif
                        </td>
                        <td>
                            <button wire:click="edit({{ $subKegiatan->id }})" class="btn-action-edit"
                                data-toggle="tooltip" data-placement="top" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete_confirmation({{ $subKegiatan->id }})"
                                class="btn-action-delete" data-toggle="tooltip" data-placement="top"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button wire:click="next({{ $subKegiatan->id }})" class="btn-action-next"
                                data-toggle="tooltip" data-placement="top" title="Next">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">
                                @if($kegiatan_id)
                                    Belum ada data sub kegiatan
                                @else
                                    Silakan pilih kegiatan dari tab Kegiatan dengan klik tombol <i class="fas fa-arrow-right text-primary"></i> Next
                                @endif
                            </p>
                            @if($kegiatan_id)
                                <small style="color: #94a3b8;">Klik tombol "Tambah Sub Kegiatan" untuk menambah data</small>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
