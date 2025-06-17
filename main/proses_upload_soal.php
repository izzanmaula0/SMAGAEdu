<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ujian_id = $_POST['ujian_id'];
    
    // Validasi tipe file
    $allowed_types = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File harus berupa dokumen Word (.docx)']);
        exit();
    }

    // Buat direktori jika belum ada
    $upload_dir = 'uploads/soal/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate nama file unik
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Simpan informasi file ke database
        $query = "INSERT INTO file_soal (ujian_id, nama_file, path_file) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "iss", $ujian_id, $file['name'], $file_path);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'File berhasil diupload',
                'data' => [
                    'filename' => $file['name'],
                    'path' => $file_path
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data file']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>