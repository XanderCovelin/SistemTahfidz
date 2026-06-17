<?php
/**
 * Script untuk mengonversi dokumen Markdown ke DOCX secara otomatis.
 * Dijalankan melalui akses browser ke: http://localhost/deploy_tahfidz/generate_docx.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class MarkdownToDocx {
    public static function convert($markdownContent) {
        // Normalisasi line endings
        $markdownContent = str_replace("\r\n", "\n", $markdownContent);
        $lines = explode("\n", $markdownContent);
        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . "\n";
        $xml .= '  <w:body>' . "\n";

        foreach ($lines as $line) {
            $line = rtrim($line);
            
            // Baris kosong
            if (empty($line)) {
                $xml .= '    <w:p></w:p>' . "\n";
                continue;
            }

            // Headings (contoh: # Judul)
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = htmlspecialchars($matches[2], ENT_XML1, 'UTF-8');
                
                // Menentukan ukuran huruf (size dalam setengah pt, misal 36 = 18pt)
                $sz = 24; 
                if ($level === 1) $sz = 36;
                elseif ($level === 2) $sz = 28;
                elseif ($level === 3) $sz = 24;

                $xml .= '    <w:p>' . "\n";
                $xml .= '      <w:pPr>';
                $xml .= '        <w:pStyle w:val="Heading' . $level . '"/>';
                $xml .= '        <w:spacing w:before="180" w:after="60"/>';
                $xml .= '      </w:pPr>' . "\n";
                $xml .= '      <w:r>';
                $xml .= '        <w:rPr><w:b/><w:sz w:val="' . $sz . '"/><w:szCs w:val="' . $sz . '"/></w:rPr>';
                $xml .= '        <w:t>' . $text . '</w:t>';
                $xml .= '      </w:r>' . "\n";
                $xml .= '    </w:p>' . "\n";
                continue;
            }

            // Garis pembatas (---)
            if ($line === '---' || $line === '***') {
                $xml .= '    <w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="6" w:space="1" w:color="auto"/></w:pBdr></w:pPr></w:p>' . "\n";
                continue;
            }

            // Item List/Daftar (contoh: * Item atau 1. Item)
            $isList = false;
            $indent = 0;
            if (preg_match('/^(\s*)([*+-]|\d+\.)\s+(.*)$/', $line, $matches)) {
                $isList = true;
                $spaces = strlen($matches[1]);
                $indent = 360 + ($spaces * 60); // Indentasi kiri
                $bullet = ($matches[2] === '*' || $matches[2] === '+' || $matches[2] === '-') ? '• ' : $matches[2] . ' ';
                $text = $bullet . $matches[3];
            } else {
                $text = $line;
            }

            // Parsing teks tebal (**tebal**)
            $text = htmlspecialchars($text, ENT_XML1, 'UTF-8');
            $parts = explode('**', $text);
            $runsXml = '';
            for ($i = 0; $i < count($parts); $i++) {
                if ($parts[$i] === '' && $i > 0 && $i < count($parts) - 1) {
                    continue;
                }
                $isBold = ($i % 2 === 1);
                $runsXml .= '      <w:r>';
                if ($isBold) {
                    $runsXml .= '<w:rPr><w:b/></w:rPr>';
                }
                $runsXml .= '<w:t>' . $parts[$i] . '</w:t>';
                $runsXml .= '</w:r>' . "\n";
            }

            $xml .= '    <w:p>' . "\n";
            $xml .= '      <w:pPr>';
            if ($isList) {
                $xml .= '<w:ind w:left="' . $indent . '"/>';
            }
            $xml .= '<w:spacing w:after="100" w:line="240" w:lineRule="auto"/>';
            $xml .= '</w:pPr>' . "\n";
            $xml .= $runsXml;
            $xml .= '    </w:p>' . "\n";
        }

        $xml .= '  </w:body>' . "\n";
        $xml .= '</w:document>';
        return $xml;
    }

    public static function createDocx($markdownFile, $outputDocxFile) {
        if (!file_exists($markdownFile)) {
            throw new Exception("File markdown tidak ditemukan: " . $markdownFile);
        }
        $markdownContent = file_get_contents($markdownFile);
        $documentXml = self::convert($markdownContent);

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
              . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n"
              . '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' . "\n"
              . '</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
                     . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
                     . '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
                     . '  <Default Extension="xml" ContentType="application/xml"/>' . "\n"
                     . '  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' . "\n"
                     . '</Types>';

        $zip = new ZipArchive();
        if ($zip->open($outputDocxFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFromString('_rels/.rels', $rels);
            $zip->addFromString('[Content_Types].xml', $contentTypes);
            $zip->addFromString('word/document.xml', $documentXml);
            $zip->close();
            return true;
        } else {
            throw new Exception("Gagal membuat file zip/docx");
        }
    }
}

$response = ['status' => 'error', 'message' => 'Tidak ada aksi.'];

try {
    $workspace = __DIR__;
    $docSistemMd = $workspace . '/DOKUMENTASI_SISTEM.md';
    $docSistemDocx = $workspace . '/DOKUMENTASI_SISTEM.docx';
    
    $panduanMd = $workspace . '/PANDUAN_PENGGUNA.md';
    $panduanDocx = $workspace . '/PANDUAN_PENGGUNA.docx';
    
    MarkdownToDocx::createDocx($docSistemMd, $docSistemDocx);
    MarkdownToDocx::createDocx($panduanMd, $panduanDocx);
    
    $response = [
        'status' => 'success',
        'message' => 'Konversi ke DOCX berhasil dilakukan.',
        'files' => [
            'DOKUMENTASI_SISTEM.docx',
            'PANDUAN_PENGGUNA.docx'
        ]
    ];
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
