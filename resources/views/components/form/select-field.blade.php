@props([
    'name',
    'id' => null,
    'label',
    'options' => [],
    'required' => false,
    'placeholder' => null,
    'value' => null,
])

@php
    $fieldId = $id ?: $name;
    $defaultPlaceholder = $placeholder ?: __('visitor.select_category');
    $requiredErrorMessage = __('visitor.field_required', ['field' => $label]);
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold control-label" for="{{ $fieldId }}">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <select class="form-select @error($name) is-invalid @enderror" name="{{ $name }}" id="{{ $fieldId }}"
        {{ $required ? 'required' : '' }} data-error="{{ $requiredErrorMessage }}">
        <option value="">{{ $defaultPlaceholder }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

    <div class="help-block with-errors"></div>
</div>
