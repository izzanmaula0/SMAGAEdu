<?php
session_start();
require_once "../koneksi.php";

// Debug info
header('Content-Type: application/json');

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized',
        'session' => isset($_SESSION['userid']) ? $_SESSION['userid'] : 'not set',
        'level' => isset($_SESSION['level']) ? $_SESSION['level'] : 'not set'
    ]);
    exit;
}

// Ambil parameter
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';
$scale = isset($_POST['scale']) ? floatval($_POST['scale']) : 0;

// Validate parameters
if (empty($kelas_id) || $scale <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing or invalid parameters',
        'params' => [
            'kelas_id' => $kelas_id,
            'scale' => $scale
        ]
    ]);
    exit;
}

// Update zoom level
$update_query = "UPDATE presentasi_aktif SET 
                zoom_scale = ?,
                updated_at = NOW()
                WHERE kelas_id = ? AND active = 1";
$stmt = mysqli_prepare($koneksi, $update_query);
mysqli_stmt_bind_param($stmt, "ds", $scale, $kelas_id);

if (mysqli_stmt_execute($stmt)) {
    // Check if any rows were affected
    if (mysqli_affected_rows($koneksi) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Zoom updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'No active presentation found for this class',
            'kelas_id' => $kelas_id
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($koneksi)
    ]);
}
?>