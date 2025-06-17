<?php
session_start();
require_once 'koneksi.php';

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Cek apakah file diupload
if (!isset($_FILES['presentationFile']) || $_FILES['presentationFile']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid file upload']);
    exit;
}

$file = $_FILES['presentationFile'];
$kelas_id = $_POST['kelas_id'] ?? '';
$total_slides = $_POST['totalSlides'] ?? 20;

// Validasi file type
$allowed_types = ['application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit;
}

// Simpan file
$upload_dir = 'uploads/presentations/';
$filename = time() . '_' . basename($file['name']);
$file_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $file_path)) {
    echo json_encode([
        'success' => true, 
        'file_path' => $file_path,
        'file_type' => $file['type'],
        'total_slides' => $total_slides
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}
?>