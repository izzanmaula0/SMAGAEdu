<?php
include 'includes/session_config.php';
require "koneksi.php";


if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// ini ujian.php


// Ambil userid dari session
$userid = $_SESSION['userid'];

// Get exams for classes the student is enrolled in
// Get exams for classes the student is enrolled in
$query = "SELECT u.*, k.mata_pelajaran, k.tingkat, k.background_image,
          (SELECT COUNT(*) FROM jawaban_ujian ju 
           WHERE ju.ujian_id = u.id AND ju.siswa_id = s.id) as sudah_ujian
          FROM ujian u
          JOIN kelas k ON u.kelas_id = k.id 
          JOIN kelas_siswa ks ON k.id = ks.kelas_id
          JOIN siswa s ON ks.siswa_id = s.id
          WHERE s.username = '$userid' AND u.is_hidden = 0
          ORDER BY u.tanggal_mulai ASC";

$result = mysqli_query($koneksi, $query);




// function untuk waktu ujian
function getExamStatus($startTime, $endTime)
{
    date_default_timezone_set('Asia/Jakarta');
    $now = time();
    $start = strtotime($startTime);
    $end = strtotime($endTime);

    if ($now < $start) {
        $diffSeconds = $start - $now;
        $hours = floor($diffSeconds / 3600);
        $minutes = floor(($diffSeconds % 3600) / 60);
        return [
            'status' => 'waiting',
            'countdown' => "$hours jam $minutes menit"
        ];
    } elseif ($now >= $start && $now <= $end) {
        return [
            'status' => 'ongoing',
            'countdown' => ''
        ];
    } else {
        return [
            'status' => 'ended',
            'countdown' => ''
        ];
    }
}

$query_siswa = "SELECT s.*, 
    k.nama_kelas AS kelas_saat_ini 
    FROM siswa s 
    LEFT JOIN kelas_siswa ks ON s.id = ks.siswa_id 
    LEFT JOIN kelas k ON ks.kelas_id = k.id 
    WHERE s.username = ?";

$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "s", $userid);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <title>Ujian - SMAGAEdu</title>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5XXM5FLKYE"></script>
</head>
<style>
    body {
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }

    /* modal */
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
</style>

