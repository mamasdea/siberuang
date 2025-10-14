{{-- resources/views/ba/administratif.blade.php --}}
@php
    use Carbon\Carbon;

    if (!function_exists('terbilang_id')) {
        function terbilang_id($nilai): string
        {
            $nilai = (int) $nilai;
            $sat = [
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
                return $sat[$nilai];
            }
            if ($nilai < 20) {
                return terbilang_id($nilai - 10) . ' belas';
            }
            if ($nilai < 100) {
                return terbilang_id(intval($nilai / 10)) .
                    ' puluh' .
                    ($nilai % 10 ? ' ' . terbilang_id($nilai % 10) : '');
            }
            if ($nilai < 200) {
                return 'seratus' . ($nilai > 100 ? ' ' . terbilang_id($nilai - 100) : '');
            }
            if ($nilai < 1000) {
                return terbilang_id(intval($nilai / 100)) .
                    ' ratus' .
                    ($nilai % 100 ? ' ' . terbilang_id($nilai % 100) : '');
            }
            if ($nilai < 2000) {
                return 'seribu' . ($nilai > 1000 ? ' ' . terbilang_id($nilai - 1000) : '');
            }
            if ($nilai < 1000000) {
                return terbilang_id(intval($nilai / 1000)) .
                    ' ribu' .
                    ($nilai % 1000 ? ' ' . terbilang_id($nilai % 1000) : '');
            }
            if ($nilai < 1000000000) {
                return terbilang_id(intval($nilai / 1000000)) .
                    ' juta' .
                    ($nilai % 1000000 ? ' ' . terbilang_id($nilai % 1000000) : '');
            }
            if ($nilai < 1000000000000) {
                return terbilang_id(intval($nilai / 1000000000)) .
                    ' miliar' .
                    ($nilai % 1000000000 ? ' ' . terbilang_id($nilai % 1000000000) : '');
            }
            return $nilai;
        }
    }

    Carbon::setLocale('id');

    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : null);
    $hariText = $tglObj?->translatedFormat('l') ?? '........';
    $tglHuruf = $tglObj ? ucfirst(terbilang_id((int) $tglObj->format('j'))) : '........';
    $bulanNama = $tglObj?->translatedFormat('F') ?? '........';
    $tahunHuruf = $tglObj ? ucwords(terbilang_id((int) $tglObj->format('Y'))) : '........';
    $tglText = $tglObj?->translatedFormat('d F Y') ?? '........';
    $tahunanggaran = $tglObj?->translatedFormat('Y') ?? '........';

    $nomorBA = $ba_nomor ?? '-';
    $keperluan = $kontrak->keperluan ?? '-';
    if (($realisasi->tipe ?? '') === 'termin' && !empty($realisasi->periode)) {
        $keperluan .= ' bulan ' . $realisasi->periode;
    }

    $namaKepala = 'KHRISTIANA DHEWI, SE., MM.';
    $nipKepala = 'NIP. 19741130 199903 2 005';

    $checks = [
        ['Jenis Dokumen' => 'Dokumen Program/Penganggaran', 'Keterangan' => 'Ada/Tidak'],
        ['Jenis Dokumen' => 'Dokumen Perencanaan Pengadaan', 'Keterangan' => 'Ada/Tidak'],
        ['Jenis Dokumen' => 'RUP/SIRUP', 'Keterangan' => 'Ada/Tidak'],
        ['Jenis Dokumen' => 'Dokumen Persiapan Pengadaan', 'Keterangan' => 'Ada/Tidak'],
        ['Jenis Dokumen' => 'Dokumen Kontrak', 'Keterangan' => 'Ada/Tidak'],
        ['Jenis Dokumen' => 'Dokumen Serah Terima Hasil Pekerjaan', 'Keterangan' => 'Ada/Tidak'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Berita Acara Administratif</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 18mm 15mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.25;
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

        .kop-wrap {
            display: grid;
            grid-template-columns: 90px 1fr;
            column-gap: 14px;
            align-items: center
        }

        .kop-logo img {
            width: 78px;
            height: auto
        }

        .kop-text {
            text-align: center
        }

        .kop-text .instansi {
            font-size: 18px;
            font-weight: bold
        }

        .kop-text .dinas {
            font-size: 17px;
            font-weight: bold
        }

        .kop-text .alamat {
            font-size: 10.5pt;
            line-height: 1.1
        }

        .kop-strip {
            border-bottom: 2px solid #000;
            margin: 8px 0 12px;
            grid-column: 1/-1
        }

        .title {
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
            font-size: 15px
        }

        .mt-2 {
            margin-top: .6rem
        }

        .mt-3 {
            margin-top: .9rem
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 4px 6px
        }

        .table th {
            background: #f0f0f0;
            text-align: center
        }

        .signature td {
            text-align: center;
            vertical-align: top;
            padding-top: 20px
        }

        .blank {
            display: block;
            height: 80px
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
            margin-top: 8px
        }
    </style>
</head>

<body>

    {{-- === KOP SURAT === --}}
    <div class="kop-wrap">
        <div class="kop-logo center">
            <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemerintah Kabupaten">
        </div>
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
        <div class="title">BERITA ACARA ADMINISTRATIF</div>
        <div class="mt-1">Nomor : <strong>{{ $nomorBA }}</strong></div>
    </div>

    <p class="justify mt-2">
        Pada hari <strong>{{ $hariText }}</strong> tanggal <strong>{{ $tglHuruf }}</strong> bulan
        <strong>{{ $bulanNama }}</strong> tahun <strong>{{ $tahunHuruf }}</strong> bertempat di
        Dinas Komunikasi dan Informatika Kabupaten Wonosobo, telah dilakukan pemeriksaan administratif
        terhadap hasil Pekerjaan <strong>{{ $keperluan }}</strong> tahun anggaran
        <strong>{{ $tahunanggaran }}</strong> oleh Pejabat
        Pembuat Komitmen Dinas
        Komunikasi dan Informatika Kabupaten Wonosobo dengan hasil sebagai berikut:
    </p>

    <div class="center mt-2"><strong>DAFTAR PEMERIKSAAN DOKUMEN ADMINISTRATIF</strong></div>

    <table class="table mt-1">
        <thead>
            <tr>
                <th style="width:45px;">No</th>
                <th>Jenis Dokumen</th>
                <th style="width:140px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($checks as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $row['Jenis Dokumen'] }}</td>
                    <td class="center">{{ $row['Keterangan'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="justify mt-2">
        Berdasarkan hasil pemeriksaan administratif tersebut, seluruh dokumen dinyatakan lengkap dan sesuai
        ketentuan yang berlaku. Berita Acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <table class="signature mt-3">
        <tr>
            <td style="width:50%"></td>
            <td style="width:50%">
                Wonosobo, {{ $tglText }}<br>
                Mengetahui;<br>
                <strong>Kepala Dinas Komunikasi dan Informatika</strong><br>
                Kabupaten Wonosobo<br>
                selaku Pejabat Pembuat Komitmen
                <div class="blank"></div>
                <span class="signature-name">{{ $namaKepala }}</span>
                {{ $nipKepala }}
            </td>
        </tr>
    </table>

    @if ($auto ?? false)
        <script>
            window.addEventListener('load', () => window.print())
        </script>
    @endif
</body>

</html>
