<div>
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $jumlahProgram }}</h3>
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
                    <h3>{{ $jumlahKegiatan }}</h3>
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
                    <h3>{{ $jumlahSubKegiatan }}</h3>
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
                    <h3>Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</h3>
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
                    <h3>Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h3>
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
                    <h3>{{ $persentaseRealisasi }}%</h3>
                    <p>Persentase Realisasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-8 bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-bold mb-4">Realisasi Anggaran per Bulan</h2>
        <canvas id="realisasiChart"></canvas>
    </div>
</div>

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('realisasiChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Realisasi Anggaran (Rp)',
                        data: @json($chartData['values']),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Menampilkan bulan di sumbu Y
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    let value = tooltipItem.raw;
                                    return 'Realisasi: Rp ' + new Intl.NumberFormat('id-ID').format(
                                        value);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
