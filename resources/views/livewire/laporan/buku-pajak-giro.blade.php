<div>
    {{-- Pilihan Bulan --}}
    <div class="col-lg-4 form-group mt-3 row">
        <label for="bulan" class="col-sm-4 col-form-label">Pilih Bulan</label>
        <div class="col-sm-8">
            <select wire:model.live="bulan" class="form-control form-control-sm">
                <option value="">Pilih Bulan</option>
                @foreach (range(1, 12) as $m)
                    <option value="{{ sprintf('%02d', $m) }}">
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tampilkan laporan hanya jika bulan dipilih dan data tersedia --}}
    @if ($bulan && count($laporan) > 0)
        <div class="bg-white p-2 mt-3" id="laporan-container">
            {{-- Header Laporan --}}
            <div id="print-area">
                <h2 class="text-center">PEMERINTAH KABUPATEN WONOSOBO</h2>
                <h3 class="text-center">BUKU PEMBANTU PAJAK</h3>
                <h4 class="text-center">Bendahara Pengeluaran</h4>
                <h5 class="text-center">
                    Periode {{ \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F Y') }}
                </h5>

                <table border="1" width="100%" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">Tgl Bukti</th>
                            <th style="width: 15%; text-align: center;">No Bukti</th>
                            <th style="width: 40%; text-align: center;">Uraian</th>
                            <th style="width: 15%; text-align: center;">Pemotongan</th>
                            <th style="width: 15%; text-align: center;">Penyetoran</th>
                            <th style="width: 15%; text-align: center;">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningSaldo = 0;
                        @endphp
                        <tr>
                            <td></td>
                            <td></td>
                            <td><strong>Saldo Awal</strong></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                        </tr>
                        @foreach ($laporan as $item)
                            @php
                                // Jika nilai null, diubah menjadi 0
                                $pemotongan = $item->pemotongan ?? 0;
                                $penyetoran = $item->penyetoran ?? 0;
                                $runningSaldo += $penyetoran - $pemotongan;
                            @endphp
                            <tr>
                                <td style="text-align: center;">
                                    {{ \Carbon\Carbon::parse($item->tgl_bukti)->format('d-m-Y') }}</td>
                                <td style="text-align: center;">{{ $item->no_bukti }}</td>
                                <td>{{ $item->uraian }}</td>
                                <td class="text-right">{{ number_format($pemotongan, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($penyetoran, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format(collect($laporan)->sum('pemotongan'), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format(collect($laporan)->sum('penyetoran'), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Tampilkan jumlah masing-masing pajak dan total Pajak Bulan Ini --}}
                <div class="mt-3">
                    <table class="table table-sm table-border" style="width: 30%;">
                        <tbody>
                            <tr>
                                <td><strong>Jumlah Pajak Bulan Lalu</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($pajakBulanLalu, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Pajak Bulan Ini</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($pajakBulanIni, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px;">- Pajak PPN</td>
                                <td class="text-right">{{ number_format($ppnTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px;">- PPh 21</td>
                                <td class="text-right">{{ number_format($pph21Total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px;">- PPh 22</td>
                                <td class="text-right">{{ number_format($pph22Total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 20px;">- PPh 23</td>
                                <td class="text-right">{{ number_format($pph23Total, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Pajak Sampai Bulan Lalu</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($totalpajak, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Tombol Cetak --}}
            <div class="text-center" style="margin-top: 20px;">
                <button onclick="printLaporan()">🖨️ Cetak Laporan</button>
            </div>
        </div>
    @endif

    {{-- Script untuk Print --}}
    <script>
        function printLaporan() {
            var printContent = document.getElementById("print-area").innerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</div>
