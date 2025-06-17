<?php
session_start();
require "koneksi.php";

$post_id = $_GET['post_id'];

$query = "SELECT emoji, COUNT(*) as count FROM reactions WHERE post_id = ? GROUP BY emoji";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reactions[$row['emoji']] = (int)$row['count'];
}

echo json_encode(['success' => true, 'reactions' => $reactions]);