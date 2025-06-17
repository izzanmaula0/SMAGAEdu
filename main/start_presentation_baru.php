<?php
session_start();
require_once 'koneksi.php';
require_once 'presentasi_handler_baru.php';

// Log semua data yang diterima
error_log("POST data baru: " . print_r($_POST, true));

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Dapatkan parameter
$file_path = isset($_POST['presentation_id']) ? $_POST['presentation_id'] : '';
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';
$total_slides = isset($_POST['total_slides']) ? intval($_POST['total_slides']) : 0;
$file_type = isset($_POST['file_type']) ? $_POST['file_type'] : 'pdf';
$zoom_scale = isset($_POST['zoom_scale']) ? floatval($_POST['zoom_scale']) : 1.5;

// Validasi input
if (empty($file_path) || empty($kelas_id) || $total_slides <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak lengkap: file_path=' . $file_path . ', kelas_id=' . $kelas_id . ', total_slides=' . $total_slides
    ]);
    exit;
}

// Mulai presentasi dengan fungsi baru
$result = start_presentation_baru($kelas_id, $file_path, $total_slides, $file_type, $zoom_scale);

// Kirim respons
if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Presentasi berhasil dimulai'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memulai presentasi'
    ]);
}
?>