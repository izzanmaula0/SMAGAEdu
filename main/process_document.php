<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

function cleanExcelValue($value)
{
    $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value); // Hapus karakter tersembunyi
    $value = str_replace(["\xC2\xA0", "\xE2\x80\x8B"], ' ', $value);    // Non-breaking space ke spasi biasa
    $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);            // Karakter kontrol
    return trim($value);
}

function extractText($file)
{
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    error_log("[Server Processor] File received: " . $file['name'] . " | Type: " . $ext);


    try {
        switch ($ext) {
            case 'pdf':
                $parser = new Parser();
                $pdf = $parser->parseFile($file['tmp_name']);
                $text = $pdf->getText();
                // Bersihkan text
                $text = preg_replace('/\s+/', ' ', $text); // Gabungkan whitespace
                $text = trim($text); // Hapus whitespace di awal/akhir
                $text = preg_replace('/\x{200B}-\x{200D}\x{FEFF}/u', '', $text); // Hapus karakter tersembunyi
                $text = str_replace(["\xC2\xA0", "\xE2\x80\x8B"], ' ', $text); // Non-breaking space ke spasi biasa
                $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text); // Hapus karakter kontrol
                $text = iconv('UTF-8', 'UTF-8//IGNORE', $text); // Buang karakter non-UTF8

                if (strlen($text) > 10000) {
                    $text = substr($text, 0, 10000) . "\n\n[Dokumen terpotong karena terlalu panjang...]";
                }
                
                // Tambahkan log untuk debugging
                error_log("[PDF Processor] Extracted text length: " . strlen($text) . " characters");
                
                return "=== DOKUMEN PDF: {$file['name']} ===\n\nISI DOKUMEN:\n" . $text;

            case 'docx':
                $zip = new ZipArchive();
                $zip->open($file['tmp_name']);
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                return strip_tags($content);

            case 'txt':
                return file_get_contents($file['tmp_name']);

            case 'xlsx':
            case 'xls':
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $content = "";

                foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                    $sheetName = $worksheet->getTitle();
                    $content .= "=== SHEET: $sheetName ===\n";

                    $rowIterator = $worksheet->getRowIterator();
                    $rowCount = 0;
                    foreach ($rowIterator as $row) {
                        if ($rowCount++ == 0) continue; // Skip header

                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);

                        $rowData = [];
                        foreach ($cellIterator as $cell) {
                            $value = $cell->getValue();
                            if ($cell->getDataType() == 'f') { // Handle formula
                                try {
                                    $value = $cell->getCalculatedValue();
                                } catch (\Exception $e) {
                                    $value = '#FORMULA ERROR#';
                                }
                            }
                            $rowData[] = cleanExcelValue($value);
                        }

                        $content .= "ROW $rowCount: " . implode(" | ", $rowData) . "\n";
                        if ($rowCount > 100) break; // Batasi 100 baris
                    }
                    $content .= "\n";
                }
                return $content;

            default:
                throw new Exception("Unsupported file type: $ext");
        }
    } catch (Exception $e) {
        error_log("[Error Processor] Error processing " . $ext . " file: " . $e->getMessage());
        throw new Exception('Error processing file: ' . $e->getMessage());
    }
}

if (isset($_FILES['file'])) {
    try {
        $content = extractText($_FILES['file']);
        echo json_encode([
            'success' => true,
            'content' => $content,
            'type' => pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
