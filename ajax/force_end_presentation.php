<?php
session_start();
require "../koneksi.php";

// Log untuk debugging
error_log("Force end presentation request received for kelas_id: " . (isset($_POST['kelas_id']) ? $_POST['kelas_id'] : 'not set'));

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    error_log("Unauthorized access to force_end_presentation.php by user: " . (isset($_SESSION['userid']) ? $_SESSION['userid'] : 'unknown'));
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Pastikan kelas_id diberikan
if (!isset($_POST['kelas_id']) || empty($_POST['kelas_id'])) {
    error_log("Missing kelas_id parameter in force_end_presentation.php");
    echo json_encode([
        'success' => false, 
        'message' => 'Missing kelas_id parameter'
    ]);
    exit;
}

$kelas_id = $_POST['kelas_id'];

// Periksa status saat ini sebelum update
$check_query = "SELECT active FROM presentasi_aktif WHERE kelas_id = ?";
$check_stmt = mysqli_prepare($koneksi, $check_query);
mysqli_stmt_bind_param($check_stmt, "s", $kelas_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) == 0) {
    error_log("No active presentation found for kelas_id: $kelas_id");
    echo json_encode([
        'success' => false,
        'message' => 'No active presentation record found'
    ]);
    exit;
}

mysqli_stmt_bind_result($check_stmt, $current_active);
mysqli_stmt_fetch($check_stmt);
mysqli_stmt_close($check_stmt);

error_log("Current active status for kelas_id $kelas_id: " . ($current_active ? 'Active' : 'Inactive'));

// Update status active menjadi 0 secara paksa
$query = "UPDATE presentasi_aktif SET active = 0, updated_at = NOW() WHERE kelas_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $kelas_id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    error_log("Successfully ended presentation for kelas_id: $kelas_id");
    
    // Verifikasi apakah update berhasil
    $verify_query = "SELECT active FROM presentasi_aktif WHERE kelas_id = ?";
    $verify_stmt = mysqli_prepare($koneksi, $verify_query);
    mysqli_stmt_bind_param($verify_stmt, "s", $kelas_id);
    mysqli_stmt_execute($verify_stmt);
    mysqli_stmt_bind_result($verify_stmt, $new_active);
    mysqli_stmt_fetch($verify_stmt);
    mysqli_stmt_close($verify_stmt);
    
    error_log("New active status after update for kelas_id $kelas_id: " . ($new_active ? 'Still Active' : 'Inactive'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Presentation ended successfully for all students',
        'previous_status' => $current_active,
        'new_status' => $new_active
    ]);
} else {
    error_log("Failed to end presentation for kelas_id: $kelas_id. Error: " . mysqli_error($koneksi));
    echo json_encode([
        'success' => false,
        'message' => 'Failed to end presentation: ' . mysqli_error($koneksi)
    ]);
}
?>