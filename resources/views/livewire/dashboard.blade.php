<div>
    {{-- ===================== STAT BOXES ===================== --}}
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $jumlahProgram ?? 0 }}</h3>
                    <p>Jumlah Program</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $jumlahKegiatan ?? 0 }}</h3>
                    <p>Jumlah Kegiatan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $jumlahSubKegiatan ?? 0 }}</h3>
                    <p>Jumlah Sub Kegiatan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-indigo">
                <div class="inner">
                    <h3>Rp {{ number_format($totalAnggaran ?? 0, 0, ',', '.') }}</h3>
                    <p>Total Anggaran</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>Rp {{ number_format($totalRealisasi ?? 0, 0, ',', '.') }}</h3>
                    <p>Total Realisasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $persentaseRealisasi ?? 0 }}%</h3>
                    <p>Persentase Realisasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== CHART ===================== --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Realisasi Anggaran per Bulan</h3>
        </div>
        <div class="card-body">
            <div class="chart-container" wire:ignore style="position: relative; height: 350px; width: 100%;">
                <canvas id="realisasiChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===================== RINGKASAN PER BIDANG ===================== --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ringkasan per Bidang</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Bidang</th>
                        <th class="text-end">Anggaran</th>
                        <th class="text-end">Realisasi</th>
                        <th class="text-center" style="width: 100px">Persen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bidangSummary as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}.</td>
                            <td>{{ $item['bidang'] ?? 'Lainnya' }}</td>
                            <td class="text-end">Rp {{ number_format($item['total_anggaran'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item['total_realisasi'], 0, ',', '.') }}</td>
                            <td class="text-center">
                                @php $p = $item['persen'] ?? 0; @endphp
                                <span class="badge bg-{{ $p > 70 ? 'success' : ($p > 40 ? 'primary' : 'danger') }}">
                                    {{ number_format($p, 2, ',', '.') }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== RINGKASAN SUB KEGIATAN ===================== --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Realisasi per Sub Kegiatan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($subKSummary as $subk)
                    <div class="col-md-6 col-xl-3 mb-3">
                        <div class="card card-{{ $subk['color_name'] ?? 'light' }} card-outline h-100" style="cursor:pointer"
                            wire:click="openSubKegiatanModal({{ $subk['id'] }})">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="me-2">
                                        <div class="fw-bold">{{ $subk['kode'] ?? '-' }}</div>
                                        <div class="text-muted small">{{ $subk['nama'] ?? '-' }}</div>
                                    </div>
                                    @php $p = $subk['persen'] ?? 0; @endphp
                                    <span class="badge bg-{{ $p > 70 ? 'success' : ($p > 40 ? 'primary' : 'danger') }}">
                                        {{ number_format($p, 2, ',', '.') }}%
                                    </span>
                                </div>

                                <div class="mb-2 small text-muted">
                                    Anggaran: Rp {{ number_format($subk['total_anggaran'] ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="mb-2 small">
                                    Realisasi:
                                    <strong>Rp
                                        {{ number_format($subk['total_realisasi'] ?? 0, 0, ',', '.') }}</strong>
                                </div>

                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ min(100, $p) }}%;"
                                        aria-valuenow="{{ $p }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>

                                <div class="mt-2 d-flex justify-content-between text-muted small"
                                    style="gap:8px; flex-wrap:wrap;">
                                    <span>GU: Rp {{ number_format($subk['total_gu'] ?? 0, 0, ',', '.') }}</span>
                                    <span>KKPD: Rp {{ number_format($subk['total_kkpd'] ?? 0, 0, ',', '.') }}</span>
                                    <span>LS: Rp {{ number_format($subk['total_ls'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">Belum ada data sub kegiatan.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===================== MODAL RINCIAN RKA ===================== --}}
    <div class="modal fade" id="subkModal" tabindex="-1" aria-labelledby="subkModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="subkModalLabel">Rincian RKA per Sub Kegiatan</h5>
                        @if ($modalSubKegiatan)
                            <div class="small text-muted">
                                {{ $modalSubKegiatan->kegiatan->program->kode ?? '' }} {{ $modalSubKegiatan->kegiatan->kode ?? '' }} {{ $modalSubKegiatan->kode ?? '' }} — {{ $modalSubKegiatan->nama ?? '' }}<br>
                                {{ $modalSubKegiatan->kegiatan->program->nama ?? '' }}
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn-close" aria-label="Close"
                        wire:click="closeSubKegiatanModal"></button>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
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
                                        <td>{{ $r['kode'] ?? '-' }}</td>
                                        <td>{{ $r['uraian'] ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($r['anggaran'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($r['gu'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($r['kkpd'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($r['ls'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($r['realisasi'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($r['persen'] ?? 0, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada RKA untuk sub
                                            kegiatan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL</th>
                                    <th class="text-end">
                                        {{ number_format($modalTotals['anggaran'] ?? 0, 0, ',', '.') }}</th>
                                    <th class="text-end">{{ number_format($modalTotals['gu'] ?? 0, 0, ',', '.') }}
                                    </th>
                                    <th class="text-end">{{ number_format($modalTotals['kkpd'] ?? 0, 0, ',', '.') }}
                                    </th>
                                    <th class="text-end">{{ number_format($modalTotals['ls'] ?? 0, 0, ',', '.') }}
                                    </th>
                                    <th class="text-end fw-bold">
                                        {{ number_format($modalTotals['realisasi'] ?? 0, 0, ',', '.') }}</th>
                                    <th class="text-end">
                                        {{ number_format($modalTotals['persen'] ?? 0, 2, ',', '.') }}%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeSubKegiatanModal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Modal control + Chart init --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === Setup Modal ===
            const subkModalEl = document.getElementById('subkModal');
            if (subkModalEl) {
                const subkModal = new bootstrap.Modal(subkModalEl);
                window.addEventListener('open-subk-modal', () => {
                    subkModal.show();
                });

                window.addEventListener('close-subk-modal', () => {
                    subkModal.hide();
                });
            }

            // === Init Chart ===
            const chartCanvas = document.getElementById('realisasiChart');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['labels'] ?? []),
                        datasets: [{
                            label: 'Realisasi Anggaran',
                            data: @json($chartData['values'] ?? []),
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Realisasi Anggaran (Rp)'
                                },
                                beginAtZero: true
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Bulan'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
