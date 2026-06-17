<?php
/**
 * Model: Kelas
 * Daftar kelas aktif dan wali kelas
 */

require_once __DIR__ . '/BaseModel.php';

class KelasModel extends BaseModel {

    public function getAll(): array {
        return $this->findAll("
            SELECT k.*, g.nama_lengkap AS nama_guru, g.id AS guru_id,
                   COUNT(s.id) AS jumlah_siswa
            FROM kelas k
            LEFT JOIN guru g ON g.id = k.guru_id
            LEFT JOIN siswa s ON s.kelas_id = k.id AND s.is_active = 1
            WHERE k.is_active = 1
            GROUP BY k.id
            ORDER BY k.jenjang, k.kode_kelas
        ");
    }

    public function getById(int $id): ?array {
        return $this->findOne("
            SELECT k.*, g.nama_lengkap AS nama_guru, g.id AS guru_id,
                   g.user_id AS guru_user_id
            FROM kelas k
            LEFT JOIN guru g ON g.id = k.guru_id
            WHERE k.id = :id
        ", [':id' => $id]);
    }

    public function getSiswaCount(int $kelasId): int {
        return $this->count('siswa', 'kelas_id = :kelas_id AND is_active = 1', [':kelas_id' => $kelasId]);
    }

    public function updateKelas(int $id, array $data): int {
        return parent::update('kelas', $id, $data);
    }
}
