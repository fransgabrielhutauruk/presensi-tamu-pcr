@props(['name', 'label', 'options' => [], 'required' => false, 'value' => null])

@php
    $requiredErrorMessage = __('visitor.field_required', ['field' => $label]);
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold control-label">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <div class="d-flex gap-3">
        @foreach ($options as $optionValue => $optionLabel)
            <div class="form-check">
                <input class="form-check-input @error($name) is-invalid @enderror" type="radio"
                    name="{{ $name }}" id="{{ $name }}_{{ $loop->index }}" value="{{ $optionValue }}"
                    {{ old($name, $value) == $optionValue ? 'checked' : '' }} {{ $required ? 'required' : '' }}
                    data-error="{{ $requiredErrorMessage }}">
                <label class="form-check-label" for="{{ $name }}_{{ $loop->index }}">
                    {{ $optionLabel }}
                </label>
            </div>
        @endforeach
    </div>

    @error($name)
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
    @enderror

    <div class="help-block with-errors"></div>
</div>
