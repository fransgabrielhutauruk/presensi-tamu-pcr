<x-form.section-header
    title="Data Pengunjung"
    icon="👤" />

<x-form.input-field
    name="nama"
    label="Nama Lengkap"
    placeholder="Nama lengkap"
    required="true" />

<x-form.radio-group
    name="jenis_kelamin"
    label="Jenis Kelamin"
    :required="true"
    :options="[
        'Laki-laki' => 'Laki-laki',
        'Perempuan' => 'Perempuan'
    ]" />

<x-form.input-field
    name="nomor_telepon"
    label="Nomor Telepon"
    placeholder="Nomor telepon"
    required="true"
    type="tel"
    validationRules='pattern="[0-9]+" data-pattern-error="Nomor telepon harus berupa angka"' />

<x-form.input-field
    name="email"
    label="Email"
    placeholder="Alamat email"
    required="true"
    type="email" />