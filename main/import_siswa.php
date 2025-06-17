<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Import library untuk membaca file Word
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

if (isset($_POST['import_siswa'])) {
    // Validasi file yang diupload
    if (!isset($_FILES['file_siswa']) || $_FILES['file_siswa']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Terjadi kesalahan saat upload file. Silakan coba lagi.";
        header("Location: siswa_admin.php");
        exit();
    }

    $file = $_FILES['file_siswa']['tmp_name'];
    $fileType = $_FILES['file_siswa']['type'];
    
    // Pastikan file yang diupload adalah file Word (.docx)
    if ($fileType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $_SESSION['error'] = "File harus berformat .docx (Word)";
        header("Location: siswa_admin.php");
        exit();
    }

    try {
        // Load file Word
        $phpWord = IOFactory::load($file);
        
        // Data siswa yang akan diimpor
        $siswa_data = [];
        
        // Loop melalui semua section dalam dokumen
        $sections = $phpWord->getSections();
        foreach ($sections as $section) {
            // Loop melalui semua elemen dalam section
            $elements = $section->getElements();
            foreach ($elements as $element) {
                // Cek apakah elemen adalah tabel
                if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                    // Akses rows dari tabel (API yang lebih umum dibandingkan countRows())
                    $rows = $element->getRows();
                    
                    // Skip row pertama (header)
                    $first = true;
                    
                    // Loop melalui semua row dalam tabel
                    foreach ($rows as $row) {
                        // Skip header row
                        if ($first) {
                            $first = false;
                            continue;
                        }
                        
                        // Akses cell dari row
                        $cells = $row->getCells();
                        
                        // Pastikan ada minimal 6 kolom (untuk field wajib)
                        if (count($cells) < 6) {
                            continue;
                        }
                        
                        // Variabel untuk menyimpan nilai dari setiap kolom
                        $username = '';
                        $password = '';
                        $nama = '';
                        $tingkat = '';
                        $tahun_masuk = '';
                        $nis = '';
                        $no_hp = '';
                        $alamat = '';
                        
                        // Ambil teks dari setiap cell dengan cara yang lebih aman
                        if (isset($cells[1]) && $cells[1]->getElements()) {
                            foreach ($cells[1]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $username .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[2]) && $cells[2]->getElements()) {
                            foreach ($cells[2]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $password .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[3]) && $cells[3]->getElements()) {
                            foreach ($cells[3]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $nama .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[4]) && $cells[4]->getElements()) {
                            foreach ($cells[4]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $tingkat .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[5]) && $cells[5]->getElements()) {
                            foreach ($cells[5]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $tahun_masuk .= $textElement->getText();
                                }
                            }
                        }
                        
                        // Field opsional
                        if (isset($cells[6]) && $cells[6]->getElements()) {
                            foreach ($cells[6]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $nis .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[7]) && $cells[7]->getElements()) {
                            foreach ($cells[7]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $no_hp .= $textElement->getText();
                                }
                            }
                        }
                        
                        if (isset($cells[8]) && $cells[8]->getElements()) {
                            foreach ($cells[8]->getElements() as $textElement) {
                                if (method_exists($textElement, 'getText')) {
                                    $alamat .= $textElement->getText();
                                }
                            }
                        }
                        
                        // Skip jika username atau nama kosong
                        if (empty($username) || empty($nama)) {
                            continue;
                        }
                        
                        // Tambahkan ke array data siswa
                        $siswa_data[] = [
                            'username' => $username,
                            'password' => $password,
                            'nama' => $nama,
                            'tingkat' => $tingkat,
                            'tahun_masuk' => $tahun_masuk,
                            'nis' => $nis,
                            'no_hp' => $no_hp,
                            'alamat' => $alamat
                        ];
                    }
                }
            }
        }
        
        // Import data ke database
        $success_count = 0;
        $error_count = 0;
        
        foreach ($siswa_data as $siswa) {
            $username = mysqli_real_escape_string($koneksi, $siswa['username']);
            $password = mysqli_real_escape_string($koneksi, $siswa['password']);
            $nama = mysqli_real_escape_string($koneksi, $siswa['nama']);
            $tingkat = mysqli_real_escape_string($koneksi, $siswa['tingkat']);
            $tahun_masuk = mysqli_real_escape_string($koneksi, $siswa['tahun_masuk']);
            $nis = mysqli_real_escape_string($koneksi, $siswa['nis']);
            $no_hp = mysqli_real_escape_string($koneksi, $siswa['no_hp']);
            $alamat = mysqli_real_escape_string($koneksi, $siswa['alamat']);
            
            // Cek apakah username sudah ada
            $check_query = "SELECT id FROM siswa WHERE username = '$username'";
            $check_result = mysqli_query($koneksi, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Username sudah ada, lakukan update
                $update_query = "UPDATE siswa SET 
                    password = '$password',
                    nama = '$nama',
                    tingkat = '$tingkat',
                    tahun_masuk = '$tahun_masuk',
                    nis = '$nis',
                    no_hp = '$no_hp',
                    alamat = '$alamat'
                    WHERE username = '$username'";
                
                if (mysqli_query($koneksi, $update_query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                // Username belum ada, lakukan insert
                $insert_query = "INSERT INTO siswa (username, password, nama, tingkat, tahun_masuk, nis, no_hp, alamat) 
                    VALUES ('$username', '$password', '$nama', '$tingkat', '$tahun_masuk', '$nis', '$no_hp', '$alamat')";
                
                if (mysqli_query($koneksi, $insert_query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $_SESSION['success'] = "Berhasil mengimpor $success_count data siswa" . ($error_count > 0 ? ", $error_count data gagal diimpor" : "");
        } else {
            $_SESSION['error'] = "Tidak ada data siswa yang berhasil diimpor. Periksa format file Anda.";
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    
    header("Location: siswa_admin.php");
    exit();
}

// Jika tidak ada POST request, redirect ke halaman admin siswa
header("Location: siswa_admin.php");
exit();
?>