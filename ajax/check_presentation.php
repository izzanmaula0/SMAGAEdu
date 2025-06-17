<?php
session_start();
require_once "../koneksi.php";

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Tambahkan log debugging
error_log("Check presentation request from user: " . $_SESSION['userid']);

// Ambil parameter
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

if (empty($kelas_id)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing kelas_id parameter'
    ]);
    exit;
}

// Periksa presentasi aktif
$query = "SELECT p.*, a.annotations 
          FROM presentasi_aktif p 
          LEFT JOIN presentasi_annotations a 
          ON p.kelas_id = a.kelas_id AND a.page = p.current_slide AND a.active = 1 
          WHERE p.kelas_id = ? AND p.active = 1";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $kelas_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $annotations = null;
    
    // Decode annotation JSON jika ada
    if (isset($row['annotations']) && !empty($row['annotations'])) {
        $annotations = json_decode($row['annotations']);
        error_log("Annotations found for slide " . $row['current_slide'] . ": " . substr($row['annotations'], 0, 100) . "...");
    } else {
        error_log("No annotations found for slide " . $row['current_slide']);
    }
    
    $response = [
        'success' => true,
        'active' => true,
        'presentation_id' => $row['presentation_id'],
        'file_path' => $row['file_path'],
        'current_slide' => (int)$row['current_slide'],
        'total_slides' => (int)$row['total_slides'],
        'zoom_scale' => (float)($row['zoom_scale'] ?? 1.5)
    ];
    
    // Add annotations if available
    if ($annotations !== null) {
        $response['annotations'] = $annotations;
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => true,
        'active' => false,
        'message' => 'No active presentation'
    ]);
}
?>