<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id = $_SESSION['userid'];
    $tugas_id = mysqli_real_escape_string($koneksi, $_POST['tugas_id']);
    
    // Validasi tugas dan batas waktu
    $query_tugas = "SELECT t.*, p.kelas_id 
                    FROM tugas t 
                    JOIN postingan_kelas p ON t.postingan_id = p.id 
                    WHERE t.id = '$tugas_id'";
    $result_tugas = mysqli_query($koneksi, $query_tugas);
    
    if (mysqli_num_rows($result_tugas) == 0) {
        header("Location: kelas_siswa.php?error=tugas_tidak_ditemukan");
        exit();
    }

    $data_tugas = mysqli_fetch_assoc($result_tugas);
    $kelas_id = $data_tugas['kelas_id'];

    // Validasi apakah siswa terdaftar di kelas ini
    $query_kelas = "SELECT * FROM kelas_siswa WHERE siswa_id = (SELECT id FROM siswa WHERE username = '$siswa_id') AND kelas_id = '$kelas_id'";
    $result_kelas = mysqli_query($koneksi, $query_kelas);
    if (mysqli_num_rows($result_kelas) == 0) {
        header("Location: beranda.php?error=akses_ditolak");
        exit();
    }

    // Cek apakah sudah melewati batas waktu
    $batas_waktu = new DateTime($data_tugas['batas_waktu']);
    $sekarang = new DateTime();
    if ($sekarang > $batas_waktu) {
        header("Location: kelas_siswa.php?id=$kelas_id&error=tugas_sudah_ditutup");
        exit();
    }

    // Cek apakah sudah pernah mengumpulkan
    $query_cek_pengumpulan = "SELECT * FROM pengumpulan_tugas WHERE tugas_id = '$tugas_id' AND siswa_id = '$siswa_id'";
    $result_cek = mysqli_query($koneksi, $query_cek_pengumpulan);
    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: kelas_siswa.php?id=$kelas_id&error=sudah_mengumpulkan");
        exit();
    }
    
    // Validasi file (minimal 1 file)
    if (!isset($_FILES['file_tugas']) || empty($_FILES['file_tugas']['name'][0])) {
        header("Location: kelas_siswa.php?id=$kelas_id&error=file_tidak_valid");
        exit();
    }

    // Buat direktori untuk upload jika belum ada
    $upload_dir = 'uploads/pengumpulan_tugas/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Simpan data pengumpulan utama
    $pesan_siswa = isset($_POST['pesan_siswa']) ? mysqli_real_escape_string($koneksi, $_POST['pesan_siswa']) : null;
    
    // Buat entri di tabel pengumpulan_tugas
    $query_simpan = "INSERT INTO pengumpulan_tugas 
                    (tugas_id, siswa_id, waktu_pengumpulan, pesan_siswa) 
                    VALUES ('$tugas_id', '$siswa_id', NOW(), '$pesan_siswa')";
    
    if (mysqli_query($koneksi, $query_simpan)) {
        // Ambil ID pengumpulan yang baru dibuat
        $pengumpulan_id = mysqli_insert_id($koneksi);
        
        // Proses setiap file yang diupload
        $upload_success = true;
        $file_errors = [];
        
        // Buat tabel file_pengumpulan_tugas jika belum ada
        $query_create_table = "CREATE TABLE IF NOT EXISTS file_pengumpulan_tugas (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pengumpulan_id INT(11) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            nama_file VARCHAR(255) NOT NULL,
            tipe_file VARCHAR(100) NOT NULL,
            ukuran_file INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (pengumpulan_id) REFERENCES pengumpulan_tugas(id) ON DELETE CASCADE
        )";
        
        mysqli_query($koneksi, $query_create_table);
        
        // Loop melalui setiap file
        $file_count = count($_FILES['file_tugas']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['file_tugas']['error'][$i] != 0) {
                $upload_success = false;
                $file_errors[] = "Error pada file " . $_FILES['file_tugas']['name'][$i];
                continue;
            }
            
            $file_name = $_FILES['file_tugas']['name'][$i];
            $file_tmp = $_FILES['file_tugas']['tmp_name'][$i];
            $file_type = $_FILES['file_tugas']['type'][$i];
            $file_size = $_FILES['file_tugas']['size'][$i];
            
            // Validasi ukuran file (maksimal 10MB)
            $max_size = 10 * 1024 * 1024; // 10MB dalam bytes
            if ($file_size > $max_size) {
                $upload_success = false;
                $file_errors[] = "File {$file_name} terlalu besar (maks. 10MB)";
                continue;
            }

            // Validasi tipe file
            $allowed_types = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png'
            ];
            
            if (!in_array($file_type, $allowed_types)) {
                $upload_success = false;
                $file_errors[] = "Tipe file {$file_name} tidak didukung";
                continue;
            }
            
            // Generate nama file unik
            $file_name_new = "tugas_{$tugas_id}_siswa_{$siswa_id}_" . uniqid() . "_" . $file_name;
            $file_path = $upload_dir . $file_name_new;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Simpan informasi file ke database
                $query_file = "INSERT INTO file_pengumpulan_tugas 
                              (pengumpulan_id, file_path, nama_file, tipe_file, ukuran_file) 
                              VALUES ('$pengumpulan_id', '$file_path', '$file_name', '$file_type', '$file_size')";
                
                if (!mysqli_query($koneksi, $query_file)) {
                    $upload_success = false;
                    $file_errors[] = "Gagal menyimpan informasi file {$file_name} ke database";
                }
            } else {
                $upload_success = false;
                $file_errors[] = "Gagal mengupload file {$file_name}";
            }
        }
        
        // Jika ada error pada satu atau lebih file
        if (!$upload_success) {
            // Hapus pengumpulan jika semua file gagal
            if (count($file_errors) == $file_count) {
                mysqli_query($koneksi, "DELETE FROM pengumpulan_tugas WHERE id = '$pengumpulan_id'");
                header("Location: kelas.php?id=$kelas_id&error=" . urlencode(implode(", ", $file_errors)));
            } else {
                // Tetap lanjut jika beberapa file berhasil
                header("Location: kelas.php?id=$kelas_id&warning=" . urlencode("Beberapa file gagal: " . implode(", ", $file_errors)));
            }
        } else {
            header("Location: kelas.php?id=$kelas_id&success=tugas_berhasil_dikumpulkan");
        }
    } else {
        header("Location: kelas.php?id=$kelas_id&error=gagal_menyimpan_data");
    }
    
    exit();
}

// Jika bukan POST request
header("Location: beranda.php");
exit();
?>