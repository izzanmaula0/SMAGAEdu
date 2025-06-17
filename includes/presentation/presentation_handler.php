<?php
// Fungsi untuk memulai presentasi
function start_presentation($kelas_id, $presentation_id, $total_slides, $file_type)
{
    global $koneksi;

    // Debug
    error_log("Start presentation params: kelas_id=$kelas_id, presentation_id=$presentation_id, total_slides=$total_slides, file_type=$file_type");

    // Cek apakah ada presentasi yang sedang aktif
    $query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Update presentasi yang sedang aktif
        $query = "UPDATE presentasi_aktif SET 
                 file_path = ?, 
                 current_slide = 1, 
                 total_slides = ?, 
                 active = 1,
                 file_type = ?,
                 updated_at = NOW() 
                 WHERE kelas_id = ?";
        
        $stmt = mysqli_prepare($koneksi, $query);
        
        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($koneksi));
            return false;
        }
        
        // PENTING: Urutannya sesuai dengan urutan ? di query
        mysqli_stmt_bind_param($stmt, "siss", $presentation_id, $total_slides, $file_type, $kelas_id);
    } else {
        // Buat entry baru
        $query = "INSERT INTO presentasi_aktif 
                (kelas_id, file_path, current_slide, total_slides, active, file_type) 
                VALUES (?, ?, 1, ?, 1, ?)";
        
        $stmt = mysqli_prepare($koneksi, $query);
        
        if (!$stmt) {
            error_log("MySQL Prepare Error: " . mysqli_error($koneksi));
            return false;
        }
        
        // PENTING: Urutannya sesuai dengan urutan ? di query
        mysqli_stmt_bind_param($stmt, "ssis", $kelas_id, $presentation_id, $total_slides, $file_type);
    }

    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("SQL Error: " . mysqli_stmt_error($stmt));
        return false;
    }
    
    return true;
}

function update_presentation_slide($kelas_id, $current_slide)
{
    global $koneksi;

    $query = "UPDATE presentasi_aktif SET 
             current_slide = ?, 
             updated_at = NOW() 
             WHERE kelas_id = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "is", $current_slide, $kelas_id);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        error_log("SQL Error: " . mysqli_error($koneksi));
        return false;
    }
}

// Fungsi untuk mengakhiri presentasi
function end_presentation($kelas_id)
{
    global $koneksi;

    $query = "UPDATE presentasi_aktif SET 
             active = 0, 
             updated_at = NOW() 
             WHERE kelas_id = ? AND active = 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);

    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        error_log("SQL Error: " . mysqli_error($koneksi));
        return false;
    }
}


// Fungsi untuk cek status presentasi
function check_presentation_status($kelas_id)
{
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
function get_slide_url($presentation_id, $slide)
{
    global $koneksi;

    // Dalam implementasi nyata, Anda akan mengambil URL slide dari database
    // atau menghasilkan URL dinamis berdasarkan file presentasi

    // Contoh sederhana:
    return "presentation_slides/{$presentation_id}/slide_{$slide}.jpg";
}
