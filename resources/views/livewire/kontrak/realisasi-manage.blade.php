<div>
    @push('css')
        <style>
            [x-cloak] {
                display: none !important
            }

            .table thead th {
                position: sticky;
                top: 0;
                background: #f8fafc;
                z-index: 1
            }
        </style>
    @endpush

    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex align-items-center mb-3">
            <nav aria-label="breadcrumb" class="mr-auto">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('kontrak') }}" wire:navigate>Kontrak</a></li>
                    <li class="breadcrumb-item active">Realisasi</li>
                </ol>
            </nav>
            <div class="ml-2">
                <a href="{{ route('kontrak') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-primary btn-sm" wire:click="createOpen" data-toggle="modal"
                    data-target="#realisasiModal">
                    <i class="fas fa-plus"></i> Tambah Realisasi
                </button>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">Realisasi Kontrak</h4>
                        <div class="text-muted">
                            <strong>No:</strong> {{ $kontrak->nomor_kontrak }} |
                            <strong>Tgl:</strong>
                            {{ \Illuminate\Support\Carbon::parse($kontrak->tanggal_kontrak)->format('d/m/Y') }} |
                            <strong>Perusahaan:</strong> {{ $kontrak->nama_perusahaan }}
                        </div>
                    </div>
                    <span class="badge badge-info align-self-center">
                        {{ strtoupper($kontrak->bentuk_perusahaan ?? '') }}
                    </span>
                </div>

                @php
                    $nilai = (float) $kontrak->nilai;
                    $real = (float) $total;
                    $sisa = max(0, $nilai - $real);
                    $pct = $nilai > 0 ? min(100, round(($real / $nilai) * 100, 1)) : 0;
                    $bar = $pct < 50 ? 'bg-info' : ($pct < 100 ? 'bg-warning' : 'bg-success');
                @endphp

                <div class="row mt-3">
                    <div class="col-md-4 mb-2">
                        <div class="p-3 border rounded">
                            <div class="text-muted">Nilai Kontrak</div>
                            <div class="h5 mb-0">Rp {{ number_format($nilai, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="p-3 border rounded">
                            <div class="text-muted">Total Realisasi</div>
                            <div class="h5 mb-0 text-primary">Rp {{ number_format($real, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="p-3 border rounded">
                            <div class="text-muted">Sisa</div>
                            <div class="h5 mb-0 {{ $sisa > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($sisa, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between text-muted mb-1">
                        <span>Progress Realisasi</span><span>{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:.6rem">
                        <div class="progress-bar {{ $bar }}" role="progressbar"
                            style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toolbar kanan --}}
        <div class="d-flex justify-content-end mb-2">
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-2">Show:</label>
                <select wire:model.live="paginate" class="form-control form-control-sm" style="max-width:120px">
                    @foreach ([5, 10, 15, 20] as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Tabel realisasi --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr class="text-center">
                                <th style="width:70px;">No.</th>
                                <th style="width:140px;">Tanggal</th>
                                <th style="width:120px;">Jenis</th>
                                <th style="width:140px;">Termin</th>
                                <th class="text-right" style="width:180px;">Nominal (Rp)</th>
                                <th>Berita Acara</th>
                                <th style="width:130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($realisasis as $i => $r)
                                <tr>
                                    <td class="text-center">{{ $realisasis->firstItem() + $i }}</td>
                                    <td class="text-center">
                                        {{ \Illuminate\Support\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                                    <td class="text-uppercase text-center">{{ $r->tipe }}</td>
                                    <td class="text-center">
                                        @if ($r->tipe === 'termin' && $r->termin_ke)
                                            @php
                                                $n = $r->termin_ke;
                                                $rom = '';
                                                $map = [
                                                    1000 => 'M',
                                                    900 => 'CM',
                                                    500 => 'D',
                                                    400 => 'CD',
                                                    100 => 'C',
                                                    90 => 'XC',
                                                    50 => 'L',
                                                    40 => 'XL',
                                                    10 => 'X',
                                                    9 => 'IX',
                                                    5 => 'V',
                                                    4 => 'IV',
                                                    1 => 'I',
                                                ];
                                                $t = $n;
                                                foreach ($map as $v => $sym) {
                                                    while ($t >= $v) {
                                                        $rom .= $sym;
                                                        $t -= $v;
                                                    }
                                                }
                                            @endphp
                                            <span class="badge badge-primary">Termin {{ $rom ?: $r->termin_ke }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right">Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $labels = [
                                                'pemeriksaan' => 'Pemeriksaan',
                                                'serah_terima' => 'Serah Terima Barang/Jasa',
                                                'pekerjaan' => 'Serah Terima Pekerjaan',
                                                'penerimaan' => 'Penerimaan',
                                                'administratif' => 'Administratif',
                                                'pembayaran' => 'Pembayaran',
                                            ];
                                        @endphp

                                        @forelse($r->beritaAcaras as $ba)
                                            <div class="d-inline-flex align-items-center mb-1 mr-1">
                                                <span class="badge badge-info badge-m">
                                                    {{ $labels[$ba->jenis] ?? $ba->jenis }}:
                                                    {{ $ba->nomor }}
                                                    ({{ \Illuminate\Support\Carbon::parse($ba->tanggal)->format('d/m/Y') }})
                                                </span>
                                                {{-- <button type="button" class="btn btn-outline-primary btn-sm ml-2"
                                                    title="Cetak {{ $labels[$ba->jenis] ?? $ba->jenis }}"
                                                    wire:click="printBA('{{ $ba->jenis }}', {{ $r->id }})">
                                                    <i class="fas fa-print"></i>
                                                </button> --}}
                                            </div>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('realisasi.ba.all', ['kontrak' => $kontrak->id, 'realisasi' => $r->id]) }}"
                                                target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            <button class="btn btn-warning text-white"
                                                wire:click="edit({{ $r->id }})" data-toggle="modal"
                                                data-target="#realisasiModal" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-danger"
                                                wire:click="deleteConfirm({{ $r->id }})" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted p-4">Belum ada realisasi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $realisasis->links('livewire::bootstrap') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create/Update --}}
    <div wire:ignore.self class="modal fade" id="realisasiModal" tabindex="-1" role="dialog"
        aria-labelledby="realisasiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ $realisasi_id ? 'Edit' : 'Tambah' }} Realisasi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="row">
                            {{-- Kiri --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Realisasi</label>
                                    <select class="form-control form-control-sm" wire:model.live="realisasi_tipe">
                                        <option value="sekaligus">Sekaligus</option>
                                        <option value="termin">Termin</option>
                                    </select>
                                    @error('realisasi_tipe')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if ($realisasi_tipe === 'termin')
                                    <div class="form-group">
                                        <label>Termin Ke <small
                                                class="text-muted">({{ $this->terminLabel }})</small></label>
                                        <input type="number" min="1" class="form-control form-control-sm"
                                            wire:model.live="realisasi_termin_ke">
                                        @error('realisasi_termin_ke')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>Tanggal *</label>
                                    <input type="date" class="form-control form-control-sm"
                                        wire:model.live="realisasi_tanggal">
                                    @error('realisasi_tanggal')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Periode & Progres Fisik (khusus TERMIN) --}}
                                @if ($realisasi_tipe === 'termin')
                                    <div class="form-group">
                                        <label>Periode</label>
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="mis. 2025-09 / September 2025 / Triwulan III 2025"
                                            wire:model.live="periode">
                                        @error('periode')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Progres Fisik (%)</label>
                                        <input type="number" min="0" max="100" step="0.01"
                                            class="form-control form-control-sm" wire:model.live="progres_fisik">
                                        @error('progres_fisik')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Kanan: Berita Acara --}}
                            <div class="col-md-6">
                                <label class="d-block mb-2">Berita Acara (opsional)</label>

                                @foreach ([
        'pemeriksaan' => 'Berita Acara Pemeriksaan',
        'serah_terima' => 'Berita Acara Serah Terima Barang/Jasa',
        'pekerjaan' => 'Berita Acara Serah Terima Pekerjaan',
        'penerimaan' => 'Berita Acara Penerimaan',
        'administratif' => 'Berita Acara Administratif',
        'pembayaran' => 'Berita Acara Pembayaran',
    ] as $key => $label)
                                    @php
                                        $flag = "ba_{$key}";
                                        $nom = "ba_{$key}_nomor";
                                        $tgl = "ba_{$key}_tanggal";
                                    @endphp
                                    <div class="border rounded p-2 mb-2" wire:key="ba-{{ $key }}"
                                        x-data="{ on: @entangle($flag) }">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="ba_{{ $key }}" x-model="on"
                                                    wire:model.live="{{ $flag }}">
                                                <label class="form-check-label"
                                                    for="ba_{{ $key }}">{{ $label }}</label>
                                            </div>
                                            {{-- Tombol Cetak (preview) --}}
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                title="Cetak {{ $label }}"
                                                wire:click="printBA('{{ $key }}')">
                                                <i class="fas fa-print"></i> Cetak
                                            </button>
                                        </div>

                                        <div class="form-row mt-2" x-show="on" x-cloak>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="Nomor" wire:model.live="{{ $nom }}">
                                                @error($nom)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col">
                                                <input type="date" class="form-control form-control-sm"
                                                    wire:model.live="{{ $tgl }}">
                                                @error($tgl)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if ($realisasi_tipe === 'sekaligus')
                                    <div class="alert alert-info mt-2">
                                        Periode opsional (boleh kosong). Progres fisik akan otomatis
                                        <strong>100%</strong> saat disimpan.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-2">Rincian Realisasi</h6>

                        {{-- SEKALIGUS --}}
                        @if ($realisasi_tipe === 'sekaligus')
                            <div class="alert alert-info py-2">
                                Rincian otomatis mengikuti seluruh rincian kontrak (qty penuh). Harga & satuan mengikuti
                                kontrak.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang/Jasa</th>
                                            <th>Qty Kontrak</th>
                                            <th>Satuan</th>
                                            <th>Harga (Rp)</th>
                                            <th>Total (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kontrak->rincians as $i => $rc)
                                            @php $line = (float)$rc->kuantitas * (float)$rc->harga; @endphp
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td>{{ $rc->nama_barang }}</td>
                                                <td class="text-right">
                                                    {{ number_format($rc->kuantitas, 2, ',', '.') }}</td>
                                                <td class="text-center">{{ $rc->satuan }}</td>
                                                <td class="text-right">Rp {{ number_format($rc->harga, 2, ',', '.') }}
                                                </td>
                                                <td class="text-right">Rp {{ number_format($line, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">Total</th>
                                            <th class="text-right">Rp
                                                {{ number_format($kontrak->nilai, 2, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        {{-- TERMIN --}}
                        @if ($realisasi_tipe === 'termin')
                            @error('itemInputs')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang/Jasa</th>
                                            <th>Qty Kontrak</th>
                                            <th>Sudah Realisasi</th>
                                            <th>Sisa</th>
                                            <th>Input Qty</th>
                                            <th>Satuan</th>
                                            <th>Harga (Rp)</th>
                                            <th>Total (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemInputs as $i => $it)
                                            @php
                                                $line = (float) ($it['qty_input'] ?? 0) * (float) $it['harga'];
                                                $currentInput = (float) ($it['qty_input'] ?? 0);
                                                $totalRealized = (float) $it['qty_teralisasi'] + $currentInput;
                                                $sisaSetelahInput = max(0, (float) $it['qty_kontrak'] - $totalRealized);
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td>{{ $it['nama_barang'] }}</td>
                                                <td class="text-right">
                                                    {{ number_format($it['qty_kontrak'], 2, ',', '.') }}</td>
                                                <td class="text-right">
                                                    {{ number_format($totalRealized, 2, ',', '.') }}</td>
                                                <td class="text-right">
                                                    {{ number_format($sisaSetelahInput, 2, ',', '.') }}</td>
                                                <td style="width:140px;">
                                                    <input type="number" min="0" max="{{ $it['qty_sisa'] }}"
                                                        step="0.01" class="form-control form-control-sm text-right"
                                                        wire:model.live="itemInputs.{{ $i }}.qty_input">
                                                    @error("itemInputs.{$i}.qty_input")
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </td>
                                                <td class="text-center">{{ $it['satuan'] }}</td>
                                                <td class="text-right">Rp
                                                    {{ number_format($it['harga'], 2, ',', '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($line, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @php
                                        $totalTermin = array_sum(
                                            array_map(
                                                fn($r) => (float) ($r['qty_input'] ?? 0) * (float) $r['harga'],
                                                $itemInputs,
                                            ),
                                        );
                                    @endphp
                                    <tfoot>
                                        <tr>
                                            <th colspan="8" class="text-right">Total Termin</th>
                                            <th class="text-right">Rp {{ number_format($totalTermin, 2, ',', '.') }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        <div class="mt-3 d-flex">
                            <button type="submit"
                                class="btn btn-primary mr-2">{{ $realisasi_id ? 'Update' : 'Simpan' }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#realisasiModal').on('shown.bs.modal', function() {
                    const first = this.querySelector('input,select,textarea');
                    first && first.focus();
                });
            });

            // Livewire 3: buka tab cetak saat event 'open-window' dikirim dari komponen
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-window', ({
                    url
                }) => {
                    window.open(url, '_blank');
                });
            });
        </script>
    @endpush
</div>
