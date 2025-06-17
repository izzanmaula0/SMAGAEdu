<?php
session_start();
require "koneksi.php";
require "create_notification.php";


if(!isset($_SESSION['userid'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postingan_id = mysqli_real_escape_string($koneksi, $_POST['postingan_id']);
    $konten = mysqli_real_escape_string($koneksi, $_POST['konten']);
    $user_id = $_SESSION['userid'];

    $query = "INSERT INTO komentar_postingan (postingan_id, user_id, konten) 
              VALUES (?, ?, ?)";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "iss", $postingan_id, $user_id, $konten);
    
    if(mysqli_stmt_execute($stmt)) {
        $komentar_id = mysqli_insert_id($koneksi);
        
        // Get post owner and kelas_id for notification
        $query_post_info = "SELECT p.user_id, p.kelas_id FROM postingan_kelas p WHERE p.id = ?";
        $stmt_post = mysqli_prepare($koneksi, $query_post_info);
        mysqli_stmt_bind_param($stmt_post, "i", $postingan_id);
        mysqli_stmt_execute($stmt_post);
        $post_result = mysqli_stmt_get_result($stmt_post);
        
        if ($post_info = mysqli_fetch_assoc($post_result)) {
            $post_owner = $post_info['user_id'];
            $kelas_id = $post_info['kelas_id'];
            
            // Only create notification if the commenter is not the post owner
            if ($user_id != $post_owner) {
                // Create notification
                createNotification($koneksi, $post_owner, 'komentar', $postingan_id, $user_id, $kelas_id);
            }
        }
        
        $query_komentar = "SELECT k.*,
            g.namaLengkap as nama_guru, g.foto_profil as foto_guru,
            s.nama as nama_siswa, s.foto_profil as foto_siswa,
            CASE 
                WHEN g.username IS NOT NULL THEN 'guru'
                ELSE 'siswa'
            END as user_type
            FROM komentar_postingan k
            LEFT JOIN guru g ON k.user_id = g.username 
            LEFT JOIN siswa s ON k.user_id = s.username
            WHERE k.id = ?";

        $stmt = mysqli_prepare($koneksi, $query_komentar);
        mysqli_stmt_bind_param($stmt, "i", $komentar_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $komentar = mysqli_fetch_assoc($result);

        // Set foto profil berdasarkan tipe user
        $komentar['foto_profil'] = $komentar['user_type'] == 'guru' ? 
            ($komentar['foto_guru'] ? 'uploads/profil/'.$komentar['foto_guru'] : 'assets/pp.png') :
            ($komentar['foto_siswa'] ? 'uploads/profil/'.$komentar['foto_siswa'] : 'assets/pp.png');
            
        $komentar['nama_user'] = $komentar['user_type'] == 'guru' ? 
            $komentar['nama_guru'] : $komentar['nama_siswa'];

        echo json_encode([
            'status' => 'success',
            'komentar' => $komentar
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan komentar'
        ]);
    }
}
?>