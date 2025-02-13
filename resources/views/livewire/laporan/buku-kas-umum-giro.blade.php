<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Buku Kas Pengeluaran / GU Giro</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="row align-items-center">
                    <div class="col-md-4 text-end">
                        <label class="form-label mb-0">Pilih Range Tanggal:</label>
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" wire:model.live="mulai" placeholder="Tanggal Mulai">
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" wire:model.live="end" placeholder="Tanggal Akhir">
                    </div>
                </div>
            </div>
        </div>



        <button class="btn btn-primary mb-3" onclick="printDiv('print-area')">Cetak</button>
    </div>

    <div class="bg-white p-4">
        <div id="print-area" class="p-2"> <!-- ✅ PERBAIKI ID di sini -->
            <div class="text-center">
                <h3 class="font-weight-bold">BUKU KAS PENGELUARAN</h3>
                <p class="mb-1">Dinas Komunikasi dan Informatika Kabupaten Wonosobo</p>
                <p class="mb-0">Tanggal:
                    <strong>
                        {{ \Carbon\Carbon::parse($mulai)->translatedFormat('d F Y') }} -
                        {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
                    </strong>
                </p>
            </div>

            <table class="table table-bordered mt-3">
                <thead style="text-align: center">
                    <tr>
                        <th width="100">Tanggal</th>
                        <th width="175">No Bukti</th>
                        <th width="100">Rekening</th>
                        <th width="400">Uraian</th>
                        <th width="100">Debet</th>
                        <th width="100">Kredit</th>
                        <th width="100">Saldo Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSaldo = $saldo;
                    @endphp
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($mulai)->format('m-d') == '01-01' ? \Carbon\Carbon::parse($mulai)->format('d-m-Y') : \Carbon\Carbon::parse($mulai)->subDay()->format('d-m-Y') }}
                        </td>
                        <td></td>
                        <td>Saldo Awal</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right">{{ number_format($saldo, 0, ',', '.') }}</td>
                    </tr>
                    @foreach ($data as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                            <td>
                                {{ Str::startsWith($row->no_bukti, 'UP-') ? $row->no_bukti : 'TBP-' . str_pad($row->no_bukti, 4, '0', STR_PAD_LEFT) . '/Diskominfo/2025' }}
                            </td>
                            <td>{{ $row->rekening ?? '-' }}</td>
                            <td>{{ $row->uraian }}</td>
                            <td style="text-align: right">{{ number_format((float) ($row->debet ?? 0), 0, ',', '.') }}
                            </td>
                            <td style="text-align: right">{{ number_format((float) ($row->kredit ?? 0), 0, ',', '.') }}
                            </td>
                            <td style="text-align: right">
                                {{ number_format($totalSaldo = $totalSaldo + ((int) $row->debet ?? 0) - ((int) $row->kredit ?? 0), 0, ',', '.') }}
                            </td>
                        </tr>
                        @foreach ($row->pajak as $asu)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Di Pungut {{ $asu->jenis_pajak }}</td>
                                <td style="text-align: right">
                                    {{ number_format((float) ($asu->nominal ?? 0), 0, ',', '.') }}
                                </td>
                                <td></td>
                                <td style="text-align: right">
                                    {{ number_format($totalSaldo = $totalSaldo + ((int) $asu->nominal ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Di Setor {{ $asu->jenis_pajak }}</td>
                                <td></td>
                                <td style="text-align: right">
                                    {{ number_format((float) ($asu->nominal ?? 0), 0, ',', '.') }}
                                </td>
                                <td style="text-align: right">
                                    {{ number_format($totalSaldo = $totalSaldo - ((int) $asu->nominal ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-center bold"><strong>Saldo Akhir</strong></td>
                        <td style="text-align: right"><strong>{{ number_format($totalSaldo, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printDiv(divId) {
            var content = document.getElementById(divId).innerHTML;
            var myWindow = window.open('', '', 'width=900,height=600');

            myWindow.document.write('<html><head><title>Cetak</title>');
            myWindow.document.write('<style>');
            myWindow.document.write(`
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 8px; text-align: left; }
                h3, p { text-align: center; }
                @media print {
                    button { display: none; } /* Sembunyikan tombol saat dicetak */
                }
            `);
            myWindow.document.write('</style></head><body>');
            myWindow.document.write(content);
            myWindow.document.write('</body></html>');

            myWindow.document.close();
            myWindow.focus();
            myWindow.print();
            myWindow.close();
        }
    </script>
@endpush
