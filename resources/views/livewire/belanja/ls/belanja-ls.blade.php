@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                     <h3 class="page-title">Bukti Pengeluaran / Belanja LS</h3>
                    <p class="page-subtitle mb-0">Kelola data belanja langsung (LS)</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="openForm" data-toggle="modal" data-target="#belanjaModal">
                         <i class="fas fa-plus mr-2"></i>Tambah Belanja LS
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
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                        <div class="stat-description">Data Belanja LS</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon green">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-label">Total Nominal</div>
                        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        <div class="stat-description">Akumulasi Nilai</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Section -->
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
                     <input type="text" 
                           class="form-control search-input" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari No Bukti, Uraian...">
                    @if($search)
                        <button type="button" class="clear-search" wire:click="$set('search', '')">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="220">No Bukti</th>
                            <th width="120">Tanggal</th>
                            <th>Uraian</th>
                            <th width="180" class="text-right">Total Nilai</th>
                            <th width="150" class="text-right">Potongan Pajak</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($belanja as $row)
                            <tr wire:key="{{ $row->id }}">
                                <td><span class="code-badge">{{ $loop->index + $belanja->firstItem() }}</span></td>
                                <td>
                                    <span class="code-badge">SPM-{{ $row->no_bukti }}/Diskominfo/{{ date('Y') }}</span>
                                </td>
                                <td style="font-weight: 500;">{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
                                <td class="text-left">{{ $row->uraian }}</td>
                                <td class="text-right">
                                     <span class="amount-badge">Rp {{ number_format($row->total_nilai, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-right">
                                    @if ($row->pajakLs->sum('nominal') > 0)
                                        <span class="badge badge-warning text-white mb-2" style="font-size: 11px;">Rp {{ number_format($row->pajakLs->sum('nominal'), 0, ',', '.') }}</span>
                                        <br>
                                        <a href="{{ route('pajakls', ['belanjaLsId' => $row->id]) }}"
                                            class="btn btn-xs btn-outline-secondary"
                                            title="Edit Pajak" style="border-radius: 4px;">
                                            <i class="fas fa-pencil-alt"></i> Edit
                                        </a>
                                    @else
                                        <a href="{{ route('pajakls', ['belanjaLsId' => $row->id]) }}"
                                            class="btn btn-sm btn-outline-primary" title="Rincian Pajak" style="border-radius: 6px; font-size: 12px;">
                                            <i class="fas fa-plus"></i> Rincian Pajak
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm" wire:click="edit({{ $row->id }})" title="Edit">
                                            <i class="fas fa-pencil-alt text-white"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" wire:click="delete_confirmation({{ $row->id }})" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group ml-1">
                                        <button wire:click="printTaiLs({{ $row->id }})" 
                                            class="btn btn-secondary btn-sm" 
                                            style="border-radius: 6px 0 0 6px;" 
                                            title="Print"
                                            wire:loading.attr="disabled"
                                            wire:target="printTaiLs({{ $row->id }})">
                                            
                                            <span wire:loading.remove wire:target="printTaiLs({{ $row->id }})">
                                                <i class="fas fa-print"></i>
                                            </span>
                                            
                                            <span wire:loading wire:target="printTaiLs({{ $row->id }})">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </span>
                                        </button>
                                        <button wire:click="downloadTaiLs({{ $row->id }})"
                                            class="btn btn-success btn-sm" style="border-radius: 0 6px 6px 0;" title="Download">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

             <div class="mt-3">
                {{ $belanja->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal Belanja LS -->
    <div wire:ignore.self class="modal fade" id="belanjaModal" tabindex="-1" aria-labelledby="belanjaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit Belanja LS' : 'Tambah Belanja LS' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="closeForm" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- No Bukti -->
                        <div class="form-group row mb-3">
                            <label for="no_bukti" class="col-md-3 form-label" style="min-width: 150px;">No Bukti</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="SPM-" readonly style="background-color: #f4f6f9;">
                            </div>
                            <div class="col-md-3">
                                <input wire:model="no_bukti" type="text" class="form-control" id="no_bukti" placeholder="0001">
                                @error('no_bukti') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="/Diskominfo/{{ date('Y') }}" readonly style="background-color: #f4f6f9;">
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="form-group row mb-3">
                            <label for="tanggal" class="col-md-3 form-label" style="min-width: 150px;">Tanggal</label>
                            <div class="col-md-9">
                                <input wire:model="tanggal" type="date" class="form-control" id="tanggal">
                                @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Pilih / Edit Sub Kegiatan -->
                        <div class="form-group row mb-3">
                            <label for="sub_kegiatan" class="col-md-3 form-label" style="min-width: 150px;">Sub Kegiatan</label>
                            <div class="col-md-7">
                                @if ($sub_kegiatan_id)
                                    <input type="text" class="form-control" readonly value="{{ $sub_kegiatan_kode }} - {{ $sub_kegiatan_nama }}">
                                @else
                                    <button type="button" class="btn btn-outline-primary btn-block" wire:click="openModal">
                                        <i class="fas fa-search mr-1"></i> Pilih Sub Kegiatan
                                    </button>
                                @endif
                                @error('sub_kegiatan_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                @if ($sub_kegiatan_id)
                                    <button type="button" class="btn btn-warning btn-block" wire:click="openModal">
                                        <i class="fas fa-edit"></i> Ubah
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Total Nilai Header -->
                        <div class="form-group row mb-3">
                            <label for="total_nilai" class="col-md-3 form-label" style="min-width: 150px;">Total Nilai</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control font-weight-bold" 
                                    value="Rp {{ number_format(array_sum(array_column($rkas, 'nilai')), 2, ',', '.') }}" 
                                    readonly style="background-color: #e8f0fe; color: #1a73e8;">
                            </div>
                        </div>

                        <!-- Tabel Detail Transaksi -->
                         @if ($sub_kegiatan_id)
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" style="background: white;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Nama Belanja</th>
                                                    <th width="180">Sisa Anggaran</th>
                                                    <th width="200">Input Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($rkas as $index => $rkaItem)
                                                    <tr>
                                                        <td>
                                                            <small class="text-muted d-block">{{ $rkaItem['kode_belanja'] }}</small>
                                                            {{ $rkaItem['nama_belanja'] }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($rkaItem['initial_sisa'] - $rkaItem['nilai'], 0, ',', '.') }}
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm text-right"
                                                                wire:model="rkas.{{ $index }}.nilai"
                                                                placeholder="0">
                                                            @if ($rkaItem['nilai'] > $rkaItem['initial_sisa'])
                                                                <small class="text-danger d-block mt-1">
                                                                    Max: {{ number_format($rkaItem['initial_sisa'], 0, ',', '.') }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center">Data RKAS tidak tersedia.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-soft-info d-flex align-items-center mb-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                Belum ada data RKAS. Silakan pilih Sub Kegiatan terlebih dahulu.
                            </div>
                        @endif

                        <!-- Uraian -->
                        <div class="form-group row mb-3">
                            <label for="uraian" class="col-md-3 form-label" style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" id="uraian" placeholder="Masukkan uraian transaksi..." rows="3"></textarea>
                                @error('uraian') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeForm" class="btn btn-secondary">Batal</button>
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}" class="btn btn-success">
                         <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sub Kegiatan -->
    <div wire:ignore.self class="modal fade" id="subkegiatanModal" tabindex="-1" aria-labelledby="subkegiatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Sub Kegiatan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <livewire:sub-kegiatan-hierarchy />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Print Modal -->
     <div wire:ignore.self class="modal fade" id="viewBelanja" tabindex="-1" aria-labelledby="viewBelanja" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                     <h5 class="modal-title" id="viewBelanja">Preview Dokumen</h5>
                    <button type="button" class="close" wire:click="closeModalPdf" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <embed src="{{ route('helper.show-picture', ['path' => 'public/reports/laporan_belanja_' . $pathpdf]) }}" class="col-12" height="600px" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="closeModalPdf">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('belanja', (event) => {
                $('#viewBelanja').modal("show");
            });
        });
    </script>
@endpush
