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
| **BBT-10 Mengelola Data Kunjungan** | **Membaca:** Admin menavigasi ke halaman daftar kunjungan. | Halaman berhasil dimuat dan daftar kunjungan ditampilkan. |
| | **Filter dan Pencarian:** Admin memfilter atau mencari data kunjungan berdasarkan kriteria tertentu. | Daftar kunjungan yang ditampilkan sesuai dengan kriteria filter atau pencarian yang dimasukkan. |
| | **Menghapus:** Admin memilih entri kunjungan dari daftar dan mengonfirmasi penghapusan. | Menampilkan pesan sukses dan entri tersebut tidak lagi terlihat. |
| **BBT-11 Mengelola Akses Pengguna** | **Memberikan Hak Akses:** Admin mencari pengguna dan memberikan peran baru. | Menampilkan pesan sukses dan peran pengguna tersebut diperbarui. |
| | **Mencabut Hak Akses:** Admin mencari pengguna yang memiliki hak akses khusus, misalnya eksekutif, menghapus perannya, lalu menyimpan perubahan. | Menampilkan pesan sukses dan peran pengguna tersebut diperbarui. |
| **BBT-12 Melihat Log Aktivitas** | **Melihat Semua Log:** Admin masuk ke halaman "Log Aktivitas". | Halaman berhasil dimuat dan daftar log aktivitas ditampilkan. |
| | **Pencarian dan Filter Log:** Admin menggunakan pencarian dan filter tertentu. | Daftar log yang ditampilkan diperbarui dan memuat data yang sesuai dengan pencarian atau kriteria filter. |
| **BBT-13 Memvalidasi Presensi Tamu Non-Event**| **Menyetujui Presensi:** Admin masuk ke halaman "Validasi Kunjungan", meninjau data yang masuk, dan mengklik tombol "Validasi" pada data yang dianggap valid. | Status data pada baris tersebut berhasil diubah menjadi "Telah divalidasi". |
| | **Menolak Presensi:** Admin masuk ke halaman "Validasi Kunjungan", menemukan data yang tidak valid, dan mengklik tombol "Tolak". | Status data pada baris tersebut berhasil diubah menjadi "Ditolak". |
| **BBT-14 Melihat Dashboard BI** | **Mengakses Dashboard:** Eksekutif masuk ke halaman dashboard. | Halaman berhasil dimuat. Dashboard Microsoft Power BI menampilkan visualisasi data. |
| **BBT-15 Monitoring Tamu Aktif** | **Melihat Daftar Tamu:** Eksekutif masuk ke halaman "Monitoring Kunjungan". | Halaman berhasil dimuat. Daftar yang ditampilkan hanya berisi tamu yang sedang berkunjung ke kampus. |
| **BBT-16 Melihat Feedback** | **Melihat Detail:** Admin mengklik salah satu entri feedback untuk melihat detail lengkap. | Halaman detail feedback berhasil dimuat dan menampilkan informasi komentar dan rating secara lengkap. |
| | **Menghapus:** Admin memilih entri feedback dari daftar dan mengonfirmasi penghapusan. | Menampilkan pesan sukses dan entri feedback tersebut tidak lagi terlihat. |
| **BBT-17 Mengelola Kategori Event** | **Membaca & Menambah:** Admin menavigasi ke tab menu event, membuka halaman kategori event, lalu mengisi form tambah kategori baru dengan data valid. | Menampilkan pesan sukses dan kategori baru ditambahkan ke dalam database. |
| | **Mengedit:** Admin memilih kategori event dari daftar, mengubah datanya, dan menyimpan perubahan. | Menampilkan pesan sukses dan data kategori event berhasil diperbarui. |
| | **Menghapus:** Admin memilih kategori event dan mengonfirmasi penghapusan. | Menampilkan pesan sukses, data kategori terhapus dan tidak lagi tersedia pada pilihan form pembuatan event. |
| **BBT-18 Melihat QR Code Presensi Event** | **Melihat QR Code:** Pengguna (Staf/Mahasiswa/Admin) mengklik aksi lihat QR code pada salah satu event yang telah dibuat. | Halaman/modal berhasil dimuat dan menampilkan gambar QR code presensi beserta tautannya dengan benar. |
| **BBT-19 Mengelola Opsi Kunjungan** | **Membaca & Menambah:** Admin menavigasi ke tab menu kunjungan, membuka pengaturan opsi kunjungan (pihak_dituju, pihak_dituju_ortu, prodi), dan menambahkan opsi baru. | Menampilkan pesan sukses, dan opsi kunjungan baru tersebut tersedia/muncul di halaman form_presensi tamu. |
| | **Mengedit:** Admin mengubah teks/nilai pada salah satu opsi kunjungan dan menyimpannya. | Menampilkan pesan sukses, dan perubahan nilai opsi tersebut diperbarui di form_presensi tamu. |
| | **Menghapus:** Admin menghapus salah satu item pada opsi kunjungan. | Menampilkan pesan sukses, item terhapus dan opsi tersebut tidak lagi muncul di halaman form_presensi tamu. |