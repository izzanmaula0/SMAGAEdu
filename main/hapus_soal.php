<?php
session_start();
require "koneksi.php";

// Cek autentikasi
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $soal_id = $data['soal_id'];
    $userid = $_SESSION['userid'];

    try {
        // Cek apakah soal ini milik ujian yang dibuat oleh guru yang sedang login
        $query_check = "SELECT bs.id 
                       FROM bank_soal bs 
                       JOIN ujian u ON bs.ujian_id = u.id 
                       WHERE bs.id = ? AND u.guru_id = ?";
        
        $stmt_check = mysqli_prepare($koneksi, $query_check);
        mysqli_stmt_bind_param($stmt_check, "is", $soal_id, $userid);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) == 0) {
            throw new Exception('Anda tidak memiliki akses untuk menghapus soal ini');
        }

        // Hapus soal
        $query_delete = "DELETE FROM bank_soal WHERE id = ?";
        $stmt_delete = mysqli_prepare($koneksi, $query_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $soal_id);

        if (!mysqli_stmt_execute($stmt_delete)) {
            throw new Exception('Gagal menghapus soal');
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Soal berhasil dihapus'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?>