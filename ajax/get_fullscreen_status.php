<?php
session_start();
require_once "../koneksi.php";

// Cek apakah user sudah login dan merupakan guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil parameter
$kelas_id = isset($_GET['kelas_id']) ? intval($_GET['kelas_id']) : 0;

if (!$kelas_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid kelas_id']);
    exit;
}

// Ambil status fullscreen siswa
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM kelas_siswa WHERE kelas_id = ?) as total_students,
          (SELECT COUNT(*) FROM presentasi_fullscreen_status WHERE kelas_id = ? AND is_fullscreen = 0) as pending_count
          FROM presentasi_fullscreen_status s
          WHERE s.kelas_id = ? AND s.is_fullscreen = 0
          ORDER BY s.updated_at DESC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "iii", $kelas_id, $kelas_id, $kelas_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pending_students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pending_students[] = [
        'id' => $row['siswa_id'],
        'name' => $row['siswa_nama'],
        'browser' => $row['browser'],
        'updated_at' => $row['updated_at']
    ];
}

// Ambil metadata
$total_students = 0;
$pending_count = 0;
if (!empty($pending_students)) {
    $total_students = $pending_students[0]['total_students'];
    $pending_count = $pending_students[0]['pending_count'];
}

echo json_encode([
    'success' => true,
    'total_students' => $total_students,
    'pending_count' => $pending_count,
    'pending_students' => $pending_students
]);
?>