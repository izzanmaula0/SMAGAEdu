<?php
session_start();
require "koneksi.php";

// if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
//     header("Location: index.php");
//     exit();
// }

$kelas_id = $_GET['id'];
$siswa_id = $_SESSION['userid'];

// Get student's numeric ID
$siswa_query = "SELECT id FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $siswa_query);
mysqli_stmt_bind_param($stmt, "s", $siswa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
$numeric_siswa_id = $siswa['id'];

// Update archive status
$update_query = "UPDATE kelas_siswa SET is_archived = 0 WHERE kelas_id = ? AND siswa_id = ?";
$stmt = mysqli_prepare($koneksi, $update_query);
mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $numeric_siswa_id);
mysqli_stmt_execute($stmt);

header("Location: beranda.php");
exit();
?>