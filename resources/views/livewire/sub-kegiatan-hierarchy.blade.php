<div>
    <div class="card">
        <div class="card-header">
            <input type="text" class="form-control" placeholder="Cari Sub Kegiatan..." wire:model.live="searchsub">
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subKegiatans as $sub)
                        <tr>
                            <td>{{ $sub->id }}</td>
                            <td>{{ $sub->kode }}</td>
                            <td>{{ $sub->nama }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                    wire:click="selectSubKegiatan({{ $sub->id }})">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $subKegiatans->links('livewire::bootstrap') }}
        </div>
    </div>
</div>
