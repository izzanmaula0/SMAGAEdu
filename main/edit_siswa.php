<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update_siswa'])) {
    $siswa_id = mysqli_real_escape_string($koneksi, $_POST['siswa_id']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // Periksa apakah username sudah digunakan oleh siswa lain
    $cek_username = mysqli_query($koneksi, "SELECT * FROM siswa WHERE username = '$username' AND id != '$siswa_id'");
    if (mysqli_num_rows($cek_username) > 0) {
        $_SESSION['error'] = "Username sudah digunakan oleh siswa lain!";
        header("Location: manajemen_siswa.php");
        exit();
    }

    // Upload foto jika ada
    $foto_update = "";
    if ($_FILES['foto_siswa']['size'] > 0) {
        $target_dir = "uploads/profil/";
        
        // Buat direktori jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["foto_siswa"]["name"], PATHINFO_EXTENSION));
        $new_filename = $username . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Cek tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            header("Location: manajemen_siswa.php");
            exit();
        }
        
        // Cek ukuran file (max 5MB)
        if ($_FILES["foto_siswa"]["size"] > 5000000) {
            $_SESSION['error'] = "Maaf, ukuran file terlalu besar (maksimal 5MB).";
            header("Location: manajemen_siswa.php");
            exit();
        }
        
        // Upload file
        if (move_uploaded_file($_FILES["foto_siswa"]["tmp_name"], $target_file)) {
            $foto_update = ", foto_profil = '$new_filename', photo_type = 'upload'";
        } else {
            $_SESSION['error'] = "Maaf, terjadi kesalahan saat mengunggah file.";
            header("Location: manajemen_siswa.php");
            exit();
        }
    }

    // Update data siswa
    $update_query = "UPDATE siswa SET 
                    username = '$username', 
                    password = '$password', 
                    nama = '$nama', 
                    tingkat = '$tingkat', 
                    tahun_masuk = '$tahun_masuk', 
                    nis = '$nis',
                    no_hp = '$no_hp',
                    alamat = '$alamat'
                    $foto_update
                    WHERE id = '$siswa_id'";
    
    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['success'] = "Data siswa berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
    
    header("Location: manajemen_siswa.php");
    exit();
}

// Jika tidak ada form submission, redirect ke halaman manajemen siswa
header("Location: manajemen_siswa.php");
exit();
?>