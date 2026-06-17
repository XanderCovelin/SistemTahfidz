<?php
/**
 * GET /api/admin/download-template.php
 * Download file template Excel (.xlsx) untuk import data Guru / Siswa
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Cek login admin
Auth::requireRole('admin');

$type = $_GET['type'] ?? '';
if (!in_array($type, ['guru', 'siswa'])) {
    http_response_code(400);
    echo "Tipe template tidak valid. Gunakan type=guru atau type=siswa.";
    exit;
}

// Muat PhpSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

if ($type === 'guru') {
    $sheet->setTitle('Template Import Guru');
    
    // Headers
    $headers = [
        'No', 
        'Nama Lengkap', 
        'Status', 
        'NUPTK / NIK', 
        'Tempat, Tanggal Lahir', 
        'Pendidikan', 
        'Golongan', 
        'Jabatan', 
        'TMT', 
        'Sertifikasi', 
        'Alamat', 
        'No Telepon'
    ];
    
    foreach ($headers as $colIdx => $header) {
        $sheet->setCellValueByColumnAndRow($colIdx + 1, 1, $header);
    }
    
    // Sample Data
    $sampleData = [
        [
            1, 
            'Ahmad Fauzi, S.Pd', 
            'Guru', 
            '1234567890123456', 
            'Malang, 12 Desember 1990', 
            'S1', 
            '-', 
            'Wali Kelas TKA 1', 
            '-', 
            '-', 
            'Jl. Mawar No. 12, Malang', 
            '081234567890'
        ]
    ];
    
    foreach ($sampleData as $rowIdx => $rowData) {
        foreach ($rowData as $colIdx => $val) {
            $sheet->setCellValueByColumnAndRow($colIdx + 1, $rowIdx + 2, $val);
        }
    }
} else {
    $sheet->setTitle('Template Import Siswa');
    
    // Tulis kelompok/kelas di baris 1
    $sheet->setCellValue('A1', 'BUSTANUL ATHFAL A1');
    
    // Headers di baris 2
    $headers = [
        'No', 
        'No Induk', 
        'Nama Lengkap', 
        'Nama Panggilan', 
        'L/P'
    ];
    
    foreach ($headers as $colIdx => $header) {
        $sheet->setCellValueByColumnAndRow($colIdx + 1, 2, $header);
    }
    
    // Sample Data di baris 3 ke bawah
    $sampleData = [
        [1, '10214', 'Abdullah Faqih', 'Faqih', 'L'],
        [2, '10215', 'Siti Aminah', 'Aminah', 'P']
    ];
    
    foreach ($sampleData as $rowIdx => $rowData) {
        foreach ($rowData as $colIdx => $val) {
            $sheet->setCellValueByColumnAndRow($colIdx + 1, $rowIdx + 3, $val);
        }
    }
}

// Auto-size kolom agar rapi
foreach ($sheet->getColumnIterator() as $column) {
    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}

// Set header response xlsx
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template_import_' . $type . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
