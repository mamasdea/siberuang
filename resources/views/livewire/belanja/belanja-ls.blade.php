<div>
    <div class="card card-secondary">
        <div class="card-body bg-navy color-palette">
            <h3 class="card-title">Bukti Pengeluaran / Belanja LS</h3>
        </div>
        <div class="card-body">
            <!-- Nav tabs -->


            <!-- Tab content -->
            <div class="tab-content mt-2">
                <div class="tab-pane fade show active" id="bukti">
                    <div class="">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button class="btn btn-outline-primary btn-sm" wire:click="openForm" data-toggle="modal"
                                    data-target="#belanjaModal">
                                    <i class="fas fa-plus"></i> Tambah Belanja LS
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="col-lg-2">
                                <div class="d-flex align-items-center">
                                    <label class="col-form-label mr-2">Show:</label>
                                    <select wire:model.live="paginate" class="form-control form-control-sm">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Search"
                                        wire:model.live="search">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="50">No.</th>
                                        <th width="150">No Bukti</th>
                                        <th width="100">Tanggal</th>
                                        <th width="380">Uraian</th>
                                        <th width="150">Total Nilai</th>
                                        <th width="150">Potongan Pajak</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($belanja as $row)
                                        <tr>
                                            <td>{{ $loop->index + $belanja->firstItem() }}</td>
                                            <td>SPM-{{ $row->no_bukti }}/Diskominfo/{{ date('Y') }}</td>
                                            <td>{{ $row->tanggal }}</td>
                                            <td class="text-left">{{ $row->uraian }}</td>
                                            <td class="text-right">
                                                {{ number_format($row->total_nilai, 2) }} <br>
                                            </td>
                                            <td class="text-center">
                                                @if ($row->pajakLs->sum('nominal') > 0)
                                                    Rp {{ number_format($row->pajakLs->sum('nominal'), 2, ',', '.') }}
                                                    <a href="{{ route('pajakls', ['belanjaLsId' => $row->id]) }}"
                                                        class="btn btn-sm btn-outline-warning rounded-circle"
                                                        title="Edit Pajak">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('pajakls', ['belanjaLsId' => $row->id]) }}"
                                                        class="btn btn-sm btn-primary" title="Rincian Pajak">
                                                        <i class="fas fa-plus"></i> Rincian Pajak
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-warning btn-sm text-white"
                                                        wire:click="edit({{ $row->id }})" data-toggle="tooltip"
                                                        title="Edit">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                        wire:click="delete_confirmation({{ $row->id }})"
                                                        data-toggle="tooltip" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    <button wire:loading.remove
                                                        wire:target="printTai({{ $row->id }})"
                                                        wire:click="printTai({{ $row->id }})"
                                                        class="btn btn-sm btn-secondary" title="Print">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button wire:click="downloadTai({{ $row->id }})"
                                                        class="btn btn-sm btn-success" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="container mt-2 mb-2">
                            {{ $belanja->links('livewire::bootstrap') }}
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="spjkan">
                    <div class="card">
                        Bukti sudah di-SPJ-kan
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Belanja LS -->
    <div wire:ignore.self class="modal fade" id="belanjaModal" tabindex="-1" aria-labelledby="belanjaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit Belanja LS' : 'Tambah Belanja LS' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        wire:click="closeForm">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form Belanja LS -->
                    <form>
                        <!-- No Bukti -->
                        <div class="form-group row mb-3">
                            <label for="no_bukti" class="col-md-3 form-label" style="min-width: 150px;">No Bukti</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="SPM-" readonly>
                            </div>
                            <div class="col-md-3">
                                <input wire:model="no_bukti" type="text" class="form-control" id="no_bukti"
                                    placeholder="Enter No Bukti">
                                @error('no_bukti')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="/Diskominfo/{{ date('Y') }}"
                                    readonly>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="form-group row mb-3">
                            <label for="tanggal" class="col-md-3 form-label"
                                style="min-width: 150px;">Tanggal</label>
                            <div class="col-md-9">
                                <input wire:model="tanggal" type="date" class="form-control" id="tanggal">
                                @error('tanggal')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Pilih / Edit Sub Kegiatan -->
                        <div class="form-group row mb-3">
                            <label for="sub_kegiatan" class="col-md-3 form-label" style="min-width: 150px;">Sub
                                Kegiatan</label>
                            <div class="col-md-7">
                                @if ($sub_kegiatan_id)
                                    <input type="text" class="form-control" readonly
                                        value="{{ $sub_kegiatan_kode }} - {{ $sub_kegiatan_nama }}">
                                @else
                                    <button type="button" class="btn btn-primary" wire:click="openModal">Pilih Sub
                                        Kegiatan</button>
                                @endif
                                @error('sub_kegiatan_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                @if ($sub_kegiatan_id)
                                    <button type="button" class="btn btn-warning" wire:click="openModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Total Nilai Header -->
                        <div class="form-group row mb-3">
                            <label for="total_nilai" class="col-md-3 form-label" style="min-width: 150px;">Total
                                Nilai</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control"
                                    value="{{ number_format(array_sum(array_column($rkas, 'nilai')), 2, ',', '.') }}"
                                    readonly>
                            </div>
                        </div>

                        <!-- Tabel Detail Transaksi -->
                        @if ($sub_kegiatan_id)
                            <div class="form-group row mb-3">
                                <div class="col-md">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama Belanja</th>
                                                <th>Sisa Anggaran</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rkas as $index => $rkaItem)
                                                <tr>
                                                    <td>{{ $rkaItem['kode_belanja'] }} -
                                                        {{ $rkaItem['nama_belanja'] }}</td>
                                                    <!-- Hitung sisa anggaran dinamis: initial_sisa - nilai -->
                                                    <td>{{ number_format($rkaItem['initial_sisa'] - $rkaItem['nilai'], 2, ',', '.') }}
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="rkas.{{ $index }}.nilai"
                                                            placeholder="Masukkan nilai transaksi">
                                                        @if ($rkaItem['nilai'] > $rkaItem['initial_sisa'])
                                                            <small class="text-danger">
                                                                Nilai tidak boleh melebihi sisa anggaran (Rp
                                                                {{ number_format($rkaItem['initial_sisa'], 2, ',', '.') }})
                                                            </small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        Data RKAS tidak tersedia.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                Belum ada data RKAS. Silakan pilih Sub Kegiatan terlebih dahulu.
                            </div>
                        @endif

                        <!-- Uraian -->
                        <div class="form-group row mb-3">
                            <label for="uraian" class="col-md-3 form-label"
                                style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" id="uraian" placeholder="Masukkan uraian transaksi"></textarea>
                                @error('uraian')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}"
                        class="btn btn-primary">
                        {{ $isEdit ? 'Update' : 'Save' }}
                    </button>
                    <button type="button" wire:click="closeForm" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sub Kegiatan -->
    <div wire:ignore.self class="modal fade" id="subkegiatanModal" tabindex="-1"
        aria-labelledby="subkegiatanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Program - Kegiatan - Sub Kegiatan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <livewire:sub-kegiatan-hierarchy />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Print -->
    <div class="modal fade" id="viewBelanja" tabindex="-1" aria-labelledby="viewBelanja" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBelanja"></h5>
                    <button type="button" class="close" wire:click="closeModalPdf" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <embed
                        src="{{ route('helper.show-picture', ['path' => 'public/reports/laporan_belanja_' . $pathpdf]) }}"
                        class="col-12" height="600px" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        wire:click="closeModalPdf">Close</button>
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
