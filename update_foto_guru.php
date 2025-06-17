<?php
session_start();
require "koneksi.php";

// Cek apakah user yang login adalah kepala sekolah
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['cropped_image']) && isset($_POST['image_type'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $image_data = $_POST['cropped_image'];
    $image_type = $_POST['image_type'];
    
    // Periksa apakah username valid
    $check_query = "SELECT * FROM guru WHERE username = '$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Decode base64 image
        list(, $image_data) = explode(',', $image_data);
        $image_data = base64_decode($image_data);
        
        // Buat nama file unik
        $file_name = uniqid() . '_' . time() . '.jpg';
        
        // Tentukan folder berdasarkan tipe gambar
        if ($image_type === 'profil') {
            $folder = 'uploads/profil/';
            $db_field = 'foto_profil';
        } else {
            $folder = 'uploads/background/';
            $db_field = 'foto_latarbelakang';
        }
        
        // Pastikan folder ada
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        
        // Path lengkap file
        $file_path = $folder . $file_name;
        
        // Simpan gambar
        file_put_contents($file_path, $image_data);
        
        // Update database
        $update_query = "UPDATE guru SET $db_field = '$file_name' WHERE username = '$username'";
        if (mysqli_query($koneksi, $update_query)) {
            $_SESSION['success'] = "Foto berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat memperbarui database: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Username tidak valid!";
    }
    
    // Redirect kembali ke halaman edit guru
    header("Location: edit_guru.php?username=$username");
    exit();
} else {
    header("Location: guru_admin.php");
    exit();
}