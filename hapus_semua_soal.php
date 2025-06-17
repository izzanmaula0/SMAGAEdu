<?php
session_start();
require "koneksi.php";

// Memeriksa apakah pengguna sudah login dan memiliki hak akses
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit();
}

// Mengambil data dari request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['ujian_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID ujian tidak ditemukan']);
    exit();
}

$ujian_id = $data['ujian_id'];
$userid = $_SESSION['userid'];

// Verifikasi apakah ujian milik guru yang sedang login
$query_check = "SELECT id FROM ujian WHERE id = ? AND guru_id = ?";
$stmt_check = mysqli_prepare($koneksi, $query_check);
mysqli_stmt_bind_param($stmt_check, 'ss', $ujian_id, $userid);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Ujian tidak ditemukan atau Anda tidak memiliki akses']);
    exit();
}

// Menghapus semua deskripsi soal (soal_descriptions) terkait dengan ujian ini
$query_delete_desc = "DELETE FROM soal_descriptions WHERE ujian_id = ?";
$stmt_delete_desc = mysqli_prepare($koneksi, $query_delete_desc);
mysqli_stmt_bind_param($stmt_delete_desc, 's', $ujian_id);
$success_desc = mysqli_stmt_execute($stmt_delete_desc);

// Menghapus semua soal terkait dengan ujian ini
$query_delete = "DELETE FROM bank_soal WHERE ujian_id = ?";
$stmt_delete = mysqli_prepare($koneksi, $query_delete);
mysqli_stmt_bind_param($stmt_delete, 's', $ujian_id);
$success = mysqli_stmt_execute($stmt_delete);

if ($success) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Semua soal berhasil dihapus']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus soal: ' . mysqli_error($koneksi)]);
}
?>