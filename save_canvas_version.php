<?php
session_start();
require "koneksi.php";
header('Content-Type: application/json');
// Pastikan request berupa POST dan ada data JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
   
    if (!isset($_SESSION['userid']) || empty($data['session_id']) || empty($data['content'])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
        exit;
    }
   
    $session_id = $data['session_id'];
    $content = $data['content'];
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
   
    // Cari version_number tertinggi yang ada
    $max_version_query = "SELECT MAX(version_number) as max_version FROM canvas_versions WHERE ai_chat_sessions_id = ?";
    $max_version_stmt = mysqli_prepare($koneksi, $max_version_query);
    mysqli_stmt_bind_param($max_version_stmt, "i", $session_id);
    mysqli_stmt_execute($max_version_stmt);
    $max_version_result = mysqli_stmt_get_result($max_version_stmt);
    $row = mysqli_fetch_assoc($max_version_result);
    
    // Version baru = version tertinggi + 1 (atau 1 jika belum ada version)
    $new_version_number = ($row['max_version'] !== null) ? $row['max_version'] + 1 : 1;
    
    // Selalu buat versi baru, jangan update yang sudah ada
    $insert_query = "INSERT INTO canvas_versions (ai_chat_sessions_id, version_number, content) VALUES (?, ?, ?)";
    $insert_stmt = mysqli_prepare($koneksi, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "iis", $session_id, $new_version_number, $content);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Versi canvas berhasil disimpan',
            'version_number' => $new_version_number
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan versi canvas: ' . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method tidak valid']);
}
?>