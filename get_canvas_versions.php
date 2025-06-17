<?php
session_start();
require "koneksi.php";
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || !isset($_GET['session_id'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$session_id = $_GET['session_id'];
$user_id = $_SESSION['userid'];

// Verifikasi session id milik user ini
$check_query = "SELECT id FROM ai_chat_sessions WHERE id = ? AND user_id = ?";
$check_stmt = mysqli_prepare($koneksi, $check_query);
mysqli_stmt_bind_param($check_stmt, "is", $session_id, $user_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Session tidak valid']);
    exit;
}

// Ambil semua versi canvas untuk session ini
$versions_query = "SELECT version_number, content, created_at FROM canvas_versions 
                  WHERE ai_chat_sessions_id = ? ORDER BY version_number ASC";
$versions_stmt = mysqli_prepare($koneksi, $versions_query);
mysqli_stmt_bind_param($versions_stmt, "i", $session_id);
mysqli_stmt_execute($versions_stmt);
$versions_result = mysqli_stmt_get_result($versions_stmt);

$versions = [];
while ($row = mysqli_fetch_assoc($versions_result)) {
    $versions[] = [
        'version_number' => $row['version_number'],
        'content' => $row['content'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode(['success' => true, 'versions' => $versions]);
?>