# Rencana Skenario Pengujian (Feature Test)
Referensi ini diadaptasi dari daftar kebutuhan Black-Box Testing (Lampiran C). Gunakan daftar ini sebagai acuan utama saat merancang skenario (Happy Path & Edge Cases) menggunakan Pest 3.

| ID & Fungsi | Skenario Pengujian (Act) | Hasil yang Diharapkan (Assert) |
| :--- | :--- | :--- |
| **BBT-1 Login** | **Login Sukses - Satu Role:** Pengguna mengklik tombol "Masuk dengan Google" dan memilih akun Google kampus (@pcr.ac.id) yang hanya memiliki satu role. | Masuk ke dashboard sesuai dengan role-nya. |
| | **Login Sukses - Multi Role:** Pengguna mengklik tombol "Masuk dengan Google" dan memilih akun Google kampus yang memiliki lebih dari satu role. | Pengguna memilih role. Setelah itu, masuk ke dashboard sesuai dengan role yang dipilih. |
| | **Login Gagal - Akun Non-Kampus:** Pengguna mengklik tombol "Masuk dengan Google" dan memilih akun Google pribadi (@gmail.com). | Menampilkan pesan error bahwa akun tidak terdaftar. |
| **BBT-2 Pengisian Presensi Non-Event** | **Isi Lengkap:** Tamu memilih kategori tujuan dan mengisi semua mandatory field dengan data valid. | Masuk ke halaman feedback dan pesan sukses ditampilkan. |
| | **Data Tidak Lengkap:** Tamu mencoba mengirimkan form dengan salah satu mandatory field yang dikosongkan. | Menampilkan pesan error validasi dan form tidak dapat dikirim. |
| **BBT-3 Pengisian Presensi Event** | **Isi Lengkap:** Tamu mengisi semua mandatory field dengan data valid. | Masuk ke halaman feedback dan menampilkan pesan sukses. |
| | **Data Tidak Lengkap:** Tamu mencoba mengirimkan form dengan mengosongkan salah satu mandatory field. | Menampilkan pesan error validasi dan form tidak dapat dikirim. |
| **BBT-4 Konfirmasi Checkout** | **Checkout Normal:** Tamu menerima notifikasi WA, mengklik tautan checkout yang valid, dan menekan tombol konfirmasi. | Menampilkan pesan sukses dan mengarahkan ke halaman feedback. |
| | **Tautan Kedaluwarsa/Ganda:** Tamu mencoba mengakses kembali tautan checkout yang sudah pernah digunakan sebelumnya. | Menampilkan halaman checkout yang sudah dilakukan. |
| **BBT-5 Pengisian Feedback** | **Isi Lengkap:** Tamu memberikan rating dan mengisi komentar. | Masuk ke halaman penutup dan menampilkan pesan sukses. |
| | **Hanya Rating:** Tamu memberikan rating tanpa mengisi komentar. | Masuk ke halaman penutup dan menampilkan pesan sukses. |
| | **Hanya Komentar:** Tamu mengisi komentar, tetapi tidak memberikan rating. | Menampilkan pesan error bahwa rating harus diisi dan form tidak dapat dikirim. |
| **BBT-6 Pembuatan Event Baru** | **Data Valid:** Staf/mahasiswa mengisi semua mandatory field dengan data valid. | Masuk ke halaman detail event yang menampilkan QR Code dan tautan presensi, serta menampilkan pesan sukses. |
| | **Data Invalid:** Staf/mahasiswa mencoba membuat event dengan tanggal di masa lalu atau format waktu yang salah. | Menampilkan pesan error dan form tidak dapat dikirim. |
| | **Data Tidak Lengkap:** Staf/mahasiswa mencoba mengirim form dengan mengosongkan salah satu mandatory field. | Menampilkan pesan error validasi dan form tidak dapat dikirim. |
| **BBT-7 Validasi Presensi Tamu Event** | **Validasi Sukses:** Staf /mahasiswa mengklik tombol aksi "Validasi" pada baris data presensi yang berstatus "Belum divalidasi". | Status data pada baris tersebut berubah menjadi "telah divalidasi". |
| | **Tolak Presensi:** Staf/mahasiswa mengklik tombol aksi "Tolak" pada baris data presensi yang berstatus "Belum divalidasi". | Status data pada baris tersebut berubah menjadi "Ditolak". |
| **BBT-8 Kirim Dokumentasi** | **URL Valid:** Staf/mahasiswa mengisi URL file dokumentasi yang valid (misalnya, tautan Google Drive). | Menampilkan pesan sukses dan kembali ke daftar event. |
| | **URL Tidak Valid:** Staf/mahasiswa mengisi URL yang tidak valid atau memiliki format yang salah. | Menampilkan pesan error validasi bahwa URL tidak valid. |
| **BBT-9 Mengelola Data Event** | **Membaca:** Admin menavigasi ke halaman daftar event. | Halaman berhasil dimuat dan daftar event ditampilkan. |
| | **Filter dan Pencarian:** Admin memfilter atau mencari data event tertentu. | Daftar event yang ditampilkan sesuai dengan kriteria filter atau pencarian yang dimasukkan. |
| | **Mengedit:** Admin memilih event dari daftar, mengubah detailnya, dan menyimpan perubahan. | Menampilkan pesan sukses, kembali ke halaman daftar event, dan data diperbarui. |
| | **Menghapus:** Admin memilih dari daftar dan mengonfirmasi penghapusan. | Menampilkan pesan sukses, halaman daftar event dimuat ulang, dan event tersebut tidak lagi terlihat. |
| **BBT-10 Melihat Status Laporan Event** | **Akses Halaman:** Admin masuk ke halaman pelaporan event. | Halaman berhasil dimuat. Daftar yang ditampilkan hanya berisi event yang status laporannya belum lengkap. |
| **BBT-11 Mengirim Pengingat via Email** | **Pengiriman Sukses:** Admin mengklik tombol "Kirim Pengingat" pada event yang laporannya belum lengkap. | Menampilkan pesan sukses bahwa pengingat berhasil dikirim. |
| **BBT-12 Mengelola Data Kunjungan** | **Membaca:** Admin menavigasi ke halaman daftar kunjungan. | Halaman berhasil dimuat dan daftar kunjungan ditampilkan. |
| | **Filter dan Pencarian:** Admin memfilter atau mencari data kunjungan berdasarkan kriteria tertentu. | Daftar kunjungan yang ditampilkan sesuai dengan kriteria filter atau pencarian yang dimasukkan. |
| | **Mengedit:** Admin memilih entri kunjungan dari daftar, mengubah detailnya, dan menyimpan perubahan. | Menampilkan pesan sukses, kembali ke halaman daftar kunjungan, dan data berhasil diperbarui. |
| | **Menghapus:** Admin memilih entri kunjungan dari daftar dan mengonfirmasi penghapusan. | Menampilkan pesan sukses dan entri tersebut tidak lagi terlihat. |
| **BBT-13 Mengunduh Laporan Kunjungan** | **Unduhan Berhasil:** Admin/eksekutif masuk ke halaman laporan, menerapkan filter, dan mengklik tombol "Unduh Laporan". | File laporan berhasil diunduh ke perangkat pengguna dan sesuai dengan filter yang dipilih. |
| **BBT-14 Mengelola Akses Pengguna** | **Memberikan Hak Akses:** Admin mencari pengguna dan memberikan peran baru. | Menampilkan pesan sukses dan peran pengguna tersebut diperbarui. |
| | **Mencabut Hak Akses:** Admin mencari pengguna yang memiliki hak akses khusus, misalnya eksekutif, menghapus perannya, lalu menyimpan perubahan. | Menampilkan pesan sukses dan peran pengguna tersebut diperbarui. |
| **BBT-15 Melihat Log Aktivitas** | **Melihat Semua Log:** Admin masuk ke halaman "Log Aktivitas". | Halaman berhasil dimuat dan daftar log aktivitas ditampilkan. |
| | **Pencarian dan Filter Log:** Admin menggunakan pencarian dan filter tertentu. | Daftar log yang ditampilkan diperbarui dan memuat data yang sesuai dengan pencarian atau kriteria filter. |
| **BBT-16 Memvalidasi Presensi Tamu Non-Event**| **Menyetujui Presensi:** Admin masuk ke halaman "Validasi Kunjungan", meninjau data yang masuk, dan mengklik tombol "Validasi" pada data yang dianggap valid. | Status data pada baris tersebut berhasil diubah menjadi "Telah divalidasi". |
| | **Menolak Presensi:** Admin masuk ke halaman "Validasi Kunjungan", menemukan data yang tidak valid, dan mengklik tombol "Tolak". | Status data pada baris tersebut berhasil diubah menjadi "Ditolak". |
| **BBT-17 Melihat Dashboard BI** | **Mengakses Dashboard:** Eksekutif masuk ke halaman dashboard. | Halaman berhasil dimuat. Dashboard Microsoft Power BI menampilkan visualisasi data. |
| | **Interaksi Data:** Pengguna berinteraksi dengan visualisasi data (misalnya, mengklik filter tanggal atau mengarahkan kursor ke grafik). | Visualisasi data diperbarui secara dinamis dan menampilkan informasi yang sesuai dengan interaksi pengguna. |
| **BBT-18 Monitoring Tamu Aktif** | **Melihat Daftar Tamu:** Eksekutif masuk ke halaman "Monitoring Tamu Aktif". | Halaman berhasil dimuat. Daftar yang ditampilkan hanya berisi tamu yang sedang berkunjung ke kampus. |
| **BBT-19 Melihat Daftar Kunjungan** | **Melihat Semua Kunjungan:** Eksekutif masuk ke halaman "Daftar Kunjungan". | Halaman berhasil dimuat. Daftar lengkap semua entri presensi yang telah tervalidasi ditampilkan. |
| | **Filter dan Pencarian:** Eksekutif menggunakan filter atau pencarian untuk menemukan kunjungan tertentu. | Daftar kunjungan diperbarui dan memuat data yang sesuai dengan filter atau pencarian yang dilakukan. |
| | **Melihat Detail:** Eksekutif mengklik salah satu entri kunjungan untuk melihat detail lengkap. | Halaman detail kunjungan berhasil dimuat dan menampilkan semua informasi relevan dengan entri tersebut. |
| **BBT-20 Melihat Daftar Event** | **Melihat Semua Event:** Eksekutif masuk ke halaman "Daftar Event". | Halaman berhasil dimuat. Daftar lengkap semua event ditampilkan. |
| | **Filter dan Pencarian:** Eksekutif menggunakan filter atau pencarian untuk menemukan event tertentu. | Daftar event diperbarui dan memuat data yang sesuai dengan filter atau pencarian yang dilakukan. |
| | **Melihat Detail:** Eksekutif mengklik salah satu entri event untuk melihat detail lengkap. | Halaman detail event berhasil dimuat dan menampilkan semua informasi yang relevan dengan entri tersebut. |