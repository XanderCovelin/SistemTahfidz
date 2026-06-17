# Panduan Penggunaan Aplikasi: Sistem Informasi Tahfidz KB/BA/TPA "Restu 2"

Dokumen ini berisi panduan cara menggunakan aplikasi untuk 3 jenis pengguna: **Koordinator (Admin)**, **Guru (Wali Kelas)**, dan **Orang Tua (Wali Murid)** dengan bahasa yang sederhana dan mudah diikuti.

---

## 1. Panduan untuk Koordinator (Admin)

Sebagai Admin/Koordinator, tugas Anda adalah mengelola data pengguna (guru dan siswa), memonitor setoran secara keseluruhan, dan mempublikasikan pengumuman.

### A. Cara Masuk ke Aplikasi (Login)
1. Buka browser Anda dan akses alamat website aplikasi Tahfidz.
2. Masukkan **Nomor Identitas** Anda (untuk admin default gunakan: `000001`).
3. Masukkan **Password** Anda (untuk admin default gunakan: `admin123`).
4. Klik tombol **Masuk Aplikasi**.

### B. Membaca Statistik Kelas (Dashboard)
1. Setelah login, Anda akan disajikan halaman **Dashboard** utama.
2. Anda bisa melihat total siswa aktif, persentase setoran hari ini secara keseluruhan, jumlah guru yang hadir/aktif, serta jumlah keterlambatan kemarin.
3. Di bagian bawah terdapat tabel status per kelas (menampilkan berapa siswa yang sudah mengumpulkan hafalan, belum mengumpulkan, disetujui, atau perlu perbaikan).

### C. Mengelola Data Guru dan Siswa (Manajemen Pengguna)
1. Masuk ke menu **Manajemen Pengguna** di sidebar kiri.
2. Terdapat dua tab utama: **Daftar Guru** dan **Daftar Siswa**.
3. **Menambah Data secara Manual**:
   * Klik tombol **Tambah Guru** atau **Tambah Siswa**.
   * Isi form data diri dengan lengkap (Nama, NUPTK/NIS, Jabatan, Kelas, No. Telepon, dll).
   * Masukkan password untuk akun tersebut (untuk siswa, password default otomatis menggunakan NIS-nya).
   * Klik tombol **Simpan**.
4. **Mengimpor Data secara Massal (Import Excel)**:
   * Jika ingin memasukkan banyak data sekaligus, klik tombol **Import Excel**.
   * Unduh template file Excel yang sudah disediakan.
   * Isi data guru atau siswa ke dalam file Excel tersebut sesuai format kolom yang ada.
   * Pilih tipe data yang akan diunggah (Guru/Siswa), pilih file Excel dari komputer Anda, lalu klik **Unggah**.
5. **Mengubah / Menonaktifkan Pengguna**:
   * Klik tombol **Edit** (ikon pensil) di samping nama pengguna untuk memperbarui data diri, mengganti password, atau memindahkan kelas wali kelas.
   * Klik tombol **Nonaktifkan** (ikon tempat sampah) jika guru atau siswa tersebut sudah tidak aktif lagi di sekolah.

### D. Memantau Aktivitas Hafalan Kelas (Monitor Kelas)
1. Buka menu **Monitor Kelas**.
2. Pilih nama kelas yang ingin dipantau dan pilih tanggal tugas hafalan.
3. Sistem akan menampilkan daftar siswa di kelas tersebut beserta status tugasnya hari itu (Belum Kumpul, Menunggu Koreksi, Diterima, atau Perbaiki).
4. Anda juga bisa mendengarkan audio rekaman murid secara langsung di menu ini.

### E. Membuat Pengumuman Global
1. Buka menu **Komando** (atau Kirim Pengumuman).
2. Isi **Judul Pengumuman** dan **Isi Pengumuman** (misal: pengumuman libur bersama atau imbauan setoran hafalan).
3. Pilih kelas mana saja yang menjadi target pengumuman ini (Anda bisa memilih beberapa kelas tertentu atau memilih opsi "Semua Kelas").
4. Klik **Kirim Pengumuman**. Notifikasi akan langsung dikirimkan ke akun Orang Tua di kelas terpilih.

