<?php
session_start();
require "koneksi.php";

$userid = $_SESSION['userid'];

$query = "SELECT s.*, 
    COUNT(h.id) as message_count,
    (SELECT pesan FROM ai_chat_history 
     WHERE session_id = s.id 
     ORDER BY created_at ASC 
     LIMIT 1) as title
    FROM ai_chat_sessions s
    JOIN ai_chat_history h ON s.id = h.session_id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}

echo json_encode($sessions);
?>