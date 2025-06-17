<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $soal_id = $_POST['soal_id'];
    $pertanyaan = $_POST['pertanyaan'];
    
    // Fungsi untuk upload gambar
    function uploadImage($file, $prefix = '') {
        if(!isset($file) || $file['error'] != 0) {
            return null;
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = $prefix . uniqid() . '.' . $ext;
            $upload_dir = 'uploads/' . ($prefix ? 'jawaban/' : 'soal/');
            
            // Buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                return $upload_path;
            }
        }
        return null;
    }
    
    // Ambil data soal yang ada
    $query = "SELECT * FROM bank_soal WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $soal_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if(!$row) {
        die(json_encode(['status' => 'error', 'message' => 'Soal tidak ditemukan']));
    }
    
    // Handle image upload untuk soal
    $gambar_soal = $row['gambar_soal'];
    if(isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] == 0) {
        // Delete old image if exists
        if($row['gambar_soal'] && file_exists($row['gambar_soal'])) {
            unlink($row['gambar_soal']);
        }
        $gambar_soal = uploadImage($_FILES['gambar_soal']);
    }
    
    // Handle image upload untuk jawaban
    $gambar_jawaban_a = $row['gambar_jawaban_a'];
    $gambar_jawaban_b = $row['gambar_jawaban_b'];
    $gambar_jawaban_c = $row['gambar_jawaban_c'];
    $gambar_jawaban_d = $row['gambar_jawaban_d'];
    
    if(isset($_FILES['gambar_jawaban_a']) && $_FILES['gambar_jawaban_a']['error'] == 0) {
        // Delete old image if exists
        if($row['gambar_jawaban_a'] && file_exists($row['gambar_jawaban_a'])) {
            unlink($row['gambar_jawaban_a']);
        }
        $gambar_jawaban_a = uploadImage($_FILES['gambar_jawaban_a'], 'a_');
    }
    
    if(isset($_FILES['gambar_jawaban_b']) && $_FILES['gambar_jawaban_b']['error'] == 0) {
        // Delete old image if exists
        if($row['gambar_jawaban_b'] && file_exists($row['gambar_jawaban_b'])) {
            unlink($row['gambar_jawaban_b']);
        }
        $gambar_jawaban_b = uploadImage($_FILES['gambar_jawaban_b'], 'b_');
    }
    
    if(isset($_FILES['gambar_jawaban_c']) && $_FILES['gambar_jawaban_c']['error'] == 0) {
        // Delete old image if exists
        if($row['gambar_jawaban_c'] && file_exists($row['gambar_jawaban_c'])) {
            unlink($row['gambar_jawaban_c']);
        }
        $gambar_jawaban_c = uploadImage($_FILES['gambar_jawaban_c'], 'c_');
    }
    
    if(isset($_FILES['gambar_jawaban_d']) && $_FILES['gambar_jawaban_d']['error'] == 0) {
        // Delete old image if exists
        if($row['gambar_jawaban_d'] && file_exists($row['gambar_jawaban_d'])) {
            unlink($row['gambar_jawaban_d']);
        }
        $gambar_jawaban_d = uploadImage($_FILES['gambar_jawaban_d'], 'd_');
    }

    if($_POST['jenis_soal'] == 'pilihan_ganda') {
        $query = "UPDATE bank_soal SET 
                  pertanyaan=?, 
                  gambar_soal=?, 
                  jawaban_a=?, 
                  jawaban_b=?, 
                  jawaban_c=?, 
                  jawaban_d=?, 
                  jawaban_benar=?,
                  gambar_jawaban_a=?,
                  gambar_jawaban_b=?,
                  gambar_jawaban_c=?,
                  gambar_jawaban_d=?
                  WHERE id=?";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param(
            $stmt, 
            "sssssssssssi", 
            $pertanyaan, 
            $gambar_soal, 
            $_POST['jawaban_a'], 
            $_POST['jawaban_b'], 
            $_POST['jawaban_c'], 
            $_POST['jawaban_d'], 
            $_POST['jawaban_benar'],
            $gambar_jawaban_a,
            $gambar_jawaban_b,
            $gambar_jawaban_c,
            $gambar_jawaban_d,
            $soal_id
        );
    } else {
        $query = "UPDATE bank_soal SET pertanyaan=?, gambar_soal=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $pertanyaan, $gambar_soal, $soal_id);
    }
    
    if(mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
}
?>