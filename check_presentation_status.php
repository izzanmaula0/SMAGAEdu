<?php
session_start();
require_once 'koneksi.php';
require_once 'includes/presentation/presentation_handler.php';

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

// Dapatkan parameter
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

// Validasi input
if (empty($kelas_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

// Cek status presentasi
$status = check_presentation_status($kelas_id);

// Kirim respons
echo json_encode([
    'success' => true,
    'active' => isset($status['active']) && $status['active'] == 1,
    'presentation_id' => $status['active'] == 1 ? $status['presentation_id'] : null,
    'current_slide' => $status['active'] == 1 ? $status['current_slide'] : null,
    'total_slides' => $status['active'] == 1 ? $status['total_slides'] : null
]);
?>