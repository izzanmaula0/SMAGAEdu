<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $guru_id = $_SESSION['userid'];
    $mata_pelajaran = mysqli_real_escape_string($koneksi, $data['mata_pelajaran']);
    $tanggal_ujian = mysqli_real_escape_string($koneksi, $data['tanggal_ujian']);
    $waktu_mulai = mysqli_real_escape_string($koneksi, $data['waktu_mulai']);
    $waktu_selesai = mysqli_real_escape_string($koneksi, $data['waktu_selesai']);
    
    // Gabungkan tanggal dengan waktu
    $tanggal_mulai = $tanggal_ujian . ' ' . $waktu_mulai;
    $tanggal_selesai = $tanggal_ujian . ' ' . $waktu_selesai;

    // Hitung durasi dalam menit
    $mulai = strtotime($tanggal_mulai);
    $selesai = strtotime($tanggal_selesai);
    $durasi = ($selesai - $mulai) / 60;

    try {
        // Simpan data ujian
        $query = "INSERT INTO ujian (judul, guru_id, mata_pelajaran, tanggal_mulai, tanggal_selesai, durasi, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = mysqli_prepare($koneksi, $query);
        $judul = "Ujian " . $mata_pelajaran; // Bisa disesuaikan
        mysqli_stmt_bind_param($stmt, "sssssi", $judul, $guru_id, $mata_pelajaran, $tanggal_mulai, $tanggal_selesai, $durasi);

        if (mysqli_stmt_execute($stmt)) {
            $ujian_id = mysqli_insert_id($koneksi);
            
            // Simpan ujian_id ke session untuk digunakan saat menambah soal
            $_SESSION['current_ujian_id'] = $ujian_id;
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Ujian berhasil dibuat',
                'ujian_id' => $ujian_id
            ]);
        } else {
            throw new Exception(mysqli_error($koneksi));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal membuat ujian: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>