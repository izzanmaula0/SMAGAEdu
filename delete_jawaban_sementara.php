<?php
session_start();
require "koneksi.php";

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data dari POST biasa
    $ujian_id = $_POST['ujian_id'] ?? null;
    $siswa_id = $_POST['siswa_id'] ?? null;
} else {
    // Data dari sendBeacon (raw JSON)
    $data = json_decode(file_get_contents('php://input'), true);
    $ujian_id = $data['ujian_id'] ?? null;
    $siswa_id = $data['siswa_id'] ?? null;
}

// Validasi data
if (!$ujian_id || !$siswa_id) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit();
}

try {
    // Hapus semua jawaban sementara untuk ujian dan siswa ini
    $query = "DELETE FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ii", $ujian_id, $siswa_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>