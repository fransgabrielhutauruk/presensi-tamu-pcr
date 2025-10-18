@extends('layouts.apps')

@section('toolbar')
<div class="d-flex align-items-center gap-3 w-100">
    <h1 class="text-gray-800 fw-bold my-1 fs-3">Keen Icons Gallery</h1>
    <div class="ms-auto w-300px">
        <input type="text" id="iconSearch" class="form-control form-control-solid" placeholder="Cari icon (mis. setting, graph, user)...">
    </div>
</div>
@endsection

@section('content')
<div class="app-container container-fluid">
    <div class="alert alert-info d-flex align-items-center"> 
        <i class="ki-duotone ki-information-2 fs-2 me-3"><i class="path1"></i><i class="path2"></i></i>
        <div>
            Gunakan pencarian di kanan atas. Klik pada nama class untuk menyalin. Variasi style: <code>ki-outline</code>, <code>ki-solid</code>, <code>ki-duotone</code>.
        </div>
    </div>

    <div class="mb-8">
        <h3 class="fw-bold mb-4">Outline ({{ count($outline) }})</h3>
        <div class="row g-3" id="grid-outline">
            @foreach ($outline as $name)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 icon-card" data-name="{{ $name }}" data-family="outline">
                    <div class="card h-100 hover-elevate-up">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center gap-3 py-6">
                            <i class="ki-outline ki-{{ $name }} fs-2hx"></i>
                            <div class="text-muted small copyable" data-class="ki-outline ki-{{ $name }}">ki-outline ki-{{ $name }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <h3 class="fw-bold mb-4">Solid ({{ count($solid) }})</h3>
        <div class="row g-3" id="grid-solid">
            @foreach ($solid as $name)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 icon-card" data-name="{{ $name }}" data-family="solid">
                <div class="card h-100 hover-elevate-up">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center gap-3 py-6">
                            <i class="ki-solid ki-{{ $name }} fs-2hx"></i>
                            <div class="text-muted small copyable" data-class="ki-solid ki-{{ $name }}">ki-solid ki-{{ $name }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-8">
        <h3 class="fw-bold mb-4">Duotone ({{ count($duotone) }})</h3>
        <div class="row g-3" id="grid-duotone">
            @foreach ($duotone as $name)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 icon-card" data-name="{{ $name }}" data-family="duotone">
                    <div class="card h-100 hover-elevate-up">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center gap-3 py-6">
                            <i class="ki-duotone ki-{{ $name }} fs-2hx"><i class="path1"></i><i class="path2"></i></i>
                            <div class="text-muted small copyable" data-class="ki-duotone ki-{{ $name }}">ki-duotone ki-{{ $name }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const q = document.getElementById('iconSearch');
    const cards = Array.from(document.querySelectorAll('.icon-card'));
    function filter() {
        const v = (q.value || '').trim().toLowerCase();
        cards.forEach(c => {
            const name = c.getAttribute('data-name');
            c.style.display = !v || name.includes(v) ? '' : 'none';
        });
    }
    q && q.addEventListener('input', filter);

    document.querySelectorAll('.copyable').forEach(el => {
        el.style.cursor = 'pointer';
        el.title = 'Klik untuk menyalin';
        el.addEventListener('click', async () => {
            const cls = el.getAttribute('data-class');
            try {
                await navigator.clipboard.writeText(cls);
                const old = el.textContent;
                el.textContent = 'Disalin: ' + cls;
                setTimeout(() => el.textContent = old, 1200);
            } catch (e) {
                console.warn('Copy failed', e);
            }
        });
    });
})();
</script>
@endpush
