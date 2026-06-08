@props(['emailRequired' => true])

@php
    $genderOptions = [
        __('visitor.male', [], 'id') => __('visitor.male'),
        __('visitor.female', [], 'id') => __('visitor.female'),
    ];

    $emailLabel = $emailRequired ? __('visitor.email_address') : __('visitor.email_address_optional');
@endphp

<x-tamu.section-header
    :title="__('visitor.personal_data')"
    icon="👤" />

<x-form.input-field
    name="nama"
    :label="__('visitor.full_name')"
    :placeholder="__('visitor.enter_visitor_name')"
    required="true" />

<x-form.radio-group
    name="jenis_kelamin"
    :label="__('visitor.gender')"
    :required="true"
    :options="$genderOptions" />

<x-form.input-field
    name="nomor_telepon"
    :label="__('visitor.phone_number')"
    :placeholder="__('visitor.enter_phone')"
    required="true"
    type="tel"
    :validationRules='"pattern=\"[0-9]+\" data-pattern-error=\"" . __("visitor.phone_pattern_error") . "\""' />

<x-form.input-field
    name="email" 
    :label="$emailLabel" 
    :placeholder="__('visitor.enter_email')" 
    :required="$emailRequired" 
    type="email" />