<style>
    .col-utama {
        padding-top: 0.7rem;
        padding-left: 14rem !important;
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

    @media (max-width: 768px) {
        .col-utama {
            padding-left: 0rem !important;
        }

        .judul {
            display: none;
        }

        .salam {
            display: block;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
    }
</style>


<body>

    <?php include 'includes/styles.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav siswa.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>


        </div>
    </div>


    <!-- ini isi kontennya -->
    <!-- Isi konten -->
    <div class="col col-utama mt-1 p-md-0 mt-md-0">
        <div class="p-md-3 pb-md-2 d-flex ms-3 mt-md-0 ms-md-0 p-3 mb-1 salam justify-content-between align-items-center">
            <div class="mt-md-2">
                <h3 class="fw-bold mb-0">Ujian</h3>
            </div>
            <div class="d-flex d-none d-md-block">
                <button type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_jadwal_ujian"
                    class="btn btn-light border d-flex align-items-center gap-2 px-3" style="border-radius: 15px;">
                    <i class="bi bi-calendar-date"></i>
                    <span class="d-none d-md-inline" style="font-size: 12px;">Jadwal Ujian</span>
                </button>
            </div>


        </div>


        <!-- Modal Jadwal Ujian -->
        <div class="modal fade" id="modal_jadwal_ujian" tabindex="-1" aria-labelledby="jadwalUjianModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body p-4">
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3 fw-bold">Tidak Ada Data</h5>
                            <p class="text-muted">Saat ini belum ada jadwal ujian yang tersedia</p>
                        </div>
                    </div>
                    <div class="modal-footer d-flex">
                        <button type="button" class="btn flex-fill" data-bs-dismiss="modal" style="background-color: rgb(218, 119, 86); color: white; border-radius: 12px;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Jumbotron yang akan berubah berdasarkan tab yang aktif -->
        <div class="jumbotron jumbotron-fluid mb-md-2 d-none d-md-block">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <!-- Konten untuk tab Diikuti (khusus) -->
                        <div id="jumbotron-khusus" class="jumbotron-content active">
                            <h2 class="display-5">
                                Aggap Ujian ini Game, dan <span style="color: rgb(198, 99, 66);">Kamu Jagoannya!</span>
                            </h2>
                            <p class="lead">Pilih ujian di bawah sesuai dengan jadwal ujian kamu, tetap sportif dan jangan lupa berdoa.</p>
                        </div>
                    </div>

                    <div class="col-md-6 text-center d-none d-md-block">
                        <!-- Gambar untuk tab Diikuti (khusus) -->
                        <img src="assets/ujian_siswa.png" class="img-fluid jumbotron-image rounded-4" id="jumbotron-image" alt="Ilustrasi kelas" style="max-height: 20rem;">
                    </div>
                </div>
            </div>
        </div>


        <style>
            .class-card {
                border-radius: 12px;
                overflow: hidden;
                background: white;
                opacity: 0;
                transform: translateY(20px);
                animation: fadeInUp 0.5s ease forwards;
                will-change: transform;
            }

            .class-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

            .class-banner {
                height: 160px;
                background-size: cover;
                background-position: center;
                position: relative;
            }

            .profile-circle {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                border: 3px solid #fff;
                position: absolute;
                bottom: -35px;
                left: 20px;
                right: 20px;
                object-fit: cover;
                background: #fff;
            }

            .class-content {
                padding: 20px;
                padding-top: 50px;
            }

            .class-title {
                font-size: 18px;
                font-weight: 600;
            }

            /* Add delay for each card */
            .col-12:nth-child(1) .class-card {
                animation-delay: 0.1s;
            }

            .col-12:nth-child(2) .class-card {
                animation-delay: 0.2s;
            }

            .col-12:nth-child(3) .class-card {
                animation-delay: 0.3s;
            }

            .col-12:nth-child(4) .class-card {
                animation-delay: 0.4s;
            }

            .col-12:nth-child(5) .class-card {
                animation-delay: 0.5s;
            }

            .col-12:nth-child(6) .class-card {
                animation-delay: 0.6s;
            }

            .btn-enter {
                background: rgb(218, 119, 86);
                color: #fff;
                padding: 8px 20px;
                border-radius: 6px;
                display: inline-flex;
                align-items: center;
                transition: background 0.3s;
            }

            .btn-enter:hover {
                background: rgb(198, 99, 66);
                color: #fff;
            }

            .btn-more {
                background: none;
                border: none;
                padding: 8px;
                color: #666;
            }

            /* View Toggle Styling */
            .view-toggle-container {
                flex-shrink: 0;
                margin-left: 10px;
                margin-right: 1rem;
            }

            .view-toggle-btn {
                width: 36px;
                height: 36px;
                border: none;
                background: transparent;
                color: #666;
                border-radius: 50% !important;
                transition: all 0.3s ease;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .view-toggle-btn:hover {
                background: rgba(218, 119, 86, 0.1);
                color: #da7756;
            }

            .view-toggle-btn.active {
                background: #da7756;
                color: white;
            }

            .view-toggle-btn i {
                font-size: 14px;
            }

            /* List View Layout untuk Ujian */
            .ujian-list-view {
                gap: 0.5rem !important;
                margin-top: 1rem;
            }

            .ujian-list-view .list-item-col {
                margin-bottom: 0.5rem !important;
                margin-top: 0 !important;
            }

            .ujian-list-view .class-card {
                border-radius: 12px;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                padding: 0;
                height: 120px;
                margin-bottom: 0;
                cursor: pointer;
            }

            .ujian-list-view .class-banner {
                width: 150px;
                height: 120px;
                border-radius: 12px 0 0 12px;
                flex-shrink: 0;
                position: relative;
            }

            .ujian-list-view .profile-circle {
                position: absolute;
                bottom: 8px;
                right: 8px;
                width: 40px;
                height: 40px;
                left: 8rem;
            }

            .ujian-list-view .class-content {
                padding: 1rem 1.5rem;
                flex: 1;
                height: 100%;
                display: flex;
                align-items: stretch;
                justify-content: space-between;
                flex-direction: row;
                gap: 1rem;
            }

            .ujian-list-view .ujian-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                min-width: 0;
                overflow: hidden;
            }

            .ujian-list-view .ujian-actions {
                margin: 0;
                width: 180px;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .ujian-list-view .ujian-info {
                flex: 1;
            }

            .ujian-list-view .class-title {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }

            .ujian-list-view .class-meta {
                margin-bottom: 0;
            }

            .ujian-list-view .ujian-actions {
                margin: 0;
                width: 200px;
                flex-shrink: 0;
            }

            .ujian-list-view .ujian-actions .btn {
                width: 100%;
            }

            /* Responsive untuk list view */
            @media (max-width: 768px) {
                .view-toggle-container {
                    display: none;
                }

                .ujian-list-view .class-card {
                    height: auto;
                    flex-direction: column;
                    align-items: stretch;
                }

                .ujian-list-view .class-banner {
                    width: 100%;
                    height: 120px;
                    border-radius: 12px 12px 0 0;
                }

                .ujian-list-view .profile-circle {
                    bottom: -20px;
                    right: 20px;
                    width: 50px;
                    height: 50px;
                }

                .ujian-list-view .class-content {
                    flex-direction: column;
                    align-items: stretch;
                    height: auto;
                    padding-top: 2rem;
                }

                .ujian-list-view .ujian-actions {
                    width: 100%;
                    margin-top: 1rem;
                }
            }
        </style>

        <!-- Filter Section -->
        <div class="filter-container ms-4 ms-md-0 mb-4">
            <div class="d-flex align-items-center">
                <div class="filter-pills-wrapper d-flex justify-content-start">
                    <button class="btn btn-light border ms-2 flex-shrink-0 me-2" id="addFilterBtn" data-bs-toggle="modal" data-bs-target="#addFilterModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <div class="filter-pills d-flex gap-2">
                        <button class="btn btn-filter" data-filter="all">Semua</button>
                        <button class="btn btn-filter" data-filter="past">Sudah Lampau</button>
                        <button class="btn btn-filter active" data-filter="today">Hari Ini</button>
                        <button class="btn btn-filter" data-filter="upcoming">Akan Datang</button>
                        <!-- Filter dinamis akan ditambahkan di sini -->
                        <div id="custom-filters-container" class="d-flex gap-2">
                            <!-- Custom filters will be generated here -->
                        </div>
                    </div>
                </div>
                <div class="view-toggle-container d-none d-md-flex align-items-center gap-1 border rounded-pill p-1 bg-light">
                    <button class="btn btn-sm view-toggle-btn active" data-view="card" title="Tampilan Kartu">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="btn btn-sm view-toggle-btn" data-view="list" title="Tampilan List">
                        <i class="bi bi-list-ul"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Filter -->
        <div class="modal fade" id="addFilterModal" tabindex="-1" aria-labelledby="addFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addFilterModalLabel">Tambah Filter Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label small mb-2">Nama Filter</label>
                            <input type="text" class="form-control" id="newFilterName" placeholder="Masukkan nama filter">
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small mb-0">Pilih Ujian untuk Filter Ini</label>
                                <div class="form-check mb-0 d-none d-md-block">
                                    <input class="form-check-input" type="checkbox" id="select-all-exams">
                                    <label class="form-check-label small" for="select-all-exams">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            <div class="rounded mt-3" style="max-height: 250px; overflow-y: auto;">
                                <div id="ujian-list" class="d-flex flex-column gap-2">
                                    <!-- Daftar ujian akan dimuat di sini -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn border" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn color-web text-white" id="saveFilterBtn">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .filter-container {
                background-color: white;
                margin-bottom: 1.5rem;
                border: 1px solid transparent;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .filter-pills-wrapper {
                flex: 1;
                overflow-x: auto;
                -ms-overflow-style: none;
                scrollbar-width: none;
                padding-bottom: 5px;
            }

            .filter-pills-wrapper::-webkit-scrollbar {
                display: none;
            }

            .filter-pills {
                white-space: nowrap;
            }

            .btn-filter {
                background-color: white;
                border: 1px solid #dee2e6;
                border-radius: 10px;
                padding: 6px 16px;
                font-size: 14px;
                transition: all 0.3s ease;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .btn-filter:hover {
                background-color: #f1f1f1;
                color: #da7756;
            }

            .btn-filter.active {
                background-color: #da7756;
                color: white;
                border-color: #da7756;
            }

            #addFilterBtn {
                width: 38px;
                height: 37px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                border: 1px solid #dee2e6;
                background-color: white;
            }

            #addFilterBtn:hover {
                background-color: #f1f1f1;
                color: #da7756;
            }

            .ujian-item {
                padding: 12px 15px;
                border-radius: 8px;
                background: white;
                border: 1px solid #dee2e6;
                transition: all 0.2s ease;
                margin-bottom: 8px;
            }

            .ujian-item:hover {
                background-color: #f8f9fa;
                border-color: #da7756;
            }

            .ujian-item .form-check {
                padding-left: 2rem;
            }

            .ujian-item .form-check-input {
                margin-top: 0.5rem;
            }

            .ujian-item .form-check-label {
                padding-left: 0.5rem;
                cursor: pointer;
            }

            .form-check-input:checked {
                background-color: #da7756;
                border-color: #da7756;
            }

            #custom-filters-container {
                display: inline-flex;
            }
        </style>

        <script>
            // Global variables
            let examMapping = {};
            let customFilters = [];

            document.addEventListener('DOMContentLoaded', function() {
                // Load exam mapping
                buildExamMapping();

                // Setup default filter buttons
                setupDefaultFilters();

                // Try to load saved filters
                try {
                    loadCustomFilters();
                } catch (e) {
                    console.error("Error loading custom filters:", e);
                }

                // Setup modal events
                setupModalEvents();

                // Setup view toggle
                setupViewToggle();

                filterExams('today'); // Default filter
            });

            // Setup default filter buttons
            function setupDefaultFilters() {
                document.querySelectorAll('.btn-filter').forEach(btn => {
                    if (!btn.hasAttribute('data-custom-filter')) {
                        btn.addEventListener('click', function() {
                            // Set active state
                            document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
                            this.classList.add('active');

                            // Apply filter
                            const filter = this.getAttribute('data-filter');
                            filterExams(filter);
                        });
                    }
                });
            }

            // Build exam mapping
            function buildExamMapping() {
                examMapping = {};
                const examCards = document.querySelectorAll('.class-card');

                examCards.forEach((card) => {
                    // Try to find exam ID from links
                    let examId = '';
                    const linkElement = card.querySelector('a[href^="buat_soal.php?ujian_id="]');

                    if (linkElement) {
                        const href = linkElement.getAttribute('href');
                        const match = href.match(/ujian_id=(\d+)/);
                        if (match && match[1]) {
                            examId = match[1];
                        }
                    }

                    // Extract exam title
                    const titleElement = card.querySelector('.class-title');
                    const examTitle = titleElement ? titleElement.textContent.trim() : '';

                    // Extract date and time
                    let examDate = '';
                    let examTime = '';

                    const dateElements = card.querySelectorAll('.d-flex.align-items-center');

                    dateElements.forEach(el => {
                        const icon = el.querySelector('i');
                        if (icon && icon.classList.contains('bi-calendar-event')) {
                            const textElement = el.querySelector('.text-secondary');
                            if (textElement) {
                                examDate = textElement.textContent.trim();
                            }
                        }

                        if (icon && icon.classList.contains('bi-clock')) {
                            const textElement = el.querySelector('.text-secondary');
                            if (textElement) {
                                examTime = textElement.textContent.trim();
                            }
                        }
                    });

                    // Extract subject
                    let subject = '';
                    const subjectElements = card.querySelectorAll('.d-flex.align-items-center');

                    subjectElements.forEach(el => {
                        const icon = el.querySelector('i');
                        if (icon && icon.classList.contains('bi-book')) {
                            const textElement = el.querySelector('.text-dark');
                            if (textElement) {
                                subject = textElement.textContent.trim();
                            }
                        }
                    });

                    examMapping[examId] = {
                        id: examId,
                        title: examTitle,
                        date: examDate,
                        time: examTime,
                        subject: subject,
                        element: card
                    };
                });

                console.log("Exam mapping built:", examMapping);
            }

            // Setup modal events
            function setupModalEvents() {
                // Load exam list when modal opens
                const addFilterModal = document.getElementById('addFilterModal');
                if (addFilterModal) {
                    addFilterModal.addEventListener('show.bs.modal', function() {
                        populateExamList();
                    });
                }

                // Setup select all checkbox
                const selectAllCheckbox = document.getElementById('select-all-exams');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.ujian-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                }

                // Setup save button
                const saveFilterBtn = document.getElementById('saveFilterBtn');
                if (saveFilterBtn) {
                    saveFilterBtn.addEventListener('click', function() {
                        saveNewFilter();
                    });
                }
            }

            // Populate exam list in modal
            function populateExamList() {
                const container = document.getElementById('ujian-list');
                if (!container) return;

                let html = '';

                // Check if we have any exams
                if (Object.keys(examMapping).length === 0) {
                    container.innerHTML = '<div class="text-center py-3 text-muted small">Tidak ada ujian tersedia</div>';
                    return;
                }

                // Build HTML for exam checkboxes
                for (const [examId, examData] of Object.entries(examMapping)) {
                    html += `
            <div class="ujian-item">
                <div class="form-check">
                    <input class="form-check-input ujian-checkbox" type="checkbox" 
                           value="${examId}" id="ujian-check-${examId}" 
                           data-ujian-title="${examData.title}">
                    <label class="form-check-label" for="ujian-check-${examId}">
                        <div>
                            <span class="d-block fw-bold">${examData.title}</span>
                            <span class="d-block text-muted small mt-1">
                                ${examData.subject} ${examData.date ? ` - ${examData.date}` : ''}
                            </span>
                        </div>
                    </label>
                </div>
            </div>`;
                }

                container.innerHTML = html;
            }

            // Save new filter
            function saveNewFilter() {
                const filterName = document.getElementById('newFilterName').value.trim();

                if (!filterName) {
                    alert('Nama filter tidak boleh kosong');
                    return;
                }

                // Get selected exam IDs
                const selectedExams = [];
                const checkboxes = document.querySelectorAll('.ujian-checkbox:checked');

                checkboxes.forEach(checkbox => {
                    selectedExams.push(checkbox.value);
                });

                if (selectedExams.length === 0) {
                    alert('Pilih minimal satu ujian untuk filter ini');
                    return;
                }

                // Check if filter already exists
                const existingIndex = customFilters.findIndex(f => f.name === filterName);

                if (existingIndex >= 0) {
                    // Update existing filter
                    customFilters[existingIndex].examIds = selectedExams;

                    // Update UI
                    const existingBtn = document.querySelector(`[data-custom-filter="${filterName}"]`);
                    if (existingBtn) existingBtn.remove();
                } else {
                    // Add new filter
                    customFilters.push({
                        name: filterName,
                        examIds: selectedExams
                    });
                }

                // Save filters
                saveCustomFilters();

                // Add or update button
                addCustomFilterButton(filterName, selectedExams);

                // Close modal
                const addFilterModal = document.getElementById('addFilterModal');
                if (addFilterModal) {
                    const modal = bootstrap.Modal.getInstance(addFilterModal);
                    if (modal) modal.hide();
                }

                // Clear input
                document.getElementById('newFilterName').value = '';
            }

            // Save custom filters
            function saveCustomFilters() {
                try {
                    // Store in hidden form field
                    const formField = document.getElementById('customFiltersData');
                    if (formField) {
                        formField.value = JSON.stringify(customFilters);
                    }

                    // Store in localStorage
                    localStorage.setItem('smagaEduExamCustomFilters', JSON.stringify(customFilters));

                    console.log("Custom filters saved:", customFilters);
                } catch (e) {
                    console.error("Error saving custom filters:", e);
                }
            }

            // Load custom filters
            function loadCustomFilters() {
                // Try to get from hidden form field first
                let filtersData = null;
                const formField = document.getElementById('customFiltersData');

                if (formField && formField.value) {
                    try {
                        filtersData = JSON.parse(formField.value);
                    } catch (e) {
                        console.error("Error parsing form field data:", e);
                    }
                }

                // If not found, try localStorage
                if (!filtersData) {
                    try {
                        const storedData = localStorage.getItem('smagaEduExamCustomFilters');
                        if (storedData) {
                            filtersData = JSON.parse(storedData);
                        }
                    } catch (e) {
                        console.error("Error loading from localStorage:", e);
                    }
                }

                // If we have data, process it
                if (filtersData && Array.isArray(filtersData)) {
                    customFilters = filtersData;

                    // Add buttons for each custom filter
                    const container = document.getElementById('custom-filters-container');
                    if (container) {
                        container.innerHTML = ''; // Clear existing

                        customFilters.forEach(filter => {
                            addCustomFilterButton(filter.name, filter.examIds);
                        });
                    }

                    console.log("Custom filters loaded:", customFilters);
                }
            }

            // Add custom filter button
            function addCustomFilterButton(filterName, examIds) {
                const container = document.getElementById('custom-filters-container');
                if (!container) return;

                // Create button
                const btn = document.createElement('button');
                btn.className = 'btn btn-filter d-flex align-items-center';
                btn.setAttribute('data-filter', 'custom');
                btn.setAttribute('data-custom-filter', filterName);
                btn.setAttribute('data-exam-ids', examIds.join(','));

                // Create filter text
                const textSpan = document.createElement('span');
                textSpan.textContent = filterName;
                btn.appendChild(textSpan);

                // Create remove button
                const removeBtn = document.createElement('i');
                removeBtn.className = 'bi bi-x ms-2';
                removeBtn.style.fontSize = '14px';

                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    removeCustomFilter(filterName, btn);
                });

                btn.appendChild(removeBtn);

                // Add click handler
                btn.addEventListener('click', function() {
                    // Set active state
                    document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Apply filter
                    const examIdsAttr = this.getAttribute('data-exam-ids');
                    const examIds = examIdsAttr ? examIdsAttr.split(',') : [];

                    filterExams('custom', examIds);
                });

                // Add to DOM
                container.appendChild(btn);
            }

            // Remove custom filter
            function removeCustomFilter(filterName, btnElement) {
                // Create a modal for confirmation
                const modal = document.getElementById('deleteFilterModal') || createDeleteFilterModal();

                // Set the filter name in the modal
                const filterNameElement = modal.querySelector('.filter-name');
                if (filterNameElement) filterNameElement.textContent = filterName;

                // Set up the confirm button
                const confirmBtn = modal.querySelector('#confirmDeleteFilterBtn');
                if (confirmBtn) {
                    // Remove previous event listeners
                    const newConfirmBtn = confirmBtn.cloneNode(true);
                    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

                    // Add new event listener
                    newConfirmBtn.addEventListener('click', function() {
                        // Remove from array
                        customFilters = customFilters.filter(f => f.name !== filterName);

                        // Save updated filters
                        saveCustomFilters();

                        // Remove button
                        btnElement.remove();

                        // If this was the active filter, switch to "All"
                        if (btnElement.classList.contains('active')) {
                            const allBtn = document.querySelector('[data-filter="all"]');
                            if (allBtn) allBtn.click();
                        }

                        // Hide modal
                        bootstrap.Modal.getInstance(modal).hide();
                    });
                }

                // Show the modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }

            // Create delete filter confirmation modal
            function createDeleteFilterModal() {
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'deleteFilterModal';
                modal.tabIndex = '-1';
                modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                    <h5 class="mt-3 fw-bold">Hapus Filter</h5>
                    <p class="mb-4">Apakah Anda yakin ingin menghapus filter "<span class="filter-name"></span>"?</p>
                    <div class="d-flex gap-2 btn-group">
                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <button type="button" id="confirmDeleteFilterBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    `;

                document.body.appendChild(modal);
                return modal;
            }

            // Perbaikan pada fungsi filterExams untuk bagian past filter
            function filterExams(filter, examIds = []) {
                console.log('Filtering by:', filter);

                // Get current date for comparison
                const now = new Date();
                const today = now.setHours(0, 0, 0, 0); // Set waktu ke 00:00:00

                // Function to parse exam date dengan tambahan waktu
                function parseExamDate(dateStr) {
                    if (!dateStr) return null;

                    // Extract date part after comma: "Monday, 25 March 2024" -> "25 March 2024"
                    const dateParts = dateStr.split(', ')[1].split(' ');
                    const day = parseInt(dateParts[0], 10);

                    const monthNames = {
                        'January': 0,
                        'February': 1,
                        'March': 2,
                        'April': 3,
                        'May': 4,
                        'June': 5,
                        'July': 6,
                        'August': 7,
                        'September': 8,
                        'October': 9,
                        'November': 10,
                        'December': 11,
                        'Januari': 0,
                        'Februari': 1,
                        'Maret': 2,
                        'April': 3,
                        'Mei': 4,
                        'Juni': 5,
                        'Juli': 6,
                        'Agustus': 7,
                        'September': 8,
                        'Oktober': 9,
                        'November': 10,
                        'Desember': 11
                    };

                    const month = monthNames[dateParts[1]];
                    const year = parseInt(dateParts[2], 10);

                    return new Date(year, month, day).setHours(0, 0, 0, 0); // Set waktu ke 00:00:00
                }

                const examCards = document.querySelectorAll('.class-card');

                examCards.forEach((card) => {
                    const parentCol = card.closest('.col-12.col-md-6.col-lg-4') ||
                        card.closest('.col-12.list-item-col') ||
                        card.closest('[class*="col-12"]');
                    if (!parentCol) return;

                    // Get exam date
                    let examDate = null;
                    const dateElement = card.querySelector('.bi-calendar-event')?.closest('.d-flex')?.querySelector('.text-secondary');
                    if (dateElement) {
                        examDate = parseExamDate(dateElement.textContent.trim());
                    }

                    // Get exam status
                    let examStatus = {
                        isToday: examDate === today,
                        isPast: examDate < today,
                        isFuture: examDate > today
                    };

                    // Apply filter
                    let shouldShow = false;
                    switch (filter) {
                        case 'all':
                            shouldShow = true;
                            break;
                        case 'past':
                            shouldShow = examStatus.isPast;
                            break;
                        case 'today':
                            shouldShow = examStatus.isToday;
                            break;
                        case 'upcoming':
                            shouldShow = examStatus.isFuture;
                            break;
                        case 'custom':
                            const examId = card.querySelector('a[href*="ujian_id="], a[href*="id="]')?.href.match(/[?&](ujian_id|id)=(\d+)/)?.[2];
                            shouldShow = examIds.includes(examId);
                            break;
                        default:
                            shouldShow = true;
                    }

                    parentCol.style.display = shouldShow ? 'block' : 'none';
                });

                // Check if all exams are hidden
                setTimeout(() => {
                    // Hapus pesan lama terlebih dahulu
                    const noExamsMessage = document.querySelector('.no-exams-message');
                    if (noExamsMessage) {
                        noExamsMessage.remove();
                    }

                    // Cari semua card ujian yang visible (tidak termasuk pesan no-exams)
                    const allExamCards = document.querySelectorAll('.row.g-4.m-0 > .col-12.col-md-6.col-lg-4');
                    let visibleCount = 0;

                    allExamCards.forEach(col => {
                        // Cek apakah ini benar-benar card ujian (memiliki .class-card di dalamnya)
                        if (col.querySelector('.class-card') && col.style.display !== 'none') {
                            visibleCount++;
                        }
                    });

                    if (visibleCount === 0) {
                        // Buat pesan baru dengan filter yang benar
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'col-12 py-5 text-center no-exams-message';

                        let filterMessage = 'tersedia';
                        if (filter === 'today') {
                            filterMessage = 'hari ini';
                        } else if (filter === 'past') {
                            filterMessage = 'yang sudah lewat';
                        } else if (filter === 'upcoming') {
                            filterMessage = 'yang akan datang';
                        } else if (filter === 'custom') {
                            filterMessage = 'dalam filter ini';
                        }

                        messageDiv.innerHTML = `
            <div class="my-5 mx-5 mx-md-auto text-center">
                <i class="ti ti-mood-wink-2" style="font-size: 5rem; color: rgb(218, 119, 86);"></i>
                <h5 class="mt-3 fw-bold" style="font-size:28px">Horey, Ga Ada Ujian ${filterMessage}!</h5>
                <p class="text-muted">Tidak ada ujian ${filterMessage}, masih ada buat main wkwk <br class="d-none d-md-block"> tapi jangan lupa belajar ya</p>
            </div>
        `;

                        document.querySelector('.row.g-4.m-0').appendChild(messageDiv);
                    }
                }, 100);

                setTimeout(() => {
                    const savedView = localStorage.getItem('smagaEdu-ujian-view-preference') || 'card';
                    if (savedView === 'list') {
                        setViewMode('list');
                    }
                }, 150);
            }

            // Initial filter
            filterExams('today');
        </script>

        <!-- script untuk view card dan list -->
        <script>
            // Setup View Toggle Functionality untuk Ujian
            function setupViewToggle() {
                const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
                const ujianContainer = document.querySelector('.row.g-4.m-0');

                // Load saved view preference
                const savedView = localStorage.getItem('smagaEdu-ujian-view-preference') || 'card';
                setViewMode(savedView);

                viewToggleBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const viewMode = this.getAttribute('data-view');

                        // Update active state
                        viewToggleBtns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        // Set view mode
                        setViewMode(viewMode);

                        // Save preference
                        localStorage.setItem('smagaEdu-ujian-view-preference', viewMode);
                    });
                });
            }

            function setViewMode(mode) {
                const ujianContainer = document.querySelector('.row.g-4.m-0');
                const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');

                if (!ujianContainer) return;

                // Update button states
                viewToggleBtns.forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-view') === mode);
                });

                if (mode === 'list') {
                    // Switch to list view - hanya tambah class
                    ujianContainer.classList.add('ujian-list-view');

                    // Ubah class columns tanpa manipulasi DOM
                    const columns = ujianContainer.querySelectorAll('.col-12.col-md-6.col-lg-4');
                    columns.forEach(col => {
                        if (!col.querySelector('.no-exams-message')) {
                            col.className = 'col-12 list-item-col';
                        }
                    });
                } else {
                    // Switch to card view - hapus class
                    ujianContainer.classList.remove('ujian-list-view');

                    // Kembalikan class columns
                    const columns = ujianContainer.querySelectorAll('.list-item-col');
                    columns.forEach(col => {
                        if (!col.querySelector('.no-exams-message')) {
                            col.className = 'col-12 col-md-6 col-lg-4';
                        }
                    });
                }
            }
        </script>

        <!-- Hidden form to store custom filters data -->
        <form id="customFiltersForm" style="display: none;">
            <input type="hidden" id="customFiltersData" name="customFiltersData" value="">
        </form>


        <div class="row g-4 m-0">
            <?php if (mysqli_num_rows($result) > 0):
                while ($ujian = mysqli_fetch_assoc($result)):
                    $guru_id = $ujian['guru_id'];
                    $query_guru = "SELECT foto_profil, namaLengkap FROM guru WHERE username = '$guru_id'";
                    $result_guru = mysqli_query($koneksi, $query_guru);
                    $guru = mysqli_fetch_assoc($result_guru);
                    $bg_image = !empty($ujian['background_image']) ? $ujian['background_image'] : 'assets/bg.jpg';


                    // Tambahkan pengecekan sebelum mendefinisikan fungsi
                    if (!function_exists('formatDurasi')) {
                        function formatDurasi($menit)
                        {
                            if ($menit >= 60) {
                                $jam = floor($menit / 60);
                                $sisa_menit = $menit % 60;

                                if ($sisa_menit > 0) {
                                    return $jam . " jam " . $sisa_menit . " menit";
                                } else {
                                    return $jam . " jam";
                                }
                            } else {
                                return $menit . " menit";
                            }
                        }
                    }
            ?>
                    <div class="col-12 col-md-6 pt-0 mt-1 p-md-3 col-lg-4" style="padding: 1rem; padding-top: 0;">
                        <div class="class-card border" style="transition: all 0.3s ease;">
                            <div class="class-banner" style="background-image: url('<?php echo $bg_image; ?>')">
                                <style>
                                    /* Animasi terpisah untuk fade-in awal */
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

                                    [data-aos="fade-up"] {
                                        opacity: 0;
                                        animation: fadeInUp 0.6s ease forwards;
                                    }

                                    /* Penyesuaian untuk list view ujian */
                                    .ujian-list-view .class-meta .row {
                                        display: flex;
                                        flex-wrap: wrap;
                                        gap: 0.25rem;
                                    }

                                    .ujian-list-view .class-meta .col-12 {
                                        flex: 0 0 auto;
                                        width: auto;
                                        margin-right: 1rem;
                                    }

                                    .ujian-list-view .class-meta .d-flex {
                                        white-space: nowrap;
                                        margin-bottom: 0.25rem;
                                    }

                                    /* Hover effect untuk list view */
                                    .ujian-list-view .class-card:hover {
                                        transform: translateY(-2px);
                                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                                    }

                                    /* Status button styling di list view */
                                    .ujian-list-view .ujian-actions .btn {
                                        font-size: 0.9rem;
                                        padding: 0.5rem 1rem;
                                    }

                                    /* Badge positioning untuk list view */
                                    .ujian-list-view .class-title {
                                        display: flex;
                                        align-items: center;
                                        gap: 0.5rem;
                                    }
                                </style>
                                <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                    class="profile-circle" data-aos="fade-up">
                            </div>
                            <div class="class-content">
                                <h4 class="class-title mb-3"><?php echo htmlspecialchars($ujian['judul']); ?></h4>

                                <div class="class-meta" style="font-size: 12px;">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-book me-2 text-muted"></i>
                                                <span class="text-dark"><?php echo htmlspecialchars($ujian['mata_pelajaran']); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-mortarboard me-2 text-muted"></i>
                                                <span class="text-dark">Kelas <?php echo htmlspecialchars($ujian['tingkat']); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person me-2 text-muted"></i>
                                                <span class="text-secondary"><?php echo htmlspecialchars($guru['namaLengkap']); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-event me-2 text-muted"></i>
                                                <span class="text-secondary"><?php echo date('l, d F Y', strtotime($ujian['tanggal_mulai'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-clock me-2 text-muted"></i>
                                                <span class="text-secondary">Waktu Ujian <?php echo formatDurasi($ujian['durasi']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <?php
                                    $examStatus = getExamStatus($ujian['tanggal_mulai'], $ujian['tanggal_selesai']);
                                    if ($examStatus['status'] === 'ongoing'): ?>
                                        <?php if ($ujian['sudah_ujian'] > 0): ?>
                                            <button class="btn btn-secondary px-3 py-2 w-100"
                                                data-bs-toggle="modal"
                                                data-bs-target="#ujianSelesaiModal">
                                                <i class="bi bi-check-circle me-1"></i> Sudah Ujian
                                            </button>
                                        <?php else: ?>
                                            <a href="mulai_ujian.php?id=<?php echo $ujian['id']; ?>"
                                                class="btn btn-primary px-3 py-2 w-100"
                                                style="background: rgb(218, 119, 86); border: none;">
                                                <i class="bi bi-play-circle me-1"></i> Mulai Ujian
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-light px-3 py-2 w-100 border"
                                            <?php if ($examStatus['status'] === 'ended'): ?>
                                            data-bs-toggle="modal"
                                            data-bs-target="#ujianSelesaiModal"
                                            <?php elseif ($examStatus['status'] === 'waiting'): ?>
                                            data-bs-toggle="modal"
                                            data-bs-target="#waitingModal"
                                            <?php endif; ?>>
                                            <i class="bi bi-clock me-1"></i>
                                            <?php
                                            if ($examStatus['status'] === 'waiting'): ?>
                                                <?php echo $examStatus['countdown'] . " tersisa"; ?>
                                            <?php else: ?>
                                                Ujian Selesai
                                            <?php endif; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="col-12 py-5 text-center">
                    <div class="my-5">
                        <i class="ti ti-mood-wink-2" style="font-size: 5rem; color: rgb(218, 119, 86);"></i>
                        <h5 class="mt-3 fw-bold" style="font-size:28px">Horey, Ga Ada Ujian!</h5>
                        <p class="text-muted">Tidak ada ujian terdeteksi, masih ada buat main wkwk <br> tapi jangan lupa belajar ya</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <style>

        </style>

        <!-- Modal Ujian Selesai -->
        <div class="modal fade" id="ujianSelesaiModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-white" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">Sesi Ujian Telah Kadaluarsa</h5>
                        <p class="mb-4">Kamu tidak dapat mengakses ujian ini karena sudah menyelesaikan ujian atau sesi ujian telah berakhir.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn px-4 w-100 flex-fill" style="background-color: rgb(218, 119, 86); color:white; border-radius: 12px;" data-bs-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ujian belum dimulai -->
        <div class="modal fade" id="waitingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">Ujian belum di mulai</h5>
                        <p class="mb-4">Kamu terlalu bersemngat, cek lagi kalau sudah waktu ujian jadi manfaatkan waktumu untuk belajar. Ok?</p>
                        <div class="d-flex gap-2 btn-group">
                            <button type="button" class="btn px-4 w-100" style="background-color: rgb(218, 119, 86); color:white; border-radius: 12px;" data-bs-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>



</body>

</html>