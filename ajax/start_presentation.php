<?php
session_start();
require_once "../koneksi.php";

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Ambil parameter
$kelas_id = isset($_POST['kelas_id']) ? $_POST['kelas_id'] : '';
$file_path = isset($_POST['file_path']) ? $_POST['file_path'] : '';
$total_slides = isset($_POST['total_slides']) ? intval($_POST['total_slides']) : 1;
$file_type = isset($_POST['file_type']) ? $_POST['file_type'] : '';

// Validasi
if (empty($kelas_id) || empty($file_path)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing parameters'
    ]);
    exit;
}

// Jika file_type tidak ada, coba tentukan dari ekstensi file
if (empty($file_type)) {
    $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
}

// Akhiri presentasi yang sedang aktif
$end_query = "UPDATE presentasi_aktif SET active = 0 WHERE kelas_id = ? AND active = 1";
$stmt = mysqli_prepare($koneksi, $end_query);
mysqli_stmt_bind_param($stmt, "s", $kelas_id);
mysqli_stmt_execute($stmt);

// Periksa struktur tabel terlebih dahulu
$check_column_query = "SHOW COLUMNS FROM presentasi_aktif LIKE 'file_type'";
$column_result = mysqli_query($koneksi, $check_column_query);
$file_type_exists = mysqli_num_rows($column_result) > 0;

// Buat presentasi baru - sesuaikan dengan struktur tabel yang ada
if ($file_type_exists) {
    $insert_query = "INSERT INTO presentasi_aktif (kelas_id, file_path, total_slides, current_slide, active, file_type) 
                    VALUES (?, ?, ?, 1, 1, ?)";
    $stmt = mysqli_prepare($koneksi, $insert_query);
    mysqli_stmt_bind_param($stmt, "siss", $kelas_id, $file_path, $total_slides, $file_type);
} else {
    // Jika tidak ada kolom file_type
    $insert_query = "INSERT INTO presentasi_aktif (kelas_id, file_path, total_slides, current_slide, active) 
                    VALUES (?, ?, ?, 1, 1)";
    $stmt = mysqli_prepare($koneksi, $insert_query);
    mysqli_stmt_bind_param($stmt, "sis", $kelas_id, $file_path, $total_slides);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => 'Presentation started successfully'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($koneksi)
    ]);
}
?>