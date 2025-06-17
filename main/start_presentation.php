<?php
session_start();
require_once 'koneksi.php';
require_once 'presentation_handler.php';

error_log("POST data: " . print_r($_POST, true));

error_log("Request: " . print_r($_POST, true));
error_log("kelas_id: " . $_POST['kelas_id']);
error_log("presentation_id: " . $_POST['presentation_id']);
error_log("total_slides: " . $_POST['total_slides']);
error_log("file_type: " . $_POST['file_type']);

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
$presentation_id = isset($_POST['presentation_id']) ? $_POST['presentation_id'] : '';
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';
$total_slides = isset($_POST['total_slides']) ? intval($_POST['total_slides']) : 0;
$file_type = isset($_POST['file_type']) ? $_POST['file_type'] : 'pdf';

// Validasi input
if (empty($presentation_id) || empty($kelas_id) || $total_slides <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

// Mulai presentasi
$result = start_presentation($kelas_id, $presentation_id, $total_slides, $file_type);

// Kirim respons
if ($result) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memulai presentasi'
    ]);
}
?>