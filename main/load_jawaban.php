<?php
session_start();
require "koneksi.php";

// Ambil data dari GET
$ujian_id = $_GET['ujian_id'] ?? null;

if(!$ujian_id) {
    echo json_encode(['success' => false, 'error' => 'ID ujian tidak diberikan']);
    exit();
}

// Ambil ID siswa dari username
$query_siswa = "SELECT id FROM siswa WHERE username = ?";
$stmt_siswa = $koneksi->prepare($query_siswa);
$stmt_siswa->bind_param("s", $_SESSION['userid']);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa = $result_siswa->fetch_assoc();
$siswa_id = $siswa['id'];

// Ambil jawaban sementara dari database
$query_jawaban = "SELECT soal_id, jawaban FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ?";
$stmt_jawaban = $koneksi->prepare($query_jawaban);
$stmt_jawaban->bind_param("ii", $ujian_id, $siswa_id);
$stmt_jawaban->execute();
$result_jawaban = $stmt_jawaban->get_result();

// Simpan jawaban dari database ke array
$jawaban_array = [];
$soal_order = $_SESSION['soal_order_' . $ujian_id] ?? [];

// Mapping soal_id ke indeks di array soal_order
$soal_id_to_index = [];
foreach($soal_order as $index => $soal_id) {
    $soal_id_to_index[$soal_id] = $index;
}

// Ambil jawaban dan konversi ke format yang dibutuhkan JavaScript
while($row = $result_jawaban->fetch_assoc()) {
    $soal_id = $row['soal_id'];
    if(isset($soal_id_to_index[$soal_id])) {
        $jawaban_array[$soal_id_to_index[$soal_id]] = $row['jawaban'];
    }
}

echo json_encode(['success' => true, 'answers' => $jawaban_array]);
?>