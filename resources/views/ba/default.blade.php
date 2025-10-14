<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --text: #111;
            --muted: #666;
            --border: #ccc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans";
            color: var(--text);
            margin: 0;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background: #fff;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 .5rem;
        }

        .t-center {
            text-align: center;
        }

        .muted {
            color: var(--muted);
        }

        .mb-1 {
            margin-bottom: .25rem;
        }

        .mb-2 {
            margin-bottom: .5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .row {
            display: flex;
            gap: 12px;
        }

        .col {
            flex: 1 1 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 6px 8px;
        }

        th {
            background: #f7f7f7;
        }

        .no-border td,
        .no-border th {
            border: 0;
        }

        .right {
            text-align: right;
        }

        .print-btn {
            position: fixed;
            right: 16px;
            top: 16px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                background: #fff;
            }

            .sheet {
                margin: 0;
                padding: 15mm;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">Cetak</button>

    <div class="sheet">
        <div class="t-center mb-3">
            <h2>{{ strtoupper($title) }}</h2>
            <div class="muted">Jenis: {{ $jenis }} &middot; Tanggal cetak: {{ $fmtDate($today) }}</div>
        </div>

        {{-- Header BA (nomor & tanggal BA) --}}
        <table class="no-border mb-3">
            <tr>
                <td style="width:40%"><strong>Nomor BA</strong></td>
                <td>{{ $ba_nomor ?: '—' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal BA</strong></td>
                <td>{{ $fmtDate($ba_tanggal) }}</td>
            </tr>
        </table>

        {{-- Info Kontrak --}}
        <h3 class="mb-1">Informasi Kontrak</h3>
        <table class="no-border mb-3">
            <tr>
                <td style="width:40%"><strong>Nomor Kontrak</strong></td>
                <td>{{ $kontrak->nomor_kontrak }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Kontrak</strong></td>
                <td>{{ $fmtDate($kontrak->tanggal_kontrak) }}</td>
            </tr>
            <tr>
                <td><strong>Perusahaan</strong></td>
                <td>{{ $kontrak->nama_perusahaan }} ({{ $kontrak->bentuk_perusahaan ?: '-' }})</td>
            </tr>
            <tr>
                <td><strong>Sub Kegiatan</strong></td>
                <td>{{ $kontrak->subKegiatan->kode ?? '-' }} — {{ $kontrak->subKegiatan->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Nilai Kontrak</strong></td>
                <td>{{ $idr($kontrak->nilai) }}</td>
            </tr>
        </table>

        {{-- Info Realisasi --}}
        <h3 class="mb-1">Informasi Realisasi</h3>
        <table class="no-border mb-3">
            <tr>
                <td style="width:40%"><strong>Tanggal Realisasi</strong></td>
                <td>{{ $fmtDate($realisasi->tanggal) }}</td>
            </tr>
            <tr>
                <td><strong>Jenis Realisasi</strong></td>
                <td class="text-uppercase">{{ $realisasi->tipe }}</td>
            </tr>
            @if ($realisasi->tipe === 'termin' && $realisasi->termin_ke)
                <tr>
                    <td><strong>Termin</strong></td>
                    <td>Termin {{ $roman((int) $realisasi->termin_ke) }} ({{ $realisasi->termin_ke }})</td>
                </tr>
            @endif
            <tr>
                <td><strong>Periode</strong></td>
                <td>{{ $realisasi->periode ?: '—' }}</td>
            </tr>
            <tr>
                <td><strong>Progres Fisik</strong></td>
                <td>{{ is_null($realisasi->progres_fisik) ? '—' : number_format($realisasi->progres_fisik, 2, ',', '.') . ' %' }}
                </td>
            </tr>
            <tr>
                <td><strong>Nominal Realisasi</strong></td>
                <td>{{ $idr($realisasi->nominal) }}</td>
            </tr>
        </table>

        {{-- Rincian item realisasi (jika ada) --}}
        <h3 class="mb-1">Rincian Barang/Jasa</h3>
        <table class="mb-3">
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Nama Barang/Jasa</th>
                    <th class="right" style="width:90px">Qty</th>
                    <th class="right" style="width:90px">Satuan</th>
                    <th class="right" style="width:110px">Harga</th>
                    <th class="right" style="width:130px">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $gt=0; @endphp
                @forelse($realisasi->items as $i => $it)
                    @php
                        $line = (float) $it->kuantitas * (float) $it->harga;
                        $gt += $line;
                    @endphp
                    <tr>
                        <td class="right">{{ $i + 1 }}</td>
                        <td>{{ $it->nama_barang }}</td>
                        <td class="right">{{ number_format($it->kuantitas, 2, ',', '.') }}</td>
                        <td class="right">{{ $it->satuan }}</td>
                        <td class="right">{{ $idr($it->harga) }}</td>
                        <td class="right">{{ $idr($line) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="t-center muted">Tidak ada rincian.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="right">Total</th>
                    <th class="right">{{ $idr($gt) }}</th>
                </tr>
            </tfoot>
        </table>

        {{-- Tanda tangan (sesuaikan kebutuhan) --}}
        <div class="row" style="margin-top:40px">
            <div class="col t-center">
                <div class="mb-2">Pejabat Pembuat Komitmen</div>
                <div style="height:70px"></div>
                <strong>( ______________________ )</strong>
            </div>
            <div class="col t-center">
                <div class="mb-2">Penyedia</div>
                <div style="height:70px"></div>
                <strong>( ______________________ )</strong>
            </div>
        </div>
    </div>

    @if ($auto ?? false)
        <script>
            window.addEventListener('load', () => setTimeout(() => window.print(), 250));
        </script>
    @endif
</body>

</html>
