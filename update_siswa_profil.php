<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id = $_POST['siswa_id'];
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $query = "UPDATE siswa SET 
              nis = ?, 
              nama = ?, 
              tahun_masuk = ?, 
              no_hp = ?, 
              alamat = ? 
              WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "issiis", $nis, $nama, $tahun_masuk, $no_hp, $alamat, $siswa_id);
    
    if(mysqli_stmt_execute($stmt)) {
        header("Location: raport_pg.php?siswa_id=$siswa_id&success=profile_updated");
    } else {
        header("Location: raport_pg.php?siswa_id=$siswa_id&error=update_failed");
    }
    exit();
}
?>