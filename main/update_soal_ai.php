<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
// DEBUGGING - hapus setelah berhasil
error_log('Data received: ' . print_r($data, true));

$soal_id = $data['soal_id'];
$type = $data['type'];

// Escape semua content
$pertanyaan = isset($data['pertanyaan']) ? mysqli_real_escape_string($koneksi, $data['pertanyaan']) : null;
$jawaban_a = isset($data['jawaban_a']) ? mysqli_real_escape_string($koneksi, $data['jawaban_a']) : null;
$jawaban_b = isset($data['jawaban_b']) ? mysqli_real_escape_string($koneksi, $data['jawaban_b']) : null;
$jawaban_c = isset($data['jawaban_c']) ? mysqli_real_escape_string($koneksi, $data['jawaban_c']) : null;
$jawaban_d = isset($data['jawaban_d']) ? mysqli_real_escape_string($koneksi, $data['jawaban_d']) : null;

// Build query dinamis
$updates = [];
if ($pertanyaan !== null) $updates[] = "pertanyaan = '$pertanyaan'";
if ($jawaban_a !== null) $updates[] = "jawaban_a = '$jawaban_a'";
if ($jawaban_b !== null) $updates[] = "jawaban_b = '$jawaban_b'";
if ($jawaban_c !== null) $updates[] = "jawaban_c = '$jawaban_c'";
if ($jawaban_d !== null) $updates[] = "jawaban_d = '$jawaban_d'";

if (empty($updates)) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data untuk diupdate']);
    exit();
}

$query = "UPDATE bank_soal SET " . implode(', ', $updates) . " WHERE id = '$soal_id'";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Soal berhasil diperbarui']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui soal: ' . mysqli_error($koneksi)]);
}
?>