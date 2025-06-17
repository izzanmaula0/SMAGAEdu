<?php
session_start();
require "koneksi.php";
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Ambil semua ujian yang dibuat oleh guru yang sedang login
$query_ujian = "SELECT u.*, k.mata_pelajaran, k.background_image, k.tingkat FROM ujian u 
                JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.guru_id = '$userid' 
                ORDER BY u.created_at DESC";

$result_ujian = mysqli_query($koneksi, $query_ujian);

// Kemudian query untuk kelas
$query_kelas = "SELECT k.*, COUNT(ks.siswa_id) as jumlah_siswa 
                FROM kelas k 
                LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                WHERE k.guru_id = '$userid' AND k.is_archived = 0
                GROUP BY k.id";

$result_kelas = mysqli_query($koneksi, $query_kelas);


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

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

    <title>Ujian - SMAGAEdu</title>
</head>
<style>
    .custom-card {
        width: 300px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 0;
    }

    .custom-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .custom-card .profile-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 3px solid white;
        margin-top: -40px;
    }

    .custom-card .card-body {
        text-align: left;
    }

    @media (max-width: 768px) {
        .custom-card {
            width: 100%;
            /* Full width di mobile */
            max-width: 350px;
            /* Maximum width tetap 300px */
        }
    }

    body {
        font-family: merriweather;
    }

    @media (max-width: 768px) {
        body {
            padding-top: 56px;
            /* Sesuaikan dengan tinggi navbar */
        }
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }

    .btn {
        transition: background-color 0.3s ease;
        border: 0;
        border-radius: 5px;
    }

    .btn:hover {
        background-color: rgb(219, 106, 68);

    }
</style>

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
</style>


<?php include 'includes/styles.php'; ?>