---

## 2. Panduan untuk Guru (Wali Kelas)

Sebagai Guru Wali Kelas, peran utama Anda adalah mempublikasikan tugas target hafalan harian dan menilai setoran rekaman suara murid.

### A. Cara Masuk ke Aplikasi (Login)
1. Masukkan **Nomor Identitas** Anda (berupa NUPTK, NIK, atau kode guru unik yang diberikan Admin).
2. Masukkan **Password** Anda (default awal biasanya `12345678`, silakan ganti setelah login).
3. Klik **Masuk Aplikasi**.

### B. Dasbor Kelas Anda
1. Pada menu **Beranda**, Anda akan melihat ringkasan status kelas Anda **hari ini**:
   * Jumlah siswa yang sudah mengumpulkan tugas.
   * Jumlah setoran yang sedang mengantre untuk dinilai.
   * Jumlah siswa yang hafalannya perlu diperbaiki.
   * Jumlah siswa yang belum mengumpulkan hafalan sama sekali.

### C. Membuat Tugas / Target Hafalan Baru
1. Buka menu **Target Hafalan** di sidebar kiri.
2. Isi formulir pembuatan tugas:
   * **Nama Surat**: Pilih atau tulis nama surah Al-Quran (misal: *An-Naba*).
   * **Ayat Dari** dan **Ayat Sampai**: Tentukan rentang ayatnya (misal: ayat *1* sampai *5*).
   * **Tanggal Tugas**: Pilih tanggal berlakunya tugas ini.
   * **Batas Waktu (Deadline)**: Jam batas akhir pengumpulan audio (default otomatis diset pukul 23:59).
   * **Audio Panduan**: Unggah rekaman suara Anda sendiri yang membacakan ayat target tersebut dengan tajwid yang benar. Ini berfungsi sebagai contoh bagi siswa.
   * **Status**: Pilih **Published** agar langsung dapat dilihat orang tua murid, atau **Draft** jika masih ingin disimpan sementara.
3. Klik **Simpan Target**. Orang tua murid di kelas Anda akan menerima notifikasi tugas baru.

### D. Mengoreksi & Menilai Hafalan Murid
1. Masuk ke menu **Koreksi Setoran**.
2. Anda akan melihat daftar murid yang status setoran audionya **Menunggu**.
3. Klik nama siswa untuk membuka lembar koreksi:
   * Klik tombol **Putar Audio** untuk mendengarkan rekaman suara murid.
   * Berikan penilaian angka (0-100) pada 3 kriteria utama:
     1. **Kelancaran** (seberapa lancar bacaan anak tanpa terbata-bata).
     2. **Tajwid** (kebenaran hukum bacaan seperti ikhfa, idgham, dll).
     3. **Makhraj** (kebenaran pelafalan huruf hijaiyah).
   * Tulis komentar atau catatan evaluasi di kolom **Catatan Guru** (misal: "Alhamdulillah sudah lancar, perhatikan panjang pendek di ayat 3").
   * Tentukan **Keputusan**:
     * Pilih **Diterima** jika hafalan dirasa sudah cukup baik untuk jenjangnya.
     * Pilih **Perbaiki** jika terdapat banyak kesalahan fatal dan anak perlu mengirimkan rekaman ulang. *(Catatan wajib diisi jika memilih opsi Perbaiki)*.
4. Klik **Kirim Koreksi**. Nilai dan pemberitahuan akan langsung dikirim ke aplikasi orang tua.

### E. Membuka Kunci Batas Waktu (Perpanjangan Waktu)
1. Jika ada siswa yang terlambat menyetor dan tombol unggahnya sudah terkunci, Anda dapat membukakan akses khusus untuk siswa tersebut.
2. Pada menu dasbor atau progres siswa, klik tombol **Perpanjang Waktu** (Unlock).
3. Pilih nama siswa, tentukan **Batas Waktu Baru** (tanggal & jam), dan tuliskan alasan singkat.
4. Klik **Simpan**. Akun siswa tersebut akan terbuka kembali untuk mengunggah hafalan.

