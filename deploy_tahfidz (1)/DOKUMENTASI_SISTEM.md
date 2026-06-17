# Dokumentasi Sistem: Sistem Informasi Tahfidz KB/BA/TPA "Restu 2"

Dokumen ini menjelaskan struktur, alur kerja, dan basis data Sistem Informasi Tahfidz KB/BA/TPA "Restu 2" dengan bahasa yang sederhana agar mudah dipahami oleh pengembang maupun pengelola teknis.

---

## 1. Apa itu Sistem Informasi Tahfidz Restu 2?

Aplikasi ini dibuat khusus untuk mempermudah proses **penyetoran, penilaian, dan pemantauan hafalan Al-Quran** secara online untuk siswa KB/BA/TPA Restu 2. 

Dengan aplikasi ini:
* **Guru** tidak perlu lagi mendengarkan hafalan secara langsung satu per satu di kelas. Guru cukup membuat tugas hafalan harian dan menilai rekaman audio yang dikirimkan.
* **Orang Tua (Wali Murid)** bisa merekam hafalan anak di rumah, mendengarkan panduan suara dari guru, mengirimkannya melalui aplikasi, dan melihat hasil penilaian secara instan.
* **Koordinator (Admin)** dapat mengawasi seluruh aktivitas kelas, membuat pengumuman, serta mengelola data guru dan siswa.

---

## 2. Alur Kerja Utama Aplikasi

Proses utama di dalam sistem ini berjalan sebagai berikut:

```
[Guru] membuat tugas & upload panduan audio 
                      │
                      ▼
[Orang Tua] melihat tugas, mendengar panduan & upload audio hafalan anak 
                      │
                      ▼
[Guru] menerima notifikasi, memutar audio hafalan, & memberikan nilai/catatan
                      │
        ┌─────────────┴─────────────┐
        ▼                           ▼
[Status: DITERIMA]          [Status: PERBAIKI]
(Tugas selesai,             (Orang tua mengirim
 poin bertambah)             ulang audio perbaikan)
```

---

## 3. Struktur Folder dan File

Kode aplikasi ini disusun menggunakan pola yang rapi (mirip MVC - *Model View Controller*):

