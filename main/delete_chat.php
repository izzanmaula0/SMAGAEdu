<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['session_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing session_id']);
    exit;
}

$user_id = $_SESSION['userid'];
$session_id = $data['session_id'];

// Verifikasi bahwa session milik user yang sedang login
$query = "SELECT * FROM ai_chat_sessions WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'is', $session_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'error' => 'Session not found or not authorized']);
    exit;
}

// Hapus pesan terlebih dahulu (karena foreign key constraint)
$query = "DELETE FROM ai_chat_messages WHERE ai_chat_sessions_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $session_id);
$result1 = mysqli_stmt_execute($stmt);

// Kemudian hapus session
$query = "DELETE FROM ai_chat_sessions WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $session_id);
$result2 = mysqli_stmt_execute($stmt);

echo json_encode(['success' => $result1 && $result2]);
?>