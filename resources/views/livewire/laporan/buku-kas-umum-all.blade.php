@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up">
        <div class="card-header-modern">
            <h3 class="page-title">BKU Semua Transaksi</h3>
            <p class="page-subtitle mb-0">Buku Kas Umum gabungan GU, TU, dan LS</p>
        </div>
        <div class="content-card">
            <div class="row align-items-end mb-4">
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="mulai" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Akhir</label>
                    <input type="date" class="form-control" wire:model.live="end" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Filter Jenis</label>
                    <select wire:model.live="filterJenis" class="form-control custom-select-modern" style="height: 40px;">
                        <option value="semua">Semua (GU+KKPD+TU+LS)</option>
                        <option value="gu">GU Giro</option>
                        <option value="kkpd">GU KKPD</option>
                        <option value="tu">TU</option>
                        <option value="ls">LS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-secondary small mb-2">Tanggal Cetak</label>
                    <input type="date" class="form-control" wire:model.live="tanggalCetak" style="height: 40px; border-radius: 8px;">
                </div>
                <div class="col-md-2 text-right">
                    <button class="btn btn-modern-add btn-block" onclick="printBkuAll()">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>
                </div>
            </div>

            <div class="bg-white p-4 border rounded" id="print-area-all">
                <div class="text-center mb-4">
                    <h3 class="font-weight-bold" style="font-size: 18px; margin-bottom: 5px;">BUKU KAS UMUM</h3>
                    <p class="mb-1" style="font-size: 14px;">Dinas Komunikasi dan Informatika Kabupaten Wonosobo</p>
                    <p class="mb-1" style="font-size: 14px;">
                        Jenis: <strong>{{ $filterJenis === 'semua' ? 'Semua (GU + KKPD + TU + LS)' : strtoupper($filterJenis) }}</strong>
                    </p>
                    <p class="mb-0" style="font-size: 14px;">Periode:
                        <strong>{{ \Carbon\Carbon::parse($mulai)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</strong>
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm" style="font-size: 13px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 90px;">Tanggal</th>
                                <th style="width: 45px;">Jenis</th>
                                <th style="width: 140px;">No Bukti</th>
                                <th style="width: 100px;">Rekening</th>
                                <th>Uraian</th>
                                <th style="width: 120px;">Debet</th>
                                <th style="width: 120px;">Kredit</th>
                                <th style="width: 120px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalSaldo = $saldo; @endphp

                            <tr class="bg-light font-weight-bold">
                                <td>{{ \Carbon\Carbon::parse($mulai)->format('m-d') == '01-01' ? \Carbon\Carbon::parse($mulai)->format('d-m-Y') : \Carbon\Carbon::parse($mulai)->subDay()->format('d-m-Y') }}</td>
                                <td></td>
                                <td></td>
                                <td>Saldo Awal</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right">{{ number_format($saldo, 0, ',', '.') }}</td>
                            </tr>

                            @foreach ($data as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                                    <td class="text-center">
                                        @php
                                            $badgeColor = match($row->kategori) { 'GU' => 'success', 'KKPD' => 'info', 'TU' => 'warning', 'LS' => 'primary', default => 'secondary' };
                                        @endphp
                                        <span class="badge badge-{{ $badgeColor }}" style="font-size: 9px;">{{ $row->kategori }}</span>
                                    </td>
                                    <td style="font-size: 12px;">
                                        @if(in_array($row->jenis, ['sp2d_gu', 'sp2d_tu', 'sp2d_kkpd']))
                                            {{ $row->no_bukti }}
                                        @elseif($row->jenis === 'nihil_tu')
                                            Nihil-{{ $row->no_bukti }}
                                        @elseif(in_array($row->jenis, ['sp2d_ls', 'belanja_ls']))
                                            SPP-{{ $row->no_bukti }}
                                        @else
                                            TBP-{{ str_pad($row->no_bukti, 4, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </td>
                                    <td style="font-size: 11px;">{{ $row->rekening ?? '-' }}</td>
                                    <td>
                                        {{ $row->uraian }}
                                        @if($row->jenis === 'nihil_tu')
                                            <span class="badge badge-dark ml-1" style="font-size: 8px;">Setor Kasda</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '' }}</td>
                                    <td class="text-right">{{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '' }}</td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($totalSaldo = $totalSaldo + $row->debet - $row->kredit, 0, ',', '.') }}
                                    </td>
                                </tr>

                                {{-- Pajak GU --}}
                                @if($row->jenis === 'belanja_gu' && isset($pajakGu[$row->id]))
                                    @foreach($pajakGu[$row->id] as $pajak)
                                        <tr style="background-color: #f0fdf4;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-success">Di Pungut {{ $pajak->jenis_pajak }} <small class="text-muted">({{ $pajak->no_billing ?? '' }})</small></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo + $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr style="background-color: #fef2f2;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-danger">Di Setor {{ $pajak->jenis_pajak }} <small class="text-muted">(NTPN: {{ $pajak->ntpn ?? '-' }} | NTB: {{ $pajak->ntb ?? '-' }})</small></td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo - $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Pajak KKPD --}}
                                @if($row->jenis === 'belanja_kkpd' && isset($pajakKkpd[$row->id]))
                                    @foreach($pajakKkpd[$row->id] as $pajak)
                                        <tr style="background-color: #f0fdf4;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-success">Di Pungut {{ $pajak->jenis_pajak }} <small class="text-muted">({{ $pajak->no_billing ?? '' }})</small></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo + $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr style="background-color: #fef2f2;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-danger">Di Setor {{ $pajak->jenis_pajak }} <small class="text-muted">(NTPN: {{ $pajak->ntpn ?? '-' }} | NTB: {{ $pajak->ntb ?? '-' }})</small></td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo - $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Pajak TU --}}
                                @if($row->jenis === 'belanja_tu' && isset($pajakTu[$row->id]))
                                    @foreach($pajakTu[$row->id] as $pajak)
                                        <tr style="background-color: #f0fdf4;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-success">Di Pungut {{ $pajak->jenis_pajak }} <small class="text-muted">({{ $pajak->no_billing ?? '' }})</small></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo + $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr style="background-color: #fef2f2;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-danger">Di Setor {{ $pajak->jenis_pajak }} <small class="text-muted">(NTPN: {{ $pajak->ntpn ?? '-' }} | NTB: {{ $pajak->ntb ?? '-' }})</small></td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo - $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Pajak LS --}}
                                @if($row->jenis === 'belanja_ls' && isset($pajakLs[$row->id]))
                                    @foreach($pajakLs[$row->id] as $pajak)
                                        <tr style="background-color: #f0fdf4;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-success">Di Pungut {{ $pajak->jenis_pajak }} <small class="text-muted">({{ $pajak->no_billing ?? '' }})</small></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td></td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo + $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr style="background-color: #fef2f2;">
                                            <td></td><td></td><td></td><td></td>
                                            <td class="font-italic text-danger">Di Setor {{ $pajak->jenis_pajak }} <small class="text-muted">(NTPN: {{ $pajak->ntpn ?? '-' }} | NTB: {{ $pajak->ntb ?? '-' }})</small></td>
                                            <td></td>
                                            <td class="text-right">{{ number_format($pajak->nominal, 0, ',', '.') }}</td>
                                            <td class="text-right text-muted">{{ number_format($totalSaldo = $totalSaldo - $pajak->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tanda Tangan --}}
                <table style="width: 100%; margin-top: 30px; font-size: 13px;">
                    <tr>
                        <td width="50%" class="text-center" style="vertical-align: top;">
                            <p style="margin-bottom: 4px;">Mengetahui,</p>
                            <p style="margin-bottom: 70px; font-weight: 700;">PENGGUNA ANGGARAN</p>
                            <p style="margin-bottom: 2px; font-weight: 700; text-decoration: underline;">{{ $penggunaAnggaran->nama ?? '___________________' }}</p>
                            <p style="margin: 0;">NIP. {{ $penggunaAnggaran->nip ?? '___________________' }}</p>
                        </td>
                        <td width="50%" class="text-center" style="vertical-align: top;">
                            <p style="margin-bottom: 4px;">Wonosobo, {{ \Carbon\Carbon::parse($tanggalCetak ?? now())->translatedFormat('d F Y') }}</p>
                            <p style="margin-bottom: 70px; font-weight: 700;">BENDAHARA PENGELUARAN</p>
                            <p style="margin-bottom: 2px; font-weight: 700; text-decoration: underline;">{{ $bendaharaPengeluaran->nama ?? '___________________' }}</p>
                            <p style="margin: 0;">NIP. {{ $bendaharaPengeluaran->nip ?? '___________________' }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function printBkuAll() {
            var printContent = document.getElementById('print-area-all').innerHTML;
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>BKU Semua Transaksi</title>
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
                    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.css') }}">
                    <style>
                        body { margin: 0; padding: 15px; font-family: 'Source Sans Pro', sans-serif; font-size: 13px; }
                        .table th, .table td { font-size: 12px; padding: 5px 6px !important; }
                        .table thead th { font-size: 12px; font-weight: 700; }
                        @page { size: 215.9mm 330.2mm; margin: 12mm; }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>setTimeout(function(){ window.print(); window.close(); }, 500);<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endpush
