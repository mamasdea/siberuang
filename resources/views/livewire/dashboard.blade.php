<div>
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

    <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
        <canvas id="realisasiChart"></canvas>
    </div>


</div>


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('realisasiChart').getContext('2d');
            var realisasiChart = new Chart(ctx, {
                type: 'bar', // Tetap gunakan bar chart
                data: {
                    labels: @json($chartData['labels']), // Bulan di sumbu Y
                    datasets: [{
                        label: 'Realisasi Anggaran',
                        data: @json($chartData['values']), // Realisasi di sumbu X
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Balik sumbu: bulan di Y, realisasi di X
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
        });
    </script>
@endpush
