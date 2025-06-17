<?php
session_start();
require "koneksi.php";


// Debugging - cek status session
error_log('SESSION data: ' . print_r($_SESSION, true));

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    error_log('Auth failed: userid=' . (isset($_SESSION['userid']) ? $_SESSION['userid'] : 'not set') . 
              ', level=' . (isset($_SESSION['level']) ? $_SESSION['level'] : 'not set'));
    header("Location: index.php");
    exit();
}

if (isset($_POST['promote_siswa'])) {
    $tingkat_asal = mysqli_real_escape_string($koneksi, $_POST['tingkat_asal']);
    $tingkat_tujuan = mysqli_real_escape_string($koneksi, $_POST['tingkat_tujuan']);
    $siswa_ids = isset($_POST['siswa_ids']) ? $_POST['siswa_ids'] : [];
    $hapus_dari_kelas_lama = isset($_POST['hapus_dari_kelas_lama']);
    $tahun_lulus = isset($_POST['tahun_lulus']) ? mysqli_real_escape_string($koneksi, $_POST['tahun_lulus']) : date('Y');

    // Validasi data
    if (empty($tingkat_tujuan) || empty($siswa_ids)) {
        $_SESSION['error'] = "Silakan pilih tingkat tujuan dan minimal satu siswa!";
        header("Location: manajemen_siswa.php");
        exit();
    }

    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);

    try {
        foreach ($siswa_ids as $siswa_id) {
            $siswa_id = mysqli_real_escape_string($koneksi, $siswa_id);

            // Jika tujuan adalah "lulus", pindahkan ke tabel alumni
            if ($tingkat_tujuan === 'lulus') {
                // Ambil data siswa untuk dipindahkan ke alumni
                $query_siswa = "SELECT * FROM siswa WHERE id = '$siswa_id'";
                $result_siswa = mysqli_query($koneksi, $query_siswa);
                $siswa_data = mysqli_fetch_assoc($result_siswa);

                if ($siswa_data) {
                    // Masukkan ke tabel alumni
                    $insert_alumni = "INSERT INTO alumni (
                        siswa_id, nama, username, tahun_masuk, tahun_lulus, 
                        nis, foto_profil, alamat, no_hp
                    ) VALUES (
                        '{$siswa_data['id']}', 
                        '{$siswa_data['nama']}', 
                        '{$siswa_data['username']}', 
                        '{$siswa_data['tahun_masuk']}', 
                        '$tahun_lulus', 
                        '{$siswa_data['nis']}', 
                        '{$siswa_data['foto_profil']}', 
                        '{$siswa_data['alamat']}', 
                        '{$siswa_data['no_hp']}'
                    )";
                    mysqli_query($koneksi, $insert_alumni);

                    // Hapus siswa dari semua kelas (opsional)
                    $hapus_semua_kelas = "DELETE FROM kelas_siswa WHERE siswa_id = '$siswa_id'";
                    mysqli_query($koneksi, $hapus_semua_kelas);
                }
            }

            // Update tingkat siswa
            if ($tingkat_tujuan === 'lulus') {
                // Set tingkat ke NULL dan status menjadi alumni
                $update_tingkat = "UPDATE siswa SET tingkat = NULL, status = 'alumni' 
                      WHERE id = '$siswa_id'";
            } else {
                // Update tingkat siswa seperti biasa
                $update_tingkat = "UPDATE siswa SET tingkat = '$tingkat_tujuan' 
                      WHERE id = '$siswa_id'";
            }
            mysqli_query($koneksi, $update_tingkat);

            // Jika opsi hapus dari kelas lama dipilih dan bukan promosi ke lulus
            if ($hapus_dari_kelas_lama && $tingkat_tujuan !== 'lulus') {
                // Hapus siswa dari kelas-kelas di tingkat lama
                $query_hapus = "DELETE FROM kelas_siswa 
                              WHERE siswa_id = '$siswa_id' 
                              AND kelas_id IN (
                                  SELECT id FROM kelas 
                                  WHERE tingkat = '$tingkat_asal'
                              )";
                mysqli_query($koneksi, $query_hapus);
            }
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        if ($tingkat_tujuan === 'lulus') {
            $_SESSION['success'] = "Siswa berhasil dipromosikan menjadi alumni!";
        } else {
            $_SESSION['success'] = "Promosi tingkat siswa berhasil dilakukan!";
        }

        header("Location: siswa_admin.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($koneksi);

        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: siswa_admin.php");
        exit();
    }
}

// Redirect jika tidak ada aksi
header("Location: siswa_admin.php");
exit();
