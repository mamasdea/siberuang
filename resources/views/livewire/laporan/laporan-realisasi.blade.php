<div>
    <!-- Form untuk memilih bulan -->
    <div class="form-group mt-3">
        <label for="bulan">Pilih Bulan</label>
        <select id="bulan" wire:model.live="selectedBulan" class="form-control">
            <option value="">-- Pilih Bulan --</option>
            @foreach ($bulanList as $key => $bulan)
                <option value="{{ $key }}">{{ $bulan }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tombol Cetak Seluruh Laporan -->
    <button class="btn btn-primary mt-3" onclick="printAll()">
        <i class="fas fa-print"></i> Cetak Semua Laporan
    </button>

    <!-- Wrapper untuk semua halaman cetak -->
    <div id="printArea">
        <!-- Loop per Sub Kegiatan -->
        @foreach ($subKegiatans as $index => $subKegiatan)
            <div class="print-page">
                <h2 class="text-center">LAPORAN REALISASI ANGGARAN</h2>

                <!-- Header Kop Surat -->
                <table class="program-info-table">
                    <tr>
                        <td><strong>PROGRAM</strong></td>
                        <td>: {{ $subKegiatan->kegiatan->program->kode }} - {{ $subKegiatan->kegiatan->program->nama }}
                        </td>
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

                <table class="table table-bordered mt-2">
                    <thead>
                        <tr class="text-center">
                            <th width="100">KODE BELANJA</th>
                            <th width="300">URAIAN</th>
                            <th width="100">ANGGARAN</th>
                            <th width="100">REALISASI BULAN LALU</th>
                            <th width="100">REALISASI BULAN INI</th>
                            <th width="100">TOTAL REALISASI</th>
                            <th width="100">SISA ANGGARAN</th>
                            <th width="25">%</th>
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
                                <td class="text-center"><strong>{{ $rka->kode_belanja }}</strong></td>
                                <td><strong>{{ $rka->nama_belanja }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($anggaran) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($realisasiLalu) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($realisasiBulanIni) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($totalRealisasiItem) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($sisaAnggaran) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($persentase, 2) }}%</strong></td>
                            </tr>
                        @endforeach

                        <!-- Baris Total -->
                        <tr class="font-weight-bold bg-light">
                            <td colspan="2" class="text-center"><strong>JUMLAH TOTAL</strong></td>
                            <td class="text-right"><strong>{{ number_format($totalAnggaran) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totalRealisasiLalu) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totalRealisasiBulanIni) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totalRealisasi) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($totalSisaAnggaran) }}</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format(($totalRealisasi / ($totalAnggaran > 0 ? $totalAnggaran : 1)) * 100, 2) }}%</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>

<!-- CSS untuk format cetak -->
@push('css')
    <style>
        .table th,
        .table td {
            font-size: 12px;
        }

        /* Untuk mencetak tiap sub kegiatan di halaman baru */
        .print-page {
            page-break-before: always;
            padding: 20px;
        }

        @media print {
            body * {
                /* visibility: hidden; */
            }

            #printArea,
            #printArea * {
                /* visibility: visible; */
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            @page {
                size: F4;
                margin: 10mm;
            }
        }
    </style>
@endpush

<!-- JavaScript untuk cetak -->
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
