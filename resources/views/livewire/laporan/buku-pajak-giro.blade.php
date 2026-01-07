@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Buku Pembantu Pajak (GU)</h3>
            <p class="page-subtitle mb-0">Laporan pemotongan dan penyetoran pajak GU</p>
        </div>

        <div class="content-card">
            <div class="row align-items-end mb-4">
                <div class="col-md-3">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Awal</label>
                    <input type="date" wire:model.live="tanggal_awal" class="form-control" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Akhir</label>
                    <input type="date" wire:model.live="tanggal_akhir" class="form-control" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-secondary small mb-2">Jenis Pajak</label>
                    <select wire:model.live="jenis" class="form-control custom-select-modern">
                        <option value="ALL">Semua</option>
                        <option value="PPN">PPN</option>
                        <option value="PPh 21">PPh 21</option>
                        <option value="PPh 22">PPh 22</option>
                        <option value="PPh 23">PPh 23</option>
                    </select>
                </div>
                <div class="col-md-3 text-right">
                    @if ($tanggal_awal && $tanggal_akhir)
                        <button onclick="printLaporan()" class="btn btn-modern-add">
                            <i class="fas fa-print mr-2"></i> Cetak Laporan
                        </button>
                    @endif
                </div>
            </div>

            @if ($tanggal_awal && $tanggal_akhir)
                <div class="bg-white p-4 border rounded" id="print-area">
                    <div class="text-center mb-4">
                        <h4 class="font-weight-bold mb-1">PEMERINTAH KABUPATEN WONOSOBO</h4>
                        <h3 class="font-weight-bold mb-1">BUKU PEMBANTU PAJAK (GU)</h3>
                        @if ($jenis === 'PPN')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPN</h5>
                        @elseif ($jenis === 'PPh 21')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 21</h5>
                        @elseif ($jenis === 'PPh 22')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 22</h5>
                        @elseif ($jenis === 'PPh 23')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 23</h5>
                        @else
                            <h5 class="font-weight-bold mb-1">Semua Jenis Pajak</h5>
                        @endif
                        <p class="mb-0">
                            Periode {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }} s/d
                            {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm modern-table" style="font-size: 12px; border: 1px solid #dee2e6;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 100px;">Tgl Bukti</th>
                                    <th style="width: 150px;">No Bukti</th>
                                    <th>Uraian</th>
                                    <th style="width: 130px;">Pemotongan</th>
                                    <th style="width: 130px;">Penyetoran</th>
                                    <th style="width: 130px;">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $runningSaldo = $saldoAwal;
                                @endphp
                                <tr class="bg-light font-weight-bold">
                                    <td></td>
                                    <td></td>
                                    <td>Saldo Awal</td>
                                    <td class="text-right">
                                        {{ $saldoAwalPemotongan > 0 ? number_format($saldoAwalPemotongan, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $saldoAwalPenyetoran > 0 ? number_format($saldoAwalPenyetoran, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="text-right">0</td>
                                </tr>
                                @if (count($laporan) > 0)
                                    @foreach ($laporan as $item)
                                        @php
                                            $pemotongan = $item->pemotongan ?? 0;
                                            $penyetoran = $item->penyetoran ?? 0;
                                            $runningSaldo += $pemotongan - $penyetoran;
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($item->tgl_bukti)->format('d-m-Y') }}</td>
                                            <td class="text-center">{{ $item->no_bukti }}</td>
                                            <td>{{ $item->uraian }}</td>
                                            <td class="text-right">
                                                {{ $pemotongan > 0 ? number_format($pemotongan, 0, ',', '.') : '' }}</td>
                                            <td class="text-right">
                                                {{ $penyetoran > 0 ? number_format($penyetoran, 0, ',', '.') : '' }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Data Tidak Ada</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="3" class="text-right">TOTAL</td>
                                    <td class="text-right">
                                        {{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($saldoAwalPenyetoran + collect($laporan)->sum('penyetoran'), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">{{ number_format($runningSaldo, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4 row">
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered modern-table" style="font-size: 12px;">
                                <tbody>
                                    <tr>
                                        <td class="bg-light font-weight-bold">Jumlah Pajak Periode Sebelumnya</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($saldoAwalPemotongan, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light font-weight-bold">Jumlah Pajak Periode Ini</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($pajakPeriodeIni, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    @if ($jenis === 'ALL' || $jenis === 'PPN')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPN</td>
                                            <td class="text-right">{{ number_format($ppnTotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 21')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 21</td>
                                            <td class="text-right">{{ number_format($pph21Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 22')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 22</td>
                                            <td class="text-right">{{ number_format($pph22Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 23')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 23</td>
                                            <td class="text-right">{{ number_format($pph23Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-secondary text-white">
                                        <td class="font-weight-bold">Total Pajak Sampai Periode Ini</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Silakan pilih range tanggal untuk menampilkan laporan.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('js')
    <script>
        function printLaporan() {
            var content = document.getElementById('print-area').innerHTML;
            var myWindow = window.open('', '', 'width=900,height=600');
            
            myWindow.document.write('<html><head><title>Cetak Laporan Pajak</title>');
            myWindow.document.write('<style>');
            myWindow.document.write(`
                body { font-family: Arial, sans-serif; margin: 20px; color: #000; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 4px 6px; text-align: left; vertical-align: top; }
                th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                h2, h3, h4, h5 { margin: 5px 0; text-align: center; }
                p { margin: 5px 0; text-align: center; font-size: 12px; }
                .no-print { display: none; }
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
