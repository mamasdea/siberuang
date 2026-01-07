<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pajak Gabungan</title>
    <style>
        @page {
            margin: 15mm 15mm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h4 {
            font-size: 12px;
            margin: 3px 0;
            font-weight: bold;
        }

        .header h3 {
            font-size: 11px;
            margin: 3px 0;
            font-weight: bold;
        }

        .header h5 {
            font-size: 10px;
            margin: 3px 0;
            font-weight: normal;
        }

        .header p {
            font-size: 9px;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #333;
        }

        th, td {
            padding: 6px 8px;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .summary-table {
            width: 60%;
            font-size: 8px;
        }

        .signature-section {
            width: 100%;
            margin-top: 30px;
            border: none;
        }

        .signature-section td {
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .signature-section p {
            margin: 3px 0;
            font-size: 9px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h4>PEMERINTAH KABUPATEN WONOSOBO</h4>
        <h3>BUKU PEMBANTU PAJAK (GABUNGAN)</h3>
        @if ($jenis === 'PPN')
            <h5>Per Jenis Pajak : PPN</h5>
        @elseif ($jenis === 'PPh 21')
            <h5>Per Jenis Pajak : PPh 21</h5>
        @elseif ($jenis === 'PPh 22')
            <h5>Per Jenis Pajak : PPh 22</h5>
        @elseif ($jenis === 'PPh 23')
            <h5>Per Jenis Pajak : PPh 23</h5>
        @elseif ($jenis === 'Pajak Restoran')
            <h5>Per Jenis Pajak : Pajak Restoran</h5>
        @else
            <h5>Semua Jenis Pajak</h5>
        @endif
        <p>Periode {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- Main Table -->
    <table>
        <thead>
            <tr class="bg-light">
                <th width="5%">NO</th>
                <th width="10%">TGL BUKTI</th>
                <th width="15%">NO BUKTI</th>
                <th>URAIAN</th>
                <th width="15%">PEMOTONGAN</th>
                <th width="15%">PENYETORAN</th>
                <th width="15%">SALDO</th>
            </tr>
        </thead>
        <tbody>
            <!-- Saldo Awal Row -->
            <tr class="bg-light font-weight-bold">
                <td colspan="4" class="text-right">Saldo Awal</td>
                <td class="text-right">Rp {{ number_format($saldoAwalPemotongan, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($saldoAwalPenyetoran, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($saldoAwal, 2, ',', '.') }}</td>
            </tr>

            @php
                $currentSaldo = $saldoAwal;
                $totalPemotongan = 0;
                $totalPenyetoran = 0;
            @endphp

            @foreach ($laporan as $index => $row)
                @php
                    $currentSaldo = $currentSaldo + ($row->pemotongan ?? 0) - ($row->penyetoran ?? 0);
                    $totalPemotongan += $row->pemotongan ?? 0;
                    $totalPenyetoran += $row->penyetoran ?? 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->tgl_bukti }}</td>
                    <td>{{ $row->no_bukti }}</td>
                    <td>{{ $row->uraian }}</td>
                    <td class="text-right">
                        @if ($row->pemotongan > 0)
                            Rp {{ number_format($row->pemotongan, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if ($row->penyetoran > 0)
                            Rp {{ number_format($row->penyetoran, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-weight-bold">
                        Rp {{ number_format($currentSaldo, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <!-- Footer Totals -->
            <tr class="bg-light font-weight-bold">
                <td colspan="4" class="text-right">JUMLAH PERIODE INI</td>
                <td class="text-right">Rp {{ number_format($totalPemotongan, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalPenyetoran, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($saldoAkhir, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Summary Table -->
    <table class="summary-table">
        <tbody>
            <tr>
                <td class="bg-light font-weight-bold">Jumlah Pajak Periode Sebelumnya</td>
                <td class="text-right font-weight-bold">{{ number_format($saldoAwalPemotongan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bg-light font-weight-bold">Jumlah Pajak Periode Ini</td>
                <td class="text-right font-weight-bold">{{ number_format($pajakPeriodeIni, 0, ',', '.') }}</td>
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
            <tr style="background-color: #6c757d; color: white;">
                <td class="font-weight-bold">Total Pajak Sampai Periode Ini</td>
                <td class="text-right font-weight-bold">
                    {{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Section -->
    <table class="signature-section">
        <tr>
            <td style="width: 50%;">
                <p>Mengetahui,</p>
                <p>PENGGUNA ANGGARAN</p>
                <br><br><br><br><br>
                <p class="signature-name">
                    {{ $penggunaAnggaran->nama ?? '..........................' }}
                </p>
                <p>NIP. {{ $penggunaAnggaran->nip ?? '..........................' }}</p>
            </td>
            <td style="width: 50%;">
                <p>Wonosobo, {{ \Carbon\Carbon::parse($tanggal_cetak)->translatedFormat('d F Y') }}</p>
                <p>BENDAHARA PENGELUARAN</p>
                <br><br><br><br><br>
                <p class="signature-name">
                    {{ $bendahara->nama ?? '..........................' }}
                </p>
                <p>NIP. {{ $bendahara->nip ?? '..........................' }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
