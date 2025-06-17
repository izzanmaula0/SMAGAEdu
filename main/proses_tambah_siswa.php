<?php
require "koneksi.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['siswa_ids']) && isset($_POST['kelas_id'])) {
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $siswa_ids = json_decode($_POST['siswa_ids']);
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        // Siapkan statement untuk insert
        $query = "INSERT INTO kelas_siswa (kelas_id, siswa_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        
        $success = true;
        foreach ($siswa_ids as $siswa_id) {
            // Cek apakah siswa sudah ada di kelas
            $check_query = "SELECT id FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?";
            $check_stmt = mysqli_prepare($koneksi, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ii", $kelas_id, $siswa_id);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($result) == 0) {
                // Jika siswa belum ada di kelas, tambahkan
                mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $siswa_id);
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false;
                    break;
                }
            }
        }
        
        if ($success) {
            mysqli_commit($koneksi);
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Gagal menambahkan beberapa siswa");
        }
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
}
?>