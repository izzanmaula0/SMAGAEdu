<?php
session_start();
require "koneksi.php";

// Cek apakah user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Cek apakah ada tugas_id
if (!isset($_POST['tugas_id'])) {
    header("Location: beranda_guru.php");
    exit();
}

$tugas_id = mysqli_real_escape_string($koneksi, $_POST['tugas_id']);

// Cek kepemilikan tugas
$query_cek = "SELECT t.*, p.user_id as guru_id 
              FROM tugas t 
              JOIN postingan_kelas p ON t.postingan_id = p.id 
              WHERE t.id = '$tugas_id'";
$result_cek = mysqli_query($koneksi, $query_cek);

if (mysqli_num_rows($result_cek) == 0) {
    header("Location: beranda_guru.php");
    exit();
}

$data_tugas = mysqli_fetch_assoc($result_cek);

// Pastikan guru yang login adalah pemilik tugas
if ($data_tugas['guru_id'] != $_SESSION['userid']) {
    header("Location: beranda_guru.php");
    exit();
}

// Update batas waktu tugas menjadi sekarang
$query_update = "UPDATE tugas 
                SET status = 'closed', 
                    batas_waktu = NOW() 
                WHERE id = '$tugas_id'";
if (mysqli_query($koneksi, $query_update)) {
    // Redirect kembali ke halaman detail tugas dengan pesan sukses
    header("Location: detail_tugas.php?id=" . $tugas_id . "&pesan=Tugas berhasil ditutup");
} else {
    // Redirect dengan pesan error
    header("Location: detail_tugas.php?id=" . $tugas_id . "&pesan=Gagal menutup tugas");
}
exit();
?>