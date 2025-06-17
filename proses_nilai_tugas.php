<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pengumpulan_id = mysqli_real_escape_string($koneksi, $_POST['pengumpulan_id']);
    $tugas_id = mysqli_real_escape_string($koneksi, $_POST['tugas_id']);
    $nilai = (int)$_POST['nilai'];
    $komentar = isset($_POST['komentar']) ? mysqli_real_escape_string($koneksi, $_POST['komentar']) : null;
    
    // Atur timezone Indonesia (WIB)
    date_default_timezone_set('Asia/Jakarta');
    
    // Tambahkan waktu penilaian
    $waktu_penilaian = date('Y-m-d H:i:s');
    
    // Validasi nilai
    $query_tugas = "SELECT poin_maksimal FROM tugas WHERE id = '$tugas_id'";
    $result_tugas = mysqli_query($koneksi, $query_tugas);
    $poin_maksimal = mysqli_fetch_assoc($result_tugas)['poin_maksimal'];
    
    if ($nilai < 1 || $nilai > $poin_maksimal) {
        header("Location: detail_tugas.php?id=$tugas_id&error=nilai_invalid");
        exit();
    }
    
    // Update nilai dengan waktu penilaian
    $query_update = "UPDATE pengumpulan_tugas SET 
                    nilai = '$nilai', 
                    komentar_guru = " . ($komentar ? "'$komentar'" : "NULL") . ",
                    tanggal_penilaian = '$waktu_penilaian'  
                    WHERE id = '$pengumpulan_id'";
                    
    if (mysqli_query($koneksi, $query_update)) {
        header("Location: detail_tugas.php?id=$tugas_id&success=nilai_tersimpan");
    } else {
        header("Location: detail_tugas.php?id=$tugas_id&error=gagal_simpan");
    }
} else {
    header("Location: beranda_guru.php");
}
?>