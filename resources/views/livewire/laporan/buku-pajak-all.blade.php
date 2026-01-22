@push('css')
    <x-styles.modern-ui />
    <style>
        .signature-section {
            display: none !important;
        }
    </style>
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="page-title">Buku Pajak All</h3>
                    <p class="page-subtitle mb-0">Gabungan Buku Pajak Giro, KKPD, dan LS</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">TANGGAL AWAL</label>
                        <input type="date" wire:model.live="tanggal_awal" min="{{ session('tahun_anggaran') }}-01-01" max="{{ session('tahun_anggaran') }}-12-31" class="form-control modern-input">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">TANGGAL AKHIR</label>
                        <input type="date" wire:model.live="tanggal_akhir" min="{{ session('tahun_anggaran') }}-01-01" max="{{ session('tahun_anggaran') }}-12-31" class="form-control modern-input">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">JENIS PAJAK</label>
                        <select wire:model.live="jenis" class="form-control modern-select">
                            <option value="ALL">SEMUA</option>
                            <option value="PPN">PPN</option>
                            <option value="PPh 21">PPh 21</option>
                            <option value="PPh 22">PPh 22</option>
                            <option value="PPh 23">PPh 23</option>
                            <option value="Pajak Restoran">Pajak Restoran</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold text-secondary small mb-2">TANGGAL CETAK</label>
                        <input type="date" wire:model.live="tanggal_cetak" class="form-control modern-input">
                    </div>
                </div>
            </div>

            <div class="row align-items-end mb-4">
                 <div class="col-md-12 text-right">
                     @if ($laporan)
                        <button wire:click="exportPdf" class="btn btn-modern-add" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="exportPdf">
                                <i class="fas fa-print mr-2"></i> Cetak Laporan
                            </span>
                            <span wire:loading wire:target="exportPdf">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Mencetak...
                            </span>
                        </button>
                    @endif
                </div>
            </div>

             @if ($laporan)
                <div id="print-area" class="bg-white p-4 border rounded">
                    <!-- Standard Report Header -->
                     <div class="text-center mb-4">
                        <h4 class="font-weight-bold mb-1">PEMERINTAH KABUPATEN WONOSOBO</h4>
                        <h3 class="font-weight-bold mb-1">BUKU PEMBANTU PAJAK (GABUNGAN)</h3>
                         @if ($jenis === 'PPN')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPN</h5>
                        @elseif ($jenis === 'PPh 21')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 21</h5>
                        @elseif ($jenis === 'PPh 22')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 22</h5>
                        @elseif ($jenis === 'PPh 23')
                            <h5 class="font-weight-bold mb-1">Per Jenis Pajak : PPh 23</h5>
                        @elseif ($jenis === 'Pajak Restoran')
                             <h5 class="font-weight-bold mb-1">Per Jenis Pajak : Pajak Restoran</h5>
                        @else
                            <h5 class="font-weight-bold mb-1">Semua Jenis Pajak</h5>
                        @endif
                        <p class="mb-0">
                            Periode {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }} s/d
                            {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    <!-- Summary Cards (Hidden in Print usually, but can keep if desired. Let's keep simpler format for print) -->
                    <!-- We will hide summary cards in print via CSS in the JS function if needed, but for now lets omit them from print area or include them? 
                         The previous file puts summary cards above the table. I'll put them inside print area but styled as a table for better print layout or just keep them as is.
                         Actually, standard reports usually just list the table. I will keep the summary cards OUTSIDE the print area or include them. 
                         Let's put them BEFORE the print area in the view so they are seen on screen, but for printing we focus on the report table. 
                         However, the user might want to see the totals.
                         Let's include the totals in the footer of the table (which is already there).
                         So I will NOT include the big colorful stat cards in the print area.
                    -->

                <table class="table modern-table table-bordered">
                    <thead>
                        <!-- Spacer Row for Print Top Margin on subsequent pages -->
                        <tr class="print-spacer" style="border: none !important; height: 40px; background-color: transparent;">
                             <th colspan="7" style="border: none !important; background-color: transparent; color: transparent;">.</th>
                        </tr>
                        <tr class="bg-light">
                            <th width="5%" class="text-center">No</th>
                            <th width="10%">Tgl Bukti</th>
                            <th width="15%">No Bukti</th>
                            <th>Uraian</th>
                            <th width="15%" class="text-right">Pemotongan</th>
                            <th width="15%" class="text-right">Penyetoran</th>
                            <th width="15%" class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($laporan)
                            <!-- Saldo Awal Row -->
                            <tr class="bg-light font-weight-bold">
                                <td colspan="4" class="text-right">Saldo Awal</td>
                                <td class="text-right text-muted">Rp
                                    {{ number_format($saldoAwalPemotongan, 2, ',', '.') }}</td>
                                <td class="text-right text-muted">Rp
                                    {{ number_format($saldoAwalPenyetoran, 2, ',', '.') }}</td>
                                <td class="text-right text-primary">Rp
                                    {{ number_format($saldoAwal, 2, ',', '.') }}</td>
                            </tr>

                            @php
                                $currentSaldo = $saldoAwal;
                                $totalPemotongan = 0;
                                $totalPenyetoran = 0;
                            @endphp

                            @foreach ($laporan as $index => $row)
                                @php
                                    $currentSaldo = $currentSaldo + ($row->pemotongan ?? 0) - ($row->penyetoran ?? 0);
                                    $totalPemotongan += $row->pemotongan ?? 0;
                                    $totalPenyetoran += $row->penyetoran ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $row->tgl_bukti }}</td>
                                    <td><span class="code-badge">{{ $row->no_bukti }}</span></td>
                                    <td>{{ $row->uraian }}</td>
                                    <td class="text-right">
                                        @if ($row->pemotongan > 0)
                                            <span class="text-success">Rp {{ number_format($row->pemotongan, 2, ',', '.') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($row->penyetoran > 0)
                                            <span class="text-danger">Rp {{ number_format($row->penyetoran, 2, ',', '.') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        Rp {{ number_format($currentSaldo, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Footer Totals -->
                            <tr class="bg-light font-weight-bold">
                                <td colspan="4" class="text-right text-uppercase">Jumlah Periode Ini</td>
                                <td class="text-right">Rp {{ number_format($totalPemotongan, 2, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($totalPenyetoran, 2, ',', '.') }}</td>
                                <td class="text-right text-primary">Rp {{ number_format($saldoAkhir, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div style="page-break-inside: avoid;">
                    <div class="mt-4 row">
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered modern-table" style="font-size: 12px; border: 1px solid #dee2e6;">
                                <!-- Spacer for Summary Table if it breaks page -->
                                <thead>
                                    <tr class="print-spacer" style="border: none !important; height: 40px; background-color: transparent;">
                                         <th colspan="2" style="border: none !important; background-color: transparent; color: transparent;">.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bg-light font-weight-bold">Jumlah Pajak Periode Sebelumnya</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($saldoAwalPemotongan, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light font-weight-bold">Jumlah Pajak Periode Ini</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($pajakPeriodeIni, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    @if ($jenis === 'ALL' || $jenis === 'PPN')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPN</td>
                                            <td class="text-right">{{ number_format($ppnTotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 21')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 21</td>
                                            <td class="text-right">{{ number_format($pph21Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 22')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 22</td>
                                            <td class="text-right">{{ number_format($pph22Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ($jenis === 'ALL' || $jenis === 'PPh 23')
                                        <tr>
                                            <td style="padding-left: 20px;">- Pajak PPh 23</td>
                                            <td class="text-right">{{ number_format($pph23Total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-secondary text-white">
                                        <td class="font-weight-bold" style="color: white !important;">Total Pajak Sampai Periode Ini</td>
                                        <td class="text-right font-weight-bold" style="color: white !important;">
                                            {{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <table class="signature-section" style="width: 100%; border: none; margin-top: 20px;">
                        <tr style="border: none;">
                            <td style="border: none; width: 50%; text-align: center; vertical-align: top;">
                                <p class="mb-0">Mengetahui,</p>
                                <p class="mb-5">PENGGUNA ANGGARAN</p>
                                <br><br><br> <!-- Space for signature -->
                                <p class="font-weight-bold mb-0 text-uppercase" style="text-decoration: underline;">
                                    {{ $penggunaAnggaran->nama ?? '..........................' }}
                                </p>
                                <p class="mb-0">NIP. {{ $penggunaAnggaran->nip ?? '..........................' }}</p>
                            </td>
                            <td style="border: none; width: 50%; text-align: center; vertical-align: top;">
                                <p class="mb-0">Wonosobo, {{ $tanggal_cetak ? \Carbon\Carbon::parse($tanggal_cetak)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                                <p class="mb-5">BENDAHARA PENGELUARAN</p>
                                <br><br><br> <!-- Space for signature -->
                                <p class="font-weight-bold mb-0 text-uppercase" style="text-decoration: underline;">
                                    {{ $bendahara->nama ?? '..........................' }}
                                </p>
                                <p class="mb-0">NIP. {{ $bendahara->nip ?? '..........................' }}</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div> <!-- End of print-area -->
            @endif
            
            @if(!$laporan) 
               <!-- Show empty state outside print area if no data -->
               <div class="table-responsive">
                    <table class="table modern-table table-bordered">
                        <tbody>
                             <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                                    <p>Silakan pilih rentang tanggal untuk menampilkan laporan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
               </div>
            @endif
        </div>
    </div>
</div>

