<?php
session_start();
require "koneksi.php";

// Cek apakah user sudah login dan memiliki hak akses
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

// Function untuk validasi dan sanitasi input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $ujian_id = isset($_POST['ujian_id']) ? intval($_POST['ujian_id']) : 0;
    $judul = isset($_POST['judul']) ? sanitize_input($_POST['judul']) : '';
    $mata_pelajaran = isset($_POST['mata_pelajaran']) ? sanitize_input($_POST['mata_pelajaran']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? sanitize_input($_POST['deskripsi']) : '';
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : '';
    $durasi = isset($_POST['durasi']) ? intval($_POST['durasi']) : 0;

    // Debugging - simpan ke file log
    $log_file = fopen("edit_materi_log.txt", "a");
    fwrite($log_file, "POST Data for edit: " . print_r($_POST, true) . "\n\n");

    // Ambil materi dari form (jika ada)
    $materi = isset($_POST['materi']) && is_array($_POST['materi']) ? $_POST['materi'] : [];
    fwrite($log_file, "Materi before filter: " . print_r($materi, true) . "\n");

    // Filter materi kosong
    $materi = array_filter($materi, function ($item) {
        return !empty(trim($item));
    });
    fwrite($log_file, "Materi after filter: " . print_r($materi, true) . "\n");

    // Encode materi menjadi JSON
    $materi_json = json_encode($materi);
    fwrite($log_file, "Materi JSON: " . $materi_json . "\n\n");
    fclose($log_file);

    // Verifikasi data
    if (
        empty($ujian_id) || empty($judul) || empty($mata_pelajaran) || empty($tanggal_mulai) ||
        empty($tanggal_selesai) || empty($durasi)
    ) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Semua field wajib diisi.'
        ]);
        exit();
    }

    // Ambil ID guru dari sesi
    $userid = $_SESSION['userid'];
    // Ambil kelas_id dari request
    $kelas_id = isset($_POST['kelas_id']) ? intval($_POST['kelas_id']) : 0;

    // Verifikasi bahwa ujian milik guru tersebut
    $query_check = "SELECT * FROM ujian WHERE id = ? AND guru_id = ?";
    $stmt_check = mysqli_prepare($koneksi, $query_check);
    mysqli_stmt_bind_param($stmt_check, "is", $ujian_id, $userid);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ujian tidak ditemukan atau Anda tidak memiliki akses.'
        ]);
        exit();
    }

    // Cek apakah ada hasil ujian untuk ujian ini
    $query_cek_hasil = "SELECT COUNT(*) as jumlah FROM jawaban_ujian WHERE ujian_id = ?";
    $stmt_cek_hasil = mysqli_prepare($koneksi, $query_cek_hasil);
    mysqli_stmt_bind_param($stmt_cek_hasil, "i", $ujian_id);
    mysqli_stmt_execute($stmt_cek_hasil);
    $result_cek_hasil = mysqli_stmt_get_result($stmt_cek_hasil);
    $data_hasil = mysqli_fetch_assoc($result_cek_hasil);
    $ada_hasil_ujian = ($data_hasil['jumlah'] > 0);

    // Ambil kelas_id saat ini dari database
    $query_current = "SELECT kelas_id FROM ujian WHERE id = ?";
    $stmt_current = mysqli_prepare($koneksi, $query_current);
    mysqli_stmt_bind_param($stmt_current, "i", $ujian_id);
    mysqli_stmt_execute($stmt_current);
    $result_current = mysqli_stmt_get_result($stmt_current);
    $current_data = mysqli_fetch_assoc($result_current);
    $current_kelas_id = $current_data['kelas_id'];

    // Jika ada hasil ujian, pastikan kelas_id tidak berubah
    if ($ada_hasil_ujian && $kelas_id != $current_kelas_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tidak dapat mengubah kelas karena sudah ada siswa yang mengikuti ujian ini.'
        ]);
        exit();
    }

    // Format tanggal
    $tanggal_mulai_formatted = date('Y-m-d H:i:s', strtotime($tanggal_mulai));
    $tanggal_selesai_formatted = date('Y-m-d H:i:s', strtotime($tanggal_selesai));

    // Update data ujian
    $query_update = "UPDATE ujian SET 
    judul = ?,
    mata_pelajaran = ?,
    deskripsi = ?,
    tanggal_mulai = ?,
    tanggal_selesai = ?,
    durasi = ?,
    materi = ?,
    kelas_id = ?
    WHERE id = ? AND guru_id = ?";

    $stmt_update = mysqli_prepare($koneksi, $query_update);
    mysqli_stmt_bind_param(
        $stmt_update,
        "sssssisiis",
        $judul,
        $mata_pelajaran,
        $deskripsi,
        $tanggal_mulai_formatted,
        $tanggal_selesai_formatted,
        $durasi,
        $materi_json,
        $kelas_id,
        $ujian_id,
        $userid
    );

    $result_update = mysqli_stmt_execute($stmt_update);

    if ($result_update) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Identitas ujian berhasil diperbarui.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui identitas ujian: ' . mysqli_error($koneksi)
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak valid.'
    ]);
}
