@props([
    'name',
    'id' => null,
    'label',
    'placeholder' => '',
    'required' => false,
    'value' => null,
    'rows' => 3,
    'validationRules' => null,
])

@php
    $fieldId = $id ?: $name;
    $hasBackendError = $errors->has($name);
    $validationAttrs = '';

    if ($required) {
        $validationAttrs .= ' required';
    }

    if ($validationRules) {
        if (is_array($validationRules)) {
            foreach ($validationRules as $rule => $value) {
                if (is_numeric($rule)) {
                    $validationAttrs .= ' ' . $value;
                } else {
                    if ($rule === 'required' && $value) {
                        $validationAttrs .= ' required';
                    } elseif ($rule === 'minlength') {
                        $validationAttrs .= ' data-minlength="' . $value . '"';
                    } elseif ($rule === 'pattern') {
                        $validationAttrs .= ' pattern="' . $value . '"';
                    }
                }
            }
        } else {
            $validationAttrs .= ' ' . $validationRules;
        }
    }
    
    $requiredErrorMessage = __('visitor.field_required', ['field' => $label]);
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold control-label" for="{{ $fieldId }}">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <textarea class="form-control @if ($hasBackendError) is-invalid @endif" name="{{ $name }}"
        id="{{ $fieldId }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" {!! $validationAttrs !!}
        data-error="{{ $requiredErrorMessage }}">{{ old($name, $value) }}</textarea>

    @if ($hasBackendError)
        <div class="invalid-feedback">
            {{ $errors->first($name) }}
        </div>
    @endif

    <div class="help-block with-errors text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;"></div>
</div>
