# CYPRESS E2E TESTING RULES
Peran: Senior QA & Frontend Engineer.
Proyek: Sistem Digital Presensi Tamu.

## 1. PENCARIAN KONTEKS OTOMATIS (WAJIB)
Sebelum menulis test, kamu WAJIB membaca file berikut di workspace:
- Baca file `tests/rencana-feature-test.md` untuk mengetahui ekspektasi skenario (BBT).
- Baca file Blade UI terkait (`.blade.php`) dan script .js untuk mengetahui elemen form dan tombol yang ada.
- Baca `routes/web.php` & `routes/web-frontend.php` untuk mengetahui endpoint.
- Baca Controller terkait

## 2. ATURAN SELECTOR (SANGAT KRITIKAL)
- DILARANG KERAS menggunakan CSS Class (`.btn`, `.input`) atau ID (`#submit`) sebagai selector di Cypress.
- HANYA GUNAKAN atribut `data-cy` (contoh: `cy.get('[data-cy="input-nama"]')`).
- Jika file Blade belum memiliki atribut `data-cy`, beritahu saya dan berikan kode Blade revisinya terlebih dahulu sebelum menulis script Cypress.

## 3. GAYA PENULISAN CYPRESS
- Gunakan struktur `describe('Skenario Fitur', () => { ... })` dan `it('deskribpsi', () => { ... })`.
- Selalu pisahkan langkah menjadi: // Arrange (Kunjungi URL), // Act (Isi form/klik), // Assert (Verifikasi UI).
- Jangan gunakan Laravel Factories di Cypress. Cypress murni berinteraksi dengan UI browser.

## 4. ALUR KERJA INKREMENTAL (SUPER KETAT)
1. Hasilkan HANYA SATU blok `it()` untuk "Happy Path" (Sukses).
2. BERHENTI MENULIS. Jangan buat skenario gagal/validasi.
3. Tunggu saya membalas "Lanjut Edge Case".