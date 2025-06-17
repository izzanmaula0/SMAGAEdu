<?php
require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

function extractTextFromFile($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    try {
        switch($ext) {
            case 'pdf':
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            
            case 'docx':
                $zip = new ZipArchive();
                $zip->open($filePath);
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                return strip_tags($content);
                
            case 'txt':
                return file_get_contents($filePath);
                
            default:
                return null;
        }
    } catch(Exception $e) {
        error_log('File extraction error: ' . $e->getMessage());
        return null;
    }
}

function saveFileContent($projectFileId, $content) {
    global $koneksi;
    
    // Simpan konten mentah
    $stmt = $koneksi->prepare(
        "INSERT INTO project_file_contents (project_file_id, content) VALUES (?, ?)"
    );
    $stmt->bind_param("is", $projectFileId, $content);
    $stmt->execute();
    
    return $stmt->insert_id;
}

// Proses batch ekstraksi
function processUploadedFiles($projectId) {
    global $koneksi;
    
    $query = "SELECT id, file_path FROM project_files WHERE project_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($file = $result->fetch_assoc()) {
        $content = extractTextFromFile($file['file_path']);
        if ($content) {
            saveFileContent($file['id'], $content);
        }
    }
}