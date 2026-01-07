@push('css')
    <x-styles.modern-ui />
    <style>
        .table th {
            text-align: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11px;
            vertical-align: middle !important;
        }

        .table td {
            font-size: 10px;
            vertical-align: middle !important;
        }

        .program-info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .program-info-table td {
            padding: 5px;
            font-size: 12px;
        }

        /* Penyesuaian lebar kolom */
        .kode-rekening { width: 12%; }
        .uraian { width: 40%; }
        .equal-width { width: 10%; }
        .equal-width-detail { width: 30%; }

        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
            }
            @page {
                size: 210mm 330mm landscape;
                margin: 10mm;
            }
            .modern-card {
                border: none;
                box-shadow: none;
            }
        }
    </style>
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">Nota Pencairan Dana (NPD)</h3>
            <p class="page-subtitle mb-0">Cetak laporan NPD per bulan</p>
        </div>
        
        <div class="content-card">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">Pilih Bulan</label>
                        <select id="bulan" wire:model.live="selectedBulan" class="form-control custom-select-modern">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach ($bulanList as $key => $bulan)
                                <option value="{{ $key }}">{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">Pilih Sub Kegiatan</label>
                        <select id="sub_kegiatan" wire:model.live="selectedSubKegiatan" class="form-control custom-select-modern">
                            <option value="">-- Pilih Sub Kegiatan --</option>
                            @foreach ($subKegiatans as $subKegiatan)
                                <option value="{{ $subKegiatan->id }}">{{ $subKegiatan->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if ($selectedSubKegiatan)
                <div class="d-flex justify-content-end mt-3 mb-4">
                    <button onclick="printReport()" class="btn btn-modern-add">
                        <i class="fas fa-print mr-2"></i> Print Preview
                    </button>
                </div>

                <div class="card p-4 border" style="background: #fff;" id="printArea">
                    <h2 class="mt-2 text-center mb-4" style="font-weight: 800; text-decoration: underline;">NOTA PENCAIRAN DANA (NPD)</h2>
                    
                    <table class="program-info-table">
                        <tr>
                            <td style="width: 150px;"><strong>PROGRAM</strong></td>
                            <td>: {{ $dataArray->kegiatan->program->kode ?? '-' }} - {{ $dataArray->kegiatan->program->nama ?? '-' }}</td>
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

                    <table class="table table-bordered table-sm mt-3 modern-table">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th class="kode-rekening" rowspan="2">KODE REKENING</th>
                                <th class="uraian" rowspan="2">URAIAN</th>
                                <th class="equal-width" rowspan="2">ANGGARAN</th>
                                <th class="equal-width" rowspan="2">AKUMULASI PENCAIRAN</th>
                                <th class="equal-width" rowspan="2">PENCAIRAN SAAT INI</th>
                                <th class="equal-width-detail" colspan="3">RINCIAN SPJ</th>
                                <th class="equal-width" rowspan="2">SISA ANGGARAN</th>
                            </tr>
                            <tr class="text-center">
                                <th class="equal-width">GU</th>
                                <th class="equal-width">KKPD</th>
                                <th class="equal-width">LS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalAnggaran = 0;
                                $totalLama = 0;
                                $totalBaru = 0;
                                $totalGu = 0;
                                $totalKkpd = 0;
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
                                    @if (\Carbon\Carbon::parse($gu->tanggal)->month == $selectedBulan && \Carbon\Carbon::parse($gu->tanggal)->year == session('tahun_anggaran', date('Y')))
                                        <tr>
                                            <td></td>
                                            <td colspan="4" class="pl-4"> - {{ $gu->uraian }}</td>
                                            <td class="text-right">{{ number_format($gu->nilai) }}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- Detail transaksi KKPD -->
                                @foreach ($rka->belanjaKkpds ?? [] as $kkpd)
                                    @if (\Carbon\Carbon::parse($kkpd->tanggal)->month == $selectedBulan && \Carbon\Carbon::parse($kkpd->tanggal)->year == session('tahun_anggaran', date('Y')))
                                        <tr>
                                            <td></td>
                                            <td colspan="4" class="pl-4"> - KKPD: {{ $kkpd->uraian }}</td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($kkpd->nilai) }}</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- Detail transaksi LS -->
                                @foreach ($rka->belanjaLsDetails ?? [] as $ls)
                                    @if (\Carbon\Carbon::parse($ls->belanjaLs->tanggal)->month == $selectedBulan && \Carbon\Carbon::parse($ls->belanjaLs->tanggal)->year == session('tahun_anggaran', date('Y')))
                                        <tr>
                                            <td></td>
                                            <td colspan="4" class="pl-4"> - LS: {{ $ls->belanjaLs->uraian }}</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($ls->nilai) }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach

                            <!-- Total Row -->
                            <tr class="bg-light">
                                <td colspan="2" class="text-center font-weight-bold">JUMLAH TOTAL</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalAnggaran) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalLama) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalBaru) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalGu, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalKkpd, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalLs, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($totalSisaAnggaran) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('js')
    <script>
        function printReport() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
@endpush
