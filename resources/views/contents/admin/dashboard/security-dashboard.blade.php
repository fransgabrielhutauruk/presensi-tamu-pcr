{{-- Dashboard khusus untuk Security --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    {{-- Widget Cards untuk Security --}}
    @foreach($dashboardData['widgets'] as $widget)
    <div class="col-md-6 col-lg-4">
        <div class="card card-flush h-md-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex flex-stack">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-light-{{ $widget['color'] }}">
                            <i class="ki-outline {{ $widget['icon'] }} fs-2x text-{{ $widget['color'] }}"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column text-end">
                        <span class="fw-bold fs-1 text-gray-800">{{ $widget['value'] }}</span>
                    </div>
                </div>
                <div class="separator separator-dashed my-3"></div>
                <span class="text-gray-600 fw-semibold fs-6">{{ $widget['title'] }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
    function validateVisit(id) {
        if (confirm('Apakah Anda yakin ingin memvalidasi kunjungan ini?')) {
            fetch(`{{ url('app/kunjungan/validate') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    alert('Kunjungan berhasil divalidasi');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal memvalidasi kunjungan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memvalidasi kunjungan');
            });
        }
    }

    // Auto refresh setiap 5 menit untuk update data real-time
    setInterval(() => {
        location.reload();
    }, 300000); // 5 menit
</script>
@endpush
