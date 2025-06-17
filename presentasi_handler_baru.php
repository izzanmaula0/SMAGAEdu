<?php
// Fungsi untuk memulai presentasi baru
function start_presentation_baru($kelas_id, $file_path, $total_slides, $file_type = 'pdf', $zoom_scale = 1.5) {
    global $koneksi;
    
    // Tambahkan log untuk debugging
    error_log("Starting new presentation: class=$kelas_id, file=$file_path, slides=$total_slides, type=$file_type");
    
    // Akhiri presentasi aktif yang ada (jika ada)
    $end_query = "UPDATE presentasi_aktif SET active = 0 WHERE kelas_id = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $end_query);
    
    if (!$stmt) {
        error_log("Error preparing end query: " . mysqli_error($koneksi));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Error ending previous presentations: " . mysqli_error($koneksi));
        // Tetap lanjutkan karena ini bukan error fatal
    }
    
    mysqli_stmt_close($stmt);
    
    // Untuk keamanan, hapus juga presentasi aktif yang sudah lebih dari 24 jam
    $cleanup_query = "DELETE FROM presentasi_aktif WHERE updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    mysqli_query($koneksi, $cleanup_query);
    
    // Cek apakah sudah ada record untuk kelas_id ini
    $check_query = "SELECT id FROM presentasi_aktif WHERE kelas_id = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update record yang sudah ada
        mysqli_stmt_close($stmt);
        
        $update_query = "UPDATE presentasi_aktif SET 
                        file_path = ?, 
                        current_slide = 1,
                        total_slides = ?,
                        active = 1,
                        file_type = ?,
                        zoom_scale = ?,
                        updated_at = NOW()
                        WHERE kelas_id = ?";
                        
        $stmt = mysqli_prepare($koneksi, $update_query);
        
        if (!$stmt) {
            error_log("Error preparing update query: " . mysqli_error($koneksi));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sisds", $file_path, $total_slides, $file_type, $zoom_scale, $kelas_id);
    } else {
        // Insert new record
        mysqli_stmt_close($stmt);
        
        $insert_query = "INSERT INTO presentasi_aktif 
                        (kelas_id, file_path, current_slide, total_slides, active, file_type, zoom_scale) 
                        VALUES (?, ?, 1, ?, 1, ?, ?)";
        
        $stmt = mysqli_prepare($koneksi, $insert_query);
        
        if (!$stmt) {
            error_log("Error preparing insert query: " . mysqli_error($koneksi));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ssisd", $kelas_id, $file_path, $total_slides, $file_type, $zoom_scale);
    }
    
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Error inserting/updating presentation: " . mysqli_error($koneksi) . 
                 " (class=$kelas_id, file=$file_path, slides=$total_slides)");
        return false;
    }
    
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    error_log("Presentation started successfully. Rows affected: $affected");
    
    return true;
}

// Fungsi untuk memperbarui slide saat ini
function update_current_slide($kelas_id, $slide_number) {
    global $koneksi;
    
    $update_query = "UPDATE presentasi_aktif SET 
                    current_slide = ?,
                    updated_at = NOW()
                    WHERE kelas_id = ? AND active = 1";
                    
    $stmt = mysqli_prepare($koneksi, $update_query);
    
    if (!$stmt) {
        error_log("Error preparing update slide query: " . mysqli_error($koneksi));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "is", $slide_number, $kelas_id);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Error updating slide: " . mysqli_error($koneksi));
        return false;
    }
    
    $affected = mysqli_stmt_affected_rows($stmt);
    
    if ($affected == 0) {
        error_log("No active presentation found for class: $kelas_id");
        return false;
    }
    
    return true;
}

// Fungsi untuk mengakhiri presentasi
function end_presentation($kelas_id) {
    global $koneksi;
    
    $update_query = "UPDATE presentasi_aktif SET 
                    active = 0,
                    updated_at = NOW()
                    WHERE kelas_id = ? AND active = 1";
                    
    $stmt = mysqli_prepare($koneksi, $update_query);
    
    if (!$stmt) {
        error_log("Error preparing end presentation query: " . mysqli_error($koneksi));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Error ending presentation: " . mysqli_error($koneksi));
        return false;
    }
    
    return true;
}