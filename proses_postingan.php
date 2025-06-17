<?php
session_start();
require "koneksi.php";
require "kelas.php";

$kelas = new Kelas($koneksi);

if(isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'create_post':
            $id_kelas = $_POST['id_kelas'];
            $konten = $_POST['konten'];
            $files = $_FILES['files'] ?? [];
            
            if($kelas->createPost($id_kelas, $konten, $files)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            break;
            
        case 'add_comment':
            $id_postingan = $_POST['id_postingan'];
            $konten = $_POST['konten'];
            $id_user = $_SESSION['userid'];
            $tipe_user = $_SESSION['level'];
            
            $query = "INSERT INTO komentar_postingan (id_postingan, id_user, tipe_user, konten) VALUES (?, ?, ?, ?)";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("iiss", $id_postingan, $id_user, $tipe_user, $konten);
            
            if($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            break;
            
        case 'delete_post':
            $id_postingan = $_POST['id_postingan'];
            
            // Hapus file terkait postingan
            $query = "SELECT * FROM file_postingan WHERE id_postingan = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("i", $id_postingan);
            $stmt->execute();
            $files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach($files as $file) {
                unlink("uploads/" . $file['nama_file']);
            }
            
            // Hapus komentar terkait postingan
            $query = "DELETE FROM komentar_postingan WHERE id_postingan = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("i", $id_postingan);
            $stmt->execute();
            
            // Hapus like terkait postingan
            $query = "DELETE FROM like_postingan WHERE id_postingan = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("i", $id_postingan);
            $stmt->execute();
            
            // Hapus postingan
            $query = "DELETE FROM postingan_kelas WHERE id_postingan = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("i", $id_postingan);
            
            if($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>