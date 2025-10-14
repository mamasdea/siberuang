<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Berita Acara' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Bootstrap CDN (opsional) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @page {
            size: A4;
            margin: 18mm;
        }

        body {
            font-size: 12pt;
            color: #111;
        }

        .kop {
            text-align: center;
            margin-bottom: 18px;
        }

        .kop .title {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14pt;
        }

        .kop .subtitle {
            font-size: 10pt;
            color: #666;
        }

        .meta {
            margin: 12px 0 18px;
        }

        .meta .row>div {
            margin-bottom: 6px;
        }

        .ttd {
            margin-top: 36px;
        }

        .ttd .col {
            text-align: center;
        }

        .ttd .name {
            display: inline-block;
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 6px;
        }

        .table-sm td,
        .table-sm th {
            padding: .25rem .4rem;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            background: #fff;
            padding: 8px 0 12px;
            z-index: 10;
            display: none;
        }

        @media screen {
            .print-toolbar {
                display: block;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        {{-- Toolbar hanya saat di layar --}}
        <div class="print-toolbar d-print-none">
            <div class="d-flex justify-content-between">
                <div><strong>{{ $title ?? 'Berita Acara' }}</strong></div>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="window.print()">Cetak</button>
                    <button class="btn btn-sm btn-secondary" onclick="window.close()">Tutup</button>
                </div>
            </div>
            <hr>
        </div>

        {{-- Kop Surat (sesuaikan logo/instansi) --}}
        <div class="kop">
            {{-- <img src="{{ asset('img/logo.png') }}" alt="Logo" height="64"> --}}
            <div class="title">{{ $title ?? 'Berita Acara' }}</div>
            <div class="subtitle">Pemerintah Kabupaten / OPD (sesuaikan)</div>
        </div>

        {{-- Konten halaman --}}
        @yield('content')

        {{-- TTD --}}
        @yield('signatures')
    </div>

    @if (!empty($auto))
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif
</body>

</html>
