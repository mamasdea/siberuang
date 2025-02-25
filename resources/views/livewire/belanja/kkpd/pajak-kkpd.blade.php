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
    <div class="card">
        <div class="card">
            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="card-title">Daftar Potongan Pajak</h3>
                    <div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" wire:click="closeFormPajak"
                                data-toggle="modal" data-target="#formPotonganPajakModal"><i class="fas fa-plus"></i>
                                Tambah
                                Pajak</button>
                            <a href="{{ url('belanja-kkpd') }}" class="btn btn-outline-success btn-sm ml-2"><i
                                    class="fas fa-undo-alt"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>No Bukti</th>
                        <td>{{ $no_bukti }}</td>
                    </tr>
                    <tr>
                        <th>Uraian</th>
                        <td>{{ $uraian }}</td>
                    </tr>
                    <tr>
                        <th>Nilai</th>
                        <td><strong>Rp {{ number_format($nilai, 2, ',', '.') }}</strong></td>
                    </tr>
                </table>

            </div>
            <div class="card-body">
                <table class="table mt-4">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>No.</th>
                            <th>Jenis Pajak</th>
                            <th>Id Billing</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pajaks as $pajak)
                            <tr>
                                <td>{{ $pajaks->firstItem() + $loop->index }}</td>
                                <td>{{ $pajak->jenis_pajak }}</td>
                                <td>{{ $pajak->no_billing }}</td>
                                <td>Rp {{ number_format($pajak->nominal, 2, ',', '.') }}</td>
                                <td class="text-center">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $pajaks->links() }}
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
