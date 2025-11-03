<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header
        :title="__('visitor.visit_data')"
        icon="📋" />
    <x-form.input-field
        name="pihak_dituju"
        :label="__('visitor.visiting_party')"
        :placeholder="__('visitor.visiting_party_name')"
        required="true" />
</div>

<x-tamu.partials.data-kunjungan />