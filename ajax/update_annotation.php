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

// Ambil parameter - Perbaikan cara menerima data JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$kelas_id = isset($data['kelas_id']) ? $data['kelas_id'] : '';
$page = isset($data['page']) ? intval($data['page']) : 0;
$annotations = isset($data['annotations']) ? $data['annotations'] : [];

// Log untuk debugging
error_log("Received annotation update: " . print_r([
    'kelas_id' => $kelas_id,
    'page' => $page,
    'annotations_count' => count($annotations)
], true));

// Validate parameters - Allow empty annotations array for clearing
if (empty($kelas_id) || $page <= 0 || !isset($data['annotations'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid parameters',
        'params' => [
            'kelas_id' => $kelas_id,
            'page' => $page,
            'annotations' => isset($annotations) ? count($annotations) . ' items' : 'not set'
        ],
        'received' => $input
    ]);
    exit;
}

// Encode annotations as JSON for storage
$annotations_json = json_encode($annotations);

// Check if an annotation record exists for this page
$check_query = "SELECT id FROM presentasi_annotations 
               WHERE kelas_id = ? AND page = ? AND active = 1";
$check_stmt = mysqli_prepare($koneksi, $check_query);
mysqli_stmt_bind_param($check_stmt, "si", $kelas_id, $page);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    // Update existing annotation
    $update_query = "UPDATE presentasi_annotations SET 
                    annotations = ?,
                    updated_at = NOW()
                    WHERE kelas_id = ? AND page = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $annotations_json, $kelas_id, $page);
} else {
    // Insert new annotation
    $insert_query = "INSERT INTO presentasi_annotations 
                    (kelas_id, page, annotations, active, created_at, updated_at) 
                    VALUES (?, ?, ?, 1, NOW(), NOW())";
    $stmt = mysqli_prepare($koneksi, $insert_query);
    mysqli_stmt_bind_param($stmt, "sis", $kelas_id, $page, $annotations_json);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => 'Annotations updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($koneksi)
    ]);
}
