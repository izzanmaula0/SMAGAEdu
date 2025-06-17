<?php
session_start();
require "koneksi.php";

// Cek apakah user sudah login dan adalah guru
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $guru_id = $_SESSION['userid'];

    // Verifikasi bahwa kelas ini milik guru yang bersangkutan
    $query_check = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '$guru_id'";
    $result_check = mysqli_query($koneksi, $query_check);

    if(mysqli_num_rows($result_check) > 0) {
        // Update deskripsi
        $query_update = "UPDATE kelas SET deskripsi = '$deskripsi' WHERE id = '$kelas_id'";
        
        if(mysqli_query($koneksi, $query_update)) {
            header("Location: kelas_guru.php?id=" . $kelas_id);
        } else {
            header("Location: kelas_guru.php?id=" . $kelas_id . "&error=update_failed");
        }
    } else {
        header("Location: beranda_guru.php");
    }
} else {
    header("Location: beranda_guru.php");
}
?>