<?php
session_start();
require "koneksi.php";

// Pastikan user yang login adalah guru
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    $response = [
        'status' => 'error',
        'message' => 'Unauthorized'
    ];
    echo json_encode($response);
    exit();
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);
$soal_id = $data['soal_id'];
$option = $data['option'];

// Kolom dalam database
$column = "gambar_jawaban_" . $option;

// Ambil data gambar sebelum dihapus
$query = "SELECT $column FROM bank_soal WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $soal_id);
$stmt->execute();
$result = $stmt->get_result();
$soal = $result->fetch_assoc();

if (!$soal) {
    $response = [
        'status' => 'error',
        'message' => 'Soal tidak ditemukan'
    ];
    echo json_encode($response);
    exit();
}

// Hapus file gambar jika ada
if (!empty($soal[$column])) {
    $file_path = $soal[$column];
    // Hapus file (pastikan path file sesuai)
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Update database: hapus referensi gambar
$query = "UPDATE bank_soal SET $column = NULL WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $soal_id);

if ($stmt->execute()) {
    $response = [
        'status' => 'success',
        'message' => 'Gambar berhasil dihapus'
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Gagal menghapus gambar dari database'
    ];
}

echo json_encode($response);
exit();
?>