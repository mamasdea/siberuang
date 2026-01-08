@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <!-- Modal Potongan Pajak -->
    <div wire:ignore.self class="modal fade" id="formPotonganPajakModal" tabindex="-1" role="dialog"
        aria-labelledby="formPotonganPajakModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="formPotonganPajakModalLabel">
                        {{ $updateMode ? 'Edit Potongan Pajak' : 'Tambah Potongan Pajak' }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="card">
                            <div class="card-body p-3">
                                <!-- Form Select Jenis Pajak -->
                                <div class="form-group d-flex align-items-center">
                                    <label for="jenis_pajak" class="mr-2" style="min-width: 150px;">Jenis
                                        Pajak</label>
                                    <select wire:model="jenis_pajak" class="form-control" id="jenis_pajak">
                                        <option value="">Pilih Jenis Pajak</option>
                                        <option value="PPN">PPN</option>
                                        <option value="PPh 21">PPh 21</option>
                                        <option value="PPh 22">PPh 22</option>
                                        <option value="PPh 23">PPh 23</option>
                                    </select>
                                </div>
                                @error('jenis_pajak')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <!-- Form Input ID Billing -->
                                <div class="form-group d-flex align-items-center">
                                    <label for="no_billing" class="mr-2" style="min-width: 150px;">ID Billing</label>
                                    <input wire:model="no_billing" type="text" class="form-control" id="no_billing"
                                        placeholder="Enter ID Billing">
                                </div>
                                @error('no_billing')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <!-- Form Input NTPN -->
                                <div class="form-group d-flex align-items-center">
                                    <label for="ntpn" class="mr-2" style="min-width: 150px;">NTPN</label>
                                    <input wire:model="ntpn" type="text" class="form-control" id="ntpn"
                                        placeholder="Enter NTPN">
                                </div>
                                @error('ntpn')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <!-- Form Input NTB -->
                                <div class="form-group d-flex align-items-center">
                                    <label for="ntb" class="mr-2" style="min-width: 150px;">NTB</label>
                                    <input wire:model="ntb" type="text" class="form-control" id="ntb"
                                        placeholder="Enter NTB">
                                </div>
                                @error('ntb')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <!-- Form Input Nominal -->
                                <div class="form-group d-flex align-items-center">
                                    <label for="nominal" class="mr-2" style="min-width: 150px;">Nominal</label>
                                    <input type="text" inputmode="numeric" wire:model.live="nominal"
                                        class="form-control" id="nominal" placeholder="Enter Nominal">
                                    <span class="mr-2 ml-2">/</span>
                                    <input type="text" inputmode="numeric" class="form-control" id="nominal_display"
                                        value="{{ number_format((float) ($nominal ?? 0), 2, ',', '.') }}" readonly>
                                </div>
                                @error('nominal')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeFormPajak"
                        data-dismiss="modal">Batal</button>
                    <button type="button" wire:click.prevent="{{ $updateMode ? 'update' : 'store' }}"
                        class="btn btn-primary">{{ $updateMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Tabel pajak -->
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Daftar Potongan Pajak</h3>
                    <p class="page-subtitle mb-0">Kelola data potongan pajak untuk belanja</p>
                </div>
                <div>
                    <button class="btn btn-modern-add" wire:click="closeFormPajak"
                        data-toggle="modal" data-target="#formPotonganPajakModal">
                        <i class="fas fa-plus mr-2"></i>Tambah Pajak
                    </button>
                    <a href="{{ url('belanja') }}" class="btn btn-outline-success ml-2">
                        <i class="fas fa-undo-alt mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Info Belanja -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">No Bukti</small>
                                    <span class="code-badge">TBP-{{ $no_bukti }}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Uraian</small>
                                    <strong>{{ $uraian }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Nilai Belanja</small>
                                    <span class="amount-badge">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th width="60">No.</th>
                            <th>Jenis Pajak</th>
                            <th>Transaksi</th>
                            <th width="180" class="text-right">Nominal</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pajaks as $pajak)
                            <tr>
                                <td><span class="code-badge">{{ $pajaks->firstItem() + $loop->index }}</span></td>
                                <td style="font-weight: 500;">{{ $pajak->jenis_pajak }}</td>
                                <td>
                                    <div class="mb-1"><strong>ID Billing:</strong> {{ $pajak->no_billing ?: '-' }}</div>
                                    <div class="mb-1"><strong>NTPN:</strong> {{ $pajak->ntpn ?: '-' }}</div>
                                    <div><strong>NTB:</strong> {{ $pajak->ntb ?: '-' }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="amount-badge">Rp {{ number_format($pajak->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm text-white"
                                            wire:click="edit({{ $pajak->id }})" data-toggle="modal"
                                            data-target="#formPotonganPajakModal" data-toggle="tooltip" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="delete_confirmation({{ $pajak->id }})" data-toggle="tooltip"
                                            title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                                    <p class="mb-0 mt-2 text-muted">Belum ada data potongan pajak</p>
                                    <small class="text-muted">Klik tombol "Tambah Pajak" untuk menambah data</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $pajaks->links() }}
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
@endpush