<body>

    <!-- Tambahkan kode ini setelah tag <body> dan sebelum kode yang lain -->

    <!-- Modal Notifikasi Duplikasi Error -->
    <div class="modal fade" id="duplicateErrorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #dc3545;"></i>
                    <h5 class="mt-3 fw-bold">Duplikasi Gagal</h5>
                    <p class="mb-4">Ujian tidak dapat diduplikasi karena sudah ada siswa yang mengikuti ujian ini.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary flex-fill px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Duplikasi Sukses -->
    <div class="modal fade" id="duplicateSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="mt-3 fw-bold">Duplikasi Berhasil</h5>
                    <p class="mb-4">Ujian berhasil diduplikasi! Anda dapat melihat dan mengedit ujian hasil duplikasi pada daftar ujian.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Script untuk menampilkan modal notifikasi -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (isset($_SESSION['pesan'])) {
                if ($_SESSION['pesan'] == "duplicate_error") {
                    echo "var duplicateErrorModal = new bootstrap.Modal(document.getElementById('duplicateErrorModal'));";
                    echo "duplicateErrorModal.show();";
                } else if ($_SESSION['pesan'] == "duplicate_success") {
                    echo "var duplicateSuccessModal = new bootstrap.Modal(document.getElementById('duplicateSuccessModal'));";
                    echo "duplicateSuccessModal.show();";
                }
                unset($_SESSION['pesan']);
            }
            ?>
        });
    </script>

    <!-- Modal Notifikasi Sembunyikan Sukses -->
    <div class="modal fade" id="hideSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="mt-3 fw-bold">Ujian Disembunyikan</h5>
                    <p class="mb-4">Ujian berhasil disembunyikan dari siswa!</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Tampilkan Sukses -->
    <div class="modal fade" id="showSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="mt-3 fw-bold">Ujian Ditampilkan</h5>
                    <p class="mb-4">Ujian berhasil ditampilkan kepada siswa!</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk menampilkan modal notifikasi -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (isset($_SESSION['pesan'])) {
                if ($_SESSION['pesan'] == "duplicate_error") {
                    echo "var duplicateErrorModal = new bootstrap.Modal(document.getElementById('duplicateErrorModal'));";
                    echo "duplicateErrorModal.show();";
                } else if ($_SESSION['pesan'] == "duplicate_success") {
                    echo "var duplicateSuccessModal = new bootstrap.Modal(document.getElementById('duplicateSuccessModal'));";
                    echo "duplicateSuccessModal.show();";
                } else if ($_SESSION['pesan'] == "hide_success") {
                    echo "var hideSuccessModal = new bootstrap.Modal(document.getElementById('hideSuccessModal'));";
                    echo "hideSuccessModal.show();";
                } else if ($_SESSION['pesan'] == "show_success") {
                    echo "var showSuccessModal = new bootstrap.Modal(document.getElementById('showSuccessModal'));";
                    echo "showSuccessModal.show();";
                }
                unset($_SESSION['pesan']);
            }
            ?>
        });
    </script>

    <!-- modal hapus ujian berhasil -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cek parameter URL untuk melihat apakah ujian baru saja dihapus
            const urlParams = new URLSearchParams(window.location.search);
            const pesan = urlParams.get('pesan');

            if (pesan === 'hapus_berhasil') {
                // Buat dan tampilkan modal sukses
                const modalHtml = `
        <div class="modal fade" id="hapusUjianBerhasilModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">Ujian Dihapus</h5>
                        <p class="mb-4">Ujian telah berhasil dihapus dari sistem.</p>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-primary flex-fill px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;

                // Tambahkan modal ke dokumen
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('hapusUjianBerhasilModal'));
                modal.show();

                // Hapus parameter dari URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (pesan === 'hapus_gagal') {
                // Buat dan tampilkan modal gagal
                const modalHtml = `
        <div class="modal fade" id="hapusUjianGagalModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #dc3545;"></i>
                        <h5 class="mt-3 fw-bold">Gagal Menghapus Ujian</h5>
                        <p class="mb-4">Terjadi kesalahan saat menghapus ujian. Silakan coba lagi nanti.</p>
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-primary flex-fill px-4" data-bs-dismiss="modal" style="border-radius: 12px; background-color: #da7756; border: none;">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;

                // Tambahkan modal ke dokumen
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('hapusUjianGagalModal'));
                modal.show();

                // Hapus parameter dari URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>


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


    <!-- ini isi kontennya -->
    <div class="col p-4 col-utama">
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

            @media (max-width: 768px) {
                .col-utama {
                    margin-left: 0;
                    margin-top: 10px;
                }
            }

            .class-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                border: 1px solid #eee;
            }

            .class-card:hover {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
                border: 3px solid white;
                position: absolute;
                bottom: -35px;
                left: 20px;
                background: white;
                object-fit: cover;
            }

            .class-content {
                padding: 2.5rem 1.5rem 1.5rem;
            }

            .class-title {
                font-size: 1.25rem;
                font-weight: bold;
                margin: 0;
            }

            .class-meta {
                color: #666;
                font-size: 0.9rem;
                margin-top: 0.5rem;
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                margin-top: 1.5rem;
            }

            .btn-enter {
                flex: 1;
                padding: 8px;
                border-radius: 8px;
                background: #da7756;
                text-align: center;
                color: white;
                border: none;
                transition: background 0.3s ease;
            }

            .btn-enter:hover {
                background: #c96845;
                color: white;
            }

            .btn-more {
                width: 38px;
                padding: 8px;
                border-radius: 8px;
                border: 1px solid #eee;
                background: white;
                color: #666;
            }

            .btn-more:hover {
                background: #f8f9fa;
            }

            /* Specific styling for btn-umum */
            .btn {
                transition: all 0.3s ease;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .btn:active {
                transform: scale(0.95);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
        </style>

        <div class="row justify-content-between align-items-center mb-0 mb-md-3">

            <div class="col-12 col-md-auto mb-3 mb-md-0">
                <h3 style="font-weight: bold;">Ujian</h3>
            </div>

            <!-- Desktop button -->
            <div class="d-none d-md-block col-md-auto">
                <a href="buat_ujian.php" class="btn btn-light border text-black d-flex align-items-center" style="border-radius: 15px;">
                    <i class="bi bi-plus-lg me-2"></i>
                    Buat
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-container mb-4">
            <div class="d-flex align-items-center">
                <div class="filter-pills-wrapper d-flex justify-content-start">
                    <button class="btn btn-light border ms-2 flex-shrink-0 me-2" id="addFilterBtn" data-bs-toggle="modal" data-bs-target="#addFilterModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <div class="filter-pills d-flex gap-2">
                        <button class="btn btn-filter active" data-filter="all">Semua</button>
                        <button class="btn btn-filter" data-filter="past">Sudah Lampau</button>
                        <button class="btn btn-filter" data-filter="today">Hari Ini</button>
                        <button class="btn btn-filter" data-filter="upcoming">Akan Datang</button>
                        <!-- Filter dinamis akan ditambahkan di sini -->
                        <div id="custom-filters-container" class="d-flex gap-2">
                            <!-- Custom filters will be generated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="alert border bg-light mt-3" style="border-radius: 15px;">
                <div class="d-flex">
                    <i class="ti ti-trash fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                    <div>
                        <p class="fw-bold p-0 m-0" style="font-size: 14px;">Bersihkan atau Sembunyikan</p>
                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                            Soal yang tidak dikelola dapat menumpuk dan membingungkan siswa. Gunakan <span class="border text-danger rounded px-1">Hapus Ujian</span> atau <span class="border rounded px-1">Sembunyikan dari siswa</span> untuk mengatur tampilan ujian pada halaman siswa.
                        </p>

                    </div>
                </div>
            </div> -->
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

            .hidden-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: rgba(108, 117, 125, 0.8);
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
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

            // Filter exams
            function filterExams(filter, examIds = []) {
                console.log('Filtering by:', filter);

                // Get current date for comparison
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

                // Function to parse Indonesian/English date format
                function parseExamDate(dateStr) {
                    if (!dateStr) return null;

                    // Contoh format date: "Monday, 25 March 2024" atau "Senin, 25 Maret 2024"
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

                    return new Date(year, month, day);
                }

                const examCards = document.querySelectorAll('.class-card');

                examCards.forEach((card) => {
                    const parentCol = card.closest('.col-12.col-md-6.col-lg-4');
                    if (!parentCol) return;

                    // Get exam ID
                    let examId = '';
                    const linkElement = card.querySelector('a[href^="buat_soal.php?ujian_id="]');

                    if (linkElement) {
                        const href = linkElement.getAttribute('href');
                        const match = href.match(/ujian_id=(\d+)/);
                        if (match && match[1]) {
                            examId = match[1];
                        }
                    }

                    // Get exam date
                    let examDate = null;
                    const dateElements = card.querySelectorAll('.d-flex.align-items-center');

                    dateElements.forEach(el => {
                        const icon = el.querySelector('i');
                        if (icon && icon.classList.contains('bi-calendar-event')) {
                            const textElement = el.querySelector('.text-secondary');
                            if (textElement) {
                                examDate = parseExamDate(textElement.textContent.trim());
                            }
                        }
                    });

                    // Apply filter
                    if (filter === 'all') {
                        parentCol.style.display = 'block';
                    } else if (filter === 'past' && examDate) {
                        parentCol.style.display = examDate < today ? 'block' : 'none';
                    } else if (filter === 'today' && examDate) {
                        const examDay = new Date(examDate.getFullYear(), examDate.getMonth(), examDate.getDate());
                        parentCol.style.display = examDay.getTime() === today.getTime() ? 'block' : 'none';
                    } else if (filter === 'upcoming' && examDate) {
                        parentCol.style.display = examDate > today ? 'block' : 'none';
                    } else if (filter === 'custom') {
                        const shouldShow = examIds.includes(examId);
                        parentCol.style.display = shouldShow ? 'block' : 'none';
                    } else {
                        // Default fallback
                        parentCol.style.display = 'block';
                    }
                });
            }
        </script>

        <!-- Hidden form to store custom filters data -->
        <form id="customFiltersForm" style="display: none;">
            <input type="hidden" id="customFiltersData" name="customFiltersData" value="">
        </form>

        <div class="row g-4">



            <?php
            if (mysqli_num_rows($result_ujian) > 0):
                while ($ujian = mysqli_fetch_assoc($result_ujian)):
                    $bg_image = !empty($ujian['background_image']) ? $ujian['background_image'] : 'assets/bg.jpg';
            ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="class-card">
                            <div class="class-banner" style="background-image: url('<?php echo $bg_image; ?>')">
                                <?php if ($ujian['is_hidden'] == 1): ?>
                                    <div class="hidden-badge">
                                        <i class="bi bi-eye-slash"></i> Tersembunyi
                                    </div>
                                <?php endif; ?>
                                <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                    class="profile-circle">
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

                                        <!-- Added class/grade info -->
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
                                                <span class="text-secondary">
                                                    <?php
                                                    echo date('H:i', strtotime($ujian['tanggal_mulai'])) . ' WIB - ' .
                                                        date('H:i', strtotime($ujian['tanggal_selesai'])) . ' WIB';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-hourglass-split me-2 text-muted"></i>
                                                <span class="text-secondary"><?php echo $ujian['durasi']; ?> menit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-buttons mt-3">
                                    <a href="buat_soal.php?ujian_id=<?php echo $ujian['id']; ?>"
                                        class="btn-enter text-decoration-none">
                                        <p class="p-0 m-0">Edit Ujian</p>
                                    </a>
                                    <style>
                                        .dropdown-menu {
                                            transition: opacity 0.2s ease-in-out;
                                            opacity: 0;
                                            display: block;
                                            pointer-events: none;
                                        }

                                        .dropdown-menu.show {
                                            opacity: 1;
                                            pointer-events: auto;
                                        }
                                    </style>
                                    <div class="dropdown">
                                        <button class="btn-more" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <?php if ($ujian['is_hidden'] == 0): ?>
                                                    <a class="dropdown-item" href="#" onclick="toggleVisibility(<?php echo $ujian['id']; ?>, 1); return false;">
                                                        <i class="ti ti-eye-off me-2"></i>Sembunyikan
                                                    </a>
                                                <?php else: ?>
                                                    <a class="dropdown-item" href="#" onclick="toggleVisibility(<?php echo $ujian['id']; ?>, 0); return false;">
                                                        <i class="ti ti-eye me-2"></i>Tampilkan
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <!-- <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="showMaintenanceModal(); return false;">
                                                    <i class="ti ti-shield-checkered me-2"></i>Monitor
                                                </a>
                                            </li> -->
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="konfirmasiDuplikat(<?php echo $ujian['id']; ?>); return false;">
                                                    <i class="ti ti-copy me-2"></i>Duplikat
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="detail_hasil_ujian.php?ujian_id=<?php echo $ujian['id']; ?>">
                                                    <i class="ti ti-report-analytics me-2"></i>Hasil
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                    onclick="hapusUjian(<?php echo $ujian['id']; ?>); return false;">
                                                    <i class="ti ti-file-x me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style>
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

                        .class-card {
                            animation: fadeInUp 0.5s ease-out;
                            animation-fill-mode: backwards;
                        }

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

                        .col-12:nth-child(7) .class-card {
                            animation-delay: 0.7s;
                        }

                        .col-12:nth-child(8) .class-card {
                            animation-delay: 0.8s;
                        }

                        .col-12:nth-child(9) .class-card {
                            animation-delay: 0.9s;
                        }

                        .col-12:nth-child(10) .class-card {
                            animation-delay: 1s;
                        }
                    </style>

                <?php
                endwhile;
            else:
                ?>
                <div class="text-center" style="margin-top:10rem;">
                    <i class="bi bi-journal-x" style="font-size: 2rem; color: #6c757d;"></i>
                    <p class="mt-3 mb-0">Belum ada ujian</p>
                    <small class="text-muted">Klik tombol "Buat Ujian" untuk membuat ujian baru</small>
                </div>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="buat_ujian.php" class="btn color-web text-white w-100 d-flex d-md-none align-items-center justify-content-center" style="border: 2px dashed #ccc; background-color: transparent; min-height: 100px;">
                <div class="text-center">
                    <i class="bi bi-plus-lg mb-2" style="font-size: 2rem; color: #666;"></i>
                    <div style="color: #666;">Tambah Ujian Baru</div>
                </div>
            </a>
        </div>
    </div>
    </div>
    </div>

    <script>
        function hapusUjian(id) {
            // Set up the modal html if it doesn't exist
            if (!document.getElementById('deleteConfirmModal')) {
                const modalHtml = `
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">Hapus Ujian?</h5>
                        <p class="mb-4">Apakah Anda yakin ingin menghapus ujian? seluruh soal serta hasil siswa yang terkait juga akan terhapus.</p>
                        <div class="d-flex gap-2 btn-group">
                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Hapus</button>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }

            // Get the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

            // Set up delete confirmation
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = () => {
                window.location.href = 'hapus_ujian.php?id=' + id;
            };

            // Show the modal
            deleteModal.show();
        }
    </script>

    <!-- duplikat ujian -->
    <script>
        function konfirmasiDuplikat(id) {
            // Set up the modal html if it doesn't exist
            if (!document.getElementById('duplicateConfirmModal')) {
                const modalHtml = `
        <div class="modal fade" id="duplicateConfirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">Duplikat Ujian?</h5>
                        <p class="mb-4">Apakah Anda yakin ingin menduplikat ujian ini? Semua soal dan identitas ujian Anda akan terduplikat.</p>
                                    <div class="alert text-start border bg-light" style="border-radius: 15px;">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                            <div class="text-start">
                                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Hanya Ujian dan Soal Yang Diduplikasi</p>
                                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Ujian Anda akan seperti baru, jawaban dan rekam jejak ujian siswa tidak ikut terduplikasi.</p>
                                            </div>
                                        </div>
                                    </div>
                        <div class="d-flex gap-2 btn-group">
                            <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                            <button type="button" id="confirmDuplicateBtn" class="btn px-4 color-web text-white" style="border-radius: 12px;">Duplikat</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }

            // Get the modal
            const duplicateModal = new bootstrap.Modal(document.getElementById('duplicateConfirmModal'));

            // Set up duplicate confirmation
            const confirmBtn = document.getElementById('confirmDuplicateBtn');
            confirmBtn.onclick = () => {
                window.location.href = 'duplikat_ujian.php?id=' + id;
            };

            // Show the modal
            duplicateModal.show();
        }
    </script>

    <script>
        function toggleVisibility(id, status) {
            // Setup the modal HTML if it doesn't exist
            const modalId = status == 1 ? 'hideConfirmModal' : 'showConfirmModal';
            const modalTitle = status == 1 ? 'Sembunyikan Ujian?' : 'Tampilkan Ujian?';
            const modalMessage = status == 1 ?
                'Apakah Anda yakin ingin menyembunyikan ujian ini dari siswa?' :
                'Apakah Anda yakin ingin menampilkan ujian ini kepada siswa?';
            const modalButtonText = status == 1 ? 'Sembunyikan' : 'Tampilkan';

            if (!document.getElementById(modalId)) {
                const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <h5 class="mt-3 fw-bold">${modalTitle}</h5>
                        <p class="mb-4">${modalMessage}</p>
                        <div class="d-flex gap-2 btn-group">
                            <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                            <button type="button" id="confirmToggleBtn" class="btn px-4 color-web text-white" style="border-radius: 12px;">${modalButtonText}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }

            // Get the modal
            const modal = new bootstrap.Modal(document.getElementById(modalId));

            // Set up confirmation
            const confirmBtn = document.getElementById('confirmToggleBtn');
            confirmBtn.onclick = () => {
                window.location.href = `toggle_visibility.php?id=${id}&status=${status}`;
            };

            // Show the modal
            modal.show();
        }
    </script>
</body>

</html>