@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Buku Kas Pengeluaran / TU</h3>
            <p class="page-subtitle mb-0">Laporan transaksi harian Tambahan Uang Persediaan</p>
        </div>
        <div class="content-card">
            <div class="row align-items-end mb-4">
                <div class="col-md-4">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="mulai" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Akhir</label>
                    <input type="date" class="form-control" wire:model.live="end" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-4 text-right">
                    <button class="btn btn-modern-add" onclick="printBkuTu()">
                        <i class="fas fa-print mr-2"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="bg-white p-4 border rounded" id="print-area-tu">
                <div class="text-center mb-4">
                    <h3 class="font-weight-bold" style="font-size: 18px; margin-bottom: 5px;">BUKU KAS PENGELUARAN TU</h3>
                    <p class="mb-1" style="font-size: 14px;">Dinas Komunikasi dan Informatika Kabupaten Wonosobo</p>
                    <p class="mb-0" style="font-size: 14px;">Periode:
                        <strong>
                            {{ \Carbon\Carbon::parse($mulai)->translatedFormat('d F Y') }} -
                            {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                        </strong>
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm modern-table" style="font-size: 12px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 100px;">Tanggal</th>
                                <th style="width: 175px;">No Bukti</th>
                                <th style="width: 100px;">Rekening</th>
                                <th>Uraian</th>
                                <th style="width: 120px;">Debet</th>
                                <th style="width: 120px;">Kredit</th>
                                <th style="width: 120px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalSaldo = $saldo; @endphp

                            {{-- Saldo Awal --}}
                            <tr class="bg-light font-weight-bold">
                                <td>
                                    {{ \Carbon\Carbon::parse($mulai)->format('m-d') == '01-01' ? \Carbon\Carbon::parse($mulai)->format('d-m-Y') : \Carbon\Carbon::parse($mulai)->subDay()->format('d-m-Y') }}
                                </td>
                                <td></td>
                                <td>Saldo Awal</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right">{{ number_format($saldo, 0, ',', '.') }}</td>
                            </tr>

                            @foreach ($data as $row)
                                @if($row->jenis == 'pajak_setor') @continue @endif
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                                    <td>
                                        @if($row->jenis == 'sp2d')
                                            {{ $row->no_bukti }}
                                        @elseif($row->jenis == 'nihil')
                                            Nihil-{{ $row->no_bukti }}
                                        @else
                                            TBP-{{ str_pad($row->no_bukti, 4, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </td>
                                    <td>{{ $row->rekening ?? '-' }}</td>
                                    <td>
                                        {{ $row->uraian }}
                                        @if($row->jenis == 'nihil')
                                            <span class="badge badge-dark ml-1" style="font-size: 9px;">Setor Kasda</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $row->debet ? number_format((float) $row->debet, 0, ',', '.') : '' }}</td>
                                    <td class="text-right">{{ $row->kredit ? number_format((float) $row->kredit, 0, ',', '.') : '' }}</td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($totalSaldo = $totalSaldo + ((int) ($row->debet ?? 0)) - ((int) ($row->kredit ?? 0)), 0, ',', '.') }}
                                    </td>
                                </tr>

                                {{-- Pajak dipungut & disetor (hanya untuk jenis belanja) --}}
                                @if($row->jenis == 'belanja')
                                    @foreach ($row->pajakTu as $pajak)
                                        {{-- Pajak Dipungut (debet) --}}
                                        <tr style="background-color: #f0fdf4;">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="font-italic text-success">Di Pungut {{ $pajak->jenis_pajak }} <small class="text-muted">(ID Billing: {{ $pajak->no_billing ?? '-' }})</small></td>
                                            <td class="text-right">
                                                {{ number_format((float) ($pajak->nominal ?? 0), 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                            <td class="text-right text-muted">
                                                {{ number_format($totalSaldo = $totalSaldo + ((int) ($pajak->nominal ?? 0)), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        {{-- Pajak Disetor (kredit) --}}
                                        <tr style="background-color: #fef2f2;">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="font-italic text-danger">Di Setor {{ $pajak->jenis_pajak }} <small class="text-muted">(NTPN: {{ $pajak->ntpn ?? '-' }} | NTB: {{ $pajak->ntb ?? '-' }})</small></td>
                                            <td></td>
                                            <td class="text-right">
                                                {{ number_format((float) ($pajak->nominal ?? 0), 0, ',', '.') }}
                                            </td>
                                            <td class="text-right text-muted">
                                                {{ number_format($totalSaldo = $totalSaldo - ((int) ($pajak->nominal ?? 0)), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printBkuTu() {
            var printContent = document.getElementById('print-area-tu').innerHTML;
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>BKU TU</title>
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.css') }}">
                    <style>
                        body { margin: 0; padding: 20px; font-family: 'Source Sans Pro', sans-serif; }
                        .table th, .table td { font-size: 12px; }
                        @page { size: 330mm 210mm; margin: 10mm; }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>setTimeout(function(){ window.print(); window.close(); }, 500);<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endpush