* **[index.php](file:///d:/C/laragon/www/deploy_tahfidz/index.php)**: Pintu masuk utama aplikasi. File ini akan mengecek apakah pengguna sudah login, lalu mengarahkan mereka ke halaman dasbor yang sesuai dengan peran/role mereka.
* **[setup.php](file:///d:/C/laragon/www/deploy_tahfidz/setup.php)**: Script otomatis untuk membuat database dan mengimpor tabel-tabel awal saat pertama kali aplikasi dipasang.
* **[config/](file:///d:/C/laragon/www/deploy_tahfidz/config)**: Berisi pengaturan dasar aplikasi, seperti koneksi database ([database.php](file:///d:/C/laragon/www/deploy_tahfidz/config/database.php)) dan pembatasan hak akses ([auth.php](file:///d:/C/laragon/www/deploy_tahfidz/config/auth.php)).
* **[controllers/](file:///d:/C/laragon/www/deploy_tahfidz/controllers)**: Berisi logika utama bisnis program untuk memproses permintaan pengguna:
  * [AdminController.php](file:///d:/C/laragon/www/deploy_tahfidz/controllers/AdminController.php): Mengatur manajemen guru, siswa, kelas, dan pengumuman.
  * [GuruController.php](file:///d:/C/laragon/www/deploy_tahfidz/controllers/GuruController.php): Mengatur tugas harian, penilaian/koreksi setoran, dan perpanjangan waktu.
  * [WaliController.php](file:///d:/C/laragon/www/deploy_tahfidz/controllers/WaliController.php): Mengatur pengiriman audio hafalan, melihat detail nilai, dan melihat raport anak.
* **[models/](file:///d:/C/laragon/www/deploy_tahfidz/models)**: Berisi perintah langsung ke database untuk mengambil, menambah, atau mengubah data. Contoh: [SiswaModel.php](file:///d:/C/laragon/www/deploy_tahfidz/models/SiswaModel.php), [SetoranModel.php](file:///d:/C/laragon/www/deploy_tahfidz/models/SetoranModel.php), dll.
* **[api/](file:///d:/C/laragon/www/deploy_tahfidz/api)**: Jembatan yang menghubungkan halaman tampilan (HTML) dengan pengolah logika (PHP) melalui format data JSON.
* **[views/](file:///d:/C/laragon/www/deploy_tahfidz/views)**: Halaman tampilan web yang dilihat oleh pengguna di browser:
  * `views/admin/`: Menu-menu khusus Admin (Dashboard, Manajemen Pengguna, Monitor Kelas, dll).
  * `views/guru/`: Menu-menu khusus Guru (Beranda kelas, Koreksi, Target Hafalan, dll).
  * `views/wali_murid/`: Menu-menu khusus Orang Tua (Setoran, Histori Hafalan, Raport, dll).
  * `views/shared/`: Halaman bersama seperti Login ([login.html](file:///d:/C/laragon/www/deploy_tahfidz/views/shared/login.html)).
* **[assets/](file:///d:/C/laragon/www/deploy_tahfidz/assets)**: Berisi file pendukung seperti Javascript ([api.js](file:///d:/C/laragon/www/deploy_tahfidz/assets/js/api.js) untuk memproses fetch API) dan ikon CSS.
* **[uploads/](file:///d:/C/laragon/www/deploy_tahfidz/uploads)**: Folder tempat menyimpan file audio rekaman hafalan siswa dan audio panduan guru.

---

## 4. Penjelasan Tabel Database

Database aplikasi ini diberi nama `tahfidz_restu2` dengan skema yang terdefinisi di [database/schema.sql](file:///d:/C/laragon/www/deploy_tahfidz/database/schema.sql). Berikut adalah penjelasan 10 tabel utamanya dalam bahasa sederhana:

1. **`users` (Akun Pengguna)**
   * **Fungsi**: Menyimpan nomor identitas (username) dan password terenkripsi untuk masuk ke aplikasi.
   * **Role (Peran)**: `admin` (Koordinator), `guru` (Wali Kelas), dan `wali_murid` (Orang Tua).

2. **`guru` (Profil Guru)**
   * **Fungsi**: Menyimpan biodata lengkap guru (seperti NUPTK, nama, jabatan, nomor telepon, alamat, dll).
   * **Relasi**: Setiap guru terhubung dengan 1 akun di tabel `users`.

3. **`kelas` (Daftar Kelas)**
   * **Fungsi**: Menyimpan daftar nama kelas aktif (KB Melati, TKA 1, TKB 1, dll).
   * **Relasi**: Setiap kelas dipimpin oleh 1 guru wali kelas.

4. **`siswa` (Profil Siswa)**
   * **Fungsi**: Menyimpan data diri murid (NIS, nama lengkap, nama panggilan, jenis kelamin, kelas).
   * **Relasi**: Setiap siswa dihubungkan ke 1 akun wali murid di tabel `users`.

5. **`tugas_hafalan` (Target Hafalan Harian)**
   * **Fungsi**: Menyimpan daftar tugas hafalan yang diterbitkan guru (nama surah, rentang ayat, tanggal, batas waktu, dan file audio panduan guru).

6. **`setoran` (Audio Hafalan Murid - Tabel Inti)**
   * **Fungsi**: Menyimpan kiriman rekaman suara hafalan murid beserta status penilaiannya (`menunggu` koreksi, `diterima`, atau harus `perbaiki`). Juga menyimpan catatan koreksi guru dan balasan pesan orang tua.

7. **`perpanjangan_waktu` (Izin Telat)**
   * **Fungsi**: Mencatat jika guru membukakan kunci batas waktu (deadline) kepada murid tertentu agar murid tersebut bisa mengirimkan hafalan meskipun sudah melewati tanggal tugas.

8. **`pengumuman` (Pengumuman Koordinator)**
   * **Fungsi**: Menyimpan pesan/pengumuman penting dari Admin yang dikirimkan langsung ke wali murid kelas tertentu atau semua kelas sekaligus.

9. **`notifikasi` (Pemberitahuan Real-Time)**
   * **Fungsi**: Menyimpan riwayat notifikasi (seperti "Tugas Baru Diterbitkan", "Setoran Diterima", atau "Ada Pengumuman").

10. **`koreksi_detail` (Rincian Nilai)**
    * **Fungsi**: Menyimpan rincian nilai setoran berdasarkan kriteria seperti Kelancaran, Tajwid, dan Makhraj.

---

## 5. Cara Memasang Aplikasi Pertama Kali (Instalasi)

Bagi pengembang atau staf IT, ikuti langkah mudah berikut untuk menjalankan sistem:

1. Salin seluruh folder project ini ke server web lokal Anda (contoh: folder `www` di Laragon atau `htdocs` di XAMPP).
2. Pastikan layanan MySQL/MariaDB Anda aktif.
3. Jalankan file setup untuk mengotomatisasi pembuatan database dan tabel:
   * **Lewat Browser**: Akses alamat `http://localhost/deploy_tahfidz/setup.php`
   * **Lewat Terminal/CMD**: Jalankan perintah `php setup.php` di dalam folder project.
4. Sistem otomatis membuat database bernama `tahfidz_restu2` dan menyuntikkan data awal.
5. Anda dapat langsung masuk (login) ke aplikasi menggunakan akun Admin default berikut:
   * **Nomor Identitas**: `000001`
   * **Password**: `admin123`
   * *(Sangat disarankan langsung mengganti password setelah masuk pertama kali demi keamanan)*.
