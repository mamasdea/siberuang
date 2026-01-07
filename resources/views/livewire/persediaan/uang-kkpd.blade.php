@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Data Uang KKPD</h3>
                    <p class="page-subtitle mb-0">Kelola data uang persediaan KKPD untuk tahun anggaran {{ $tahun }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-modern-add" data-toggle="modal" data-target="#uangKkpdModal"
                        wire:click="resetInput()">
                        <i class="fas fa-plus mr-2"></i>Tambah Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value">{{ $totalTransaksi }}</div>
                <div class="stat-description">Transaksi tahun {{ $tahun }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-label">Total Nominal</div>
                <div class="stat-value">{{ number_format($totalNominal / 1000000, 1) }}M</div>
                <div class="stat-description">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-label">Tahun Anggaran</div>
                <div class="stat-value">{{ $tahun }}</div>
                <div class="stat-description">Periode aktif saat ini</div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center section-header">
                <div>
                    <h5 class="section-title">Daftar Transaksi Uang KKPD</h5>
                    <p class="section-subtitle mb-0">Semua transaksi uang persediaan KKPD</p>
                </div>
            </div>

            <!-- Search Box -->
            <div class="mb-3">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           class="form-control search-input" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari berdasarkan No Bukti, Uraian, atau Tanggal...">
                    @if($search)
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
                            <th width="60">No</th>
                            <th width="150">No Bukti</th>
                            <th width="120">Tanggal</th>
                            <th>Uraian</th>
                            <th width="180">Nominal</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($uangKkpds as $index => $uangKkpd)
                            <tr>
                                <td>
                                    <span class="code-badge">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <span class="code-badge">{{ $uangKkpd->no_bukti }}</span>
                                </td>
                                <td style="font-weight: 500;">{{ date('d-m-Y', strtotime($uangKkpd->tanggal)) }}</td>
                                <td style="font-weight: 500;">{{ $uangKkpd->uraian }}</td>
                                <td>
                                    <span class="amount-badge">Rp {{ number_format($uangKkpd->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-action-edit" wire:click="edit({{ $uangKkpd->id }})"
                                            data-toggle="modal" data-target="#uangKkpdModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-action-delete"
                                            wire:click="deleteConfirmation({{ $uangKkpd->id }})">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h5>Belum Ada Data</h5>
                                        <p>Belum ada transaksi uang KKPD untuk tahun {{ $tahun }}</p>
                                        <button class="btn btn-modern-add" data-toggle="modal" data-target="#uangKkpdModal"
                                            wire:click="resetInput()">
                                            <i class="fas fa-plus mr-2"></i>Tambah Data Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $uangKkpds->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="uangKkpdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit' : 'Tambah' }} Uang KKPD</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>No Bukti</label>
                            <input type="text" class="form-control" wire:model="no_bukti" placeholder="Masukkan nomor bukti">
                            @error('no_bukti') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" wire:model="tanggal">
                            @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Uraian</label>
                            <input type="text" class="form-control" wire:model="uraian" placeholder="Masukkan uraian transaksi">
                            @error('uraian') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Nominal</label>
                            <input type="number" class="form-control" wire:model="nominal" placeholder="Masukkan nominal">
                            @error('nominal') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success"
                        wire:click="{{ $isEditMode ? 'update' : 'store' }}">
                        <i class="fas fa-save mr-2"></i>{{ $isEditMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
