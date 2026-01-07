@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Buku Kas Pengeluaran / GU Giro</h3>
            <p class="page-subtitle mb-0">Laporan transaksi harian GU Giro</p>
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
                    <button class="btn btn-modern-add" onclick="printDiv('print-area')">
                        <i class="fas fa-print mr-2"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="bg-white p-4 border rounded" id="print-area">
                <div class="text-center mb-4">
                    <h3 class="font-weight-bold" style="font-size: 18px; margin-bottom: 5px;">BUKU KAS PENGELUARAN</h3>
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
                            @php
                                $totalSaldo = $saldo;
                            @endphp
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
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                                    <td>
                                        {{ Str::startsWith($row->no_bukti, ['UP-', 'GU-'])
                                            ? $row->no_bukti
                                            : 'TBP-' . str_pad($row->no_bukti, 4, '0', STR_PAD_LEFT) . '/Diskominfo/2025' }}
                                    </td>

                                    <td>{{ $row->rekening ?? '-' }}</td>
                                    <td>{{ $row->uraian }}</td>
                                    <td class="text-right">{{ number_format((float) ($row->debet ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">{{ number_format((float) ($row->kredit ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($totalSaldo = $totalSaldo + ((int) $row->debet ?? 0) - ((int) $row->kredit ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                                @foreach ($row->pajak as $asu)
                                    <tr style="background-color: #f8fafc;">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="font-italic text-secondary">Di Pungut {{ $asu->jenis_pajak }}</td>
                                        <td class="text-right">
                                            {{ number_format((float) ($asu->nominal ?? 0), 0, ',', '.') }}
                                        </td>
                                        <td></td>
                                        <td class="text-right text-muted">
                                            {{ number_format($totalSaldo = $totalSaldo + ((int) $asu->nominal ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f8fafc;">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="font-italic text-secondary">Di Setor {{ $asu->jenis_pajak }}</td>
                                        <td></td>
                                        <td class="text-right">
                                            {{ number_format((float) ($asu->nominal ?? 0), 0, ',', '.') }}
                                        </td>
                                        <td class="text-right text-muted">
                                            {{ number_format($totalSaldo = $totalSaldo - ((int) $asu->nominal ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="6" class="text-center font-weight-bold">SALDO AKHIR</td>
                                <td class="text-right font-weight-bold" style="font-size: 14px;">{{ number_format($totalSaldo, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printDiv(divId) {
            var content = document.getElementById(divId).innerHTML;
            var myWindow = window.open('', '', 'width=900,height=600');

            myWindow.document.write('<html><head><title>Cetak Laporan BKU</title>');
            myWindow.document.write('<style>');
            myWindow.document.write(`
                body { font-family: Arial, sans-serif; margin: 20px; color: #000; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 4px 6px; text-align: left; vertical-align: top; }
                th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                h3 { margin-bottom: 5px; text-align: center; font-size: 16px; text-transform: uppercase; }
                p { margin: 2px 0; text-align: center; font-size: 12px; }
            `);
            myWindow.document.write('</style></head><body>');
            myWindow.document.write(content);
            myWindow.document.write('</body></html>');

            myWindow.document.close();
            myWindow.focus();
            setTimeout(function() {
                myWindow.print();
                myWindow.close();
            }, 500);
        }
    </script>
@endpush
