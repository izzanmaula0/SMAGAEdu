<?php
require "koneksi.php";

// Path ke direktori knowledge
$knowledgePath = __DIR__ . '/knowledge';

// Daftar kategori
$categories = ['umum', 'fasilitas', 'kurikulum', 'ekskul', 'guru'];

// Fungsi untuk memproses dan menyimpan data JSON ke database
function processAndSaveJson($category, $jsonData, $koneksi) {
    global $kategoriMapped;
    
    foreach($jsonData as $key => $value) {
        if(is_array($value)) {
            // Handle array (bisa berupa list atau nested object)
            if(isset($value[0]) && is_scalar($value[0])) {
                // Array sederhana (list)
                $konten = implode(", ", $value);
                $query = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "sss", $category, $key, $konten);
                mysqli_stmt_execute($stmt);
            } else {
                // Nested array/object
                foreach($value as $subKey => $subValue) {
                    if(is_array($subValue)) {
                        // Array dalam array
                        $konten = implode(", ", $subValue);
                        $judul = $key . " - " . $subKey;
                        $query = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query);
                        mysqli_stmt_bind_param($stmt, "sss", $category, $judul, $konten);
                        mysqli_stmt_execute($stmt);
                    } else {
                        // Value biasa dalam nested object
                        $judul = $key;
                        $konten = $subKey . ": " . $subValue;
                        $query = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query);
                        mysqli_stmt_bind_param($stmt, "sss", $category, $judul, $konten);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
        } else {
            // Value sederhana (string, angka, dll)
            $query = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, NULL, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ss", $category, $value);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Proses migrasi untuk setiap kategori
foreach($categories as $category) {
    $filePath = $knowledgePath . '/' . $category . '.json';
    
    if(file_exists($filePath)) {
        $jsonContent = file_get_contents($filePath);
        $jsonData = json_decode($jsonContent, true);
        
        if($jsonData) {
            echo "Memproses kategori: $category<br>";
            processAndSaveJson($category, $jsonData, $koneksi);
            echo "Selesai memproses kategori: $category<br>";
        } else {
            echo "Error: Gagal parsing JSON untuk kategori $category<br>";
        }
    } else {
        echo "File untuk kategori $category tidak ditemukan<br>";
    }
}

echo "Migrasi data selesai!";
?>