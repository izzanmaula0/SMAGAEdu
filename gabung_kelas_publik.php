<?php
session_start();
require "koneksi.php";

// Check if user is logged in as student
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Check if class ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID kelas tidak valid.";
    header("Location: beranda.php");
    exit();
}

$kelas_id = $_GET['id'];
$userid = $_SESSION['userid'];

// Get student ID
$query_student = "SELECT id FROM siswa WHERE username = ?";
$stmt_student = mysqli_prepare($koneksi, $query_student);
mysqli_stmt_bind_param($stmt_student, "s", $userid);
mysqli_stmt_execute($stmt_student);
$result_student = mysqli_stmt_get_result($stmt_student);
$student = mysqli_fetch_assoc($result_student);

if (!$student) {
    $_SESSION['error'] = "Data siswa tidak ditemukan.";
    header("Location: beranda.php");
    exit();
}

$student_id = $student['id'];

// Verify that the class exists and is public
$query_check_kelas = "SELECT * FROM kelas WHERE id = ? AND is_public = 1";
$stmt_check_kelas = mysqli_prepare($koneksi, $query_check_kelas);
mysqli_stmt_bind_param($stmt_check_kelas, "i", $kelas_id);
mysqli_stmt_execute($stmt_check_kelas);
$result_check_kelas = mysqli_stmt_get_result($stmt_check_kelas);

if (mysqli_num_rows($result_check_kelas) == 0) {
    $_SESSION['error'] = "Kelas tidak ditemukan atau bukan kelas umum.";
    header("Location: beranda.php");
    exit();
}

// Check if student is already enrolled
$query_check_enrolled = "SELECT * FROM kelas_siswa 
                         WHERE kelas_id = ? AND siswa_id = ?";
$stmt_check_enrolled = mysqli_prepare($koneksi, $query_check_enrolled);
mysqli_stmt_bind_param($stmt_check_enrolled, "ii", $kelas_id, $student_id);
mysqli_stmt_execute($stmt_check_enrolled);
$result_check_enrolled = mysqli_stmt_get_result($stmt_check_enrolled);

if (mysqli_num_rows($result_check_enrolled) > 0) {
    // Student is already enrolled, just make sure it's not archived
    $enrolled = mysqli_fetch_assoc($result_check_enrolled);
    if ($enrolled['is_archived'] == 1) {
        // Unarchive the enrollment
        $query_update = "UPDATE kelas_siswa SET is_archived = 0 
                        WHERE kelas_id = ? AND siswa_id = ?";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $kelas_id, $student_id);
        mysqli_stmt_execute($stmt_update);
        
        $_SESSION['success'] = "Berhasil bergabung dengan kelas.";
    } else {
        $_SESSION['info'] = "Anda sudah terdaftar di kelas ini.";
    }
} else {
    // Enroll the student
    $query_enroll = "INSERT INTO kelas_siswa (kelas_id, siswa_id, is_archived) 
                    VALUES (?, ?, 0)";
    $stmt_enroll = mysqli_prepare($koneksi, $query_enroll);
    mysqli_stmt_bind_param($stmt_enroll, "ii", $kelas_id, $student_id);
    
    if (mysqli_stmt_execute($stmt_enroll)) {
        $_SESSION['success'] = "Berhasil bergabung dengan kelas.";
    } else {
        $_SESSION['error'] = "Gagal bergabung dengan kelas: " . mysqli_error($koneksi);
    }
}

header("Location: beranda.php");
exit();
?>