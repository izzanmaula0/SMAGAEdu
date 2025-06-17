<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || !isset($_GET['session_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['userid'];
$session_id = $_GET['session_id'];

// Verifikasi bahwa session milik user yang sedang login
$query = "SELECT * FROM ai_chat_sessions WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'is', $session_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode([]);
    exit;
}

// Ambil pesan-pesan dari session
$query = "SELECT * FROM ai_chat_messages WHERE ai_chat_sessions_id = ? ORDER BY created_at ASC";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);
?>