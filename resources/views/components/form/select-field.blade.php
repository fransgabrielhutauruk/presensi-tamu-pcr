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
    'multiple' => false,
])

@php
    $fieldId = $id ?: $name;
    $fieldDataCy = $dataCy ?: 'select-' . $name;
    $defaultPlaceholder = $placeholder ?: __('visitor.select_category');
    $requiredErrorMessage = __('visitor.select_required', ['field' => $label]);
    $selectedValue = old($name, $value);
    $selectedArray = $multiple ? (array) ($selectedValue ?? []) : null;
    $guardId = $fieldId . '-guard';
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold control-label" @if(!$multiple) for="{{ $fieldId }}" @endif>
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if ($multiple)
        {{-- Hidden guard input: required & unfocusable, toggled by JS based on checkbox state --}}
        <input type="text"
               id="{{ $guardId }}"
               name="{{ $guardId }}"
               tabindex="-1"
               aria-hidden="true"
               autocomplete="off"
               style="opacity:0; height:0; width:0; padding:0; border:0; position:absolute;"
               {{ $required ? 'required' : '' }}
               data-error="{{ $requiredErrorMessage }}"
               data-cy="{{ $fieldDataCy }}-guard">

        <div id="{{ $fieldId }}-checkboxes"
             class="checkbox-group-wrapper @error($name) is-invalid-group @enderror"
             data-cy="{{ $fieldDataCy }}"
             data-guard="{{ $guardId }}">
            @foreach ($options as $optionValue => $optionLabel)
                @php
                    $checkboxId = $fieldId . '-' . Str::slug($optionValue);
                    $isChecked = in_array($optionValue, $selectedArray);
                @endphp
                <div class="form-check-custom">
                    <input class="form-check-input-custom"
                           type="checkbox"
                           name="{{ $name }}[]"
                           id="{{ $checkboxId }}"
                           value="{{ $optionValue }}"
                           data-cy="{{ $fieldDataCy }}-{{ Str::slug($optionValue) }}"
                           {{ $isChecked ? 'checked' : '' }}>
                    <label class="form-check-label-custom" for="{{ $checkboxId }}">
                        {{ $optionLabel }}
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Error from server-side validation --}}
        @error($name)
            <div class="invalid-feedback d-block" data-cy="error-{{ $name }}">
                {{ $message }}
            </div>
        @enderror

        {{-- Error from client-side validation (shown via JS) --}}
        <div id="{{ $guardId }}-error"
             class="invalid-feedback"
             style="display:none;"
             data-cy="error-{{ $name }}-client">
            {{ $requiredErrorMessage }}
        </div>

        <script>
            (function () {
                const groupEl  = document.getElementById('{{ $fieldId }}-checkboxes');
                const guardEl  = document.getElementById('{{ $guardId }}');
                const errorEl  = document.getElementById('{{ $guardId }}-error');
                if (!groupEl || !guardEl) return;

                function showError() {
                    groupEl.classList.add('is-invalid-group');
                    if (errorEl) errorEl.style.display = 'block';
                }

                function hideError() {
                    groupEl.classList.remove('is-invalid-group');
                    if (errorEl) errorEl.style.display = 'none';
                }

                function syncGuard() {
                    const anyChecked = groupEl.querySelectorAll('input[type="checkbox"]:checked').length > 0;
                    guardEl.value = anyChecked ? 'checked' : '';
                    if (anyChecked) hideError();
                }

                // Intercept the native 'invalid' event so the browser tooltip is suppressed
                // and we show our own styled error message instead.
                guardEl.addEventListener('invalid', function (e) {
                    e.preventDefault();
                    showError();
                });

                groupEl.addEventListener('change', syncGuard);

                // Reset error state when the parent form is reset
                const formEl = guardEl.closest('form');
                if (formEl) {
                    formEl.addEventListener('reset', function () {
                        guardEl.value = '';
                        hideError();
                    });
                }

                syncGuard(); // sync on page load (repopulate after validation error)
            })();
        </script>

    @else
        <select class="form-select @error($name) is-invalid @enderror"
                name="{{ $name }}"
                id="{{ $fieldId }}"
                data-cy="{{ $fieldDataCy }}"
                {{ $required ? 'required' : '' }}
                data-error="{{ $requiredErrorMessage }}">
            <option value="{{ '' }}" {{ $placeholderDisabled ? 'disabled' : '' }}
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
    @endif

    <div class="help-block with-errors"></div>
</div>

<style>
    .checkbox-group-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out;
    }

    .checkbox-group-wrapper.is-invalid-group {
        border-color: #dc3545;
    }

    .form-check-custom {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.35rem 0.5rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .form-check-custom:hover {
        background-color: #f0f4ff;
    }

    .form-check-input-custom {
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 0.25rem;
        border: 2px solid #adb5bd;
        cursor: pointer;
        flex-shrink: 0;
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        transition: background-color 0.15s ease, border-color 0.15s ease;
        position: relative;
    }

    .form-check-input-custom:checked {
        background-color: var(--primary-color, #0d6efd);
        border-color: var(--primary-color, #0d6efd);
    }

    .form-check-input-custom:checked::after {
        content: '';
        display: block;
        position: absolute;
        top: 1px;
        left: 4px;
        width: 5px;
        height: 9px;
        border: 2px solid #fff;
        border-top: none;
        border-left: none;
        transform: rotate(45deg);
    }

    .form-check-input-custom:focus {
        outline: 2px solid var(--primary-color, #0d6efd);
        outline-offset: 2px;
    }

    .form-check-label-custom {
        cursor: pointer;
        font-size: 0.9rem;
        color: #212529;
        user-select: none;
        flex: 1;
    }
</style>
