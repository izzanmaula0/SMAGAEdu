<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Tambahkan ini untuk mengatasi CORS
error_reporting(E_ALL);
ini_set('display_errors', 0);

require "koneksi.php";

try {
    if (!isset($_GET['id'])) {
        throw new Exception('No ID provided');
    }

    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Query untuk mendapatkan data pengumpulan tugas
    $query = "SELECT 
        p.id,
        p.siswa_id,
        p.waktu_pengumpulan,
        p.nilai,
        p.komentar_guru,
        p.pesan_siswa,
        s.nama as nama_siswa
    FROM pengumpulan_tugas p
    JOIN siswa s ON p.siswa_id = s.username
    WHERE p.id = '$id'";

    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        throw new Exception(mysqli_error($koneksi));
    }

    if ($data = mysqli_fetch_assoc($result)) {
        // Query untuk mengambil semua file terkait dengan pengumpulan
        $query_files = "SELECT * FROM file_pengumpulan_tugas WHERE pengumpulan_id = '$id'";
        $result_files = mysqli_query($koneksi, $query_files);
        
        if (!$result_files) {
            // Jika tabel belum ada, coba gunakan metode lama (backward compatibility)
            $files = [];
            if (!empty($data['file_path'])) {
                $file_name = basename($data['file_path']);
                
                // Cek apakah file_path adalah Google Drive URL
                if (strpos($data['file_path'], 'drive.google.com') !== false) {
                    $file_url = $data['file_path']; // Gunakan URL Google Drive langsung
                } else {
                    // Buat URL lengkap untuk file lokal
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                    $file_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $data['file_path'];
                }
                
                $files[] = [
                    'name' => $file_name,
                    'url' => $file_url,
                    'type' => $data['tipe_file'] ?? 'unknown',
                    'size' => $data['ukuran_file'] ?? 0
                ];
            }
        } else {
            // Gunakan metode baru - multiple files dari tabel file_pengumpulan_tugas
            $files = [];
            while ($file = mysqli_fetch_assoc($result_files)) {
                $file_name = basename($file['file_path']);
                
                // Cek apakah file_path adalah Google Drive URL
                if (strpos($file['file_path'], 'drive.google.com') !== false) {
                    $file_url = $file['file_path']; // Gunakan URL Google Drive langsung
                } else {
                    // Buat URL lengkap untuk file lokal
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                    $file_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $file['file_path'];
                }
                
                $files[] = [
                    'name' => $file['nama_file'],
                    'url' => $file_url,
                    'type' => $file['tipe_file'],
                    'size' => $file['ukuran_file']
                ];
            }
        }
        
        $response = [
            'success' => true,
            'nilai' => $data['nilai'],
            'komentar' => $data['komentar_guru'],
            'waktu_pengumpulan' => $data['waktu_pengumpulan'],
            'pesan' => $data['pesan_siswa'],
            'files' => $files
        ];
        
        echo json_encode($response);
    } else {
        throw new Exception('No data found for ID: ' . $id);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>