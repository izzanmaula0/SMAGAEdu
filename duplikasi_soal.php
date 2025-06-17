<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$soal_id = $data['soal_id'];

// Ambil data soal yang akan diduplikasi
$query = "SELECT * FROM bank_soal WHERE id = '$soal_id'";
$result = mysqli_query($koneksi, $query);
$soal = mysqli_fetch_assoc($result);

if (!$soal) {
    echo json_encode(['status' => 'error', 'message' => 'Soal tidak ditemukan']);
    exit();
}

// Untuk mendapatkan posisi soal, kita perlu tahu urutan soal
// Jika soal memiliki description_id, cari posisi terakhir dalam group tersebut
if ($soal['description_id']) {
    // Cari soal terakhir dalam group description yang sama
    $query_position = "SELECT MAX(id) as last_id FROM bank_soal 
                      WHERE ujian_id = '{$soal['ujian_id']}' 
                      AND description_id = '{$soal['description_id']}'";
    $result_position = mysqli_query($koneksi, $query_position);
    $position_data = mysqli_fetch_assoc($result_position);
    $after_id = $position_data['last_id'];
} else {
    // Jika tidak ada description_id, duplikasi setelah soal ini
    $after_id = $soal_id;
}

// Duplikasi soal
$pertanyaan = mysqli_real_escape_string($koneksi, $soal['pertanyaan'] . ' (Salinan)');
$jenis_soal = $soal['jenis_soal'];
$jawaban_a = mysqli_real_escape_string($koneksi, $soal['jawaban_a']);
$jawaban_b = mysqli_real_escape_string($koneksi, $soal['jawaban_b']);
$jawaban_c = mysqli_real_escape_string($koneksi, $soal['jawaban_c']);
$jawaban_d = mysqli_real_escape_string($koneksi, $soal['jawaban_d']);
$jawaban_benar = $soal['jawaban_benar'];
$ujian_id = $soal['ujian_id'];
$description_id = $soal['description_id'] ? "'{$soal['description_id']}'" : 'NULL';
$gambar_soal = $soal['gambar_soal'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_soal']) . "'" : 'NULL';
$gambar_jawaban_a = $soal['gambar_jawaban_a'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_jawaban_a']) . "'" : 'NULL';
$gambar_jawaban_b = $soal['gambar_jawaban_b'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_jawaban_b']) . "'" : 'NULL';
$gambar_jawaban_c = $soal['gambar_jawaban_c'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_jawaban_c']) . "'" : 'NULL';
$gambar_jawaban_d = $soal['gambar_jawaban_d'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_jawaban_d']) . "'" : 'NULL';

$query_insert = "INSERT INTO bank_soal (pertanyaan, jenis_soal, jawaban_a, jawaban_b, jawaban_c, jawaban_d, 
                jawaban_benar, ujian_id, description_id, gambar_soal, gambar_jawaban_a, gambar_jawaban_b, 
                gambar_jawaban_c, gambar_jawaban_d) 
                VALUES ('$pertanyaan', '$jenis_soal', '$jawaban_a', '$jawaban_b', '$jawaban_c', '$jawaban_d', 
                '$jawaban_benar', '$ujian_id', $description_id, $gambar_soal, $gambar_jawaban_a, 
                $gambar_jawaban_b, $gambar_jawaban_c, $gambar_jawaban_d)";

if (mysqli_query($koneksi, $query_insert)) {
    $new_id = mysqli_insert_id($koneksi);
    
    // Update urutan soal jika perlu (opsional, tergantung struktur database Anda)
    // Jika Anda memiliki kolom urutan, update di sini
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Soal berhasil diduplikasi',
        'new_id' => $new_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menduplikasi soal: ' . mysqli_error($koneksi)]);
}
?>