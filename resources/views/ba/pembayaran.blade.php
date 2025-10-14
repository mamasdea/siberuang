@php
    use Carbon\Carbon;

    if (!function_exists('terbilang_id')) {
        function terbilang_id($nilai): string
        {
            $nilai = (int) $nilai;
            $satuan = [
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
            if ($nilai < 12) {
                return $satuan[$nilai];
            }
            if ($nilai < 20) {
                return terbilang_id($nilai - 10) . ' belas';
            }
            if ($nilai < 100) {
                return terbilang_id($nilai / 10) . ' puluh' . ($nilai % 10 ? ' ' . terbilang_id($nilai % 10) : '');
            }
            if ($nilai < 200) {
                return 'seratus ' . terbilang_id($nilai - 100);
            }
            if ($nilai < 1000) {
                return terbilang_id($nilai / 100) . ' ratus' . ($nilai % 100 ? ' ' . terbilang_id($nilai % 100) : '');
            }
            if ($nilai < 2000) {
                return 'seribu ' . terbilang_id($nilai - 1000);
            }
            if ($nilai < 1000000) {
                return terbilang_id($nilai / 1000) . ' ribu' . ($nilai % 1000 ? ' ' . terbilang_id($nilai % 1000) : '');
            }
            if ($nilai < 1000000000) {
                return terbilang_id($nilai / 1000000) .
                    ' juta' .
                    ($nilai % 1000000 ? ' ' . terbilang_id($nilai % 1000000) : '');
            }
            return $nilai;
        }
    }
    if (!function_exists('idr')) {
        function idr($angka)
        {
            return 'Rp. ' . number_format((float) $angka, 0, ',', '.');
        }
    }

    Carbon::setLocale('id');
    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : now());
    $hariText = $tglObj->translatedFormat('l');
    $tglText = $tglObj->translatedFormat('d F Y');
    $hariHuruf = ucfirst(terbilang_id($tglObj->day));
    $bulanNama = $tglObj->translatedFormat('F');
    $tahunHuruf = ucwords(terbilang_id($tglObj->year));

    $nomorBA = $ba_nomor ?? '-';
    $nomorKontrak = $kontrak->nomor_kontrak ?? '-';
    $tglKontrak = $kontrak->tanggal_kontrak ? Carbon::parse($kontrak->tanggal_kontrak)->translatedFormat('d F Y') : '-';
    $keperluan = $kontrak->uraian ?? '-';
    $namaPerusahaan = $kontrak->nama_perusahaan ?? '-';
    $alamatPerusahaan = $kontrak->alamat_perusahaan ?? '-';
    $namaPimpinan = $kontrak->nama_pimpinan ?? '-';
    $nominal = $realisasi->nominal ?? 0;

    $nama_bank = $kontrak->nama_bank ?? '-';
    $nomor_rekening = $kontrak->nomor_rekening ?? '-';
    $nama_pemilik_rekening = $kontrak->nama_pemilik_rekening ?? '-';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pembayaran</title>
    <style>
        @page {
            size: A4;
            margin: 1.5mm 18mm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            line-height: 1.2;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .justify {
            text-align: justify;
        }

        .right {
            text-align: right;
        }

        .kop-wrap {
            display: grid;
            grid-template-columns: 90px 1fr;
            align-items: center;
            column-gap: 14px;
        }

        .kop-logo img {
            width: 90px;
        }

        .kop-text {
            text-align: center;
        }

        .kop-text .instansi {
            font-size: 24px;
            font-weight: bold;
        }

        .kop-text .dinas {
            font-size: 22px;
            font-weight: bold;
        }

        .kop-text .alamat {
            font-size: 11pt;
            line-height: 1.2;
        }

        .kop-strip {
            border-bottom: 2px solid #000;
            margin: 8px 0 12px;
            grid-column: 1 / -1;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .blank {
            height: 90px;
            display: block;
        }

        .signature td {
            text-align: center;
            vertical-align: top;
            padding-top: 20px;
        }

        .signature-name {
            text-decoration: underline;
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop-wrap">
        <div class="kop-logo center"><img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo"></div>
        <div class="kop-text">
            <div class="instansi">PEMERINTAH KABUPATEN WONOSOBO</div>
            <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
            <div class="alamat">Jl. Sabuk Alu No.2A Telp. (0286) 325112 / Fax 325112</div>
            <div class="alamat">Website: diskominfo.wonosobokab.go.id, email: diskominfo@wonosobokab.go.id</div>
            <div class="alamat">WONOSOBO - 56311</div>
        </div>
        <div class="kop-strip"></div>
    </div>

    <div class="center">
        <strong style="font-size:12pt;text-decoration:underline;">BERITA ACARA PEMBAYARAN</strong><br>
        Nomor : <strong>{{ $nomorBA }}</strong>
    </div>

    <p class="justify mt-3">
        Pada hari ini, <strong>{{ $hariText }}</strong> tanggal <strong>{{ $hariHuruf }}</strong> bulan
        <strong>{{ $bulanNama }}</strong> tahun <strong>{{ $tahunHuruf }}</strong>,
        yang bertanda tangan di bawah ini :
    </p>

    <table style="border:none;line-height:1.25;margin-bottom:6px;">
        <tr>
            <td style="width:25px;">1.</td>
            <td style="width:90px;">Nama</td>
            <td style="width:10px;">:</td>
            <td><strong>{{ $namaPA }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>Kepala Dinas Komunikasi dan Informatika selaku Pengguna Anggaran Dinas Komunikasi dan Informatika
                Kabupaten Wonosobo</td>
        </tr>
        <tr>
            <td></td>
            <td>Alamat</td>
            <td>:</td>
            <td>Jl. Sabuk Alu No. 2A Wonosobo</td>
        </tr>
    </table>
    <p style="margin-left:35px;">Untuk selanjutnya disebut <strong>PIHAK PERTAMA</strong>.</p>

    <table style="border:none;line-height:1.25;margin-bottom:6px;">
        <tr>
            <td style="width:25px;">2.</td>
            <td style="width:90px;">Nama</td>
            <td style="width:10px;">:</td>
            <td><strong>{{ $namaPimpinan }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>Direktur {{ $namaPerusahaan }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $alamatPerusahaan }}</td>
        </tr>
    </table>
    <p style="margin-left:35px;">Untuk selanjutnya disebut <strong>PIHAK KEDUA</strong>.</p>

    <p class="justify mt-3">
        <strong>Berdasarkan:</strong><br>
        1) Nomor & Tanggal Kontrak : {{ $nomorKontrak }} tanggal {{ $tglKontrak }}.<br>
        2) Uraian Pekerjaan : {{ $keperluan }}.<br>
        3) Biaya Pekerjaan : {{ idr($nominal) }},00 ({{ ucwords(terbilang_id($nominal)) }} Rupiah).<br>
        4) Berita Acara Pemeriksaan dan Serah Terima Barang/Jasa telah dilakukan dengan hasil sesuai kontrak.<br>
        5) Sesuai dengan Kontrak dan Mekanisme Pembayaran, PIHAK KEDUA berhak menerima pembayaran sebesar
        <strong>{{ idr($nominal) }},00</strong> ({{ ucwords(terbilang_id($nominal)) }} Rupiah).<br>
        PIHAK KEDUA sepakat atas pembayaran tersebut dan dibayarkan pada <strong>{{ $nama_bank }}:
            {{ $nomor_rekening }}</strong>
        atas nama <strong>{{ $nama_pemilik_rekening }}</strong>.
    </p>

    <p class="justify">
        Berita Acara Pembayaran ini dibuat untuk dipergunakan seperlunya.
    </p>

    <table class="signature mt-3" style="width:100%;">
        <tr>
            <td></td>
            <td>Wonosobo, {{ $tglText }}<br></td>
        </tr>
        <tr>
            <td style="width:50%;">
                <strong>PIHAK KEDUA</strong><br>
                {{ $namaPerusahaan }}
                <div class="blank"></div>
                <span class="signature-name">{{ $namaPimpinan }}</span>
                Direktur
            </td>
            <td style="width:50%;">
                <strong>PIHAK PERTAMA</strong><br>
                Kepala Dinas Komunikasi dan Informatika<br>
                Kabupaten Wonosobo<br>
                selaku Pengguna Anggaran
                <div class="blank"></div>
                <span class="signature-name">{{ $namaPA }}</span>
                NIP. {{ $nipPA }}
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
