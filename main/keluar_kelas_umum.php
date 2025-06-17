<?php
session_start();
require "koneksi.php";

// Cek apakah pengguna sudah login sebagai siswa
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Cek apakah ID kelas ada
if (!isset($_GET['id'])) {
    header("Location: beranda.php");
    exit();
}

$kelas_id = intval($_GET['id']);
$userid = $_SESSION['userid'];

// Ambil ID siswa berdasarkan username
$query_siswa = "SELECT id FROM siswa WHERE username = ?";
$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "s", $userid);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);

if (mysqli_num_rows($result_siswa) == 0) {
    header("Location: beranda.php?error=siswa_tidak_ditemukan");
    exit();
}

$siswa = mysqli_fetch_assoc($result_siswa);
$siswa_id = $siswa['id'];

// Periksa apakah kelas adalah kelas umum
$query_cek = "SELECT is_public FROM kelas WHERE id = ?";
$stmt_cek = mysqli_prepare($koneksi, $query_cek);
mysqli_stmt_bind_param($stmt_cek, "i", $kelas_id);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    header("Location: beranda.php?error=kelas_tidak_ditemukan");
    exit();
}

$kelas = mysqli_fetch_assoc($result_cek);

// Pastikan ini adalah kelas umum
if (!$kelas['is_public']) {
    header("Location: beranda.php?error=bukan_kelas_umum");
    exit();
}

// Hapus siswa dari kelas
$query_hapus = "DELETE FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?";
$stmt_hapus = mysqli_prepare($koneksi, $query_hapus);
mysqli_stmt_bind_param($stmt_hapus, "ii", $kelas_id, $siswa_id);

if (mysqli_stmt_execute($stmt_hapus)) {
    header("Location: beranda.php?success=keluar_kelas_berhasil");
} else {
    header("Location: beranda.php?error=keluar_kelas_gagal");
}
exit();
?>