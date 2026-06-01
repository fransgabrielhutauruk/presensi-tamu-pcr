# K6 PERFORMANCE TESTING RULES
Peran: Senior QA & Performance Engineer.
Proyek: Sistem Digital Presensi Tamu.

## 1. PENCARIAN KONTEKS OTOMATIS (WAJIB)
Sebelum merancang skrip performa, kamu WAJIB membaca file berikut:
- Baca `tests/rencana-feature-test.md` untuk memahami parameter data (payload) dari skenario BBT yang akan diuji.
- Baca file routing (`web.php` atau `web-frontend.php`) untuk memastikan URL endpoint yang akan ditembak sudah benar.

## 2. ACUAN SKENARIO UJI
Kamu WAJIB menggunakan konfigurasi `stages` berikut saat merancang pengujian:

- **Jika saya meminta "Load Test Non-Event":**
  Gunakan target 100 VUs. Konfigurasi `stages`: Ramp-up ke 100 VUs, tahan beberapa saat, lalu ramp-down.
- **Jika saya meminta "Load Test Event":**
  Gunakan target 500 VUs. Konfigurasi `stages`: Ramp-up agresif ke 500 VUs, tahan, lalu ramp-down.
- **Jika saya meminta "Stress Test Event":**
  Gunakan skenario penambahan lonjakan 50 VUs secara bertahap tanpa ramp-down, terus menanjak (misal: 50, 100, 150, 200, dst.) hingga saya menghentikannya secara manual saat sistem gagal.

## 3. STRUKTUR STANDAR SCRIPT K6
- Gunakan format ES6 Module: `import http from 'k6/http';` dan `import { check, sleep } from 'k6';`.
- Selalu gunakan blok `check()` pada setiap request HTTP untuk memastikan responsnya berhasil (misal: HTTP status 200, 201, atau 302 Redirect).
- Letakkan `sleep(1)` di akhir fungsi untuk menyimulasikan jeda nyata (Think Time).

## 4. PENANGANAN DATA & KEAMANAN LARAVEL (KRITIKAL)
- **Data Dinamis:** Untuk POST request, pastikan data yang dikirimkan acak (tambahkan `Math.random()` pada nama/no_telepon) agar tidak error validasi `unique`.
- **CSRF Token:** Aplikasi ini menggunakan Laravel Web Routes. Skrip K6 kamu WAJIB melakukan `http.get()` ke halaman form terlebih dahulu, mengekstrak nilai CSRF token dari HTML, lalu menyisipkan token tersebut di payload `http.post()`.

## 5. ALUR KERJA INKREMENTAL (SUPER KETAT)
1. Hasilkan skrip HANYA untuk SATU tipe pengujian yang saya minta.
2. BERHENTI MENULIS. Jangan buat file atau skenario tambahan sebelum saya memberikan instruksi lanjutan.