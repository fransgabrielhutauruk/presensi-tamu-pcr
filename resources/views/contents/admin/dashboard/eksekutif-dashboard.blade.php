{{-- Dashboard khusus untuk Eksekutif --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    {{-- Widget Cards --}}
    @foreach($dashboardData['widgets'] as $widget)
    <div class="col-md-6 col-lg-6 col-xl-4">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800 fs-2hx">{{ $widget['value'] }}</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">{{ $widget['title'] }}</span>
                </h3>
                <div class="card-toolbar">
                    <i class="ki-outline {{ $widget['icon'] }} fs-3x text-{{ $widget['color'] }}"></i>
                </div>
            </div>
            <div class="card-body d-flex align-items-end">
                <div class="w-100">
                    <div class="separator separator-dashed border-{{ $widget['color'] }}"></div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    {{-- Statistik Kunjungan --}}
    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Statistik Status Kunjungan</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Keseluruhan data</span>
                </h3>
            </div>
            <div class="card-body pt-6">
                <canvas id="chartStatistikKunjungan" height="300"></canvas>
            </div>
        </div>
    </div>

    {{-- Trend Kunjungan 7 Hari Terakhir --}}
    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Trend Kunjungan</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">7 Hari Terakhir</span>
                </h3>
            </div>
            <div class="card-body pt-6">
                <canvas id="chartTrendKunjungan" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-5 g-xl-10">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Ringkasan Eksekutif</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-4">
                        <div class="border border-dashed border-gray-300 rounded p-5">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-chart-simple-2 fs-3x text-success me-5"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">
                                        {{ number_format($dashboardData['charts']['statistikKunjungan']['total']) }}
                                    </div>
                                    <div class="text-gray-400 fw-semibold">Total Seluruh Kunjungan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border border-dashed border-gray-300 rounded p-5">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-check-circle fs-3x text-primary me-5"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">
                                        {{ number_format($dashboardData['charts']['statistikKunjungan']['validated']) }}
                                    </div>
                                    <div class="text-gray-400 fw-semibold">Kunjungan Tervalidasi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border border-dashed border-gray-300 rounded p-5">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-time fs-3x text-warning me-5"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">
                                        {{ number_format($dashboardData['charts']['statistikKunjungan']['pending']) }}
                                    </div>
                                    <div class="text-gray-400 fw-semibold">Menunggu Validasi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart Statistik Status Kunjungan (Pie Chart)
    const ctxStatistik = document.getElementById('chartStatistikKunjungan');
    if (ctxStatistik) {
        const statistik = @json($dashboardData['charts']['statistikKunjungan']);
        
        new Chart(ctxStatistik, {
            type: 'doughnut',
            data: {
                labels: ['Tervalidasi', 'Pending', 'Ditolak'],
                datasets: [{
                    data: [statistik.validated, statistik.pending, statistik.rejected],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Chart Trend Kunjungan (Line Chart)
    const ctxTrend = document.getElementById('chartTrendKunjungan');
    if (ctxTrend) {
        const trendData = @json($dashboardData['charts']['trendKunjungan']);
        
        const labels = trendData.map(item => {
            const date = new Date(item.tanggal);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        const data = trendData.map(item => item.total);

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: data,
                    fill: true,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>
@endpush
