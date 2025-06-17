<?php
session_start();
require "koneksi.php";

$userid = $_SESSION['userid'];
$query = "SELECT * FROM ai_chat_history WHERE user_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No records found']);
    exit;
}

$rows = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);
?>