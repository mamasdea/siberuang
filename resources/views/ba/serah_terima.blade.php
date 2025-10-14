{{-- resources/views/ba/serah_terima.blade.php --}}
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
                return terbilang_id(intval($nilai / 10)) .
                    ' puluh' .
                    ($nilai % 10 ? ' ' . terbilang_id($nilai % 10) : '');
            }
            if ($nilai < 200) {
                return 'seratus ' . terbilang_id($nilai - 100);
            }
            if ($nilai < 1000) {
                return terbilang_id(intval($nilai / 100)) .
                    ' ratus' .
                    ($nilai % 100 ? ' ' . terbilang_id($nilai % 100) : '');
            }
            if ($nilai < 2000) {
                return 'seribu ' . terbilang_id($nilai - 1000);
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
            return $nilai;
        }
    }

    if (!function_exists('idr')) {
        function idr($angka, $prefix = 'Rp ')
        {
            return $prefix . number_format((float) $angka, 0, ',', '.');
        }
    }

    Carbon::setLocale('id');

    $tglObj = $ba_tanggal ?? ($realisasi->tanggal ? Carbon::parse($realisasi->tanggal) : null);
    $hariText = $tglObj?->translatedFormat('l') ?? '........';
    $tglText = $tglObj?->translatedFormat('d F Y') ?? '........';
    $hariAngkaHuruf = $tglObj ? ucfirst(terbilang_id((int) $tglObj->format('j'))) : '........';
    $bulanNama = $tglObj?->translatedFormat('F') ?? '........';
    $tahunHuruf = $tglObj ? ucwords(terbilang_id((int) $tglObj->format('Y'))) : '........';

    $nomorBA = $ba_nomor ?? '-';
    $nomorKontrak = $kontrak->nomor_kontrak ?? '-';
    $tglKontrak = $kontrak->tanggal_kontrak ? Carbon::parse($kontrak->tanggal_kontrak)->translatedFormat('d F Y') : '-';

    $namaPerusahaan = $kontrak->nama_perusahaan ?? '-';
    $alamatPerusahaan = $kontrak->alamat_perusahaan ?? '-';
    $namaPimpinan = $kontrak->nama_pimpinan ?? '-';
    $keperluan = $kontrak->uraian ?? '-';

    // Tambah "bulan {{ $periode }}" jika tipe termin
    if (($realisasi->tipe ?? '') === 'termin' && !empty($realisasi->periode)) {
        $keperluan .= ' bulan ' . $realisasi->periode;
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Berita Acara Serah Terima Barang/Jasa</title>
    <style>
        @page {
            size: A4;
            margin: 1.8mm 18mm 1.8mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.2;
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

        .page {
            page-break-after: always
        }

        .page:last-child {
            page-break-after: auto
        }

        .kop-wrap {
            display: grid;
            grid-template-columns: 90px 1fr;
            align-items: center;
            column-gap: 14px
        }

        .kop-logo img {
            width: 90px;
            height: auto
        }

        .kop-text {
            text-align: center
        }

        .kop-text .instansi {
            font-size: 24px;
            font-weight: bold
        }

        .kop-text .dinas {
            font-size: 22px;
            font-weight: bold
        }

        .kop-text .alamat {
            font-size: 11pt;
            line-height: 1.2
        }

        .kop-strip {
            border-bottom: 2px solid #000;
            margin: 8px 0 12px;
            grid-column: 1/-1
        }

        .mt-3 {
            margin-top: 1rem
        }

        .mt-4 {
            margin-top: 1.5rem
        }

        .mb-2 {
            margin-bottom: .6rem
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px
        }

        .table th {
            text-align: center;
            background: #f0f0f0
        }

        .signature td {
            text-align: center;
            vertical-align: top;
            padding-top: 20px
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
            margin-top: 10px
        }

        .blank {
            height: 90px;
            display: block
        }

        .pasal-title {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 5px
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            font-size: 16px;
        }

        /* ====== Identitas Pihak (rapi & sejajar) ====== */
        .ident {
            width: 100%;
            border-collapse: collapse;
            line-height: 1.15;
            /* agak rapat */
            margin-bottom: 6px;
        }

        .ident td {
            padding: 0 0 2px 0;
            vertical-align: top;
        }

        .ident .no {
            width: 25px;
        }

        .ident .lbl {
            width: 90px;
        }

        .ident .sep {
            width: 10px;
        }

        .after-note {
            margin: 2px 0 8px 35px;
        }

        /* sejajarkan dg kolom teks */
    </style>
</head>

<body>

    <div class="page">
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
            <strong class="title">BERITA ACARA SERAH TERIMA BARANG/JASA</strong><br>
            Nomor : <strong>{{ $nomorBA }}</strong>
        </div>

        <div class="justify mt-3">
            Pada hari <strong>{{ $hariText }}</strong> tanggal <strong>{{ $hariAngkaHuruf }}</strong>
            bulan <strong>{{ $bulanNama }}</strong> tahun <strong>{{ $tahunHuruf }}</strong>,
            kami yang bertanda tangan di bawah ini:
        </div>

        <div class="mt-2">
            {{-- PIHAK KESATU --}}
            <table class="ident">
                <tr>
                    <td class="no">1.</td>
                    <td class="lbl">Nama</td>
                    <td class="sep">:</td>
                    <td><strong>{{ $namaPimpinan }}</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>Direktur</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $alamatPerusahaan }}</td>
                </tr>
            </table>
            <p class="after-note">Untuk selanjutnya disebut <strong>PIHAK KESATU</strong>.</p>

            {{-- PIHAK KEDUA --}}
            <table class="ident">
                <tr>
                    <td class="no">2.</td>
                    <td class="lbl">Nama</td>
                    <td class="sep">:</td>
                    <td><strong>{{ $namaPA }}</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>
                        Kepala Dinas Komunikasi dan Informatika Kabupaten Wonosobo<br>
                        <span>selaku Pejabat Pembuat Komitmen</span>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>Jl. Sabuk Alu No.2A Wonosobo</td>
                </tr>
            </table>
            <p class="after-note">Untuk selanjutnya disebut <strong>PIHAK KEDUA</strong>.</p>
        </div>


        <p class="justify">
            Berdasarkan Dokumen Surat Pesanan Nomor: <strong>{{ $nomorKontrak }}</strong> tanggal
            <strong>{{ $tglKontrak }}</strong>,
            kedua belah pihak sepakat untuk mengadakan <strong>Penerimaan Hasil Pekerjaan</strong>,
            dengan ketentuan sebagai berikut:
        </p>

        <div class="pasal-title">PASAL 01</div>
        <p class="justify">
            PIHAK PERTAMA menyerahkan kepada PIHAK KEDUA dan PIHAK KEDUA menyatakan menerima dari PIHAK PERTAMA hasil
            pelaksanaan
            pekerjaan <strong>{{ $keperluan }}</strong> dalam keadaan baik, lengkap, dan benar sesuai Surat Pesanan.
        </p>

        <div class="pasal-title">PASAL 02</div>
        <p class="justify">
            Dengan telah dilakukannya Penerimaan Hasil Pekerjaan, maka PIHAK PERTAMA berhak menerima pembayaran sebesar
            <strong>{{ idr($realisasi->nominal ?? 9990000) }}</strong>
            ({{ ucwords(terbilang_id($realisasi->nominal ?? 9990000)) }} Rupiah),
            yang akan dituangkan dalam Berita Acara Pembayaran.
        </p>

        <p class="justify">
            Berita Acara Serah Terima Barang/Jasa ini dibuat untuk dipergunakan seperlunya.
        </p>

        <table class="signature mt-2">
            <tr style="width:100%">
                <td></td>
                <td>
                    Wonosobo, {{ $tglText }}<br>
                </td>
            </tr>
            <tr>
                <td style="width:50%">
                    <strong>PIHAK KEDUA</strong><br>
                    Kepala Dinas Komunikasi dan Informatika<br>
                    selaku Pejabat Pembuat Komitmen
                    <div class="blank"></div>
                    <span class="signature-name">{{ $namaPA }}</span>
                    NIP. {{ $nipPA }}
                </td>
                <td style="width:50%">
                    <strong>PIHAK PERTAMA</strong><br>
                    Untuk dan atas nama<br>
                    {{ $namaPerusahaan }}
                    <div class="blank"></div>
                    <span class="signature-name">{{ $namaPimpinan }}</span>
                    Direktur
                </td>
            </tr>
        </table>
    </div>

    {{-- === PAGE 2 LAMPIRAN === --}}
    <div class="page lampiran">
        <div class="page">
            <div><strong>Lampiran Berita Acara Serah Terima Barang/Jasa</strong><br>
                Nomor: {{ $nomorBA }}<br>Tanggal: {{ $tglText }}</div>

            <div class="center mt-2 mb-2"><strong>DAFTAR RINCIAN</strong><br>{{ $keperluan }}</div>

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

            <table class="signature mt-4">
                <tr>
                    <td>
                    <td>Wonosobo, {{ $tglText }}<br></td>
                    </td>
                </tr>
                <tr>
                    <td style="width:50%">
                        <strong>PIHAK KEDUA</strong><br>
                        Kepala Dinas Komunikasi dan Informatika<br>
                        selaku Pejabat Pembuat Komitmen
                        <div class="blank"></div>
                        <span class="signature-name">{{ $namaPA }}</span>
                        NIP. {{ $nipPA }}
                    </td>
                    <td style="width:50%">
                        <strong>PIHAK PERTAMA</strong><br>
                        Untuk dan atas nama<br>
                        {{ $namaPerusahaan }}
                        <div class="blank"></div>
                        <span class="signature-name">{{ $namaPimpinan }}</span>
                        Direktur
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
