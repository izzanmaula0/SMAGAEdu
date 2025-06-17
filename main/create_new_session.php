<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['userid'];

try {
    // Hapus session ID aktif terlebih dahulu
    unset($_SESSION['active_chat_session']);
    
    // Buat sesi chat baru
    $query = "INSERT INTO ai_chat_sessions (user_id, title, created_at) 
              VALUES (?, 'Chat Baru', NOW())";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 's', $user_id);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        throw new Exception('Failed to create new session: ' . mysqli_error($koneksi));
    }
    
    $new_session_id = mysqli_insert_id($koneksi);
    
    // Set session ID baru
    $_SESSION['active_chat_session'] = $new_session_id;
    
    echo json_encode([
        'success' => true, 
        'session_id' => $new_session_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>