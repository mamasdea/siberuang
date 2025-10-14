{{-- resources/views/ba/pemeriksaan.blade.php --}}
@php
    use Carbon\Carbon;

    // --- helper terbilang angka Indonesia (hingga triliunan cukup) ---
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
                return terbilang_id(intval($nilai / 10)) .
                    ' puluh' .
                    ($nilai % 10 ? ' ' . terbilang_id($nilai % 10) : '');
            }
            if ($nilai < 200) {
                return 'seratus' . ($nilai - 100 ? ' ' . terbilang_id($nilai - 100) : '');
            }
            if ($nilai < 1000) {
                return terbilang_id(intval($nilai / 100)) .
                    ' ratus' .
                    ($nilai % 100 ? ' ' . terbilang_id($nilai % 100) : '');
            }
            if ($nilai < 2000) {
                return 'seribu' . ($nilai - 1000 ? ' ' . terbilang_id($nilai - 1000) : '');
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
            return terbilang_id(intval($nilai / 1000000000000)) .
                ' triliun' .
                ($nilai % 1000000000000 ? ' ' . terbilang_id($nilai % 1000000000000) : '');
        }
    }

    // --- helper format IDR ---
    if (!function_exists('idr')) {
        function idr($angka, $prefix = 'Rp. '): string
        {
            $angka = (float) $angka;
            return $prefix . number_format($angka, 0, ',', '.');
        }
    }

    Carbon::setLocale('id');

    // Mengambil data dari variabel yang disalurkan, jika tidak ada, gunakan default '........'
    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : null);

    $hariText = $tglObj ? $tglObj->translatedFormat('l') : '........';
    $tglText = $tglObj ? $tglObj->translatedFormat('d F Y') : '........';

    // === tanggal versi huruf ===
    $hariAngkaHuruf = '........';
    $bulanNama = '........';
    $tahunAngkaHuruf = '........';

    if ($tglObj) {
        $hariAngka = (int) $tglObj->format('j');
        $bulanNama = $tglObj->translatedFormat('F');
        $tahunAngka = (int) $tglObj->format('Y');

        $hariAngkaHuruf = ucfirst(terbilang_id($hariAngka));
        $tahunAngkaHuruf = ucwords(terbilang_id($tahunAngka));
    }

    $nomorBA = $ba_nomor ?? '500.12.11/        /2025';
    $nomorKontrak = $kontrak->nomor_kontrak ?? '500.12.11/          /Diskominfo';
    $tglKontrak = isset($kontrak->tanggal_kontrak)
        ? Carbon::parse($kontrak->tanggal_kontrak)->translatedFormat('d F Y')
        : '                          ';

    $namaPerusahaan = $kontrak->nama_perusahaan ?? '                                   ';
    $bentukPerusahaan = $kontrak->bentuk_perusahaan ?? '                     ';
    $alamatPerusahaan = $kontrak->alamat_perusahaan ?? '                                          ';
    $namaPimpinan = $kontrak->nama_pimpinan ?? '                        ';
    $progresFisik = is_null($realisasi->progres_fisik ?? null) ? 100 : (float) $realisasi->progres_fisik;
    $keperluan = $kontrak->keperluan ?? '__________________________________________________________';
