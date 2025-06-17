<?php
session_start();
require_once 'koneksi.php';
require_once 'presentation_handler.php';

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

// Dapatkan parameter
$presentation_id = isset($_POST['id']) ? $_POST['id'] : '';
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';
$total_slides = isset($_POST['total_slides']) ? intval($_POST['total_slides']) : 0;
$file_type = isset($_POST['file_type']) ? $_POST['file_type'] : 'pdf';

// Validasi input
if (empty($presentation_id) || empty($kelas_id) || $slide <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

// Update slide
$result = update_presentation_slide($kelas_id, $presentation_id, $slide);

// Kirim respons
if ($result) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengupdate slide'
    ]);
}
?>