<?php
session_start();
require "koneksi.php";

// Pastikan hanya guru yang bisa mengakses
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Ambil query dari parameter POST atau GET
$query = isset($_POST['query']) ? $_POST['query'] : (isset($_GET['query']) ? $_GET['query'] : '');

// Log query yang diterima
error_log("get_context.php dipanggil dengan query: " . $query);

// Panggil fungsi untuk mendapatkan konteks
function getContextualKnowledge($query)
{
    global $koneksi;

    // Log untuk debugging
    error_log("getContextualKnowledge dipanggil dengan query: " . $query);

    // Jika query kosong, gunakan default
    if (empty(trim($query))) {
        error_log("Query kosong, menggunakan query default");
        $query = "informasi sekolah"; // Query default
    }

    // Data default jika tidak ada informasi yang sesuai
    $defaultInfo = "SMP dan SMA Muhammadiyah Gatak adalah sekolah Islam terpadu yang berlokasi di Gatak, Sukoharjo.";

    // Ambil semua kategori dari database
    $allCategories = [];
    $categoryKeywords = [];

    $categorySql = "SELECT DISTINCT kategori FROM informasi_sekolah";
    $categoryResult = mysqli_query($koneksi, $categorySql);

    if (!$categoryResult) {
        error_log("Error query kategori: " . mysqli_error($koneksi));
        return $defaultInfo;
    }

    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $allCategories[] = $row['kategori'];
    }

    error_log("Semua kategori: " . implode(", ", $allCategories));

    // Ambil kata kunci untuk setiap kategori
    $keywordSql = "SELECT kategori, judul, konten FROM informasi_sekolah WHERE judul = 'keywords'";
    $keywordResult = mysqli_query($koneksi, $keywordSql);

    if (!$keywordResult) {
        error_log("Error query keywords: " . mysqli_error($koneksi));
    } else {
        while ($row = mysqli_fetch_assoc($keywordResult)) {
            $kategori = $row['kategori'];
            $keywords = explode(',', $row['konten']);
            $categoryKeywords[$kategori] = array_map('trim', $keywords);
            error_log("Keywords untuk kategori $kategori: " . implode(", ", $categoryKeywords[$kategori]));
        }
    }

    // Tambahkan default keywords untuk kategori yang tidak memiliki keyword khusus
    foreach ($allCategories as $category) {
        if (!isset($categoryKeywords[$category])) {
            $categoryKeywords[$category] = [$category]; // Gunakan nama kategori sebagai keyword default
            error_log("Default keyword untuk kategori $category: $category");
        }
    }

    // Mulai dengan array kosong untuk kategori relevan
    $relevantCategories = [];
    error_log("Kategori awal: " . implode(", ", $relevantCategories));

    // Identifikasi kategori yang relevan berdasarkan keyword
    foreach ($allCategories as $category) {
        // Periksa apakah nama kategori muncul dalam query
        if (stripos($query, $category) !== false) {
            $relevantCategories[] = $category;
            error_log("Kategori $category relevan (dari nama kategori)");
            continue;
        }

        // Periksa keyword untuk kategori ini
        if (isset($categoryKeywords[$category])) {
            foreach ($categoryKeywords[$category] as $keyword) {
                if ($keyword !== '' && stripos($query, $keyword) !== false) {
                    $relevantCategories[] = $category;
                    error_log("Kategori $category relevan (dari keyword: $keyword)");
                    break;
                }
            }
        }
    }

    error_log("Kategori yang relevan: " . implode(", ", $relevantCategories));

    // Jika tidak ada kategori yang relevan, ambil semua kategori (maksimal 3)
    if (empty($relevantCategories) && !empty($query)) {
        error_log("Tidak ada kategori relevan, mengambil semua kategori (maksimal 3)");
        $relevantCategories = array_slice($allCategories, 0, 3);
        error_log("Menggunakan kategori: " . implode(", ", $relevantCategories));
    }

    // Kumpulkan data dari database
    $contextData = '';

    foreach ($relevantCategories as $category) {
        $categoryData = '';

        // Query untuk mengambil data
        $sql = "SELECT * FROM informasi_sekolah WHERE kategori = ? AND judul != 'keywords'";
        $stmt = mysqli_prepare($koneksi, $sql);

        if (!$stmt) {
            error_log("Prepare statement error: " . mysqli_error($koneksi));
            continue;
        }

        mysqli_stmt_bind_param($stmt, "s", $category);

        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute statement error: " . mysqli_stmt_error($stmt));
            continue;
        }

        $result = mysqli_stmt_get_result($stmt);
        $rowCount = mysqli_num_rows($result);

        error_log("Kategori $category: ditemukan $rowCount baris");

        if ($rowCount > 0) {
            $categoryData .= "### " . strtoupper($category) . "\n";

            // Kelompokkan berdasarkan judul
            $groupedData = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $judul = $row['judul'] ?: 'Info Umum';
                if (!isset($groupedData[$judul])) {
                    $groupedData[$judul] = [];
                }
                $groupedData[$judul][] = $row['konten'];
            }

            // Format data untuk ditampilkan
            foreach ($groupedData as $judul => $items) {
                if ($judul !== 'Info Umum') {
                    $categoryData .= "$judul:\n";
                }

                foreach ($items as $item) {
                    if (str_contains($item, ':')) {
                        // Jika item mengandung ':', tampilkan dengan indentasi
                        $categoryData .= "  $item\n";
                    } else {
                        // Jika tidak, tampilkan sebagai list item
                        $categoryData .= "- $item\n";
                    }
                }
                $categoryData .= "\n";
            }

            $contextData .= $categoryData;
        }
    }

    // Jika tidak ada data yang ditemukan, gunakan default
    if (empty($contextData)) {
        error_log("Tidak ada data kontekstual yang ditemukan, menggunakan default");
        return $defaultInfo;
    }

    error_log("Data kontekstual berhasil disiapkan: " . substr($contextData, 0, 200) . "...");
    return "INFORMASI SEKOLAH:\n\n$contextData";
}

// Dapatkan konteks dan kembalikan sebagai JSON
$context = getContextualKnowledge($query);
header('Content-Type: application/json');
echo json_encode(['context' => $context]);
?>