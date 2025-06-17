<?php
session_start();
header('Content-Type: application/json');
require "koneksi.php";

// Pastikan request adalah POST dan JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Parse JSON body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit();
}

// Validasi data
if (!isset($data['session_id']) || !isset($data['version_number']) || !isset($data['content'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$session_id = $data['session_id'];
$version_number = intval($data['version_number']);
$content = $data['content'];

// Periksa apakah versi ini sudah ada
$query = "SELECT id FROM canvas_versions WHERE session_id = ? AND version_number = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ii", $session_id, $version_number);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    // Update versi yang sudah ada
    mysqli_stmt_bind_result($stmt, $version_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    $update_query = "UPDATE canvas_versions SET content = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($koneksi, $update_query);
    mysqli_stmt_bind_param($update_stmt, "si", $content, $version_id);
    $success = mysqli_stmt_execute($update_stmt);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update version: ' . mysqli_error($koneksi)]);
    }
    
    mysqli_stmt_close($update_stmt);
} else {
    // Versi tidak ditemukan
    echo json_encode(['success' => false, 'error' => 'Version not found']);
}

mysqli_close($koneksi);