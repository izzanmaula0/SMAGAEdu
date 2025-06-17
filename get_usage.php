<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];

$query_usage = "SELECT ai_usage_count, ai_usage_date FROM siswa WHERE username = ?";
$stmt_usage = mysqli_prepare($koneksi, $query_usage);
mysqli_stmt_bind_param($stmt_usage, "s", $userid);
mysqli_stmt_execute($stmt_usage);
$result_usage = mysqli_stmt_get_result($stmt_usage);
$usage = mysqli_fetch_assoc($result_usage);

$remaining_usage = 20 - ($usage['ai_usage_count'] ?? 0);

echo json_encode(['remaining_usage' => $remaining_usage]);
?>