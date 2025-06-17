<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

$userid = $_SESSION['userid'];

// Update nama dan gelar
if(isset($_POST['update_nama'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    mysqli_query($koneksi, "UPDATE guru SET namaLengkap = '$nama' WHERE username = '$userid'");
    header("Location: profil_guru.php?pesan=sukses");
    exit();
}

// Update foto profil
if(isset($_FILES['foto_profil'])) {
    $target_dir = "uploads/profil/";
    if(!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION));
    $new_filename = "profil_" . $userid . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if(move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
        mysqli_query($koneksi, "UPDATE guru SET foto_profil = '$target_file' WHERE username = '$userid'");
    }
    header("Location: profil_guru.php?pesan=sukses");
    exit();
}

// Update foto latar belakang
if(isset($_FILES['foto_latarbelakang'])) {
    $target_dir = "uploads/background/";
    if(!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["foto_latarbelakang"]["name"], PATHINFO_EXTENSION));
    $new_filename = "bg_" . $userid . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if(move_uploaded_file($_FILES["foto_latarbelakang"]["tmp_name"], $target_file)) {
        mysqli_query($koneksi, "UPDATE guru SET foto_latarbelakang = '$target_file' WHERE username = '$userid'");
    }
    header("Location: profil_guru.php?pesan=sukses");
    exit();
}

if(isset($_POST['cropped_image']) && isset($_POST['image_type'])) {
    $image_data = $_POST['cropped_image'];
    $image_type = $_POST['image_type'];
    
    // Hapus header base64
    $image_parts = explode(";base64,", $image_data);
    $image_base64 = base64_decode($image_parts[1]);
    
    // Generate nama file
    $filename = uniqid() . '_' . time() . '.jpg';
    
    // Tentukan folder berdasarkan tipe
    $folder = ($image_type === 'profil') ? 'uploads/profil/' : 'uploads/background/';
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    // Simpan file
    $file_path = $folder . $filename;
    file_put_contents($file_path, $image_base64);
    
    // Update database
    $column = ($image_type === 'profil') ? 'foto_profil' : 'foto_latarbelakang';
    $query = "UPDATE guru SET $column = '$filename' WHERE username = '$userid'";
    mysqli_query($koneksi, $query);
    
    header("Location: profil_guru.php?pesan=sukses");
    exit();
}

// Update informasi lengkap
if(isset($_POST['update_info'])) {
    $pendidikan_s1 = mysqli_real_escape_string($koneksi, $_POST['pendidikan_s1']);
    $pendidikan_s2 = mysqli_real_escape_string($koneksi, $_POST['pendidikan_s2']);
    $pendidikan_lainnya = mysqli_real_escape_string($koneksi, $_POST['pendidikan_lainnya']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $sertifikasi1 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi1']);
    $sertifikasi2 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi2']);
    $sertifikasi3 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi3']);
    $publikasi1 = mysqli_real_escape_string($koneksi, $_POST['publikasi1']);
    $publikasi2 = mysqli_real_escape_string($koneksi, $_POST['publikasi2']);
    $publikasi3 = mysqli_real_escape_string($koneksi, $_POST['publikasi3']);
    $proyek1 = mysqli_real_escape_string($koneksi, $_POST['proyek1']);
    $proyek2 = mysqli_real_escape_string($koneksi, $_POST['proyek2']);
    $proyek3 = mysqli_real_escape_string($koneksi, $_POST['proyek3']);

    $query = "UPDATE guru SET 
              pendidikan_s1 = '$pendidikan_s1',
              pendidikan_s2 = '$pendidikan_s2',
              pendidikan_lainnya = '$pendidikan_lainnya',
              jabatan = '$jabatan',
              sertifikasi1 = '$sertifikasi1',
              sertifikasi2 = '$sertifikasi2',
              sertifikasi3 = '$sertifikasi3',
              publikasi1 = '$publikasi1',
              publikasi2 = '$publikasi2',
              publikasi3 = '$publikasi3',
              proyek1 = '$proyek1',
              proyek2 = '$proyek2',
              proyek3 = '$proyek3'
              WHERE username = '$userid'";
              
    mysqli_query($koneksi, $query);
    header("Location: profil_guru.php?pesan=sukses");
    exit();
}
?>