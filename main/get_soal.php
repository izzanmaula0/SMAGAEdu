<?php
session_start();
require "koneksi.php";
header('Content-Type: application/json; charset=utf-8'); // Pastikan output JSON dengan charset UTF-8

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if(isset($_GET['id'])) {
    $soal_id = $_GET['id'];
    $userid = $_SESSION['userid'];

    try {
        // Cek apakah soal ini milik ujian yang dibuat oleh guru yang sedang login
        // Tambahkan juga informasi deskripsi soal jika ada
        $query = "SELECT bs.*, sd.content as description_content, sd.title as description_title  
                 FROM bank_soal bs 
                 JOIN ujian u ON bs.ujian_id = u.id 
                 LEFT JOIN soal_descriptions sd ON bs.description_id = sd.id
                 WHERE bs.id = ? AND u.guru_id = ?";
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "is", $soal_id, $userid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if($row = mysqli_fetch_assoc($result)) {
            // Ambil informasi ujian
            $ujian_query = "SELECT judul, mata_pelajaran FROM ujian WHERE id = ?";
            $ujian_stmt = mysqli_prepare($koneksi, $ujian_query);
            mysqli_stmt_bind_param($ujian_stmt, "i", $row['ujian_id']);
            mysqli_stmt_execute($ujian_stmt);
            $ujian_result = mysqli_stmt_get_result($ujian_stmt);
            $ujian_data = mysqli_fetch_assoc($ujian_result);
            
            // Tambahkan informasi ujian ke data soal
            $row['ujian_judul'] = $ujian_data['judul'] ?? 'Ujian tidak ditemukan';
            $row['mata_pelajaran'] = $ujian_data['mata_pelajaran'] ?? '';
            
            echo json_encode([
                'status' => 'success',
                'soal' => $row
            ]);
        } else {
            throw new Exception('Soal tidak ditemukan');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID soal tidak diberikan'
    ]);
}
?>