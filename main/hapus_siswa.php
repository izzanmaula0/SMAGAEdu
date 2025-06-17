<?php
session_start();
require "koneksi.php";

// Cek apakah user adalah guru
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Cek apakah parameter yang diperlukan ada
if(!isset($_GET['siswa_id']) || !isset($_GET['kelas_id'])) {
    header("Location: beranda_guru.php");
    exit();
}

$siswa_id = mysqli_real_escape_string($koneksi, $_GET['siswa_id']);
$kelas_id = mysqli_real_escape_string($koneksi, $_GET['kelas_id']);

// Verifikasi apakah guru ini adalah pengajar di kelas tersebut
$query_verify = "SELECT id FROM kelas WHERE id = '$kelas_id' AND guru_id = '{$_SESSION['userid']}'";
$result_verify = mysqli_query($koneksi, $query_verify);

if(mysqli_num_rows($result_verify) == 0) {
    // Jika guru bukan pengajar di kelas ini
    header("Location: beranda_guru.php");
    exit();
}

// Proses hapus siswa dari kelas
$query_hapus = "DELETE FROM kelas_siswa WHERE kelas_id = '$kelas_id' AND siswa_id = '$siswa_id'";

if(mysqli_query($koneksi, $query_hapus)) {
    // Jika berhasil, kembali ke halaman kelas dengan pesan sukses
    header("Location: kelas_guru.php?id=$kelas_id&pesan=siswa_dihapus");
} else {
    // Jika gagal, kembali dengan pesan error
    header("Location: kelas_guru.php?id=$kelas_id&pesan=gagal_hapus");
}
?>