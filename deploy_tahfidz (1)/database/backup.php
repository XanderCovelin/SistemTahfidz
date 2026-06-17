<?php
/**
 * Script Backup Database
 * Jalankan: php database/backup.php
 *
 * Membuat file SQL dump di folder /data/backups/
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "===========================================\n";
echo " Backup Database - Sistem Tahfidz Restu 2\n";
echo "===========================================\n\n";

require_once __DIR__ . '/../config/database.php';

$backupDir = __DIR__ . '/../data/backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'tahfidz_backup_' . date('Y-m-d_His') . '.sql';
$filepath = $backupDir . $filename;

try {
    $db = getDB();

    echo "[1] Mengambil daftar tabel...\n";
    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        $tables[] = reset($row);
    }
    echo "    Ditemukan " . count($tables) . " tabel\n\n";

    $sql = "-- ========================================\n";
    $sql .= "-- Backup Sistem Tahfidz Restu 2\n";
    $sql .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ========================================\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        echo "[2] Backup tabel: {$table}\n";

        // Get CREATE statement
        $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
        $row = $stmt->fetch();
        $sql .= "-- Tabel: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $createKey = isset($row['Create Table']) ? 'Create Table' : 'Create View';
        $sql .= $row[$createKey] . ";\n\n";

        // Get data
        $stmt = $db->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!empty($rows)) {
            $sql .= "INSERT INTO `{$table}` VALUES\n";
            $values = [];
            foreach ($rows as $row) {
                $escaped = array_map(function ($val) use ($db) {
                    if ($val === null) return 'NULL';
                    return $db->quote($val);
                }, $row);
                $values[] = '(' . implode(', ', $escaped) . ')';
            }
            $sql .= implode(",\n", $values) . ";\n\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    echo "\n[3] Menyimpan file backup...\n";
    file_put_contents($filepath, $sql);

    $size = filesize($filepath);
    $sizeFormatted = $size > 1024 * 1024
        ? round($size / (1024 * 1024), 2) . ' MB'
        : round($size / 1024, 2) . ' KB';

    echo "    File: {$filepath}\n";
    echo "    Ukuran: {$sizeFormatted}\n\n";

    // Cleanup old backups (keep last 10)
    echo "[4] Membersihkan backup lama...\n";
    $files = glob($backupDir . 'tahfidz_backup_*.sql');
    usort($files, function ($a, $b) { return filemtime($b) - filemtime($a); });

    if (count($files) > 10) {
        $toDelete = array_slice($files, 10);
        foreach ($toDelete as $file) {
            unlink($file);
            echo "    Hapus: " . basename($file) . "\n";
        }
    }
    echo "    Total backup: " . min(count($files), 10) . " file\n";

    echo "\n===========================================\n";
    echo " Backup Selesai!\n";
    echo "===========================================\n";
    echo " File: {$filepath}\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
