<?php
session_start();
require_once 'koneksi.php';
require_once 'includes/presentation/upload_handler.php';

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

// Dapatkan daftar presentasi
$presentations = get_class_presentations($kelas_id);

// Kirim respons
echo json_encode([
    'success' => true,
    'presentations' => $presentations
]);
?>