### F. Melihat Laporan Bulanan Kelas
1. Klik menu **Progres Hafalan**.
2. Pilih **Bulan** dan **Tahun** yang diinginkan.
3. Anda akan melihat ringkasan performa setiap siswa dalam satu bulan: total setoran yang dikirim, berapa yang diterima, berapa yang ditolak/harus diperbaiki, dan persentase kelulusan hafalannya.

---

## 3. Panduan untuk Orang Tua (Wali Murid)

Sebagai Orang Tua/Wali Murid, Anda bertugas memandu anak menghafal di rumah, merekam suaranya, mengirimkannya ke aplikasi, serta melihat masukan dari guru.

### A. Cara Masuk ke Aplikasi (Login)
1. Masukkan **Nomor Identitas** (menggunakan **NIS - Nomor Induk Siswa** anak Anda).
2. Masukkan **Password** Anda (secara default di awal, password Anda juga disamakan dengan **NIS** anak Anda. Segera ubah setelah berhasil masuk demi keamanan).
3. Klik **Masuk Aplikasi**.

### B. Memeriksa Tugas Hari Ini
1. Pada halaman **Beranda**, Anda akan melihat nama anak Anda, kelasnya, total poin hafalan yang terkumpul, dan persentase kehadiran setoran.
2. Di bagian **Tugas Hafalan Hari Ini**, Anda akan melihat nama surah dan ayat yang harus dihafalkan hari ini beserta batas waktu pengumpulannya (deadline).
3. Jika guru mengunggah panduan bacaan, Anda bisa menekan tombol **Putar Audio Panduan** untuk mendengarkan contoh bacaan dari guru.

### C. Merekam & Mengirim Hafalan Anak
1. Setelah anak siap melafalkan hafalan, rekam suara anak menggunakan perekam suara bawaan HP/komputer Anda (simpan dalam format MP3, WAV, M4A, OGG, atau WebM dengan ukuran maksimal 10MB).
2. Di halaman beranda bagian tugas hari ini, klik tombol **Kirim Setoran** (atau pilih menu **Setoran**).
3. Klik area unggah file, lalu pilih file rekaman suara anak yang sudah disimpan.
4. Klik **Kirim Rekaman**. Status tugas anak Anda akan berubah menjadi **Menunggu** (menunggu dinilai oleh wali kelas).

### D. Melihat Hasil Penilaian dan Catatan Guru
1. Jika guru selesai menilai, Anda akan mendapat pemberitahuan notifikasi.
2. Masuk ke menu **Histori** atau **Raport**.
3. Di situ, Anda dapat melihat status hafalan hari itu:
   * **Diterima** (berwarna hijau): Hafalan anak berhasil disetujui. Poin anak Anda akan bertambah.
   * **Perbaiki** (berwarna merah): Terdapat bagian hafalan yang salah dan perlu diulang.
4. Klik tombol **Detail Koreksi** untuk melihat rincian nilai angka (Kelancaran, Tajwid, Makhraj) serta catatan koreksi dari guru.

### E. Membalas Catatan Guru
1. Apabila Anda ingin menanggapi catatan dari guru (misal mengucapkan terima kasih atau memberi penjelasan), Anda bisa mengisi kolom **Balasan Wali Murid** di halaman detail koreksi tersebut.
2. Tulis pesan Anda, lalu klik **Kirim Balasan**. Pesan Anda akan langsung masuk ke dasbor notifikasi guru wali kelas.

### F. Memantau Raport Bulanan Anak
1. Klik menu **Raport** di sidebar kiri.
2. Pilih bulan dan tahun pelajaran.
3. Anda dapat melihat perkembangan hafalan anak Anda secara keseluruhan dalam satu bulan, termasuk persentase keberhasilan setoran dan daftar riwayat hafalan lengkap dengan catatan guru dari hari ke hari.
