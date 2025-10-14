<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Cetak Semua Berita Acara</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
        }

        body {
            font-family: "Times New Roman", serif;
            color: #000;
            font-size: 12pt;
            line-height: 1.25;
        }

        /* Hilangkan margin kosong antar halaman */
        .page-wrapper {
            display: block;
            page-break-after: always;
            margin-top: 30px;
        }

        .page-wrapper:last-child {
            page-break-after: avoid;
            margin-top: 30px;
        }

        /* Perbaiki tampilan antar halaman */
        @media print {

            html,
            body {
                height: 100%;
            }
        }

        .page-wrapper .lampiran,
        .page-wrapper [class*="Lampiran"],
        .page-wrapper [id*="lampiran"] {
            margin-top: 10mm !important;
        }
    </style>
</head>

<body>
    {{-- Render setiap BA --}}
    @foreach ($pages as $i => $page)
        <div class="page-wrapper">
            {!! preg_replace('/<\/*html>|<\/*body>/', '', $page) !!}
        </div>
    @endforeach

    <script>
        // Cetak otomatis jika diperlukan
        window.addEventListener('load', () => window.print());
    </script>
</body>

</html>
