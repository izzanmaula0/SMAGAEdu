<?php
session_start();
require "koneksi.php";

// Pastikan yang mengakses adalah guru
if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data yang dikirim dari form
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = isset($_POST['tingkat']) ? mysqli_real_escape_string($koneksi, $_POST['tingkat']) : null;
    $tahun_masuk = isset($_POST['tahun_masuk']) ? mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']) : null;
    $no_hp = isset($_POST['no_hp']) ? mysqli_real_escape_string($koneksi, $_POST['no_hp']) : null;
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($koneksi, $_POST['alamat']) : null;
    $nis = isset($_POST['nis']) ? mysqli_real_escape_string($koneksi, $_POST['nis']) : null;
    
    // Update data siswa sesuai dengan kolom yang ada di database
    $query = "UPDATE siswa SET 
        nama = ?, 
        tingkat = ?,
        tahun_masuk = ?,
        no_hp = ?,
        alamat = ?,
        nis = ?
        WHERE username = ?";

    // Menggunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssissis", 
        $nama,
        $tingkat,
        $tahun_masuk,
        $no_hp,
        $alamat,
        $nis,
        $username
    );

    if(mysqli_stmt_execute($stmt)) {
        // Jika berhasil, redirect dengan pesan sukses
        $_SESSION['message'] = "Data siswa berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
    } else {
        // Jika gagal, redirect dengan pesan error
        $_SESSION['message'] = "Gagal memperbarui data siswa: " . mysqli_error($koneksi);
        $_SESSION['message_type'] = "danger";
    }

    mysqli_stmt_close($stmt);

    // Redirect kembali ke halaman edit
    header("Location: siswa_admin.php");
    exit();
} else {
    // Jika bukan method POST, redirect ke halaman utama
    header("Location: siswa_admin.php");
    exit();
}
?>