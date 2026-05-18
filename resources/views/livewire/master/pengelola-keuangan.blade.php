@push('css')
    <x-styles.modern-ui />
    <style>
        .badge-definitif { background-color: #28a745; color: #fff; }
        .badge-plt       { background-color: #fd7e14; color: #fff; }
        .badge-lainnya   { background-color: #6c757d; color: #fff; }
        .badge-status { font-size: 0.72rem; padding: 3px 8px; border-radius: 10px; font-weight: 600; }
    </style>
@endpush

<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                     <h3 class="page-title">Pengelola Keuangan</h3>
                     <p class="page-subtitle mb-0">Manajemen data pengelola keuangan per tahun anggaran</p>
                </div>
                <button class="btn btn-modern-add" wire:click="resetInputFields" data-toggle="modal"
                    data-target="#pengelolaModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Pengelola
                </button>
            </div>
        </div>

        <div class="content-card">
              <div class="d-flex justify-content-between align-items-center mb-4">
                 <div class="d-flex align-items-center">
                     <label class="text-secondary mr-2 mb-0 small font-weight-bold">Show:</label>
                     <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 80px;">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                     </select>
                 </div>
                 <div class="search-box">
                      <i class="fas fa-search search-icon"></i>
                      <input type="text" class="form-control search-input" placeholder="Cari nama / NIP / jabatan..." wire:model.live="search">
                 </div>
             </div>

             <div class="table-responsive">
                 <table class="table modern-table">
                     <thead>
                         <tr>
                             <th width="4%">No</th>
                             <th>Nama</th>
                             <th>NIP</th>
                             <th>Jabatan</th>
                             <th>Bidang</th>
                             <th class="text-center">TA</th>
                             <th class="text-center">Masa Aktif</th>
                             <th class="text-center">Status</th>
                             <th width="12%" class="text-center">Aksi</th>
                         </tr>
                     </thead>
                     <tbody>
                        @foreach ($asu as $pengelola)
                             <tr>
                                 <td>{{ $loop->index + 1 }}</td>
                                 <td>{{ $pengelola->nama }}</td>
                                 <td><span class="code-badge">{{ $pengelola->nip }}</span></td>
                                 <td>{{ $pengelola->jabatan }}</td>
                                 <td>{{ $pengelola->bidang }}</td>
                                 <td class="text-center">{{ $pengelola->tahun_anggaran ?? '-' }}</td>
                                 <td class="text-center small">
                                     @if($pengelola->tanggal_mulai && $pengelola->tanggal_selesai)
                                         {{ $pengelola->tanggal_mulai->format('d/m/Y') }}
                                         &ndash;
                                         {{ $pengelola->tanggal_selesai->format('d/m/Y') }}
                                     @else
                                         <span class="text-muted">-</span>
                                     @endif
                                 </td>
                                 <td class="text-center">
                                     @php
                                         $ket = strtoupper($pengelola->keterangan ?? '');
                                         $badgeClass = match(true) {
                                             str_contains($ket, 'PLT') || str_contains($ket, 'PELAKSANA') => 'badge-plt',
                                             $ket === '' || str_contains($ket, 'DEFINITIF') => 'badge-definitif',
                                             default => 'badge-lainnya',
                                         };
                                     @endphp
                                     <span class="badge-status {{ $badgeClass }}">
                                         {{ $pengelola->keterangan ?: 'Definitif' }}
                                     </span>
                                 </td>
                                 <td class="text-center">
                                     <button wire:click="edit({{ $pengelola->id }})" class="btn btn-action-edit"
                                         data-toggle="modal" data-target="#pengelolaModal" title="Edit">
                                         <i class="fas fa-edit"></i>
                                     </button>
                                     <button wire:click="delete_confirmation({{ $pengelola->id }})"
                                         class="btn btn-action-delete" title="Hapus">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                 </td>
                             </tr>
                        @endforeach
                     </tbody>
                 </table>
             </div>

             <div class="mt-4">
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
                    <h5 class="modal-title font-weight-bold" id="pengelolaModalLabel">
                        {{ $isEdit ? 'Edit Pengelola' : 'Tambah Pengelola Baru' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        wire:click="resetInputFields">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">Nama</label>
                            <input type="text" class="form-control" wire:model="nama" placeholder="Nama lengkap">
                            @error('nama') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">NIP</label>
                            <input type="text" class="form-control" wire:model="nip" placeholder="NIP">
                            @error('nip') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">Jabatan</label>
                            <select wire:model="jabatan" class="form-control custom-select-modern">
                                <option value="">Pilih</option>
                                <option value="PENGGUNA ANGGARAN">PENGGUNA ANGGARAN</option>
                                <option value="PPK-SKPD">PPK-SKPD</option>
                                <option value="BENDAHARA PENGELUARAN">BENDAHARA PENGELUARAN</option>
                                <option value="PPTK">PPTK</option>
                                <option value="PENGURUS BARANG">PENGURUS BARANG</option>
                            </select>
                            @error('jabatan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">Bidang</label>
                            <select wire:model="bidang" class="form-control custom-select-modern">
                                <option value="">Pilih</option>
                                <option value="SEKRETARIAT">SEKRETARIAT</option>
                                <option value="INFORMATIKA">INFORMATIKA</option>
                                <option value="INFORMASI DAN KOMUNIKASI PUBLIK">INFORMASI DAN KOMUNIKASI PUBLIK</option>
                            </select>
                            @error('bidang') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">Status / Keterangan</label>
                            <select wire:model="keterangan" class="form-control custom-select-modern">
                                <option value="">Definitif</option>
                                <option value="PLT">PLT (Pelaksana Tugas)</option>
                                <option value="Pejabat Sementara">Pejabat Sementara</option>
                            </select>
                            @if(strtoupper($jabatan ?? '') === 'PPTK')
                                <small class="text-info"><i class="fas fa-info-circle"></i>
                                    PPTK boleh lebih dari satu aktif bersamaan (per kegiatan).
                                </small>
                            @endif
                            @error('keterangan') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-secondary">Tahun Anggaran</label>
                            <input type="number" class="form-control" wire:model="tahun_anggaran"
                                min="2020" max="2099" placeholder="Contoh: 2026">
                            @error('tahun_anggaran') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-secondary">Tanggal Mulai</label>
                                    <input type="date" class="form-control" wire:model="tanggal_mulai">
                                    @error('tanggal_mulai') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-secondary">Tanggal Selesai</label>
                                    <input type="date" class="form-control" wire:model="tanggal_selesai">
                                    @error('tanggal_selesai') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @if(strtoupper($jabatan ?? '') !== 'PPTK' && $jabatan)
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                Hanya boleh <strong>1 orang aktif</strong> untuk jabatan <strong>{{ $jabatan }}</strong>
                                pada periode yang sama. Sistem akan menolak jika tanggal tumpang tindih.
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        wire:click="resetInputFields">Tutup</button>
                    <button type="button" class="btn btn-modern-add"
                        wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        {{ $isEdit ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = document.getElementById('pengelolaModal');
            if (myModal) {
                myModal.addEventListener('shown.bs.modal', function() {
                    var namaInput = document.getElementById('nama');
                    if (namaInput) namaInput.focus();
                });
            }
        });
    </script>
@endpush
