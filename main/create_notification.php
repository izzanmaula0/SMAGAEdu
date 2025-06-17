<?php


// File untuk membuat notifikasi
function createNotification($koneksi, $penerima_id, $jenis, $postingan_id, $pelaku_id, $kelas_id) {
    // Cek apakah sudah ada notifikasi serupa yang belum dibaca
    $query = "SELECT id, jumlah FROM notifikasi 
              WHERE penerima_id = ? AND jenis = ? AND postingan_id = ? AND sudah_dibaca = 0";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $penerima_id, $jenis, $postingan_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update jumlah notifikasi yang ada
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $jumlah = $row['jumlah'] + 1;
        
        $update_query = "UPDATE notifikasi SET jumlah = ?, waktu = CURRENT_TIMESTAMP, pelaku_id = ? 
                        WHERE id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "isi", $jumlah, $pelaku_id, $id);
        mysqli_stmt_execute($update_stmt);
        
        return $id;
    } else {
        // Buat notifikasi baru
        $insert_query = "INSERT INTO notifikasi (penerima_id, jenis, postingan_id, pelaku_id, kelas_id) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "ssssi", $penerima_id, $jenis, $postingan_id, $pelaku_id, $kelas_id);
        mysqli_stmt_execute($insert_stmt);
        
        return mysqli_insert_id($koneksi);
    }
}
?>