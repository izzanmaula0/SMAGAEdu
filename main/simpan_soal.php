<?php
session_start();
require "koneksi.php";
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ujian_id = $_POST['ujian_id'];
    $jenis_soal = $_POST['jenis_soal'];
    $pertanyaan = $_POST['pertanyaan'];

    function sanitizeHtml($html)
    {
        // Tag yang diizinkan: bold, italic, underline, paragraf, dll
        $allowedTags = '<b><i><u><strong><em><p><br><span>';
        return strip_tags($html, $allowedTags);
    }

    $pertanyaan = sanitizeHtml($_POST['pertanyaan']);

    // Fungsi untuk upload gambar
    function uploadImage($file, $prefix = '')
    {
        if (!isset($file) || $file['error'] != 0) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = $prefix . uniqid() . '.' . $ext;
            $upload_dir = 'uploads/' . ($prefix ? 'jawaban/' : 'soal/');

            // Buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                return $upload_path;
            }
        }
        return null;
    }

    // Handle image upload untuk soal
    $gambar_soal = null;
    if (isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] == 0) {
        $gambar_soal = uploadImage($_FILES['gambar_soal']);
    }

    // Handle image upload untuk jawaban
    $gambar_jawaban_a = null;
    $gambar_jawaban_b = null;
    $gambar_jawaban_c = null;
    $gambar_jawaban_d = null;

    if (isset($_FILES['gambar_jawaban_a']) && $_FILES['gambar_jawaban_a']['error'] == 0) {
        $gambar_jawaban_a = uploadImage($_FILES['gambar_jawaban_a'], 'a_');
    }
    if (isset($_FILES['gambar_jawaban_b']) && $_FILES['gambar_jawaban_b']['error'] == 0) {
        $gambar_jawaban_b = uploadImage($_FILES['gambar_jawaban_b'], 'b_');
    }
    if (isset($_FILES['gambar_jawaban_c']) && $_FILES['gambar_jawaban_c']['error'] == 0) {
        $gambar_jawaban_c = uploadImage($_FILES['gambar_jawaban_c'], 'c_');
    }
    if (isset($_FILES['gambar_jawaban_d']) && $_FILES['gambar_jawaban_d']['error'] == 0) {
        $gambar_jawaban_d = uploadImage($_FILES['gambar_jawaban_d'], 'd_');
    }

    if ($jenis_soal == 'pilihan_ganda') {
        $jawaban_a = $_POST['jawaban_a'];
        $jawaban_b = $_POST['jawaban_b'];
        $jawaban_c = $_POST['jawaban_c'];
        $jawaban_d = $_POST['jawaban_d'];
        $jawaban_benar = $_POST['jawaban_benar'];

        $query = "INSERT INTO bank_soal (ujian_id, jenis_soal, pertanyaan, gambar_soal, jawaban_a, jawaban_b, jawaban_c, jawaban_d, jawaban_benar, gambar_jawaban_a, gambar_jawaban_b, gambar_jawaban_c, gambar_jawaban_d) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "issssssssssss", $ujian_id, $jenis_soal, $pertanyaan, $gambar_soal, $jawaban_a, $jawaban_b, $jawaban_c, $jawaban_d, $jawaban_benar, $gambar_jawaban_a, $gambar_jawaban_b, $gambar_jawaban_c, $gambar_jawaban_d);
    } else {
        $query = "INSERT INTO bank_soal (ujian_id, jenis_soal, pertanyaan, gambar_soal) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "isss", $ujian_id, $jenis_soal, $pertanyaan, $gambar_soal);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
}
