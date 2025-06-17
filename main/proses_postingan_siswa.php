<?php
session_start();
require "koneksi.php";

// Check if user is logged in and is a student
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['userid'];
    $kelas_id = $_POST['kelas_id'];
    $konten = $_POST['konten'];
    
    // Validate content is not empty
    if (empty(trim($konten))) {
        header("Location: kelas.php?id=$kelas_id&error=empty_content");
        exit();
    }
    
    // Verify the class is public and student has access
    $query_check = "SELECT k.* FROM kelas k 
                    JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                    JOIN siswa s ON ks.siswa_id = s.id 
                    WHERE s.username = ? AND k.id = ? AND k.is_public = 1";
    
    $stmt = mysqli_prepare($koneksi, $query_check);
    mysqli_stmt_bind_param($stmt, "ss", $userid, $kelas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: kelas.php?id=$kelas_id&error=not_allowed");
        exit();
    }
    
    // Insert the post
    $query_insert = "INSERT INTO postingan_kelas (kelas_id, user_id, konten, jenis_postingan, user_type) 
                     VALUES (?, ?, ?, 'postingan', 'siswa')";
    
    $stmt = mysqli_prepare($koneksi, $query_insert);
    mysqli_stmt_bind_param($stmt, "sss", $kelas_id, $userid, $konten);
    
    if (mysqli_stmt_execute($stmt)) {
        $postingan_id = mysqli_insert_id($koneksi);
        
        // Handle file uploads if any
        if (isset($_FILES['lampiran']) && !empty($_FILES['lampiran']['name'][0])) {
            $upload_dir = 'uploads/lampiran/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $allowed_types = [
                'image/jpeg', 'image/png', 'image/gif',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4', 'video/webm', 'video/ogg'
            ];
            
            // Batasan ukuran video (100MB)
            $max_video_size = 100 * 1024 * 1024;
            
            foreach ($_FILES['lampiran']['name'] as $i => $filename) {
                if ($_FILES['lampiran']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['lampiran']['tmp_name'][$i];
                    $name = basename($filename);
                    $file_type = $_FILES['lampiran']['type'][$i];
                    $file_size = $_FILES['lampiran']['size'][$i];
                    
                    // Validasi tipe file
                    if (!in_array($file_type, $allowed_types)) {
                        continue; // Lewati file dengan tipe tidak didukung
                    }
                    
                    // Validasi ukuran video
                    if (strpos($file_type, 'video/') === 0 && $file_size > $max_video_size) {
                        continue; // Lewati file video yang terlalu besar
                    }
                    
                    // Generate unique filename
                    $new_filename = uniqid() . '_' . $name;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $destination)) {
                        // Insert into lampiran_postingan table
                        $query_lampiran = "INSERT INTO lampiran_postingan (postingan_id, nama_file, path_file, tipe_file) 
                                         VALUES (?, ?, ?, ?)";
                        
                        $stmt_lampiran = mysqli_prepare($koneksi, $query_lampiran);
                        mysqli_stmt_bind_param(
                            $stmt_lampiran, 
                            "isss", 
                            $postingan_id, 
                            $name, 
                            $destination, 
                            $file_type
                        );
                        mysqli_stmt_execute($stmt_lampiran);
                    }
                }
            }
        }
        
        header("Location: kelas.php?id=$kelas_id&success=posted");
    } else {
        header("Location: kelas.php?id=$kelas_id&error=db_error");
    }
} else {
    header("Location: beranda.php");
}
?>