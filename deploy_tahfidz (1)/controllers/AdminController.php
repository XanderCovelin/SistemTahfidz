<?php
/**
 * Controller: Admin
 * Semua aksi yang berkaitan dengan role admin
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/GuruModel.php';
require_once __DIR__ . '/../models/SiswaModel.php';
require_once __DIR__ . '/../models/KelasModel.php';
require_once __DIR__ . '/../models/SetoranModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PengumumanModel.php';
require_once __DIR__ . '/../models/NotifikasiModel.php';

class AdminController {

    private $guruModel;
    private $siswaModel;
    private $kelasModel;
    private $setoranModel;
    private $userModel;
    private $pengumumanModel;
    private $notifikasiModel;

    private $db;

    public function __construct() {
        Auth::requireRole('admin');
        $this->guruModel = new GuruModel();
        $this->siswaModel = new SiswaModel();
        $this->kelasModel = new KelasModel();
        $this->setoranModel = new SetoranModel();
        $this->userModel = new UserModel();
        $this->pengumumanModel = new PengumumanModel();
        $this->notifikasiModel = new NotifikasiModel();
        $this->db = getDB();
    }

    private function beginTransaction(): void { $this->db->beginTransaction(); }
    private function commit(): void { $this->db->commit(); }
    private function rollback(): void { $this->db->rollBack(); }

    /**
     * GET /api/admin/dashboard-stats.php
     * Statistik semua kelas untuk dashboard admin
     */
    public function dashboardStats(): array {
        $stats = $this->setoranModel->getDashboardStatsAll();

        $totalSiswa = 0;
        $totalDiterima = 0;
        $totalSudahKumpul = 0;
        $totalGuruHadir = 0;

        foreach ($stats as &$kelas) {
            $totalSiswa += (int) $kelas['total_siswa'];
            $totalDiterima += (int) $kelas['diterima'];
            $totalSudahKumpul += (int) $kelas['sudah_kumpul'];
            $kelas['persentase'] = (int) $kelas['total_siswa'] > 0
                ? round(($kelas['sudah_kumpul'] / $kelas['total_siswa']) * 100)
                : 0;
        }

        // Hitung guru yang sudah login hari ini
        $totalGuruHadir = $this->guruModel->countAll(); // simplified

        // Hitung keterlambatan kemarin
        $keterlambatan = $this->setoranModel->countKeterlambatanKemarin();

        $persentaseGlobal = $totalSiswa > 0
            ? round(($totalSudahKumpul / $totalSiswa) * 100)
            : 0;

        return [
            'total_siswa' => $totalSiswa,
            'persentase_setoran' => $persentaseGlobal,
            'guru_hadir' => $totalGuruHadir,
            'total_guru' => $this->guruModel->countAll(),
            'keterlambatan' => (int) $keterlambatan,
            'kelas' => $stats
        ];
    }

    /**
     * GET /api/admin/monitor-kelas.php?kelas_id=X&tanggal=Y
     * Data setoran per kelas untuk monitor
     */
    public function monitorKelas(): array {
        $kelasId = (int) ($_GET['kelas_id'] ?? 0);
        $tanggal = $_GET['tanggal'] ?? null;

        if (!$kelasId) {
            throw new \Exception('Parameter kelas_id wajib diisi.');
        }

        $kelas = $this->kelasModel->getById($kelasId);
        if (!$kelas) {
            throw new \Exception('Kelas tidak ditemukan.');
        }

        $data = $this->setoranModel->getMonitorKelas($kelasId, $tanggal);
        $tugas = $this->setoranModel->findOne("
            SELECT * FROM tugas_hafalan
            WHERE kelas_id = :kelas_id AND tanggal_tugas = :tanggal AND status_publikasi = 'published'
            LIMIT 1
        ", [':kelas_id' => $kelasId, ':tanggal' => $tanggal ?: date('Y-m-d')]);

        return [
            'kelas' => $kelas,
            'tugas' => $tugas,
            'siswa' => $data
        ];
    }

    /**
     * GET /api/admin/daftar-guru.php?search=X&page=1&per_page=20
     */
    public function daftarGuru(): array {
        $search = $_GET['search'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(1000, max(1, (int) ($_GET['per_page'] ?? 20)));
        return $this->guruModel->getAll($search, $page, $perPage);
    }

    /**
     * GET /api/admin/daftar-siswa.php?kelas_id=X&search=Y&page=1&per_page=20
     */
    public function daftarSiswa(): array {
        $kelasId = !empty($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : null;
        $search = $_GET['search'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(10000, max(1, (int) ($_GET['per_page'] ?? 20)));
        return $this->siswaModel->getAll($kelasId, $search, $page, $perPage);
    }

    /**
     * POST /api/admin/tambah-guru.php
     */
    public function tambahGuru(array $input): array {
        $nama = trim($input['nama_lengkap'] ?? '');
        $nuptk = trim($input['nuptk'] ?? '');
        $jabatan = trim($input['jabatan'] ?? '');
        $kelasId = $input['kelas_id'] ?? null;
        $noTelp = trim($input['no_telepon'] ?? '');
        $password = $input['password'] ?? '12345678';

        if (empty($nama) || empty($jabatan)) {
            throw new \Exception('Nama lengkap dan jabatan wajib diisi.');
        }

        $nomorIdentitas = $nuptk ?: ('GUR' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));

        $this->beginTransaction();

        try {
            $userId = $this->userModel->create([
                'nomor_identitas' => $nomorIdentitas,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'guru',
                'is_active' => 1
            ]);

            $guruId = $this->guruModel->create([
                'user_id' => $userId,
                'nuptk' => $nuptk ?: null,
                'nama_lengkap' => $nama,
                'jabatan' => $jabatan,
                'status' => $jabatan,
                'no_telepon' => $noTelp ?: null
            ]);

            if ($kelasId) {
                $this->kelasModel->updateKelas((int) $kelasId, ['guru_id' => $guruId]);
            }

            $this->commit();

            return [
                'id' => $guruId,
                'user_id' => $userId,
                'nomor_identitas' => $nomorIdentitas,
                'nama_lengkap' => $nama
            ];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * POST /api/admin/tambah-siswa.php
     */
    public function tambahSiswa(array $input): array {
        $nis = trim($input['nis'] ?? '');
        $nama = trim($input['nama_lengkap'] ?? '');
        $namaPanggilan = trim($input['nama_panggilan'] ?? '');
        $jenisKelamin = $input['jenis_kelamin'] ?? '';
        $kelasId = (int) ($input['kelas_id'] ?? 0);
        $namaWali = trim($input['nama_wali'] ?? '');
        $password = $input['password'] ?: $nis;

        if (empty($nis) || empty($nama) || !$kelasId || empty($jenisKelamin)) {
            throw new \Exception('NIS, nama, jenis kelamin, dan kelas wajib diisi.');
        }

        $this->beginTransaction();

        try {
            $userId = $this->userModel->create([
                'nomor_identitas' => $nis,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'wali_murid',
                'is_active' => 1
            ]);

            $siswaId = $this->siswaModel->create([
                'user_id' => $userId,
                'nis' => $nis,
                'nama_lengkap' => $nama,
                'nama_panggilan' => $namaPanggilan ?: null,
                'jenis_kelamin' => $jenisKelamin,
                'kelas_id' => $kelasId,
                'nama_wali' => $namaWali ?: null,
                'is_active' => 1
            ]);

            $this->commit();

            return [
                'id' => $siswaId,
                'user_id' => $userId,
                'nis' => $nis,
                'nama_lengkap' => $nama
            ];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * PUT /api/admin/edit-guru.php?id=X
     */
    public function editGuru(array $input): array {
        $id = (int) ($_GET['id'] ?? $input['id'] ?? 0);
        if (!$id) throw new \Exception('ID guru wajib diisi.');

        $guru = $this->guruModel->getById($id);
        if (!$guru) {
            throw new \Exception('Guru tidak ditemukan.');
        }

        $userId = (int) $guru['user_id'];

        $this->beginTransaction();

        try {
            // 1. Update profil guru
            $guruData = [];
            if (isset($input['nama_lengkap'])) $guruData['nama_lengkap'] = trim($input['nama_lengkap']);
            if (isset($input['jabatan'])) {
                $guruData['jabatan'] = trim($input['jabatan']);
                $guruData['status'] = trim($input['jabatan']);
            }
            if (isset($input['no_telepon'])) $guruData['no_telepon'] = trim($input['no_telepon']) ?: null;
            if (isset($input['alamat'])) $guruData['alamat'] = trim($input['alamat']) ?: null;
            if (isset($input['pendidikan'])) $guruData['pendidikan'] = trim($input['pendidikan']) ?: null;
            if (isset($input['nuptk'])) $guruData['nuptk'] = trim($input['nuptk']) ?: null;

            if (!empty($guruData)) {
                $this->guruModel->updateGuru($id, $guruData);
            }

            // 2. Update data user (akun login)
            $userData = [];
            if (isset($input['nuptk']) && trim($input['nuptk']) !== '') {
                $userData['nomor_identitas'] = trim($input['nuptk']);
            }
            if (isset($input['is_active'])) {
                $userData['is_active'] = (int) $input['is_active'];
            }
            if (!empty($input['password'])) {
                $userData['password_hash'] = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }

            if (!empty($userData)) {
                $this->userModel->updateUserInfo($userId, $userData);
            }

            // 3. Mutasi kelas
            if (isset($input['kelas_id'])) {
                $newKelasId = (int) $input['kelas_id'];
                
                // Cari kelas lama guru ini
                $currentKelas = $this->guruModel->getKelasByGuruId($id);
                $currentKelasId = $currentKelas ? (int) $currentKelas['id'] : 0;

                if ($currentKelasId !== $newKelasId) {
                    // Kosongkan kelas lama
                    if ($currentKelasId > 0) {
                        $this->kelasModel->updateKelas($currentKelasId, ['guru_id' => null]);
                    }
                    // Isi kelas baru jika newKelasId > 0
                    if ($newKelasId > 0) {
                        // Kosongkan guru lain di kelas baru tersebut agar tidak bentrok
                        $stmt = $this->db->prepare("UPDATE kelas SET guru_id = NULL WHERE id = :id");
                        $stmt->execute([':id' => $newKelasId]);
                        // Set guru baru
                        $this->kelasModel->updateKelas($newKelasId, ['guru_id' => $id]);
                    }
                }
            }

            $this->commit();
            return ['id' => $id, 'updated' => true];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * DELETE /api/admin/hapus-pengguna.php?id=X&type=guru|siswa
     */
    public function hapusPengguna(): array {
        $id = (int) ($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? '';

        if (!$id || !in_array($type, ['guru', 'siswa'])) {
            throw new \Exception('Parameter id dan type wajib diisi.');
        }

        if ($type === 'guru') {
            $guru = $this->guruModel->getById($id);
            if ($guru) $this->userModel->setActive((int) $guru['user_id'], 0);
        } else {
            $siswa = $this->siswaModel->getById($id);
            if ($siswa) {
                $this->userModel->setActive((int) $siswa['user_id'], 0);
                $this->siswaModel->updateSiswa($id, ['is_active' => 0]);
            }
        }

        return ['id' => $id, 'type' => $type, 'deleted' => true];
    }

    /**
     * POST /api/admin/kirim-pengumuman.php
     */
    public function kirimPengumuman(array $input): array {
        $judul = trim($input['judul'] ?? '');
        $isi = trim($input['isi'] ?? '');
        $targetKelas = $input['target_kelas'] ?? [];

        if (empty($judul) || empty($isi)) {
            throw new \Exception('Judul dan isi pengumuman wajib diisi.');
        }

        if (empty($targetKelas)) {
            throw new \Exception('Pilih minimal satu kelas target.');
        }

        $user = Auth::getCurrentUser();

        $pengumumanId = $this->pengumumanModel->create([
            'judul' => $judul,
            'isi' => $isi,
            'pengirim_id' => $user['user_id'],
            'target_kelas' => json_encode($targetKelas)
        ]);

        // Kirim notifikasi ke wali murid di kelas target
        $waliMuridIds = $this->notifikasiModel->getWaliMuridByKelas($targetKelas);
        $notifCount = 0;
        if (!empty($waliMuridIds)) {
            $notifCount = $this->notifikasiModel->bulkCreate(
                $waliMuridIds,
                "Pengumuman: {$judul}",
                $isi
            );
        }

        return [
            'pengumuman_id' => $pengumumanId,
            'notif_terkirim' => $notifCount
        ];
    }

    /**
     * PUT /api/admin/edit-siswa.php?id=X
     */
    public function editSiswa(array $input): array {
        $id = (int) ($_GET['id'] ?? $input['id'] ?? 0);
        if (!$id) throw new \Exception('ID siswa wajib diisi.');

        $siswa = $this->siswaModel->getById($id);
        if (!$siswa) {
            throw new \Exception('Siswa tidak ditemukan.');
        }

        $userId = (int) $siswa['user_id'];

        $this->beginTransaction();

        try {
            // 1. Update profil siswa
            $siswaData = [];
            if (isset($input['nama_lengkap'])) $siswaData['nama_lengkap'] = trim($input['nama_lengkap']);
            if (isset($input['nama_panggilan'])) $siswaData['nama_panggilan'] = trim($input['nama_panggilan']) ?: null;
            if (isset($input['jenis_kelamin'])) $siswaData['jenis_kelamin'] = $input['jenis_kelamin'];
            if (isset($input['nama_wali'])) $siswaData['nama_wali'] = trim($input['nama_wali']) ?: null;
            if (isset($input['kelas_id'])) $siswaData['kelas_id'] = (int) $input['kelas_id'];

            if (!empty($siswaData)) {
                $this->siswaModel->updateSiswa($id, $siswaData);
            }

            // 2. Update data user
            $userData = [];
            if (isset($input['nis']) && trim($input['nis']) !== '') {
                $userData['nomor_identitas'] = trim($input['nis']);
                $this->siswaModel->updateSiswa($id, ['nis' => trim($input['nis'])]);
            }
            if (isset($input['is_active'])) {
                $userData['is_active'] = (int) $input['is_active'];
                $this->siswaModel->updateSiswa($id, ['is_active' => (int) $input['is_active']]);
            }
            if (!empty($input['password'])) {
                $userData['password_hash'] = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }

            if (!empty($userData)) {
                $this->userModel->updateUserInfo($userId, $userData);
            }

            $this->commit();
            return ['id' => $id, 'updated' => true];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * GET /api/admin/riwayat-pengumuman.php
     */
    public function riwayatPengumuman(): array {
        return $this->pengumumanModel->getAll(50);
    }
}
