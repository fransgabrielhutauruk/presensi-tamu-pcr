<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'accepted_if' => ':attribute harus diterima bila :other adalah :value.',
    'active_url' => ':attribute bukan URL yang sah.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, dash, dan underscore.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa array.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus antara :min dan :max.',
        'file' => ':attribute harus antara :min dan :max kilobytes.',
        'string' => ':attribute harus antara :min dan :max karakter.',
        'array' => ':attribute harus antara :min dan :max items.',
    ],
    'boolean' => ':attribute harus berupa true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password salah.',
    'date' => ':attribute bukan tanggal yang sah.',
    'date_equals' => ':attribute harus tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak bila :other adalah :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus :digits digit.',
    'digits_between' => ':attribute harus antara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak sah.',
    'distinct' => ':attribute memiliki nilai yang duplikat.',
    'email' => ':attribute harus alamat email yang sah.',
    'ends_with' => ':attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => ':attribute yang dipilih tidak sah.',
    'exists' => ':attribute yang dipilih tidak sah.',
    'file' => ':attribute harus berupa file.',
    'filled' => ':attribute wajib diisi.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'file' => ':attribute harus lebih besar dari :value kilobytes.',
        'string' => ':attribute harus lebih besar dari :value karakter.',
        'array' => ':attribute harus memiliki lebih dari :value items.',
    ],
    'gte' => [
        'numeric' => ':attribute harus lebih besar atau sama dengan :value.',
        'file' => ':attribute harus lebih besar atau sama dengan :value kilobytes.',
        'string' => ':attribute harus lebih besar atau sama dengan :value karakter.',
        'array' => ':attribute harus memiliki :value items atau lebih.',
    ],
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak sah.',
    'in_array' => ':attribute tidak ada di :other.',
    'integer' => ':attribute harus berupa integer.',
    'ip' => ':attribute harus alamat IP yang sah.',
    'ipv4' => ':attribute harus alamat IPv4 yang sah.',
    'ipv6' => ':attribute harus alamat IPv6 yang sah.',
    'json' => ':attribute harus berupa JSON string yang sah.',
    'lt' => [
        'numeric' => ':attribute harus kurang dari :value.',
        'file' => ':attribute harus kurang dari :value kilobytes.',
        'string' => ':attribute harus kurang dari :value karakter.',
        'array' => ':attribute harus memiliki kurang dari :value items.',
    ],
    'lte' => [
        'numeric' => ':attribute harus kurang dari atau sama dengan :value.',
        'file' => ':attribute harus kurang dari atau sama dengan :value kilobytes.',
        'string' => ':attribute harus kurang dari atau sama dengan :value karakter.',
        'array' => ':attribute harus memiliki :value items atau kurang.',
    ],
    'mac_address' => ':attribute harus alamat MAC yang sah.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'file' => ':attribute tidak boleh lebih besar dari :max kilobytes.',
        'string' => ':attribute tidak boleh lebih besar dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max items.',
    ],
    'mimes' => ':attribute harus berupa file bertipe: :values.',
    'mimetypes' => ':attribute harus berupa file bertipe: :values.',
    'min' => [
        'numeric' => ':attribute harus minimal :min.',
        'file' => ':attribute harus minimal :min kilobytes.',
        'string' => ':attribute harus minimal :min karakter.',
        'array' => ':attribute harus memiliki minimal :min items.',
    ],
    'multiple_of' => ':attribute harus kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak sah.',
    'not_regex' => 'Format :attribute tidak sah.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => 'Password salah.',
    'present' => ':attribute harus ada.',
    'prohibited' => ':attribute dilarang.',
    'prohibited_if' => ':attribute dilarang bila :other adalah :value.',
    'prohibited_unless' => ':attribute dilarang kecuali :other ada di :values.',
    'prohibits' => ':attribute melarang :other untuk ada.',
    'regex' => 'Format :attribute tidak sah.',
    'required' => ':attribute wajib diisi.',
    'required_array_keys' => ':attribute harus berisi entri untuk: :values.',
    'required_if' => ':attribute wajib diisi bila :other adalah :value.',
    'required_unless' => ':attribute wajib diisi kecuali :other ada di :values.',
    'required_with' => ':attribute wajib diisi bila :values ada.',
    'required_with_all' => ':attribute wajib diisi bila :values ada.',
    'required_without' => ':attribute wajib diisi bila :values tidak ada.',
    'required_without_all' => ':attribute wajib diisi bila tidak ada :values.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'numeric' => ':attribute harus berukuran :size.',
        'file' => ':attribute harus berukuran :size kilobytes.',
        'string' => ':attribute harus berukuran :size karakter.',
        'array' => ':attribute harus mengandung :size items.',
    ],
    'starts_with' => ':attribute harus dimulai dengan salah satu dari: :values.',
    'string' => ':attribute harus berupa string.',
    'timezone' => ':attribute harus zona waktu yang sah.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diupload.',
    'url' => ':attribute harus URL yang sah.',
    'uuid' => ':attribute harus UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'nama' => 'Nama Lengkap',
        'email' => 'Email',
        'nomor_telepon' => 'Nomor Telepon',
        'jenis_kelamin' => 'Jenis Kelamin',
        'institusi' => 'Institusi',
        'jabatan' => 'Jabatan/Posisi',
        'transportasi' => 'Transportasi',
        'nim_nip' => 'NIM/NIP',
        'keperluan' => 'Keperluan Kunjungan',
        'jumlah_rombongan' => 'Jumlah Rombongan',
        'waktu_keluar' => 'Jam Selesai',
        'pihak_dituju' => 'Pihak yang Dituju',
        'asal_sekolah' => 'Asal Sekolah',
        'prodi_diminati' => 'Program Studi yang Diminati',
        'nama_mahasiswa' => 'Nama Mahasiswa',
        'nim_mahasiswa' => 'NIM Mahasiswa',
        'hubungan_dengan_mahasiswa' => 'Hubungan dengan Mahasiswa',
        'jenis_instansi' => 'Jenis Instansi',
        'kategori_instansi' => 'Kategori Instansi',
        'skala_perusahaan' => 'Skala Perusahaan',
        'prodi_mahasiswa' => 'Program Studi Mahasiswa',
    ],
];