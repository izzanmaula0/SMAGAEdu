<?php
// Fungsi untuk memulai presentasi
function start_presentation($kelas_id, $presentation_id, $total_slides) {
    global $koneksi;
    
    // Cek apakah ada presentasi yang sedang aktif
    $query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id); 
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update presentasi yang sedang aktif
        $query = "UPDATE presentasi_aktif SET 
                 presentation_id = ?, 
                 current_slide = 1, 
                 total_slides = ?, 
                 active = 1, 
                 updated_at = NOW() 
                 WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sis", $presentation_id, $total_slides, $kelas_id);
    } else {
        // Buat entry baru
        $query = "INSERT INTO presentasi_aktif 
                 (kelas_id, presentation_id, current_slide, total_slides, active, created_at, updated_at) 
                 VALUES (?, ?, 1, ?, 1, NOW(), NOW())";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sis", $kelas_id, $presentation_id, $total_slides);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Fungsi untuk update slide
function update_presentation_slide($kelas_id, $presentation_id, $slide) {
    global $koneksi;
    
    $query = "UPDATE presentasi_aktif SET 
             current_slide = ?, 
             updated_at = NOW() 
             WHERE kelas_id = ? AND presentation_id = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "iss", $slide, $kelas_id, $presentation_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Fungsi untuk mengakhiri presentasi
function end_presentation($kelas_id, $presentation_id) {
    global $koneksi;
    
    $query = "UPDATE presentasi_aktif SET 
             active = 0, 
             updated_at = NOW() 
             WHERE kelas_id = ? AND presentation_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $kelas_id, $presentation_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Fungsi untuk cek status presentasi
function check_presentation_status($kelas_id) {
    global $koneksi;
    
    $query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return [
            'active' => false
        ];
    }
}

// Fungsi untuk mendapatkan URL slide
function get_slide_url($presentation_id, $slide) {
    global $koneksi;
    
    // Dalam implementasi nyata, Anda akan mengambil URL slide dari database
    // atau menghasilkan URL dinamis berdasarkan file presentasi
    
    // Contoh sederhana:
    return "presentation_slides/{$presentation_id}/slide_{$slide}.jpg";
}
?>