<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guru_id = $_SESSION['userid'];
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $judul_tugas = mysqli_real_escape_string($koneksi, $_POST['judul_tugas']);
    $deskripsi_tugas = mysqli_real_escape_string($koneksi, $_POST['deskripsi_tugas']);
    $batas_tanggal = mysqli_real_escape_string($koneksi, $_POST['batas_tanggal']);
    $batas_jam = mysqli_real_escape_string($koneksi, $_POST['batas_jam']);
    $poin_tugas = isset($_POST['poin_tugas']) ? (int)$_POST['poin_tugas'] : 100; // Default 100 jika tidak diset
    
    $batas_waktu = date('Y-m-d H:i:s', strtotime("$batas_tanggal $batas_jam"));
    
    // Start transaction
    mysqli_begin_transaction($koneksi);
    
    try {
        // Insert postingan
        $query_insert_post = "INSERT INTO postingan_kelas (kelas_id, user_id, konten, jenis_postingan, created_at) 
                             VALUES (?, ?, ?, 'tugas', NOW())";
        $stmt = mysqli_prepare($koneksi, $query_insert_post);
        mysqli_stmt_bind_param($stmt, "sss", $kelas_id, $guru_id, $deskripsi_tugas);
        mysqli_stmt_execute($stmt);
        $postingan_id = mysqli_insert_id($koneksi);
        
        // Insert tugas
        $query_insert_tugas = "INSERT INTO tugas (postingan_id, judul, deskripsi, batas_waktu, poin_maksimal, created_at, status) 
                              VALUES (?, ?, ?, ?, ?, NOW(), 'active')";
        $stmt = mysqli_prepare($koneksi, $query_insert_tugas);
        mysqli_stmt_bind_param($stmt, "isssi", $postingan_id, $judul_tugas, $deskripsi_tugas, $batas_waktu, $poin_tugas);
        mysqli_stmt_execute($stmt);
        $tugas_id = mysqli_insert_id($koneksi);
        
        // Handle file uploads
        if (isset($_FILES['file_tugas']) && is_array($_FILES['file_tugas']['name'])) {
            $upload_dir = 'uploads/tugas/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            foreach ($_FILES['file_tugas']['name'] as $key => $name) {
                if ($_FILES['file_tugas']['error'][$key] == 0 && $name != '') {
                    $file_name = $name;
                    $file_tmp = $_FILES['file_tugas']['tmp_name'][$key];
                    $file_type = $_FILES['file_tugas']['type'][$key];
                    $file_size = $_FILES['file_tugas']['size'][$key];
                    
                    $file_name_new = "tugas_" . $tugas_id . "_" . uniqid() . "_" . $file_name;
                    $file_path = $upload_dir . $file_name_new;
                    
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $query_lampiran = "INSERT INTO lampiran_tugas (tugas_id, nama_file, path_file, tipe_file, ukuran_file) 
                                         VALUES (?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_lampiran);
                        mysqli_stmt_bind_param($stmt, "isssi", $tugas_id, $file_name, $file_path, $file_type, $file_size);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
        }
        
        mysqli_commit($koneksi);
        header("Location: kelas_guru.php?id=$kelas_id&pesan=tugas_berhasil");
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: kelas_guru.php?id=$kelas_id&error=gagal_tambah_tugas");
    }
    
} else {
    header("Location: beranda_guru.php");
}
?>