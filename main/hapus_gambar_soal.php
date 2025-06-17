<?php
session_start();
require "koneksi.php";

// Periksa apakah user sudah login dan adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Ambil data JSON yang dikirim
$data = json_decode(file_get_contents('php://input'), true);
$soal_id = $data['soal_id'];
$userid = $_SESSION['userid'];

// Validasi soal milik guru tersebut
$query = "SELECT bs.*, u.guru_id 
          FROM bank_soal bs 
          INNER JOIN ujian u ON bs.ujian_id = u.id 
          WHERE bs.id = ? AND u.guru_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "is", $soal_id, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Soal tidak ditemukan']);
    exit();
}

$soal = mysqli_fetch_assoc($result);

// Hapus file gambar jika ada
if (!empty($soal['gambar_soal'])) {
    $file_path = __DIR__ . $soal['gambar_soal'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Update database untuk menghapus referensi gambar
$query_update = "UPDATE bank_soal SET gambar_soal = NULL WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query_update);
mysqli_stmt_bind_param($stmt, "i", $soal_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Gambar berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus gambar']);
}

mysqli_close($koneksi);
?>