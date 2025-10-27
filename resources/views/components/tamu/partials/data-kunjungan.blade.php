<x-form.textarea-field
    name="keperluan"
    label="Keperluan Kunjungan"
    placeholder="Jelaskan keperluan kunjungan"
    required="true"
    rows="3" />

<x-form.input-field
    name="waktu_keluar"
    label="Jam Selesai (Estimasi)"
    type="time"
    required="true" />

<x-form.select-field
    name="transportasi"
    label="Jenis Kendaraan/Transportasi"
    required="true"
    :options="[
            'Mobil' => 'Mobil',
            'Motor' => 'Motor', 
            'Bus' => 'Bus',
            'Travel' => 'Travel',
            'Online Ride' => 'Online Ride',
            'Jalan Kaki' => 'Jalan Kaki',
            'Lainnya' => 'Lainnya'
        ]" />