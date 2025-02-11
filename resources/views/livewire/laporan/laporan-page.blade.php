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

        <!-- Tombol Download Laporan NPD -->
        <button wire:click="exportLaporanNPD" class="btn btn-sm btn-primary mt-3">
            <i class="fas fa-download"></i> Download Laporan NPD
        </button>
        <button onclick="printReport()" class="btn btn-sm btn-primary mt-3">
            <i class="fas fa-print"></i> Print Preview
        </button>
        <button wire:click="printLaporanNPD" class="btn btn-sm btn-danger mt-3">
            <i class="fas fa-print"></i> Print PDF
        </button>

        <div class="card p-4 mt-2" id="printArea">
            <!-- Tampilkan laporan NPD -->
            <h2 class="mt-4 text-center">NOTA PENCAIRAN DANA (NPD)</h2>
            <table class="program-info-table">
                <tr>
                    <td><strong>PROGRAM </strong></td>
                    <td><strong>: </strong>
                        {{ $dataArray->kegiatan->program->kode ?? '-' }} -
                        {{ $dataArray->kegiatan->program->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>KEGIATAN</strong></td>
                    <td><strong>: </strong>{{ $dataArray->kegiatan->kode ?? '-' }} -
                        {{ $dataArray->kegiatan->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>SUB KEGIATAN</strong></td>
                    <td><strong>: </strong>
                        {{ $dataArray->kode ?? '-' }} - {{ $dataArray->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>PPTK / NIP </strong></td>
                    <td><strong>: </strong> {{ $dataArray->pptk->nama ?? '-' }} / {{ $dataArray->pptk->nip ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>BULAN</strong></td>
                    <td><strong>: </strong>{{ $bulanList[$selectedBulan] ?? '-' }} {{ date('Y') }}</td>
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
                        <th class="equal-width-detail" colspan="2">RINCIAN SPJ</th>
                        <!-- Gabungan subkolom GU dan LS -->
                        <th class="equal-width" rowspan="2">SISA ANGGARAN</th>
                    </tr>
                    <tr class="text-center">
                        <th class="equal-width">GU</th>
                        <th class="equal-width">LS</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Inisialisasi total untuk setiap kolom
                        $totalAnggaran = 0;
                        $totalLama = 0;
                        $totalBaru = 0;
                        $totalGu = 0;
                        $totalLs = 0;
                        $totalSisaAnggaran = 0;
                    @endphp
                    @foreach ($dataArray->rkas ?? [] as $rka)
                        <tr>
                            <td class="text-center"><strong>{{ $rka->kode_belanja }}</strong></td>
                            <td><strong>{{ $rka->nama_belanja }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->anggaran) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->lama) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($rka->baru) }}</strong></td>
                            <td class="text-right">{{ number_format($rka->gu_total ?? 0) }}</td> <!-- Total GU -->
                            <td class="text-right">{{ number_format($rka->ls_total ?? 0) }}</td> <!-- Total LS -->
                            <td class="text-right">
                                <strong>{{ number_format($rka->anggaran - $rka->lama - $rka->baru) }}</strong>
                            </td>
                        </tr>

                        @php
                            $totalAnggaran += $rka->anggaran;
                            $totalLama += $rka->lama;
                            $totalBaru += $rka->baru;
                            $totalGu += $rka->gu_total ?? 0;
                            $totalLs += $rka->ls_total ?? 0;
                            $totalSisaAnggaran += $rka->anggaran - $rka->lama - $rka->baru;
                        @endphp

                        <!-- Detail transaksi GU -->
                        @foreach ($rka->belanjas ?? [] as $gu)
                            <tr>
                                <td></td>
                                <td colspan="4"> - {{ $gu->uraian }}</td>
                                <td class="text-right">{{ number_format($gu->nilai) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach

                        <!-- Detail transaksi LS -->
                        @foreach ($rka->belanjaLsDetails as $ls)
                            @if (
                                \Carbon\Carbon::parse($ls->belanjaLs->tanggal)->month == $selectedBulan &&
                                    \Carbon\Carbon::parse($ls->belanjaLs->tanggal)->year == $tahun)
                                <tr>
                                    <td></td>
                                    <td colspan="4"> - LS: {{ $ls->belanjaLs->uraian }}</td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($ls->nilai) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    <tr>
                        <td></td>
                        <td><strong>Jumlah Total</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalAnggaran) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalLama) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalBaru) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalGu) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalLs) }}</strong></td>
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
            font-size: 14px;
        }

        .table td {
            font-size: 12px;
            /* Memastikan teks tidak keluar dari batas */
        }

        /* Penyesuaian lebar kolom */
        .kode-rekening {
            width: 15%;
            /* Lebar yang lebih proporsional */
        }

        .uraian {
            width: 35%;
            /* Lebar lebih besar untuk uraian */
        }

        .equal-width {
            width: 10%;

            /* Semua kolom equal-width termasuk GU dan LS memiliki lebar yang sama */
        }

        .equal-width-detail {
            width: 20%;
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
