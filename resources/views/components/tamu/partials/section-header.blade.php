@props(['title', 'icon' => null])

<div class="mb-3 mt-4">
    <h3 class="d-flex aling-items-center gap-2" style="font-size: 1.125rem; font-weight: 600">
        @if ($icon)
            <span>{{ $icon }}</span>
        @endif
        <span>{{ $title }}</span>
    </h3>
</div>
