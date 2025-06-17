<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $kelas_id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $guru_id = $_SESSION['userid'];
    
    mysqli_begin_transaction($koneksi);
    
    try {
        // 1. Hapus data ujian yang terkait dengan kelas
        // Tapi sebelum itu, hapus dulu soal-soal dari ujian tersebut
        $query_ujian = "SELECT id FROM ujian WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_ujian);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        $result_ujian = mysqli_stmt_get_result($stmt);
        
        while($ujian = mysqli_fetch_assoc($result_ujian)) {
            
            $query_hapus_jawaban = "DELETE FROM jawaban_ujian WHERE ujian_id = ?";
            $stmt_jawaban = mysqli_prepare($koneksi, $query_hapus_jawaban);
            mysqli_stmt_bind_param($stmt_jawaban, "i", $ujian['id']);
            mysqli_stmt_execute($stmt_jawaban);


            // Hapus soal dari setiap ujian
            $query_hapus_soal = "DELETE FROM bank_soal WHERE ujian_id = ?";
            $stmt_soal = mysqli_prepare($koneksi, $query_hapus_soal);
            mysqli_stmt_bind_param($stmt_soal, "i", $ujian['id']);
            mysqli_stmt_execute($stmt_soal);
        }
        
        // Setelah soal dihapus, hapus ujiannya
        $query_hapus_ujian = "DELETE FROM ujian WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_hapus_ujian);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);

        // 2. Hapus data postingan kelas
        // Tapi sebelumnya hapus dulu komentar dan likes dari postingan tersebut
        $query_postingan = "SELECT id FROM postingan_kelas WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_postingan);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        $result_postingan = mysqli_stmt_get_result($stmt);
        
        while($postingan = mysqli_fetch_assoc($result_postingan)) {
            // Hapus komentar
            $query_hapus_komentar = "DELETE FROM komentar_postingan WHERE postingan_id = ?";
            $stmt_komentar = mysqli_prepare($koneksi, $query_hapus_komentar);
            mysqli_stmt_bind_param($stmt_komentar, "i", $postingan['id']);
            mysqli_stmt_execute($stmt_komentar);
            
            // Hapus likes
            $query_hapus_likes = "DELETE FROM likes_postingan WHERE postingan_id = ?";
            $stmt_likes = mysqli_prepare($koneksi, $query_hapus_likes);
            mysqli_stmt_bind_param($stmt_likes, "i", $postingan['id']);
            mysqli_stmt_execute($stmt_likes);
            
            // Hapus lampiran
            $query_hapus_lampiran = "DELETE FROM lampiran_postingan WHERE postingan_id = ?";
            $stmt_lampiran = mysqli_prepare($koneksi, $query_hapus_lampiran);
            mysqli_stmt_bind_param($stmt_lampiran, "i", $postingan['id']);
            mysqli_stmt_execute($stmt_lampiran);
        }
        
        // Hapus postingan
        $query_hapus_postingan = "DELETE FROM postingan_kelas WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_hapus_postingan);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);

        // 3. Hapus catatan guru
        $query_hapus_catatan = "DELETE FROM catatan_guru WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_hapus_catatan);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);

        // 4. Hapus relasi kelas_siswa
        $query_hapus_relasi = "DELETE FROM kelas_siswa WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_hapus_relasi);
        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);

        // 5. Terakhir, hapus kelas
        $query_hapus_kelas = "DELETE FROM kelas WHERE id = ? AND guru_id = ?";
        $stmt = mysqli_prepare($koneksi, $query_hapus_kelas);
        mysqli_stmt_bind_param($stmt, "is", $kelas_id, $guru_id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($koneksi);
        header("Location: beranda_guru.php?pesan=kelas_berhasil_dihapus");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: beranda_guru.php?pesan=gagal_hapus_kelas&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: beranda_guru.php");
    exit();
}
?>