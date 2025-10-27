@props(['title', 'subtitle' => 'POLITEKNIK CALTEX RIAU', 'question' => null, 'img' => false])

<header class="text-center wow fadeInUp">
    @if ($img)
        <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
            class="mx-auto d-block mb-3 img-fluid mt-5" style="width:30%" />
    @endif

    <h1 class="fw-bold fs-2 text-uppercase" style="font-size: 1.75rem; letter-spacing: 0.025em;">
        {{ $title }}
    </h1>
    <p class="mt-1 text-muted mb-3 text-uppercase" style="font-weight: 500;">
        {!! $subtitle !!}
    </p>
    <div class="mx-auto rounded-pill" style="height: 3px; width: 5rem; background-color: var(--primary-color);"></div>
</header>

@if ($question)
    <div style="margin-top: 2.5rem;">
        <h2 class="fw-semibold d-flex align-items-center justify-content-center gap-2 wow fadeInUp"
            style="font-size: 1rem;">
            <span class="fw-bold" style="font-size: 1.125rem;">{{ $question }}</span>
        </h2>
    </div>
@endif
