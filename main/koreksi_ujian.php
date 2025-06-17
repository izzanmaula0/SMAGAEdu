<?php
session_start();
require "koneksi.php";

// Pastikan hanya guru yang bisa akses
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

$ujian_id = $_GET['ujian_id'];
$siswa_id = $_GET['siswa_id'];

// Ambil soal-soal ujian
$query_soal = "SELECT * FROM bank_soal WHERE ujian_id = ?";
$stmt_soal = $koneksi->prepare($query_soal);
$stmt_soal->bind_param("i", $ujian_id);
$stmt_soal->execute();
$result_soal = $stmt_soal->get_result();

// Ambil jawaban siswa
$query_jawaban = "SELECT * FROM jawaban_ujian WHERE ujian_id = ? AND siswa_id = ?";
$stmt_jawaban = $koneksi->prepare($query_jawaban);
$stmt_jawaban->bind_param("ii", $ujian_id, $siswa_id);
$stmt_jawaban->execute();
$result_jawaban = $stmt_jawaban->get_result();

// Hitung skor
$total_soal = $result_soal->num_rows;
$skor_benar = 0;

$result_soal->data_seek(0);
$result_jawaban->data_seek(0);

while($soal = $result_soal->fetch_assoc()) {
    $jawaban = $result_jawaban->fetch_assoc();
    
    // Hanya untuk pilihan ganda
    if($soal['jenis_soal'] == 'pilihan_ganda') {
        if($jawaban['jawaban'] == $soal['jawaban_benar']) {
            $skor_benar++;
        }
    }
}

// Hitung persentase
$skor_akhir = ($skor_benar / $total_soal) * 100;

// Update skor di database
$query_update = "UPDATE jawaban_ujian SET skor = ? WHERE ujian_id = ? AND siswa_id = ?";
$stmt_update = $koneksi->prepare($query_update);
$stmt_update->bind_param("dii", $skor_akhir, $ujian_id, $siswa_id);
$stmt_update->execute();

// Redirect ke halaman detail hasil
header("Location: detail_hasil_ujian.php?ujian_id=$ujian_id");
exit();
?>