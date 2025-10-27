@props(['name', 'id', 'value', 'icon' => '📋', 'label', 'delay' => '0.2s', 'required' => false, 'checked' => false])

<label for="{{ $id }}"
    class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp"
    data-wow-delay="{{ $delay }}" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">

    <span class="position-relative d-flex align-items-center justify-content-center"
        style="height: 1.25rem; width: 1.25rem;">
        <input class="radio-input"
            style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;"
            type="radio" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
            {{ $required ? 'required' : '' }} {{ $checked ? 'checked' : '' }}>
        <span class="radio-dot position-absolute rounded-circle"
            style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
    </span>

    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">{{ $icon }}</span>
    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">{{ $label }}</span>
</label>
