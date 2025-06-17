<?php
session_start();
require "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Ambil daftar kelas yang tidak diarsipkan
$query = "SELECT id, mata_pelajaran, tingkat FROM kelas WHERE is_archived = 0 ORDER BY tingkat, mata_pelajaran";
$result = mysqli_query($koneksi, $query);

$kelas_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kelas_list[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($kelas_list);
?>