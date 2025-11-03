@props([
    'name',
    'id' => null,
    'type' => 'text',
    'label',
    'placeholder' => '',
    'required' => false,
    'value' => null,
    'validationRules' => null,
])

@php
    $fieldId = $id ?: $name;
    $hasBackendError = $errors->has($name);
    $validationAttrs = '';

    if ($required) {
        $validationAttrs .= ' required';
    }
    if ($type === 'email') {
        $validationAttrs .= ' data-error="' . __('visitor.email_format_invalid') . '"';
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
    <input type="{{ $type }}" class="form-control @if ($hasBackendError) is-invalid @endif"
        name="{{ $name }}" id="{{ $fieldId }}" placeholder="{{ $placeholder }}"
        value="{{ old($name, $value) }}" {!! $validationAttrs !!} data-error="{{ $requiredErrorMessage }}">

    @if ($hasBackendError)
        <div class="invalid-feedback">
            {{ $errors->first($name) }}
        </div>
    @endif

    <div class="help-block with-errors text-danger" style="font-size: 0.875rem; margin-top: 0.25rem;"></div>
</div>
