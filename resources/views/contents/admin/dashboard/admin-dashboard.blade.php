<div class="card mb-5 mb-xl-6">
    <div class="card-body p-5">
        <div class="row g-5 g-xl-8 mb-5 mb-xl-6">
            @foreach (array_slice($dashboardData['widgets'], 0, 4) as $widget)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ $widget['link'] ?? '#' }}" class="hoverable card-xl-stretch bg-white"
                        style="text-decoration: none;">
                        <div class="rounded p-5 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline {{ $widget['icon'] }} fs-3x text-{{ $widget['color'] }} me-5"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">
                                        {{ $widget['value'] }}
                                    </div>
                                    <div class="text-gray-700 fw-semibold">{{ $widget['title'] }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="row g-5 g-xl-8">
            @foreach (array_slice($dashboardData['widgets'], 4, 4) as $widget)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ $widget['link'] ?? '#' }}" class="hoverable card-xl-stretch"
                        style="text-decoration: none;">
                        <div class="rounded p-5 shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline {{ $widget['icon'] }} fs-3x text-{{ $widget['color'] }} me-5"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-800 fw-bold fs-2">
                                        {{ $widget['value'] }}
                                    </div>
                                    <div class="text-gray-700 fw-semibold">{{ $widget['title'] }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-5">
    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Kunjungan Per Bulan</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Tahun {{ now()->year }}</span>
                </h3>
            </div>
            <div class="card-body pt-6">
                <canvas id="chartKunjunganPerBulan" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Event Aktif Hari Ini</span>
                    <span
                        class="text-gray-400 mt-1 fw-semibold fs-6">{{ tanggal(\Carbon\Carbon::now()) }}</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('app.event.index') }}" class="btn btn-sm btn-light-primary">
                        <i class="ki-outline ki-calendar fs-3"></i>
                        Lihat Semua Event
                    </a>
                </div>
            </div>
            <div class="card-body pt-6">
                @if ($dashboardData['charts']['eventAktifHariIni']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-stripped align-middle gs-0 gy-4 my-0">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                    <th class="p-0 w-50px pb-1">No</th>
                                    <th class="ps-0 min-w-200px pb-1">Nama Event</th>
                                    <th class="text-center min-w-100px pb-1">Waktu</th>
                                    <th class="text-end min-w-100px pb-1">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dashboardData['charts']['eventAktifHariIni'] as $index => $event)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="ps-0">
                                            <a href="{{ route('app.event.show', $event->event_id) }}"
                                                class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">
                                                {{ $event->nama_event }}
                                            </a>
                                            @if ($event->deskripsi_event)
                                                <div class="text-gray-400 fs-7">
                                                    {{ Str::limit($event->deskripsi_event, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($event->waktu_mulai_event)
                                                <span class="badge badge-light-success">
                                                    {{ \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-600">{{ $event->lokasi_event ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="ki-outline ki-calendar-remove fs-4x text-gray-400"></i>
                        <p class="text-gray-600 fs-6 mt-3 mb-0">Tidak ada event hari ini</p>
                        <p class="text-gray-400 fs-7">Event akan ditampilkan di sini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('theme/js/common.js') }}"></script>
    <script>
        const ctx = document.getElementById('chartKunjunganPerBulan');
        if (ctx) {
            const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const chartData = @json($dashboardData['charts']['kunjunganPerBulan']);

            const labels = chartData.map(item => bulanNames[item.bulan - 1]);
            const data = chartData.map(item => item.total);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
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
                    }
                }
            });
        }
    </script>
@endpush
