<?php
session_start();
require "koneksi.php";

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validasi input wajib
$required_fields = ['siswa_id', 'semester', 'tahun_ajaran'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        die("Error: Field $field harus diisi");
    }
}

// Ambil data dari POST dengan validasi
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$semester = filter_input(INPUT_POST, 'semester', FILTER_VALIDATE_INT);
$tahun_ajaran = filter_input(INPUT_POST, 'tahun_ajaran', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Perbaikan di sini
$guru_id = $_SESSION['userid'];

$profil_fields = [
    'nis' => FILTER_VALIDATE_INT,
    'nama' => FILTER_SANITIZE_STRING,
    'tahun_masuk' => FILTER_VALIDATE_INT,
    'no_hp' => FILTER_VALIDATE_INT,
    'alamat' => FILTER_SANITIZE_STRING
];

// Validasi data
if (!$siswa_id || !$semester || !$tahun_ajaran) {
    die("Error: Data tidak valid. Pastikan semua field diisi dengan benar");
}

// Konfigurasi database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi->autocommit(FALSE);

        // Update profil siswa terlebih dahulu
    $query_profil = "UPDATE siswa SET 
                     nis = ?, 
                     nama = ?, 
                     tahun_masuk = ?, 
                     no_hp = ?, 
                     alamat = ? 
                     WHERE id = ?";
                     
    $stmt_profil = $koneksi->prepare($query_profil);
    $stmt_profil->bind_param("isiiss", 
        $_POST['nis'],
        $_POST['nama'],
        $_POST['tahun_masuk'],
        $_POST['no_hp'],
        $_POST['alamat'],
        $siswa_id
    );
    $stmt_profil->execute();

    // Field yang diizinkan
    $allowed_fields = [
        'nilai_akademik', 'keaktifan', 'pemahaman',
        'kehadiran_ibadah', 'kualitas_ibadah', 'pemahaman_agama',
        'minat_bakat', 'prestasi', 'keaktifan_ekskul',
        'partisipasi_sosial', 'empati', 'kerja_sama',
        'kebersihan_diri', 'aktivitas_fisik', 'pola_makan',
        'kejujuran', 'tanggung_jawab', 'kedisiplinan'
    ];
    
    // Kumpulkan data
    $columns = [];
    $values = [];
    $types = '';
    
    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $value = $_POST[$field] === '' ? null : (int)$_POST[$field];
            $columns[] = $field;
            $values[] = $value;
            $types .= 'i'; // Tetap 'i' karena NULL bisa di-handle
        }
    }

    // Perbaikan query dan parameter
    $placeholders = str_repeat('?,', count($columns));
    $placeholders = rtrim($placeholders, ',');

    // This should be your query instead:
    $query = "INSERT INTO pg (
        siswa_id, 
        guru_id, 
        semester, 
        tahun_ajaran, 
        " . implode(", ", $columns) . "
    ) VALUES (
        ?, ?, ?, ?, " . $placeholders . "
    ) 
    ON DUPLICATE KEY UPDATE
        " . implode(", ", array_map(function($col) {
            return "$col = IF(VALUES($col) IS NOT NULL, VALUES($col), $col)";
        }, $columns));

    // Siapkan parameter
    $params = array_merge(
        [$siswa_id, $guru_id, $semester, $tahun_ajaran],
        $values
    );
    
    // Perbaikan type definition
    $types = "iiss" . $types; // i=siswa_id, i=semester, s=guru_id, s=tahun_ajaran

    // Validasi jumlah parameter
    if (count($params) !== strlen($types)) {
        throw new Exception("Jumlah parameter tidak sesuai dengan type definition");
    }

    $stmt = $koneksi->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $koneksi->commit();
    
    // Redirect
    $redirect_url = "raport_pg.php?siswa_id=$siswa_id&semester=$semester&tahun_ajaran=" . urlencode($tahun_ajaran);
    header("Location: $redirect_url&success=1");
    exit();

} catch (Exception $e) {
    $koneksi->rollback();
    die("Error: Gagal menyimpan data. " . $e->getMessage());
}
?>