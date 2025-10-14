{{-- resources/views/ba/penerimaan.blade.php --}}
@php
    use Carbon\Carbon;

    // ===== Helpers =====
    if (!function_exists('terbilang_id')) {
        function terbilang_id($n): string
        {
            $n = (int) $n;
            $w = [
                '',
                'satu',
                'dua',
                'tiga',
                'empat',
                'lima',
                'enam',
                'tujuh',
                'delapan',
                'sembilan',
                'sepuluh',
                'sebelas',
            ];
            if ($n < 12) {
                return $w[$n];
            }
            if ($n < 20) {
                return terbilang_id($n - 10) . ' belas';
            }
            if ($n < 100) {
                return terbilang_id(intval($n / 10)) . ' puluh' . ($n % 10 ? ' ' . terbilang_id($n % 10) : '');
            }
            if ($n < 200) {
                return 'seratus' . ($n > 100 ? ' ' . terbilang_id($n - 100) : '');
            }
            if ($n < 1000) {
                return terbilang_id(intval($n / 100)) . ' ratus' . ($n % 100 ? ' ' . terbilang_id($n % 100) : '');
            }
            if ($n < 2000) {
                return 'seribu' . ($n > 1000 ? ' ' . terbilang_id($n - 1000) : '');
            }
            if ($n < 1000000) {
                return terbilang_id(intval($n / 1000)) . ' ribu' . ($n % 1000 ? ' ' . terbilang_id($n % 1000) : '');
            }
            if ($n < 1000000000) {
                return terbilang_id(intval($n / 1000000)) .
                    ' juta' .
                    ($n % 1000000 ? ' ' . terbilang_id($n % 1000000) : '');
            }
            return (string) $n;
        }
    }
    if (!function_exists('idr0')) {
        // 2 desimal seperti contoh
        function idr0($v)
        {
            return 'Rp ' . number_format((float) $v, 2, ',', '.');
        }
    }
    Carbon::setLocale('id');

    // ===== Sumber Data Utama =====
    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : null);
    $hariText = $tglObj?->translatedFormat('l') ?? '........';
    $hariHuruf = $tglObj ? ucfirst(terbilang_id((int) $tglObj->format('j'))) : '........';
    $bulanNama = $tglObj?->translatedFormat('F') ?? '........';
    $tahunHuruf = $tglObj ? ucwords(terbilang_id((int) $tglObj->format('Y'))) : '........';
    $tglText = $tglObj?->translatedFormat('d F Y') ?? '........';

    $nomorBA = $ba_nomor ?? '-';

    // BA Pemeriksaan acuan
    $noPeriksa =
        optional($realisasi->beritaAcaras->firstWhere('jenis', 'pemeriksaan'))->nomor ?? ($ba_pemeriksaan_nomor ?? '-');
    $tglPeriksa =
        optional($realisasi->beritaAcaras->firstWhere('jenis', 'pemeriksaan'))->tanggal ??
        ($ba_pemeriksaan_tanggal ?? $tglObj?->toDateString());
    $tglPeriksaT = $tglPeriksa ? Carbon::parse($tglPeriksa)->translatedFormat('j F Y') : '-';

    // Kontrak & Penyedia
    $perusahaan = $kontrak->nama_perusahaan ?? '-';
    $pimpinan = $kontrak->nama_pimpinan ?? '-';
    $alamatPerus = $kontrak->alamat_perusahaan ?? '';
    $keperluan = $kontrak->uraian ?? '-';
    if (($realisasi->tipe ?? '') === 'termin' && !empty($realisasi->periode)) {
        $keperluan .= ' bulan ' . $realisasi->periode;
    }

    // Pemegang Barang (yang menerima)
    $pemegangNama = $namaPB ?? 'DEWANGGA TOMI YULIANTARA';
    $pemegangNip = $nipPB ?? '19950720 202521 1 022';

    // Mengetahui (Kepala Dinas)
    $kadisNama = $namaPA ?? 'KHRISTIANA DHEWI, SE., MM.';
    $kadisNip = $nipPA ?? '19741130 199903 2 005';

    // Keputusan Bupati (boleh dipecah jika mau diisi per-bagian)
    $kepNo = $kep_bupati_nomor ?? ''; // mis. "050/123/2025"
    $kepTgl = $kep_bupati_tanggal ?? ''; // opsional

    // Hitung Total & PPN
    $items = $realisasi->items ?? collect();
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += (float) $it->harga * (float) $it->kuantitas;
    }

    $show_ppn = isset($show_ppn) ? (bool) $show_ppn : true; // set false jika ingin tanpa PPN
    $ppn = $show_ppn ? round($subtotal * 0.11, 2) : 0;
    $grand = $subtotal + $ppn;
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Berita Acara Penerimaan</title>
    <style>
        @page {
            size: A4;
            margin: 1.8mm 18mm 18mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.0;
            color: #000;
        }

        .center {
            text-align: center
        }

        .right {
            text-align: right
        }

        .justify {
            text-align: justify
        }

        .kop {
            display: grid;
            grid-template-columns: 90px 1fr;
            column-gap: 14px;
            align-items: center;
        }

        .kop img {
            width: 90px;
            height: auto
        }

        .kop h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-align: center
        }

        .kop h2 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            text-align: center
        }

        .kop .meta {
            font-size: 11pt;
            text-align: center;
            line-height: 1.2
        }

        .strip {
            grid-column: 1/-1;
            border-bottom: 2px solid #000;
            margin: 8px 0 12px
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .mt-1 {
            margin-top: .4rem
        }

        .mt-2 {
            margin-top: .7rem
        }

        .mt-3 {
            margin-top: 1rem
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        .data td {
            padding: 0;
            border: none;
            vertical-align: top;
        }

        .data .lbl {
            width: 115px
        }

        .data .sep {
            width: 10px
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px
        }

        .table th {
            background: #f4f4f4;
            text-align: center
        }

        .ttd td {
            text-align: center;
            vertical-align: top;
            padding-top: 18px
        }

        .blank {
            display: block;
            height: 70px
        }

        .sign {
            font-weight: bold;
            text-decoration: underline
        }
    </style>
</head>

<body>

    {{-- KOP --}}
    <div class="kop">
        <div class="logo center"><img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo"></div>
        <div>
            <h1>PEMERINTAH KABUPATEN WONOSOBO</h1>
            <h2>DINAS KOMUNIKASI DAN INFORMATIKA</h2>
            <div class="meta">
                Jl. Sabuk Alu No.2 A Telp. (0286) 325112 / Fax 325112<br>
                Website: diskominfo.wonosobokab.go.id, email: diskominfo@wonosobokab.go.id<br>
                WONOSOBO - 56311
            </div>
        </div>
        <div class="strip"></div>
    </div>

    <div class="title">BERITA ACARA PENERIMAAN BARANG/PEKERJAAN</div>
    <div class="center mt-1">Nomor&nbsp;&nbsp;:&nbsp;<strong>{{ $nomorBA }}</strong></div>

    <p class="justify mt-2">
        Pada hari <strong>{{ $hariText }}</strong> tanggal <strong>{{ $hariHuruf }}</strong> bulan
        <strong>{{ $bulanNama }}</strong>
        tahun <strong>{{ $tahunHuruf }}</strong> kami yang bertanda tangan di bawah ini:
    </p>

    {{-- Identitas Penerima (Pemegang Barang) --}}
    <table class="data">
        <tr>
            <td class="lbl">Nama</td>
            <td class="sep">:</td>
            <td><strong>{{ $pemegangNama }}</strong></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>PEMEGANG BARANG</td>
        </tr>
    </table>

    <p class="justify mt-1">
        Berdasarkan Keputusan Bupati Wonosobo Nomor:
        <strong>{{ $kepNo ?: '........' }}</strong>{{ $kepTgl ? ' tanggal ' . Carbon::parse($kepTgl)->translatedFormat('d F Y') : '' }}
        telah menerima barang-barang yang diserahkan oleh: <strong>{{ $perusahaan }}</strong>, sesuai dengan
        Berita Acara Pemeriksaan Barang/Jasa:
    </p>
    <table class="data">
        <tr>
            <td class="lbl">Nomor</td>
            <td class="sep">:</td>
            <td>{{ $noPeriksa }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $tglPeriksaT }}</td>
        </tr>
    </table>

    <div class="mt-2">Daftar Barang yang sebagai berikut:</div>

    {{-- Tabel Barang --}}
    <table class="table mt-1">
        <thead>
            <tr>
                <th style="width:42px;">No</th>
                <th>Nama barang</th>
                <th style="width:140px;">Harga item</th>
                <th style="width:90px;">Qty</th>
                <th style="width:160px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no=1; @endphp
            @forelse($items as $it)
                @php
                    $harga = (float) ($it->harga ?? 0);
                    $qty = (float) ($it->kuantitas ?? 0);
                    $line = $harga * $qty;
                @endphp
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td>{{ $it->nama_barang ?? '-' }}</td>
                    <td class="right">{{ idr0($harga) }}</td>
                    <td class="center">{{ rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') }}
                        {{ $it->satuan ?? '' }}</td>
                    <td class="right">{{ idr0($line) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">— Tidak ada data rincian —</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Jumlah</th>
                <th class="right">{{ idr0($subtotal) }}</th>
            </tr>
        </tfoot>
    </table>

    <p class="justify mt-2">
        Demikian Berita Acara Penerimaan Barang ini dibuat dalam rangkap 3 (Tiga) untuk dipergunakan sebagaimana
        mestinya.
    </p>

    {{-- Tanda tangan: 2 di atas, 1 di bawah --}}
    <table class="ttd" style="width:100%; margin-top:12px;">
        <tr>
            <td style="width:50%">
                Yang Menyerahkan<br>
                {{ strtoupper($perusahaan) }}
                <span class="blank"></span>
                <span class="sign">{{ strtoupper($pimpinan) }}</span><br>
                Direktur
            </td>
            <td style="width:50%">
                Yang menerima,
                <span class="blank"></span>
                <span class="sign">{{ strtoupper($pemegangNama) }}</span><br>
                NIP. {{ $pemegangNip }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="center" style="padding-top:22px">
                Mengetahui;<br>
                Kepala Dinas Komunikasi dan Informatika<br>
                Kabupaten Wonosobo
                <span class="blank"></span>
                <span class="sign">{{ $kadisNama }}</span><br>
                NIP. {{ $kadisNip }}
            </td>
        </tr>
    </table>

    @if ($auto ?? false)
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>

</html>
