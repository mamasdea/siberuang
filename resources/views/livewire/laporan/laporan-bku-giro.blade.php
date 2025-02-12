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

    {{-- Tampilkan laporan hanya jika bulan dipilih dan ada data --}}
    @if ($bulan)
        @if (count($laporan) > 0)
            <div class="bg-white p-2 mt-3" id="laporan-container">
                {{-- Header Laporan --}}
                <div id="print-area">
                    <h2 class="text-center">BUKU KAS UMUM (BKU) GIRO</h2>
                    <h3 class="text-center">Dinas Komunikasi dan Informatika</h3>
                    <h4 class="text-center">
                        Bulan: {{ \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F Y') }}
                    </h4>

                    <table border="1" width="100%" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 10%; text-align: center;">Tanggal</th>
                                <th style="width: 10%; text-align: center;">No Bukti</th>
                                <th style="width: 15%; text-align: center;">Rekening</th>
                                <th style="width: 35%; text-align: center;">Uraian</th>
                                <th style="width: 15%; text-align: right;">Nominal</th>
                                <th style="width: 15%; text-align: right;">Pajak</th>
                            </tr>
                        </thead>


                        <tbody>
                            @foreach ($laporan as $item)
                                <tr>
                                    <td style="text-align: center;">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                    <td style="text-align: center;">{{ $item->no_bukti }}</td>
                                    <td style="text-align: center;">{{ $item->rka->kode_belanja }}</td>
                                    <td>{{ $item->uraian }}</td>
                                    <td class="text-right">{{ number_format($item->nilai, 0, ',', '.') }}</td>
                                    <td class="text-right">-</td>

                                </tr>

                                {{-- Tambahkan baris baru jika ada pajak --}}
                                @if ($item->pajak->isNotEmpty())
                                    @foreach ($item->pajak as $pajak)
                                        <tr>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td colspan="2">
                                                {{ 'Pajak :' . $pajak->jenis_pajak . ' - ' . $pajak->no_billing }}</td>
                                            <td class="text-right">
                                                {{ number_format($pajak->nominal, 0, ',', '.') }}
                                            </td>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>

                        @php
                            $totalNominal = $laporan->sum('nilai');
                            $totalPajak = $laporan->sum(fn($item) => $item->pajak->sum('nominal'));
                        @endphp

                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalNominal, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-right"><strong>{{ number_format($totalPajak, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tfoot>

                        {{-- <tbody>
                            @php
                                $totalNominal = 0;
                                $totalPajak = 0;
                                $lastBukti = null;
                            @endphp
                            @foreach ($laporan as $item)
                                @if ($item->no_bukti !== null)
                                    <tr>
                                        <td style="text-align: center;">
                                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                        <td style="text-align: center;">{{ $item->no_bukti }}</td>
                                        <td style="text-align: center;">{{ $item->rekening }}</td>
                                        <td>{{ $item->uraian }}</td>
                                        <td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                        <td class="text-right">-</td>
                                    </tr>
                                    @php
                                        $totalNominal += $item->nominal;
                                        $lastBukti = $item->belanja_id;
                                    @endphp
                                @endif

                                @if ($item->jenis_pajak && $lastBukti === $item->belanja_id)
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $item->uraian }} - <strong>Billing:</strong> {{ $item->no_billing }}
                                        </td>
                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ number_format($item->pajak_nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @php
                                        $totalPajak += $item->pajak_nominal;
                                    @endphp
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalNominal, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-right"><strong>{{ number_format($totalPajak, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tfoot> --}}
                    </table>
                </div>

                {{-- Tombol Cetak --}}
                <div class="text-center" style="margin-top: 20px;">
                    <button onclick="printLaporan()">🖨️ Cetak Laporan</button>
                </div>
            </div>
        @else
            <p class="text-center text-danger mt-3">❌ Tidak ada data untuk bulan yang dipilih.</p>
        @endif
    @endif

    {{-- Script untuk Print --}}
    <script>
        function printLaporan() {
            var printContent = document.getElementById("print-area").innerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload(); // Reload halaman agar tidak terganggu setelah print
        }
    </script>
</div>
