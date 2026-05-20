@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    if (!function_exists('tbgls')) {
        function tbgls($n): string
        {
            $n = abs((int) $n);
            $h = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
            if ($n < 12)   return $h[$n];
            if ($n < 20)   return trim(tbgls($n - 10) . ' belas');
            if ($n < 100)  return trim(tbgls((int)($n / 10)) . ' puluh ' . tbgls($n % 10));
            if ($n < 200)  return trim('seratus ' . tbgls($n - 100));
            if ($n < 1000) return trim(tbgls((int)($n / 100)) . ' ratus ' . tbgls($n % 100));
            if ($n < 2000) return trim('seribu ' . tbgls($n - 1000));
            if ($n < 1000000)    return trim(tbgls((int)($n / 1000)) . ' ribu ' . tbgls($n % 1000));
            if ($n < 1000000000) return trim(tbgls((int)($n / 1000000)) . ' juta ' . tbgls($n % 1000000));
            return (string)$n;
        }
    }

    if (!function_exists('rpls')) {
        function rpls($n) { return number_format((float)$n, 0, ',', '.'); }
    }

    $tglText  = $tgl->translatedFormat('j F Y');
    $bulanTxt = $tgl->translatedFormat('F');

    $namaPA   = $pa?->nama  ?? '________________';
    $nipPA    = $pa?->nip   ?? '________________';
    $namaBP   = $bp?->nama  ?? '________________';
    $nipBP    = $bp?->nip   ?? '________________';
    $namaPPK  = $ppk?->nama ?? '________________';
    $nipPPK   = $ppk?->nip  ?? '________________';
    $namaPB   = $pb?->nama;
    $nipPB    = $pb?->nip;
    $namaPPTK = $pptk?->nama ?? '________________';
    $nipPPTK  = $pptk?->nip  ?? '________________';

    $noBuktiFormatted = 'SPP-' . $belanja->no_bukti . '/Diskominfo/' . $tahun;

    $paper       = strtoupper(request('paper', 'A4'));
    $paperWidth  = $paper === 'F4' ? '215mm' : '210mm';
    $paperHeight = $paper === 'F4' ? '330mm' : '297mm';
    $paperMargin = $paper === 'F4' ? '11mm'  : '10mm';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPP-LS {{ $noBuktiFormatted }}</title>
    <style>
        :root {
            --paper-width:  {{ $paperWidth }};
            --paper-height: {{ $paperHeight }};
            --paper-margin: {{ $paperMargin }};
            --font-main: Arial, Helvetica, sans-serif;
            --font-size: 9pt;
            --line-height: 1.18;
        }

        @page { size: {{ $paperWidth }} {{ $paperHeight }}; margin: 0; }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        body {
            font-family: var(--font-main);
            background: #d7d7d7;
            color: #000;
            font-size: var(--font-size);
            line-height: var(--line-height);
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

        .print-page {
            width: var(--paper-width);
            min-height: calc(var(--paper-height) - 2mm);
            margin: 14px auto;
            padding: var(--paper-margin);
            background: #fff;
            page-break-after: always; break-after: page;
            box-shadow: 0 0 7px rgba(0,0,0,0.20);
            overflow: hidden; page-break-inside: avoid;
        }
        .print-page:last-of-type { page-break-after: auto !important; break-after: auto !important; }

        .page { width: 100%; border: 1px solid #000; }

        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }
        p { margin: 0 0 7px; }

        .center  { text-align: center; }
        .right   { text-align: right; }
        .justify { text-align: justify; }
        .bold    { font-weight: 700; }
        .nowrap  { white-space: nowrap; }

        .header td         { padding: 2.5px 6px; }
        .header-title      { font-size: 13pt; padding-top: 9px !important; }
        .section           { border-top: 1px solid #000; }
        .box               { border-left: 1px solid #000; }
        .content td        { padding: 2.2px 5px; }
        .line td           { min-height: 22px; padding-top: 3px; padding-bottom: 3px; }
        .main-title        { font-size: 11pt; margin-bottom: 8px; }

        .tax-table td      { border: 1px solid #000; padding: 3px; }
        .form-table td     { padding: 3px 4px; line-height: 1.22; }
        .grid-table th,
        .grid-table td     { border: 1px solid #000; padding: 4px 5px; line-height: 1.22; }

        .signature td {
            border-top: 1px solid #000; border-right: 1px solid #000;
            text-align: center; padding: 18px 5px;
            height: 165px; vertical-align: bottom;
            font-size: 8.3pt; line-height: 1.15;
        }
        .signature td:last-child { border-right: none; }
        .signature-name { min-height: 42px; line-height: 1.15; }
        .signature b, .clean-signature b { display: block; font-size: 8.5pt; line-height: 1.15; margin-top: 3px; }
        .nip { display: block; font-size: 8pt; line-height: 1.1; margin-top: 2px; word-break: break-word; }

        .clean-signature td {
            text-align: center; padding: 6px; vertical-align: bottom;
            font-size: 8.8pt; line-height: 1.15;
        }

        .ttd-space-sm { height: 35px; }
        .ttd-space    { height: 75px; }
        .ttd-space-lg { height: 100px; }

        .office-head { text-align: center; line-height: 1.2; }
        .office-head .a { font-weight: 700; font-size: 15pt; letter-spacing: 0.5px; }
        .office-head .b { font-weight: 700; font-size: 13pt; margin-top: 1px; margin-bottom: 2px; }
        .kop-text   { font-size: 9pt; line-height: 1.2; }
        .kop-kota   { font-size: 10pt; font-weight: 700; letter-spacing: 4px; margin-top: 3px; }
        .office-line { border: 0; height: 4px; margin: 8px 0 15px; border-top: 2.5px solid #000; border-bottom: 1px solid #000; }

        .page-pad      { padding: 8px 10px; }
        .page-pad-wide { padding: 8px 14px; }

        .mt-6  { margin-top: 6px; }
        .mt-10 { margin-top: 10px; }
        .mb-8  { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .mb-18 { margin-bottom: 18px; }

        .fs-8  { font-size: 8pt; }
        .fs-85 { font-size: 8.5pt; }
        .fs-9  { font-size: 9pt; }
        .fs-95 { font-size: 9.5pt; }
        .fs-10 { font-size: 10pt; }
        .fs-11 { font-size: 11pt; }
        .fs-12 { font-size: 12pt; }

        @media print {
            @page { margin: 0; }
            .print-page:empty { display: none !important; }
            html, body { width: var(--paper-width); min-height: var(--paper-height); background: #fff !important; }
            body { margin: 0 !important; padding: 0 !important; }
            .toolbar { display: none !important; }
            .print-page {
                width: var(--paper-width) !important; min-height: 0 !important; height: auto !important;
                margin: 0 !important; padding: var(--paper-margin) !important;
                box-shadow: none !important; overflow: hidden !important;
                page-break-after: always !important; break-after: page !important;
            }
            .print-page:last-of-type { page-break-after: auto !important; break-after: auto !important; }
            table, tr, td, th { page-break-inside: avoid; break-inside: avoid; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <span>{{ $noBuktiFormatted }} &mdash; {{ $tglText }} &mdash; Kertas: {{ $paper }}</span>
    <div class="toolbar-actions">
        <a href="?paper=A4">A4</a>
        <a href="?paper=F4">F4</a>
        <button onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
        <button onclick="window.close()">&#10005; Tutup</button>
    </div>
</div>

<!-- HALAMAN 1: SPP BELANJA LANGSUNG -->
<section class="print-page">
    <div class="page">
        <table class="header">
            <tr>
                <td colspan="3" class="center bold header-title">PEMERINTAH KABUPATEN WONOSOBO</td>
            </tr>
            <tr>
                <td colspan="3" class="center bold">NO: {{ $noBuktiFormatted }}</td>
            </tr>
            <tr class="line">
                <td width="32%">S K P D / Kode Rekening</td>
                <td width="2%">:</td>
                <td class="bold">DINAS KOMUNIKASI DAN INFORMATIKA / {{ optional($subKeg)->kode ?? '-' }}</td>
            </tr>
            <tr class="line">
                <td>No. SPM SIPD</td>
                <td>:</td>
                <td class="bold">{{ $belanja->no_spm_sipd ?? '-' }}</td>
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
                    <div class="center bold main-title">SURAT PERMINTAAN PEMBAYARAN (SPP)</div>
                    <div class="center" style="margin-bottom:6px;">Belanja Langsung (LS)</div>

                    <table class="content">
                        <tr class="line">
                            <td width="34%">Diminta oleh</td>
                            <td width="3%">:</td>
                            <td>Bendahara Pengeluaran Diskominfo</td>
                        </tr>
                        <tr class="line">
                            <td>Jumlah yang diminta</td>
                            <td>:</td>
                            <td>Rp. {{ rpls($belanja->total_nilai) }}</td>
                        </tr>
                        <tr class="line">
                            <td>Terbilang</td>
                            <td>:</td>
                            <td>{{ ucwords(tbgls($belanja->total_nilai)) }} Rupiah</td>
                        </tr>
                        <tr class="line">
                            <td>Untuk keperluan</td>
                            <td>:</td>
                            <td>{{ $belanja->uraian }}</td>
                        </tr>
                        <tr class="line">
                            <td>Sub Kegiatan</td>
                            <td>:</td>
                            <td>{{ optional($subKeg)->kode }} - {{ optional($subKeg)->nama }}</td>
                        </tr>
                        @if ($belanja->details->count() === 1)
                        <tr class="line">
                            <td>Rekening Belanja</td>
                            <td>:</td>
                            <td>{{ optional($firstRka)->kode_belanja }} - {{ optional($firstRka)->nama_belanja }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="ttd-space-sm"></div>

                    <div style="text-align:right; padding-right:10px; font-size:8.8pt; line-height:1.15;">
                        Wonosobo, {{ $tglText }}<br>
                        Bendahara Pengeluaran<br>
                        <div class="ttd-space-lg"></div>
                        <b>{{ $namaBP }}</b><br>
                        <span class="nip">NIP. {{ $nipBP }}</span>
                    </div>
                </td>

                <td width="36%" class="box">
                    <div class="center bold" style="padding:7px;border-bottom:1px solid #000;">KETERANGAN</div>

                    <div style="padding:7px;height:60px;">
                        Jenis SPP: Belanja Langsung (LS)
                    </div>

                    <table class="tax-table fs-85">
                        <tr>
                            <td class="center">Jumlah kotor</td>
                            <td class="center">Pajak</td>
                            <td class="center">Jumlah bersih</td>
                        </tr>
                        <tr>
                            <td class="right">{{ rpls($belanja->total_nilai) }}</td>
                            <td class="right">{{ rpls($totalPajak) }}</td>
                            <td class="right">{{ rpls($totalBersih) }}</td>
                        </tr>
                    </table>

                    <div style="padding:7px;">
                        <div class="bold">Uraian Pajak:</div>
                        <table style="width:100%;margin-top:5px;">
                            <tr>
                                <td>1. PPN</td>
                                <td width="12%">Rp.</td>
                                <td class="right">{{ rpls($ppn) }}</td>
                            </tr>
                            <tr>
                                <td>2. PPh 21</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpls($pph21) }}</td>
                            </tr>
                            <tr>
                                <td>3. PPh 22</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpls($pph22) }}</td>
                            </tr>
                            <tr>
                                <td>4. PPh 23</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpls($pph23) }}</td>
                            </tr>
                            <tr class="bold">
                                <td>JUMLAH</td>
                                <td>Rp.</td>
                                <td class="right">{{ rpls($totalPajak) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="signature">
            <tr>
                @if ($namaPB)
                <td width="25%">
                    <div class="signature-name">
                        Yang Menerima Barang<br>
                        Bendahara Barang,
                    </div>
                    <div class="ttd-space"></div>
                    <b>{{ $namaPB }}</b>
                    <div class="nip">NIP. {{ $nipPB }}</div>
                </td>
                @endif
                <td width="{{ $namaPB ? '25%' : '33%' }}">
                    <div class="signature-name">
                        Mengetahui &amp; Menyetujui,<br>
                        Pengguna Anggaran
                    </div>
                    <div class="ttd-space"></div>
                    <b>{{ $namaPA }}</b>
                    <div class="nip">NIP. {{ $nipPA }}</div>
                </td>
                <td width="{{ $namaPB ? '25%' : '33%' }}">
                    <div class="signature-name">
                        PPK-SKPD,
                    </div>
                    <div class="ttd-space"></div>
                    <b>{{ $namaPPK }}</b>
                    <div class="nip">NIP. {{ $nipPPK }}</div>
                </td>
                <td width="{{ $namaPB ? '25%' : '34%' }}">
                    <div class="signature-name">
                        P P T K
                    </div>
                    <div class="ttd-space"></div>
                    <b>{{ $namaPPTK }}</b>
                    <div class="nip">NIP. {{ $nipPPTK }}</div>
                </td>
            </tr>
        </table>
    </div>
</section>

<!-- HALAMAN 2: RINCIAN OBYEK BELANJA PER RKA -->
<section class="print-page">
    <div class="page-pad">
        <div class="center bold fs-11">PEMERINTAH KABUPATEN WONOSOBO</div>
        <div class="center bold fs-11">RINCIAN OBYEK BELANJA LANGSUNG (LS)</div>
        <div class="center bold fs-95 mb-18">{{ $noBuktiFormatted }}</div>

        <table class="form-table mb-18">
            <tr>
                <td style="width:30%;">SKPD</td>
                <td style="width:3%;">:</td>
                <td>Dinas Komunikasi dan Informatika</td>
            </tr>
            <tr>
                <td>Kode dan Nama Kegiatan</td>
                <td>:</td>
                <td>{{ optional($kegiatan)->kode }} - {{ optional($kegiatan)->nama }}</td>
            </tr>
            <tr>
                <td>Kode dan Nama Sub Kegiatan</td>
                <td>:</td>
                <td>{{ optional($subKeg)->kode }} - {{ optional($subKeg)->nama }}</td>
            </tr>
            <tr>
                <td>No. SPP</td>
                <td>:</td>
                <td>{{ $noBuktiFormatted }}</td>
            </tr>
            <tr>
                <td>No. SPM SIPD</td>
                <td>:</td>
                <td>{{ $belanja->no_spm_sipd ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $tglText }}</td>
            </tr>
            <tr>
                <td>Tahun Anggaran</td>
                <td>:</td>
                <td>{{ $tahun }}</td>
            </tr>
            <tr>
                <td>Bulan</td>
                <td>:</td>
                <td>{{ $bulanTxt }}</td>
            </tr>
        </table>

        <table class="grid-table fs-85">
            <tr class="center bold">
                <td style="width:5%;">NO</td>
                <td style="width:20%;">KODE REKENING</td>
                <td style="width:30%;">NAMA BELANJA</td>
                <td style="width:11%;">ANGGARAN</td>
                <td style="width:11%;">S.D. SEBELUMNYA</td>
                <td style="width:11%;">NILAI LS INI</td>
                <td style="width:12%;">SISA</td>
            </tr>
            <tr class="center fs-8">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
            </tr>
            @foreach ($detailsWithRealisasi as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center" style="font-size:8pt;">{{ optional($item['rka'])->kode_belanja }}</td>
                <td>{{ optional($item['rka'])->nama_belanja }}</td>
                <td class="right">{{ rpls(optional($item['rka'])->anggaran ?? 0) }}</td>
                <td class="right">{{ rpls($item['ls_sebelumnya']) }}</td>
                <td class="right">{{ rpls($item['detail']->nilai) }}</td>
                <td class="right">{{ rpls($item['sisa_sd_ini']) }}</td>
            </tr>
            @endforeach
            <tr class="bold">
                <td colspan="3" class="center">J U M L A H</td>
                <td class="right">{{ rpls($belanja->details->sum(fn($d) => optional($d->rka)->anggaran ?? 0)) }}</td>
                <td class="right">{{ rpls($detailsWithRealisasi->sum('ls_sebelumnya')) }}</td>
                <td class="right">{{ rpls($belanja->total_nilai) }}</td>
                <td class="right">{{ rpls($detailsWithRealisasi->sum('sisa_sd_ini')) }}</td>
            </tr>
        </table>

        @if ($belanja->pajakLs->count() > 0)
        <div class="mt-10">
            <div class="bold mb-8">Rincian Pajak:</div>
            <table class="grid-table fs-85" style="width:60%;">
                <tr class="center bold">
                    <td style="width:10%;">No</td>
                    <td>Jenis Pajak</td>
                    <td>No. Billing</td>
                    <td>Nominal</td>
                </tr>
                @foreach ($belanja->pajakLs as $i => $pjk)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $pjk->jenis_pajak }}</td>
                    <td class="center">{{ $pjk->no_billing ?? '-' }}</td>
                    <td class="right">{{ rpls($pjk->nominal) }}</td>
                </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="3" class="center">Total Pajak</td>
                    <td class="right">{{ rpls($totalPajak) }}</td>
                </tr>
            </table>
        </div>
        @endif

        <div class="ttd-space-sm"></div>
        <div style="text-align:right;padding-right:45px;">Wonosobo, {{ $tglText }}</div>

        <table class="clean-signature mt-6">
            <tr>
                <td style="width:33%;">
                    P P T K
                    <div class="ttd-space"></div>
                    <b>{{ $namaPPTK }}</b>
                    <div class="nip">NIP. {{ $nipPPTK }}</div>
                </td>
                <td style="width:34%;">
                    PPK-SKPD
                    <div class="ttd-space"></div>
                    <b>{{ $namaPPK }}</b>
                    <div class="nip">NIP. {{ $nipPPK }}</div>
                </td>
                <td style="width:33%;">
                    Bendahara Pengeluaran
                    <div class="ttd-space"></div>
                    <b>{{ $namaBP }}</b>
                    <div class="nip">NIP. {{ $nipBP }}</div>
                </td>
            </tr>
        </table>
    </div>
</section>

<!-- HALAMAN 3: SURAT PERMINTAAN MEMBAYAR (KOP) -->
<section class="print-page">
    <div class="page-pad-wide">
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

        <div class="center bold fs-11" style="margin-bottom:4px;">SURAT PERNYATAAN TANGGUNG JAWAB BELANJA</div>
        <div class="center" style="font-size:9pt;margin-bottom:14px;">Belanja Langsung (LS)</div>

        <table class="form-table mb-12" style="width:90%;">
            <tr>
                <td style="width:28%;">Nama SKPD</td>
                <td style="width:3%;">:</td>
                <td>Dinas Komunikasi dan Informatika</td>
            </tr>
            <tr>
                <td>No. SPP</td>
                <td>:</td>
                <td>{{ $noBuktiFormatted }}</td>
            </tr>
            <tr>
                <td>No. SPM SIPD</td>
                <td>:</td>
                <td>{{ $belanja->no_spm_sipd ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $tglText }}</td>
            </tr>
            <tr>
                <td>Jumlah Pembayaran</td>
                <td>:</td>
                <td>Rp. {{ rpls($belanja->total_nilai) }}</td>
            </tr>
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td>{{ ucwords(tbgls($belanja->total_nilai)) }} Rupiah</td>
            </tr>
        </table>

        <p class="justify" style="line-height:1.4;">
            Yang bertanda tangan di bawah ini Pengguna Anggaran Dinas Komunikasi dan Informatika
            Kabupaten Wonosobo, menyatakan bahwa saya bertanggung jawab penuh atas segala
            pengeluaran yang telah dibayar lunas oleh Bendahara Pengeluaran kepada yang berhak
            menerima dengan rincian sebagaimana tersebut dalam SPP-LS nomor {{ $noBuktiFormatted }}
            tanggal {{ $tglText }} sejumlah Rp. {{ rpls($belanja->total_nilai) }}
            ({{ ucwords(tbgls($belanja->total_nilai)) }} Rupiah).
        </p>

        <p class="justify mt-10" style="line-height:1.4;">
            Bukti-bukti pengeluaran yang sah sesuai dengan ketentuan peraturan perundang-undangan
            yang berlaku disimpan pada SKPD untuk kelengkapan administrasi dan keperluan pemeriksaan
            aparat pengawasan fungsional.
        </p>

        <div style="margin-top:30px;text-align:right;padding-right:40px;">Wonosobo, {{ $tglText }}</div>

        <table class="clean-signature" style="margin-top:10px;width:60%;margin-left:auto;">
            <tr>
                <td>
                    Pengguna Anggaran,<br>
                    Kepala Dinas Komunikasi dan Informatika<br>
                    Kabupaten Wonosobo
                    <div class="ttd-space-lg"></div>
                    <b>{{ $namaPA }}</b>
                    <div class="nip">NIP. {{ $nipPA }}</div>
                </td>
            </tr>
        </table>
    </div>
</section>

</body>
</html>
