<?php
session_start();
require "koneksi.php";

error_log("Archive process started"); // Debug log

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    error_log("Session check failed: " . print_r($_SESSION, true));
    header("Location: index.php");
    exit();
}

$kelas_id = $_GET['id'];
$siswa_id = $_SESSION['userid'];

error_log("Kelas ID: " . $kelas_id . ", Siswa ID: " . $siswa_id); // Debug log

// Get student's numeric ID
$siswa_query = "SELECT id FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $siswa_query);
mysqli_stmt_bind_param($stmt, "s", $siswa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

if(!$siswa) {
    error_log("Student not found for username: " . $siswa_id);
    header("Location: beranda.php");
    exit();
}

$numeric_siswa_id = $siswa['id'];
error_log("Numeric Siswa ID: " . $numeric_siswa_id); // Debug log

// Update archive status
$update_query = "UPDATE kelas_siswa SET is_archived = 1 
                WHERE kelas_id = ? AND siswa_id = ?";
$stmt = mysqli_prepare($koneksi, $update_query);
mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $numeric_siswa_id);

if(mysqli_stmt_execute($stmt)) {
    error_log("Archive successful");
    $_SESSION['message'] = "Kelas berhasil diarsipkan.";
} else {
    error_log("Archive failed: " . mysqli_error($koneksi));
    $_SESSION['error'] = "Gagal mengarsipkan kelas.";
}

header("Location: beranda.php");
exit();
?>