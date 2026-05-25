@props([
    'name',
    'id' => null,
    'label',
    'options' => [],
    'required' => false,
    'placeholder' => null,
    'value' => null,
    'dataCy' => null,
    'placeholderDisabled' => false,
])

@php
    $fieldId = $id ?: $name;
    $fieldDataCy = $dataCy ?: 'select-' . $name;
    $defaultPlaceholder = $placeholder ?: __('visitor.select_category');
    $requiredErrorMessage = __('visitor.field_required', ['field' => $label]);
    $selectedValue = old($name, $value);
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold control-label" for="{{ $fieldId }}">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <select class="form-select @error($name) is-invalid @enderror" name="{{ $name }}" id="{{ $fieldId }}"
        data-cy="{{ $fieldDataCy }}" {{ $required ? 'required' : '' }} data-error="{{ $requiredErrorMessage }}">
        <option value="" {{ $placeholderDisabled ? 'disabled' : '' }}
            {{ $selectedValue === null || $selectedValue === '' ? 'selected' : '' }}>
            {{ $defaultPlaceholder }}
        </option>
        @foreach ($options as $optionValue => $optionLabel)
            @if (is_array($optionLabel))
                <optgroup label="{{ $optionValue }}">
                    @foreach ($optionLabel as $groupValue => $groupLabel)
                        <option value="{{ $groupValue }}" {{ $selectedValue == $groupValue ? 'selected' : '' }}>
                            {{ $groupLabel }}
                        </option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optionValue }}" {{ $selectedValue == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endif
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback" data-cy="error-{{ $name }}">
            {{ $message }}
        </div>
    @enderror

    <div class="help-block with-errors"></div>
</div>
