@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    if (!function_exists('tbgspm')) {
        function tbgspm($n): string {
            $n = abs((int)$n);
            $h = ['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh','sebelas'];
            if ($n < 12)         return $h[$n];
            if ($n < 20)         return trim(tbgspm($n - 10) . ' belas');
            if ($n < 100)        return trim(tbgspm((int)($n / 10)) . ' puluh ' . tbgspm($n % 10));
            if ($n < 200)        return trim('seratus ' . tbgspm($n - 100));
            if ($n < 1000)       return trim(tbgspm((int)($n / 100)) . ' ratus ' . tbgspm($n % 100));
            if ($n < 2000)       return trim('seribu ' . tbgspm($n - 1000));
            if ($n < 1000000)    return trim(tbgspm((int)($n / 1000)) . ' ribu ' . tbgspm($n % 1000));
            if ($n < 1000000000) return trim(tbgspm((int)($n / 1000000)) . ' juta ' . tbgspm($n % 1000000));
            return (string)$n;
        }
    }
    if (!function_exists('rpspm')) {
        function rpspm($n) { return number_format((float)$n, 0, ',', '.'); }
    }

    $tglText  = $tgl->translatedFormat('j F Y');
    $bulanTxt = $tgl->translatedFormat('F');

    $namaPA   = $pa?->nama   ?? '________________';  $nipPA   = $pa?->nip   ?? '________________';
    $namaBP   = $bp?->nama   ?? '________________';  $nipBP   = $bp?->nip   ?? '________________';
    $namaPPK  = $ppk?->nama  ?? '________________';  $nipPPK  = $ppk?->nip  ?? '________________';
    $namaPPTK = $pptk?->nama ?? '________________';  $nipPPTK = $pptk?->nip ?? '________________';

    $subKegKode = optional($subKeg)->kode ?? '-';
    $subKegNama = optional($subKeg)->nama ?? '-';
    $rkaNama    = optional($firstRka)->nama_belanja ?? '-';
    $rkaKode    = optional($firstRka)->kode_belanja ?? '-';

    $nilaiTerbilang = ucwords(tbgspm($dokumen->total_nilai)) . ' Rupiah';

    $paper       = strtoupper(request('paper', 'A4'));
    $paperWidth  = $paper === 'F4' ? '215mm' : '210mm';
    $paperHeight = $paper === 'F4' ? '330mm' : '297mm';
    $paperMargin = $paper === 'F4' ? '11mm'  : '10mm';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SPP-SPM {{ $jenis }} – {{ $noSpp }}</title>
<style>
@page { size: {{ $paperWidth }} {{ $paperHeight }}; margin: 0; }
*, html, body { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.28;
    color: #000;
    background: #d7d7d7;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.toolbar {
    position: sticky; top: 0; z-index: 99;
    background: #1f2937; color: #fff;
    padding: 8px 16px;
    display: flex; justify-content: space-between; align-items: center; gap: 10px;
    font-size: 12px;
}
.toolbar-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.toolbar button, .toolbar a {
    display: inline-block; background: #fff; color: #1f2937;
    border: none; border-radius: 5px; padding: 6px 12px;
    font-weight: 700; font-size: 10px; cursor: pointer; text-decoration: none;
}
.sheet {
    width: var(--pw, {{ $paperWidth }});
    min-height: calc({{ $paperHeight }} - 2mm);
    margin: 14px auto;
    background: #fff;
    page-break-after: always; break-after: page;
    box-shadow: 0 0 7px rgba(0,0,0,.20);
    overflow: hidden;
}
.sheet:last-of-type { page-break-after: auto !important; break-after: auto !important; }
.inner { padding: 12mm 13mm; }

table { width: 100%; border-collapse: collapse; border-spacing: 0; }
td, th { vertical-align: top; }
p { margin: 0 0 9px; text-align: justify; }
.center  { text-align: center; }
.right   { text-align: right; }
.bold    { font-weight: bold; }
.underline { text-decoration: underline; }

