<?php
session_start();
require "../koneksi.php";

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Ambil parameter
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';

if (empty($kelas_id)) {
    exit(json_encode(['success' => false, 'message' => 'Missing parameters']));
}

// Deactivate presentation
$update_query = "UPDATE presentasi_aktif SET 
                active = 0,
                updated_at = NOW()
                WHERE kelas_id = ?";
$stmt = mysqli_prepare($koneksi, $update_query);
mysqli_stmt_bind_param($stmt, "s", $kelas_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>