<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header
        :title="__('visitor.prospective_student_data')"
        icon="🎓" />
    <x-form.input-field
        name="asal_sekolah"
        :label="__('visitor.school_origin')"
        :placeholder="__('visitor.school_origin_placeholder')"
        required="true" />
    <x-form.select-field
        name="prodi_diminati"
        :label="__('visitor.interested_program')"
        required="true"
        :options="[
            __('visitor.computer_engineering') => __('visitor.computer_engineering'),
            __('visitor.information_systems') => __('visitor.information_systems'),
            __('visitor.computer_engineering_technology') => __('visitor.computer_engineering_technology'),
            __('visitor.mechanical_engineering') => __('visitor.mechanical_engineering'),
            __('visitor.electronics_system_engineering') => __('visitor.electronics_system_engineering'),
            __('visitor.mechatronics_engineering') => __('visitor.mechatronics_engineering'),
            __('visitor.electronics_engineering') => __('visitor.electronics_engineering'),
            __('visitor.electrical_engineering') => __('visitor.electrical_engineering'),
            __('visitor.telecommunication_network_engineering') => __('visitor.telecommunication_network_engineering'),
            __('visitor.tax_accounting') => __('visitor.tax_accounting'),
            __('visitor.digital_business') => __('visitor.digital_business'),
            __('visitor.public_relations_digital_communication') => __('visitor.public_relations_digital_communication')
            ]" />
</div>

<div>
    <x-tamu.section-header
        :title="__('visitor.visit_data')"
        icon="🎯" />
    <x-tamu.partials.data-kunjungan />
</div>