<x-mail::message>
Halo {{ $jenisKelamin == 'Laki-laki' ? 'Pak' : 'Bu' }} {{ $namaTamu }},

Terima kasih telah berkunjung ke **Politeknik Caltex Riau**.

Jika {{ $jenisKelamin == 'Laki-laki' ? 'Bapak' : 'Ibu'}} sudah selesai berkunjung, silakan lakukan checkout atau konfirmasi kepulangan melalui tombol di bawah ini:

<x-mail::button :url="$checkoutUrl" color="success">
Konfirmasi Check-Out
</x-mail::button>

---

**Detail Kunjungan:**
- Nama: {{ $namaTamu }}
- Tanggal Kunjungan: {{ \Carbon\Carbon::parse($kunjungan->created_at)->format('d F Y') }}
- Waktu Kunjungan: {{ \Carbon\Carbon::parse($kunjungan->created_at)->format('H:i') }} WIB

---

Terima kasih.
</x-mail::message>
