<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Uang KKPD</h3>
            <button class="btn btn-primary float-right" data-toggle="modal" data-target="#uangGiroModal"
                wire:click="resetInput()">Tambah</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Bukti</th>
                        <th>Tanggal</th>
                        <th>Uraian</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($uangGiros as $index => $uangGiro)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $uangGiro->no_bukti }}</td>
                            <td>{{ date('d-m-Y', strtotime($uangGiro->tanggal)) }}</td>
                            <td>{{ $uangGiro->uraian }}</td>
                            <td>{{ number_format($uangGiro->nominal, 2, ',', '.') }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" wire:click="edit({{ $uangGiro->id }})"
                                    data-toggle="modal" data-target="#uangGiroModal">Edit</button>
                                <button class="btn btn-danger btn-sm"
                                    wire:click="deleteConfirmation({{ $uangGiro->id }})">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="uangGiroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit' : 'Tambah' }} Uang Giro</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>No_Bukti</label>
                            <input type="text" class="form-control" wire:model="no_bukti">
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" wire:model="tanggal">
                        </div>
                        <div class="form-group">
                            <label>Uraian</label>
                            <input type="text" class="form-control" wire:model="uraian">
                        </div>
                        <div class="form-group">
                            <label>Nominal</label>
                            <input type="number" class="form-control" wire:model="nominal">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success"
                        wire:click="{{ $isEditMode ? 'update' : 'store' }}">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
