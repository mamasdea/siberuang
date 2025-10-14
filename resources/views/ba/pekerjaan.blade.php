{{-- resources/views/ba/pekerjaan.blade.php --}}
@php
    use Carbon\Carbon;

    if (!function_exists('terbilang_id')) {
        function terbilang_id($n): string
        {
            $n = (int) $n;
            $a = [
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
                return $a[$n];
            }
            if ($n < 20) {
                return terbilang_id($n - 10) . ' belas';
            }
            if ($n < 100) {
                return terbilang_id(intval($n / 10)) . ' puluh' . ($n % 10 ? ' ' . terbilang_id($n % 10) : '');
            }
            if ($n < 200) {
                return 'seratus' . ($n - 100 ? ' ' . terbilang_id($n - 100) : '');
            }
            if ($n < 1000) {
                return terbilang_id(intval($n / 100)) . ' ratus' . ($n % 100 ? ' ' . terbilang_id($n % 100) : '');
            }
            if ($n < 2000) {
                return 'seribu' . ($n - 1000 ? ' ' . terbilang_id($n - 1000) : '');
            }
            if ($n < 1_000_000) {
                return terbilang_id(intval($n / 1000)) . ' ribu' . ($n % 1000 ? ' ' . terbilang_id($n % 1000) : '');
            }
            if ($n < 1_000_000_000) {
                return terbilang_id(intval($n / 1_000_000)) .
                    ' juta' .
                    ($n % 1_000_000 ? ' ' . terbilang_id($n % 1_000_000) : '');
            }
            if ($n < 1_000_000_000_000) {
                return terbilang_id(intval($n / 1_000_000_000)) .
                    ' miliar' .
                    ($n % 1_000_000_000 ? ' ' . terbilang_id($n % 1_000_000_000) : '');
            }
            return terbilang_id(intval($n / 1_000_000_000_000)) .
                ' triliun' .
                ($n % 1_000_000_000_000 ? ' ' . terbilang_id($n % 1_000_000_000_000) : '');
        }
    }
    if (!function_exists('idr')) {
        function idr($v, $p = 'Rp ')
        {
            return $p . number_format((float) $v, 0, ',', '.');
        }
    }

    Carbon::setLocale('id');

    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : null);
    $hariText = $tglObj ? $tglObj->translatedFormat('l') : '........';
    $hariAngkaHuruf = $tglObj ? ucfirst(terbilang_id((int) $tglObj->format('j'))) : '........';
    $bulanNama = $tglObj ? $tglObj->translatedFormat('F') : '........';
    $tahunAngkaHuruf = $tglObj ? ucwords(terbilang_id((int) $tglObj->format('Y'))) : '........';
    $tglText = $tglObj ? $tglObj->translatedFormat('d F Y') : '........';

    $nomorBA = $ba_nomor ?? '-';
    $nomorSP = $kontrak->nomor_kontrak ?? '-';
    $tglSP = isset($kontrak->tanggal_kontrak)
        ? Carbon::parse($kontrak->tanggal_kontrak)->translatedFormat('d F Y')
        : '-';
    $keperluan = $kontrak->uraian ?? 'Belanja Modal ...';
    $periodeBA = $realisasi->periode ?? null;
    $isTermin = ($realisasi->tipe ?? '') === 'termin';

    $pihak_kesatu_nama = $namaPA;
    $pihak_kesatu_jabatan1 = 'Kepala Dinas Komunikasi dan Informatika Kabupaten Wonosobo';
    $pihak_kesatu_jabatan2 = 'selaku Pejabat Pembuat Komitmen';
    $namaPA = $namaPA;
    $pihak_kedua_jabatan1 = 'Kepala Dinas Komunikasi dan Informatika Kabupaten Wonosobo';
    $pihak_kedua_jabatan2 = 'selaku Pengguna Anggaran';
    $alamat_kantor = 'Jl. Sabuk Alu No.2A Wonosobo';
    $nipPA = $nipPA;

    $items = $realisasi->items ?? collect();
    $sub = 0;
    foreach ($items as $it) {
        $sub += ((float) $it->kuantitas) * ((float) $it->harga);
    }
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>BA Pekerjaan - {{ $nomorBA }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: A4;
            margin: 1.4mm 14mm 14mm 14mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11.2pt;
            color: #000;
            line-height: 1.25;
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

        .mt-1 {
            margin-top: .3rem
        }

        .mt-2 {
            margin-top: .5rem
        }

        .mt-3 {
            margin-top: .7rem
        }

        .mt-4 {
            margin-top: 1rem
        }

        .mb-1 {
            margin-bottom: .3rem
        }

        .kop {
            display: grid;
            grid-template-columns: 80px 1fr;
            column-gap: 10px;
            align-items: center
        }

        .kop img {
            width: 90px;
            height: auto
        }

        .kop .instansi {
            font-weight: bold;
            font-size: 24px;
            line-height: 1.05
        }

        .kop .dinas {
            font-weight: bold;
            font-size: 22px;
            line-height: 1.05
        }

        .kop .alamat {
            font-size: 11pt;
            line-height: 1.05
        }

        .strip {
            border-bottom: 2px solid #000;
            margin: 6px 0 10px;
            grid-column: 1 / -1
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            font-size: 16px;
        }

        .numbering .num {
            display: inline-block;
            width: 20px;
            vertical-align: top
        }

        .numbering .cont {
            display: inline-block;
            width: calc(100% - 20px);
            vertical-align: top
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        .data td {
            padding: 0;
            vertical-align: top;
            line-height: 1.2
        }

        .data .lbl {
            width: 78px
        }

        .data .sep {
            width: 6px
        }

        .tbl th,
        .tbl td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        .tbl th {
            background: #f3f3f3
        }

        .sign2 {
            width: 100%;
            margin-top: 20px
        }

        .sign2 td {
            width: 50%;
            text-align: center;
            vertical-align: top
        }

        .blank {
            display: block;
            height: 82px
        }

        /* ruang tanda tangan dipendekkan */
        .u {
            text-decoration: underline;
            font-weight: bold
        }

        .pasal {
            text-align: center;
            font-weight: bold
        }
    </style>
</head>

<body>

    <div class="kop">
        <div class="center"><img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo"></div>
        <div class="center">
            <div class="instansi">PEMERINTAH KABUPATEN WONOSOBO</div>
            <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
            <div class="alamat">Jl. Sabuk Alu No.2 A Telp. (0286) 325112 / Fax 325112</div>
            <div class="alamat">Website: diskominfo.wonosobokab.go.id, email: diskominfo@wonosobokab.go.id,</div>
            <div class="alamat">WONOSOBO - 56311</div>
        </div>
        <div class="strip"></div>
    </div>

    <div class="title">BERITA ACARA SERAH TERIMA HASIL PEKERJAAN</div>
    <div class="center mt-1">Nomor : <strong>{{ $nomorBA }}</strong></div>

    <div class="mt-2 justify">
        Pada hari <strong>{{ $hariText }}</strong> tanggal <strong>{{ $hariAngkaHuruf }}</strong> bulan
        <strong>{{ $bulanNama }}</strong> tahun <strong>{{ $tahunAngkaHuruf }}</strong> bertempat di Dinas Komunikasi
        dan Informatika Kabupaten Wonosobo, telah diadakan serah terima hasil pekerjaan Belanja
        <strong>{{ $keperluan }}</strong>
        @if ($isTermin && $periodeBA)
            bulan
            <strong>{{ $periodeBA }}</strong>
        @endif antara:
    </div>

    {{-- PIHAK KESATU --}}
    <div class="mt-2 numbering">
        <table class="data" style="margin-left:0;">
            <tr>
                <td style="width:25px; vertical-align:top;"><strong>I.</strong></td>
                <td class="lbl">Nama</td>
                <td class="sep">:</td>
                <td><strong>{{ $pihak_kesatu_nama }}</strong></td>
            </tr>
            <tr>
                <td></td>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pihak_kesatu_jabatan1 }}<br>{{ $pihak_kesatu_jabatan2 }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat_kantor }}</td>
            </tr>
        </table>
        <div style="margin-left:28px;">Selanjutnya disebut <strong>PIHAK KESATU</strong>.</div>
    </div>

    {{-- PIHAK KEDUA --}}
    <div class="mt-2 numbering">
        <table class="data" style="margin-left:0;">
            <tr>
                <td style="width:25px; vertical-align:top;"><strong>II.</strong></td>
                <td class="lbl">Nama</td>
                <td class="sep">:</td>
                <td><strong>{{ $namaPA }}</strong></td>
            </tr>
            <tr>
                <td></td>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $pihak_kedua_jabatan1 }}<br>{{ $pihak_kedua_jabatan2 }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat_kantor }}</td>
            </tr>
        </table>
        <div style="margin-left:28px;">Selanjutnya disebut <strong>PIHAK KEDUA</strong>.</div>
    </div>

    <div class="justify">
        Dengan ini <strong>PIHAK KESATU</strong> menyerahkan hasil pekerjaan kepada <strong>PIHAK KEDUA</strong>
        sesuai Dokumen Surat Pesanan Nomor: <strong>{{ $nomorSP }}</strong> tanggal
        <strong>{{ $tglSP }}</strong>
        berupa rincian berikut:
    </div>

    <table class="tbl mt-1">
        <thead>
            <tr>
                <th style="width:42px">No</th>
                <th>Nama Barang/Jasa</th>
                <th style="width:110px">Harga Item</th>
                <th style="width:80px">Qty</th>
                <th style="width:130px">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $it)
                @php
                    $harga = (float) $it->harga;
                    $qty = (float) $it->kuantitas;
                    $line = $harga * $qty;
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $it->nama_barang }}</td>
                    <td class="right">{{ idr($harga) }}</td>
                    <td class="center">{{ number_format($qty, 2, ',', '.') }} {{ $it->satuan }}</td>
                    <td class="right">{{ idr($line) }}</td>
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
                <th class="right">{{ idr($sub) }}</th>
            </tr>
            {{-- PPN 11% dihapus sesuai permintaan --}}
        </tfoot>
    </table>

    <div class="justify">

        Berdasarkan pemeriksaan atas Hasil Pekerjaan yang diserahkan oleh <strong>PIHAK KESATU</strong> yang
        berupa pekerjaan {{ $keperluan }}. Hasil pekerjaan telah diselesaikan sesuai spesifikasi dalam Surat
        Pesanan/Perjanjian dan dinyatakan <strong>baik dan lengkap</strong>.
        Demikian Berita Acara Penerimaan Barang ini dibuat dengan sebenarnya dalam rangkap 3
        (tiga) untuk dipergunakan seperlunya.
    </div>

    <table class="sign2">
        <tr>
            <td>
                <div class="mb-1">PIHAK KESATU</div>
                <div>{{ $pihak_kesatu_jabatan1 }}</div>
                <div>Kabupaten Wonosobo</div>
                <div>{{ $pihak_kesatu_jabatan2 }}</div>
                <span class="blank"></span>
                <div class="u">{{ $pihak_kesatu_nama }}</div>
                <div>{{ $nipPA }}</div>
            </td>
            <td>
                <div class="mb-1">PIHAK KEDUA</div>
                <div>{{ $pihak_kedua_jabatan1 }}</div>
                <div>Kabupaten Wonosobo</div>
                <div>{{ $pihak_kedua_jabatan2 }}</div>
                <span class="blank"></span>
                <div class="u">{{ $namaPA }}</div>
                <div>{{ $nipPA }}</div>
            </td>
        </tr>
    </table>

    @if (($auto ?? false) === true)
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>

</html>
