<?php
session_start();
require "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

// Pastikan data lengkap
if (!isset($data['session_id']) || !isset($data['revision_content'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$session_id = $data['session_id'];
$revision_content = $data['revision_content'];
$user_id = $_SESSION['userid'];

// Verifikasi bahwa session milik user yang sedang login
$query = "SELECT id FROM ai_chat_sessions WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'is', $session_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    exit;
}

// Ambil pesan terakhir yang menggunakan canvas dari session ini
$query = "SELECT id FROM ai_chat_messages WHERE ai_chat_sessions_id = ? AND uses_canvas = 1 ORDER BY created_at DESC LIMIT 1";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Update pesan yang ada
    $row = mysqli_fetch_assoc($result);
    $message_id = $row['id'];
    
    $query = "UPDATE ai_chat_messages SET respons = ? WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'si', $revision_content, $message_id);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Canvas revision saved']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($koneksi)]);
    }
} else {
    // Jika tidak ada pesan canvas sebelumnya, tambahkan pesan baru
    $query = "INSERT INTO ai_chat_messages (ai_chat_sessions_id, pesan, respons, created_at, uses_canvas) 
              VALUES (?, 'Canvas Revision', ?, NOW(), 1)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'is', $session_id, $revision_content);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'New canvas content saved']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($koneksi)]);
    }
}
?>