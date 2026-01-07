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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormLabel">{{ $isEdit ? 'Edit Kontrak' : 'Tambah Kontrak' }}</h5>
                    <button wire:click="resetInputFields" type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        <div class="row">
                            <div class="col-md-6">
                                {{-- KIRI --}}
                                <div class="form-group">
                                    <label for="nomor_kontrak">Nomor Kontrak *</label>
                                    <input id="nomor_kontrak" type="text"
                                        class="form-control @error('nomor_kontrak') is-invalid @enderror"
                                        wire:model="nomor_kontrak">
                                    @error('nomor_kontrak')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="tanggal_kontrak">Tanggal Kontrak *</label>
                                    <input id="tanggal_kontrak" type="date"
                                        class="form-control @error('tanggal_kontrak') is-invalid @enderror"
                                        wire:model="tanggal_kontrak">
                                    @error('tanggal_kontrak')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="jangka_waktu">Jangka Waktu</label>
                                    <input id="jangka_waktu" type="text"
                                        class="form-control @error('jangka_waktu') is-invalid @enderror"
                                        wire:model="jangka_waktu" placeholder="mis. 4 (empat) hari kalender">
                                    @error('jangka_waktu')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="sub_kegiatan_id">Sub Kegiatan *</label>
                                    <select id="sub_kegiatan_id" class="form-control"
                                        wire:model="sub_kegiatan_id">
                                        <option value="">-- pilih sub kegiatan --</option>
                                        @foreach ($listSubKegiatan as $sk)
                                            <option value="{{ $sk->id }}">{{ $sk->kode }} -
                                                {{ $sk->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('sub_kegiatan_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="keperluan">Keperluan</label>
                                    <textarea id="keperluan" class="form-control @error('keperluan') is-invalid @enderror" wire:model="keperluan"
                                        rows="2"></textarea>
                                    @error('keperluan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="id_kontrak_lkpp">ID Kontrak LKPP</label>
                                    <input id="id_kontrak_lkpp" type="text"
                                        class="form-control @error('id_kontrak_lkpp') is-invalid @enderror"
                                        wire:model="id_kontrak_lkpp">
                                    @error('id_kontrak_lkpp')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- KANAN --}}
                                <div class="form-group">
                                    <label for="nama_perusahaan">Nama Perusahaan *</label>
                                    <input id="nama_perusahaan" type="text"
                                        class="form-control @error('nama_perusahaan') is-invalid @enderror"
                                        wire:model="nama_perusahaan">
                                    @error('nama_perusahaan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="bentuk_perusahaan">Bentuk Perusahaan</label>
                                    <input id="bentuk_perusahaan" type="text"
                                        class="form-control @error('bentuk_perusahaan') is-invalid @enderror"
                                        wire:model="bentuk_perusahaan" placeholder="CV / PT / ...">
                                    @error('bentuk_perusahaan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan">Alamat Perusahaan</label>
                                    <textarea id="alamat_perusahaan" class="form-control @error('alamat_perusahaan') is-invalid @enderror"
                                        wire:model="alamat_perusahaan" rows="2"></textarea>
                                    @error('alamat_perusahaan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nama_pimpinan">Nama Pimpinan</label>
                                    <input id="nama_pimpinan" type="text"
                                        class="form-control @error('nama_pimpinan') is-invalid @enderror"
                                        wire:model="nama_pimpinan">
                                    @error('nama_pimpinan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="npwp_perusahaan">NPWP Perusahaan</label>
                                    <input id="npwp_perusahaan" type="text"
                                        class="form-control @error('npwp_perusahaan') is-invalid @enderror"
                                        wire:model="npwp_perusahaan">
                                    @error('npwp_perusahaan')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Nilai otomatis --}}
                                <div class="form-group">
                                    <label for="nilai">Nilai Kontrak (Rp) *</label>
                                    <input id="nilai" type="number" min="0" step="0.01"
                                        class="form-control @error('nilai') is-invalid @enderror" wire:model="nilai"
                                        readonly>
                                    <small class="text-muted">Otomatis dari total rincian di bawah.</small>
                                    @error('nilai')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nama_bank">Nama Bank</label>
                                    <input id="nama_bank" type="text"
                                        class="form-control @error('nama_bank') is-invalid @enderror"
                                        wire:model="nama_bank">
                                    @error('nama_bank')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nama_pemilik_rekening">Nama Pemilik Rekening</label>
                                    <input id="nama_pemilik_rekening" type="text"
                                        class="form-control @error('nama_pemilik_rekening') is-invalid @enderror"
                                        wire:model="nama_pemilik_rekening">
                                    @error('nama_pemilik_rekening')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="nomor_rekening">Nomor Rekening</label>
                                    <input id="nomor_rekening" type="text"
                                        class="form-control @error('nomor_rekening') is-invalid @enderror"
                                        wire:model="nomor_rekening">
                                    @error('nomor_rekening')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ====== RINCIAN KONTRAK (DINAMIS) ====== --}}
                        <hr>
                        <h6 class="mb-2 font-weight-bold">Rincian Kontrak</h6>

                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Item</label>
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="Nama barang/jasa" wire:model="item_nama">
                                @error('item_nama')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted mb-1">Qty</label>
                                <input type="number" min="0" step="0.01"
                                    class="form-control form-control-sm" placeholder="Qty" wire:model="item_qty">
                                @error('item_qty')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted mb-1">Satuan</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Satuan"
                                    wire:model="item_satuan">
                                @error('item_satuan')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted mb-1">Harga</label>
                                <input type="number" min="0" step="0.01"
                                    class="form-control form-control-sm" placeholder="Harga (Rp)"
                                    wire:model="item_harga">
                                @error('item_harga')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="button" class="btn btn-success btn-sm btn-block" wire:click="addItem">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th style="width:60px;">No</th>
                                        <th>Nama Barang/Jasa</th>
                                        <th style="width:120px;">Qty</th>
                                        <th style="width:120px;">Satuan</th>
                                        <th style="width:160px;">Harga (Rp)</th>
                                        <th style="width:180px;">Total (Rp)</th>
                                        <th style="width:70px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $i => $it)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $it['nama_barang'] }}</td>
                                            <td class="text-right">{{ number_format($it['kuantitas'], 2, ',', '.') }}
                                            </td>
                                            <td class="text-center">{{ $it['satuan'] }}</td>
                                            <td class="text-right">Rp {{ number_format($it['harga'], 2, ',', '.') }}
                                            </td>
                                            <td class="text-right font-weight-bold">Rp
                                                {{ number_format($it['total_harga'], 2, ',', '.') }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    wire:click="removeItem({{ $i }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada rincian</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="5" class="text-right">Grand Total</td>
                                        <td class="text-right">Rp
                                            {{ number_format($this->totalRincian, 2, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="button" class="btn btn-secondary mr-1" wire:click="resetInputFields"
                                data-dismiss="modal">Batal</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Transaksi' }}</button>
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
