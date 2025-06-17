<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['userid'];

// Ambil pesan pertama untuk setiap session sebagai judul
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM ai_chat_messages m WHERE m.ai_chat_sessions_id = s.id) as message_count,
          (SELECT pesan FROM ai_chat_messages m WHERE m.ai_chat_sessions_id = s.id ORDER BY m.created_at ASC LIMIT 1) as first_message
          FROM ai_chat_sessions s
          WHERE s.user_id = ?
          ORDER BY s.created_at DESC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 's', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$sessions = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Gunakan pesan pertama sebagai judul jika tersedia
    if (!empty($row['first_message'])) {
        $row['title'] = $row['first_message'];
    }
    $sessions[] = $row;
}

echo json_encode($sessions);
?>