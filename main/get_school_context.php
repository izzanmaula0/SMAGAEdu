<?php
session_start();
require "koneksi.php";
header('Content-Type: application/json');

// Validasi akses
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fungsi getContextualKnowledge (sama persis dengan yang Anda miliki)
function getContextualKnowledge($query)
{
    // Path ke direktori knowledge
    $knowledgePath = __DIR__ . '/knowledge';
    
    // Buat direktori jika belum ada
    if (!is_dir($knowledgePath)) {
        mkdir($knowledgePath, 0755, true);
    }
    
    // Data default jika tidak ada file
    $defaultInfo = "SMP dan SMA Muhammadiyah Gatak adalah sekolah Islam terpadu yang berlokasi di Gatak, Sukoharjo.";
    
    // Daftar kategori dan keyword
    $categories = [
        'umum' => [''], // Selalu diambil
        'fasilitas' => ['fasilitas', 'gedung', 'ruang', 'kelas', 'lab', 'perpustakaan'],
        'kurikulum' => ['kurikulum', 'pelajaran', 'mata pelajaran', 'mapel', 'belajar'],
        'ekskul' => ['ekstrakurikuler', 'ekskul', 'kegiatan', 'lomba'],
        'guru' => ['guru', 'pengajar', 'staff', 'karyawan']
    ];
    
    // Tentukan kategori yang relevan
    $relevantCategories = ['umum']; // Selalu termasuk info umum
    
    foreach ($categories as $category => $keywords) {
        if ($category === 'umum') continue; // Skip umum karena sudah ditambahkan
        
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && stripos($query, $keyword) !== false) {
                $relevantCategories[] = $category;
                break;
            }
        }
    }
    
    // Kumpulkan data dari file JSON
    $contextData = '';
    
    foreach ($relevantCategories as $category) {
        $filePath = $knowledgePath . '/' . $category . '.json';
        
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $jsonData = json_decode($jsonContent, true);
            
            if ($jsonData) {
                $contextData .= "### " . strtoupper($category) . "\n";
                
                // Format data JSON menjadi teks yang mudah dibaca
                foreach ($jsonData as $key => $value) {
                    if (is_array($value)) {
                        $contextData .= "$key:\n";
                        
                        if (isset($value[0]) && is_scalar($value[0])) {
                            // Array sederhana
                            foreach ($value as $item) {
                                $contextData .= "- $item\n";
                            }
                        } else {
                            // Array asosiatif
                            foreach ($value as $subKey => $subValue) {
                                if (is_array($subValue)) {
                                    $contextData .= "  $subKey:\n";
                                    foreach ($subValue as $subItem) {
                                        $contextData .= "    - $subItem\n";
                                    }
                                } else {
                                    $contextData .= "  $subKey: $subValue\n";
                                }
                            }
                        }
                    } else {
                        $contextData .= "$key: $value\n";
                    }
                }
                
                $contextData .= "\n";
            }
        }
    }
    
    // Jika tidak ada data yang ditemukan, gunakan default
    if (empty($contextData)) {
        return $defaultInfo;
    }
    
    return "INFORMASI SEKOLAH:\n\n$contextData";
}

// Ambil data dari POST request
$userMessage = isset($_POST['query']) ? $_POST['query'] : '';

// Dapatkan konteks sekolah
$context = getContextualKnowledge($userMessage);

// Kirim respons JSON
echo json_encode([
    'status' => 'success',
    'context' => $context
]);
?>