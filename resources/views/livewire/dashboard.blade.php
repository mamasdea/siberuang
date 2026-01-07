<div class="p-4">
    <style>
        .modern-stat-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .modern-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .modern-stat-card .icon-bg {
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        .modern-stat-card .content {
            position: relative;
            z-index: 2;
        }
        .bg-gradient-primary-soft { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .bg-gradient-success-soft { background: linear-gradient(135deg, #2af598 0%, #009efd 100%); color: white; }
        .bg-gradient-warning-soft { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: white; }
        .bg-gradient-danger-soft { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #feada6 100%); color: white; }
        .bg-gradient-info-soft { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); color: white; }
        .bg-gradient-dark-soft { background: linear-gradient(135deg, #434343 0%, #000000 100%); color: white; }
        
        .modern-table thead th {
            border: none;
            background-color: #f8f9fa;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        .modern-table tbody td {
            border-bottom: 1px solid #f1f1f1;
            padding: 1rem;
            vertical-align: middle;
        }
        .card-subkegiatan {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            transition: all 0.2s;
            background: white;
        }
        .card-subkegiatan:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
    </style>

    {{-- ===================== STAT BOXES ===================== --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-gradient-primary-soft h-100">
                <div class="card-body p-4 content d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="text-uppercase mb-2 font-weight-bold" style="letter-spacing: 1px;">Program</h5>
                        <h2 class="display-4 font-weight-bold mb-0">{{ $jumlahProgram ?? 0 }}</h2>
                    </div>
                    <div class="mt-3 font-weight-bold" style="font-size: 1.2rem;">Total Program Terdaftar</div>
                </div>
                <i class="fas fa-project-diagram icon-bg"></i>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-gradient-success-soft h-100">
                <div class="card-body p-4 content d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="text-uppercase mb-2 font-weight-bold" style="letter-spacing: 1px;">Kegiatan</h5>
                        <h2 class="display-4 font-weight-bold mb-0">{{ $jumlahKegiatan ?? 0 }}</h2>
                    </div>
                    <div class="mt-3 font-weight-bold" style="font-size: 1.2rem;">Total Kegiatan Aktif</div>
                </div>
                <i class="fas fa-tasks icon-bg"></i>
            </div>
        </div>

        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-gradient-warning-soft h-100">
                <div class="card-body p-4 content d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="text-uppercase mb-2 font-weight-bold" style="letter-spacing: 1px;">Sub Kegiatan</h5>
                        <h2 class="display-4 font-weight-bold mb-0">{{ $jumlahSubKegiatan ?? 0 }}</h2>
                    </div>
                    <div class="mt-3 font-weight-bold" style="font-size: 1.2rem;">Rincian Pelaksanaan</div>
                </div>
                <i class="fas fa-list icon-bg"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-white h-100">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 mr-3 bg-light text-primary">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="text-muted text-uppercase font-weight-bold mb-1">Total Anggaran</h5>
                            <h2 class="font-weight-bold mb-0 text-dark">Rp {{ number_format($totalAnggaran ?? 0, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 3px;">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-white h-100">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 mr-3 bg-light text-danger">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="text-muted text-uppercase font-weight-bold mb-1">Total Realisasi</h5>
                            <h2 class="font-weight-bold mb-0 text-dark">Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 3px;">
                        <div class="progress-bar bg-danger" style="width: {{ $persentaseRealisasi ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-4">
            <div class="modern-stat-card bg-gradient-dark-soft h-100 text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-white-50 text-uppercase font-weight-bold mb-1">Capaian Realisasi</h5>
                        <h2 class="font-weight-bold mb-0" style="font-size: 2.5rem;">{{ $persentaseRealisasi ?? 0 }}%</h2>
                    </div>
                    <div style="width: 80px; height: 80px; position:relative;">
                        <svg viewBox="0 0 36 36" class="circular-chart text-success">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3" />
                            <path class="circle" stroke-dasharray="{{ $persentaseRealisasi ?? 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        <i class="fas fa-percentage position-absolute text-white" style="top:50%; left:50%; transform:translate(-50%, -50%); opacity:0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== CHART ===================== --}}
    <div class="card border-0 shadow-sm rounded-lg mb-5" style="border-radius: 20px;">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="font-weight-bold text-dark">Grafik Realisasi Anggaran</h5>
            <p class="text-muted small">Tren realisasi anggaran per bulan dalam tahun berjalan.</p>
        </div>
        <div class="card-body px-4">
            <div class="chart-container" wire:ignore style="position: relative; height: 350px; width: 100%;">
                <canvas id="realisasiChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===================== RINGKASAN PER BIDANG ===================== --}}
    <div class="card border-0 shadow-sm rounded-lg mb-5" style="border-radius: 20px; overflow:hidden;">
        <div class="card-header bg-white border-bottom border-light pt-4 px-4 pb-3">
            <h5 class="font-weight-bold text-dark mb-0">Ringkasan per Bidang</h5>
        </div>
        <div class="table-responsive">
            <table class="table modern-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Bidang</th>
                        <th class="text-end">Anggaran</th>
                        <th class="text-end">Realisasi</th>
                        <th class="text-center" style="width: 150px">Capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bidangSummary as $index => $item)
                        <tr>
                            <td class="pl-4 text-muted">{{ $index + 1 }}</td>
                            <td class="font-weight-bold text-dark">{{ $item['bidang'] ?? 'Lainnya' }}</td>
                            <td class="text-end font-weight-600 text-secondary">Rp {{ number_format($item['total_anggaran'], 0, ',', '.') }}</td>
                            <td class="text-end font-weight-600 text-success">Rp {{ number_format($item['total_realisasi'], 0, ',', '.') }}</td>
                            <td class="text-center pr-4">
                                @php $p = $item['persen'] ?? 0; @endphp
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="font-weight-bold small mr-2">{{ number_format($p, 1) }}%</span>
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                        <div class="progress-bar bg-{{ $p > 80 ? 'success' : ($p > 50 ? 'info' : 'warning') }}" style="width: {{ $p }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== RINGKASAN SUB KEGIATAN ===================== --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="font-weight-bold text-dark mb-0">Detail Sub Kegiatan</h5>
        <span class="badge badge-pill badge-light border">{{ count($subKSummary) }} Kegiatan</span>
    </div>

    <div class="row">
        @forelse($subKSummary as $subk)
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card-subkegiatan h-100 p-3" style="cursor:pointer" wire:click="openSubKegiatanModal({{ $subk['id'] }})">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="badge badge-light text-primary border border-primary px-2 py-1 rounded small font-weight-bold">
                            {{ $subk['kode'] ?? '-' }}
                        </div>
                         @php $p = $subk['persen'] ?? 0; @endphp
                        <span class="small font-weight-bold text-{{ $p > 70 ? 'success' : ($p > 30 ? 'warning' : 'danger') }}">
                            {{ number_format($p, 1) }}%
                        </span>
                    </div>
                    <div class="font-weight-bold text-dark mb-3" style="font-size: 1.15rem; line-height: 1.4; min-height: 2.8em;">
                        {{ \Illuminate\Support\Str::limit($subk['nama'] ?? '-', 60) }}
                    </div>
                    
                    <div class="mb-3">
                         <div class="d-flex justify-content-between mb-1">
                             <span class="text-secondary">Anggaran</span>
                             <span class="text-dark font-weight-bold">Rp {{ number_format($subk['total_anggaran'] ?? 0, 0, ',', '.') }}</span>
                         </div>
                         <div class="d-flex justify-content-between">
                             <span class="text-secondary">Realisasi</span>
                             <span class="text-success font-weight-bold">Rp {{ number_format($subk['total_realisasi'] ?? 0, 0, ',', '.') }}</span>
                         </div>
                    </div>

                    <div class="progress mb-3" style="height:8px; border-radius:4px;">
                        <div class="progress-bar bg-gradient-{{ $p > 70 ? 'success' : ($p > 30 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ min(100, $p) }}%;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-around border-top pt-3 mt-auto">
                        <div class="text-center">
                            <div class="small text-muted text-uppercase font-weight-bold">GU</div>
                            <div class="font-weight-bold">{{ number_format(($subk['total_gu'] ?? 0)/1000000, 1) }}M</div>
                        </div>
                        <div class="text-center border-left pl-3 ml-3 border-right pr-3 mr-3">
                            <div class="small text-muted text-uppercase font-weight-bold">KKPD</div>
                            <div class="font-weight-bold">{{ number_format(($subk['total_kkpd'] ?? 0)/1000000, 1) }}M</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted text-uppercase font-weight-bold">LS</div>
                            <div class="font-weight-bold">{{ number_format(($subk['total_ls'] ?? 0)/1000000, 1) }}M</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border-0 shadow-sm text-center py-5 rounded-lg">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                    <p class="mb-0 text-muted">Belum ada data sub kegiatan.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ===================== MODAL RINCIAN RKA ===================== --}}
    <div wire:ignore.self class="modal fade" id="subkModal" tabindex="-1" aria-labelledby="subkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title font-weight-bold" id="subkModalLabel">Rincian RKA</h5>
                        @if ($modalSubKegiatan)
                            <div class="text-muted small mt-1">
                                <span class="badge badge-light border mr-2">{{ $modalSubKegiatan->kode ?? '' }}</span>
                                {{ $modalSubKegiatan->nama ?? '' }}
                            </div>
                        @endif
                    </div>
                    <button type="button" class="close" wire:click="closeSubKegiatanModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div class="table-responsive shadow-sm rounded-lg border">
                        <table class="table modern-table mb-0 table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width:140px;">Kode RKA</th>
                                    <th>Uraian</th>
                                    <th class="text-end">Anggaran</th>
                                    <th class="text-end">GU</th>
                                    <th class="text-end">KKPD</th>
                                    <th class="text-end">LS</th>
                                    <th class="text-end">Realisasi</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($modalRkaRows as $r)
                                    <tr>
                                        <td><span class="font-weight-bold text-dark">{{ $r['kode'] ?? '-' }}</span></td>
                                        <td>{{ $r['uraian'] ?? '-' }}</td>
                                        <td class="text-end font-family-monospace text-muted">{{ number_format($r['anggaran'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end font-family-monospace text-muted">{{ number_format($r['gu'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end font-family-monospace text-muted">{{ number_format($r['kkpd'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end font-family-monospace text-muted">{{ number_format($r['ls'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end font-weight-bold text-success">{{ number_format($r['realisasi'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                             @php $pct = $r['persen'] ?? 0; @endphp
                                             <span class="badge badge-{{ $pct > 80 ? 'success' : ($pct > 50 ? 'info' : 'warning') }}">{{ number_format($pct, 1) }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada RKA untuk sub kegiatan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="2" class="text-end text-uppercase text-secondary small">Total</td>
                                    <td class="text-end text-dark">{{ number_format($modalTotals['anggaran'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">{{ number_format($modalTotals['gu'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">{{ number_format($modalTotals['kkpd'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">{{ number_format($modalTotals['ls'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">{{ number_format($modalTotals['realisasi'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end text-dark">{{ number_format($modalTotals['persen'] ?? 0, 2, ',', '.') }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 bg-white">
                    <button class="btn btn-block btn-light font-weight-bold text-uppercase text-secondary" style="border-radius:12px;" wire:click="closeSubKegiatanModal">Tutup Rincian</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === Setup Modal ===
            const subkModalEl = document.getElementById('subkModal');
            if (subkModalEl) {
                const subkModal = new bootstrap.Modal(subkModalEl, {backdrop: 'static'}); // Fix backbone
                window.addEventListener('open-subk-modal', () => subkModal.show());
                window.addEventListener('close-subk-modal', () => subkModal.hide());
            }

            // === Init Chart ===
            // Update chart colors to match theme
            const chartCanvas = document.getElementById('realisasiChart');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                // Gradient for chart
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(102, 126, 234, 0.5)');   
                gradient.addColorStop(1, 'rgba(118, 75, 162, 0.1)');

                new Chart(ctx, {
                    type: 'line', // Changed to Line for better trend visualization, or Bar with rounded corners
                    data: {
                        labels: @json($chartData['labels'] ?? []),
                         datasets: [{
                            label: 'Realisasi Anggaran',
                            data: @json($chartData['values'] ?? []),
                            backgroundColor: gradient,
                            borderColor: '#667eea',
                            borderWidth: 2,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#764ba2',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#333',
                                bodyColor: '#666',
                                borderColor: '#ddd',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { borderDash: [2, 4], color: '#f0f0f0', drawBorder: false },
                                ticks: { font: { family: "'Inter', sans-serif" }, callback: (val) => 'Rp ' + val.toLocaleString('id-ID') }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: "'Inter', sans-serif" } }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
