<?php
/**
 * POST /api/admin/import-excel.php
 * Upload file Excel (.xlsx) untuk import data Guru / Siswa
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Cek login admin
Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Validasi CSRF token jika dikirim
if (isset($_POST['csrf_token'])) {
    if (!Auth::validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
}

$type = $_GET['type'] ?? $_POST['type'] ?? '';
if (!in_array($type, ['guru', 'siswa'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tipe import (guru/siswa) tidak valid.']);
    exit;
}

if (empty($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'File Excel wajib diunggah.']);
    exit;
}

$tmpPath = $_FILES['excel_file']['tmp_name'];

// Muat PhpSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load($tmpPath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    $db = getDB();

    $imported = 0;
    $skipped = 0;

    $db->beginTransaction();

    if ($type === 'guru') {
        // Logika import guru
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(trim($row[1] ?? ''))) continue;

            $nama_lengkap = trim($row[1] ?? '');
            $status_guru  = trim($row[2] ?? '');
            $nuptk_raw    = trim($row[3] ?? '');
            $ttl_raw      = trim($row[4] ?? '');
            $pendidikan   = trim($row[5] ?? '');
            $jabatan      = trim($row[7] ?? '');
            $alamat       = trim($row[10] ?? '');
            $no_telepon   = trim($row[11] ?? '');

            if (empty($nama_lengkap)) {
                $skipped++;
                continue;
            }

            $tempat_lahir = null;
            $tanggal_lahir = null;
            if (!empty($ttl_raw)) {
                $parts = explode(',', $ttl_raw);
                if (count($parts) >= 2) {
                    $tempat_lahir = trim($parts[0]);
                    $tgl_str = trim(end($parts));
                    $tgl_parsed = date('Y-m-d', strtotime($tgl_str));
                    if ($tgl_parsed && $tgl_parsed !== '1970-01-01') {
                        $tanggal_lahir = $tgl_parsed;
                    }
                } else {
                    $tempat_lahir = $ttl_raw;
                }
            }

            $nomor_identitas = $nuptk_raw;
            if (empty($nomor_identitas) || $nomor_identitas === '-' || strtolower($nomor_identitas) === 'nan') {
                $nomor_identitas = 'GUR' . str_pad(mt_rand(1000, 9999) . $i, 6, '0', STR_PAD_LEFT);
            }

            $role = (strtolower($status_guru) === 'karyawan') ? 'karyawan' : 'guru';
            $password_hash = password_hash('12345678', PASSWORD_BCRYPT, ['cost' => 12]);

            // Cek duplikat
            $cek = $db->prepare("SELECT id FROM users WHERE nomor_identitas = :nomor LIMIT 1");
            $cek->execute([':nomor' => $nomor_identitas]);
            if ($cek->fetch()) {
                $skipped++;
                continue;
            }

            // Insert users
            $stmt_user = $db->prepare("
                INSERT INTO users (nomor_identitas, password_hash, role, is_active)
                VALUES (:nomor, :hash, :role, 1)
            ");
            $stmt_user->execute([
                ':nomor' => $nomor_identitas,
                ':hash'  => $password_hash,
                ':role'  => $role
            ]);
            $user_id = $db->lastInsertId();

            // Insert guru
            $stmt_guru = $db->prepare("
                INSERT INTO guru (user_id, nuptk, nama_lengkap, jabatan, status, pendidikan,
                                  tempat_lahir, tanggal_lahir, no_telepon, alamat)
                VALUES (:user_id, :nuptk, :nama, :jabatan, :status, :pendidikan,
                        :tempat_lahir, :tanggal_lahir, :no_telepon, :alamat)
            ");
            $stmt_guru->execute([
                ':user_id'       => $user_id,
                ':nuptk'         => !empty($nuptk_raw) && $nuptk_raw !== '-' ? $nuptk_raw : null,
                ':nama'          => $nama_lengkap,
                ':jabatan'       => $jabatan,
                ':status'        => $status_guru,
                ':pendidikan'    => !empty($pendidikan) ? $pendidikan : null,
                ':tempat_lahir'  => $tempat_lahir,
                ':tanggal_lahir' => $tanggal_lahir,
                ':no_telepon'    => !empty($no_telepon) ? $no_telepon : null,
                ':alamat'        => !empty($alamat) ? $alamat : null
            ]);

            $imported++;
        }
    } else {
        // Logika import siswa
        $kelas_mapping = [
            'KELOMPOK BERMAIN MELATI'  => 'KB-1',
            'KELOMPOK BERMAIN MAWAR'   => 'KB-2',
            'KELOMPOK BERMAIN TERATAI' => 'KB-3',
            'KELOMPOK BERMAIN SAKURA'  => 'KB-4',
            'BUSTANUL ATHFAL A1'       => 'TKA-1',
            'BUSTANUL ATHFAL A2'       => 'TKA-2',
            'BUSTANUL ATHFAL A3'       => 'TKA-3',
            'BUSTANUL ATHFAL A4'       => 'TKA-4',
            'BUSTANUL ATHFAL B1'       => 'TKB-1',
            'BUSTANUL ATHFAL B2'       => 'TKB-2',
            'BUSTANUL ATHFAL B3'       => 'TKB-3',
            'BUSTANUL ATHFAL B4'       => 'TKB-4',
            'TAMAN PENITIPAN ANAK'     => 'TPA',
            'KB MELATI'                => 'KB-1',
            'KB MAWAR'                 => 'KB-2',
            'KB TERATAI'               => 'KB-3',
            'KB SAKURA'                => 'KB-4',
            'TPA'                      => 'TPA',
        ];

        $stmt_kelas = $db->query("SELECT id, kode_kelas FROM kelas WHERE is_active = 1");
        $kelas_db = [];
        while ($k = $stmt_kelas->fetch()) {
            $kelas_db[strtoupper($k['kode_kelas'])] = $k['id'];
        }

        $current_kelas_id = null;

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $col_a = strtoupper(trim($row[0] ?? ''));
            $col_b = strtoupper(trim($row[1] ?? ''));
            
            $found_kelas = false;
            foreach ($kelas_mapping as $pattern => $kode) {
                if (strpos($col_a, $pattern) !== false || strpos($col_b, $pattern) !== false) {
                    $current_kelas_id = $kelas_db[strtoupper($kode)] ?? null;
                    $found_kelas = true;
                    break;
                }
            }
            if ($found_kelas) continue;

            if (empty(trim($row[1] ?? '')) || strtolower(trim($row[1])) === 'no induk') {
                continue;
            }

            if (!$current_kelas_id) continue;

            $nis            = trim($row[1] ?? '');
            $nama_lengkap   = trim($row[2] ?? '');
            $nama_panggilan = trim($row[3] ?? '');
            $jenis_kelamin  = strtoupper(trim($row[4] ?? ''));

            if (empty($nis) || empty($nama_lengkap)) {
                $skipped++;
                continue;
            }

            if (!in_array($jenis_kelamin, ['L', 'P'])) {
                $jenis_kelamin = 'L';
            }

            $password_hash = password_hash($nis, PASSWORD_BCRYPT, ['cost' => 12]);

            $cek = $db->prepare("SELECT id FROM users WHERE nomor_identitas = :nomor LIMIT 1");
            $cek->execute([':nomor' => $nis]);
            if ($cek->fetch()) {
                $skipped++;
                continue;
            }

            // Insert users
            $stmt_user = $db->prepare("
                INSERT INTO users (nomor_identitas, password_hash, role, is_active)
                VALUES (:nomor, :hash, 'wali_murid', 1)
            ");
            $stmt_user->execute([
                ':nomor' => $nis,
                ':hash'  => $password_hash
            ]);
            $user_id = $db->lastInsertId();

            // Insert siswa
            $stmt_siswa = $db->prepare("
                INSERT INTO siswa (user_id, nis, nama_lengkap, nama_panggilan, jenis_kelamin, kelas_id, is_active)
                VALUES (:user_id, :nis, :nama, :panggilan, :jk, :kelas_id, 1)
            ");
            $stmt_siswa->execute([
                ':user_id'   => $user_id,
                ':nis'       => $nis,
                ':nama'      => $nama_lengkap,
                ':panggilan' => !empty($nama_panggilan) ? $nama_panggilan : null,
                ':jk'        => $jenis_kelamin,
                ':kelas_id'  => $current_kelas_id
            ]);

            $imported++;
        }
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Import Excel berhasil.',
        'data' => [
            'imported' => $imported,
            'skipped' => $skipped
        ]
    ]);
} catch (\Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
