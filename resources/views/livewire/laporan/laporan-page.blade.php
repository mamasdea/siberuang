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

    <!-- Form untuk memilih sub kegiatan -->
    <div class="form-group mt-3">
        <label for="sub_kegiatan">Pilih Sub Kegiatan</label>
        <select id="sub_kegiatan" wire:model.live="selectedSubKegiatan" class="form-control">
            <option value="">-- Pilih Sub Kegiatan --</option>
            @foreach ($subKegiatans as $subKegiatan)
                <option value="{{ $subKegiatan->id }}">{{ $subKegiatan->nama }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tampilkan detail sub kegiatan yang dipilih -->
    @if ($selectedSubKegiatan)
        <button onclick="printReport()" class="btn btn-sm btn-primary mt-3">
            <i class="fas fa-print"></i> Print Preview
        </button>

        <div class="card p-4 mt-2" id="printArea">
            <!-- Laporan -->
            <h2 class="mt-4 text-center">NOTA PENCAIRAN DANA (NPD)</h2>
            <table class="program-info-table">
                <tr>
                    <td><strong>PROGRAM</strong></td>
                    <td>: {{ $dataArray->kegiatan->program->kode ?? '-' }} -
                        {{ $dataArray->kegiatan->program->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>KEGIATAN</strong></td>
                    <td>: {{ $dataArray->kegiatan->kode ?? '-' }} - {{ $dataArray->kegiatan->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>SUB KEGIATAN</strong></td>
                    <td>: {{ $dataArray->kode ?? '-' }} - {{ $dataArray->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>PPTK / NIP</strong></td>
                    <td>: {{ $dataArray->pptk->nama ?? '-' }} / {{ $dataArray->pptk->nip ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>BULAN</strong></td>
                    <td>: {{ $bulanList[$selectedBulan] ?? '-' }} {{ date('Y') }}</td>
                </tr>
            </table>

            <table class="table table-bordered mt-2">
                <thead>
                    <tr class="text-center">
                        <th class="kode-rekening" rowspan="2">KODE REKENING</th>
                        <th class="uraian" rowspan="2">URAIAN</th>
                        <th class="equal-width" rowspan="2">ANGGARAN</th>
                        <th class="equal-width" rowspan="2">AKUMULASI PENCAIRAN</th>
                        <th class="equal-width" rowspan="2">PENCAIRAN SAAT INI</th>
                        <th class="equal-width-detail" colspan="3">RINCIAN SPJ</th> <!-- Tambahan KKPD -->
                        <th class="equal-width" rowspan="2">SISA ANGGARAN</th>
                    </tr>
                    <tr class="text-center">
                        <th class="equal-width">GU</th>
                        <th class="equal-width">KKPD</th> <!-- Tambahan KKPD -->
                        <th class="equal-width">LS</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAnggaran = 0;
                        $totalLama = 0;
                        $totalBaru = 0;
                        $totalGu = 0;
                        $totalKkpd = 0; // Tambahan KKPD
                        $totalLs = 0;
                        $totalSisaAnggaran = 0;
                    @endphp

                    @foreach ($dataArray->rkas ?? [] as $rka)
                        @php
                            $totalAnggaran += $rka->anggaran;
                            $totalLama += $rka->lama;
                            $totalBaru += $rka->baru;
                            $totalGu += $rka->gu_baru ?? 0;
                            $totalKkpd += $rka->kkpd_baru ?? 0;
                            $totalLs += $rka->ls_baru ?? 0;
                            $totalSisaAnggaran += $rka->anggaran - $rka->lama - $rka->baru;
                        @endphp

                        <tr>
                            <td class="text-center"><strong>{{ $rka->kode_belanja }}</strong></td>
                            <td><strong>{{ $rka->nama_belanja }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->anggaran) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->lama) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->baru) }}</strong></td>
                            <td class="text-right">{{ number_format($rka->gu_total ?? 0) }}</td>
                            <td class="text-right">{{ number_format($rka->kkpd_total ?? 0) }}</td>
                            <td class="text-right">{{ number_format($rka->ls_total ?? 0) }}</td>
                            <td class="text-right">
                                <strong>{{ number_format($rka->anggaran - $rka->lama - $rka->baru) }}</strong>
                            </td>
                        </tr>

                        <!-- Detail transaksi GU -->
                        @foreach ($rka->belanjas ?? [] as $gu)
                            @if (\Carbon\Carbon::parse($gu->tanggal)->month == $selectedBulan && \Carbon\Carbon::parse($gu->tanggal)->year == $tahun)
                                <tr>
                                    <td></td>
                                    <td colspan="4"> - {{ $gu->uraian }}</td>
                                    <td class="text-right">{{ number_format($gu->nilai) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Detail transaksi KKPD -->
                        @foreach ($rka->belanjaKkpds ?? [] as $kkpd)
                            @if (
                                \Carbon\Carbon::parse($kkpd->tanggal)->month == $selectedBulan &&
                                    \Carbon\Carbon::parse($kkpd->tanggal)->year == $tahun)
                                <tr>
                                    <td></td>
                                    <td colspan="4"> - KKPD: {{ $kkpd->uraian }}</td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($kkpd->nilai) }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Detail transaksi LS -->
                        @foreach ($rka->belanjaLsDetails ?? [] as $ls)
                            @if (
                                \Carbon\Carbon::parse($ls->belanjaLs->tanggal)->month == $selectedBulan &&
                                    \Carbon\Carbon::parse($ls->belanjaLs->tanggal)->year == $tahun)
                                <tr>
                                    <td></td>
                                    <td colspan="4"> - LS: {{ $ls->belanjaLs->uraian }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($ls->nilai) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    <!-- Total Row -->
                    <tr class="font-weight-bold bg-light">
                        <td colspan="2" class="text-center"><strong>JUMLAH TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAnggaran) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalLama) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalBaru) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalGu, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalKkpd, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalLs, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalSisaAnggaran) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- CSS untuk print dan layout -->
@push('css')
    <style>
        .table th {
            text-align: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11px;
        }

        .table td {
            font-size: 10px;
            /* Memastikan teks tidak keluar dari batas */
        }

        /* Penyesuaian lebar kolom */
        .kode-rekening {
            width: 12%;
            /* Lebar yang lebih proporsional */
        }

        .uraian {
            width: 40%;
            /* Lebar lebih besar untuk uraian */
        }

        .equal-width {
            width: 10%;

            /* Semua kolom equal-width termasuk GU dan LS memiliki lebar yang sama */
        }

        .equal-width-detail {
            width: 30%;
            /* Semua kolom equal-width termasuk GU dan LS memiliki lebar yang sama */
        }
    </style>

    <style>
        .program-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* 3 columns with equal width */
            gap: 20px;
            /* Gap between columns */
        }

        .program-info .label {
            display: inline-block;
            width: 150px;
            /* Adjust label width */
        }

        .program-info div {
            display: flex;
            align-items: baseline;
        }

        .table {
            width: 100%;
            table-layout: fixed;
            /* All columns will have fixed width */
        }


        .text-center {
            text-align: center;
            justify-content: center;
        }

        /* Print styles to format layout when printing */
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

            /* Set paper size and orientation to landscape F4 */
            @page {
                size: 210mm 330mm landscape;
                margin: 10mm;
            }

            /* Optional: reduce padding/margin inside the print area */
            .card {
                padding: 5mm;
            }
        }
    </style>
    <style media="print">
        @page {
            size: A4 landscape !important;
            margin: 10mm !important;
        }
    </style>
@endpush


<!-- JavaScript untuk print -->
@push('js')
    <script>
        function printReport() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
@endpush
