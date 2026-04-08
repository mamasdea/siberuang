@push('css')
    <x-styles.modern-ui />
    <style>
        .rincian-table th, .rincian-table td {
            font-size: 12.5px;
            vertical-align: middle !important;
            padding: 6px 8px !important;
        }
        .info-table td {
            padding: 3px 8px;
            font-size: 13px;
        }
        .ttd-table {
            width: 100%;
            margin-top: 30px;
            font-size: 13px;
        }
        .ttd-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .print-page {
            padding: 24px;
            background: white;
            margin-bottom: 24px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .print-page:last-child {
            border-bottom: none;
        }
        .spj-sebelumnya-row td {
            background: #f8fafc !important;
            font-weight: 600 !important;
            font-style: italic;
        }
        .total-row td {
            background: #ede9fe !important;
            font-weight: 700 !important;
        }
    </style>
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Laporan Rincian Obyek Belanja</h3>
            <p class="page-subtitle mb-0">Detail realisasi per rekening belanja berdasarkan periode</p>
        </div>

        <div class="content-card">
            <!-- Filter -->
            <div class="row align-items-end mb-4">
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Periode Awal</label>
                    <input type="date" wire:model.live="periodeAwal" class="form-control custom-select-modern">
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Periode Akhir</label>
                    <input type="date" wire:model.live="periodeAkhir" class="form-control custom-select-modern">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-secondary small mb-2">Sub Kegiatan</label>
                    <select wire:model.live="selectedSubKegiatan" class="form-control custom-select-modern">
                        <option value="">-- Pilih Sub Kegiatan --</option>
                        @foreach($subKegiatanList as $sk)
                            <option value="{{ $sk->id }}">{{ $sk->kode }} - {{ $sk->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Laporan</label>
                    <input type="date" wire:model.live="tanggalLaporan" class="form-control custom-select-modern">
                </div>
                <div class="col-md-2">
                    @if(count($rkaPages) > 0)
                        <button class="btn btn-modern-add btn-block" onclick="printRincian()">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                    @endif
                </div>
            </div>

            <!-- Hasil Laporan -->
            <div id="printArea">
                @if(count($rkaPages) > 0)
                    @foreach($rkaPages as $pageIdx => $page)
                        <div class="print-page">
                            <!-- Header -->
                            <h4 class="text-center font-weight-bold" style="font-size: 16px; margin-bottom: 4px;">RINCIAN OBYEK BELANJA</h4>
                            <p class="text-center" style="font-size: 13px; color: #475569; margin-bottom: 20px;">Tahun Anggaran {{ $tahun }}</p>

                            <table class="info-table" style="width: 100%; margin-bottom: 20px;">
                                <tr>
                                    <td width="180"><strong>Program</strong></td>
                                    <td>: {{ $page['subKegiatan']['program_kode'] }} - {{ $page['subKegiatan']['program_nama'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kegiatan</strong></td>
                                    <td>: {{ $page['subKegiatan']['kegiatan_kode'] }} - {{ $page['subKegiatan']['kegiatan_nama'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Kegiatan</strong></td>
                                    <td>: {{ $page['subKegiatan']['kode'] }} - {{ $page['subKegiatan']['nama'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Rekening Belanja</strong></td>
                                    <td>: {{ $page['rka']['kode_belanja'] }} - {{ $page['rka']['nama_belanja'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>PPTK</strong></td>
                                    <td>: {{ $page['subKegiatan']['pptk_nama'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Anggaran (DPA)</strong></td>
                                    <td>: <strong>Rp {{ number_format($page['rka']['penetapan'] ?? 0, 2, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Anggaran (DPPA)</strong></td>
                                    <td>: <strong>Rp {{ number_format($page['rka']['perubahan'] ?? 0, 2, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Periode</strong></td>
                                    <td>: {{ \Carbon\Carbon::parse($periodeAwal)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($periodeAkhir)->translatedFormat('d F Y') }}</td>
                                </tr>
                            </table>

                            <table class="table table-bordered rincian-table">
                                <thead>
                                    <tr class="text-center" style="background: #e2e8f0;">
                                        <th width="80">TANGGAL</th>
                                        <th width="80">No. BKU</th>
                                        <th>URAIAN</th>
                                        <th width="105" class="text-right">BELANJA LS</th>
                                        <th width="105" class="text-right">BELANJA TU</th>
                                        <th width="105" class="text-right">BELANJA UP/GU</th>
                                        <th width="115" class="text-right">JUMLAH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- SPJ Sebelumnya --}}
                                    <tr class="spj-sebelumnya-row">
                                        <td colspan="3">SPJ Sebelumnya (s/d {{ \Carbon\Carbon::parse($periodeAwal)->subDay()->translatedFormat('d F Y') }})</td>
                                        <td class="text-right">{{ $page['spjSebelumnya']['ls'] > 0 ? number_format($page['spjSebelumnya']['ls'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['spjSebelumnya']['tu'] > 0 ? number_format($page['spjSebelumnya']['tu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['spjSebelumnya']['gu'] > 0 ? number_format($page['spjSebelumnya']['gu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ number_format($page['spjSebelumnya']['total'], 0, ',', '.') }}</td>
                                    </tr>

                                    {{-- Detail Belanja Periode Ini --}}
                                    @foreach($page['belanjas'] as $belanja)
                                        <tr>
                                            <td class="text-center">{{ date('d/m/Y', strtotime($belanja['tanggal'])) }}</td>
                                            <td class="text-center">{{ $belanja['no_bukti'] }}</td>
                                            <td>{{ $belanja['uraian'] }}</td>
                                            <td class="text-right">{{ $belanja['jenis'] === 'ls' ? number_format($belanja['nilai'], 0, ',', '.') : '' }}</td>
                                            <td class="text-right">{{ $belanja['jenis'] === 'tu' ? number_format($belanja['nilai'], 0, ',', '.') : '' }}</td>
                                            <td class="text-right">{{ $belanja['jenis'] === 'gu' ? number_format($belanja['nilai'], 0, ',', '.') : '' }}</td>
                                            <td class="text-right">{{ number_format($belanja['nilai'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach

                                    {{-- Jumlah Periode Ini --}}
                                    <tr style="font-weight: 700; background: #f8fafc;">
                                        <td colspan="3" class="text-center">JUMLAH</td>
                                        <td class="text-right">{{ $page['totalPeriodeIni']['ls'] > 0 ? number_format($page['totalPeriodeIni']['ls'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['totalPeriodeIni']['tu'] > 0 ? number_format($page['totalPeriodeIni']['tu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['totalPeriodeIni']['gu'] > 0 ? number_format($page['totalPeriodeIni']['gu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ number_format($page['totalPeriodeIni']['total'], 0, ',', '.') }}</td>
                                    </tr>

                                    {{-- Jumlah Realisasi --}}
                                    <tr class="total-row">
                                        <td colspan="3" class="text-center">JUMLAH REALISASI s/d BULAN INI</td>
                                        <td class="text-right">{{ $page['jumlahRealisasi']['ls'] > 0 ? number_format($page['jumlahRealisasi']['ls'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['jumlahRealisasi']['tu'] > 0 ? number_format($page['jumlahRealisasi']['tu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ $page['jumlahRealisasi']['gu'] > 0 ? number_format($page['jumlahRealisasi']['gu'], 0, ',', '.') : '' }}</td>
                                        <td class="text-right">{{ number_format($page['jumlahRealisasi']['total'], 0, ',', '.') }}</td>
                                    </tr>

                                    {{-- Sisa Anggaran --}}
                                    <tr style="font-weight: 700; background: #fefce8;">
                                        <td colspan="3" class="text-center">SISA ANGGARAN</td>
                                        <td colspan="3" class="text-right">{{ number_format(($page['anggaranAcuan'] ?? 0) - $page['jumlahRealisasi']['total'], 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($page['sisaAnggaran'], 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Tanda Tangan --}}
                            <table class="ttd-table">
                                <tr>
                                    <td width="50%" class="text-center">
                                        <p style="margin-bottom: 4px;">Mengetahui,</p>
                                        <p style="margin-bottom: 70px; font-weight: 700;">PENGGUNA ANGGARAN</p>
                                        <p style="margin-bottom: 2px; font-weight: 700; text-decoration: underline;">{{ $penggunaAnggaran->nama ?? '___________________' }}</p>
                                        <p style="margin: 0;">NIP. {{ $penggunaAnggaran->nip ?? '___________________' }}</p>
                                    </td>
                                    <td width="50%" class="text-center">
                                        <p style="margin-bottom: 4px;">Wonosobo, {{ \Carbon\Carbon::parse($tanggalLaporan ?? now())->translatedFormat('d F Y') }}</p>
                                        <p style="margin-bottom: 70px; font-weight: 700;">BENDAHARA PENGELUARAN</p>
                                        <p style="margin-bottom: 2px; font-weight: 700; text-decoration: underline;">{{ $bendaharaPengeluaran->nama ?? '___________________' }}</p>
                                        <p style="margin: 0;">NIP. {{ $bendaharaPengeluaran->nip ?? '___________________' }}</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
                        @if($selectedSubKegiatan && $periodeAwal && $periodeAkhir)
                            <p class="text-muted">Tidak ada realisasi belanja pada sub kegiatan dan periode ini.</p>
                        @elseif($periodeAwal && $periodeAkhir)
                            <p class="text-muted">Pilih sub kegiatan untuk menampilkan laporan.</p>
                        @else
                            <p class="text-muted">Pilih periode dan sub kegiatan untuk menampilkan laporan.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printRincian() {
            var printContent = document.getElementById('printArea').innerHTML;
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Laporan Rincian Obyek Belanja</title>
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.css') }}">
                    <style>
                        body { margin: 0; padding: 0; font-family: 'Source Sans Pro', sans-serif; }
                        .rincian-table th, .rincian-table td { font-size: 12.5px; vertical-align: middle !important; padding: 6px 8px !important; }
                        .info-table td { padding: 3px 8px; font-size: 13px; }
                        .ttd-table { width: 100%; margin-top: 30px; font-size: 13px; }
                        .ttd-table td { padding: 4px 8px; vertical-align: top; }
                        .spj-sebelumnya-row td { background: #f8fafc !important; font-weight: 600 !important; font-style: italic; }
                        .total-row td { background: #ede9fe !important; font-weight: 700 !important; }
                        .print-page { padding: 20px 24px; background: white; }
                        .print-page:first-child { page-break-before: auto; }
                        .print-page + .print-page { page-break-before: always; }
                        @page { size: A4 portrait; margin: 14mm; }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        setTimeout(function() { window.print(); window.close(); }, 500);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endpush
