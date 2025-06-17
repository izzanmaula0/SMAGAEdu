<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $konten = mysqli_real_escape_string($koneksi, $_POST['konten']);
    $guru_id = $_SESSION['userid'];

    // Cek kepemilikan kelas
    $query_check = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '$guru_id'";
    $result_check = mysqli_query($koneksi, $query_check);

    if(mysqli_num_rows($result_check) > 0) {
        $file_lampiran = null;

        // Handle file upload jika ada
        if(isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] == 0) {
            $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
            $filename = $_FILES['file_lampiran']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if(in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/catatan/' . $new_filename;

                if(!is_dir('uploads/catatan')) {
                    mkdir('uploads/catatan', 0777, true);
                }

                if(move_uploaded_file($_FILES['file_lampiran']['tmp_name'], $upload_path)) {
                    $file_lampiran = $upload_path;
                }
            }
        }

        // Insert catatan ke database
        $query_insert = "INSERT INTO catatan_guru (kelas_id, guru_id, judul, konten, file_lampiran) 
                        VALUES ('$kelas_id', '$guru_id', '$judul', '$konten', " . 
                        ($file_lampiran ? "'$file_lampiran'" : "NULL") . ")";

        if(mysqli_query($koneksi, $query_insert)) {
            header("Location: kelas_guru.php?id=" . $kelas_id . "&success=catatan_added");
        } else {
            header("Location: kelas_guru.php?id=" . $kelas_id . "&error=insert_failed");
        }
    } else {
        header("Location: beranda_guru.php");
    }
} else {
    header("Location: beranda_guru.php");
}
?>