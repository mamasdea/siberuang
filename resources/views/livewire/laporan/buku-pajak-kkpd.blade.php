<div>
    {{-- Filter Range Tanggal --}}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label for="tanggal_awal" class="col-sm-4 col-form-label">Tanggal Awal</label>
                        <div class="col-sm-8">
                            <input type="date" wire:model.live="tanggal_awal" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label for="tanggal_akhir" class="col-sm-4 col-form-label">Tanggal Akhir</label>
                        <div class="col-sm-8">
                            <input type="date" wire:model.live="tanggal_akhir" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label for="jenis" class="col-sm-4 col-form-label">Jenis Pajak</label>
                        <div class="col-sm-8">
                            <select wire:model.live="jenis" class="form-control form-control-sm">
                                <option value="ALL">Semua</option>
                                <option value="PPN">PPN</option>
                                <option value="PPh 21">PPh 21</option>
                                <option value="PPh 22">PPh 22</option>
                                <option value="PPh 23">PPh 23</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tampilkan laporan hanya jika range tanggal dipilih --}}
    @if ($tanggal_awal && $tanggal_akhir)
        <div class="bg-white p-2 mt-3" id="laporan-container">
            {{-- Header Laporan --}}
            <div id="print-area">
                <h2 class="text-center">PEMERINTAH KABUPATEN WONOSOBO</h2>
                <h3 class="text-center">BUKU PEMBANTU PAJAK (KKPD)</h3>
                @if ($jenis === 'PPN')
                    <h4 class="text-center">Per Jenis Pajak : PPN</h4>
                @elseif ($jenis === 'PPh 21')
                    <h4 class="text-center">Per Jenis Pajak : PPh 21</h4>
                @elseif ($jenis === 'PPh 22')
                    <h4 class="text-center">Per Jenis Pajak : PPh 22</h4>
                @elseif ($jenis === 'PPh 23')
                    <h4 class="text-center">Per Jenis Pajak : PPh 23</h4>
                @else
                    <h4 class="text-center">Semua Jenis Pajak</h4>
                @endif
                <h5 class="text-center">
                    Periode {{ \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') }} s/d
                    {{ \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') }}
                </h5>

                <table border="1" width="100%" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">Tgl Bukti</th>
                            <th style="width: 15%; text-align: center;">No Bukti</th>
                            <th style="width: 40%; text-align: center;">Uraian</th>
                            <th style="width: 15%; text-align: center;">Pemotongan</th>
                            <th style="width: 15%; text-align: center;">Penyetoran</th>
                            <th style="width: 15%; text-align: center;">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningSaldo = $saldoAwal;
                        @endphp
                        <tr>
                            <td></td>
                            <td></td>
                            <td><strong>Saldo Awal</strong></td>
                            <td class="text-right">
                                {{ $saldoAwalPemotongan > 0 ? number_format($saldoAwalPemotongan, 0, ',', '.') : '' }}
                            </td>
                            <td class="text-right">
                                {{ $saldoAwalPenyetoran > 0 ? number_format($saldoAwalPenyetoran, 0, ',', '.') : '' }}
                            </td>
                            <td class="text-right">0</td>
                        </tr>
                        @if (count($laporan) > 0)
                            @foreach ($laporan as $item)
                                @php
                                    // Jika nilai null, diubah menjadi 0
                                    $pemotongan = $item->pemotongan ?? 0;
                                    $penyetoran = $item->penyetoran ?? 0;
                                    $runningSaldo += $pemotongan - $penyetoran;
                                @endphp
                                <tr>
                                    <td style="text-align: center;">
                                        {{ \Carbon\Carbon::parse($item->tgl_bukti)->format('d-m-Y') }}</td>
                                    <td style="text-align: center;">{{ $item->no_bukti }}</td>
                                    <td>{{ $item->uraian }}</td>
                                    <td class="text-right">
                                        {{ $pemotongan > 0 ? number_format($pemotongan, 0, ',', '.') : '' }}</td>
                                    <td class="text-right">
                                        {{ $penyetoran > 0 ? number_format($penyetoran, 0, ',', '.') : '' }}</td>
                                    <td class="text-right">{{ number_format($runningSaldo, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" style="text-align: center;"><strong>Data Tidak Ada</strong></td>
                            </tr>
                        @endif

                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">
                                <strong>{{ number_format($saldoAwalPenyetoran + collect($laporan)->sum('penyetoran'), 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right"><strong>{{ number_format($runningSaldo, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Tampilkan ringkasan pajak --}}
                <div class="mt-3">
                    <table class="table table-sm table-bordered" style="width: 40%;">
                        <tbody>
                            <tr>
                                <td><strong>Jumlah Pajak Periode Sebelumnya</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($saldoAwalPemotongan, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Pajak Periode Ini</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($pajakPeriodeIni, 0, ',', '.') }}</strong>
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
                            <tr>
                                <td><strong>Total Pajak Sampai Periode Ini</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format($saldoAwalPemotongan + collect($laporan)->sum('pemotongan'), 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tombol Cetak --}}
            <div class="text-center" style="margin-top: 20px;">
                <button onclick="printLaporan()" class="btn btn-primary">🖨️ Cetak Laporan</button>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            Silakan pilih range tanggal untuk menampilkan laporan.
        </div>
    @endif

    {{-- Script untuk Print --}}
    <script>
        function printLaporan() {
            var printContent = document.getElementById("print-area").innerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</div>
