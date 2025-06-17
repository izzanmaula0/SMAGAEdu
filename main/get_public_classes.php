<?php
session_start();
require "koneksi.php";

// Pastikan user login
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];

// Ambil data siswa untuk mengetahui tingkat kelasnya
$query_siswa = "SELECT * FROM siswa WHERE username = ?";
$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "s", $userid);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);


// Query untuk kelas publik
$query = "SELECT k.*, g.namaLengkap as nama_guru, g.foto_profil,
          (SELECT COUNT(*) FROM kelas_siswa ks 
           JOIN siswa s ON ks.siswa_id = s.id 
           WHERE ks.kelas_id = k.id AND s.username = ?) as is_joined
          FROM kelas k
          JOIN guru g ON k.guru_id = g.username
          WHERE k.is_public = 1 AND k.is_archived = 0";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$classes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['is_joined'] = (int)$row['is_joined'] > 0;
    $classes[] = $row;
}

echo json_encode($classes);