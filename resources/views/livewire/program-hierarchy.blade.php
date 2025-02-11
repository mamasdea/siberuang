<div>
    <div class="container mt-4">
        <input type="text" wire:model.live="search" placeholder="Search sub kegiatan..."
            class="form-control mb-3 shadow-sm" />

        @foreach ($subKegiatans as $subKegiatan)
            <div class="card mb-2 shadow-sm">
                <div class="card-header bg-secondary text-white cursor-pointer"
                    wire:click="selectSubKegiatan({{ $subKegiatan->id }})" style="transition: background-color 0.3s;">
                    {{ $subKegiatan->nama }} ({{ $subKegiatan->kode }})
                </div>
                @if ($selectedSubKegiatanId === $subKegiatan->id)
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Kode Belanja</th>
                                    <th>Nama Belanja</th>
                                    <th>Sisa Anggaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subKegiatan->rkas as $rka)
                                    <tr>
                                        <td>{{ $rka->kode_belanja }}</td>
                                        <td>{{ $rka->nama_belanja }}</td>
                                        <td>{{ number_format($rka->sisaAnggaran, 2, ',', '.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary"
                                                wire:click="kirim({{ $rka->id }})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach

    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .card-header:hover {
            background-color: #0056b3 !important;
        }

        .card-body {
            padding-left: 2rem;
        }

        .card {
            border-radius: 10px;
        }

        .table {
            margin-bottom: 0;
        }

        .btn {
            margin-right: 5px;
        }
    </style>
</div>
