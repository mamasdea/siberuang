@push('css')
    <x-styles.modern-ui />
    <style>
        .table th,
        .table td {
            font-size: 11px;
            vertical-align: middle !important;
        }

        .program-info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .program-info-table td {
            padding: 4px;
            font-size: 12px;
        }

        /* Untuk mencetak tiap sub kegiatan di halaman baru */
        .print-page {
            page-break-before: always;
            padding: 20px;
            background: white;
            margin-bottom: 30px;
            border-bottom: 1px dashed #ccc; /* Visual separator on screen */
        }
        
        .print-page:last-child {
            border-bottom: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .print-page {
                border-bottom: none;
                margin-bottom: 0;
                page-break-before: always;
            }
            
            /* First page avoid break before */
            .print-page:first-child {
                page-break-before: auto;
            }

            @page {
                size: 210mm 330mm landscape;
                margin: 10mm;
            }
        }
    </style>
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Laporan Realisasi Anggaran</h3>
            <p class="page-subtitle mb-0">Ringkasan realisasi anggaran per sub kegiatan</p>
        </div>

        <div class="content-card">
            <div class="row align-items-end mb-4">
                <div class="col-md-4">
                    <label class="font-weight-bold text-secondary small mb-2">Pilih Bulan</label>
                    <select id="bulan" wire:model.live="selectedBulan" class="form-control custom-select-modern">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach ($bulanList as $key => $bulan)
                            <option value="{{ $key }}">{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    @if($selectedBulan)
                    <button class="btn btn-modern-add" onclick="printAll()">
                        <i class="fas fa-print mr-2"></i> Cetak Semua Laporan
                    </button>
                    @endif
                </div>
            </div>

            <!-- Wrapper untuk semua halaman cetak -->
            <div class="bg-white border rounded shadow-sm" id="printArea" style="min-height: 500px;">
                @if(count($subKegiatans) > 0)
                    @foreach ($subKegiatans as $index => $subKegiatan)
                        <div class="print-page">
                            <h3 class="text-center font-weight-bold mb-4" style="text-decoration: underline;">LAPORAN REALISASI ANGGARAN</h3>
            
                            <!-- Header Kop Surat -->
                            <table class="program-info-table">
                                <tr>
                                    <td width="150"><strong>PROGRAM</strong></td>
                                    <td>: {{ $subKegiatan->kegiatan->program->kode }} - {{ $subKegiatan->kegiatan->program->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>KEGIATAN</strong></td>
                                    <td>: {{ $subKegiatan->kegiatan->kode }} - {{ $subKegiatan->kegiatan->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>SUB KEGIATAN</strong></td>
                                    <td>: {{ $subKegiatan->kode }} - {{ $subKegiatan->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>PPTK / NIP</strong></td>
                                    <td>: {{ $subKegiatan->pptk->nama ?? '-' }} / {{ $subKegiatan->pptk->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>BULAN</strong></td>
                                    <td>: {{ $bulanList[$selectedBulan] ?? '-' }} {{ $tahun }}</td>
                                </tr>
                            </table>
            
                            <table class="table table-bordered table-sm modern-table mt-2">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="100">KODE BELANJA</th>
                                        <th width="300">URAIAN</th>
                                        <th width="100">ANGGARAN</th>
                                        <th width="100">REALISASI BULAN LALU</th>
                                        <th width="100">REALISASI BULAN INI</th>
                                        <th width="100">TOTAL REALISASI</th>
                                        <th width="100">SISA ANGGARAN</th>
                                        <th width="40">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalAnggaran = 0;
                                        $totalRealisasiLalu = 0;
                                        $totalRealisasiBulanIni = 0;
                                        $totalRealisasi = 0;
                                        $totalSisaAnggaran = 0;
                                    @endphp
            
                                    @foreach ($subKegiatan->rkas as $rka)
                                        @php
                                            $anggaran = $rka->anggaran;
                                            $realisasiLalu = $rka->gu_bulan_lalu + $rka->ls_bulan_lalu;
                                            $realisasiBulanIni = $rka->gu_bulan_ini + $rka->ls_bulan_ini;
                                            $totalRealisasiItem = $realisasiLalu + $realisasiBulanIni;
                                            $sisaAnggaran = $anggaran - $totalRealisasiItem;
                                            $persentase = $anggaran > 0 ? ($totalRealisasiItem / $anggaran) * 100 : 0;
            
                                            $totalAnggaran += $anggaran;
                                            $totalRealisasiLalu += $realisasiLalu;
                                            $totalRealisasiBulanIni += $realisasiBulanIni;
                                            $totalRealisasi += $totalRealisasiItem;
                                            $totalSisaAnggaran += $sisaAnggaran;
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold">{{ $rka->kode_belanja }}</td>
                                            <td class="font-weight-bold">{{ $rka->nama_belanja }}</td>
                                            <td class="text-right">{{ number_format($anggaran) }}</td>
                                            <td class="text-right">{{ number_format($realisasiLalu) }}</td>
                                            <td class="text-right">{{ number_format($realisasiBulanIni) }}</td>
                                            <td class="text-right">{{ number_format($totalRealisasiItem) }}</td>
                                            <td class="text-right">{{ number_format($sisaAnggaran) }}</td>
                                            <td class="text-right">{{ number_format($persentase, 2) }}%</td>
                                        </tr>
                                    @endforeach
            
                                    <!-- Baris Total -->
                                    <tr class="font-weight-bold bg-light">
                                        <td colspan="2" class="text-center">JUMLAH TOTAL</td>
                                        <td class="text-right">{{ number_format($totalAnggaran) }}</td>
                                        <td class="text-right">{{ number_format($totalRealisasiLalu) }}</td>
                                        <td class="text-right">{{ number_format($totalRealisasiBulanIni) }}</td>
                                        <td class="text-right">{{ number_format($totalRealisasi) }}</td>
                                        <td class="text-right">{{ number_format($totalSisaAnggaran) }}</td>
                                        <td class="text-right">
                                            {{ number_format(($totalRealisasi / ($totalAnggaran > 0 ? $totalAnggaran : 1)) * 100, 2) }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data realisasi untuk bulan ini.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printAll() {
            var printContent = document.getElementById('printArea').innerHTML;
            var originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
@endpush
