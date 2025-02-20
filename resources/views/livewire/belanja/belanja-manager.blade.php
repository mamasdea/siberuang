<div>
    <div class="card card-secondary">
        <div class="card-body bg-navy color-palette">
            <h5 class="card-title">Bukti Pengeluaran / Belanja GU</h5>
        </div>
        <div class="tab-content mt-2">
            <div class="tab-pane fade show active" id="bukti">
                <div class="p-2">
                    <!-- Header: Tambah Belanja -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-outline-primary" wire:click="openForm" data-toggle="modal"
                            data-target="#belanjaModal">
                            <i class="fas fa-plus"></i> Tambah Belanja
                        </button>
                    </div>

                    <!-- Filter & Pencarian -->
                    <div class="row mb-3">
                        <!-- Show -->
                        <div class="col-auto d-flex align-items-center">
                            <label class="mr-2">Show:</label>
                            <select wire:model.live="paginate" class="form-control form-control-sm w-auto">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <!-- Bulan -->
                        <div class="col-auto d-flex align-items-center">
                            <label class="mr-2">Bulan:</label>
                            <select wire:model.live="bulan" class="form-control form-control-sm w-auto">
                                <option value="">Bulan sekarang</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>

                        <!-- Search (Menggunakan sisa ruang) -->
                        <div class="col d-flex align-items-center">
                            <label class="mr-2">Cari:</label>
                            <div class="input-group input-group-sm flex-grow-1">
                                <input type="text" class="form-control form-control-sm" placeholder="Search"
                                    wire:model.live="search">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="50">No.</th>
                                    <th width="120">No Bukti</th>
                                    <th width="100">Tanggal</th>
                                    <th width="380">Uraian</th>
                                    <th width="150">Nilai</th>
                                    <th width="50">Penerimaan</th>
                                    <th width="50">Pajak</th>
                                    <th width="50">Transfer</th>
                                    <th width="50">SIPD</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($belanja as $row)
                                    <tr>
                                        <td>{{ $loop->index + $belanja->firstItem() }}</td>
                                        <td>TBP-{{ $row->no_bukti }}</td>
                                        <td>{{ $row->tanggal }}</td>
                                        <td class="text-left">{{ $row->uraian }}</td>
                                        <td class="text-right">
                                            {{ number_format($row->nilai, 2) }} <br>
                                            <span
                                                class="badge {{ $row->nilai == ($row->total_penerimaan ?? 0) + ($row->total_pajak ?? 0) ? 'badge-success' : 'badge-danger' }}"
                                                data-toggle="tooltip"
                                                title="Penerimaan = {{ number_format($row->total_penerimaan ?? 0, 2) }} + Pajak = {{ number_format($row->total_pajak ?? 0, 2) }}">
                                                {{ number_format(($row->total_penerimaan ?? 0) + ($row->total_pajak ?? 0), 2) }}
                                            </span>
                                        </td>

                                        <td>
                                            <a href="{{ route('penerimaan', $row->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-coins"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('pajak', $row->id) }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <input type="checkbox"
                                                wire:click="toggleField({{ $row->id }}, 'is_transfer')"
                                                {{ $row->is_transfer ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <input type="checkbox"
                                                wire:click="toggleField({{ $row->id }}, 'is_sipd')"
                                                {{ $row->is_sipd ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-warning btn-sm"
                                                    wire:click="edit({{ $row->id }})" data-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                    wire:click="delete_confirmation({{ $row->id }})"
                                                    data-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <button wire:click="printTai({{ $row->id }})"
                                                    class="btn btn-sm btn-secondary" wire:loading.attr="disabled"
                                                    wire:target="printTai({{ $row->id }})">

                                                    <span wire:loading wire:target="printTai({{ $row->id }})">
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>

                                                    </span>

                                                    <span wire:loading.remove
                                                        wire:target="printTai({{ $row->id }})">
                                                        <i class="fas fa-print"></i> </span>
                                                </button>



                                                <button wire:click="downloadTai({{ $row->id }})"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-2">
                        {{ $belanja->links('livewire::bootstrap') }}
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="spjkan">
                <div class="card">
                    bukti sudah terspjkan
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Belanja -->
    <div wire:ignore.self class="modal fade" id="belanjaModal" tabindex="-1" aria-labelledby="belanjaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Edit Belanja' : 'Tambah Belanja' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        wire:click="closeForm">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form Belanja -->
                    <form>
                        <!-- No Bukti -->

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

                        <!-- Rekening -->
                        <div class="form-group row mb-3">
                            <label for="rekening" class="col-md-3 form-label"
                                style="min-width: 150px;">Rekening</label>
                            <div class="col-md-9">
                                <button type="button" wire:click='openModal' class="btn btn-primary">Pilih
                                    Rekening</button>
                            </div>
                        </div>
                        <!-- Kondisi jika ada rincian sub kegiatan -->
                        @if ($rincian_subkegiatan)
                            <div class="form-group row mb-3">
                                <label for="sub_kegiatan" class="col-md-3 form-label" style="min-width: 150px;">Sub
                                    Kegiatan</label>
                                <div class="col-md-9">
                                    <textarea id="subkegiatan" class="form-control" readonly>{{ $rka && $rka->subKegiatan ? $rka->subKegiatan->kode . ' - ' . $rka->subKegiatan->nama : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="rekening_belanja" class="col-md-3 form-label"
                                    style="min-width: 150px;">Rekening Belanja</label>
                                <div class="col-md-9">
                                    <textarea id="kodebelanja" class="form-control" readonly>{{ $rka ? $rka->kode_belanja . ' - ' . $rka->nama_belanja : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="rekening_belanja" class="col-md-3 form-label"
                                    style="min-width: 150px;">Sisa Anggaran</label>
                                <div class="col-md-9">
                                    <input id="sisaanggaran" type="text" class="form-control" readonly
                                        value="{{ number_format($rka->sisaanggaran, 2, ',', '.') }}">
                                </div>
                            </div>
                        @endif

                        <!-- Nilai -->
                        <div class="form-group row mb-3">
                            <label for="nilai" class="col-md-3 form-label" style="min-width: 150px;">Nilai</label>
                            <div class="col-md-5">
                                <input wire:model.live="nilai" type="number" class="form-control" id="nilai"
                                    placeholder="Enter Nilai">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control"
                                    value="{{ number_format((float) ($nilai ?? 0), 2, ',', '.') }}" readonly>
                            </div>
                            @error('nilai')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Uraian -->
                        <div class="form-group row mb-3">
                            <label for="uraian" class="col-md-3 form-label"
                                style="min-width: 150px;">Uraian</label>
                            <div class="col-md-9">
                                <textarea wire:model="uraian" class="form-control" id="uraian"></textarea>
                                @error('uraian')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click.prevent="{{ $isEdit ? 'update' : 'store' }}"
                        class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
                    <button type="button" wire:click="closeForm" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal program -->
    {{-- <div class="modal fade @if ($open) show @endif"
        style="display: @if ($open) block @else none @endif;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Program - Kegiatan - Sub Kegiatan</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"><i
                            class="fas fa-times"></i> </button>
                </div>
                <div class="modal-body">
                    <livewire:program-hierarchy>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div> --}}

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
                    <livewire:program-hierarchy>
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
            $('[data-toggle="tooltip"]').tooltip()
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('belanja', (event) => {
                $('#viewBelanja').modal("show");
            });
        });
    </script>
@endpush
