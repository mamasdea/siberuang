<div>
    <input type="text" wire:model.live="search" placeholder="Cari Penerima..." class="form-control mb-3" />

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Bank</th>
                <th>No Rekening</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penerimas as $penerima)
                <tr>
                    <td>{{ $penerima->nama }}</td>
                    <td>{{ $penerima->bank }}</td>
                    <td>{{ $penerima->no_rekening }}</td>
                    <td>
                        <button wire:click="selectPenerima({{ $penerima->id }})" class="btn btn-primary">
                            Pilih
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $penerimas->links() }}


</div>