@endphp


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Berita Acara Pemeriksaan - {{ $nomorBA }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: A4;
            /* Margin standar dokumen formal */
            margin: 1.8mm 18mm 1.8mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* Alignment Helpers */
        .justify {
            text-align: justify;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Margin Helpers */
        .mt-4 {
            margin-top: 1.25rem;
        }

        .mt-3 {
            margin-top: .9rem;
        }

        .mt-2 {
            margin-top: .6rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Data Table (Nama, Jabatan, Alamat) */
        .data-table {
            width: 100%;
            margin-bottom: 0;
            margin-top: 5px;
        }

        .data-table td {
            border: none;
            padding: 0;
            line-height: 1.2;
            vertical-align: top;
        }

        .data-table .label {
            width: 80px;
            /* Lebar untuk 'Nama', 'Jabatan', 'Alamat' */
        }

        .data-table .separator {
            width: 5px;
            /* Lebar untuk ':' */
            padding-right: 5px;
        }

        /* === Styles for Hanging Indent (Romawi I. & II.) === */
        .romawi-line {
            text-align: justify;
            padding-left: 25px;
            /* Total blok indent (padding-kiri) */
            text-indent: -25px;
            /* Mengeluarkan marker I./II. ke kiri */
            line-height: 1.5;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        /* Wrapper untuk tabel data di bawah I. agar rata dengan teks setelah I. */
        .romawi-data-wrapper {
            margin-left: 25px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .romawi-data-wrapper p {
            margin: 5px 0;
        }

        /* ================================================= */

        /* Signature Block styles Page 1 */
        .signature-block-page1 {
            width: 100%;
            margin-top: 30px;
        }

        .signature-block-page1 td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0;
        }

        .blank-space-signature {
            display: block;
            height: 80px;
            /* Space for actual signature */
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
            margin-top: 5px;
        }

        /* Kop Surat styles (as before) */
        .kop-wrap {
            display: grid;
            grid-template-columns: 90px 1fr;
            align-items: center;
            column-gap: 14px;
        }

        .kop-logo img {
            width: 90px;
            height: auto;
        }

        .kop-text {
            text-align: center;
        }

        .kop-text .instansi {
            font-size: 24px;
            font-weight: bold;
            line-height: 1.1;
        }

        .kop-text .dinas {
            font-size: 22px;
            font-weight: bold;
            line-height: 1.1;
        }

        .kop-text .alamat {
            font-size: 11pt;
            line-height: 1.1;
        }

        .kop-strip {
            border-bottom: 2.2px solid #000;
            margin: 8px 0 12px;
            grid-column: 1 / -1;
        }

        /* Styles for Lampiran Table (Page 2) */
        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .table th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            font-size: 16px;
        }
    </style>
</head>

<body>

    {{-- ======================= PAGE 1 ======================= --}}
    <div class="page">
        {{-- KOP SURAT DENGAN LOGO --}}
        <div class="kop-wrap">
            <div class="kop-logo center">
                <img src="{{ asset('logo-pemkab-hp.png') }}" alt="Logo Pemerintah Kabupaten">
            </div>
            <div class="kop-text">
                <div class="instansi">PEMERINTAH KABUPATEN WONOSOBO</div>
                <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                <div class="alamat">Jl. Sabuk Alu No.2 A Telp. (0286) 325112/ Fax 325112</div>
                <div class="alamat">Website: diskominfo.wonosobokab.go.id, email: diskominfo@wonosobokab.go.id,</div>
                <div class="alamat">WONOSOBO 56311</div>
            </div>
            <div class="kop-strip"></div>
        </div>

        <div class="center">
            <div class="title"><strong>BERITA ACARA PEMERIKSAAAN HASIL PEKERJAAN</strong></div>
            <div class="mt-1">Nomor : <strong>{{ $nomorBA }}</strong></div>
        </div>

        <div class="mt-3">

            <p class="romawi-line">
                I. Yang bertanda tangan di bawah ini:
            </p>
            <div class="romawi-data-wrapper">
                <table class="data-table">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="separator">:</td>
                        <td><strong>{{ $namaPimpinan }}</strong></td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>Direktur {{ $namaPerusahaan }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td>{{ $alamatPerusahaan }}</td>
                    </tr>
                </table>
                <p>Yang selanjutnya disebut <strong>PIHAK PERTAMA:</strong></p>

                <table class="data-table">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="separator">:</td>
                        <td><strong>{{ $namaPA }}</strong></td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>Pejabat Pembuat Komitmen</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td>Jl. Sabuk Alu No 2 A Wonosobo</td>
                    </tr>
                </table>
                <p class="justify">
                    Dalam hal ini bertindak atas nama dan untuk Dinas Komunikasi dan Informatika Kabupaten Wonosobo.
                    <br>Yang selanjutnya disebut <strong>PIHAK KEDUA.</strong>
                </p>
            </div>

            <p class="romawi-line">
                II. Pada hari <strong>{{ $hariText }}</strong> tanggal <strong>{{ $hariAngkaHuruf }}</strong> bulan
                <strong>{{ $bulanNama }}</strong> tahun
                <strong>{{ $tahunAngkaHuruf }}</strong>, PIHAK PERTAMA telah menyerahkan hasil
                Pekerjaan <strong>{{ $keperluan }}</strong> kepada PIHAK KEDUA. PIHAK KEDUA
                telah memeriksa dan menerima penyerahan hasil tersebut telah selesai
                {{ number_format($progresFisik, 0, ',', '.') }}% (1 Paket), baik dan lengkap sesuai dengan Dokumen
                Surat Pesanan Nomor:
                {{ $nomorKontrak }} tanggal {{ $tglKontrak }}.
            </p>

        </div>

        <div class="mt-3 justify">
            Demikian berita acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
        </div>

        <table class="signature-block-page1 mt-4">
            <tr>
                <td class="center mb-4">
                    <div><strong>PIHAK PERTAMA</strong></div>
                    <div>{{ $namaPerusahaan }}</div>
                </td>
                <td class="center mb-4">
                    <div><strong>PIHAK KEDUA</strong></div>
                    <div>Pejabat Pembuat Komitmen</div>
                    <div>Dinas Komunikasi dan Informatika</div>
                    <div>Kabupaten Wonosobo</div>
                </td>
            </tr>
            <tr>
                <td class="center">
                    <span class="blank-space-signature"></span>
                    <span class="signature-name">{{ $namaPimpinan }}</span>
                    <div>Direktur</div>
                </td>
                <td class="center">
                    <span class="blank-space-signature"></span>
                    <span class="signature-name">{{ $namaPA }}</span>
                    <div>NIP. {{ $nipPA }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ======================= PAGE 2 (Lampiran) ======================= --}}
    <div class="page lampiran">
        <div class="page">
            <div class="left">
                <div class="lead">Lampiran BAPP No. {{ $nomorBA }}</div>
                <div>Tanggal: {{ $tglText }}</div>
            </div>

            <div class="mt-3 mb-2 center"><strong>Daftar Rincian</strong> — {{ $keperluan }}</div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama Barang/Jasa</th>
                        <th style="width:120px;">Harga Item</th>
                        <th style="width:90px;">Qty</th>
                        <th style="width:140px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grand = 0; @endphp
                    {{-- Placeholder loop for items. Data should come from $realisasi->items --}}
                    @forelse($realisasi->items ?? [] as $idx => $it)
                        @php
                            $harga = (float) ($it->harga ?? 0);
                            $kuantitas = (float) ($it->kuantitas ?? 0);
                            $line = $kuantitas * $harga;
                            $grand += $line;
                        @endphp
                        <tr>
                            <td class="center">{{ $idx + 1 }}</td>
                            <td>{{ $it->nama_barang ?? 'Nama Barang/Jasa' }}</td>
                            <td class="right">{{ idr($harga) }}</td>
                            <td class="center">{{ number_format($kuantitas, 2, ',', '.') }}
                                {{ $it->satuan ?? 'Unit' }}
                            </td>
                            <td class="right">{{ idr($line) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="center">— Tidak ada rincian data (Isi data item di
                                $realisasi->items)
                                —</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="right">Jumlah</th>
                        <th class="right">{{ idr($grand) }}</th>
                    </tr>
                </tfoot>
            </table>
            <table class="signature-block-page1 mt-4">

                <th>
                    <div class="center">Wonosobo, {{ $tglText }}</div>
                </th>

                <tr>
                    <td class="center" style="width: 50%;">

                        <div class="mb-1">Menerima dan menyetujui</div>
                        <div><strong>PIHAK KEDUA</strong></div>
                        <div>Pejabat Pembuat Komitmen</div>
                        <div>Dinas Komunikasi dan Informatika</div>
                        <div>Kabupaten Wonosobo</div>
                    </td>
                    <td class="center" style="width: 50%;">
                        <div>&nbsp;</div>
                        <div>&nbsp;</div>
                        <div><strong>PIHAK PERTAMA</strong></div>
                        <div>Untuk dan atas nama</div>
                        <div>{{ $namaPerusahaan }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="center">
                        <span class="blank-space-signature"></span>
                        <span class="signature-name">{{ $namaPA }}</span>
                        <div>NIP. {{ $nipPA }}</div>
                    </td>
                    <td class="center">
                        <span class="blank-space-signature"></span>
                        <span class="signature-name">{{ $namaPimpinan }}</span>
                        <div>Direktur</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if ($auto ?? false)
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>

</html>
