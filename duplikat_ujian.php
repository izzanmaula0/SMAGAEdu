<?php
session_start();
require "koneksi.php";

// Pastikan user yang login adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Pastikan ID ujian tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ujian_guru.php");
    exit();
}

$ujian_id = $_GET['id'];
$guru_id = $_SESSION['userid'];

// Periksa apakah ujian ini memang milik guru yang login
$query_cek_ujian = "SELECT * FROM ujian WHERE id = '$ujian_id' AND guru_id = '$guru_id'";
$result_cek_ujian = mysqli_query($koneksi, $query_cek_ujian);

if (mysqli_num_rows($result_cek_ujian) == 0) {
    // Ujian tidak ditemukan atau bukan milik guru ini
    header("Location: ujian_guru.php");
    exit();
}

// // Periksa apakah ada siswa yang sudah mengikuti ujian ini
// $query_cek_siswa = "SELECT COUNT(*) as total FROM jawaban_ujian WHERE ujian_id = '$ujian_id'";
// $result_cek_siswa = mysqli_query($koneksi, $query_cek_siswa);
// $data_siswa = mysqli_fetch_assoc($result_cek_siswa);

// if ($data_siswa['total'] > 0) {
//     // Ada siswa yang sudah mengikuti ujian ini
//     $_SESSION['pesan'] = "duplicate_error";
//     header("Location: ujian_guru.php");
//     exit();
// }

// Dapatkan data ujian asli
$ujian = mysqli_fetch_assoc($result_cek_ujian);

// Buat nama baru dengan menambahkan " - copy" di akhir judul
$judul_baru = $ujian['judul'] . " - copy";

// Duplikasi data ujian ke database
$query_duplikat = "INSERT INTO ujian (judul, deskripsi, kelas_id, guru_id, tanggal_mulai, tanggal_selesai, durasi, status, created_at) 
                  VALUES (
                      '" . mysqli_real_escape_string($koneksi, $judul_baru) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['deskripsi']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['kelas_id']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['guru_id']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['tanggal_mulai']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['tanggal_selesai']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['durasi']) . "', 
                      '" . mysqli_real_escape_string($koneksi, $ujian['status']) . "', 
                      NOW()
                  )";

if (!mysqli_query($koneksi, $query_duplikat)) {
    $_SESSION['pesan'] = "error";
    header("Location: ujian_guru.php");
    exit();
}

// Ambil ID ujian baru
$ujian_baru_id = mysqli_insert_id($koneksi);

// Duplikasi soal-soal ujian dari bank_soal
$query_soal = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id'";
$result_soal = mysqli_query($koneksi, $query_soal);

while ($soal = mysqli_fetch_assoc($result_soal)) {
    // Duplikasi soal
    $query_duplikat_soal = "INSERT INTO bank_soal (
                                ujian_id, 
                                jenis_soal, 
                                pertanyaan, 
                                gambar_soal, 
                                jawaban_a, 
                                jawaban_b, 
                                jawaban_c, 
                                jawaban_d, 
                                jawaban_benar, 
                                created_at
                            ) VALUES (
                                '$ujian_baru_id', 
                                '" . mysqli_real_escape_string($koneksi, $soal['jenis_soal']) . "', 
                                '" . mysqli_real_escape_string($koneksi, $soal['pertanyaan']) . "', 
                                " . ($soal['gambar_soal'] ? "'" . mysqli_real_escape_string($koneksi, $soal['gambar_soal']) . "'" : "NULL") . ", 
                                " . ($soal['jawaban_a'] ? "'" . mysqli_real_escape_string($koneksi, $soal['jawaban_a']) . "'" : "NULL") . ", 
                                " . ($soal['jawaban_b'] ? "'" . mysqli_real_escape_string($koneksi, $soal['jawaban_b']) . "'" : "NULL") . ", 
                                " . ($soal['jawaban_c'] ? "'" . mysqli_real_escape_string($koneksi, $soal['jawaban_c']) . "'" : "NULL") . ", 
                                " . ($soal['jawaban_d'] ? "'" . mysqli_real_escape_string($koneksi, $soal['jawaban_d']) . "'" : "NULL") . ", 
                                " . ($soal['jawaban_benar'] ? "'" . mysqli_real_escape_string($koneksi, $soal['jawaban_benar']) . "'" : "NULL") . ", 
                                NOW()
                            )";
    
    mysqli_query($koneksi, $query_duplikat_soal);
}

// Set pesan sukses dan redirect
$_SESSION['pesan'] = "duplicate_success";
header("Location: ujian_guru.php");
exit();
?>