<?php
// filepath: update_kategori_siswa.php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update_kategori'])) {
    $kategori_baru = mysqli_real_escape_string($koneksi, $_POST['kategori_baru']);
    $siswa_ids = $_POST['siswa_ids'] ?? [];

    if (empty($siswa_ids)) {
        $_SESSION['error'] = "Silakan pilih minimal satu siswa!";
        header("Location: siswa_admin.php");
        exit();
    }

    if (empty($kategori_baru)) {
        $_SESSION['error'] = "Silakan pilih kategori baru!";
        header("Location: siswa_admin.php");
        exit();
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        $updated_count = 0;
        $siswa_names = [];

        foreach ($siswa_ids as $siswa_id) {
            $siswa_id = mysqli_real_escape_string($koneksi, $siswa_id);

            // Ambil nama siswa untuk notifikasi
            $query_nama = "SELECT nama FROM siswa WHERE id = '$siswa_id'";
            $result_nama = mysqli_query($koneksi, $query_nama);
            if ($siswa_data = mysqli_fetch_assoc($result_nama)) {
                $siswa_names[] = $siswa_data['nama'];
            }

            // Update kategori siswa
            $query_update = "UPDATE siswa SET kategori = '$kategori_baru' WHERE id = '$siswa_id'";
            if (mysqli_query($koneksi, $query_update)) {
                $updated_count++;
            }
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        $nama_list = implode(', ', array_slice($siswa_names, 0, 3));
        if (count($siswa_names) > 3) {
            $nama_list .= ' dan ' . (count($siswa_names) - 3) . ' siswa lainnya';
        }

        $_SESSION['success'] = "Berhasil mengubah kategori $updated_count siswa ($nama_list) menjadi $kategori_baru!";
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Akses tidak valid!";
}

header("Location: siswa_admin.php");
exit();
