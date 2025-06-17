<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

function getContextualKnowledge($query)
{
    global $koneksi;

    // Log untuk debugging
    error_log("getContextualKnowledge dipanggil dengan query: " . $query);

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

    // Kategori umum selalu disertakan
    $relevantCategories = in_array('umum', $allCategories) ? ['umum'] : [];
    error_log("Kategori awal: " . implode(", ", $relevantCategories));

    // Identifikasi kategori yang relevan berdasarkan keyword
    foreach ($allCategories as $category) {
        if ($category === 'umum' || in_array($category, $relevantCategories)) continue;

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

    // Jika tidak ada kategori yang relevan selain umum, ambil semua kategori (batasi 3)
    if (count($relevantCategories) <= 1 && $query) {
        error_log("Tidak ada kategori relevan, mencoba menambahkan kategori berdasarkan nama kategori dalam database");

        // Coba cari beberapa kategori yang mungkin relevan
        $potentialCategories = array_diff($allCategories, $relevantCategories);

        // Batasi hanya 2 kategori tambahan (total 3 dengan umum)
        if (count($potentialCategories) > 0) {
            $additionalCategories = array_slice($potentialCategories, 0, 2);
            $relevantCategories = array_merge($relevantCategories, $additionalCategories);
            error_log("Menambahkan kategori: " . implode(", ", $additionalCategories));
        }
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

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- darkmode -->
    <script>
        (function() {
            // Periksa localStorage untuk status dark mode
            if (localStorage.getItem('darkmode') === 'true') {
                // Jika dark mode aktif, segera tambahkan class
                document.documentElement.classList.add('darkmode-preload');
            }
        })();
    </script>
    <?php include 'darkmode_preload.php'; ?>
   
    <title>SAGA - SMAGAEdu</title>
</head>
<style>
    body {
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }

    /* Container utama */
    #model-list-container {
        position: fixed !important;
        z-index: 100000 !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        background: white !important;
        border-radius: 8px !important;
        overflow: visible !important;
        /* Tetap visible untuk sub-menu */
        margin-top: 0 !important;
        padding: 0.5rem 0 !important;
        transform: none !important;
    }

    /* Aturan untuk sub-menu container */
    .model-item-container .sub-models-container {
        position: absolute !important;
        top: 0 !important;
        left: 100% !important;
        display: none !important;
        /* Penting! Hidden secara default */
        padding: 0.5rem 0 !important;
        background: white !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        border-radius: 8px !important;
        z-index: 100001 !important;
        min-width: 220px !important;
    }

    /* Hanya tampilkan saat hover pada parent */
    .model-item-container:hover>.sub-models-container {
        display: block !important;
    }

    .model-item-container {
        position: relative !important;
    }

    /* Untuk memastikan sub-menu tidak menutup terlalu cepat */
    .model-item-container .sub-models-container:hover {
        display: block !important;
    }

    .model-item {
        white-space: normal !important;
        /* Allow text to wrap */
    }

    /* Make sure the dropup container has proper position */
    #modelDropupContainer {
        position: relative !important;
    }

    /* Fix for Bootstrap's dropdown backdrop which might be blocking clicks */
    .dropdown-backdrop {
        display: none !important;
    }

    /* Style for the model list */
    #model-list-container {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    .model-item {
        padding: 10px 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .model-item:hover {
        background-color: #f8f9fa;
    }

    .model-item.active {
        background-color: rgba(218, 119, 86, 0.1);
    }

    /* Improved button styling for smoother transitions */
    .btn {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275),
            box-shadow 0.3s ease,
            background-color 0.3s ease,
            color 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition-duration: 0.1s;
    }

    /* Tambahkan style di bagian <style> atau file CSS Anda */
    .chat-bubble h1,
    .chat-bubble h2,
    .chat-bubble h3,
    .chat-bubble h4,
    .chat-bubble h5,
    .chat-bubble h6 {
        margin-top: 0.75rem;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .chat-bubble h1 {
        font-size: 1.5rem;
    }

    .chat-bubble h2 {
        font-size: 1.4rem;
    }

    .chat-bubble h3 {
        font-size: 1.3rem;
    }

    .chat-bubble h4 {
        font-size: 1.2rem;
    }

    .chat-bubble h5 {
        font-size: 1.1rem;
    }

    .chat-bubble pre {
        background-color: #f5f5f5;
        padding: 0.75rem;
        border-radius: 4px;
        overflow-x: auto;
        margin: 0.75rem 0;
    }

    .chat-bubble code {
        font-family: monospace;
        background-color: #f5f5f5;
        padding: 0.1rem 0.3rem;
        border-radius: 3px;
        font-size: 0.9em;
    }

    .canvas-editor h1,
    .canvas-editor h2,
    .canvas-editor h3,
    .canvas-editor h4,
    .canvas-editor h5,
    .canvas-editor h6 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    /* CANVASS */
    .canvas-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 0;
        height: 100vh;
        background-color: white;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: width 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .canvas-container.active {
        width: 60%;
    }

    .canvas-header {
        padding: 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .canvas-actions {
        display: flex;
        gap: 10px;
    }

    .canvas-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .canvas-editor {
        min-height: 100%;
        outline: none;
        font-size: 14px;
        line-height: 1.6;
        font-family: 'Merriweather', serif;
        transition: opacity 0.5s ease;
        opacity: 0;
    }

    .canvas-editor.fade-in {
        opacity: 1;
    }

    .canvas-footer {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
    }

    /* Editor toolbar canvas footer */
    .editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .format-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .format-btn:hover {
        background-color: #f8f9fa;
    }

    .format-btn.active {
        background-color: #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Styling untuk area chat ketika canvas aktif */
    .col-utama.canvas-active {
        margin-left: 60% !important;
        transition: margin-left 0.3s ease;
    }


    /* Tambahkan CSS ini di bagian <head> dokumen */
    .canvas-container.active {
        width: 60% !important;
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 1500 !important;
    }

    .col-utama.canvas-active {
        margin-left: 60% !important;
        transition: margin-left 0.3s ease !important;
    }


    /* Tambahkan style ini di bagian CSS */
    .canvas-button-container {
        opacity: 0;
        transform: translateY(-10px);
        animation: fadeInDown 0.5s forwards;
    }

    @keyframes fadeInDown {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .typing-container {
        white-space: pre-wrap;
    }

    /* TEXT TRUNCRATE UNTUK BUTTON LUAR CANVAS */
    /* Tambahkan ke bagian <style> yang sudah ada */
    .first-message-text {
        display: inline-block;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .first-message-text {
            max-width: 100px;
        }
    }

    /* Format untuk elemen di dalam canvas */
    .canvas-editor h1,
    .canvas-editor h2,
    .canvas-editor h3,
    .canvas-editor h4,
    .canvas-editor h5,
    .canvas-editor h6 {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .canvas-editor p {
        margin-bottom: 0.75rem;
    }

    .canvas-editor ol,
    .canvas-editor ul {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .canvas-editor li {
        margin-bottom: 0.25rem;
    }

    .canvas-editor pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 4px;
        overflow-x: auto;
    }

    .canvas-editor code {
        font-family: monospace;
    }


    /* Styling canvas untuk mobile */
    @media (max-width: 768px) {
        .canvas-container {
            /* Reset properti horizontal yang sudah ada */
            left: 0;
            width: 100% !important;

            /* Atur properti vertikal untuk animasi slide-up */
            bottom: -100%;
            top: auto;
            height: 90vh;
            transition: bottom 0.3s ease;
        }

        .canvas-container.active {
            /* Untuk mobile, geser dari bawah ke atas */
            bottom: 0 !important;
            width: 100% !important;
        }

        /* Reset margin untuk col-utama di mobile */
        .col-utama.canvas-active {
            margin-left: 0 !important;
        }

        /* Tambahkan border radius di bagian atas canvas untuk mobile */
        .canvas-container {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        /* Tambahkan indicator drag di atas canvas */
        .canvas-container::before {
            content: '';
            display: block;
            width: 50px;
            height: 5px;
            background-color: #ccc;
            border-radius: 3px;
            margin: 8px auto;
        }

        .editor-toolbar {
            overflow-x: auto;
            padding-bottom: 5px;
            margin-bottom: 5px;
            width: 100%;
        }

        .canvas-footer .d-flex {
            flex-direction: column;
            gap: 10px;
        }

        .canvas-footer .d-flex:last-child {
            width: 100%;
        }

        .canvas-footer .btn {
            width: 100%;
        }
    }

    /* canvas button */

    #canvasModeBtn {
        transition: all 0.3s ease;
    }

    #canvasModeBtn.active {
        background-color: rgb(218, 119, 86) !important;
        color: white !important;
    }

    #canvasModeBtn.active i {
        color: white !important;
    }

    #canvasModeBtn.active p {
        color: white !important;
    }

    /* sub ai models */
    .model-item.active,
    .sub-model-item.active {
        background-color: rgba(218, 119, 86, 0.1);
    }

    /* .model-item.parent-active {
        border-left: 3px solid rgb(218, 119, 86);
        padding-left: calc(0.75rem - 3px);
    } */

    /* Tambahkan area hover lebih lebar untuk memudahkan navigasi */
    .model-item-container::after {
        content: '';
        position: absolute;
        top: 0;
        right: -20px;
        /* Lebih lebar */
        width: 20px;
        height: 100%;
    }

    .sub-model-item {
        padding: 10px 15px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .sub-model-item:hover {
        background-color: #f8f9fa;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .sub-models-container {
            position: static;
            display: none;
            margin-left: 1rem;
            box-shadow: none;
            border-left: 2px solid #f0f0f0;
            padding-left: 1rem;
            opacity: 1;
        }

        .model-item-container.active .sub-models-container {
            display: block;
        }

        .has-sub-models::after {
            content: '↓' !important;
        }

        .model-item-container.active .has-sub-models::after {
            content: '↑' !important;
        }
    }

    /* Hover effect untuk semua item */
    .model-item:hover,
    .sub-model-item:hover {
        background-color: rgba(218, 119, 86, 0.05);
    }

    /* Transisi halus */
    .model-item,
    .sub-model-item {
        transition: all 0.2s ease;
    }


    @media (max-width: 768px) {
        .menu-samping {
            display: none;
        }

        .col-utama {
            margin-left: 0;
            margin-top: 1rem;
        }

        .peringatan {
            display: none;
        }
    }
</style>

<!-- loading screen -->
<style>
    /* Loading Screen */
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: white;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .loader-container {
        text-align: center;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid rgba(218, 119, 86, 0.2);
        border-top: 5px solid rgb(218, 119, 86);
        border-radius: 50%;
        margin: 0 auto 20px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .loader-container p {
        color: rgb(218, 119, 86);
        font-family: 'Merriweather', serif;
        font-size: 16px;
        margin: 0;
    }

    /* Tambahkan class untuk fade out */
    #loading-screen.fade-out {
        opacity: 0;
        visibility: hidden;
    }
</style>

<body>

    <!-- loading awal -->
    <div id="loading-screen" class="loading-screen">
        <div class="loader-container">
            <img src="assets/ai_loading.gif" alt="Loading..." style="width: 80px; height: 80px;" />
        </div>
    </div>

    <!-- lazy loading sheetjs -->
    <script>
        function loadSheetJS() {
            return new Promise((resolve, reject) => {
                if (window.XLSX) {
                    resolve(window.XLSX);
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
                script.async = true;
                script.onload = () => resolve(window.XLSX);
                script.onerror = reject;
                document.body.appendChild(script);
            });
        }
    </script>

    <!-- style untuk animasi modal -->
    <!-- style animasi modal -->
    <style>
        .modal-content {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .modal .btn {
            font-weight: 500;
            transition: all 0.2s;
        }

        .modal .btn:active {
            transform: scale(0.98);
        }

        .modal.fade .modal-dialog {
            transform: scale(0.95);
            transition: transform 0.2s ease-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        /* Tambahkan ke bagian style yang sudah ada */
        .version-indicator {
            font-size: 12px;
            padding: 0 8px;
            white-space: nowrap;
            min-width: 80px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .version-indicator {
                font-size: 10px;
                padding: 0 4px;
                min-width: 60px;
            }
        }
    </style>


    <?php include 'includes/styles.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>


        </div>
    </div>

    <div id="canvas-container" class="canvas-container">
        <div class="canvas-header">
            <h5 class="canvas-title p-0 m-0">Canvas Anda</h5>
            <div class="canvas-actions d-flex gap-2">
                <button id="prev-canvas-version" class="btn btn-sm btn-light" title="Versi sebelumnya" disabled>
                    <i class="bi bi-arrow-left"></i>
                </button>

                <!-- Indikator versi -->
                <span id="version-indicator" class="version-indicator d-flex align-items-center text-muted small">
                    Versi 1 dari 1
                </span>

                <button id="next-canvas-version" class="btn btn-sm btn-light" title="Versi berikutnya" disabled>
                    <i class="bi bi-arrow-right"></i>
                </button>
                <button id="copy-canvas" class="btn btn-sm btn-light" title="Salin konten">
                    <i class="bi bi-clipboard"></i>
                </button>
                <button id="close-canvas" class="btn btn-sm btn-light" title="Tutup editor">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="canvas-body">
            <div id="canvas-editor" contenteditable="true" class="canvas-editor"></div>
        </div>
        <div class="canvas-footer">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="editor-toolbar d-flex gap-2">
                    <button class="btn btn-sm btn-light format-btn" data-format="bold" title="Bold">
                        <i class="bi bi-type-bold"></i>
                    </button>
                    <button class="btn btn-sm btn-light format-btn" data-format="italic" title="Italic">
                        <i class="bi bi-type-italic"></i>
                    </button>
                    <button class="btn btn-sm btn-light format-btn" data-format="underline" title="Underline">
                        <i class="bi bi-type-underline"></i>
                    </button>
                    <div class="vr mx-1"></div>
                    <button class="btn btn-sm btn-light format-btn" data-format="h1" title="Heading 1">
                        <i class="bi bi-type-h1"></i>
                    </button>
                    <button class="btn btn-sm btn-light format-btn" data-format="h2" title="Heading 2">
                        <i class="bi bi-type-h2"></i>
                    </button>
                    <button class="btn btn-sm btn-light format-btn" data-format="h3" title="Heading 3">
                        <i class="bi bi-type-h3"></i>
                    </button>
                    <div class="vr mx-1"></div>
                    <button class="btn btn-sm btn-light format-btn" data-format="ul" title="Bullet List">
                        <i class="bi bi-list-ul"></i>
                    </button>
                    <button class="btn btn-sm btn-light format-btn" data-format="ol" title="Numbered List">
                        <i class="bi bi-list-ol"></i>
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button id="export-canvas" class="btn btn-sm btn-primary" style="border-radius: 15px; padding: 5px 15px;">
                        <i class="bi bi-file-earmark-word me-1"></i> Export
                    </button>
                    <button id="update-canvas" class="btn btn-sm" style="background-color: rgb(218, 119, 86); color: white; border-radius: 15px; padding: 5px 15px;">
                        <i class="bi bi-pen me-1"></i> Revisi
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ini isi kontennya -->
    <div class="col pt-0 pb-0 p-4 col-utama">
        <style>
            .col-utama {
                margin-left: 13rem;
                animation: fadeInUp 0.5s;
                opacity: 1;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>



        <div class="container-fluid p-0 m-0 mt-4">
            <div class="">
                <div class="d-flex justify-content-between">
                    <div class="headerChat">
                        <h3 class="mb-0 fw-bold">Saga</h3>
                        <div style="display: none;">
                            <p class="loading animate__animated animate__fadeIn animate__flash animate__infinite text-muted p-0 m-0"
                                id="loading"
                                style="font-size: 13px; z-index: 10;display: none;">Sedang berpikir...</p>
                            <p class="animate__animated animate__fadeIn text-muted p-0 m-0"
                                style="font-size: 13px; z-index: 10;"
                                id="tersedia">SMAGAAI tersedia</p>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="text-muted" style="font-size: 13px;">
                                <span id="firstMessage" class="d-none">
                                    <i class="bi bi-chat-text"></i>
                                    <span class="first-message-text ms-1"></span>
                                </span>
                            </div>
                        </div>
                        <style>
                            #firstMessage {
                                animation: fadeIn 0.3s ease;
                            }

                            @keyframes fadeIn {
                                from {
                                    opacity: 0;
                                    transform: translateY(-10px);
                                }

                                to {
                                    opacity: 1;
                                    transform: translateY(0);
                                }
                            }
                        </style>

                    </div>
                    <style>
                        .loading {
                            animation-duration: 3s;
                        }

                        .chat-message {
                            margin: 8px 0;
                            transition: all 0.3s ease;
                        }

                        .chat-container {
                            flex: 1;
                            /* Mengisi seluruh ruang tersedia */
                            overflow-y: auto;
                            /* Scroll vertikal ketika diperlukan */
                            padding-bottom: 20px;
                            /* Jarak dari konten terakhir ke input */
                            position: relative;
                            /* Untuk positioning yang tepat */
                            height: calc(95vh - 200px);
                            /* Base height, dikurangi header + input + margins */

                        }

                        @media (max-width: 768px) {

                            /* Sesuaikan chat container */
                            .chat-container {
                                /* background-color: black; */
                                height: calc(165vh - 700px) !important;
                                /* Mengurangi tinggi lebih banyak */
                                overflow-y: auto !important;
                                margin-bottom: 0 !important;
                                padding-bottom: 90px;
                            }

                            .chat-message {
                                max-width: 85% !important;
                            }

                            /* Pastikan input container tetap di posisinya */
                            .input-container {
                                position: fixed !important;
                                bottom: 100px !important;
                                /* Memberikan ruang di atas navbar */
                                /* left: 0 !important; */
                                /* right: 0 !important; */
                                background-color: #EEECE2 !important;
                                padding: 1rem !important;
                                margin: 1rem;
                            }



                            @media (min-width: 769px) {
                                .input-container {
                                    left: 12rem !important;
                                    /* Sama dengan margin-left pada .col-utama */
                                    width: calc(90% - 10rem);
                                    /* Kurangi lebar sidebar */
                                    position: fixed !important;
                                    bottom: 0 !important;
                                    left: 0 !important;
                                    right: 12rem;
                                    padding: 10px;
                                    z-index: 1000;
                                }

                                /* Ketika canvas aktif */
                                .canvas-active .input-container {
                                    left: 60%;
                                    /* Mulai dari akhir canvas */
                                    width: 40%;
                                    /* Hanya ambil sisa ruang */
                                }
                            }

                            /* Sesuaikan ukuran dan posisi input wrapper */
                            .input-wrapper {
                                max-height: 150px;
                                overflow: hidden;
                                width: 100%;
                                max-width: 45rem;
                                margin: 0 auto;
                                /* Tetap di tengah dari parent container */
                                background-color: white;
                                border-radius: 15px;
                            }

                            /* Container utama */
                            .col-utama {
                                margin-left: 0 !important;
                            }
                        }

                        /* Tambahkan ini untuk memastikan scroll berfungsi dengan baik */
                        body {
                            height: 100vh;
                            overflow-y: auto;
                        }
                    </style>
                    <div class="gap-2 mb-4 d-flex">
                        <button class="btn btn-sm bg-light text-black d-flex align-items-center gap-2 px-3 py-2 border"
                            onclick="createNewChatSession()"
                            style="border-radius: 15px;">
                            <i class="bi bi-plus-circle"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Baru</span>
                        </button>
                        <button class="btn btn-sm bg-light text-black d-flex align-items-center gap-2 px-3 py-2 border"
                            data-bs-toggle="modal"
                            data-bs-target="#historyModal"
                            style="border-radius: 15px;">
                            <i class="bi bi-clock-history"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Riwayat</span>
                        </button>

                        <!-- MAINTENANCEEE - MASIH BUG DI PERSONALITY AI -->
                        <!-- <button class="btn btn-sm d-flex text-black bg-white align-items-center gap-2 px-3 py-2 border"
                            data-bs-toggle="modal"
                            data-bs-target="#projectModal"
                            style="border-radius: 15px;">-
                            <i class="bi bi-gear"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Pengaturan</span>
                        </button> -->
                    </div>

                    <style>
                        .btn {
                            padding: 8px 12px;
                            font-size: 14px;
                            border-radius: 8px;
                        }

                        .btn i {
                            font-size: 16px;
                        }

                        .button-text {
                            margin: 0;
                            font-weight: 500;
                        }

                        /* Welcome Message Styling */
                        .welcome-message {
                            position: absolute;
                            top: 40%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            width: 90%;
                            max-width: 500px;
                            background-color: white;
                            border-radius: 15px;
                            padding: 30px;
                            text-align: center;
                            z-index: 10;
                            transition: all 0.3s ease;
                        }

                        .welcome-avatar {
                            width: 60px;
                            height: 60px;
                            object-fit: cover;
                            border-radius: 50%;
                            background-color: rgb(218, 119, 86, 0.1);
                            padding: 10px;
                        }

                        .welcome-content {
                            animation-duration: 0.5s;
                        }

                        .welcome-examples button {
                            font-size: 12px;
                            padding: 5px 10px;
                            transition: all 0.2s;
                        }

                        .welcome-examples button:hover {
                            background-color: rgb(218, 119, 86, 0.1);
                        }

                        /* Penyesuaian untuk desktop dan mobile */
                        @media (min-width: 769px) {
                            .welcome-message {
                                /* Penyesuaian posisi untuk desktop dengan sidebar */
                                left: calc(50% + 6.5rem);
                                /* Kompensasi untuk sidebar */
                            }
                        }

                        @media (max-width: 768px) {
                            .welcome-message {
                                width: 85%;
                                padding: 20px;
                            }

                            .welcome-avatar {
                                width: 50px;
                                height: 50px;
                            }
                        }
                    </style>
                </div>

                <!-- Chat Messages Container -->
                <div id="chat-container" class="card-body chat-container pe-3 mb-0 pb-1">
                    <!-- Pesan chat akan ditampilkan di sini -->
                </div>

                <!-- Welcome Message -->
                <div id="welcomeMessage" class="welcome-message">
                    <div class="welcome-content animate__animated animate__fadeIn">
                        <!-- <img src="assets/ai_chat.png" class="welcome-avatar mb-3" alt="SAGA AI"> -->
                        <h5 class="fw-bold" style="font-size:28px">Halo, ada yang bisa <br> saya bantu?</h5>
                    </div>
                </div>


                <!-- Tambah HTML setelah chat-container: -->
                <div class="recommendation-container" style="position: relative; margin-top: -40px;">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi" onclick="fillPrompt('Berikan saya Tips mengajar efektif')">
                            <i class="bi bi-pen-fill pe-1 "></i>
                            Tips mengajar efektif
                        </button>
                        <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi2" onclick="fillPrompt('Berikan saya Ide aktivitas kelas')">
                            <i class="bi bi-lightbulb-fill pe-1"></i>
                            Ide aktivitas kelas
                        </button>
                        <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi3" onclick="fillPrompt('Beri saya kejutan!')">
                            <i class="bi bi-emoji-surprise-fill pe-1"></i>
                            Beri saya kejutan!
                        </button>
                    </div>
                </div>

                <style>
                    .buttonRekomendasi {
                        background-color: #EEECE2;
                        border: 0;
                        padding: 5px 10px;
                        font-size: 12px;
                        border-radius: 10px;
                        cursor: pointer;
                    }

                    @media (max-width: 768px) {
                        .buttonRekomendasi2 {
                            display: none;
                        }
                    }
                </style>

                <!-- Input Area -->
                <div class="d-flex justify-content-center input-container mt-0 pt-0">
                    <style>
                        .input-container {
                            position: fixed;
                            bottom: 0;
                            left: 0;
                            right: 12rem;
                            padding: 10px;
                            z-index: 1000;
                            left: 25rem;
                            transition: left 0.3s ease, width 0.3s ease;
                            /* Tambahkan transisi agar perubahan halus */
                        }

                        /* Input container saat canvas aktif di desktop */
                        .canvas-active .input-container {
                            left: 60%;
                            /* Sesuaikan dengan lebar canvas (60%) */
                            right: 0;
                        }



                        @media (max-width: 768px) {
                            /* .input-container {
                                position: fixed;
                                bottom: 0;
                                left: 0;
                                right: 0;
                                background-color: #EEECE2;
                                padding: 10px;
                            } */
                        }

                        .input-wrapper {
                            max-height: 150px;
                            overflow: hidden;
                            width: 100%;
                            max-width: 45rem;
                            margin: 0 auto;
                            background-color: white;
                            border-radius: 15px;
                        }

                        #user-input {
                            min-height: 38px;
                            max-height: 150px;
                            resize: none;
                            overflow-y: auto;
                        }

                        @media (max-width: 768px) {
                            .input-wrapper {
                                position: fixed;
                                bottom: 4.5rem;
                                left: 0;
                                right: 0;
                                padding: 2rem;
                            }

                            #user-input {
                                height: auto;
                            }
                        }

                        /* CSS untuk animasi fade out rekomendasi chat */
                        .recommendation-container {
                            transition: opacity 0.3s;
                        }

                        .recommendation-container.hide {
                            opacity: 0;
                            pointer-events: none;
                        }

                        .recommendation-container {
                            margin: 10px 0;
                        }

                        @media screen and (max-width: 768px) {
                            .recommendation-container {
                                display: none;
                            }

                        }
                    </style>
                    <div class="input-wrapper border rounded-5 card-footer p-2 w-100" id="input-wrapper" style="max-width: 45rem;">
                        <div class="input-group d-flex align-items-center">
                            <textarea id="user-input" class="form-control border-0" style="background-color: transparent;" placeholder="Tulis apapun"></textarea>
                            <button id="send-button" class="btn d-md-none bi-arrow-up rounded-4 text-white" style="background-color: rgb(218, 119, 86); margin-top:-1rem;"></button>
                            <button class="btn d-none d-md-block btn-sm rounded-4 border" style="margin-top:-1rem; font-size:10px;"> Entr <span class=" bi-arrow-return-left"></span></button>
                        </div>
                        <!--  -->
                        <!-- settings mode -->
                        <div class="d-flex gap-2 mt-2">
                            <!-- Upload button -->
                            <button class="btn border ms-1 button-style d-flex rounded-pill align-items-center"
                                style="padding: 5px 15px;"
                                onclick="document.getElementById('file-input').click()">
                                <i class="bi bi-plus" style="font-size: 16px;"></i>
                                <input type="file" id="file-input" accept=".pdf,.doc,.docx.xlsx,.xls" style="display: none;">
                            </button>

                            <!-- Model selection dropdown (menggantikan deep-thinking-toggle) -->
                            <div class="btn-group dropup" id="modelDropupContainer">
                                <button class="btn rounded-pill border button-style p-2 d-flex align-items-center gap-2"
                                    id="modelDropdownBtn">
                                    <i class="bi bi-stars" style="font-size: 16px;"></i>
                                    <p class="p-0 m-0 text-dark" style="font-size: 12px; cursor: pointer;">
                                        <span id="current-model-name">LLaMA 3.3 70B</span>
                                    </p>
                                </button>

                                <ul id="model-list-container" style="display: none;">
                                    <!-- Your model items here -->
                                </ul>
                            </div>

                            <button class="btn rounded-pill border button-style p-2 d-flex align-items-center gap-2" id="canvasModeBtn">
                                <i class="bi bi-easel-fill" style="font-size: 16px;"></i>
                                <p class="p-0 m-0 text-dark" style="font-size: 12px; cursor: pointer;">Canvas</p>
                            </button>

                            <!-- canvas mode toogle -->
                            <script>
                                // Canvas Mode handling
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Inisialisasi variabel untuk canvas mode
                                    window.canvasModeEnabled = false;

                                    // Ambil referensi ke tombol
                                    const canvasModeBtn = document.getElementById('canvasModeBtn');

                                    if (canvasModeBtn) {
                                        // Tambahkan event listener untuk toggle mode
                                        canvasModeBtn.addEventListener('click', function() {
                                            // Toggle status
                                            window.canvasModeEnabled = !window.canvasModeEnabled;

                                            // Update UI untuk menunjukkan status aktif/nonaktif
                                            if (window.canvasModeEnabled) {
                                                canvasModeBtn.classList.add('active');
                                                canvasModeBtn.style.backgroundColor = 'rgb(218, 119, 86)';
                                                canvasModeBtn.querySelector('p').style.color = 'white';
                                            } else {
                                                canvasModeBtn.classList.remove('active');
                                                canvasModeBtn.style.backgroundColor = '';
                                                canvasModeBtn.querySelector('p').style.color = 'black';
                                            }

                                            console.log('Canvas Mode:', window.canvasModeEnabled ? 'ON' : 'OFF');
                                        });
                                    }
                                });
                            </script>

                            <!-- Checkbox bernalar tersembunyi untuk kompatibilitas -->
                            <input class="form-check-input d-none" type="checkbox" id="deepThinkingToggle">


                            <!-- taruh memori yang aktif di sini -->
                        </div>

                        <!-- taruh memori yang aktif di sini -->

                    </div>
                </div>

                <style>
                    .deep-thinking-toggle {
                        transition: all 0.3s ease;
                    }

                    .deep-thinking-toggle.active {
                        background-color: rgb(218, 119, 86) !important;
                        border: 0;
                    }

                    .deep-thinking-toggle.active .toggle-text {
                        color: white !important;
                    }

                    .button-style {
                        transition: all 0.2s ease;
                    }

                    .button-style:hover {
                        opacity: 0.9;
                        transform: translateY(-1px);
                    }

                    @media (min-width: 768px) {
                        .deep-thinking-toggle {
                            display: flex !important;
                        }
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Create a custom dropup solution
                        const modelBtn = document.getElementById('modelDropdownBtn');
                        const modelList = document.getElementById('model-list-container');

                        if (modelBtn && modelList) {
                            // Remove Bootstrap's data attributes to prevent its dropdown behavior
                            modelBtn.removeAttribute('data-bs-toggle');
                            modelBtn.removeAttribute('data-bs-target');

                            // Create our own custom dropup
                            let isOpen = false;

                            modelBtn.addEventListener('click', function(e) {
                                e.stopPropagation(); // Prevent event bubbling

                                if (!isOpen) {
                                    // Show dropup
                                    showDropup();
                                } else {
                                    // Hide dropup
                                    hideDropup();
                                }
                            });

                            // Function to show dropup properly positioned
                            function showDropup() {
                                // Remove from current location and append to body
                                document.body.appendChild(modelList);

                                // Style the dropup
                                modelList.style.position = 'fixed';
                                modelList.style.display = 'block';
                                modelList.style.zIndex = '999999';
                                modelList.style.background = 'white';
                                modelList.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
                                modelList.style.borderRadius = '8px';
                                modelList.style.overflow = 'hidden';
                                modelList.style.minWidth = '200px';
                                modelList.style.maxWidth = '250px';
                                modelList.style.padding = '0.5rem 0';

                                // First position it off-screen to calculate its height
                                modelList.style.top = '-9999px';
                                modelList.style.left = '-9999px';

                                // Force layout calculation so we can get the height
                                const dropupHeight = modelList.offsetHeight;

                                // Position it ABOVE the button
                                const btnRect = modelBtn.getBoundingClientRect();
                                modelList.style.top = (btnRect.top + window.scrollY - dropupHeight - 5) + 'px';
                                modelList.style.left = (btnRect.left + window.scrollX) + 'px';

                                // Tambahkan event listener untuk mencegah dropdown tertutup saat hover
                                modelList.addEventListener('mouseenter', function() {
                                    if (closeTimeout) {
                                        clearTimeout(closeTimeout);
                                    }
                                });

                                isOpen = true;



                                // Close when clicking outside
                                setTimeout(() => {
                                    document.addEventListener('click', documentClickHandler);
                                }, 10);
                            }

                            let closeTimeout;


                            function hideDropup() {
                                // Berikan delay sebelum menutup dropdown
                                closeTimeout = setTimeout(() => {
                                    modelList.style.display = 'none';
                                    isOpen = false;
                                    document.removeEventListener('click', documentClickHandler);
                                }, 100);
                            }

                            function documentClickHandler(e) {
                                if (!modelList.contains(e.target) && e.target !== modelBtn) {
                                    hideDropup();
                                }
                            }

                            // Prevent clicks inside dropup from closing it
                            modelList.addEventListener('click', function(e) {
                                e.stopPropagation();

                                // Handle model selection
                                if (e.target.closest('.model-item')) {
                                    const modelId = e.target.closest('.model-item').dataset.modelId;
                                    if (modelId && typeof window.setActiveModel === 'function') {
                                        window.setActiveModel(modelId);
                                        hideDropup();
                                    }
                                }
                            });
                        }
                    });

                    // Prevent dropdown from closing when clicking inside
                    $(document).on('click', '#model-list-container', function(e) {
                        e.stopPropagation();
                    });

                    // Tambahkan script berikut untuk mengganti semuanya:
                    document.addEventListener('DOMContentLoaded', function() {

                        // Tambahkan script ini setelah model-list-container diisi
                        setTimeout(() => {
                            document.querySelectorAll('.model-item-container').forEach(container => {
                                container.addEventListener('mouseenter', () => {
                                    console.log('Hover on model:', container.querySelector('.model-item').textContent.trim());
                                    const subModels = container.querySelector('.sub-models-container');
                                    if (subModels) {
                                        console.log('Sub-models found, showing');
                                        subModels.style.display = 'block';
                                        subModels.style.opacity = '1';
                                        subModels.style.visibility = 'visible';
                                    }
                                });

                                container.addEventListener('mouseleave', () => {
                                    const subModels = container.querySelector('.sub-models-container');
                                    if (subModels) {
                                        // Tambahkan delay kecil untuk mencegah penutupan terlalu cepat
                                        setTimeout(() => {
                                            if (!subModels.matches(':hover')) {
                                                subModels.style.display = 'none';
                                                subModels.style.opacity = '0';
                                            }
                                        }, 100);
                                    }
                                });
                            });
                        }, 500); // Delay untuk memastikan model-list-container sudah terisi

                        // Definisikan model-model AI yang tersedia
                        const aiModels = [{
                                id: 'meta-llama/llama-4-scout-17b-16e-instruct',
                                name: 'SAGA Opus',
                                desc: 'Model Tercerdas',
                                isDefault: true,
                                subModels: [{
                                        id: 'meta-llama/llama-4-scout-17b-16e-instruct',
                                        name: 'SAGA 1.5 Opus',
                                        desc: 'Model SAGA Tercerdas saat ini'
                                    },
                                    {
                                        id: 'llama-3.3-70b-versatile',
                                        name: 'SAGA Opus',
                                        desc: 'Model SAGA Legacy'
                                    }
                                ]
                            },
                            {
                                id: 'deepseek-r1-distill-llama-70b',
                                name: 'SAGA Reasoning',
                                desc: 'Terbaik untuk analisis',
                                subModels: [{
                                        id: 'deepseek-r1-distill-llama-70b',
                                        name: 'SAGA 1.5 Reasoning Pro',
                                        desc: 'Model Penalaran terbaik SAGA saat ini'
                                    },
                                    {
                                        id: 'deepseek-r1-distill-qwen-32b',
                                        name: 'SAGA 1.5 Reasoning',
                                        desc: 'Model Penalaran efisien '
                                    },
                                    {
                                        id: 'qwen-qwq-32b',
                                        name: 'SAGA Reasoning',
                                        desc: 'Model Penalaran SAGA Legacy'
                                    }
                                ]
                            },
                            {
                                id: 'mistral-saba-24b',
                                name: 'SAGA Haiku',
                                desc: 'Terbaik untuk text panjang'
                            },
                            {
                                id: 'gemma2-9b-it',
                                name: 'SAGA Mini',
                                desc: 'Seimbang'
                            },
                            {
                                id: 'llama-3.1-8b-instant',
                                name: 'SAGA Ultra Mini',
                                desc: 'Tercepat'
                            }
                        ];

                        // Set model aktif secara default
                        window.activeModelId = 'llama-3.3-70b-versatile';

                        // Modifikasi fungsi setActiveModel untuk memperbaiki kedua masalah
                        window.setActiveModel = function(modelId) {
                            window.activeModelId = modelId;

                            // Daftar model yang perlu dinonaktifkan fitur Canvas-nya
                            const disableCanvasForModels = [
                                'deepseek-r1-distill-llama-70b',
                                'deepseek-r1-distill-qwen-32b',
                                'qwen-qwq-32b',
                                'gemma2-9b-it',
                                'llama-3.1-8b-instant'
                            ];

                            // Dapatkan tombol Canvas
                            const canvasModeBtn = document.getElementById('canvasModeBtn');

                            // Nonaktifkan tombol jika model termasuk dalam daftar
                            if (canvasModeBtn) {
                                if (disableCanvasForModels.includes(modelId)) {
                                    // Nonaktifkan tombol Canvas
                                    canvasModeBtn.disabled = true;
                                    canvasModeBtn.classList.add('disabled');
                                    canvasModeBtn.style.opacity = '0.5';
                                    canvasModeBtn.setAttribute('title', 'Canvas tidak tersedia untuk model ini');

                                    // Reset status canvas mode jika sedang aktif
                                    if (window.canvasModeEnabled) {
                                        window.canvasModeEnabled = false;
                                        canvasModeBtn.classList.remove('active');
                                        canvasModeBtn.style.backgroundColor = '';
                                        canvasModeBtn.querySelector('p').style.color = 'black';
                                    }
                                } else {
                                    // Aktifkan kembali tombol Canvas
                                    canvasModeBtn.disabled = false;
                                    canvasModeBtn.classList.remove('disabled');
                                    canvasModeBtn.style.opacity = '1';
                                    canvasModeBtn.removeAttribute('title');
                                }
                            }

                            // Temukan model dari struktur data (termasuk sub-models)
                            let selectedModel = null;
                            aiModels.forEach(model => {
                                if (model.id === modelId) {
                                    selectedModel = model;
                                } else if (model.subModels) {
                                    model.subModels.forEach(subModel => {
                                        if (subModel.id === modelId) {
                                            selectedModel = subModel;
                                            // Tambahkan referensi ke parent model untuk tampilan di UI
                                            selectedModel.parentName = model.name;
                                        }
                                    });
                                }
                            });

                            // Update UI
                            const displayElement = document.getElementById('current-model-name');
                            if (displayElement && selectedModel) {
                                // Tampilkan hanya nama sub-model, tanpa parent model
                                displayElement.textContent = selectedModel.name;
                            }

                            // Simpan di localStorage untuk persistensi
                            localStorage.setItem('preferredModel', modelId);
                            console.log(`Model changed to: ${selectedModel?.name} (${modelId})`);

                            // Update semua ikon centang di dropdown
                            setTimeout(() => {
                                // Reset semua item
                                document.querySelectorAll('.model-item, .sub-model-item').forEach(item => {
                                    item.classList.remove('active');
                                    const checkIcon = item.querySelector('.bi-check-circle-fill');
                                    if (checkIcon) checkIcon.remove();
                                });

                                // Set active class dan checkmark untuk model utama dan sub-model yang dipilih
                                const mainItems = document.querySelectorAll('.model-item');
                                const subItems = document.querySelectorAll('.sub-model-item');

                                // Cek sub-model dulu
                                let subModelFound = false;
                                subItems.forEach(item => {
                                    if (item.dataset.modelId === modelId) {
                                        item.classList.add('active');

                                        // Tambahkan checkmark
                                        const checkIcon = document.createElement('i');
                                        checkIcon.className = 'bi bi-check-circle-fill ms-auto';
                                        checkIcon.style.color = 'rgb(218, 119, 86)';
                                        item.appendChild(checkIcon);

                                        // Tandai parent juga sebagai active
                                        const parentContainer = item.closest('.model-item-container');
                                        if (parentContainer) {
                                            const parentItem = parentContainer.querySelector('.model-item');
                                            if (parentItem) {
                                                parentItem.classList.add('parent-active');
                                            }
                                        }

                                        subModelFound = true;
                                    }
                                });

                                // Jika bukan sub-model, tandai model utama yang dipilih
                                if (!subModelFound) {
                                    mainItems.forEach(item => {
                                        if (item.dataset.modelId === modelId) {
                                            item.classList.add('active');

                                            // Tambahkan checkmark
                                            const checkIcon = document.createElement('i');
                                            checkIcon.className = 'bi bi-check-circle-fill ms-auto';
                                            checkIcon.style.color = 'rgb(218, 119, 86)';
                                            item.appendChild(checkIcon);
                                        }
                                    });
                                }
                            }, 100);
                        };


                        // Tambahkan kode ini ketika membuat sub-model item
                        document.addEventListener('DOMContentLoaded', function() {
                            // Buat ulang event handler untuk sub-model item
                            function setupSubModelHandlers() {
                                document.querySelectorAll('.sub-model-item').forEach(item => {
                                    // Hapus event listener yang mungkin sudah ada untuk mencegah duplikasi
                                    item.removeEventListener('click', subModelClickHandler);
                                    // Tambahkan event listener baru
                                    item.addEventListener('click', subModelClickHandler);
                                });
                            }

                            // Handler untuk klik pada sub-model
                            function subModelClickHandler(e) {
                                e.preventDefault();
                                e.stopPropagation();

                                const modelId = this.dataset.modelId;
                                console.log('Sub-model clicked:', modelId);

                                // Pastikan modelId ada sebelum memanggil setActiveModel
                                if (modelId) {
                                    window.setActiveModel(modelId);

                                    // Tutup dropdown
                                    const modelList = document.getElementById('model-list-container');
                                    if (modelList) {
                                        modelList.style.display = 'none';
                                    }
                                } else {
                                    console.error('No model ID found for sub-model item');
                                }
                            }

                            // Jalankan setup setelah model-list-container diisi
                            setTimeout(setupSubModelHandlers, 1000);

                            // Juga, pasang pengintai mutasi untuk menangani sub-model yang ditambahkan secara dinamis
                            const observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.addedNodes.length) {
                                        setupSubModelHandlers();
                                    }
                                });
                            });

                            // Mulai pengamatan setelah model-list-container tersedia
                            setTimeout(() => {
                                const modelList = document.getElementById('model-list-container');
                                if (modelList) {
                                    observer.observe(modelList, {
                                        childList: true,
                                        subtree: true
                                    });
                                }
                            }, 1000);
                        });


                        // Handler untuk mobile
                        function setupMobileHandlers() {
                            if (window.innerWidth <= 768) {
                                document.querySelectorAll('.model-item.has-sub-models').forEach(item => {
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        // Toggle sub-models container
                                        const parent = this.closest('.model-item-container');
                                        parent.classList.toggle('active');
                                    });
                                });
                            }
                        }
                        // Mencegah dropdown menutup saat hover sub-model
                        document.addEventListener('DOMContentLoaded', function() {
                            const modelList = document.getElementById('model-list-container');

                            if (modelList) {
                                modelList.addEventListener('mouseover', function(e) {
                                    // Hentikan event bubbling yang mungkin menutup dropdown
                                    e.stopPropagation();
                                });

                                // Mencegah parent dropdown tertutup saat mengklik sub-model
                                modelList.addEventListener('click', function(e) {
                                    if (e.target.closest('.sub-model-item')) {
                                        e.stopPropagation();
                                    }
                                });
                            }
                        });


                        // Panggil saat window load dan resize
                        window.addEventListener('load', setupMobileHandlers);
                        window.addEventListener('resize', setupMobileHandlers);



                        // Populasi model list di dropdown
                        const modelListEl = document.getElementById('model-list-container');
                        // console.log('model list el', modelListEl);
                        if (modelListEl) {
                            modelListEl.innerHTML = ''; // Hapus semua item terlebih dahulu

                            aiModels.forEach(model => {
                                const item = document.createElement('li');
                                item.innerHTML = `
                <a class="dropdown-item d-flex align-items-center p-3 model-item ${model.isDefault ? 'active' : ''}" 
                   href="javascript:void(0)" 
                   data-model-id="${model.id}"
                   style="z-index:9999 !important;">
                  <div class="me-3 d-flex align-items-center justify-content-center" 
                       style="width: 40px; height: 40px; border-radius: 10px; background-color: #f8f9fa;">
                    <i class="bi bi-cpu" style="font-size: 20px; color: ${model.isDefault ? 'rgb(218, 119, 86)' : '#6c757d'};"></i>
                  </div>
                  <div>
                    <h6 class="mb-0" style="font-size: 14px;">${model.name}</h6>
                    <p class="mb-0 text-muted" style="font-size: 12px;">${model.desc}</p>
                  </div>
                  ${model.isDefault ? '<i class="bi bi-check-circle-fill ms-auto" style="color: rgb(218, 119, 86);"></i>' : ''}
                </a>
            `;

                                modelListEl.appendChild(item);

                                // Menangani klik pada tombol model selector
                                document.getElementById('modelDropdownBtn').addEventListener('click', function() {
                                    // const modelModal = new bootstrap.Modal(document.getElementById('modelSelectorModal'));
                                    // modelModal.show();
                                });

                                // Add click event
                                const modelItem = item.querySelector('.model-item');
                                if (modelItem) {
                                    modelItem.addEventListener('click', function() {
                                        const modelId = this.dataset.modelId;

                                        // Update aktif di UI
                                        document.querySelectorAll('.model-item').forEach(item => {
                                            item.classList.remove('active');
                                            const cpuIcon = item.querySelector('.bi-cpu');
                                            if (cpuIcon) cpuIcon.style.color = '#6c757d';
                                            const checkIcon = item.querySelector('.bi-check-circle-fill');
                                            if (checkIcon) checkIcon.remove();
                                        });

                                        // Update yang baru dipilih
                                        this.classList.add('active');
                                        const cpuIcon = this.querySelector('.bi-cpu');
                                        if (cpuIcon) cpuIcon.style.color = 'rgb(218, 119, 86)';

                                        // Tambahkan check icon
                                        if (!this.querySelector('.bi-check-circle-fill')) {
                                            const checkIcon = document.createElement('i');
                                            checkIcon.className = 'bi bi-check-circle-fill ms-auto';
                                            checkIcon.style.color = 'rgb(218, 119, 86)';
                                            this.appendChild(checkIcon);
                                        }

                                        setActiveModel(modelId);
                                    });
                                }
                            });
                        }

                        const activeModelId = window.activeModelId || 'llama-3.3-70b-versatile';
                        document.querySelectorAll('.model-item').forEach(item => {
                            const itemModelId = item.dataset.modelId;
                            const checkIcon = item.querySelector('.bi-check-circle-fill');

                            if (itemModelId === activeModelId) {
                                item.classList.add('active');
                                const modelImg = item.querySelector('.model-img');
                                if (modelImg) modelImg.style.border = '2px solid rgb(218, 119, 86)';

                                // Tambahkan ikon centang jika tidak ada
                                if (!checkIcon) {
                                    const newCheckIcon = document.createElement('i');
                                    newCheckIcon.className = 'bi bi-check-circle-fill ms-auto';
                                    newCheckIcon.style.color = 'rgb(218, 119, 86)';
                                    item.appendChild(newCheckIcon);
                                }
                            } else {
                                item.classList.remove('active');
                                const modelImg = item.querySelector('.model-img');
                                if (modelImg) modelImg.style.border = '1px solid #dee2e6';

                                // Hapus ikon centang jika ada
                                if (checkIcon) checkIcon.remove();
                            }
                        });

                        // Inisialisasi model dari localStorage jika ada
                        const savedModel = localStorage.getItem('preferredModel');
                        if (savedModel && aiModels.some(m => m.id === savedModel)) {
                            setActiveModel(savedModel);

                            // Update UI juga
                            const modelItems = document.querySelectorAll('.model-item');
                            modelItems.forEach(item => {
                                if (item.dataset.modelId === savedModel) {
                                    item.classList.add('active');
                                    const modelImg = item.querySelector('.model-img');
                                    if (modelImg) modelImg.style.border = '2px solid rgb(218, 119, 86)';
                                } else {
                                    item.classList.remove('active');
                                    const modelImg = item.querySelector('.model-img');
                                    if (modelImg) modelImg.style.border = '1px solid #dee2e6';
                                    const checkIcon = item.querySelector('.bi-check-circle-fill');
                                    if (checkIcon) checkIcon.remove();
                                }
                            });
                        } else {
                            // Default ke model pertama
                            setActiveModel(aiModels[0].id);
                        }

                        // Populasi model list dengan gambar model alih-alih icon
                        // Reuse the existing modelListEl variable instead of redeclaring it
                        // Populasi model list dengan gambar model dan sub-model
                        if (modelListEl) {
                            modelListEl.innerHTML = ''; // Hapus semua item terlebih dahulu

                            // Definisikan gambar untuk setiap model
                            const modelImages = {
                                'llama-3.3-70b-versatile': 'assets/brain.png',
                                'meta-llama/llama-4-scout-17b-16e-instruct': 'assets/brain.png',
                                'deepseek-r1-distill-llama-70b': 'assets/search.png',
                                'deepseek-r1-distill-qwen-32b': 'assets/search.png',
                                'qwen-qwq-32b': 'assets/search.png',
                                'llama-3.1-8b-instant': 'assets/fast.png',
                                'mistral-saba-24b': 'assets/text.png',
                                'gemma2-9b-it': 'assets/fast.png'
                            };

                            aiModels.forEach(model => {
                                const item = document.createElement('li');
                                item.className = 'model-item-container';
                                const modelImageUrl = modelImages[model.id] || 'assets/llama.png';

                                // Main model item
                                const hasSubModels = model.subModels && model.subModels.length > 0;

                                item.innerHTML = `
            <a class="dropdown-item d-flex align-items-center p-3 model-item ${model.isDefault ? 'active' : ''} ${hasSubModels ? 'has-sub-models' : ''}" 
               href="javascript:void(0)" 
               data-model-id="${model.id}"
               style="z-index:9999 !important;">
              <div class="me-3 d-flex align-items-center justify-content-center border flex-shrink-0" 
                   style="width: 40px; height: 40px; border-radius: 10px; overflow: hidden;">
                <img src="${modelImageUrl}" alt="${model.name}" class="model-img" 
                    style="width: 40px; height: 40px; object-fit: cover;">
              </div>
              <div>
                <h6 class="mb-0" style="font-size: 14px;">${model.name}</h6>
                <p class="mb-0 text-muted" style="font-size: 12px;">${model.desc}</p>
              </div>
              ${model.isDefault ? '<i class="bi bi-check-circle-fill ms-auto" style="color: rgb(218, 119, 86);"></i>' : ''}
            </a>
        `;

                                // Add sub-models if any
                                if (hasSubModels) {
                                    const subModelsContainer = document.createElement('div');
                                    subModelsContainer.className = 'sub-models-container';
                                    subModelsContainer.style.zIndex = '1000001'; // Tambahkan ini

                                    model.subModels.forEach(subModel => {
                                        const subModelImageUrl = modelImages[subModel.id] || modelImageUrl;

                                        const subModelItem = document.createElement('div');
                                        subModelItem.className = 'sub-model-item';
                                        subModelItem.dataset.modelId = subModel.id;

                                        subModelItem.innerHTML = `
    <div style="margin-left: 10px;"> <!-- Margin untuk alignment -->
        <h6 class="mb-0" style="font-size: 13px;">${subModel.name}</h6>
        <p class="mb-0 text-muted" style="font-size: 11px;">${subModel.desc}</p>
    </div>
`;

                                        // Add click event to sub-model
                                        subModelItem.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            const modelId = this.dataset.modelId;
                                            console.log('Sub-model clicked:', this.dataset.modelId);
                                            setActiveModel(modelId);
                                            // Tutup dropdown
                                            const modelList = document.getElementById('model-list-container');
                                            if (modelList) modelList.style.display = 'none';
                                        });
                                        subModelsContainer.style.marginTop = '-1px';
                                        subModelsContainer.appendChild(subModelItem);
                                    });

                                    item.appendChild(subModelsContainer);
                                }

                                // Add click event to the model item
                                const modelItem = item.querySelector('.model-item');
                                if (modelItem) {
                                    // Temukan bagian ini di event listener untuk model item:
                                    modelItem.addEventListener('click', function(e) {
                                        e.stopPropagation();

                                        // Jika memiliki sub-model
                                        if (hasSubModels) {
                                            e.preventDefault();

                                            // Toggle tampilan sub-model
                                            const parent = this.closest('.model-item-container');
                                            const subModels = parent.querySelector('.sub-models-container');

                                            // Tutup semua sub-model lain
                                            document.querySelectorAll('.sub-models-container').forEach(el => {
                                                if (el !== subModels) {
                                                    el.style.display = 'none';
                                                }
                                            });

                                            // Toggle sub-model saat ini
                                            if (subModels.style.display === 'block') {
                                                subModels.style.display = 'none';
                                            } else {
                                                // Posisikan sub-model dengan benar
                                                subModels.style.display = 'block';
                                                subModels.style.opacity = '1';
                                                subModels.style.visibility = 'visible';

                                                // Jika ini di desktop, posisikan di sebelah kanan
                                                if (window.innerWidth > 768) {
                                                    subModels.style.top = '0';
                                                    subModels.style.left = '100%';
                                                } else {
                                                    // Jika di mobile, posisikan di bawah
                                                    subModels.style.top = '100%';
                                                    subModels.style.left = '0';
                                                }
                                            }
                                            return;
                                        }

                                        // Kode untuk model tanpa sub-model tetap sama
                                        const modelId = this.dataset.modelId;
                                        setActiveModel(modelId);
                                        document.getElementById('model-list-container').style.display = 'none';
                                    });
                                }

                                modelListEl.appendChild(item);
                            });
                        }

                        // CSS untuk model dropdown
                        const styleElement = document.createElement('style');
                        styleElement.textContent = `
        .model-dropdown {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }
        
        .model-item {
            transition: all 0.2s ease;
        }
        
        .model-item:hover {
            background-color: #f8f9fa;
        }
        
        .model-item.active {
            background-color: rgba(218, 119, 86, 0.1);
        }
    `;
                        document.head.appendChild(styleElement);
                    });

                    // Modifikasi getAIResponse untuk menggunakan model yang dipilih
                    //                 async function getAIResponse(userMessage) {
                    //                     const API_KEY = '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';
                    //                     const API_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

                    //                     // Gunakan model dari dropdown
                    //                     const modelId = window.activeModelId || 'llama-3.3-70b-versatile';

                    //                     // Dapatkan konteks sekolah dari PHP (tambahkan ini)
                    //                     let schoolContext = '';
                    //                     try {
                    //                         const contextResponse = await fetch('get_school_context.php', {
                    //                             method: 'POST',
                    //                             headers: {
                    //                                 'Content-Type': 'application/json',
                    //                             },
                    //                             body: JSON.stringify({
                    //                                 query: userMessage
                    //                             })
                    //                         });

                    //                         if (contextResponse.ok) {
                    //                             const contextData = await contextResponse.json();
                    //                             if (contextData.status === 'success') {
                    //                                 schoolContextData = contextData.context;
                    //                                 console.log("Konteks sekolah berhasil diambil");
                    //                             }
                    //                         }
                    //                     } catch (error) {
                    //                         console.error("Error mengambil konteks sekolah:", error);
                    //                     }


                    //                     // Tetap support deep thinking mode
                    //                     const isDeepThinking = document.getElementById('deepThinkingToggle')?.checked || false;
                    //                     const selectedSystemMessage = isDeepThinking ? deepThinkingSystemMessage : systemMessage;

                    //                     // Log untuk debugging
                    //                     console.log(`Using model: ${modelId}, Deep thinking: ${isDeepThinking}`);

                    //                     // Kode yang sudah ada
                    //                     const docContent = window.documentContent || '';
                    //                     const projectContext = window.projectContext ? `
                    //     Berikut konteks project yang relevan:
                    //     ${window.projectContext}

                    //     gunakan informasi ini sebagai acuan utama dalam menjawab pertanyaan guru, fokus pada informasi ini,
                    //     jangan bahas yang lain kecuali guru membahas hal lainya.jika pertanyaan 
                    //     tidak terkait dengan konteks project, jawab seperti instruksi awal ya.
                    // ` : '';

                    //                     let contextMessage = [];
                    //                     if (docContent) {
                    //                         const chunks = docContent.match(/[^.!?]+[.!?]+/g) || [];
                    //                         const contextualized_chunks = chunks.join(' ').substring(0, 2000);
                    //                         contextMessage = [{
                    //                             role: "system",
                    //                             content: `Document context: ${contextualized_chunks}`
                    //                         }];
                    //                     }

                    //                     conversationHistory.push({
                    //                         role: "user",
                    //                         content: userMessage
                    //                     });

                    //                     if (conversationHistory.length > MAX_HISTORY * 2) {
                    //                         conversationHistory = conversationHistory.slice(-MAX_HISTORY * 2);
                    //                     }

                    //                     try {
                    //                         const messages = [{
                    //                                 role: "system",
                    //                                 content: systemMessage.content + (schoolContextData ? "\n\n" + schoolContextData : "")
                    //                             },
                    //                             ...contextMessage,
                    //                             ...contohDialog,
                    //                             ...conversationHistory
                    //                         ];

                    //                         const loadingEl = document.getElementById('loading');
                    //                         if (loadingEl) {
                    //                             loadingEl.textContent = `Menggunakan model ${modelId.split('-').slice(-3, -1).join(' ')}...`;
                    //                         }

                    //                         const response = await fetch(API_ENDPOINT, {
                    //                             method: 'POST',
                    //                             headers: {
                    //                                 'Authorization': `Bearer ${API_KEY}`,
                    //                                 'Content-Type': 'application/json'
                    //                             },
                    //                             body: JSON.stringify({
                    //                                 model: modelId,
                    //                                 messages: messages,
                    //                                 temperature: 0.7
                    //                             }),
                    //                             mode: 'cors'
                    //                         });

                    //                         if (!response.ok) {
                    //                             throw new Error(`HTTP error! status: ${response.status}`);
                    //                         }

                    //                         const data = await response.json();
                    //                         const aiResponse = data.choices[0].message.content;

                    //                         conversationHistory.push({
                    //                             role: "assistant",
                    //                             content: aiResponse
                    //                         });

                    //                         return aiResponse;
                    //                     } catch (error) {
                    //                         console.error('Error:', error);
                    //                         return 'Maaf, terjadi kesalahan saat berkomunikasi dengan AI. Coba lagi nanti atau refresh halaman.';
                    //                     }


                    //                 }
                </script>


                <div id="document-preview" class="document-preview">
                    <div class="preview-content text-center">
                        <h6 class="loading-text">Memproses dokumen...</h6>
                        <p class="loading-subtext"></p>
                    </div>
                </div>

                <style>
                    .document-preview {
                        position: fixed;
                        inset: 0;
                        background: rgba(255, 255, 255, 0.95);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(5px);
                    }

                    .preview-content {
                        animation: slideUp 0.3s ease;
                    }

                    .loading-animation {
                        position: relative;
                        width: 80px;
                        height: 80px;
                        margin: 0 auto;
                    }

                    .loading-circle {
                        width: 100%;
                        height: 100%;
                        border: 3px solid #da775633;
                        border-top-color: #da7756;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }

                    .loading-bar {
                        position: absolute;
                        bottom: -10px;
                        left: 0;
                        width: 100%;
                        height: 3px;
                        background: #da775633;
                        border-radius: 3px;
                        overflow: hidden;
                    }

                    .loading-bar .progress-bar {
                        width: 0%;
                        height: 100%;
                        background: #da7756;
                        border-radius: 3px;
                        animation: progress 2s ease infinite;
                    }

                    .loading-text {
                        font-size: 14px;
                        margin-bottom: 4px;
                        color: #333;
                    }

                    .loading-subtext {
                        font-size: 12px;
                        color: #666;
                        margin: 0;
                    }

                    @keyframes spin {
                        to {
                            transform: rotate(360deg);
                        }
                    }

                    @keyframes progress {
                        0% {
                            width: 0%;
                        }

                        50% {
                            width: 70%;
                        }

                        100% {
                            width: 100%;
                        }
                    }

                    @keyframes slideUp {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>

                <script>
                    // Function to show loading with dynamic text updates
                    function showDocumentLoading(filename) {
                        const preview = document.getElementById('document-preview');
                        const subtext = preview.querySelector('.loading-subtext');

                        preview.style.display = 'flex';

                        // Array of loading messages
                        const loadingStates = [
                            'Membaca dokumen...',
                            'Menganalisis konten...',
                            'Memproses data...',
                            'Hampir selesai...'
                        ];

                        let currentState = 0;

                        // Update loading message every 1.5 seconds
                        const messageInterval = setInterval(() => {
                            subtext.textContent = loadingStates[currentState];
                            currentState = (currentState + 1) % loadingStates.length;
                        }, 1500);

                        // Store the interval ID to clear it later
                        preview.dataset.intervalId = messageInterval;
                    }

                    // Function to hide loading
                    function hideDocumentLoading() {
                        const preview = document.getElementById('document-preview');

                        // Clear the message interval
                        if (preview.dataset.intervalId) {
                            clearInterval(parseInt(preview.dataset.intervalId));
                        }

                        preview.style.opacity = '0';
                        setTimeout(() => {
                            preview.style.display = 'none';
                            preview.style.opacity = '1';
                        }, 300);
                    }
                </script>

                <!-- Floating document container -->
                <div id="floating-docs-container" class="floating-docs-container d-flex gap-2 justify-content-center align-items-center text-truncate" style="display: none;">
                    <!-- Documents will be added here dynamically -->
                </div>

                <!-- modal untuk riwayat session -->
                <style>
                    .session-item {
                        cursor: pointer;
                        transition: all 0.2s ease;
                    }

                    .session-item:hover {
                        background-color: rgba(218, 119, 86, 0.1);
                    }

                    .session-title {
                        font-weight: 500;
                        color: #333;
                    }

                    .delete-session {
                        opacity: 0;
                        transition: opacity 0.2s ease;
                    }

                    .session-item:hover .delete-session {
                        opacity: 1;
                    }

                    .session-meta {
                        color: #6c757d;
                        font-size: 12px;
                    }

                    .chat-count {
                        background: rgba(218, 119, 86, 0.2);
                        color: rgb(218, 119, 86);
                        padding: 2px 8px;
                        border-radius: 12px;
                        font-size: 11px;
                    }
                </style>

                <div class="modal fade" id="historyModal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="fw-bold mb-1 w-100 text-center">Riwayat Chat</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3" id="historyList">
                                <!-- Sessions will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project List Modal -->
                <div class="modal fade" id="projectModal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <div class="text-center w-100">
                                    <h5 class="modal-title fw-bold mb-1">Pengaturan</h5>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <!-- memori custom -->
                                <div class="mb-4">
                                    <div class="list-group">
                                        <button class="list-group-item border-0 px-0  py-3 m-0 list-group-item-action d-flex justify-content-between align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customMemoriesModal">
                                            <div>
                                                <h6 class="fw-bold p-0 m-0">Memori Kustom</h6>
                                                <p class="p-0 m-0" style="font-size: 12px;">Memori kustom untuk membuat ruang interaksi mandiri dengan riwayat chat ataupun basis pengetahuan yang diberikan.</p>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 14px;"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- memori custom -->
                                <div class="">
                                    <div class="list-group">
                                        <button class="list-group-item border-0 px-0  py-3 m-0 list-group-item-action d-flex justify-content-between align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#aiInfo">
                                            <div>
                                                <h6 class="fw-bold p-0 m-0">Panduan Model AI</h6>
                                                <p class="p-0 m-0" style="font-size: 12px;">Panduan dalam memilih model dalam AI sesuai dengan kebutuhan Anda.</p>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 14px;"></i>
                                        </button>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>

                <style>
                    .category-btn {
                        background: #f8f9fa;
                        color: #6c757d;
                        border: none;
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 13px;
                    }

                    .category-btn:hover,
                    .category-btn.active {
                        background: rgb(218, 119, 86);
                        color: white;
                    }

                    #projectSearch:focus {
                        box-shadow: none;
                    }

                    .modal-content {
                        border-radius: 15px;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    }

                    .modal-header {
                        padding: 20px 30px;
                    }

                    .modal-body {
                        padding: 0 30px 30px 30px;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .modal-dialog {
                            margin: 10px;
                        }

                        .category-btn {
                            font-size: 12px;
                            padding: 5px 10px;
                        }
                    }
                </style>

                <!-- AI Info -->
                <!-- AI Info Modal -->
                <div class="modal fade" id="aiInfo">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 shadow">
                            <div class="modal-header w-100 text-center border-0">
                                <h5 class="modal-title fw-bold">Panduan Model AI</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="list-group" id="aiModelList">
                                            <button class="list-group-item list-group-item-action border-0 px-0 py-3 m-0 d-flex align-items-center" data-model="llama-3.3-70b">
                                                <img src="assets/llama.png" alt="LLaMA 3.3 70B" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">LLaMA 3.3 70B</h6>
                                                    <p class="text-muted mb-0" style="font-size: 13px;">Paling cerdas & lengkap</p>
                                                </div>
                                            </button>
                                            <button class="list-group-item list-group-item-action border-0 px-0 py-3 m-0 d-flex align-items-center" data-model="llama-3.1-8b">
                                                <img src="assets/llama.png" alt="LLaMA 3.1 8B" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">LLaMA 3.1 8B</h6>
                                                    <p class="text-muted mb-0" style="font-size: 13px;">Cepat & efisien</p>
                                                </div>
                                            </button>
                                            <button class="list-group-item list-group-item-action border-0 px-0 py-3 m-0 d-flex align-items-center" data-model="mixtral-8x7b">
                                                <img src="assets/mixtral.png" alt="Mixtral 8x7B" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">Mixtral 8x7B</h6>
                                                    <p class="text-muted mb-0" style="font-size: 13px;">Konteks panjang & akurat</p>
                                                </div>
                                            </button>
                                            <button class="list-group-item list-group-item-action border-0 px-0 py-3 m-0 d-flex align-items-center" data-model="deepseek-llama-70b">
                                                <img src="assets/deepseek.png" alt="DeepSeek Llama 70B" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">DeepSeek Llama 70B</h6>
                                                    <p class="text-muted mb-0" style="font-size: 13px;">Analisis mendalam</p>
                                                </div>
                                            </button>
                                            <button class="list-group-item list-group-item-action border-0 px-0 py-3 m-0 d-flex align-items-center" data-model="gemma2-9b">
                                                <img src="assets/google.png" alt="Gemma2 9B" class="me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">Gemma2 9B</h6>
                                                    <p class="text-muted mb-0" style="font-size: 13px;">Seimbang & efisien</p>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div id="aiModelDescription">
                                            <h6 class="fw-bold">Bantu saya dalam memilih model AI</h6>
                                            <p class="text-muted" style="font-size: 13px;">Memilih model AI yang tepat sangat penting agar performa sesuai dengan kebutuhan. Pilih model SAGA AI yang Anda inginkan untuk menampilkan ringkasan dari setiap model yang tersedia.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // loading screen 
                        // Sembunyikan loading screen setelah 1.5 detik (1000ms untuk loading + 500ms untuk fade out)
                        setTimeout(function() {
                            const loadingScreen = document.getElementById('loading-screen');
                            if (loadingScreen) {
                                loadingScreen.classList.add('fade-out');

                                // Hapus dari DOM setelah transisi selesai
                                setTimeout(function() {
                                    loadingScreen.style.display = 'none';
                                }, 500);
                            }
                        }, 1000);


                        const modelDescriptions = {
                            'llama-3.3-70b': {
                                title: 'LLaMA 3.3 70B',
                                description: 'LLaMA 3.3 70B adalah model AI paling canggih dengan pemahaman mendalam dan fleksibilitas tinggi. Model ini mampu menangani berbagai tugas dengan akurasi luar biasa, cocok untuk pekerjaan yang membutuhkan analisis kompleks dan kreativitas tinggi.',
                                examples: [
                                    'Menganalisis laporan bisnis dan riset ilmiah',
                                    'Membantu penulisan artikel, skrip, dan konten kreatif',
                                    'Memecahkan soal teknis yang kompleks'
                                ],
                                suitableFor: [
                                    'Data scientist & analis bisnis',
                                    'Penulis dan content creator',
                                    'Pengguna yang membutuhkan AI serbaguna'
                                ]
                            },
                            'llama-3.1-8b': {
                                title: 'LLaMA 3.1 8B',
                                description: 'LLaMA 3.1 8B adalah model AI yang cepat dan ringan, dirancang untuk respons instan dengan efisiensi tinggi. Model ini ideal untuk penggunaan sehari-hari yang membutuhkan kecepatan tanpa mengorbankan kualitas.',
                                examples: [
                                    'Menjawab pertanyaan cepat dalam chatbot',
                                    'Menyediakan saran teks dalam waktu nyata',
                                    'Melakukan pencarian informasi sederhana'
                                ],
                                suitableFor: [
                                    'Chatbot & asisten virtual',
                                    'Aplikasi yang membutuhkan respons cepat',
                                    'Pengguna dengan perangkat terbatas'
                                ]
                            },
                            'mixtral-8x7b': {
                                title: 'Mixtral 8x7B',
                                description: 'Mixtral 8x7B unggul dalam memahami konteks panjang hingga 32K token. Model ini sangat cocok untuk membaca, menganalisis, dan meringkas dokumen besar dengan akurasi tinggi.',
                                examples: [
                                    'Menganalisis dokumen hukum atau penelitian panjang',
                                    'Meringkas artikel berita atau jurnal akademik',
                                    'Menjalankan diskusi yang membutuhkan pemahaman mendalam'
                                ],
                                suitableFor: [
                                    'Akademisi & peneliti',
                                    'Pengacara & analis data',
                                    'Pengguna yang sering bekerja dengan teks panjang'
                                ]
                            },
                            'deepseek-llama-70b': {
                                title: 'DeepSeek Llama 70B',
                                description: 'DeepSeek Llama 70B adalah model AI yang dirancang untuk analisis mendalam dan pemecahan masalah kompleks. Model ini sangat cocok untuk riset, eksplorasi informasi, dan tugas yang membutuhkan pemahaman detail.',
                                examples: [
                                    'Menganalisis data finansial atau teknis',
                                    'Meneliti tren industri berdasarkan data historis',
                                    'Memecahkan soal matematika atau algoritma tingkat lanjut'
                                ],
                                suitableFor: [
                                    'Analis data & riset pasar',
                                    'Teknisi & insinyur',
                                    'Pengguna yang membutuhkan pemahaman data kompleks'
                                ]
                            },
                            'gemma2-9b': {
                                title: 'Gemma2 9B',
                                description: 'Gemma2 9B adalah model AI yang menyeimbangkan antara kecepatan dan akurasi. Model ini sangat cocok untuk tugas sehari-hari yang membutuhkan performa stabil dan efisien.',
                                examples: [
                                    'Menulis email dan ringkasan dokumen',
                                    'Menyediakan rekomendasi berdasarkan preferensi pengguna',
                                    'Membantu dalam tugas administratif dan organisasi'
                                ],
                                suitableFor: [
                                    'Profesional & pekerja kantoran',
                                    'Asisten virtual & customer service',
                                    'Pengguna yang menginginkan AI yang serbaguna & ringan'
                                ]
                            }
                        };


                        const aiModelList = document.getElementById('aiModelList');
                        const aiModelDescription = document.getElementById('aiModelDescription');

                        aiModelList.addEventListener('click', function(e) {
                            const modelButton = e.target.closest('.list-group-item');
                            if (modelButton) {
                                const modelId = modelButton.getAttribute('data-model');
                                const modelInfo = modelDescriptions[modelId];

                                if (modelInfo) {
                                    // Format daftar contoh penggunaan & cocok untuk
                                    const examplesList = modelInfo.examples
                                        .map(example => `<li>${example}</li>`)
                                        .join('');

                                    const suitableForList = modelInfo.suitableFor
                                        .map(suitable => `<li>${suitable}</li>`)
                                        .join('');

                                    // Masukkan ke dalam tampilan
                                    aiModelDescription.innerHTML = `
                <h6 class="fw-bold">${modelInfo.title}</h6>
                <p class="text-muted" style="font-size: 13px;">${modelInfo.description}</p>
                <strong>Contoh Penggunaan:</strong>
                <ul>${examplesList}</ul>
            `;
                                }
                            }
                        });

                    });
                </script>

                <!-- Custom Memories Modal -->
                <div class="modal fade" id="customMemoriesModal">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header border-0 px-4">
                                <div>
                                    <h5 class="modal-title fw-bold mb-1">Memori Kustom</h5>
                                    <p class="text-muted mb-0" style="font-size: 13px;">Koleksi memori yang dapat digunakan SAGA untuk memberikan respon lebih personal</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-4">
                                <!-- buat baru -->
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="bi bi-bounding-box-circles" style="font-size: 15px;"></span>
                                        <h6 style="font-size: 15px;" class="p-0 m-0">Project Anda</h6>
                                    </div>
                                    <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#newProjectModal"
                                        style="background-color: rgb(218, 119, 86); border:0;">
                                        <i class="bi bi-plus-circle"></i>
                                        <p class="p-0 m-0" style="font-size: 15px;">Buat Memori Baru</p>
                                    </button>
                                </div>


                                <!-- Memory Cards Grid -->
                                <div class="row g-4" id="projectList">
                                    <!-- Sample Memory Card -->
                                    <div class="col-md-4">
                                        <div class="card h-100 border shadow-sm hover-card">
                                            <div class="card-body p-4">
                                                <div class="d-flex justify-content-between mb-3">
                                                    <div class="memory-icon rounded-circle p-2 d-flex align-items-center justify-content-center"
                                                        style="background-color: rgba(218, 119, 86, 0.1); width: 40px; height: 40px;">
                                                        <i class="bi bi-book" style="color: rgb(218, 119, 86);"></i>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                            <li><a class="dropdown-item" href="#"><i class="bi bi-archive me-2"></i>Arsipkan</a></li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <h6 class="card-title fw-bold mb-2">Nama Memori</h6>
                                                <p class="card-text text-muted mb-3" style="font-size: 13px;">
                                                    Deskripsi singkat tentang memori ini dan apa kegunaannya...
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-light text-dark rounded-pill">
                                                        <i class="bi bi-file-text me-1"></i>
                                                        5 dokumen
                                                    </span>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        style="border-color: rgb(218, 119, 86); color: rgb(218, 119, 86);">
                                                        <i class="bi bi-lightning-charge-fill"></i>
                                                        Gunakan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div class="text-center p-5 d-none" id="emptyState">
                                    <i class="bi bi-folder-plus display-4 text-muted mb-4"></i>
                                    <h6 class="fw-bold text-muted">Belum Ada Memori</h6>
                                    <p class="text-muted mb-4" style="font-size: 13px;">
                                        Buat memori kustom untuk meningkatkan pemahaman SAGA AI terhadap kebutuhan Anda
                                    </p>
                                    <button class="btn btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#newProjectModal"
                                        style="background-color: rgb(218, 119, 86); border:0;">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Buat Memori Pertama
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .hover-card {
                        transition: all 0.3s ease;
                        border-radius: 12px;
                    }

                    .hover-card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
                    }

                    .memory-icon {
                        transition: all 0.3s ease;
                    }

                    .hover-card:hover .memory-icon {
                        transform: scale(1.1);
                    }

                    .btn-outline-primary:hover {
                        background-color: rgb(218, 119, 86) !important;
                        color: white !important;
                    }

                    #memorySearch:focus {
                        box-shadow: none;
                        border-color: rgb(218, 119, 86);
                    }


                    @media (max-width: 768px) {
                        .modal-dialog {
                            margin: 1rem;
                        }

                        .col-md-4 {
                            margin-bottom: 1rem;
                        }
                    }
                </style>



                <!-- New Project Modal -->
                <div class="modal fade" id="newProjectModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border" style="border-radius: 15px;">
                            <div class="modal-header border-0 px-4 pt-4">
                                <div>
                                    <h5 class="modal-title fw-bold mb-1">Buat Memori Baru</h5>
                                    <p class="text-muted mb-0" style="font-size: 13px;">SAGA akan menggunakan data memori yang telah diupload dalam merespon Anda</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body px-4 pb-4">
                                <form id="projectForm" enctype="multipart/form-data">
                                    <div class="mb-4">
                                        <label class="form-label" style="font-size: 14px;">Apa yang sedang Anda kerjakan saat ini?</label>
                                        <input type="text"
                                            class="form-control border"
                                            name="project_name"
                                            id="project_name"
                                            placeholder="Masukkan nama project Anda"
                                            style="border-radius: 10px; padding: 12px 16px;"
                                            required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" style="font-size: 14px;">Apa yang ingin Anda capai?</label>
                                        <textarea class="form-control border"
                                            name="description"
                                            id="description"
                                            placeholder="Berikan deskripsi project Anda, tujuan, subjek, dan lainya"
                                            style="border-radius: 10px; padding: 12px 16px;"
                                            rows="3"></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                            <div>
                                                <label class="form-label mb-0 fw-bold" style="font-size: 14px;">
                                                    <i class="bi bi-book me-1"></i>
                                                    Pengetahuan Proyek
                                                </label>
                                                <p class="text-muted mb-0" style="font-size: 12px;">
                                                    Data project dapat berisi instruksi formal untuk mengarahkan SMAGA AI atau
                                                    digunakan untuk mengupload dokumen PDF/Word sebagai basis pengetahuan tambahan
                                                </p>
                                            </div>
                                        </div>

                                        <div id="knowledgeContainer" class="d-flex flex-column gap-3">
                                            <!-- Knowledge fields added via JavaScript -->
                                            <script>
                                                // Add default knowledge field on load
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    addKnowledgeField();
                                                });
                                            </script>
                                        </div>

                                        <style>
                                            .knowledge-field {
                                                animation: slideIn 0.3s ease-out;
                                                transition: all 0.3s ease;
                                                background: white;
                                                border-radius: 10px;
                                                border: 1px solid #dee2e6;
                                            }

                                            .knowledge-field:hover {
                                                transform: translateX(5px);
                                                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                                            }

                                            .knowledge-field .input-group {
                                                padding: 10px;
                                            }

                                            .knowledge-field .form-select {
                                                border-radius: 8px;
                                                font-size: 13px;
                                                border: 1px solid #dee2e6;
                                            }

                                            .knowledge-field .btn-danger {
                                                border-radius: 8px;
                                                padding: 8px 12px;
                                                background: #dc3545;
                                                transition: all 0.2s;
                                            }

                                            .knowledge-field .btn-danger:hover {
                                                background: #bb2d3b;
                                                transform: scale(1.05);
                                            }

                                            @keyframes slideIn {
                                                from {
                                                    opacity: 0;
                                                    transform: translateX(-20px);
                                                }

                                                to {
                                                    opacity: 1;
                                                    transform: translateX(0);
                                                }
                                            }

                                            @keyframes fadeOut {
                                                to {
                                                    opacity: 0;
                                                    transform: translateX(20px);
                                                }
                                            }

                                            .knowledge-field.removing {
                                                animation: fadeOut 0.3s ease-in forwards;
                                            }

                                            /* Tambahkan ini ke dalam style yang sudah ada */
                                            .ai-thinking {
                                                color: #6c757d !important;
                                                font-size: 10px !important;
                                                margin-bottom: 8px !important;
                                                font-style: italic !important;
                                                opacity: 0.8 !important;
                                            }
                                        </style>
                                    </div>

                                    <button type="submit"
                                        class="btn text-white w-100 d-flex align-items-center justify-content-center gap-2"
                                        style="border-radius: 10px; padding: 12px; background-color:rgb(218, 119, 86);">
                                        <i class="bi bi-save"></i>
                                        <span>Simpan Project</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .form-control:focus {
                        border-color: rgb(218, 119, 86);
                        box-shadow: none;
                    }

                    .modal-content {
                        background-color: #fff;
                    }

                    .knowledge-field {
                        transition: all 0.3s ease;
                    }

                    .knowledge-field:hover {
                        transform: translateY(-2px);
                    }

                    .form-control-memori {
                        border-radius: 0;
                    }
                </style>



                <!-- script project -->
                <script>
                    // load proyek saat modal di buka
                    $('#projectModal').on('show.bs.modal', function() {
                        loadProjects();
                    });

                    // Fungsi untuk menambah field pengetahuan
                    function addKnowledgeField(type = 'text') {
                        const container = document.getElementById('knowledgeContainer');
                        const id = Date.now();

                        const field = document.createElement('div');
                        field.className = 'mb-3 knowledge-field';
                        field.innerHTML = `
    <div class="input-group">
      <select class="form-select input-type" style="max-width:120px">
        <option value="text">Teks</option>
        <!-- <option value="file">Dokumen</option> -->
      </select>
      
      <div class="flex-grow-1 ms-2">
        <textarea class="form-control form-control-memori text-input" 
                  rows="2" 
                  placeholder="Masukkan teks pengetahuan"
                  style="${type === 'file' ? 'display:none' : ''}"></textarea>
                  
        <!--  <input type="file" 
               class="form-control file-input" 
               accept=".pdf,.doc,.docx,.txt"
               style="${type === 'text' ? 'display:none' : ''}"> -->
      </div>
      
      <button class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  `;

                        // Handle perubahan tipe input
                        field.querySelector('.input-type').addEventListener('change', (e) => {
                            const isFile = e.target.value === 'file';
                            field.querySelector('.text-input').style.display = isFile ? 'none' : 'block';
                            field.querySelector('.file-input').style.display = isFile ? 'block' : 'none';
                        });

                        container.appendChild(field);
                    }

                    // Di fungsi loadProjects(), perbaiki penanganan error:
                    async function loadProjects() {
                        try {
                            const response = await fetch('get_project.php');
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const projects = await response.json();
                            const container = document.getElementById('projectList');

                            if (!container) {
                                console.error('Project list container not found');
                                return;
                            }

                            container.innerHTML = '';

                            if (!projects.length) {
                                document.getElementById('emptyState')?.classList.remove('d-none');
                                return;
                            }

                            document.getElementById('emptyState')?.classList.add('d-none');
                            projects.forEach(project => {
                                const projectEl = document.createElement('div');
                                projectEl.className = 'col-md-6 rounded-3';
                                projectEl.innerHTML = `
                                        <div class="card h-100 border" style="border-radius: 12px;">
                                            <div class="card-body p-4">
                                                <h5 class="card-title mb-2 fw-bold">${project.project_name}</h5>
                                                <p class="card-text text-muted mb-3" style="font-size: 12px">
                                                    ${project.description || 'Tidak ada deskripsi'}
                                                </p>
                                                <div class="d-flex gap-2 justify-content-between">
                                                    <button class="btn btn-sm flex-fill buttonGunakan" 
                                                            onclick="selectProject(${project.id})" 
                                                            style="font-size: 12px; border-radius: 8px;">
                                                        <i class="bi bi-play-circle me-1"></i> Gunakan
                                                    </button>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light" 
                                                                data-bs-toggle="dropdown" 
                                                                style="font-size: 12px; border-radius: 8px;">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <button class="dropdown-item text-danger" 
                                                                        onclick="deleteProject(${project.id})">
                                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;

                                // Add iOS-style hover effect
                                const style = document.createElement('style');
                                style.textContent = `
                                        .hover-card {
                                            transition: all 0.2s ease;
                                            border: 1px solid #dee2e6;
                                        }
                                        .hover-card:hover {
                                            border-color: #adb5bd;
                                            transform: translateY(-2px);
                                        }
                                        .btn:active {
                                            transform: scale(0.95);
                                        }
                                        .buttonGunakan {
                                            background-color:rgb(218, 119, 86); 
                                            color: white; 
                                            font-size: 12px; 
                                            border-radius: 8px;
                                            justify-content: center;
                                            align-items: center;
                                        }
                                        .buttonGunakan:hover {
                                            background-color: #da7756;
                                            color:white;
                                        }
                                    `;
                                document.head.appendChild(style);
                                container.appendChild(projectEl);
                            });
                        } catch (error) {
                            console.error('Error loading projects:', error);
                            alert('Gagal memuat daftar project: ' + error.message);
                        }
                    }


                    async function selectProject(projectId) {
                        try {
                            const response = await fetch(`get_project_context.php?project_id=${projectId}`);
                            if (!response.ok) throw new Error('Network response was not ok');

                            const result = await response.json();
                            if (!result.success) throw new Error(result.error || "Gagal memuat project");

                            document.querySelectorAll('.project-badge').forEach(el => el.remove());

                            // Create badge element
                            const badge = document.createElement('div');
                            badge.className = 'btn rounded-pill button-style p-0 project-badge d-flex align-items-center gap-2 p-2 animate__animated animate__fadeIn';
                            badge.style.cssText = 'background-color: rgb(219, 213, 183); border-radius: 20px;';

                            badge.innerHTML = `
                                    <i class="bi bi-folder" style="font-size: 16px;"></i>
                                    <p class="p-0 m-0 text-dark" style="font-size: 12px; cursor: pointer;">${result.project_name}</p>
                                    <i class="bi bi-x-circle close-icon" 
                                       style="cursor: pointer; opacity: 0.6;" 
                                       onclick="removeProjectBadge()">
                                    </i>
                                `;

                            // Append badge after deep thinking toggle
                            const settingsDiv = document.querySelector('.deep-thinking-toggle').parentNode;
                            settingsDiv.appendChild(badge);

                            window.activeProject = {
                                id: projectId,
                                name: result.project_name,
                                contents: result.contents
                            };

                            systemMessage.content = `${systemMessage.content.split('\n\nKonteks Project Aktif')[0]}\n\nKonteks Project Aktif (${result.project_name}):\n${result.contents.map(c => {
                                    if(c.content_type === 'text') return c.content;
                                    if(c.content_type === 'file') return `[Dokumen: ${c.file_path?.split('/').pop()}]`;
                                    return '';
                                }).join("\n")}`;

                            // Close both modals
                            const projectModal = bootstrap.Modal.getInstance(document.getElementById('projectModal'));
                            const customMemoriesModal = bootstrap.Modal.getInstance(document.getElementById('customMemoriesModal'));

                            if (projectModal) projectModal.hide();
                            if (customMemoriesModal) customMemoriesModal.hide();

                        } catch (error) {
                            console.error("Error:", error);
                            alert(error.message);
                        }
                    }

                    async function deleteProject(projectId) {
                        if (!confirm('Apakah Anda yakin ingin menghapus project ini?')) return;

                        try {
                            const response = await fetch('delete_project.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    project_id: projectId
                                })
                            });

                            const result = await response.json();
                            if (result.success) {
                                if (window.activeProject?.id === projectId) {
                                    removeProjectBadge();
                                }
                                await loadProjects();
                            } else {
                                throw new Error(result.error || 'Gagal menghapus project');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Gagal menghapus project: ' + error.message);
                        }
                    }


                    function removeProjectBadge() {
                        const badges = document.querySelectorAll('.project-badge');
                        badges.forEach(badge => {
                            badge.classList.add('animate__fadeOut');
                            setTimeout(() => badge.remove(), 500);
                        });
                        window.activeProject = null;
                        systemMessage.content = systemMessage.content.split('\n\nKonteks Project Aktif')[0];
                    }

                    async function editProject(projectId) {
                        try {
                            const response = await fetch(`get_project_details.php?id=${projectId}`);
                            if (!response.ok) throw new Error('Failed to fetch project details');

                            const data = await response.json();
                            if (!data.success) throw new Error(data.error);

                            // Fill form with project details
                            document.getElementById('project_name').value = data.project.project_name;
                            document.getElementById('description').value = data.project.description;

                            // Clear existing knowledge fields
                            const container = document.getElementById('knowledgeContainer');
                            container.innerHTML = '';

                            // Add existing knowledge fields
                            data.contents.forEach(content => {
                                const field = document.createElement('div');
                                field.className = 'knowledge-field animate__animated animate__fadeInDown';

                                field.innerHTML = `
                <div class="input-group">
                    <select class="form-select input-type" style="max-width:120px" onchange="toggleInputType(this)">
                        <option value="text" ${content.content_type === 'text' ? 'selected' : ''}>Teks</option>
                        <option value="file" ${content.content_type === 'file' ? 'selected' : ''}>Dokumen</option>
                    </select>
                    <div class="flex-grow-1 ms-2">
                        <textarea class="form-control text-input" rows="2" 
                            placeholder="Masukkan teks pengetahuan"
                            style="display: ${content.content_type === 'text' ? 'block' : 'none'}">${content.content || ''}</textarea>
                        <input type="file" class="form-control file-input" 
                            accept=".pdf,.doc,.docx,.txt"
                            style="display: ${content.content_type === 'file' ? 'block' : 'none'}">
                        ${content.content_type === 'file' && content.file_path ? `<div class="current-file text-muted mt-1">${content.file_path}</div>` : ''}
                    </div>
                    <button class="btn btn-danger" onclick="removeKnowledgeField(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

                                container.appendChild(field);
                            });

                            // Add project ID to form
                            const form = document.getElementById('projectForm');
                            form.dataset.projectId = projectId;

                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('newProjectModal'));
                            modal.show();

                        } catch (error) {
                            console.error('Error:', error);
                            alert('Failed to load project details');
                        }
                    }

                    // Add toggle function for input types
                    function toggleInputType(select) {
                        const parentField = select.closest('.knowledge-field');
                        const textInput = parentField.querySelector('.text-input');
                        const fileInput = parentField.querySelector('.file-input');

                        if (select.value === 'text') {
                            textInput.style.display = '';
                            fileInput.style.display = 'none';
                        } else {
                            textInput.style.display = 'none';
                            fileInput.style.display = '';
                        }
                    }

                    // Update project form submission
                    document.getElementById('projectForm').addEventListener('submit', async (e) => {
                        e.preventDefault();

                        const formData = new FormData();
                        const projectId = e.target.dataset.projectId;

                        if (projectId) {
                            formData.append('project_id', projectId);
                        }

                        formData.append('project_name', document.getElementById('project_name').value);
                        formData.append('description', document.getElementById('description').value);

                        // Add knowledge fields
                        const knowledgeFields = document.querySelectorAll('.knowledge-field');
                        knowledgeFields.forEach((field, index) => {
                            const type = field.querySelector('.input-type').value;
                            formData.append(`knowledge[${index}][type]`, type);

                            if (type === 'text') {
                                formData.append(`knowledge[${index}][content]`, field.querySelector('.text-input').value);
                            } else {
                                const fileInput = field.querySelector('.file-input');
                                if (fileInput.files.length > 0) {
                                    formData.append(`knowledge[${index}][file]`, fileInput.files[0]);
                                }
                            }
                        });

                        try {
                            const url = projectId ? 'update_project.php' : 'save_project.php';
                            const response = await fetch(url, {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) {
                                $('#newProjectModal').modal('hide');
                                loadProjects();
                                e.target.reset();
                                delete e.target.dataset.projectId;
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Failed to save project');
                        }
                    });

                    document.getElementById('projectForm').addEventListener('submit', async (e) => {
                        e.preventDefault();

                        const formData = new FormData();

                        // Tambahkan field utama
                        formData.append('project_name', document.getElementById('project_name').value);
                        formData.append('description', document.getElementById('description').value);

                        // Tambahkan knowledge fields
                        const knowledgeFields = document.querySelectorAll('.knowledge-field');
                        knowledgeFields.forEach((field, index) => {
                            const type = field.querySelector('.input-type').value;
                            formData.append(`knowledge[${index}][type]`, type);

                            if (type === 'text') {
                                formData.append(`knowledge[${index}][content]`,
                                    field.querySelector('.text-input').value);
                            } else {
                                formData.append(`knowledge[${index}][file]`,
                                    field.querySelector('.file-input').files[0]);
                            }
                        });

                        // Kirim request
                        try {
                            const response = await fetch('save_project.php', {
                                method: 'POST',
                                body: formData // Tidak perlu header Content-Type untuk FormData
                            });

                            if (response.ok) {
                                $('#newProjectModal').modal('hide');
                                loadProjects();
                                e.target.reset();
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    });
                </script>

                <!-- script history -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Periksa apakah ada active_chat_session
                        fetch('check_active_session.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.has_active_session) {
                                    currentSessionId = data.session_id;
                                    console.log('Active session loaded:', currentSessionId);
                                } else {
                                    currentSessionId = null;
                                    console.log('No active session');
                                }
                            })
                            .catch(error => {
                                console.error('Error checking session:', error);
                            });
                    });


                    function loadHistory() {
                        fetch('chat_sessions.php')
                            .then(response => response.json())
                            .then(sessions => {
                                const historyList = document.getElementById('historyList');
                                if (!historyList) return;

                                historyList.innerHTML = '';

                                if (!Array.isArray(sessions) || sessions.length === 0) {
                                    historyList.innerHTML = `
                    <div class="text-center p-4">
                        <i class="bi bi-chat-dots text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Belum ada riwayat chat</p>
                    </div>
                `;
                                    return;
                                }

                                sessions.forEach(session => {
                                    if (!session) return;

                                    const date = new Date(session.created_at);
                                    const formattedDate = new Intl.DateTimeFormat('id-ID', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    }).format(date);

                                    const sessionDiv = document.createElement('div');
                                    sessionDiv.className = 'session-item p-3 rounded-4';
                                    sessionDiv.innerHTML = `
                    <div class="d-flex align-items-center gap-3">
                        <div class="session-icon">
                            <i class="bi bi-chat-text text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="session-title mb-1">${session.title || 'Chat ' + formattedDate}</div>
                            <div class="session-meta d-flex align-items-center gap-2">
                                <span>${formattedDate}</span>
                                <span class="chat-count">${session.message_count || 0} pesan</span>
                            </div>
                        </div>
                        <button class="btn border btn-sm text-danger delete-session" 
                                onclick="deleteSession(${session.id}, event)">
                            <i class="bi bi-trash2"></i>
                        </button>
                    </div>
                `;

                                    sessionDiv.onclick = () => loadSessionChats(session.id);
                                    historyList.appendChild(sessionDiv);
                                });
                            })
                            .catch(error => {
                                console.error('Error loading history:', error);
                                if (document.getElementById('historyList')) {
                                    document.getElementById('historyList').innerHTML = `
                    <div class="text-center p-4 text-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        <p class="mt-2 mb-0">Gagal memuat riwayat chat</p>
                    </div>
                `;
                                }
                            });
                    }

                    async function createNewChatSession() {
                        try {
                            // Tampilkan loading jika perlu
                            showLoader();

                            // Panggil API untuk membuat session baru
                            const response = await fetch('create_new_session.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                }
                            });

                            const result = await response.json();

                            if (result.success) {
                                // Reset semua data client-side
                                conversationHistory = [];
                                window.documentContent = '';
                                if (typeof activeDocuments !== 'undefined') {
                                    activeDocuments.clear();
                                }

                                // Reset UI
                                chatContainer.innerHTML = '';
                                document.getElementById('user-input').value = '';

                                // Tampilkan welcome message
                                const welcomeMessage = document.getElementById('welcomeMessage');
                                if (welcomeMessage) {
                                    welcomeMessage.style.display = 'block';
                                    welcomeMessage.style.opacity = '1';
                                    welcomeMessage.style.transform = 'translate(-50%, -50%)';
                                }

                                // Reset first message
                                const firstMessage = document.getElementById('firstMessage');
                                if (firstMessage) {
                                    firstMessage.classList.add('d-none');
                                    if (firstMessage.querySelector('.first-message-text')) {
                                        firstMessage.querySelector('.first-message-text').textContent = '';
                                    }
                                }

                                // Tampilkan rekomendasi
                                const recommendationContainer = document.querySelector('.recommendation-container');
                                if (recommendationContainer) {
                                    recommendationContainer.classList.remove('d-none', 'hide');
                                }

                                // Set sesi baru
                                currentSessionId = result.session_id;
                                isFirstChat = true;

                                console.log('New chat session created:', result.session_id);
                            } else {
                                console.error('Failed to create new session:', result.error);
                            }
                        } catch (error) {
                            console.error('Error creating new session:', error);
                        } finally {
                            hideLoader();
                        }
                    }

                    function loadSessionChats(sessionId) {
                        if (!sessionId) return;

                        currentSessionId = sessionId;
                        showLoader();
                        chatContainer.innerHTML = '';

                        // Sembunyikan recommendation container
                        document.querySelector('.recommendation-container').classList.add('d-none');

                        // Sembunyikan welcome message
                        const welcomeMessage = document.getElementById('welcomeMessage');
                        if (welcomeMessage) {
                            welcomeMessage.style.opacity = '0';
                            welcomeMessage.style.transform = 'translate(-50%, -60%)';
                            setTimeout(() => {
                                welcomeMessage.style.display = 'none';
                            }, 300);
                        }

                        // Muat pesan chat
                        fetch(`get_session_messages.php?session_id=${sessionId}`)
                            .then(response => response.json())
                            .then(messages => {
                                if (Array.isArray(messages) && messages.length > 0) {
                                    // Update first message dengan pesan pertama dari riwayat
                                    const firstMessage = messages[0];
                                    if (firstMessage && firstMessage.pesan) {
                                        updateFirstMessage(firstMessage.pesan);
                                    }

                                    // Flag untuk melacak apakah ada canvas yang perlu ditampilkan
                                    let canvasContent = null;
                                    let hasCanvasMessages = false;

                                    // Tampilkan semua pesan
                                    messages.forEach((message, index) => {
                                        if (message && message.pesan) {
                                            addHistoryMessage('user', message.pesan);
                                        }

                                        if (message && message.respons) {
                                            // Cek apakah pesan ini menggunakan canvas
                                            if (message.uses_canvas == 1) {
                                                hasCanvasMessages = true;

                                                // Tampilkan pengantar di chat
                                                addHistoryMessage('ai', 'Jawaban telah disiapkan. Silakan lihat di canvas untuk tampilan lengkap.');

                                                // Simpan konten untuk canvas (simpan konten dari pesan terakhir yang menggunakan canvas)
                                                canvasContent = message.respons;

                                                // Tambahkan tombol canvas
                                                addCanvasButton('konten');
                                            } else {
                                                // Pesan normal - tampilkan lengkap
                                                addHistoryMessage('ai', message.respons);
                                            }
                                        }
                                    });

                                    // Muat versi canvas jika ada
                                    // TAMBAHKAN: Load canvas versions setelah memuat pesan
                                    if (hasCanvasMessages) {
                                        loadCanvasVersions(sessionId).then(versions => {
                                            if (versions.length > 0) {
                                                // Set canvas versions dari data yang dimuat
                                                window.canvasVersions = versions.map(v => v.content);
                                                window.currentVersionIndex = window.canvasVersions.length - 1;

                                                // Update UI tombol navigasi
                                                updateVersionNavButtons();

                                                // Tampilkan canvas dengan konten terakhir
                                                if (canvasContent) {
                                                    // Tandai konten sebagai sudah di-type untuk mencegah animasi ulang
                                                    window.contentTyped = true;
                                                    window.lastTypedContent = canvasContent;

                                                    // Tampilkan canvas setelah delay singkat
                                                    setTimeout(() => {
                                                        showCanvas(canvasContent, 'konten', false);
                                                    }, 500);
                                                }
                                            }
                                        });
                                    }
                                } else {
                                    addHistoryMessage('ai', 'Tidak ada pesan dalam chat ini');
                                }
                            })
                            .catch(error => {
                                console.error('Error in loadSessionChats:', error);
                                addHistoryMessage('ai', 'Maaf, gagal memuat pesan chat');
                            })
                            .finally(() => {
                                hideLoader();
                                $('#historyModal').modal('hide');
                            });
                    }

                    function startNewSession() {
                        // Simpan state bahwa ini adalah sesi baru dalam localStorage
                        localStorage.setItem('new_session', 'true');
                        // Refresh halaman
                        window.location.reload();
                    }

                    // Tambahkan pada event DOMContentLoaded
                    document.addEventListener('DOMContentLoaded', function() {
                        // Periksa apakah ini adalah sesi baru
                        if (localStorage.getItem('new_session') === 'true') {
                            // Hapus flag
                            localStorage.removeItem('new_session');

                            // Reset status sesi aktif
                            currentSessionId = null;

                            // Lakukan panggilan AJAX untuk me-reset sesi di server
                            fetch('reset_session.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                }
                            }).then(response => {
                                console.log('Session reset successfully');
                            }).catch(error => {
                                console.error('Error resetting session:', error);
                            });
                        }
                    });


                    // Fungsi khusus untuk menambahkan pesan riwayat tanpa animasi
                    function addHistoryMessage(sender, text) {
                        const messageWrapper = document.createElement('div');
                        messageWrapper.classList.add(
                            'd-flex',
                            'mb-3',
                            sender === 'user' ? 'justify-content-end' : 'justify-content-start'
                        );

                        const formattedText = formatText(text);

                        messageWrapper.innerHTML = `
        <div class="d-flex chat-message align-items-center pt-1 pb-1 p-2 rounded-4 ${sender === 'user' ? 'flex-row-reverse' : ''}" 
            style="background-color: ${sender === 'user' ? 'rgb(239, 239, 239)' : 'transparent'}; 
                max-width: ${sender === 'user' ? '30rem' : '40rem'}">
            <img src="${sender === 'user' ? userImage : aiImage}" 
                class="chat-profile bg-white ms-2 me-2 rounded-circle" 
                alt="${sender} profile" 
                style="width: 20px; height: 20px; object-fit: cover;">
            <div class="chat-bubble rounded p-2 align-content-center"
                style="font-size: 13px; ${sender === 'ai' ? 'background-color: transparent; width: 100%' : ''}">
                ${formattedText}
            </div>
        </div>
    `;

                        chatContainer.appendChild(messageWrapper);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }

                    // Tambahkan event listener untuk menampilkan kembali rekomendasi saat chat baru dimulai
                    document.getElementById('user-input').addEventListener('input', function() {
                        const recommendationContainer = document.querySelector('.recommendation-container');
                        if (recommendationContainer.classList.contains('d-none') && this.value.trim() === '') {
                            recommendationContainer.classList.remove('d-none');
                        }
                    });


                    async function deleteSession(sessionId, event) {
                        event.stopPropagation();

                        // Get chat title from the session item
                        const sessionItem = event.target.closest('.session-item');
                        const chatTitle = sessionItem.querySelector('.session-title').textContent;

                        // Hide history modal first
                        const historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                        historyModal.hide();

                        // Create and show delete confirmation modal
                        const modalHtml = `
                            <div class="ios-modal modal fade" id="deleteModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow">
                                        <div class="modal-body p-4 text-center">
                                            <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                                            <h5 class="mb-3 fw-bold">Hapus Percakapan</h5>
                                            <p class="mb-4">Apakah Anda yakin ingin menghapus percakapan <span class="fw-bold">${chatTitle} </span> ?</p>
                                            <p class="text-muted p-0 m-0" style="font-size:12px;"> Seluruh percakapan, analisa, pengetahuan SAGA dalam percakapan ini akan dihapus dan tidak dapat dikembalikan.</p>
                                            <div class="d-flex gap-2 mt-3 justify-content-center">
                                                <button class="btn btn-lg border btn-light w-100" onclick="cancelDelete()">Batal</button>
                                                <button class="btn btn-lg btn-danger w-100" id="confirmDelete">Hapus</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;

                        // Add modal to document
                        document.body.insertAdjacentHTML('beforeend', modalHtml);

                        // Get modal element
                        const modalEl = document.getElementById('deleteModal');
                        const modal = new bootstrap.Modal(modalEl);

                        // Show modal
                        modal.show();

                        // Handle delete confirmation
                        document.getElementById('confirmDelete').onclick = async () => {
                            try {
                                const response = await fetch('delete_chat.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        session_id: sessionId
                                    })
                                });

                                if ((await response.json()).success) {
                                    modal.hide();
                                    loadHistory();
                                    historyModal.show(); // Show history modal again after deletion
                                }
                            } catch (error) {
                                console.error('Error:', error);
                            }
                        };

                        // Add cancel delete function
                        window.cancelDelete = () => {
                            modal.hide();
                            historyModal.show(); // Show history modal again when cancelled
                        };

                        // Remove modal from DOM after it's hidden
                        modalEl.addEventListener('hidden.bs.modal', () => {
                            modalEl.remove();
                        });
                    }

                    // Add this after your existing JavaScript
                    document.addEventListener('DOMContentLoaded', () => {
                        const welcomeContainer = document.getElementById('welcomeContainer');
                        // console.log('DOM Loaded, checking elements:');
                        // console.log('Chat container exists:', !!document.getElementById('chat-container'));
                        // console.log('First message element exists:', !!document.getElementById('firstMessage'));


                        // Hide welcome message when user starts typing
                        document.getElementById('user-input').addEventListener('input', () => {
                            if (welcomeContainer) {
                                welcomeContainer.style.opacity = '0';
                                welcomeContainer.style.transform = 'translate(-50%, -60%)';
                                setTimeout(() => {
                                    welcomeContainer.style.display = 'none';
                                }, 300);
                            }
                        });

                        // Also hide when user clicks send
                        document.getElementById('send-button').addEventListener('click', () => {
                            if (welcomeContainer) {
                                welcomeContainer.style.opacity = '0';
                                welcomeContainer.style.transform = 'translate(-50%, -60%)';
                                setTimeout(() => {
                                    welcomeContainer.style.display = 'none';
                                }, 300);
                            }
                        });

                        // Variabel untuk welcome message
                        const welcomeMessage = document.getElementById('welcomeMessage');

                        // Modifikasi fungsi sendMessage yang sudah ada
                        const originalSendMessage = sendMessage;
                        // Modifikasi fungsi sendMessage yang sudah ada
                        // window.sendMessage = async function() {
                        //     const userMessageText = userInput.value.trim();
                        //     if (userMessageText === '') return;

                        //     // Sembunyikan recommendation container saat mulai chat
                        //     const recommendationContainer = document.querySelector('.recommendation-container');
                        //     if (recommendationContainer) {
                        //         recommendationContainer.classList.add('hide');
                        //         setTimeout(() => {
                        //             recommendationContainer.classList.add('d-none');
                        //         }, 300);
                        //     }

                        //     if (isFirstChat) {
                        //         updateFirstMessage(userMessageText);
                        //         isFirstChat = false;
                        //     }

                        //     // Add user message
                        //     await addMessage('user', userMessageText);

                        //     // Show thinking message with italic, fade-in, and muted text
                        //     const tempMessage = await addMessage('ai', '<em class="text-muted animate__animated animate__fadeIn">Sedang berpikir...</em>', true);

                        //     // Clear input and update UI states
                        //     userInput.value = '';
                        //     hideTersedia();
                        //     showLoader();

                        //     try {
                        //         // Check if it's a revision request for canvas content
                        //         const isRevisionRequest = window.canvasActive && checkIfRevisionRequest(userMessageText);

                        //         if (isRevisionRequest) {
                        //             // Handle revision request
                        //             await handleCanvasRevision(userMessageText);
                        //             tempMessage.remove();
                        //             return;
                        //         }

                        //         // Normal flow - get AI response
                        //         const aiResponse = await getAIResponse(userMessageText);

                        //         // Remove temporary message
                        //         tempMessage.remove();

                        //         // Cek apakah canvas mode diaktifkan
                        //         if (window.canvasModeEnabled) {
                        //             // Tampilkan pengantar di chat
                        //             await addMessage('ai', 'Jawaban telah disiapkan. Silakan lihat di canvas untuk tampilan lengkap.');

                        //             // Tampilkan konten utama di canvas
                        //             showCanvas(aiResponse, 'konten');
                        //         } else {
                        //             // Cek apakah konten panjang seperti biasa
                        //             const contentInfo = detectLongContent(aiResponse, userMessageText);

                        //             if (contentInfo.isLong) {
                        //                 // Tampilkan pengantar di chat
                        //                 await addMessage('ai', contentInfo.chatContent);

                        //                 // Tambahkan tombol canvas
                        //                 addCanvasButton(contentInfo.contentType);

                        //                 // Tampilkan konten utama di canvas
                        //                 showCanvas(contentInfo.canvasContent, contentInfo.contentType);

                        //                 // Tampilkan penutup di chat jika ada
                        //                 if (contentInfo.closingContent) {
                        //                     setTimeout(async () => {
                        //                         await addMessage('ai', contentInfo.closingContent);
                        //                     }, 1000);
                        //                 }
                        //             } else {
                        //                 // Tampilkan respons normal
                        //                 await addMessage('ai', aiResponse);
                        //             }
                        //         }

                        //         // Save to database
                        //         await saveToDatabase(userMessageText, aiResponse);
                        //     } catch (error) {
                        //         console.error('Error:', error);
                        //         tempMessage.remove();
                        //         await addMessage('ai', 'Maaf, terjadi kesalahan saat memproses pesan Anda.');
                        //     } finally {
                        //         hideLoader();
                        //         showTersedia();
                        //     }
                        // };
                        // Tambahkan listener untuk input juga
                        userInput.addEventListener('input', function() {
                            if (userInput.value.trim() !== '' && welcomeMessage) {
                                welcomeMessage.style.opacity = '0';
                                welcomeMessage.style.transform = 'translate(-50%, -60%)';
                                setTimeout(() => {
                                    welcomeMessage.style.display = 'none';
                                }, 300);
                            }
                        });


                        const historyModal = document.getElementById('historyModal');
                        if (historyModal) {
                            historyModal.addEventListener('show.bs.modal', () => {
                                console.log('Modal opening');
                                loadHistory();
                            });
                        } else {
                            console.error('History modal not found');
                        }

                        const historyButton = document.querySelector('[data-bs-target="#historyModal"]');
                        if (!historyButton) {
                            console.error('History button not found');
                        }
                    });

                    function loadChat(pesan, respons) {
                        chatContainer.innerHTML = '';
                        addMessage('user', pesan);
                        addMessage('ai', respons);
                        $('#historyModal').modal('hide');
                    }
                </script>

                <div id="drag-drop-overlay" class="drag-drop-overlay">
                    <div class="drag-drop-content text-center p-4 rounded-3">
                        <i class="bi bi-file-earmark-arrow-up display-4 mb-3" style="color:rgb(218, 119, 86) ;"></i>
                        <h5 class="fw-semibold mb-2">Seret & Lepaskan Dokumen di Sini</h5>
                        <p class="text-muted mb-0 small">Format yang didukung: DOCX, XLSX, PDF</p>
                    </div>
                </div>

                <style>
                    .drag-drop-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.19);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        border: 2px solid rgba(218, 119, 86, 0.3);
                        backdrop-filter: blur(2px);
                        transition: opacity 0.2s ease;
                    }

                    .drag-drop-content {
                        background: white;
                        border: 2px dashed rgba(218, 119, 86, 0.5);
                        padding: 2rem 3rem !important;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                        max-width: 90%;
                    }

                    .drag-drop-content .bi {
                        color: rgb(218, 119, 86);
                        transition: transform 0.2s ease;
                    }

                    .drag-drop-overlay.dragover .drag-drop-content {
                        border-color: rgb(218, 119, 86);
                        background-color: rgba(218, 119, 86, 0.03);
                    }

                    .drag-drop-overlay.dragover .bi {
                        transform: translateY(-5px);
                    }

                    /* Mobile responsive */
                    @media (max-width: 768px) {
                        .drag-drop-content {
                            padding: 1.5rem !important;
                        }

                        .drag-drop-content h5 {
                            font-size: 1.1rem;
                        }

                        .drag-drop-content .bi {
                            font-size: 2.5rem;
                        }
                    }
                </style>

                <!-- style untuk animasi warna file -->
                <style>
                    .document-preview {
                        position: fixed;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        transition: opacity 0.3s ease-in-out;
                    }

                    .preview-content {
                        background: white;
                        padding: 2rem;
                        border-radius: 1rem;
                        text-align: center;
                    }

                    .document-preview {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.95);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(3px);
                        transition: opacity 0.3s ease;
                    }

                    .preview-content {
                        background: white;
                        padding: 2rem 3rem !important;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                        border: 1px solid rgba(218, 119, 86, 0.1);
                        max-width: 90%;
                        animation: contentFade 0.3s ease;
                    }

                    .preview-loader {
                        position: relative;
                        display: inline-block;
                    }

                    .preview-loader .bi {
                        color: rgb(218, 119, 86);
                        animation: bounce 1.5s ease-in-out infinite;
                    }

                    .progress-overlay {
                        position: absolute;
                        top: -8px;
                        left: -8px;
                        right: -8px;
                        bottom: -8px;
                        border: 2px solid rgba(218, 119, 86, 0.1);
                        border-radius: 50%;
                    }

                    .upload-progress {
                        width: 200px;
                        margin: 0 auto;
                    }

                    @keyframes contentFade {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    @keyframes bounce {

                        0%,
                        100% {
                            transform: translateY(0);
                        }

                        50% {
                            transform: translateY(-8px);
                        }
                    }

                    /* Mobile responsive */
                    @media (max-width: 768px) {
                        .preview-content {
                            padding: 1.5rem !important;
                        }

                        .preview-loader .bi {
                            font-size: 2.5rem;
                        }

                        .upload-progress {
                            width: 150px;
                        }
                    }

                    .col-utama {
                        transition: background-color 0.3s ease,
                            box-shadow 0.3s ease,
                            transform 0.3s ease;
                    }

                    /* Untuk button */
                    .col-utama .btn:not(.buttonRekomendasi) {
                        transition: background-color 0.3s ease;
                    }

                    .floating-docs-container {
                        position: fixed;
                        top: 40%;
                        left: 58%;
                        transform: translate(-50%, -50%);
                        background: none;
                        padding: 1rem;
                        border-radius: 1rem;
                        max-width: 80%;
                        overflow-x: auto;
                        opacity: 0;
                        /* Tambahkan ini */
                        visibility: hidden;
                        /* Tambahkan ini */
                        transition: all 0.3s ease;
                        /* Tambahkan ini */
                    }

                    .floating-docs-container.show {
                        opacity: 1;
                        visibility: visible;
                    }

                    .doc-item {
                        background: white;
                        padding: 0.5rem 1rem;
                        border: 1px solid rgb(158, 158, 158);
                        border-radius: 1rem;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem
                    }

                    .doc-icon {
                        color: #3B82F6;
                        font-size: 1.2rem;
                    }

                    .close-btn {
                        background: none;
                        border: none;
                        color: #666;
                        cursor: pointer;
                        padding: 0 5px;
                        transition: color 0.2s;
                    }

                    .close-btn:hover {
                        color: #ff0000;
                    }
                </style>

                <!-- script untuk drag and drop -->
                <script>
                    const docsContainer = document.getElementById('floating-docs-container');
                    let documentContext = '';
                    let activeDocuments = new Set();
                    // Event handler untuk seluruh dokumen
                    document.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);

                    document.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);


                    document.addEventListener('drop', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);


                    function addDocumentToContainer(filename) {
                        if (!activeDocuments.has(filename)) {
                            const docElement = createDocElement(filename);
                            const docsContainer = document.getElementById('floating-docs-container');

                            // Tambahkan class show
                            docsContainer.classList.add('show');
                            docsContainer.style.display = 'flex';

                            docsContainer.appendChild(docElement);
                            activeDocuments.add(filename);

                            // Sembunyikan welcome message
                            const welcomeMessage = document.getElementById('welcomeMessage');
                            if (welcomeMessage) {
                                welcomeMessage.style.opacity = '0';
                                welcomeMessage.style.transform = 'translate(-50%, -60%)';
                                setTimeout(() => {
                                    welcomeMessage.style.display = 'none';
                                }, 300);
                            }
                        }
                    }

                    function createDocElement(filename) {
                        const docDiv = document.createElement('div');
                        docDiv.className = 'doc-item animate__animated animate__fadeIn';

                        const ext = filename.split('.').pop().toLowerCase();
                        const colUtama = document.querySelector('.col-utama');

                        // Reset semua kelas warna
                        colUtama.classList.remove('excel-bg', 'word-bg', 'pdf-bg');

                        switch (ext) {
                            case 'xlsx':
                            case 'xls':
                                colUtama.classList.add('excel-bg');
                                break;
                            case 'doc':
                            case 'docx':
                                colUtama.classList.add('word-bg');
                                break;
                            case 'pdf':
                                colUtama.classList.add('pdf-bg');
                                break;
                        }


                        docDiv.innerHTML = `
                                    ${getFileIcon(filename)}
                                    <span class="doc-name">${filename}</span>
                                    <button class="close-btn" onclick="removeDocument('${filename}')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                `;
                        return docDiv;
                    }

                    function removeDocument(filename) {
                        const docs = docsContainer.querySelectorAll('.doc-item');
                        docs.forEach(doc => {
                            if (doc.querySelector('.doc-name').textContent === filename) {
                                doc.classList.add('animate__fadeOut');
                                setTimeout(() => {
                                    doc.remove();
                                    activeDocuments.delete(filename);

                                    // Update warna berdasarkan file yang tersisa
                                    const colUtama = document.querySelector('.col-utama');
                                    const remainingFiles = Array.from(docsContainer.children)
                                        .filter(item => item.classList.contains('doc-item'))
                                        .map(item => item.querySelector('.doc-name').textContent);

                                    if (remainingFiles.length === 0) {
                                        // Jika tidak ada file tersisa
                                        colUtama.classList.remove('word-bg', 'excel-bg', 'pdf-bg');
                                        docsContainer.style.display = 'none';
                                    } else {
                                        // Jika masih ada file, update warna berdasarkan file terakhir
                                        const lastFile = remainingFiles[remainingFiles.length - 1];
                                        const ext = lastFile.split('.').pop().toLowerCase();

                                        colUtama.classList.remove('word-bg', 'excel-bg', 'pdf-bg');

                                        switch (ext) {
                                            case 'doc':
                                            case 'docx':
                                                colUtama.classList.add('word-bg');
                                                break;
                                            case 'xls':
                                            case 'xlsx':
                                                colUtama.classList.add('excel-bg');
                                                break;
                                            case 'pdf':
                                                colUtama.classList.add('pdf-bg');
                                                break;
                                        }
                                    }
                                }, 500);
                            }
                        });
                    }

                    function getFileIcon(fileName) {
                        const ext = fileName.split('.').pop().toLowerCase();
                        const icons = {
                            'pdf': '<i class="bi bi-file-pdf text-danger" style="font-size: 1.2rem;"></i>',
                            'doc': '<i class="bi bi-file-word text-primary" style="font-size: 1.2rem;"></i>',
                            'docx': '<i class="bi bi-file-word text-primary" style="font-size: 1.2rem;"></i>',
                            'xls': '<i class="bi bi-file-excel text-success" style="font-size: 1.2rem;"></i>',
                            'xlsx': '<i class="bi bi-file-excel text-success" style="font-size: 1.2rem;"></i>',
                            'default': '<i class="bi bi-file-text" style="font-size: 1.2rem;"></i>'
                        };
                        return icons[ext] || icons.default;
                    }
                </script>


                <!-- script untuk membaca file excel -->
                <script>
                    // Di dalam fungsi handleExcelFile
                    async function handleExcelFile(file) {
                        console.log("[Excel Processor] Starting Excel file processing...", file.name);

                        const reader = new FileReader();

                        return new Promise((resolve, reject) => {
                            reader.onload = function(e) {
                                try {
                                    console.log("[Excel Processor] File read successfully, parsing...");

                                    const data = new Uint8Array(e.target.result);
                                    const workbook = XLSX.read(data, {
                                        type: 'array'
                                    });

                                    console.log("[Excel Processor] Workbook structure:", {
                                        sheetNames: workbook.SheetNames,
                                        sheetCount: workbook.SheetNames.length
                                    });

                                    let structuredContent = "";
                                    workbook.SheetNames.forEach((sheetName, index) => {
                                        const worksheet = workbook.Sheets[sheetName];
                                        const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                                            header: 1
                                        });

                                        console.log(`[Excel Processor] Processing sheet ${index + 1}/${workbook.SheetNames.length}: ${sheetName}`);
                                        console.log(`[Excel Processor] Sheet ${sheetName} data sample:`, jsonData.slice(0, 2));

                                        structuredContent += `=== LEMBAR: ${sheetName} ===\n`;
                                        structuredContent += `Jumlah Baris: ${jsonData.length}\n\n`;

                                        if (jsonData.length > 0) {
                                            // Header
                                            structuredContent += "Kolom:\n";
                                            structuredContent += jsonData[0].join(" | ") + "\n\n";

                                            // Contoh data (maksimal 5 baris)
                                            structuredContent += "Contoh Data:\n";
                                            jsonData.slice(1, 6).forEach((row, index) => {
                                                structuredContent += `Baris ${index + 1}: ${row.join(" | ")}\n`;
                                            });
                                        }
                                        structuredContent += "\n\n";
                                    });

                                    console.log("[Excel Processor] Excel content extracted:", structuredContent);
                                    resolve(structuredContent);

                                } catch (error) {
                                    console.error("[Excel Processor] Error processing Excel file:", error);
                                    reject(error);
                                }
                            };

                            reader.onerror = (error) => {
                                console.error("[Excel Processor] File read error:", error);
                                reject(error);
                            };

                            reader.readAsArrayBuffer(file);
                        });
                    }



                    // Update your existing file input handler
                    document.getElementById('file-input').addEventListener('change', async function(e) {
                        const file = e.target.files[0];
                        console.log("[File Handler] File selected:", file?.name);

                        const preview = document.getElementById('document-preview');

                        if (file) {
                            preview.style.display = 'flex';
                            preview.style.opacity = '1';

                            try {
                                if (file.name.match(/\.(xlsx|xls)$/)) {
                                    console.log("[File Handler] Excel file detected, starting processing...");
                                    // Load SheetJS hanya ketika file Excel terdeteksi
                                    await loadSheetJS();
                                    const content = await handleExcelFile(file);
                                    console.log("[File Handler] Excel processing completed:", {
                                        fileName: file.name,
                                        contentPreview: content.substring(0, 200) + "..."
                                    });
                                    window.documentContent = content;
                                    addDocumentToContainer(file.name);
                                }
                            } catch (error) {
                                console.error("[File Handler] Excel processing failed:", error);
                            } finally {
                                preview.style.opacity = '0';
                                setTimeout(() => {
                                    preview.style.display = 'none';
                                }, 300);
                            }
                        }
                    });


                    // Di dalam fungsi updateConversationWithExcel():
                    function updateConversationWithExcel() {
                        if (window.documentContent) {
                            conversationHistory.push({
                                role: "system",
                                content: `DATA EXCEL USER:
                                            ${window.documentContent}
                                            
                                            INSTRUKSI KHUSUS:
                                            - Analisis semua sheet
                                            - Jika ditanya data spesifik, cek di semua sheet
                                            - Bandingkan data antar sheet jika diperlukan`
                            });
                        }
                    }
                </script>

                <!-- script untuk upload -->
                <script>
                    // Add event listener for file input
                    document.getElementById('file-input').addEventListener('change', async function(e) {
                        const file = e.target.files[0];
                        const preview = document.getElementById('document-preview');


                        if (file) {
                            console.log("File selected:", file);


                            // Show loading preview
                            preview.style.display = 'flex';
                            preview.style.opacity = '1';

                            const formData = new FormData();
                            formData.append('file', file);

                            try {
                                const response = await fetch('process_document.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                const result = await response.json();

                                if (result.success) {
                                    window.documentContent = result.content;
                                    addDocumentToContainer(file.name);
                                    userInput.value = ``;
                                }
                            } catch (error) {
                                console.error("Error processing file:", error);
                            } finally {
                                preview.style.opacity = '0';
                                setTimeout(() => {
                                    preview.style.display = 'none';
                                }, 300);
                            }
                        }
                    });

                    // Update button styling
                    const fileInputLabel = document.querySelector('label[for="file-input"]');
                    if (fileInputLabel) {
                        fileInputLabel.style.margin = '0';
                        fileInputLabel.style.padding = '8px';
                        fileInputLabel.style.cursor = 'pointer';
                        fileInputLabel.addEventListener('mouseover', () => {
                            fileInputLabel.style.color = '#666';
                        });
                        fileInputLabel.addEventListener('mouseout', () => {
                            fileInputLabel.style.color = 'initial';
                        });
                    }
                </script>
            </div>
        </div>
    </div>

    <!-- style chat-bubble ai dan user  -->
    <style>
        /* Untuk chat AI */
        .chat-bubble {
            background-color: transparent !important;
            /* Pastikan tidak ada background */
            max-width: 100%;
            /* Isi lebar parent container */
        }

        /* Untuk bubble user */
        [style*="rgb(239, 239, 239)"] .chat-bubble {
            background-color: rgb(239, 239, 239);
        }

        .chat-bubble ul {
            list-style-type: disc;
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }

        .chat-bubble ol {
            list-style-type: decimal;
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }

        .chat-bubble li {
            margin: 0.2rem 0;
            line-height: 1.4;
        }

        .chat-bubble strong {
            font-weight: 600;
            color: #333;
        }

        .chat-list {
            list-style-type: none;
            margin: 0.75rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .chat-list li {
            margin: 0.4rem 0;
            line-height: 1.5;
            position: relative;
        }

        .chat-list li::before {
            content: "•";
            color: #da7756;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        .chat-paragraph {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
        }
    </style>


    <script>
        localStorage.removeItem('sagaPersonality');
        // Elemen DOM
        const chatContainer = document.getElementById('chat-container');
        const userInput = document.getElementById('user-input');
        const sendButton = document.getElementById('send-button');

        let currentSessionId = null;

        // Gambar profil
        const userImage = '<?php echo !empty($guru["foto_profil"]) ? "uploads/profil/" . $guru["foto_profil"] : "assets/pp.png"; ?>';

        const aiImage = 'assets/ai_chat.png';

        let conversationHistory = [];
        const MAX_HISTORY = 10; // Batasan riwayat

        let isFirstChat = true; //rekomendasi chat

        // Loading animation
        let loadingInterval;

        // Fungsi untuk menampilkan pesan loading
        function showLoader() {
            const loadingEl = document.getElementById('loading');
            loadingEl.style.display = 'block';
            let dots = 0;
            loadingInterval = setInterval(() => {
                loadingEl.textContent = 'Tunggu sebentar' + '.'.repeat(dots);
                dots = (dots + 1) % 4;
            }, 500);
        }

        function hideLoader() {
            clearInterval(loadingInterval);
            document.getElementById('loading').style.display = 'none';
        }



        const formatText = (text) => {
            // Handle think tags first
            text = text.replace(/<think>([\s\S]*?)<\/think>/g,
                '<div class="ai-thinking animate__animated animate__fadeIn">$1</div>'
            );

            // Handle markdown headers
            text = text.replace(/^##### (.*?)$/gm, '<h5>$1</h5>');
            text = text.replace(/^#### (.*?)$/gm, '<h4>$1</h4>');
            text = text.replace(/^### (.*?)$/gm, '<h3>$1</h3>');
            text = text.replace(/^## (.*?)$/gm, '<h2>$1</h2>');
            text = text.replace(/^# (.*?)$/gm, '<h1>$1</h1>');

            // Handle bold text
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Handle italics
            text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');

            // Handle code blocks
            text = text.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

            // Handle inline code
            text = text.replace(/`(.*?)`/g, '<code>$1</code>');

            const lines = text.split('\n');
            let inList = false;
            let listType = 'ul';

            const processedLines = lines.map(line => {
                // Handle list items
                const listMatch = line.match(/^\s*([*\-])(\s+)(.*)/);
                const numberedListMatch = line.match(/^\s*(\d+\.)(\s+)(.*)/);

                if (listMatch) {
                    const [_, symbol, space, content] = listMatch;
                    const isNested = space.length > 1;
                    let listHtml = '';
                    if (!inList) {
                        listType = symbol === '*' ? 'ul' : 'ul';
                        listHtml += `<${listType} class="chat-list">`;
                        inList = true;
                    }
                    listHtml += `<li${isNested ? ' style="margin-left: 1.5rem"' : ''}>${content}</li>`;
                    return listHtml;
                } else if (numberedListMatch) {
                    const [_, number, space, content] = numberedListMatch;
                    const isNested = space.length > 1;
                    let listHtml = '';
                    if (!inList) {
                        listType = 'ol';
                        listHtml += `<${listType} class="chat-list">`;
                        inList = true;
                    }
                    listHtml += `<li${isNested ? ' style="margin-left: 1.5rem"' : ''}>${content}</li>`;
                    return listHtml;
                } else {
                    let lineHtml = line;
                    if (inList) {
                        lineHtml = `</${listType}>${lineHtml}`;
                        inList = false;
                    }
                    return line.trim().length > 0 ? `<p class="chat-paragraph">${lineHtml}</p>` : lineHtml;
                }
            });

            if (inList) processedLines.push(`</${listType}>`);
            return processedLines.join('');
        };

        // Update addMessage function to include logging
        async function addMessage(sender, text, isTemporary = false) {
            console.log(`Adding ${sender} message:`, text.substring(0, 50) + '...');

            const messageWrapper = document.createElement('div');
            messageWrapper.classList.add(
                'd-flex',
                'mb-3',
                sender === 'user' ? 'justify-content-end' : 'justify-content-start'
            );

            if (isTemporary) messageWrapper.id = 'thinking-message';

            let processedText = text;
            if (sender === 'ai' && !isTemporary) {
                processedText = text.replace(/<think>([\s\S]*?)<\/think>/g,
                    '<div class="ai-thinking animate__animated animate__fadeIn">$1</div>'
                );
            }

            const formattedText = formatText(processedText);

            messageWrapper.innerHTML = `
        <div class="d-flex chat-message align-items-center pt-1 pb-1 p-2 rounded-4 ${sender === 'user' ? 'flex-row-reverse' : ''}" 
            style="background-color: ${sender === 'user' ? 'rgb(239, 239, 239)' : 'transparent'}; 
                max-width: ${sender === 'user' ? '30rem' : '40rem'}">
            <img src="${sender === 'user' ? userImage : aiImage}" 
                class="chat-profile bg-white ms-2 me-2 rounded-circle" 
                alt="${sender} profile" 
                style="width: 20px; height: 20px; object-fit: cover;">
            <div class="chat-bubble rounded p-2 align-content-center"
                style="font-size: 13px; ${sender === 'ai' ? 'background-color: transparent; width: 100%' : ''}">
                ${sender === 'user' || isTemporary ? formattedText : ''}
            </div>
        </div>
    `;

            chatContainer.appendChild(messageWrapper);

            if (sender === 'ai' && !isTemporary) {
                const chatBubble = messageWrapper.querySelector('.chat-bubble');
                await typeMessage(chatBubble, text);
            }

            chatContainer.scrollTop = chatContainer.scrollHeight;
            console.log('Message added successfully');

            return messageWrapper;
        }



        // Add this after your DOM content loaded
        document.getElementById('deepThinkingToggle').addEventListener('change', function(e) {
            const isDeepThinking = e.target.checked;
            console.log('Deep thinking mode (dom loaded):', isDeepThinking ? 'ON' : 'OFF');

            // Visual feedback
            document.querySelector('.deep-thinking-toggle').classList.toggle('active', isDeepThinking);
        });

        async function typeMessage(element, text) {
            // Helper function untuk auto-scroll
            const autoScroll = () => {
                const elementBottom = element.getBoundingClientRect().bottom;
                const containerBottom = chatContainer.getBoundingClientRect().bottom;
                if (elementBottom > containerBottom) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            };

            // Proses thinking tag terlebih dahulu jika ada
            const thinkMatch = text.match(/<think>([\s\S]*?)<\/think>/);
            if (thinkMatch) {
                // Tambahkan indikator "Berpikir..."
                const thinkIndicator = document.createElement('div');
                thinkIndicator.className = 'ai-thinking animate__animated animate__fadeIn';
                thinkIndicator.textContent = 'SAGA AI sedang berkontemplasi ... ';
                element.appendChild(thinkIndicator);
                autoScroll(); // Auto-scroll setelah menambahkan indikator

                await new Promise(resolve => setTimeout(resolve, 300));

                const thinkContent = thinkMatch[1];
                const thinkDiv = document.createElement('div');
                thinkDiv.className = 'ai-thinking animate__animated animate__fadeIn';
                element.appendChild(thinkDiv);

                // Typing animation untuk konten thinking
                let currentThinkText = '';
                for (let i = 0; i < thinkContent.length; i++) {
                    currentThinkText += thinkContent[i];
                    thinkDiv.textContent = currentThinkText;
                    autoScroll(); // Auto-scroll saat thinking
                    await new Promise(resolve => setTimeout(resolve, 5));
                }

                text = text.replace(/<think>[\s\S]*?<\/think>\n?/, '').trim();
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            // Mulai typing animation untuk teks utama
            let currentText = '';
            for (let i = 0; i < text.length; i++) {
                currentText += text[i];

                const formattedText = currentText
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/^(\d+\.|\-)\s+/gm, '<br>$1 ')
                    .split('\n')
                    .join('<br>');

                const thinkingDivs = element.querySelectorAll('.ai-thinking');
                element.innerHTML = Array.from(thinkingDivs).map(div => div.outerHTML).join('') + formattedText;

                autoScroll(); // Auto-scroll saat typing teks utama
                await new Promise(resolve => setTimeout(resolve, 5));
            }
        }


        let systemMessage = {
            role: "system",
            content: `Namamu adalah SAGA AI, asisten untuk guru di SMP dan SMA Muhammadiyah Gatak. Kamu dirancang untuk memberikan dukungan yang bijaksana, efisien, dan tepat sasaran.

            informasi guru yang sedang berinteraksi:
            nama: ${<?php echo json_encode($guru['namaLengkap']); ?>}
            jabatan : ${<?php echo json_encode($guru['jabatan']); ?>}

            Karakter dan Perilaku:
            - Berbicara dengan bijaksana dan profesional layaknya rekan kerja sesama pendidik
            - Memberikan respons singkat, padat, dan tepat sasaran tanpa bertele-tele
            - Bicara ketika dibutuhkan, tidak memberikan informasi berlebihan
            - Menunjukkan rasa hormat dan kolegalitas dalam interaksi dengan guru lain
            - Menggunakan bahasa yang sopan dan formal namun tetap hangat
            - Menghindari humor yang tidak perlu atau komentar yang terlalu kasual
            - Selalu berorientasi solusi dan praktis dalam setiap saran

            Keahlian Utama:
            - Perencanaan pembelajaran dan penilaian yang komprehensif dan sesuai kurikulum
            - Strategi mengajar aktif serta inovatif yang disesuaikan dengan kebutuhan siswa
            - Manajemen kelas efektif serta teknik motivasi guru dan siswa
            - Pengembangan materi pembelajaran yang kreatif dan berbasis kompetensi
            - Integrasi teknologi pembelajaran dan media pembelajaran yang relevan
            - Pemahaman mendalam tentang prinsip pendidikan Muhammadiyah

            Batasan dan Panduan:
            - Selalu prioritaskan nilai-nilai pendidikan Islam dan Kemuhammadiyahan
            - Berikan informasi faktual dan berbasis bukti ilmiah
            - Hindari memberikan jawaban yang terlalu panjang kecuali diminta secara khusus
            - Jika tidak yakin atau tidak memiliki informasi yang cukup, akui keterbatasan dan tawarkan solusi alternatif
            - Hormati kebijakan sekolah dan sistem pendidikan nasional yang berlaku
            - Pastikan untuk setiap percakapan di buat berparagraf-paragraf dengan (\n)

            Format Respons:
            - Mulai dengan salam yang singkat dan profesional
            - Langsung ke inti permasalahan/pertanyaan
            - Berikan solusi atau jawaban dengan struktur yang jelas
            - Akhiri dengan pertanyaan lanjutan yang relevan dengan konteks jawaban, misalnya:
            * "Apakah ada aspek lain dari [topik] yang ingin Bapak/Ibu diskusikan?"
            * "Apakah Bapak/Ibu memerlukan penjelasan lebih detail tentang [bagian tertentu]?"
            * "Apakah ada kendala spesifik terkait [topik] yang Bapak/Ibu hadapi dalam praktik?"
            - Jika percakapan tampak akan berakhir, tutup dengan kalimat yang menunjukkan kesiapan membantu di masa mendatang
            `
        };

        let contohDialog = [{
                role: "user",
                content: "halo saga"
            },
            {
                role: "assistant",
                content: "Halo Bapak/Ibu Guru. Ada yang bisa SAGA hari ini?"
            },
            {
                role: "user",
                content: "Bagaimana cara membuat ujian di LMS?"
            },
            {
                role: "assistant",
                content: "Untuk membuat ujian di LMS, silakan ikuti langkah berikut:\n\n1. Akses menu 'Ujian' pada panel navigasi kiri\n2. Pilih 'Tambah Ujian' di sudut kanan atas\n3. Isi formulir detail ujian (nama, deskripsi, waktu pelaksanaan, durasi)\n4. Tambahkan soal-soal yang diperlukan\n5. Klik 'Simpan' untuk mengaktifkan ujian\n\nApakah ada tahapan spesifik dari proses pembuatan ujian yang perlu SAGA jelaskan lebih detail?"
            },
            {
                role: "user",
                content: "Saya perlu contoh RPP untuk kelas 8 mapel IPA"
            },
            {
                role: "assistant",
                content: "Baik. SAGA dapat menyediakan kerangka RPP IPA Kelas 8 yang sesuai kurikulum terbaru. Mohon informasi spesifik mengenai KD atau topik yang Bapak/Ibu butuhkan untuk memastikan RPP yang relevan."
            },
            {
                role: "user",
                content: "KD 3.4 tentang sistem pencernaan manusia"
            },
            {
                role: "assistant",
                content: "Baik, berikut kerangka RPP IPA Kelas 8 untuk KD 3.4 tentang sistem pencernaan manusia:\n\n1. Identitas RPP: Mata pelajaran, kelas, semester, alokasi waktu\n2. Kompetensi Inti & Kompetensi Dasar 3.4\n3. Indikator Pencapaian Kompetensi\n4. Tujuan Pembelajaran\n5. Materi Pembelajaran: Struktur dan fungsi sistem pencernaan, organ-organ pencernaan, proses pencernaan, enzim pencernaan, gangguan sistem pencernaan\n6. Metode: Pendekatan saintifik, model Discovery Learning\n7. Media: Video animasi proses pencernaan, gambar organ pencernaan, model torso\n8. Sumber: Buku siswa, e-modul, referensi online terpercaya\n9. Langkah Pembelajaran: Pendahuluan, kegiatan inti, penutup\n10. Penilaian: Teknik penilaian kognitif, keterampilan, dan sikap\n\nApakah Bapak/Ibu memerlukan penjelasan lebih detail tentang salah satu komponen RPP tersebut?"
            },
            {
                role: "user",
                content: "Tolong berikan strategi untuk mengajar siswa yang kurang fokus di kelas"
            },
            {
                role: "assistant",
                content: "Strategi efektif untuk meningkatkan fokus siswa:\n\n1. Variasikan metode pembelajaran dengan kombinasi visual-auditori-kinestetik\n2. Terapkan pembelajaran berbasis masalah yang relevan dengan kehidupan siswa\n3. Gunakan teknik 'brain break' selama 2-3 menit setiap 20-25 menit pembelajaran\n4. Integrasikan teknologi interaktif seperti kuis digital atau simulasi\n5. Implementasikan sistem reward sederhana untuk partisipasi aktif\n\nPenerapan dapat disesuaikan dengan karakteristik kelas Bapak/Ibu.\n\nApakah ada strategi khusus yang ingin Bapak/Ibu eksplorasi lebih dalam untuk karakteristik siswa tertentu?"
            },
            {
                role: "user",
                content: "Terima kasih informasinya"
            },
            {
                role: "assistant",
                content: "Sama-sama, Bapak/Ibu Guru. SAGA siap membantu jika ada kebutuhan lain terkait pembelajaran atau strategi yang telah disampaikan. Semoga sukses dalam mengajar."
            },
            {
                role: "user",
                content: "kamu siapa?"
            }, {
                role: "assistant",
                content: "Saya SAGA atau SMAGA Asistant \n\n Saya di sini bekerja di SMP dan SMA Muhammadiyah Gatak sebagai pembantu Bapak/Ibu dalam mengajar. \n\n Ada yang bisa saya bantu Bapak/Ibu?"
            }
        ];

        let deepThinkingSystemMessage = {
            role: "system",
            content: `

                            Kamu adalah SAGA AI, asisten guru yang sangat analitis. Untuk setiap pertanyaan:

                             1. Mulai dengan proses analisis dalam Bahasa Indonesia:
                            <think>
                            Mari saya analisis situasi ini secara mendalam:
                            
                            KONTEKS:
                            - [Identifikasi masalah utama]
                            - [Siapa saja yang terlibat]
                            - [Situasi saat ini]
                            
                            TANTANGAN:
                            - [Uraikan tantangan utama]
                            - [Kendala yang ada]
                            - [Dampak yang mungkin terjadi]
                            
                            PERTIMBANGAN:
                            - [Faktor-faktor yang perlu diperhatikan]
                            - [Sumber daya yang tersedia]
                            - [Batasan yang ada]
                            
                            ARAH SOLUSI:
                            - [Pendekatan yang mungkin dilakukan]
                            - [Prioritas yang perlu diutamakan]
                            - [Target yang ingin dicapai]
                            </think>

                            2. Setelah analisis, berikan respons terstruktur dengan format:
                            ### Analisis Situasi
                            [Rangkuman hasil analisis]
                            
                            ### Tantangan Utama
                            1. [Tantangan 1]
                                - Dampak
                                - Penyebab
                            2. [Tantangan 2]
                                ...
                            
                            ### Solusi Terstruktur
                            [Uraian solusi detail]
                            
                            ### Langkah Implementasi
                            [Tahapan pelaksanaan]
                            
                            ### Antisipasi Tantangan
                            [Potensi masalah dan solusi]
                            
                            ### Monitoring Keberhasilan
                            [Cara mengukur hasil]

                            3. Akhiri dengan rangkuman dan tawaran bantuan lebih lanjut

                            Selalu:
                            - Gunakan bahasa yang empatik dan suportif
                            - Berikan contoh konkret
                            - Pertimbangkan keterbatasan sumber daya
                            - Tawarkan alternatif solusi
                            
                            PENTING: Selalu mulai dengan <think> tag dan akhiri dengan </think> sebelum memberikan respons utama.
                            
                            `
        };

        // First, define both models and their configurations
        const models = {
            llama: {
                name: 'llama-3.3-70b-versatile',
                temperature: 1
            },
            deepseek: {
                name: 'deepseek-r1-distill-llama-70b',
                temperature: 0.7
            }
        };

        async function getAIResponse(userMessage) {
            const API_KEY = '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';
            const API_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

            // Gunakan model yang dipilih dari dropdown
            const modelId = window.activeModelId || 'llama-3.3-70b-versatile';

            // Dapatkan konteks dari endpoint terpisah
            let contextFromEndpoint = '';
            try {
                const contextResponse = await fetch('get_context.php?query=' + encodeURIComponent(userMessage));
                const contextData = await contextResponse.json();
                contextFromEndpoint = contextData.context;
                console.log('Konteks yang didapatkan:', contextFromEndpoint);
            } catch (error) {
                console.error('Error getting context:', error);
                contextFromEndpoint = 'SMP dan SMA Muhammadiyah Gatak adalah sekolah Islam terpadu.';
            }


            const schoolContextData = `<?php echo addslashes($schoolContext); ?>`;

            // Deep thinking tetap bisa digunakan
            const isDeepThinking = document.getElementById('deepThinkingToggle').checked;
            const selectedSystemMessage = isDeepThinking ? deepThinkingSystemMessage : systemMessage;
            // Ambil konten dokumen jika ada
            const docContent = window.documentContent || '';

            // Ambil konteks project jika ada
            const projectContext = window.projectContext ? `
                                Berikut konteks project yang relevan:
                                ${window.projectContext}
                                
                                gunakan informasi ini sebagai acuan utama dalam menjawab pertanyaan guru, fokus pada informasi ini,
                                jangan bahas yang lain kecuali guru membahas hal lainya.jika pertanyaan 
                                tidak terkait dengan konteks project, jawab seperti instruksi awal ya.
                            ` : '';

            // Proses konten dokumen jika ada
            let contextMessage = [];
            if (docContent) {
                const chunks = docContent.match(/[^.!?]+[.!?]+/g) || [];
                const contextualized_chunks = chunks.join(' ').substring(0, 2000); // Batasi panjang teks
                contextMessage = [{
                    role: "system",
                    content: `Document context: ${contextualized_chunks}`
                }];
            }

            // Tambahkan pesan pengguna ke riwayat percakapan
            conversationHistory.push({
                role: "user",
                content: userMessage
            });

            // Batasi riwayat percakapan
            if (conversationHistory.length > MAX_HISTORY * 2) {
                conversationHistory = conversationHistory.slice(-MAX_HISTORY * 2);
            }

            // Tambahkan log untuk melihat system message yang digunakan
            console.log('Mode:', isDeepThinking ? 'Deep Thinking' : 'Regular');
            console.log('System Messages:');
            console.log('Regular System Message:', systemMessage.content);
            console.log('Deep Thinking System Message:', deepThinkingSystemMessage.content);
            console.log('Selected System Message:', selectedSystemMessage.content);


            try {
                // Susun pesan yang akan dikirim ke AI
                const messages = [{
                        role: "system",
                        content: systemMessage.content + "\n\n" + contextFromEndpoint
                    },
                    ...contextMessage, // Konteks dokumen yang diupload
                    ...contohDialog, // Contoh dialog
                    ...conversationHistory // Riwayat percakapan
                ];

                // Tambahkan indikator visual di UI
                const loadingEl = document.getElementById('loading');
                loadingEl.textContent = `Thinking in ${isDeepThinking ? 'Deep' : 'Regular'} mode...`;

                // Kirim request ke API
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${API_KEY}`,
                        'Content-Type': 'application/json',
                        'accept': 'application/json'
                    },
                    body: JSON.stringify({
                        model: modelId, // Gunakan model ID dari pilihan
                        messages: messages,
                        temperature: 0.7
                    }),
                    mode: 'cors'
                });

                // Handle response
                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('Groq API Error:', errorData);

                    // Handle different error types with specific messages
                    if (errorData.error) {
                        const errorCode = errorData.error.code;
                        const errorMsg = errorData.error.message;

                        switch (errorCode) {
                            case 'model_decommissioned':
                                return `⚠️ Model ${modelId} tidak tersedia oleh Penyedia AI. Silakan pilih model lain dan hubungi Tim IT Anda.`;

                            case 'invalid_api_key':
                            case 'expired_api_key':
                                return '⚠️ Kunci API tidak aktif. Hubungi Tim IT Anda.';

                            case 'insufficient_quota':
                                return '⚠️ Kuota percakapan Anda telah habis, silahkan hubungi Tim IT Anda untuk meningkatkan paket pembelian layanan AI.';

                            case 'rate_limit_exceeded':
                                return '⚠️ Anda terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi setelah beberapa saat.';

                            default:
                                return `⚠️ API Error (${errorCode}): ${errorMsg}`;
                        }
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                const aiResponse = data.choices[0].message.content;

                // Tambahkan respons AI ke riwayat percakapan
                conversationHistory.push({
                    role: "assistant",
                    content: aiResponse
                });

                return aiResponse;

            } catch (error) {
                console.error('Error:', error);
                return 'Maaf, terjadi kesalahan saat berkomunikasi dengan AI. Coba lagi nanti atau refresh';
            }
        }


        // tampilkan loading
        function showLoader() {
            document.getElementById('loading').style.display = 'block';
        }

        // sembunyikan loading
        function hideLoader() {
            document.getElementById('loading').style.display = 'none';
        }

        //tampilkan gemini tersedia
        function showTersedia() {
            document.getElementById('tersedia').style.display = 'block';
        }

        // sembunyikan gemini tersedia
        function hideTersedia() {
            document.getElementById('tersedia').style.display = 'none';
        }


        async function saveToDatabase(message, aiResponse) {
            try {
                const data = {
                    user_id: '<?php echo $_SESSION["userid"]; ?>',
                    pesan: message,
                    respons: aiResponse,
                    uses_canvas: window.canvasModeEnabled ? 1 : 0 // Tambahkan info canvas mode
                };

                console.log('Preparing to send data:', data);

                const response = await fetch('save_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                // Sisa kode sama seperti sebelumnya
            } catch (error) {
                console.error('Saving to database error:', error);
            }
        }



        function updateCurrentTopic(title) {
            const topicElement = document.getElementById('currentTopic');
            const topicText = topicElement.querySelector('.topic-text');
            topicText.textContent = title;
            topicElement.classList.remove('d-none');
        }

        function fillPrompt(text) {
            document.getElementById('user-input').value = text;
            document.getElementById('user-input').focus();
        }

        // Fungsi untuk memperbarui pesan pertama
        function updateFirstMessage(message) {
            if (!message) return; // Guard clause untuk mencegah error jika message undefined

            const messageElement = document.getElementById('firstMessage');
            if (!messageElement) return; // Guard clause untuk mencegah error jika elemen tidak ditemukan

            const messageText = messageElement.querySelector('.first-message-text');
            if (!messageText) return; // Guard clause untuk mencegah error jika elemen text tidak ditemukan

            // Batasi panjang pesan dan tambahkan ellipsis jika perlu
            const truncatedMessage = message.length > 50 ? message.substring(0, 47) + '...' : message;
            messageText.textContent = truncatedMessage;
            messageElement.classList.remove('d-none');
        }

        // Fungsi untuk mengirim pesan
        // Fungsi untuk mengirim pesan
        async function sendMessage() {
            const userMessageText = userInput.value.trim();
            if (userMessageText === '') return;

            // Sembunyikan recommendation container
            const recommendationContainer = document.querySelector('.recommendation-container');
            if (recommendationContainer) {
                recommendationContainer.classList.add('hide');
                setTimeout(() => {
                    recommendationContainer.classList.add('d-none');
                }, 300);
            }

            if (isFirstChat) {
                updateFirstMessage(userMessageText);
                isFirstChat = false;
            }

            // Clear floating docs
            const docsContainer = document.getElementById('floating-docs-container');
            if (docsContainer) {
                docsContainer.style.opacity = '0';
                docsContainer.style.visibility = 'hidden';
                setTimeout(() => {
                    docsContainer.style.display = 'none';
                    docsContainer.innerHTML = '';
                    activeDocuments.clear();
                    window.documentContent = '';
                }, 300);
            }

            // Add user message
            await addMessage('user', userMessageText);

            // Show thinking message
            const tempMessage = await addMessage('ai', '<em class="text-muted animate__animated animate__fadeIn">Sedang berpikir...</em>', true);

            // Clear input and update UI states
            userInput.value = '';
            hideTersedia();
            showLoader();

            try {
                // Update conversation with Excel data if needed
                if (typeof updateConversationWithExcel === 'function') {
                    updateConversationWithExcel();
                }

                // Check if it's a revision request
                const isRevisionRequest = window.canvasActive && checkIfRevisionRequest(userMessageText);

                if (isRevisionRequest) {
                    // Hapus message temporary
                    tempMessage.remove();

                    // Handle revision request
                    await handleCanvasRevision(userMessageText);
                    return;
                }

                // Normal flow - get AI response
                const aiResponse = await getAIResponse(userMessageText);

                // Remove temporary message
                tempMessage.remove();

                // Cek apakah canvas mode diaktifkan
                if (window.canvasModeEnabled) {
                    // Bersihkan konten untuk canvas
                    const cleanedResponse = cleanCanvasContent(aiResponse);

                    // Simpan konten untuk canvas
                    window.currentCanvasContent = cleanedResponse;
                    window.currentCanvasType = 'konten';

                    // Tampilkan pengantar di chat
                    await addMessage('ai', 'Jawaban telah disiapkan. Silakan lihat di canvas untuk tampilan lengkap.');

                    // Tambahkan tombol canvas dan tampilkan canvas
                    addCanvasButton('konten');
                    showCanvas(cleanedResponse, 'konten');
                } else {
                    // Canvas mode tidak aktif, tampilkan respons normal
                    await addMessage('ai', aiResponse);
                }

                // Save to database
                await saveToDatabase(userMessageText, aiResponse);
            } catch (error) {
                console.error('Error:', error);
                tempMessage.remove();
                await addMessage('ai', 'Maaf, terjadi kesalahan saat memproses pesan Anda.');
            } finally {
                hideLoader();
                showTersedia();
            }
        }

        // Event listener untuk tombol Kirim
        sendButton.addEventListener('click', sendMessage);

        // Event listener untuk tombol Enter
        userInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Mencegah form submit (default behavior)
                sendMessage();
            }
        });

        // Fungsi untuk memisahkan konten dalam tag <konten> dan teks normal
        function extractCanvasContent(text) {
            const contentRegex = /<konten>([\s\S]*?)<\/konten>/g;
            const matches = [...text.matchAll(contentRegex)];

            // Ekstrak semua konten dalam tag <konten>
            let canvasContent = '';
            if (matches.length > 0) {
                canvasContent = matches.map(match => match[1]).join('\n\n');
            }

            // Hapus tag <konten> dan isinya dari teks asli untuk chat
            const chatContent = text.replace(contentRegex, '').trim();

            return {
                chatContent: chatContent,
                canvasContent: canvasContent,
                hasCanvasContent: canvasContent.length > 0
            };
        }

        function detectLongContent(aiResponse, userMessage) {
            // Selalu kembalikan false untuk mencegah pembukaan canvas otomatis
            return {
                isLong: false
            };
        }

        // Tambahkan variabel global untuk melacak status konten
        // Tambahkan variabel global untuk melacak status konten
        window.contentTyped = false;
        window.lastTypedContent = '';

        // Tambahkan fungsi ini ke kode Anda
        function cleanCanvasContent(content) {
            // Hapus hanya kalimat terakhir yang mengandung pertanyaan
            const paragraphs = content.trim().split(/\n\s*\n/);
            const lastPara = paragraphs[paragraphs.length - 1];
            if (lastPara && /(\?|apakah|tolong|mohon)/i.test(lastPara)) {
                paragraphs.pop();
            }
            return paragraphs.join('\n\n');
        }

        // Modifikasi fungsi showCanvas
        function showCanvas(content, contentType = 'konten', animate = true) {
            try {
                console.log("showCanvas called with content type:", contentType);
                console.log("showCanvas with animate:", animate);

                const cleanedContent = cleanCanvasContent(content);
                const theCanvas = document.getElementById('canvas-container');
                const canvasEditor = document.getElementById('canvas-editor');
                const colUtama = document.querySelector('.col-utama');

                // Reset canvas versions jika ini adalah canvas baru
                if (!window.canvasActive) {
                    // Jika ini adalah canvas baru, buat versi pertama
                    if (!window.canvasVersions || window.canvasVersions.length === 0) {
                        window.canvasVersions = [cleanedContent];
                        window.currentVersionIndex = 0;

                        // Simpan versi pertama ke database
                        if (currentSessionId) {
                            saveCanvasVersion(cleanedContent);
                        }
                    }
                } else if (window.currentCanvasContent !== cleanedContent) {
                    // Jika konten berubah dan canvas sudah aktif, tambahkan sebagai versi baru
                    // Simpan versi baru ke memori dan database
                    saveCanvasVersion(cleanedContent);
                }

                if (!theCanvas || !canvasEditor) {
                    console.error("Canvas elements not found", {
                        theCanvas,
                        canvasEditor
                    });
                    return;
                }

                // Reset posisi untuk mobile jika perlu
                if (isMobileDevice()) {
                    theCanvas.style.bottom = "0";
                }

                // Simpan konten untuk referensi
                window.currentCanvasContent = cleanedContent;
                window.currentCanvasType = contentType;
                canvasEditor.classList.add('fade-in');

                // Tampilkan canvas
                theCanvas.classList.add('active');

                // Tambahkan class canvas-active ke col-utama hanya jika bukan mobile
                if (!isMobileDevice()) {
                    colUtama.classList.add('canvas-active');
                }

                window.canvasActive = true;
                updateButtonAppearance(true);

                // Cek apakah konten ini sudah pernah di-type atau sama dengan konten terakhir
                if (!animate || (window.contentTyped && window.lastTypedContent === cleanedContent)) {
                    canvasEditor.style.opacity = 0;
                    canvasEditor.innerHTML = formatCanvasContent(cleanedContent);

                    // Animasi fade-in
                    setTimeout(() => {
                        canvasEditor.style.transition = 'opacity 0.5s ease';
                        canvasEditor.style.opacity = 1;
                    }, 100);
                } else {
                    setTimeout(() => {
                        typeContentInCanvas(cleanedContent);
                        window.contentTyped = true;
                        window.lastTypedContent = cleanedContent;
                    }, 100);
                }

                // Update status tombol navigasi
                updateVersionNavButtons();

                console.log("Canvas activated with", animate ? "animation" : "fade effect");
            } catch (error) {
                console.error("Error in showCanvas:", error);
            }
        }

        // Format konten untuk canvas
        function formatCanvasContent(content) {
            // Handle nested tags and complex markdown
            content = content
                .replace(/^######\s?(.+)$/gm, '<h6>$1</h6>')
                .replace(/^#####\s?(.+)$/gm, '<h5>$1</h5>')
                .replace(/^####\s?(.+)$/gm, '<h4>$1</h4>')
                .replace(/^###\s?(.+)$/gm, '<h3>$1</h3>')
                .replace(/^##\s?(.+)$/gm, '<h2>$1</h2>')
                .replace(/^#\s?(.+)$/gm, '<h1>$1</h1>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
                .replace(/`(.*?)`/g, '<code>$1</code>')
                .replace(/(\n\s*-\s.*)+/g, m => `<ul>${m.replace(/-\s(.*)/g, '<li>$1</li>')}</ul>`)
                .replace(/(\n\s*\d+\.\s.*)+/g, m => `<ol>${m.replace(/\d+\.\s(.*)/g, '<li>$1</li>')}</ol>`);

            // Handle paragraphs
            return content.split('\n\n').map(p => {
                if (!p.startsWith('<')) return `<p>${p}</p>`;
                return p;
            }).join('\n');
        }

        // Tutup canvas
        function closeCanvas() {
            console.log('closeCanvas function called'); // Debug log

            const canvasContainer = document.getElementById('canvas-container');
            const colUtama = document.querySelector('.col-utama');

            // Tambahkan class untuk animasi menutup
            if (isMobileDevice()) {
                canvasContainer.style.bottom = '-100%'; // Untuk mobile, geser ke bawah
            }

            canvasContainer.classList.remove('active');
            colUtama.classList.remove('canvas-active');

            window.canvasActive = false;
            updateButtonAppearance(false);

            console.log('Canvas classes removed'); // Debug log

            // Reset style tambahan yang mungkin ditambahkan
            setTimeout(() => {
                if (isMobileDevice()) {
                    canvasContainer.style.bottom = ''; // Reset style
                }
                console.log('Canvas transition completed'); // Debug log
                window.dispatchEvent(new Event('resize'));
            }, 300);
        }


        // Tambahkan fungsi untuk mengecek apakah device mobile
        function isMobileDevice() {
            return window.innerWidth <= 768;
        }

        // Salin konten canvas
        function copyCanvasContent() {
            const canvasEditor = document.getElementById('canvas-editor');
            const content = canvasEditor.innerText;

            navigator.clipboard.writeText(content)
                .then(() => {
                    // Tampilkan notifikasi sukses
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 p-3';
                    toast.style.zIndex = '5000';
                    toast.innerHTML = `
        <div class="toast show" role="alert">
          <div class="toast-header">
            <strong class="me-auto">SAGA AI</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">
            Konten berhasil disalin!
          </div>
        </div>
      `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                })
                .catch(err => {
                    console.error('Gagal menyalin konten:', err);
                });
        }

        // Tambahkan ini untuk menghubungkan tombol perbarui di canvas
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Pasang event listener untuk tombol-tombol canvas
                document.getElementById('close-canvas').addEventListener('click', function() {
                    closeCanvas();
                });

                document.getElementById('copy-canvas').addEventListener('click', function() {
                    copyCanvasContent();
                });

                // Cari dan ganti fungsi event listener untuk tombol update-canvas
                document.getElementById('update-canvas').addEventListener('click', function() {
                    // Jika mobile, tutup canvas terlebih dahulu
                    if (isMobileDevice()) {
                        closeCanvas();
                    }

                    // Buat pesan revisi dari AI
                    const messageWrapper = document.createElement('div');
                    messageWrapper.classList.add('d-flex', 'mb-3', 'justify-content-start');
                    messageWrapper.innerHTML = `
        <div class="d-flex chat-message align-items-center pt-1 pb-1 p-2 rounded-4" 
            style="background-color: transparent; max-width: 40rem;">
            <img src="${aiImage}" 
                class="chat-profile bg-white ms-2 me-2 rounded-circle" 
                alt="ai profile" 
                style="width: 20px; height: 20px; object-fit: cover;">
            <div class="chat-bubble rounded p-2 align-content-center"
                style="font-size: 13px; background-color: transparent; width: 100%">
                <p class="chat-paragraph">Silakan berikan instruksi revisi Anda.</p>
            </div>
        </div>
    `;

                    // Tambahkan ke chat container
                    const chatContainer = document.getElementById('chat-container');
                    chatContainer.appendChild(messageWrapper);
                    chatContainer.scrollTop = chatContainer.scrollHeight;

                    // Masukkan kata "revisi" ke input
                    const userInput = document.getElementById('user-input');
                    userInput.value = "revisi: ";

                    // Fokuskan input
                    userInput.focus();
                    userInput.setSelectionRange(userInput.value.length, userInput.value.length);
                });
            } catch (e) {
                console.error("Error setting up canvas buttons:", e);
            }
        });

        //         // Update konten canvas dengan AI
        //         async function updateCanvasWithAI() {
        //             const canvasEditor = document.getElementById('canvas-editor');
        //             const currentContent = canvasEditor.innerText;
        //             const userPrompt = prompt("Masukkan instruksi untuk mengubah konten:", "Revisi konten menjadi lebih singkat dan padat");

        //             if (!userPrompt) return; // Cancel jika user tidak memasukkan apa-apa

        //             // Tampilkan loading
        //             canvasEditor.innerHTML += '<div id="canvas-loading" class="text-center my-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Memperbarui konten...</p></div>';

        //             try {
        //                 const prompt = `Berikut ini adalah konten yang perlu direvisi:

        // ${currentContent}

        // Instruksi revisi: ${userPrompt}

        // Harap berikan versi yang direvisi sesuai instruksi. Berikan HANYA konten hasil revisi, tanpa penjelasan tambahan.`;

        //                 // Gunakan fungsi getAIResponse yang sudah ada
        //                 const response = await getAIResponse(prompt);

        //                 // Hapus loading
        //                 document.getElementById('canvas-loading')?.remove();

        //                 // Update canvas dengan respons AI
        //                 canvasEditor.innerHTML = formatCanvasContent(response);

        //             } catch (error) {
        //                 console.error('Error updating canvas with AI:', error);
        //                 document.getElementById('canvas-loading')?.remove();
        //                 canvasEditor.innerHTML += '<div class="alert alert-danger">Gagal memperbarui konten. Silakan coba lagi.</div>';
        //             }
        //         }

        // Tambahkan variable untuk tracking canvas
        window.canvasActive = false;
        window.currentCanvasContent = '';
        window.currentCanvasType = '';

        // Tambahkan di bagian atas file, bersama variable global lainnya
        window.canvasVersions = []; // Array untuk menyimpan versi canvas
        window.currentVersionIndex = 0; // Indeks versi yang aktif saat ini

        // Fungsi untuk menyimpan versi canvas
        async function saveCanvasVersion(content) {
            // Jika kita sedang di versi lama dan menambah versi baru,
            // hapus semua versi sesudahnya dari memory
            if (window.currentVersionIndex < window.canvasVersions.length - 1) {
                window.canvasVersions = window.canvasVersions.slice(0, window.currentVersionIndex + 1);
            }

            // Tambahkan versi baru ke memory
            window.canvasVersions.push(content);

            // Perbarui indeks
            window.currentVersionIndex = window.canvasVersions.length - 1;

            // Update status tombol navigasi
            updateVersionNavButtons();

            // Simpan ke database jika ada session id
            if (currentSessionId) {
                try {
                    // Hilangkan parameter version_number dari request body
                    const response = await fetch('save_canvas_version.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            session_id: currentSessionId,
                            content: content
                        })
                    });

                    const result = await response.json();
                    console.log('Canvas version save result:', result);

                    if (result.success) {
                        // Gunakan version_number yang dikirim dari server
                        console.log(`Versi canvas ${result.version_number} berhasil disimpan`);
                    } else {
                        console.error('Failed to save canvas version:', result.error);
                    }
                } catch (error) {
                    console.error('Error saving canvas version:', error);
                }
            }

            console.log(`Version saved. Total versions: ${window.canvasVersions.length}, Current: ${window.currentVersionIndex + 1}`);
        }


        // Fungsi untuk memuat versi canvas dari database
        async function loadCanvasVersions(sessionId) {
            try {
                const response = await fetch(`get_canvas_versions.php?session_id=${sessionId}`);
                const data = await response.json();

                if (data.success && data.versions && data.versions.length > 0) {
                    console.log(`Loaded ${data.versions.length} canvas versions from database`);

                    // Reset versi canvas di memory
                    window.canvasVersions = [];

                    // Urutkan versi berdasarkan version_number (pastikan urutan benar)
                    data.versions.sort((a, b) => a.version_number - b.version_number);

                    // Tambahkan konten dari setiap versi ke memory
                    data.versions.forEach(version => {
                        window.canvasVersions.push(version.content);
                    });

                    // Set versi terkini ke versi terakhir
                    window.currentVersionIndex = window.canvasVersions.length - 1;

                    // Update tampilan tombol navigasi
                    updateVersionNavButtons();

                    return data.versions;
                } else {
                    console.log('No canvas versions found in database');
                    return [];
                }
            } catch (error) {
                console.error('Error loading canvas versions:', error);
                return [];
            }
        }

        // Fungsi untuk update status tombol navigasi
        // Update fungsi untuk perbarui status tombol dan indikator versi
        function updateVersionNavButtons() {
            const prevBtn = document.getElementById('prev-canvas-version');
            const nextBtn = document.getElementById('next-canvas-version');
            const versionIndicator = document.getElementById('version-indicator');

            // Aktifkan/nonaktifkan tombol berdasarkan posisi saat ini
            prevBtn.disabled = window.currentVersionIndex <= 0;
            nextBtn.disabled = window.currentVersionIndex >= window.canvasVersions.length - 1;

            // Update indikator versi
            const currentVersion = window.currentVersionIndex + 1;
            const totalVersions = window.canvasVersions.length;
            versionIndicator.textContent = `Versi ${currentVersion} dari ${totalVersions}`;
        }

        async function navigateCanvasVersion(direction) {
            const canvasEditor = document.getElementById('canvas-editor');

            // Hitung indeks baru
            const newIndex = window.currentVersionIndex + direction;

            // Validasi indeks
            if (newIndex < 0 || newIndex >= window.canvasVersions.length) {
                console.error('Invalid version index');
                return;
            }

            // Animasi fade out
            canvasEditor.style.transition = 'opacity 0.3s ease';
            canvasEditor.style.opacity = '0';

            // Tunggu animasi fade out selesai
            await new Promise(resolve => setTimeout(resolve, 300));

            // Ganti konten dengan versi yang dipilih
            window.currentVersionIndex = newIndex;
            const versionContent = window.canvasVersions[newIndex];
            canvasEditor.innerHTML = formatCanvasContent(versionContent);

            // Update current canvas content untuk revisi berikutnya
            window.currentCanvasContent = versionContent;

            // Animasi fade in
            canvasEditor.style.opacity = '1';

            // Update status tombol
            updateVersionNavButtons();

            console.log(`Navigated to version ${newIndex + 1} of ${window.canvasVersions.length}`);
        }


        async function handleCanvasRevision(revisionRequest) {
            // Buat satu elemen chat yang akan diupdate secara bertahap
            const aiMessageElement = await addMessage('ai', `Saya akan merevisi ${window.currentCanvasType} sesuai permintaan Anda.`);

            // Ambil referensi ke bubble chat untuk diupdate nanti
            const chatBubble = aiMessageElement.querySelector('.chat-bubble');

            // Tunggu 1.5 detik untuk menampilkan pesan pertama
            await new Promise(resolve => setTimeout(resolve, 1500));

            // Fade out pesan pertama
            chatBubble.style.transition = 'opacity 0.5s ease';
            chatBubble.style.opacity = '0';

            // Tunggu fade out selesai
            await new Promise(resolve => setTimeout(resolve, 500));

            // Update ke "Sedang berpikir..." dan fade in
            chatBubble.innerHTML = '<em class="text-muted animate__animated animate__fadeIn">Merevisi canvas Anda, sebentar lagi ...</em>';
            chatBubble.style.opacity = '1';

            // Gunakan AI untuk merevisi konten
            const revisionPrompt = `
Berikut adalah ${window.currentCanvasType} yang perlu direvisi:

${window.currentCanvasContent}

Permintaan revisi: ${revisionRequest}

Tolong berikan versi yang sudah direvisi sesuai permintaan. Berikan HANYA konten hasil revisi lengkap, tanpa penjelasan tambahan.`;

            try {
                // Ambil referensi ke canvas editor
                const canvasEditor = document.getElementById('canvas-editor');

                // Simpan konten asli
                const originalContent = canvasEditor.innerHTML;

                // Buat wrapper untuk animasi pulse
                canvasEditor.innerHTML = `<div id="canvas-revision-content">${originalContent}</div>`;
                const contentWrapper = document.getElementById('canvas-revision-content');

                // Tambahkan animasi pulse ke konten canvas (animasi berpikir)
                let pulseCount = 0;
                const pulseAnimation = setInterval(() => {
                    contentWrapper.style.opacity = contentWrapper.style.opacity === '0.4' ? '1' : '0.4';
                    contentWrapper.style.transition = 'opacity 0.7s ease';
                    pulseCount++;
                    if (pulseCount > 10) {
                        clearInterval(pulseAnimation);
                        contentWrapper.style.opacity = '1';
                    }
                }, 700);

                // Dapatkan respons revisi
                const revisedContent = await getAIResponse(revisionPrompt);
                const cleanedRevisedContent = cleanCanvasContent(revisedContent);

                // Hentikan animasi pulse
                clearInterval(pulseAnimation);
                if (contentWrapper) contentWrapper.style.opacity = '1';

                // Animasi fade out untuk konten lama
                canvasEditor.style.transition = 'opacity 0.3s ease';
                canvasEditor.style.opacity = '0';

                // Tunggu fade out selesai
                await new Promise(resolve => setTimeout(resolve, 300));

                // Update konten dengan hasil revisi
                canvasEditor.innerHTML = formatCanvasContent(cleanedRevisedContent);

                // Animasi fade in untuk konten baru
                canvasEditor.style.opacity = '1';

                // Simpan konten baru
                window.currentCanvasContent = cleanedRevisedContent;

                // Simpan sebagai versi baru - PENTING
                saveCanvasVersion(cleanedRevisedContent);

                // Update status tombol navigasi
                updateVersionNavButtons();

                // Fade out pesan "Sedang berpikir..."
                chatBubble.style.opacity = '0';
                await new Promise(resolve => setTimeout(resolve, 500));

                // Update chat message ke pesan selesai dan fade in
                chatBubble.innerHTML = 'Canvas telah saya revisi, apakah ada yang bisa saya bantu kembali?';
                chatBubble.style.opacity = '1';

                // Simpan revisi ke database sebagai message juga jika diperlukan
                if (currentSessionId) {
                    try {
                        const response = await fetch('save_canvas_revision.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                session_id: currentSessionId,
                                revision_content: revisedContent
                            })
                        });

                        const result = await response.json();
                        console.log('Revision save result:', result);
                    } catch (error) {
                        console.error('Error saving canvas revision:', error);
                    }
                }
            } catch (error) {
                console.error('Error revising canvas content:', error);

                // Update chat message untuk menunjukkan error
                chatBubble.style.opacity = '0';
                await new Promise(resolve => setTimeout(resolve, 500));
                chatBubble.innerHTML = 'Maaf, terjadi kesalahan saat merevisi konten. Silakan coba lagi.';
                chatBubble.style.opacity = '1';
            }
        }

        // Tombol "Baru"
        const newChatButton = document.querySelector('button[onclick="createNewChatSession()"]');
        if (newChatButton) {
            const originalClickHandler = newChatButton.onclick;
            newChatButton.onclick = function(event) {
                if (window.canvasActive) {
                    closeCanvas();
                }
                return originalClickHandler.call(this, event);
            };
        }

        // Tombol "Riwayat"
        const historyButton = document.querySelector('button[data-bs-toggle="modal"][data-bs-target="#historyModal"]');
        if (historyButton) {
            historyButton.addEventListener('click', function() {
                if (window.canvasActive) {
                    closeCanvas();
                }
            });
        }

        // Saat Canvas aktif, hanya tampilkan icon
        updateButtonAppearance();

        async function fadeInRevisedContent(content) {
            const canvasEditor = document.getElementById('canvas-editor');

            // Format konten
            const formattedContent = formatCanvasContent(content);

            // Buat container untuk animasi fade-in
            const container = document.createElement('div');
            container.className = 'revised-content';
            container.style.opacity = '0';
            container.style.transition = 'opacity 0.8s ease';
            container.innerHTML = formattedContent;

            // Kosongkan canvas dan tambahkan container
            canvasEditor.innerHTML = '';
            canvasEditor.appendChild(container);

            // Animasi fade-in untuk seluruh konten sekaligus
            return new Promise(resolve => {
                setTimeout(() => {
                    container.style.opacity = '1';
                    setTimeout(resolve, 800); // Tunggu animasi selesai
                }, 100);
            });
        }

        // Tambahkan CSS untuk animasi
        const styleElement = document.createElement('style');
        styleElement.textContent = `
@keyframes fadeInParagraph {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-paragraph {
    animation: fadeInParagraph 0.5s ease forwards;
}

#canvas-revision-content {
    transition: opacity 0.7s ease;
}
`;
        document.head.appendChild(styleElement);

        function addCanvasButton(contentType) {
            const chatContainer = document.getElementById('chat-container');
            const lastMessage = chatContainer.lastElementChild;

            if (!lastMessage) return;

            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'canvas-button-container my-2';
            buttonContainer.innerHTML = `
                <button class="btn btn-sm bg-light text-black d-flex align-items-center ms-5 gap-2 px-3 py-2 border"
                       style="border-radius: 15px;">
                    <i class="bi bi-easel"></i>
                    <span class="button-text d-none d-md-inline" style="font-size: 13px;">Buka di Canvas</span>
                </button>
            `;

            // Tambahkan button setelah pesan terakhir
            chatContainer.insertBefore(buttonContainer, lastMessage.nextSibling);

            // Perbaikan event listener - tambahkan parameter false untuk menonaktifkan animasi
            buttonContainer.querySelector('button').addEventListener('click', function() {
                console.log("Canvas button clicked");
                // Pastikan konten tersimpan dan panggil showCanvas secara eksplisit
                if (window.currentCanvasContent) {
                    showCanvas(window.currentCanvasContent, window.currentCanvasType || 'konten', false); // Tambahkan parameter false
                } else {
                    console.error("Canvas content not available");
                    alert("Maaf, konten tidak tersedia. Silakan coba lagi.");
                }
            });
        }

        // Fungsi typing untuk canvas
        async function typeContentInCanvas(content) {
            const canvasEditor = document.getElementById('canvas-editor');
            if (!canvasEditor) return;

            // Simpan konten asli untuk referensi
            window.currentCanvasContent = content;

            // Format konten dengan HTML yang sesuai
            const formattedContent = formatCanvasContent(content);

            // Kosongkan canvas
            canvasEditor.innerHTML = '';

            // Buat elemen untuk menampung typing effect
            const typingContainer = document.createElement('div');
            typingContainer.className = 'typing-container';
            canvasEditor.appendChild(typingContainer);

            // Mulai typing animation
            let currentText = '';
            const textChunks = splitContentIntoChunks(formattedContent);

            for (const chunk of textChunks) {
                // Mulai dengan tag HTML jika ada
                if (chunk.startsWith('<')) {
                    currentText += chunk;
                    typingContainer.innerHTML = currentText;
                    // Tunggu sedikit untuk tag HTML
                    await new Promise(resolve => setTimeout(resolve, 5));
                } else {
                    // Typing effect untuk text biasa
                    for (let i = 0; i < chunk.length; i++) {
                        currentText += chunk[i];
                        typingContainer.innerHTML = currentText;
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }

                // Auto-scroll saat typing
                canvasEditor.scrollTop = canvasEditor.scrollHeight;
            }
        }

        // Setup editor untuk toolbar canvas footer
        document.addEventListener('DOMContentLoaded', function() {
            // Setup editor toolbar
            setupEditorToolbar();

            // Setup export button
            document.getElementById('export-canvas').addEventListener('click', exportToWord);
        });

        function setupEditorToolbar() {
            const formatButtons = document.querySelectorAll('.format-btn');
            const editor = document.getElementById('canvas-editor');

            formatButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const format = this.getAttribute('data-format');
                    formatText(format);
                });
            });

            // Function to apply formatting
            function formatText(format) {
                // Save current selection
                const selection = window.getSelection();
                const range = selection.getRangeAt(0);

                // Apply different formatting based on format type
                switch (format) {
                    case 'bold':
                        document.execCommand('bold', false, null);
                        break;
                    case 'italic':
                        document.execCommand('italic', false, null);
                        break;
                    case 'underline':
                        document.execCommand('underline', false, null);
                        break;
                    case 'h1':
                        document.execCommand('formatBlock', false, '<h1>');
                        break;
                    case 'h2':
                        document.execCommand('formatBlock', false, '<h2>');
                        break;
                    case 'h3':
                        document.execCommand('formatBlock', false, '<h3>');
                        break;
                    case 'ul':
                        document.execCommand('insertUnorderedList', false, null);
                        break;
                    case 'ol':
                        document.execCommand('insertOrderedList', false, null);
                        break;
                }

                // Focus back on editor
                editor.focus();
            }
        }

        function exportToWord() {
            // Get content
            const canvasEditor = document.getElementById('canvas-editor');
            const content = canvasEditor.innerHTML;

            // Create a properly formatted Word HTML document
            const wordContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" 
              xmlns:w="urn:schemas-microsoft-com:office:word" 
              xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <title>Export from SAGA AI</title>
            <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>90</w:Zoom>
                    <w:DoNotOptimizeForBrowser/>
                </w:WordDocument>
            </xml>
            <![endif]-->
            <style>
                body {
                    font-family: 'Calibri', sans-serif;
                    font-size: 12pt;
                }
                h1, h2, h3, h4, h5, h6 {
                    margin-top: 12pt;
                    margin-bottom: 6pt;
                    font-weight: bold;
                }
                p {
                    margin: 6pt 0;
                }
                /* Add more styles as needed */
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `;

            // Create Blob and download
            const blob = new Blob([wordContent], {
                type: 'application/msword'
            });
            const timestamp = new Date().toISOString().replace(/[^0-9]/g, '').slice(0, 14);
            const filename = `SAGA_Export_${timestamp}.doc`;

            // Create download link
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;

            // Append to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Show success message
            showExportNotification('Konten berhasil diunduh');
        }

        function showExportNotification(message) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '5000';
            toast.innerHTML = `
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-check-circle-fill me-2 text-success"></i>
                <strong class="me-auto">SAGA AI</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

            // Add to document
            document.body.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Check if we're on mobile and adjust UI
        function adjustForMobile() {
            if (window.innerWidth <= 768) {
                const toolbar = document.querySelector('.editor-toolbar');
                if (toolbar) {
                    // Keep only essential formatting options on mobile
                    const essentialFormats = ['bold', 'italic', 'ul'];
                    document.querySelectorAll('.format-btn').forEach(btn => {
                        const format = btn.getAttribute('data-format');
                        if (!essentialFormats.includes(format)) {
                            btn.style.display = 'none';
                        }
                    });
                }
            }
        }

        // Call on load and resize
        window.addEventListener('load', adjustForMobile);
        window.addEventListener('resize', adjustForMobile);

        // Helper untuk split konten menjadi chunks (HTML tags dan text)
        function splitContentIntoChunks(content) {
            const chunks = [];
            let currentChunk = '';
            let inTag = false;

            for (let i = 0; i < content.length; i++) {
                const char = content[i];

                if (char === '<') {
                    if (currentChunk) {
                        chunks.push(currentChunk);
                        currentChunk = '';
                    }
                    inTag = true;
                    currentChunk = char;
                } else if (char === '>' && inTag) {
                    currentChunk += char;
                    chunks.push(currentChunk);
                    currentChunk = '';
                    inTag = false;
                } else {
                    currentChunk += char;

                    // Buat chunk baru setiap beberapa karakter jika tidak dalam tag
                    if (!inTag && currentChunk.length >= 50 && [' ', '.', '!', '?', ',', '\n'].includes(char)) {
                        chunks.push(currentChunk);
                        currentChunk = '';
                    }
                }
            }

            if (currentChunk) {
                chunks.push(currentChunk);
            }

            return chunks;
        }

        // Fungsi untuk mengubah tampilan tombol
        function updateButtonAppearance(canvasIsActive = false) {
            const buttonTexts = document.querySelectorAll('.button-text');
            buttonTexts.forEach(text => {
                if (canvasIsActive || window.canvasActive) {
                    text.classList.add('d-none');
                } else {
                    text.classList.remove('d-none');
                }
            });
        }

        // Tambahkan fungsi ini sebelum fungsi sendMessage()
        function checkIfRevisionRequest(message) {
            const revisionKeywords = [
                'revisi', 'ubah', 'ganti', 'modifikasi', 'edit', 'perbaiki',
                'perbarui', 'update', 'koreksi', 'tambahkan', 'hapus'
            ];

            const messageLC = message.toLowerCase();
            return revisionKeywords.some(keyword => messageLC.includes(keyword));
        }


        // Setup event listeners setelah DOM loaded
        document.addEventListener('DOMContentLoaded', function() {

            // Pasang event listener untuk tombol-tombol navigasi versi
            const prevButton = document.getElementById('prev-canvas-version');
            const nextButton = document.getElementById('next-canvas-version');

            // Tambahkan event listeners untuk tombol navigasi versi
            document.getElementById('prev-canvas-version').addEventListener('click', function() {
                navigateCanvasVersion(-1); // Navigasi ke versi sebelumnya
            });

            document.getElementById('next-canvas-version').addEventListener('click', function() {
                navigateCanvasVersion(1); // Navigasi ke versi berikutnya
            });

            // Pasang event listener untuk tombol-tombol canvas
            document.getElementById('close-canvas').addEventListener('click', closeCanvas);
            document.getElementById('copy-canvas').addEventListener('click', copyCanvasContent);
            // document.getElementById('update-canvas').addEventListener('click', updateCanvasWithAI);

            // Modifikasi fungsi sendMessage yang sudah ada
            const originalSendMessage = sendMessage;
        });

        // Tambahkan kode ini untuk mengaktifkan swipe down untuk menutup canvas di mobile
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('canvas-container');
            let startY = 0;
            let distY = 0;

            function handleTouchStart(e) {
                startY = e.touches[0].clientY;
            }

            function handleTouchMove(e) {
                // Cek apakah sedang di bagian atas canvas
                const isAtTop = canvas.scrollTop <= 0;

                if (isAtTop) {
                    distY = e.touches[0].clientY - startY;

                    // Jika swipe ke bawah lebih dari 30px
                    if (distY > 30) {
                        e.preventDefault(); // Hindari scroll
                        canvas.style.bottom = `-${distY/3}px`; // Efek drag
                    }
                }
            }

            function handleTouchEnd(e) {
                // Jika swipe cukup jauh ke bawah, tutup canvas
                if (distY > 100) {
                    closeCanvas();
                } else {
                    // Reset posisi canvas
                    canvas.style.bottom = '0';
                }
                startY = 0;
                distY = 0;
            }

            canvas.addEventListener('touchstart', handleTouchStart, {
                passive: false
            });
            canvas.addEventListener('touchmove', handleTouchMove, {
                passive: false
            });
            canvas.addEventListener('touchend', handleTouchEnd);
        });


        // Tambahkan kode ini di bagian DOMContentLoaded untuk memperbaiki tombol silang di mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil referensi ke tombol close
            const closeButton = document.getElementById('close-canvas');

            // Hapus event listener lama jika ada (untuk mencegah duplikasi)
            closeButton.removeEventListener('click', closeCanvas);

            // Tambahkan listener baru dengan peningkatan area klik
            closeButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Hentikan event bubbling
                console.log('Close button clicked'); // Debug log
                closeCanvas();
            });

            // Tambahkan CSS untuk memperbesar area klik di mobile
            const style = document.createElement('style');
            style.textContent = `
        @media (max-width: 768px) {
            #close-canvas {
                padding: 10px !important;  /* Area klik lebih besar */
                z-index: 10000 !important; /* z-index sangat tinggi */
                position: relative;        /* Pastikan posisi relatif */
            }
            
            #close-canvas::before {
                content: '';
                position: absolute;
                top: -10px;
                right: -10px;
                bottom: -10px;
                left: -10px;
                z-index: -1;
            }
            
            .canvas-header {
                position: sticky;          /* Header tetap terlihat */
                top: 0;
                background: white;
                z-index: 9999;
                padding: 15px !important;
            }
        }
    `;
            document.head.appendChild(style);
        });
    </script>
    <!-- <script>
        // DEBUG CANVAS
        // Fungsi untuk menguji canvas
        window.testCanvas = function() {
            const longContent = `Ini adalah contoh konten panjang untuk menguji canvas. 
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
    Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
    Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
    nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in 
    reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla 
    pariatur. Excepteur sint occaecat cupidatat non proident, sunt in 
    culpa qui officia deserunt mollit anim id est laborum.`;

            showCanvas(longContent);
            console.log("Test canvas called");
        };

        // Tambahkan tombol tes di dokumen
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.createElement('button');
            testBtn.innerText = "Test Canvas";
            testBtn.style.position = "fixed";
            testBtn.style.right = "10px";
            testBtn.style.bottom = "10px";
            testBtn.style.zIndex = "9999";
            testBtn.onclick = window.testCanvas;
            document.body.appendChild(testBtn);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Force show canvas saat tombol diklik
            document.getElementById('close-canvas').addEventListener('click', function() {
                console.log("CANVAS DEBUG: Close button clicked");
                closeCanvas();
            });

            // Tambahkan handler untuk tombol show canvas yang bisa diuji
            const showCanvasBtn = document.createElement('button');
            showCanvasBtn.innerText = "Show Canvas";
            showCanvasBtn.style.position = "fixed";
            showCanvasBtn.style.right = "10px";
            showCanvasBtn.style.bottom = "50px";
            showCanvasBtn.style.zIndex = "9999";
            showCanvasBtn.onclick = function() {
                const canvasContainer = document.getElementById('canvas-container');
                const colUtama = document.querySelector('.col-utama');

                // Force toggle class active
                canvasContainer.classList.toggle('active');
                colUtama.classList.toggle('canvas-active');
                console.log("CANVAS DEBUG: Toggled canvas classes manually");
            };
            document.body.appendChild(showCanvasBtn);
        });

        // Tambahkan ini di bagian akhir file
        function testCanvasManual() {
            try {
                // Tes sederhana tanpa menggunakan formatCanvasContent
                const theCanvas = document.getElementById('canvas-container');
                const editor = document.getElementById('canvas-editor');
                const main = document.querySelector('.col-utama');

                console.log("Canvas elements:", {
                    canvas: !!theCanvas,
                    editor: !!editor,
                    main: !!main
                });

                if (theCanvas && editor) {
                    editor.innerHTML = '<p>Ini adalah tes canvas manual</p><p>Baris kedua untuk test</p>';
                    theCanvas.classList.add('active');
                    main.classList.add('canvas-active');
                    console.log("Manual canvas activation attempted");
                }
            } catch (e) {
                console.error("Manual canvas test error:", e);
            }
        }

        // Tambahkan tombol tes manual
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Tombol test canvas lama
                const testBtn = document.createElement('button');
                testBtn.innerText = "Test Canvas";
                testBtn.style.position = "fixed";
                testBtn.style.right = "10px";
                testBtn.style.bottom = "10px";
                testBtn.style.zIndex = "9999";
                testBtn.onclick = window.testCanvas;
                document.body.appendChild(testBtn);

                // Tombol test canvas manual baru
                const manualBtn = document.createElement('button');
                manualBtn.innerText = "Manual Canvas Test";
                manualBtn.style.position = "fixed";
                manualBtn.style.right = "10px";
                manualBtn.style.bottom = "50px";
                manualBtn.style.zIndex = "9999";
                manualBtn.onclick = testCanvasManual;
                document.body.appendChild(manualBtn);
            } catch (e) {
                console.error("Error creating test buttons:", e);
            }
        });
    </script> -->
    <!-- <script>
        // Debugging helper - tambahkan di bagian bawah dokumen
        document.addEventListener('DOMContentLoaded', function() {
            const debugBtn = document.createElement('button');
            debugBtn.textContent = "Debug Canvas";
            debugBtn.style.position = "fixed";
            debugBtn.style.bottom = "10px";
            debugBtn.style.right = "10px";
            debugBtn.style.zIndex = "9999";
            debugBtn.style.padding = "5px 10px";
            debugBtn.style.backgroundColor = "#f8f9fa";
            debugBtn.style.border = "1px solid #dee2e6";
            debugBtn.style.borderRadius = "4px";

            debugBtn.addEventListener('click', function() {
                console.log("Canvas active:", window.canvasActive);
                console.log("Current canvas type:", window.currentCanvasType);
                console.log("Current canvas content available:", !!window.currentCanvasContent);

                const canvas = document.getElementById('canvas-container');
                console.log("Canvas element:", canvas);
                console.log("Canvas has active class:", canvas.classList.contains('active'));

                if (window.currentCanvasContent) {
                    showCanvas(window.currentCanvasContent, window.currentCanvasType || 'debug');
                } else {
                    showCanvas("Ini adalah konten test untuk canvas debugging", "debug");
                }
            });

            document.body.appendChild(debugBtn);
        });
    </script> -->
    </div>

</body>

</html>