/* KOP SURAT */
.office-head { text-align: center; line-height: 1.2; }
.office-head .a { font-weight: 700; font-size: 15pt; letter-spacing: 0.5px; }
.office-head .b { font-weight: 700; font-size: 13pt; margin-top: 1px; margin-bottom: 2px; }
.kop-text { font-size: 9pt; line-height: 1.2; }
.kop-kota { font-size: 10pt; font-weight: 700; letter-spacing: 4px; margin-top: 3px; }
.office-line { border: 0; height: 4px; margin: 6px 0 14px; border-top: 2.5px solid #000; border-bottom: 1px solid #000; }

.title { font-weight: bold; text-align: center; font-size: 13px; margin: 16px 0 10px; }
.form td { padding: 3px 4px; line-height: 1.4; }
.grid th, .grid td { border: 1px solid #000; padding: 5px 6px; }

.ttd td { text-align: center; vertical-align: top; padding-top: 8px; }
.ttd-space    { height: 70px; display: block; }
.ttd-space-lg { height: 90px; display: block; }

/* HALAMAN 5 – KWITANSI */
.print-page-kwitansi {
    width: {{ $paperWidth }};
    min-height: calc({{ $paperHeight }} - 2mm);
    margin: 14px auto;
    padding: {{ $paperMargin }};
    background: #fff;
    page-break-after: always; break-after: page;
    box-shadow: 0 0 7px rgba(0,0,0,.20);
    overflow: hidden; page-break-inside: avoid;
}
.print-page-kwitansi:last-of-type { page-break-after: auto !important; break-after: auto !important; }
.page { width: 100%; border: 1px solid #000; }
.header td { padding: 2.5px 6px; }
.header-title { font-size: 13pt; padding-top: 9px !important; }
.line td { min-height: 22px; padding-top: 3px; padding-bottom: 3px; }
.section { border-top: 1px solid #000; }
.box { border-left: 1px solid #000; }
.content td { padding: 2.2px 5px; }
.main-title { font-size: 11pt; margin-bottom: 8px; }
.tax-table td { border: 1px solid #000; padding: 3px; }
.signature td {
    border-top: 1px solid #000; border-right: 1px solid #000;
    text-align: center; padding: 18px 5px;
    height: 165px; vertical-align: bottom;
    font-size: 8.3pt; line-height: 1.15;
}
.signature td:last-child { border-right: none; }
.signature-name { min-height: 42px; line-height: 1.15; }
.signature b { display: block; font-size: 8.5pt; line-height: 1.15; margin-top: 3px; }
.nip { display: block; font-size: 8pt; line-height: 1.1; margin-top: 2px; word-break: break-word; }
.ttd-space-sm { height: 35px; }
.ttd-space-kw  { height: 75px; }
.ttd-space-lg-kw { height: 100px; }
.recipient-sign { width: 42%; margin-left: auto; text-align: center; font-size: 8.8pt; line-height: 1.15; }
.fs-85 { font-size: 8.5pt; }

@media print {
    @page { margin: 0; }
    html, body { background: #fff !important; }
    .toolbar { display: none !important; }
    .sheet {
        width: {{ $paperWidth }} !important;
        min-height: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        page-break-after: always !important; break-after: page !important;
    }
    .sheet:last-of-type { page-break-after: auto !important; break-after: auto !important; }
    .print-page-kwitansi {
        width: {{ $paperWidth }} !important;
        min-height: 0 !important; height: auto !important;
        margin: 0 !important; padding: {{ $paperMargin }} !important;
        box-shadow: none !important; overflow: hidden !important;
        page-break-after: always !important; break-after: page !important;
    }
    .print-page-kwitansi:last-of-type { page-break-after: auto !important; break-after: auto !important; }
    table, tr, td, th { page-break-inside: avoid; break-inside: avoid; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
</head>
<body>

<div class="toolbar">
    <span>SPP-SPM {{ $jenis }}: {{ $noSpp }} &mdash; {{ $tglText }} &mdash; Kertas: {{ $paper }}</span>
    <div class="toolbar-actions">
        <a href="?paper=A4">A4</a>
        <a href="?paper=F4">F4</a>
        <button onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
        <button onclick="window.close()">&#10005; Tutup</button>
    </div>
</div>

{{-- ======================================================
     HALAMAN 1 – SURAT PERNYATAAN PENGAJUAN
     ====================================================== --}}
<section class="sheet"><div class="inner">
    <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
        <tr>
            <td style="width:115px;text-align:center;vertical-align:middle;padding-left:50px;padding-right:10px;">
                <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemkab"
                    style="width:95px;height:auto;display:block;margin:auto;">
            </td>
            <td style="text-align:center;vertical-align:middle;padding-right:5px;">
                <div class="office-head">
                    <div class="a">PEMERINTAH KABUPATEN WONOSOBO</div>
                    <div class="b">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                    <div class="kop-text">Jl. Sabuk Alu No. 2A (0286) 325112 / Fax 325115</div>
                    <div class="kop-text">Website: diskominfo.wonosobokab.go.id email: diskominfo@wonosobokab.go.id</div>
                    <div class="kop-kota">W O N O S O B O &nbsp; - &nbsp; 56314</div>
                </div>
            </td>
        </tr>
    </table>
    <hr class="office-line">

    <div class="title" style="margin-top:18px;">SURAT PERNYATAAN PENGAJUAN – {{ $jenis }}</div>
    <div class="center" style="margin-bottom:26px;">Nomor : {{ $noSpp }}</div>

    <p>Sehubungan dengan Surat Perintah Membayar {{ $jenis }} (SPM-{{ $jenis }} Nomor: {{ $noSpmSipd }}) tanggal {{ $tglText }}
    yang kami ajukan sebesar Rp. {{ rpspm($dokumen->total_nilai) }}
    ({{ $nilaiTerbilang }}) untuk keperluan SKPD Dinas Komunikasi dan Informatika Kabupaten Wonosobo
    tahun anggaran {{ $tahun }} dengan ini menyatakan bahwa saya :</p>

    <table class="form" style="width:75%;margin:18px 0;">
        <tr><td style="width:18%;">Nama</td><td style="width:3%;">:</td><td>{{ $namaPA }}</td></tr>
        <tr><td>NIP</td><td>:</td><td>{{ $nipPA }}</td></tr>
        <tr><td>Jabatan</td><td>:</td><td>Kepala Dinas Komunikasi dan Informatika</td></tr>
    </table>

    <p>Bertanggung Jawab secara formal dan material atas kebenaran penggunaan dana tersebut diatas sesuai ketentuan yang berlaku.</p>
    <p>Demikian surat Pernyataan ini dibuat untuk melengkapi pernyataan dan persyaratan pengajuan SPP-{{ $jenis }} SKPD kami.</p>

    <table class="ttd" style="margin-top:40px;">
        <tr>
            <td style="width:55%;"></td>
            <td>
                Wonosobo, {{ $tglText }}<br>
                Pengguna Anggaran<br>
                Kepala Dinas Komunikasi dan Informatika<br>
                Kabupaten Wonosobo
                <span class="ttd-space-lg"></span>
                <span class="bold">{{ $namaPA }}</span><br>
                NIP. {{ $nipPA }}
            </td>
        </tr>
    </table>
</div></section>

{{-- ======================================================
     HALAMAN 2 – VERIFIKASI PPK ATAS PENGAJUAN SPM
     ====================================================== --}}
<section class="sheet"><div class="inner">
    <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
        <tr>
            <td style="width:115px;text-align:center;vertical-align:middle;padding-left:50px;padding-right:10px;">
                <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemkab"
                    style="width:95px;height:auto;display:block;margin:auto;">
            </td>
            <td style="text-align:center;vertical-align:middle;padding-right:5px;">
                <div class="office-head">
                    <div class="a">PEMERINTAH KABUPATEN WONOSOBO</div>
                    <div class="b">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                    <div class="kop-text">Jl. Sabuk Alu No. 2A (0286) 325112 / Fax 325115</div>
                    <div class="kop-text">Website: diskominfo.wonosobokab.go.id email: diskominfo@wonosobokab.go.id</div>
                    <div class="kop-kota">W O N O S O B O &nbsp; - &nbsp; 56314</div>
                </div>
            </td>
        </tr>
    </table>
    <hr class="office-line">

    <div class="title">PEJABAT PENATAUSAHAAN KEUANGAN SKPD<br>VERIFIKASI ATAS PENGAJUAN SPM – {{ $jenis }}</div>

    <table class="form" style="width:80%;margin-top:28px;">
        <tr><td style="width:48%;">Bulan</td><td style="width:3%;">:</td><td>{{ $bulanTxt }} {{ $tahun }}</td></tr>
        <tr><td>Nama Bendahara Pengeluaran</td><td>:</td><td>{{ $namaBP }}</td></tr>
        <tr><td>SPM – {{ $jenis }} yang diajukan Sebesar</td><td>:</td><td>Rp. {{ rpspm($dokumen->total_nilai) }}</td></tr>
        <tr><td>SPM – {{ $jenis }} disahkan</td><td>:</td><td>Rp. {{ rpspm($dokumen->total_nilai - $totalPajak) }}</td></tr>
    </table>

    <table class="form" style="width:65%;margin-top:16px;">
        <tr>
            <td style="width:44%;">Pemungutan Potongan</td>
            <td style="width:4%;">-</td>
            <td style="width:18%;">PPN</td>
            <td style="width:4%;">:</td>
            <td class="right">{{ rpspm($ppn) }}</td>
        </tr>
        <tr>
            <td></td><td>-</td><td>PPh 21</td><td>:</td>
            <td class="right">{{ rpspm($pph21) }}</td>
        </tr>
        <tr>
            <td></td><td>-</td><td>PPh 22</td><td>:</td>
            <td class="right">{{ rpspm($pph22) }}</td>
        </tr>
        <tr>
            <td></td><td>-</td><td>PPh 23</td><td>:</td>
            <td class="right">{{ rpspm($pph23) }}</td>
        </tr>
        <tr class="bold">
            <td colspan="4">Jumlah Potongan &nbsp; Rp.</td>
            <td class="right" style="border-top:1px solid #000;">{{ rpspm($totalPajak) }}</td>
        </tr>
    </table>

    <div style="margin-top:24px;">
        <div style="font-size:10px;margin-bottom:4px;">Catatan Verifikasi :</div>
        <div style="border:1px solid #000;min-height:120px;width:100%;"></div>
    </div>

    <table class="ttd" style="margin-top:4px;">
        <tr>
            <td style="width:55%;"></td>
            <td>
                Wonosobo, {{ $tglText }}<br><br>
                PPK – SKPD
                <span class="ttd-space-lg"></span>
                <span class="bold">{{ $namaPPK }}</span><br>
                NIP. {{ $nipPPK }}
            </td>
        </tr>
    </table>
</div></section>

{{-- ======================================================
     HALAMAN 3 – LEMBAR CEK LIST PPK-SKPD
     ====================================================== --}}
<section class="sheet"><div class="inner">
    <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
        <tr>
            <td style="width:115px;text-align:center;vertical-align:middle;padding-left:50px;padding-right:10px;">
                <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemkab"
                    style="width:95px;height:auto;display:block;margin:auto;">
            </td>
            <td style="text-align:center;vertical-align:middle;padding-right:5px;">
                <div class="office-head">
                    <div class="a">PEMERINTAH KABUPATEN WONOSOBO</div>
                    <div class="b">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                    <div class="kop-text">Jl. Sabuk Alu No. 2A (0286) 325112 / Fax 325115</div>
                    <div class="kop-text">Website: diskominfo.wonosobokab.go.id email: diskominfo@wonosobokab.go.id</div>
                    <div class="kop-kota">W O N O S O B O &nbsp; - &nbsp; 56314</div>
                </div>
            </td>
        </tr>
    </table>
    <hr class="office-line">

    <div class="title">LEMBAR CEK LIST PPK – SKPD UNTUK SPJ {{ $jenis }}</div>

    <table class="form" style="margin-bottom:12px;">
        <tr><td style="width:16%;">SKPD</td><td style="width:3%;">:</td><td>DINAS KOMUNIKASI DAN INFORMATIKA</td></tr>
        <tr><td>KABUPATEN</td><td>:</td><td>WONOSOBO</td></tr>
        <tr><td>JENIS SPJ</td><td>:</td><td>{{ $dokumen->uraian }}</td></tr>
    </table>

    <table class="grid" style="font-size:10px;">
        <tr class="center bold">
            <th style="width:6%;">NO</th>
            <th>URAIAN</th>
            <th style="width:13%;">ADA /<br>TIDAK</th>
            <th style="width:15%;">SESUAI /<br>TIDAK</th>
            <th style="width:15%;">KETERANGAN</th>
        </tr>
        @foreach([
            'Kode Rekening dan Nominasi Belanja Sesuai SPJ',
            'Nota Belanja dan Kwitansi Dinas',
            'Bukti Setoran Pajak',
            'Surat Pengantar SPP GU/TU/UP/LS',
            'Ringkasan SPP GU/TU/UP/LS',
            'Rincian SPP',
            'Surat Perintah Membayar (SPM)',
            'Surat Pernyataan Pengajuan SPP GU/TU/UP/LS',
            'Surat Pernyataan Tanggung Jawab PA',
            'Lembar Verifikasi PPK - SKPD',
        ] as $idx => $item)
        <tr>
            <td class="center">{{ $idx + 1 }}</td>
            <td>{{ $item }}</td>
            <td class="center">√</td>
            <td class="center">√</td>
            <td></td>
        </tr>
        @endforeach
    </table>

    <div style="margin-top:14px;">
        PAGU ANGGARAN SPP/SPM YANG DIAJUKAN &nbsp;&nbsp;&nbsp; : Rp. {{ rpspm($dokumen->total_nilai) }}<br>
        SPP/SPM YANG DI SAHKAN
    </div>
    <div style="height:44px;border:1px solid #000;margin:6px 0 0;"></div>

    <table class="ttd" style="margin-top:4px;">
        <tr>
            <td style="width:55%;"></td>
            <td>
                Wonosobo, {{ $tglText }}<br><br>
                PPK – SKPD
                <span class="ttd-space-lg"></span>
                <span class="bold">{{ $namaPPK }}</span><br>
                NIP. {{ $nipPPK }}
            </td>
        </tr>
    </table>
</div></section>

{{-- ======================================================
     HALAMAN 4 – SURAT PERNYATAAN TANGGUNG JAWAB PA
     ====================================================== --}}
<section class="sheet"><div class="inner">
    <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
        <tr>
            <td style="width:115px;text-align:center;vertical-align:middle;padding-left:50px;padding-right:10px;">
                <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemkab"
                    style="width:95px;height:auto;display:block;margin:auto;">
            </td>
            <td style="text-align:center;vertical-align:middle;padding-right:5px;">
                <div class="office-head">
                    <div class="a">PEMERINTAH KABUPATEN WONOSOBO</div>
                    <div class="b">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                    <div class="kop-text">Jl. Sabuk Alu No. 2A (0286) 325112 / Fax 325115</div>
                    <div class="kop-text">Website: diskominfo.wonosobokab.go.id email: diskominfo@wonosobokab.go.id</div>
                    <div class="kop-kota">W O N O S O B O &nbsp; - &nbsp; 56314</div>
                </div>
            </td>
        </tr>
    </table>
    <hr class="office-line">

    <div class="title">SURAT PERNYATAAN TANGGUNG JAWAB PENGGUNA ANGGARAN</div>
    <div class="center" style="margin-bottom:22px;">Nomor: {{ $noTjawab }}</div>

    <p>Sehubungan dengan Surat Perintah Membayar {{ $jenis }} (SPM-{{ $jenis }}) Nomor: {{ $noSpmSipd }}
    tanggal {{ $tglText }} sebesar Rp. {{ rpspm($dokumen->total_nilai) }}</p>

    <p>Terbilang: {{ $nilaiTerbilang }}</p>

    <p>untuk keperluan SKPD Dinas Komunikasi &amp; Informatika Kab. Wonosobo
    Tahun Anggaran {{ $tahun }} dengan ini menyatakan bahwa saya:</p>

    <table class="form" style="width:82%;margin:14px 0;">
        <tr><td style="width:17%;">N a m a</td><td style="width:3%;">:</td><td>{{ $namaPA }}</td></tr>
        <tr><td>N I P</td><td>:</td><td>{{ $nipPA }}</td></tr>
        <tr><td>Jabatan</td><td>:</td><td>Kepala Dinas Komunikasi &amp; Informatika Kabupaten Wonosobo.</td></tr>
    </table>

    <p>Bertanggung Jawab secara formal dan material atas kebenaran penggunaan dana tersebut diatas sesuai ketentuan yang berlaku.</p>
    <p>Demikian Surat Pernyataan ini kami buat untuk melengkapi persyaratan pengajuan SPM-{{ $jenis }} SKPD kami.</p>

    <table class="ttd" style="margin-top:40px;">
        <tr>
            <td style="width:45%;"></td>
            <td>
                Wonosobo, {{ $tglText }}<br>
                Kepala Dinas Komunikasi &amp; Informatika<br>
                Kabupaten Wonosobo<br>
                Selaku Pengguna Anggaran
                <span class="ttd-space-lg"></span>
                <span class="bold">{{ $namaPA }}</span><br>
                NIP. {{ $nipPA }}
            </td>
        </tr>
    </table>
</div></section>

{{-- ======================================================
     HALAMAN 5 – KWITANSI DINAS
     ====================================================== --}}
<section class="print-page-kwitansi">
    <div class="page">
        <table class="header">
            <tr>
                <td colspan="3" class="center bold header-title">
                    PEMERINTAH KABUPATEN WONOSOBO
                </td>
            </tr>
            <tr>
                <td colspan="3" class="center bold">NO: {{ $noSpp }}</td>
            </tr>
            <tr class="line">
                <td width="32%">S K P D / Kode Rekening</td>
                <td width="2%">:</td>
                <td class="bold">DINAS KOMUNIKASI DAN INFORMATIKA / {{ $subKegKode }}</td>
            </tr>
            <tr class="line">
                <td>Pengguna Anggaran</td>
                <td>:</td>
                <td class="bold">{{ $namaPA }}</td>
            </tr>
            <tr class="line">
                <td>Bendahara Pengeluaran</td>
                <td>:</td>
                <td class="bold">{{ $namaBP }}</td>
            </tr>
            <tr class="line">
                <td>Tahun Anggaran</td>
                <td>:</td>
                <td class="bold">{{ $tahun }}</td>
            </tr>
        </table>

        <table class="section">
            <tr>
                <td width="64%" style="padding:7px;">
                    <div class="center bold main-title">SURAT BUKTI PEMBAYARAN</div>

                    <table class="content">
                        <tr class="line">
                            <td width="32%">Sudah terima dari</td>
                            <td width="3%">:</td>
                            <td>Bendahara Pengeluaran Diskominfo</td>
                        </tr>
                        <tr class="line">
                            <td>Uang sejumlah</td>
                            <td>:</td>
                            <td>Rp. {{ rpspm($dokumen->total_nilai) }}</td>
                        </tr>
                        <tr class="line">
                            <td>Terbilang</td>
                            <td>:</td>
                            <td>{{ $nilaiTerbilang }}</td>
                        </tr>
                        <tr class="line">
                            <td>Yaitu untuk pembayaran</td>
                            <td>:</td>
                            <td>{{ $noSpp }} - {{ $dokumen->uraian }}</td>
                        </tr>
                        <tr class="line">
                            <td>Berguna buat pekerjaan</td>
                            <td>:</td>
                            <td>{{ $rkaNama }}<br>{{ $subKegNama }}</td>
                        </tr>
                        <tr class="line">
                            <td>Kode Rekening</td>
                            <td>:</td>
                            <td>{{ $rkaKode }}</td>
                        </tr>
                    </table>

                    <div class="ttd-space-sm"></div>

                    <div class="recipient-sign">
                        Wonosobo, {{ $tglText }}<br>
                        Yang Berhak Menerima
                        <div class="ttd-space-lg-kw"></div>
                        (...........................................)
                        <br><br><br><br>
                    </div>
                </td>

                <td width="36%" class="box">
                    <div class="center bold" style="padding:7px;border-bottom:1px solid #000;">KETERANGAN</div>

                    <div style="padding:7px;height:74px;">
                        Barang barang termasuk telah masuk buku Persediaan / Inventaris pada Tgl ..........
                    </div>

                    <table class="tax-table fs-85">
                        <tr>
                            <td class="center">Jumlah kotor</td>
                            <td class="center">Pajak</td>
                            <td class="center">Jumlah bersih</td>
                        </tr>
                        <tr>
                            <td class="right">{{ rpspm($dokumen->total_nilai) }}</td>
                            <td class="right">{{ rpspm($totalPajak) }}</td>
                            <td class="right">{{ rpspm($totalBersih) }}</td>
                        </tr>
                    </table>

                    <div style="padding:7px;">
                        <div class="bold">Uraian Pajak:</div>
                        <table style="width:100%;margin-top:5px;">
                            <tr>
                                <td>1. PPN</td>
                                <td width="12%">Rp.</td>
                                <td class="right">{{ rpspm($ppn) }}</td>
                            </tr>
                            <tr>
                                <td>2. PPh 21</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpspm($pph21) }}</td>
                            </tr>
                            <tr>
                                <td>3. PPh 22</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpspm($pph22) }}</td>
                            </tr>
                            <tr>
                                <td>4. PPh 23</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpspm($pph23) }}</td>
                            </tr>
                            <tr class="bold">
                                <td>JUMLAH</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpspm($totalPajak) }}</td>
                            </tr>
                        </table>

                        <div style="margin-top:14px;">Pengeluaran / Pembelian dilakukan berdasarkan :</div>
                        <div style="margin-top:18px;text-align:center;">Alamat Penerima</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="signature">
            <tr>
                <td width="25%">
                    <div class="signature-name">
                        Mengetahui &amp; Menyetujui,<br>
                        Pengguna Anggaran
                    </div>
                    <div class="ttd-space-kw"></div>
                    <b>{{ $namaPA }}</b>
                    <div class="nip">NIP. {{ $nipPA }}</div>
                </td>
                <td width="25%">
                    <div class="signature-name">
                        Yang Membayarkan<br>
                        Bendahara Pengeluaran,
                    </div>
                    <div class="ttd-space-kw"></div>
                    <b>{{ $namaBP }}</b>
                    <div class="nip">NIP. {{ $nipBP }}</div>
                </td>
                <td width="25%">
                    <div class="signature-name">
                        P P T K
                    </div>
                    <div class="ttd-space-kw"></div>
                    <b>{{ $namaPPTK }}</b>
                    <div class="nip">NIP. {{ $nipPPTK }}</div>
                </td>
                <td width="25%">
                    <div class="signature-name">
                        Yang Menerima,<br>
                        &nbsp;
                    </div>
                    <div class="ttd-space-kw"></div>
                    <b>(..............................)</b>
                    <div class="nip">NIP. ___________________</div>
                </td>
            </tr>
        </table>
    </div>
</section>

</body>
</html>
