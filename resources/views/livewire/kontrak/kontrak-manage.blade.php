@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Manajemen Kontrak</h3>
                    <p class="page-subtitle mb-0">Kelola data kontrak dan rinciannya</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" data-toggle="modal" data-target="#modalForm"
                        wire:click="resetInputFields">
                        <i class="fas fa-plus mr-2"></i>Tambah Kontrak
                    </button>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon blue">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="stat-label">Total Kontrak</div>
                        <div class="stat-value">{{ number_format($totalKontrak ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-description">Data Kontrak</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-label">Total Nilai</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNilai ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="stat-description">Akumulasi Nilai</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-secondary font-weight-bold" style="font-size: 14px;">Show:</span>
                    <select wire:model.live="paginate" class="form-control custom-select-modern" style="width: 90px;">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>

                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" wire:model.live.debounce.300ms="search"
                        placeholder="Cari Nomor/Nama/Sub...">
                    @if ($search)
                        <button type="button" class="clear-search" wire:click="$set('search', '')">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th>Nomor Kontrak</th>
                            <th>Tanggal</th>
                            <th>Nama Perusahaan</th>
                            <th>Sub Kegiatan</th>
                            <th class="text-right">Nilai (Rp)</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="text-center">{{ $loop->index + $rows->firstItem() }}</td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ $row->nomor_kontrak }}</span>
                                    <br>
                                    <small class="text-muted">{{ $row->id_kontrak_lkpp }}</small>
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->tanggal_kontrak)->format('d/m/Y') }}</td>
                                <td>{{ $row->nama_perusahaan }}</td>
                                <td>{{ $row->subKegiatan?->nama }}</td>
                                <td class="text-right has-text-success font-weight-bold" style="color: var(--success-color);">
                                    Rp {{ number_format($row->nilai, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="{{ route('kontrak.realisasi', $row->id) }}"
                                            class="btn btn-sm" 
                                            style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 6px 10px; border-radius: 6px; margin-right: 4px;"
                                            title="Realisasi">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <button class="btn btn-action-edit" wire:click="edit({{ $row->id }})" data-toggle="modal"
                                            data-target="#modalForm" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-action-delete"
                                            wire:click="delete_confirmation({{ $row->id }})" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open"></i>
                                        <p>Data kontrak tidak ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $rows->links('livewire::bootstrap') }}
            </div>
        </div>
    </div>

    <!-- Modal Form (Create/Update) -->
    <div wire:ignore.self class="modal fade" id="modalForm" tabindex="-1" role="dialog"
        aria-labelledby="modalFormLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-card border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white">
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark" style="font-size: 1.25rem;">
                            {{ $isEdit ? 'Edit Kontrak' : 'Tambah Kontrak Baru' }}
                        </h5>
                        <small class="text-muted">Lengkapi informasi kontrak dan rinciannya</small>
                    </div>
                    <button wire:click="resetInputFields" type="button" class="close" data-dismiss="modal" style="opacity: 0.5;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        <!-- Card untuk Informasi Kontrak -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1.3rem;">
                                    <i class="fas fa-file-contract text-primary mr-2"></i>Informasi Kontrak
                                </h5>
                            </div>
                            <div class="card-body bg-white">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- KIRI --}}
                                        <div class="form-group mb-4">
                                            <label for="nomor_kontrak" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nomor Kontrak <span class="text-danger">*</span>
                                            </label>
                                            <input id="nomor_kontrak" type="text"
                                                class="form-control bg-light border-0 @error('nomor_kontrak') is-invalid @enderror"
                                                wire:model="nomor_kontrak"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nomor_kontrak')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="tanggal_kontrak" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Tanggal Kontrak <span class="text-danger">*</span>
                                            </label>
                                            <input id="tanggal_kontrak" type="date"
                                                class="form-control bg-light border-0 @error('tanggal_kontrak') is-invalid @enderror"
                                                wire:model="tanggal_kontrak"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('tanggal_kontrak')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="jangka_waktu" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Jangka Waktu
                                            </label>
                                            <input id="jangka_waktu" type="text"
                                                class="form-control bg-light border-0 @error('jangka_waktu') is-invalid @enderror"
                                                wire:model="jangka_waktu" placeholder="mis. 4 (empat) hari kalender"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('jangka_waktu')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="sub_kegiatan_id" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Sub Kegiatan <span class="text-danger">*</span>
                                            </label>
                                            <select id="sub_kegiatan_id" class="form-control bg-light border-0"
                                                wire:model="sub_kegiatan_id"
                                                style="height: 48px; border-radius: 8px;">
                                                <option value="">-- pilih sub kegiatan --</option>
                                                @foreach ($listSubKegiatan as $sk)
                                                    <option value="{{ $sk->id }}">{{ $sk->kode }} - {{ $sk->nama }}</option>
                                                @endforeach
                                            </select>
                                            @error('sub_kegiatan_id')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="keperluan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Keperluan
                                            </label>
                                            <textarea id="keperluan" class="form-control bg-light border-0 @error('keperluan') is-invalid @enderror"
                                                wire:model="keperluan" rows="3"
                                                style="border-radius: 8px; resize: none; padding: 12px;"></textarea>
                                            @error('keperluan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="id_kontrak_lkpp" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                ID Kontrak LKPP
                                            </label>
                                            <input id="id_kontrak_lkpp" type="text"
                                                class="form-control bg-light border-0 @error('id_kontrak_lkpp') is-invalid @enderror"
                                                wire:model="id_kontrak_lkpp"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('id_kontrak_lkpp')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        {{-- KANAN --}}
                                        <div class="form-group mb-4">
                                            <label for="nama_perusahaan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nama Perusahaan <span class="text-danger">*</span>
                                            </label>
                                            <input id="nama_perusahaan" type="text"
                                                class="form-control bg-light border-0 @error('nama_perusahaan') is-invalid @enderror"
                                                wire:model="nama_perusahaan"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nama_perusahaan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="bentuk_perusahaan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Bentuk Perusahaan
                                            </label>
                                            <input id="bentuk_perusahaan" type="text"
                                                class="form-control bg-light border-0 @error('bentuk_perusahaan') is-invalid @enderror"
                                                wire:model="bentuk_perusahaan" placeholder="CV / PT / ..."
                                                style="height: 48px; border-radius: 8px;">
                                            @error('bentuk_perusahaan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="alamat_perusahaan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Alamat Perusahaan
                                            </label>
                                            <textarea id="alamat_perusahaan" class="form-control bg-light border-0 @error('alamat_perusahaan') is-invalid @enderror"
                                                wire:model="alamat_perusahaan" rows="2"
                                                style="border-radius: 8px; resize: none; padding: 12px;"></textarea>
                                            @error('alamat_perusahaan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="nama_pimpinan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nama Pimpinan
                                            </label>
                                            <input id="nama_pimpinan" type="text"
                                                class="form-control bg-light border-0 @error('nama_pimpinan') is-invalid @enderror"
                                                wire:model="nama_pimpinan"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nama_pimpinan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="npwp_perusahaan" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                NPWP Perusahaan
                                            </label>
                                            <input id="npwp_perusahaan" type="text"
                                                class="form-control bg-light border-0 @error('npwp_perusahaan') is-invalid @enderror"
                                                wire:model="npwp_perusahaan"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('npwp_perusahaan')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Nilai otomatis --}}
                                        <div class="form-group mb-4">
                                            <label for="nilai" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nilai Kontrak (Rp) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-0 font-weight-bold text-dark pl-3">Rp</span>
                                                </div>
                                                <input id="nilai" type="number" min="0" step="0.01"
                                                    class="form-control border-0 @error('nilai') is-invalid @enderror"
                                                    wire:model="nilai" readonly
                                                    style="height: 50px; font-size: 1.1rem; font-weight: 600; background: #f0fdf4;">
                                            </div>
                                            <small class="text-muted">Otomatis dari total rincian di bawah</small>
                                            @error('nilai')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="nama_bank" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nama Bank
                                            </label>
                                            <input id="nama_bank" type="text"
                                                class="form-control bg-light border-0 @error('nama_bank') is-invalid @enderror"
                                                wire:model="nama_bank"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nama_bank')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="nama_pemilik_rekening" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nama Pemilik Rekening
                                            </label>
                                            <input id="nama_pemilik_rekening" type="text"
                                                class="form-control bg-light border-0 @error('nama_pemilik_rekening') is-invalid @enderror"
                                                wire:model="nama_pemilik_rekening"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nama_pemilik_rekening')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="nomor_rekening" class="font-weight-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.5px;">
                                                Nomor Rekening
                                            </label>
                                            <input id="nomor_rekening" type="text"
                                                class="form-control bg-light border-0 @error('nomor_rekening') is-invalid @enderror"
                                                wire:model="nomor_rekening"
                                                style="height: 48px; border-radius: 8px;">
                                            @error('nomor_rekening')
                                                <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ====== RINCIAN KONTRAK (DINAMIS) ====== --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1.3rem;">
                                    <i class="fas fa-list-ul text-success mr-2"></i>Rincian Kontrak
                                </h5>
                            </div>
                            <div class="card-body bg-white">
                                <!-- Input Form untuk Tambah Item -->
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-4 mb-2">
                                        <label class="font-weight-bold small text-secondary mb-1">Nama Barang/Jasa</label>
                                        <input type="text" class="form-control bg-light border-0"
                                            placeholder="Nama barang/jasa" wire:model="item_nama"
                                            style="height: 40px; border-radius: 6px;">
                                        @error('item_nama')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="font-weight-bold small text-secondary mb-1">Qty</label>
                                        <input type="number" min="0" step="0.01"
                                            class="form-control bg-light border-0" placeholder="0" wire:model="item_qty"
                                            style="height: 40px; border-radius: 6px;">
                                        @error('item_qty')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="font-weight-bold small text-secondary mb-1">Satuan</label>
                                        <input type="text" class="form-control bg-light border-0" placeholder="Unit"
                                            wire:model="item_satuan"
                                            style="height: 40px; border-radius: 6px;">
                                        @error('item_satuan')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="font-weight-bold small text-secondary mb-1">Harga (Rp)</label>
                                        <input type="number" min="0" step="0.01"
                                            class="form-control bg-light border-0" placeholder="0"
                                            wire:model="item_harga"
                                            style="height: 40px; border-radius: 6px;">
                                        @error('item_harga')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-success btn-block shadow-sm" wire:click="addItem"
                                            style="height: 40px; border-radius: 6px; font-weight: 600;">
                                            <i class="fas fa-plus mr-1"></i> Tambah
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabel Rincian -->
                                <div class="table-responsive" style="border-radius: 8px; overflow: hidden;">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <tr class="text-center">
                                                <th style="width:60px;">No</th>
                                                <th>Nama Barang/Jasa</th>
                                                <th style="width:100px;">Qty</th>
                                                <th style="width:100px;">Satuan</th>
                                                <th style="width:150px;">Harga (Rp)</th>
                                                <th style="width:180px;">Total (Rp)</th>
                                                <th style="width:70px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            @forelse($items as $i => $it)
                                                <tr>
                                                    <td class="text-center font-weight-bold">{{ $i + 1 }}</td>
                                                    <td>{{ $it['nama_barang'] }}</td>
                                                    <td class="text-right">{{ number_format($it['kuantitas'], 2, ',', '.') }}</td>
                                                    <td class="text-center">{{ $it['satuan'] }}</td>
                                                    <td class="text-right">Rp {{ number_format($it['harga'], 0, ',', '.') }}</td>
                                                    <td class="text-right font-weight-bold text-success">
                                                        Rp {{ number_format($it['total_harga'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            wire:click="removeItem({{ $i }})"
                                                            style="border-radius: 6px;" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                                        <p class="text-muted mb-0 mt-2">Belum ada rincian kontrak</p>
                                                        <small class="text-muted">Tambahkan item di formulir di atas</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($items) > 0)
                                            <tfoot style="background: #f8f9fa;">
                                                <tr class="font-weight-bold">
                                                    <td colspan="5" class="text-right text-dark" style="font-size: 1.1rem;">Grand Total</td>
                                                    <td class="text-right text-success" style="font-size: 1.2rem;">
                                                        Rp {{ number_format($this->totalRincian, 0, ',', '.') }}
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="mt-4 d-flex justify-content-end bg-white p-3" style="border-radius: 8px;">
                            <button type="button" class="btn btn-light text-secondary font-weight-600 mr-2 py-2 px-4"
                                wire:click="resetInputFields" data-dismiss="modal"
                                style="border-radius: 8px;">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm py-2 px-4"
                                style="border-radius: 8px; background: var(--primary-color); border: none;">
                                <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kontrak' }}
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
            $('#modalForm').on('shown.bs.modal', function() {
                const firstInput = this.querySelector('input,textarea,select');
                firstInput && firstInput.focus();
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        window.addEventListener('close-modal', event => {
            $('#modalForm').modal('hide');
        });
    </script>
@endpush
