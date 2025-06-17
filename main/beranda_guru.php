<?php
include 'includes/session_config.php';
require "koneksi.php";
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}
// Tambahkan debug info di sini
// echo "Debug seluruh session:<br>";
// var_dump($_SESSION);
// echo "<br><br>"; 

// Ambil userid dari session
$userid = $_SESSION['userid'];

// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

?>

<?php if (isset($_SESSION['show_siswa_modal']) && $_SESSION['show_siswa_modal']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tampilkanModalPilihSiswa(<?php echo $_SESSION['temp_kelas_id']; ?>, '<?php echo $_SESSION['temp_tingkat']; ?>');

            // Hapus session setelah modal ditampilkan
            <?php
            unset($_SESSION['show_siswa_modal']);
            unset($_SESSION['temp_kelas_id']);
            unset($_SESSION['temp_tingkat']);
            ?>
        });

        // Tambahkan event listener untuk modal
        document.getElementById('modal_pilih_siswa').addEventListener('hidden.bs.modal', function() {
            // Ketika modal ditutup (baik dengan tombol close atau backdrop)
            window.location.href = 'beranda_guru.php';
        });
    </script>
<?php endif; ?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="assets/tab.png">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    <!-- fungsi untuk darkmode -->
    <script>
        (function() {
            // Periksa localStorage untuk status dark mode
            if (localStorage.getItem('darkmode') === 'true') {
                // Jika dark mode aktif, segera tambahkan class
                document.documentElement.classList.add('darkmode-preload');
            }
        })();
    </script>
    <!-- pengecualian welcome modal -->
    <style id="modal-fix">
        /* Reset animasi modal untuk modal fitur baru */
        #updateFeatureModal.modal {
            padding-right: 0 !important;
        }

        #updateFeatureModal.modal.fade .modal-dialog {
            transition: transform 0.3s ease-out !important;
            transform: translate(0, -50px) scale(0.95) !important;
        }

        #updateFeatureModal.modal.show .modal-dialog {
            transform: translate(0, 0) scale(1) !important;
        }

        /* Force border-radius pada semua kondisi */
        #updateFeatureModal .modal-content,
        .darkmode--activated #updateFeatureModal .modal-content {
            border-radius: 16px !important;
            overflow: hidden !important;
            border: none !important;
            transition: all 0.3s ease !important;
        }

        /* Membuat backdrop transisi lebih halus */
        .modal-backdrop {
            transition: opacity 0.3s ease !important;
        }

        /* Agar tidak jerky di dark mode */
        body.darkmode--activated {
            transition: background-color 0.2s ease, color 0.2s ease !important;
        }

        /* Selalu pastikan konten modal visible */
        #updateFeatureModal.show .modal-content {
            opacity: 1 !important;
            visibility: visible !important;
        }
    </style>
    <?php include 'darkmode_preload.php'; ?>
    <title>Beranda - SMAGAEdu</title>
</head>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-5XXM5FLKYE"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-5XXM5FLKYE');
</script>



<style>
    .custom-card {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .menu-samping {
            display: none;
        }

        body {
            padding-top: 60px;
        }

        .custom-card {
            max-width: 100%;
        }
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

    body {
        font-family: merriweather, sans-serif;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
        transition: background-color 0.3s ease;
    }

    .color-web:hover {
        background-color: rgb(206, 100, 65);
    }

    /* Button click effect for all buttons */
    button {
        transition: transform 0.2s ease;
    }

    button:active {
        transform: scale(0.95);
    }

    /* Specific styling for btn-umum */
    .btn-umum {
        background-color: rgb(218, 119, 86);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-umum:hover {
        background-color: rgb(206, 100, 65);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-umum:active {
        transform: scale(0.95);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
<?php include 'includes/styles.php'; ?>

<body>



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

    <style>
        /* CSS untuk Perbaikan Modal Fitur Baru */
        #updateFeatureModal.modal.fade .modal-dialog {
            transform: translateY(-10px) scale(0.95);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
        }

        #updateFeatureModal.modal.show .modal-dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        #updateFeatureModal .modal-content {
            border-radius: 16px !important;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            border: none !important;
        }

        /* Override untuk dark mode */
        .darkmode--activated #updateFeatureModal .modal-content {
            border-radius: 16px !important;
            background-color: var(--card-bg-color) !important;
            border: 1px solid var(--border-color) !important;
        }

        /* Animasi tombol close */
        #updateFeatureModal .btn-close {
            transition: transform 0.2s ease;
        }

        #updateFeatureModal .btn-close:hover {
            transform: rotate(90deg);
        }

        /* Animasi button */
        #updateFeatureModal #closeButton {
            transition: all 0.3s ease;
        }

        #updateFeatureModal #closeButton:hover {
            background-color: rgb(206, 100, 65) !important;
            transform: translateY(-2px);
        }

        #updateFeatureModal #closeButton:active {
            transform: scale(0.98);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes modalFadeOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
        }

        #updateFeatureModal.show .modal-dialog {
            animation: modalFadeIn 0.3s forwards !important;
        }

        #updateFeatureModal.fade:not(.show) .modal-dialog {
            animation: modalFadeOut 0.3s forwards !important;
        }
    </style>


    <!-- Modal Fitur Baru dengan Slide -->
    <!-- <div class="modal fade" id="updateFeatureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px; z-index: 1050;" data-bs-dismiss="modal" aria-label="Close"></button>

                <div class="carousel-indicators position-relative mt-3" style="margin-bottom: 0;">
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                </div>

                <div id="featureCarousel" class="carousel slide" data-bs-interval="false">
                    <div class="carousel-inner">
                        <div class="carousel-item active p-4 pt-0 pb-0">
                            <div class="modal-body">
                                <div class="border rounded-4 position-relative" style="height: 250px; overflow: hidden;">
                                    <img src="feature/dark_mode.png" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <div class=" mt-4 mb-md-2">
                                    <span style="font-size: 10px; background-color:rgb(206, 100, 65)" class="text-white p-1 rounded border">Fitur Baru</span>
                                    <span style="font-size: 10px;" class="text-muted p-1 rounded border">v 1.2.2</span>
                                </div>
                                <h4 class="fw-bold mt-2" style="font-size: 1.8rem;">Tingkatkan Kenyamanan Anda dengan Mode Gelap</h4>
                                <p class="p-0 m-0 text-muted" style="font-size: 13px;">Saat ini Anda dapat mengaktifkan fitur mode gelap pada SMAGAEdu, dengan adanya mode gelap kami harapkan dapat meringankan stress dan tekanan cahaya pada mata Anda.</p>
                            </div>
                        </div>

                        <div class="carousel-item p-4 pt-0 pb-0">
                            <div class="modal-body p-4 pt-0 pb-0">
                                <div class="border rounded-4 position-relative" style="height: 250px; overflow: hidden;">
                                    <img src="feature/dark_mode2.gif" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <p class="mt-3 text-muted" style="font-size: 13px;">
                                    Anda dapat mengaktifkan mode gelap dengan mengklik tombol profil Anda di pojok kiri bawah halaman, kemudian klik Mode Gelap atau Mode Terang sesuai dengan prefensi Anda
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 mt-0 mt-md-0 px-3">
                    <div class="d-flex align-items-center mb-3 w-100 ms-4">
                        <input class="form-check-input me-2 pt-0 mt-0" type="checkbox" id="dontShowUpdateModal">
                        <label class="form-check-label text-muted" for="dontShowUpdateModal" style="font-size: 13px;">
                            Centang kalau Anda ga mau pesan ini muncul lagi
                        </label>
                    </div>
                    <div class="d-flex w-100 gap-2">
                        <button type="button" class="btn border text-black flex-grow-1" id="prevButton" data-bs-target="#featureCarousel" data-bs-slide="prev" style="border-radius:15px;">Sebelumnya</button>
                        <button type="button" class="btn flex-grow-1" id="nextButton" data-bs-target="#featureCarousel" data-bs-slide="next" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px;">Ajari Saya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
 -->

    <!-- Script untuk menangani modal -->
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kunci unik untuk preferensi modal
            const modalKey = 'update_1.2';

            // Check if this is the first login session
            const isFirstLogin = <?php echo (isset($_SESSION['first_login']) && $_SESSION['first_login']) ? 'true' : 'false'; ?>;

            // Only show modal on first login or if explicitly requested by session
            <?php if (isset($_SESSION['show_update_modal']) && $_SESSION['show_update_modal']): ?>
                // Tampilkan modal dan hapus session flag
                showUpdateModal();
                <?php unset($_SESSION['show_update_modal']); ?>
            <?php else: ?>
                // Only check modal preference on first login
                if (isFirstLogin) {
                    checkModalPreference();
                    // Remove first login flag to prevent showing on subsequent refreshes
                    <?php $_SESSION['first_login'] = false; ?>
                }
            <?php endif; ?>

            // Fungsi untuk memeriksa apakah modal harus ditampilkan berdasarkan preferensi di database
            function checkModalPreference() {
                fetch('check_modal_preference.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `key=${modalKey}&userid=<?php echo $userid; ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.hide) {
                            showUpdateModal();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking modal preference:', error);
                    });
            }

            // Fungsi untuk menampilkan modal dan mengatur semua event listeners
            function showUpdateModal() {
                const updateFeatureModal = new bootstrap.Modal(document.getElementById('updateFeatureModal'));
                updateFeatureModal.show();

                // Hide Previous button initially
                document.getElementById('prevButton').style.display = 'none';

                // Handle slide changes
                const carousel = document.getElementById('featureCarousel');
                const prevButton = document.getElementById('prevButton');
                const nextButton = document.getElementById('nextButton');

                carousel.addEventListener('slid.bs.carousel', function() {
                    // Get current active slide
                    const activeSlide = document.querySelector('.carousel-item.active');
                    const activeIndex = Array.from(document.querySelectorAll('.carousel-item')).indexOf(activeSlide);
                    const totalSlides = document.querySelectorAll('.carousel-item').length;

                    // Set Previous button visibility
                    prevButton.style.display = activeIndex === 0 ? 'none' : 'block';

                    // Change Next button to "Saya Mengerti" on last slide
                    if (activeIndex === totalSlides - 1) {
                        nextButton.textContent = 'Saya Mengerti';
                        nextButton.setAttribute('data-bs-dismiss', 'modal');
                        nextButton.removeAttribute('data-bs-target');
                        nextButton.removeAttribute('data-bs-slide');

                        // Add event listener for the last slide's button
                        nextButton.addEventListener('click', savePreference, {
                            once: true
                        });
                    } else {
                        nextButton.textContent = 'Ajari Saya';
                        nextButton.removeAttribute('data-bs-dismiss');
                        nextButton.setAttribute('data-bs-target', '#featureCarousel');
                        nextButton.setAttribute('data-bs-slide', 'next');
                    }
                });

                // Function to save preference
                function savePreference() {
                    const dontShowCheckbox = document.getElementById('dontShowUpdateModal');
                    if (dontShowCheckbox.checked) {
                        // Save preference to database with AJAX
                        fetch('save_modal_preference.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `key=${modalKey}&userid=<?php echo $userid; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Modal preference saved successfully');
                                }
                            })
                            .catch(error => {
                                console.error('Error saving modal preference:', error);
                            });
                    }
                }

                // Handle close button click
                const closeButton = document.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.addEventListener('click', savePreference);
                }

                // Enable swipe functionality for mobile
                let touchstartX = 0;
                let touchendX = 0;

                carousel.addEventListener('touchstart', function(e) {
                    touchstartX = e.changedTouches[0].screenX;
                }, false);

                carousel.addEventListener('touchend', function(e) {
                    touchendX = e.changedTouches[0].screenX;
                    handleSwipe();
                }, false);

                function handleSwipe() {
                    if (touchendX < touchstartX) {
                        // Swipe left, go to next slide
                        bootstrap.Carousel.getInstance(carousel).next();
                    }
                    if (touchendX > touchstartX) {
                        // Swipe right, go to previous slide
                        bootstrap.Carousel.getInstance(carousel).prev();
                    }
                }
            }
        });
    </script> -->


    <!-- ini isi kontennya -->
    <div class="col p-4 col-utama mt-1 mt-md-0">
        <style>
            .col-utama {
                margin-left: 0;
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

            @media (min-width: 768px) {
                .col-utama {
                    margin-left: 13rem;
                }
            }

            /* Modern card styling */
            .class-card {
                background: white;
                border-radius: 16px;
                overflow: hidden;
                transition: all 0.3s ease;
                border: 1px solid #eee;
            }

            .class-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            }

            .class-banner {
                height: 120px;
                background-size: cover;
                background-position: center;
                position: relative;
            }

            .class-content {
                padding: 1.5rem;
            }

            .profile-circle {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                border: 3px solid white;
                position: absolute;
                bottom: -24px;
                right: 20px;
                background: white;
                object-fit: cover;
            }

            .class-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 0.3rem;
            }

            .class-meta {
                font-size: 0.85rem;
                color: #7f8c8d;
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                margin-top: 1rem;
            }

            .btn-enter {
                flex: 1;
                padding: 0.6rem;
                border-radius: 8px;
                border: none;
                background: #da7756;
                color: white;
                font-weight: 500;
                transition: background 0.3s ease;
            }

            .btn-enter:hover {
                background: #c56647;
            }

            .btn-more {
                width: 42px;
                border-radius: 8px;
                border: 1px solid #eee;
                background: white;
                color: #666;
            }

            /* Facebook-style notification dropdown */
            .notification-dropdown {
                position: absolute;
                top: calc(100% + 5px);
                right: 0;
                width: 350px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                z-index: 1060;
                display: none;
                max-height: 80vh;
                overflow: hidden;
                flex-direction: column;
            }

            .notification-dropdown.show {
                display: flex;
            }

            .notification-body {
                max-height: 60vh;
                overflow-y: auto;
                padding: 0.5rem;
            }

            #notif-count {
                transform: translate(-50%, -50%);
            }

            .notification-scroll {
                overflow-y: auto;
                max-height: 100%;
            }

            /* Notification items styling */
            .notification-item {
                margin-bottom: 10px;
                border-radius: 14px;
                background-color: white;
                padding: 14px;
                cursor: pointer;
                transition: transform 0.2s ease;
                display: flex;
                align-items: flex-start;
            }

            .notification-item:hover {
                background-color: rgba(206, 100, 65, 0.08);

            }

            .notification-item:last-child {
                margin-bottom: 0;
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

            .button-beranda {
                border-radius: 15px;
            }
        </style>

        <style>
            .feature-content {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease-out, padding 0.3s ease;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            .feature-content.show {
                max-height: 1000px;
                /* Nilai yang cukup besar untuk menampung konten */
                padding: 1rem !important;
                transition: max-height 0.5s ease-in, padding 0.3s ease;
            }
        </style>

        <!--  -->
        <style>
            /* CSS untuk tooltip */
            .menu-item {
                position: relative;
            }

            .menu-item .tooltip-text {
                visibility: hidden;
                width: auto;
                background-color: #333;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 5px 10px;
                position: absolute;
                z-index: 1;
                left: 100%;
                top: 50%;
                transform: translateY(-50%);
                margin-left: 10px;
                opacity: 0;
                transition: opacity 0.3s;
                white-space: nowrap;
                font-size: 12px;
            }

            .menu-item:hover .tooltip-text {
                visibility: visible;
                opacity: 1;
            }

            /* Panah untuk tooltip */
            .menu-item .tooltip-text::after {
                content: "";
                position: absolute;
                top: 50%;
                right: 100%;
                margin-top: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: transparent #333 transparent transparent;
            }
        </style>

        <style>
            /* CSS untuk tooltip button */
            .position-relative {
                display: inline-block;
            }

            .tooltip-button {
                position: relative;
            }

            .tooltip-button .tooltip-text {
                visibility: hidden;
                width: auto;
                background-color: #333;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 5px 10px;
                position: absolute;
                z-index: 1050;
                /* Lebih tinggi dari modal bootstrap (1040) */
                top: 125%;
                /* Posisi di atas button */
                left: 50%;
                transform: translateX(-50%);
                /* Center tooltip */
                margin-bottom: 5px;
                opacity: 0;
                transition: opacity 0.3s;
                white-space: nowrap;
                font-size: 12px;
                pointer-events: none;
                /* Memastikan klik pada tooltip tidak menghalangi klik tombol */
            }

            .tooltip-button:hover .tooltip-text {
                visibility: visible;
                opacity: 1;
            }

            /* Panah untuk tooltip */
            .tooltip-button .tooltip-text::after {
                content: "";
                position: absolute;
                bottom: 100%;
                /* Di bagian bawah tooltip */
                left: 50%;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #333 transparent transparent transparent;
                /* Arah panah ke bawah */
            }
        </style>



        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0"> Beranda
            </h3>
            <div class="d-none d-md-flex gap-2">
                <!-- Notification Button -->
                <div class="position-relative">
                    <button class="btn button-beranda btn-sm btn-light border px-3 py-2" id="notificationBtn">
                        <i class="bi bi-bell me-1"></i>Notifikasi
                        <span id="notif-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; display: none;">0</span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div class="notification-dropdown shadow-sm border" id="notificationDropdown">
                        <div class="p-4 pb-0">
                            <h5 class="fw-bold">Notifikasi</h5>
                        </div>
                        <div class="notification-body" id="notification-list">
                            <!-- Notifications will be loaded here -->
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bell-slash fs-4 mb-2"></i>
                                <p class="mb-0 small">Memuat notifikasi...</p>
                            </div>
                        </div>
                        <!-- <div id="notification-footer" class="notification-footer d-flex p-2 text-end border-top">
                            <button id="markAllAsRead" class="btn flex-fill text-white shadow-none" style="background-color: rgb(206, 100, 65); border-radius:10px;">
                                <span class="fst-normal">Baca semua</span>
                            </button>
                        </div> -->
                    </div>
                </div>
                <div class="position-relative d-inline-block">
                    <button class="btn button-beranda btn-sm btn-light border px-3 py-2 tooltip-button" data-bs-toggle="modal" id="kelasBaruBtn" data-bs-target="#modal_tambah_kelas">
                        <i class="bi bi-plus-lg me-2"></i>Baru
                        <span class="tooltip-text">Tambah kelas baru</span>
                    </button>
                </div>
                <div class="position-relative d-inline-block">
                    <button class="btn button-beranda btn-sm btn-light border px-3 py-2 tooltip-button" data-bs-toggle="modal" id="arsipKelasBtn" data-bs-target="#modal_arsip_kelas">
                        <i class="bi bi-archive me-2"></i>Arsip
                        <!-- <span class="tooltip-text">Lihat kelas yang diarsipkan</span> -->
                    </button>
                </div>
            </div>
        </div>


        <!-- Filter Section -->
        <div class="filter-container mb-4">
            <div class="d-flex align-items-center">
                <div class="filter-pills-wrapper d-flex justify-content-start">
                    <button class="btn btn-light border ms-2 flex-shrink-0 me-2" id="addFilterBtn" data-bs-toggle="modal" data-bs-target="#addFilterModal" data-bs-custom-class="simple-tooltip" data-bs-title="Tambah Filter">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <div class="filter-pills d-flex gap-2">
                        <button class="btn btn-filter active" data-filter="all">Semua</button>
                        <button class="btn btn-filter" data-filter="private">Kelas Privat</button>
                        <button class="btn btn-filter" data-filter="public">Kelas Umum</button>
                        <!-- Filter dinamis akan ditambahkan di sini -->
                        <div id="custom-filters-container" class="d-flex gap-2">
                            <!-- Custom filters will be generated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>

        </script>

        <script>
            document.getElementById('OMContentLoaded', function() {
                new bootstrap.Tooltip(document.getElementById('addFilterBtn'))
            })
        </script>

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
                                <label class="form-label small mb-0">Pilih Kelas untuk Filter Ini</label>
                                <div class="form-check mb-0 d-none d-md-block">
                                    <input class="form-check-input" type="checkbox" id="select-all-classes">
                                    <label class="form-check-label small" for="select-all-classes">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            <div class="rounded mt-3" style="max-height: 250px; overflow-y: auto;">
                                <div id="kelas-list" class="d-flex flex-column gap-2">
                                    <!-- Daftar kelas akan dimuat di sini -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer btn-group border-0 gap-2">
                        <button type="button" class="btn border rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn color-web text-white rounded-3" id="saveFilterBtn">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSS untuk filter -->
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
                /* IE and Edge */
                scrollbar-width: none;
                /* Firefox */
                padding-bottom: 5px;
            }

            .filter-pills-wrapper::-webkit-scrollbar {
                display: none;
                /* Chrome, Safari and Opera */
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

            /* Update the kelas-item styling */
            .kelas-item {
                padding: 12px 15px;
                border-radius: 8px;
                background: white;
                border: 1px solid #dee2e6;
                transition: all 0.2s ease;
                margin-bottom: 8px;
            }

            .kelas-item:hover {
                background-color: #f8f9fa;
                border-color: #da7756;
            }

            .kelas-item .form-check {
                padding-left: 2rem;
            }

            .kelas-item .form-check-input {
                margin-top: 0.5rem;
            }

            .kelas-item .form-check-label {
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

        <!-- Hidden form to store custom filters data -->
        <form id="customFiltersForm" style="display: none;">
            <input type="hidden" id="customFiltersData" name="customFiltersData" value="">
        </form>

        <!-- Script untuk filter -->
        <script>
            // Global variables
            let classMapping = {};
            let customFilters = [];

            document.addEventListener('DOMContentLoaded', function() {
                // Load class mapping
                buildClassMapping();

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
                            filterClasses(filter);
                        });
                    }
                });
            }

            // Update the buildClassMapping function to capture more information
            function buildClassMapping() {
                classMapping = {};
                const classCards = document.querySelectorAll('.class-card');

                classCards.forEach((card) => {
                    const linkElement = card.querySelector('a[href^="kelas_guru.php?id="]');
                    if (!linkElement) return;

                    const href = linkElement.getAttribute('href');
                    const match = href.match(/id=(\d+)/);
                    if (!match || !match[1]) return;

                    const kelasId = match[1];
                    const titleElement = card.querySelector('.class-title');
                    const kelasName = titleElement ? titleElement.textContent.trim() : '';
                    const isPublic = card.querySelector('.badge.bg-success') !== null;

                    // Extract student count from the class card
                    const studentCountElement = card.querySelector('.class-meta .text-muted.small:last-child');
                    let studentCount = '0 Siswa';
                    if (studentCountElement) {
                        studentCount = studentCountElement.textContent.trim();
                    }

                    // Extract class level from description or other elements
                    let classLevel = '';
                    // Try to find class level info in any text element
                    const textElements = card.querySelectorAll('.text-muted.small');
                    textElements.forEach(el => {
                        const text = el.textContent.trim();
                        if (text.includes('Kelas') || text.includes('SMP') || text.includes('SMA') || text.includes('Fase')) {
                            classLevel = text;
                        }
                    });

                    classMapping[kelasId] = {
                        id: kelasId,
                        name: kelasName,
                        isPublic: isPublic,
                        element: card,
                        studentCount: studentCount,
                        classLevel: classLevel
                    };
                });

                console.log("Class mapping built:", classMapping);
            }

            // Setup modal events
            function setupModalEvents() {
                // Load class list when modal opens
                const addFilterModal = document.getElementById('addFilterModal');
                if (addFilterModal) {
                    addFilterModal.addEventListener('show.bs.modal', function() {
                        populateClassList();
                    });
                }

                // Setup select all checkbox
                const selectAllCheckbox = document.getElementById('select-all-classes');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.kelas-checkbox');
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

            // Update the populateClassList function to display more information
            function populateClassList() {
                const container = document.getElementById('kelas-list');
                if (!container) return;

                let html = '';

                // Check if we have any classes
                if (Object.keys(classMapping).length === 0) {
                    container.innerHTML = '<div class="text-center py-3 text-muted small">Tidak ada kelas tersedia</div>';
                    return;
                }

                // Build HTML for class checkboxes with additional information
                for (const [kelasId, kelasData] of Object.entries(classMapping)) {
                    html += `
            <div class="kelas-item">
                <div class="form-check">
                    <input class="form-check-input kelas-checkbox" type="checkbox" 
                           value="${kelasId}" id="kelas-check-${kelasId}" 
                           data-kelas-name="${kelasData.name}">
                    <label class="form-check-label" for="kelas-check-${kelasId}">
                        <div>
                            <span class="d-block fw-bold">${kelasData.name} ${kelasData.isPublic ? '<span class="badge bg-success" style="font-size: 10px;">Publik</span>' : ''}</span>
                            <span class="d-block text-muted small mt-1">
                                ${kelasData.classLevel ? `${kelasData.classLevel}` : ''}
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

                // Get selected class IDs
                const selectedClasses = [];
                const checkboxes = document.querySelectorAll('.kelas-checkbox:checked');

                checkboxes.forEach(checkbox => {
                    selectedClasses.push(checkbox.value);
                });

                if (selectedClasses.length === 0) {
                    alert('Pilih minimal satu kelas untuk filter ini');
                    return;
                }

                // Check if filter already exists
                const existingIndex = customFilters.findIndex(f => f.name === filterName);

                if (existingIndex >= 0) {
                    // Update existing filter
                    customFilters[existingIndex].classIds = selectedClasses;

                    // Update UI
                    const existingBtn = document.querySelector(`[data-custom-filter="${filterName}"]`);
                    if (existingBtn) existingBtn.remove();
                } else {
                    // Add new filter
                    customFilters.push({
                        name: filterName,
                        classIds: selectedClasses
                    });
                }

                // Save filters
                saveCustomFilters();

                // Add or update button
                addCustomFilterButton(filterName, selectedClasses);

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
                    // Store in hidden form field (more reliable than localStorage for some contexts)
                    const formField = document.getElementById('customFiltersData');
                    if (formField) {
                        formField.value = JSON.stringify(customFilters);
                    }

                    // Also try localStorage as backup
                    localStorage.setItem('smagaEduCustomFilters', JSON.stringify(customFilters));

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
                        const storedData = localStorage.getItem('smagaEduCustomFilters');
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
                            addCustomFilterButton(filter.name, filter.classIds);
                        });
                    }

                    console.log("Custom filters loaded:", customFilters);
                }
            }

            // Add custom filter button
            function addCustomFilterButton(filterName, classIds) {
                const container = document.getElementById('custom-filters-container');
                if (!container) return;

                // Create button
                const btn = document.createElement('button');
                btn.className = 'btn btn-filter d-flex align-items-center';
                btn.setAttribute('data-filter', 'custom');
                btn.setAttribute('data-custom-filter', filterName);
                btn.setAttribute('data-class-ids', classIds.join(','));

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
                    const classIdsAttr = this.getAttribute('data-class-ids');
                    const classIds = classIdsAttr ? classIdsAttr.split(',') : [];

                    filterClasses('custom', classIds);
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

            // Function to create the modal if it doesn't exist
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

                // Append to body
                document.body.appendChild(modal);
                return modal;
            }

            // Filter classes
            function filterClasses(filter, classIds = []) {
                console.log('Filtering by:', filter);
                console.log('Class IDs:', classIds);

                const classCards = document.querySelectorAll('.class-card');

                classCards.forEach((card) => {
                    const parentCol = card.closest('.col-12.col-md-6.col-lg-4');
                    if (!parentCol) return;

                    // Get class ID from the link
                    let kelasId = '';
                    const linkElement = card.querySelector('a[href^="kelas_guru.php?id="]');

                    if (linkElement) {
                        const href = linkElement.getAttribute('href');
                        const match = href.match(/id=(\d+)/);
                        if (match && match[1]) {
                            kelasId = match[1];
                        }
                    }

                    // Apply filter
                    if (filter === 'all') {
                        parentCol.style.display = 'block';
                    } else if (filter === 'public') {
                        const isPublic = card.querySelector('.badge.bg-success') !== null;
                        parentCol.style.display = isPublic ? 'block' : 'none';
                    } else if (filter === 'private') {
                        const isPublic = card.querySelector('.badge.bg-success') !== null;
                        parentCol.style.display = !isPublic ? 'block' : 'none';
                    } else if (filter === 'custom') {
                        const shouldShow = classIds.includes(kelasId);
                        console.log('Checking class ID:', kelasId, 'Should show:', shouldShow);
                        parentCol.style.display = shouldShow ? 'block' : 'none';
                    }
                });
            }
        </script>

        <!-- Jumbotron yang akan berubah berdasarkan tab yang aktif -->
        <div class="jumbotron jumbotron-fluid mb-md-2 d-none d-md-block">
            <div class="container">
                <div class="row">
                    <!-- Statistik Kelas (yang sudah ada) -->
                    <?php
                    // Query untuk menghitung jumlah kelas khusus/privat dan umum/publik
                    $query_count = "SELECT 
            SUM(CASE WHEN is_public = 0 THEN 1 ELSE 0 END) as private_count,
            SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) as public_count
            FROM kelas 
            WHERE guru_id = '$userid' AND is_archived = 0";

                    $result_count = mysqli_query($koneksi, $query_count);
                    $count_data = mysqli_fetch_assoc($result_count);
                    $private_count = $count_data['private_count'] ?? 0;
                    $public_count = $count_data['public_count'] ?? 0;
                    ?>

                    <style>
                        /* iOS-style notification styling */
                        .notification-card {
                            background-color: white;
                            overflow: hidden;
                            width: 100%;
                            max-width: 400px;
                            /* Increased from 350px */
                            float: right;
                        }

                        .notification-header {
                            background-color: rgba(248, 248, 248, 0.95);
                        }

                        .notification-body {
                            height: 300px;
                            /* Increased from 280px */
                            overflow: hidden;
                            border: 0;
                        }

                        .notification-scroll {
                            overflow-y: auto;
                            -ms-overflow-style: none;
                            /* IE and Edge */
                            scrollbar-width: none;
                            /* Firefox */
                            max-height: 300px;
                            /* Increased from 280px */
                        }

                        .notification-scroll::-webkit-scrollbar {
                            display: none;
                            /* Chrome, Safari and Opera */
                        }

                        /* The following styles won't apply since the scrollbar is hidden */
                        .notification-scroll::-webkit-scrollbar-track {
                            display: none;
                        }

                        .notification-scroll::-webkit-scrollbar-thumb {
                            display: none;
                        }

                        .notification-scroll::-webkit-scrollbar-thumb:hover {
                            display: none;
                        }

                        /* Styling untuk foto profil di notifikasi */
                        .notification-profile {
                            min-width: 40px;
                            /* Fixed width instead of variable */
                            height: 40px;
                            margin-right: 12px;
                            position: relative;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .profile-image {
                            width: 40px;
                            /* Increased from 2rem */
                            height: 40px;
                            /* Increased from 2rem */
                            border: 1px solid rgba(0, 0, 0, 0.1);
                            object-fit: cover;
                            border-radius: 50%;
                        }

                        /* Border khusus untuk jenis notifikasi */
                        .like-border {
                            border: 1px solid rgba(220, 53, 69, 0.7);
                        }

                        .comment-border {
                            border: 1px solid rgba(13, 110, 253, 0.7);
                        }

                        .like-border::after {
                            content: '👍';
                            font-size: 8px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background-color: rgba(220, 53, 69, 0.9);
                            color: white;
                        }

                        .comment-border::after {
                            content: '💬';
                            font-size: 8px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background-color: rgba(13, 110, 253, 0.9);
                            color: white;
                        }



                        .notification-content {
                            font-size: 0.9rem;
                            line-height: 1.4;
                            word-wrap: break-word;
                            overflow-wrap: break-word;
                            width: 100%;
                        }

                        .notification-time {
                            font-size: 0.75rem;
                            color: #8e8e93;
                            margin-top: 3px;
                        }

                        .notification-class {
                            display: inline-block;
                            background-color: rgba(0, 0, 0, 0.03);
                            padding: 2px 8px;
                            border-radius: 10px;
                            margin-top: 5px;
                            font-size: 0.75rem;
                            color: #8e8e93;
                        }

                        .notification-icon {
                            width: 32px;
                            height: 32px;
                            border-radius: 50%;
                            margin-right: 12px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .notification-icon.like {
                            background-color: rgba(255, 45, 85, 0.1);
                            color: #ff2d55;
                        }

                        .notification-icon.comment {
                            background-color: rgba(0, 122, 255, 0.1);
                            color: #007aff;
                        }

                        .badge {
                            font-weight: normal;
                            padding: 2px 6px;
                            font-size: 10px;
                        }

                        .notification-footer {
                            background-color: rgba(248, 248, 248, 0);
                        }

                        .notification-footer a {
                            color: #007aff;
                            font-weight: 500;
                        }

                        @media (max-width: 768px) {
                            .notification-card {
                                max-width: 100%;
                            }
                        }
                    </style>
                </div>
            </div>
        </div>


        <!-- Floating Action Button -->
        <div class="floating-action-button d-block d-md-none">
            <!-- Main FAB -->
            <button class="btn btn-lg main-fab rounded-circle shadow" id="mainFab">
                <i class="bi bi-plus-lg"></i>
            </button>

            <!-- Mini FABs -->
            <div class="mini-fabs">
                <!-- Buat Kelas Button -->
                <button class="btn mini-fab rounded-circle shadow"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_tambah_kelas"
                    title="Buat Kelas">
                    <i class="bi bi-plus-lg"></i>
                </button>

                <!-- Arsip Button -->
                <div data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Arsip Kelas" data-bs-placement="bottom">
                    <button class="btn mini-fab rounded-circle shadow"
                        data-bs-toggle="modal"
                        data-bs-target="#modal_arsip_kelas"
                        title="Arsip">
                        <i class="bi bi-archive"></i>
                    </button>
                </div>
            </div>
        </div>


        <style>
            /* Floating Action Button Styling */
            .floating-action-button {
                position: fixed;
                bottom: 80px;
                right: 20px;
                z-index: 1050;
            }

            .main-fab {
                width: 56px;
                height: 56px;
                background: #da7756;
                color: white;
                transition: transform 0.3s;
            }

            .main-fab:hover {
                background: #c56647;
                color: white;
            }

            .main-fab.active {
                transform: rotate(45deg);
            }

            .mini-fabs {
                position: absolute;
                bottom: 70px;
                right: 7px;
                display: flex;
                flex-direction: column;
                gap: 16px;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
            }

            .mini-fabs.show {
                opacity: 1;
                visibility: visible;
            }

            .mini-fab {
                width: 42px;
                height: 42px;
                background: white;
                color: #666;
                transform: scale(0);
                transition: transform 0.3s;
            }

            .mini-fabs.show .mini-fab {
                transform: scale(1);
            }

            .mini-fab:hover {
                background: #f8f9fa;
                color: #da7756;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mainFab = document.getElementById('mainFab');
                const miniFabs = document.querySelector('.mini-fabs');
                let isOpen = false;

                mainFab.addEventListener('click', function(e) {
                    e.stopPropagation();
                    isOpen = !isOpen;
                    mainFab.classList.toggle('active');
                    miniFabs.classList.toggle('show');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!mainFab.contains(e.target) && !miniFabs.contains(e.target) && isOpen) {
                        isOpen = false;
                        mainFab.classList.remove('active');
                        miniFabs.classList.remove('show');
                    }
                });

                // Prevent menu from closing when clicking menu items
                miniFabs.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        </script>
        <!-- Classes Grid -->
        <div class="row g-4">
            <?php
            $query_kelas = "SELECT k.*, COUNT(ks.siswa_id) as jumlah_siswa 
                                    FROM kelas k 
                                    LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                                    WHERE k.guru_id = '$userid' AND k.is_archived = 0
                                    GROUP BY k.id";
            $result_kelas = mysqli_query($koneksi, $query_kelas);

            if (mysqli_num_rows($result_kelas) > 0):
                while ($kelas = mysqli_fetch_assoc($result_kelas)):
                    $bg_image = !empty($kelas['background_image']) ? $kelas['background_image'] : 'assets/bg.jpg';
            ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="class-card">
                            <div class="class-banner" style="background-image: url('<?php echo $bg_image; ?>')">
                                <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                    class="profile-circle">
                            </div>
                            <div class="class-content">
                                <h4 class="class-title">
                                    <?php
                                    if ($kelas['is_public']) {
                                        echo htmlspecialchars($kelas['nama_kelas']);
                                    } else {
                                        echo htmlspecialchars($kelas['mata_pelajaran']);
                                    }
                                    ?>

                                    <?php if ($kelas['is_public']): ?>
                                        <span class="badge bg-success ms-2" style="font-size: 10px;"><i class="bi bi-globe me-1"></i>Publik</span>
                                    <?php endif; ?>

                                </h4>
                                <div class="class-meta mb-2">
                                    <div class="d-flex text-muted small mt-1">
                                        <i class="bi bi-book me-2"></i>
                                        <div class="description-container">
                                            <?php
                                            $deskripsi = !empty($kelas['deskripsi']) ? $kelas['deskripsi'] : 'Tidak ada deskripsi';
                                            $kelas_id = $kelas['id']; // Pastikan variabel ini ada

                                            // Hanya tampilkan 50 karakter pertama saat awal
                                            $short_text = substr($deskripsi, 0, 20);
                                            $show_toggle = (strlen($deskripsi) > 50);
                                            ?>

                                            <span id="short-desc-<?php echo $kelas_id; ?>" style="<?php echo $show_toggle ? '' : 'display:none;' ?>">
                                                <?php echo $short_text; ?>...
                                                <a href="#" onclick="showFullDesc(<?php echo $kelas_id; ?>); return false;" class="ms-1 text-decoration-none" style="color: #da7756;">selengkapnya</a>
                                            </span>

                                            <span id="full-desc-<?php echo $kelas_id; ?>" style="<?php echo $show_toggle ? 'display:none;' : '' ?>">
                                                <?php echo $deskripsi; ?>
                                                <?php if ($show_toggle): ?>
                                                    <a href="#" onclick="showShortDesc(<?php echo $kelas_id; ?>); return false;" class="ms-1 text-decoration-none" style="color: #da7756;">sembunyikan</a>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center text-muted small mt-1">
                                        <i class="bi bi-mortarboard me-2"></i>
                                        <?php
                                        $tingkat = $kelas['tingkat'];
                                        $formatted_tingkat = '';

                                        if (in_array($tingkat, ['7', '8', '9'])) {
                                            $formatted_tingkat = "SMP Kelas " . $tingkat;
                                        } elseif (in_array($tingkat, ['E', 'F'])) {
                                            $formatted_tingkat = "SMA Fase " . $tingkat;
                                        } else {
                                            $formatted_tingkat = "Kelas " . $tingkat;
                                        }
                                        echo $formatted_tingkat;
                                        ?>
                                    </div>
                                </div>
                                <script>
                                    function showFullDesc(id) {
                                        document.getElementById('short-desc-' + id).style.display = 'none';
                                        document.getElementById('full-desc-' + id).style.display = 'inline';
                                        return false;
                                    }

                                    function showShortDesc(id) {
                                        document.getElementById('full-desc-' + id).style.display = 'none';
                                        document.getElementById('short-desc-' + id).style.display = 'inline';
                                        return false;
                                    }
                                </script>

                                <div class="action-buttons">
                                    <a href="kelas_guru.php?id=<?php echo $kelas['id']; ?>"
                                        class="btn-enter text-decoration-none d-flex align-items-center justify-content-center">
                                        Masuk
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn-more d-flex align-items-center justify-content-center" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end animate slideIn">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="archive_kelas.php?id=<?php echo $kelas['id']; ?>">
                                                    <i class="bi bi-archive me-2"></i>Arsipkan
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center text-danger" href="#" onclick="showDeleteConfirmation(<?php echo $kelas['id']; ?>)">
                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <style>
                                    .animate {
                                        animation-duration: 0.3s;
                                        animation-fill-mode: both;
                                    }

                                    .slideIn {
                                        animation-name: slideIn;
                                    }

                                    @keyframes slideIn {
                                        0% {
                                            transform: translateY(1rem);
                                            opacity: 0;
                                        }

                                        100% {
                                            transform: translateY(0rem);
                                            opacity: 1;
                                        }
                                    }

                                    .dropdown-menu {
                                        margin-top: 0.5rem;
                                        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
                                        border: none;
                                        border-radius: 8px;
                                    }

                                    .dropdown-item {
                                        padding: 0.5rem 1rem;
                                        transition: all 0.2s;
                                    }

                                    .dropdown-item:hover {
                                        background: #f8f9fa;
                                        transform: translateX(5px);
                                    }
                                </style>
                                <style>
                                    .action-buttons {
                                        display: flex;
                                        gap: 0.5rem;
                                        margin-top: 1rem;
                                        height: 38px;
                                    }

                                    .btn-enter {
                                        flex: 1;
                                        border-radius: 8px;
                                        border: none;
                                        background: #da7756;
                                        color: white;
                                        font-weight: 500;
                                        transition: all 0.3s ease;
                                        height: 100%;
                                    }

                                    .btn-enter:hover {
                                        background: #c56647;
                                        transform: translateY(-2px);
                                    }

                                    .btn-more {
                                        width: 38px;
                                        border-radius: 8px;
                                        border: 1px solid #eee;
                                        background: white;
                                        color: #666;
                                        height: 100%;
                                        transition: all 0.3s ease;
                                    }

                                    .btn-more:hover {
                                        background: #f8f9fa;
                                        color: #da7756;
                                        transform: translateY(-2px);
                                    }

                                    .dropdown-item {
                                        padding: 8px 16px;
                                        transition: all 0.2s ease;
                                    }

                                    .dropdown-item:hover {
                                        background: #f8f9fa;
                                        color: #da7756;
                                    }

                                    /* Smooth animation for class cards */
                                    .class-card {
                                        opacity: 0;
                                        transform: translateY(20px);
                                        animation: fadeInUp 0.6s ease forwards;
                                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        will-change: transform;
                                    }

                                    .class-card:hover {
                                        transform: translateY(-8px);
                                        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
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

                                    /* Stagger animation for multiple cards */
                                    @media (min-width: 768px) {
                                        .class-card {
                                            animation-delay: calc(0.1s * var(--animation-order, 0));
                                        }
                                    }
                                </style>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // Add animation order to each card
                                        const cards = document.querySelectorAll('.class-card');
                                        cards.forEach((card, index) => {
                                            card.style.setProperty('--animation-order', index + 1);
                                        });
                                    });
                                </script>

                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="col-12">
                    <div class="text-center" style="margin-top:8rem;">
                        <i class="bi bi-journal-x" style="font-size: 2rem; color: #6c757d;"></i>
                        <p class="mt-3 mb-0">Belum Ada Kelas</p>
                        <small class="text-muted">Klik tombol "Baru" untuk membuat kelas baru</small>
                        <!-- <div class="mt-3">
                            <button class="btn btn-umum" data-bs-toggle="modal" data-bs-target="#modal_tambah_kelas">
                                <i class="bi bi-plus-lg me-2"></i>Buat Kelas Baru
                            </button>
                        </div> -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .empty-state {
            margin: 0 auto;
            max-width: 500px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
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

    <!-- hapus kelas -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                    <h5 class="mt-3 fw-bold">Hapus Kelas</h5>
                    <p class="mb-4">Apakah Anda yakin ingin menghapus kelas ini? Penghapusan Anda berdampak pada kelas siswa.</p>
                    <div class="d-flex gap-2 btn-group">
                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Hapus</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDeleteConfirmation(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            document.getElementById('confirmDeleteBtn').href = 'hapus_kelas.php?id=' + id;
            modal.show();
        }
    </script>


    <!-- modal untuk buat kelas -->
    <!-- Modal Buat Kelas dan Pilih Siswa -->
    <div class="modal fade" id="modal_tambah_kelas" tabindex="-1" aria-labelledby="label_tambah_kelas" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="tambah_kelas.php" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h1 class="modal-title fs-5 fw-bold" id="label_tambah_kelas">Buat Kelas Baru</h1>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="form-group mb-4">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="jenis_kelas" id="kelas_privat" value="0" checked>
                                <label class="btn btn-outline-secondary kelas-btn" for="kelas_privat">
                                    <i class="bi bi-lock me-1"></i>Privat
                                </label>

                                <input type="radio" class="btn-check" name="jenis_kelas" id="kelas_publik" value="1">
                                <label class="btn btn-outline-secondary kelas-btn" for="kelas_publik">
                                    <i class="bi bi-globe me-1"></i>Publik
                                </label>
                            </div>
                            <div class="form-text small">
                                <div class="alert alert-light border p-3 mt-2 d-flex align-items-start" style="border-radius: 15px; font-size: 13px;">
                                    <i class="bi bi-lock me-2 mt-1" id="jenis_kelas_icon" style="font-size: 16px;"></i>
                                    <div>
                                        <strong id="jenis_kelas_title">Akses kelas terbatas</strong><br>
                                        <span id="jenis_kelas_info">Pilih siswa secara manual untuk bergabung dengan kelas ini. Hak akses kelas sepenuhnya diberikan kepada Anda. Cocok untuk pembelajaran formal.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form fields for private class -->
                        <div id="private_class_form">
                            <div class="row g-4">
                                <!-- Form Kelas -->
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label small mb-2">Mata Pelajaran</label>
                                        <select class="form-select form-select-lg shadow-sm" name="mata_pelajaran" id="mata_pelajaran" required>
                                            <option value="">Pilih salah satu</option>
                                            <option value="Akidah AKhlak">Akidah Akhlak</option>
                                            <option value="Akutansi">Akutansi</option>
                                            <option value="Bahasa Arab">Bahasa Arab</option>
                                            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                                            <option value="Bahasa Jawa">Bahasa Jawa</option>
                                            <option value="Bimbingan Konseling">Bimbingan Konseling</option>
                                            <option value="Biologi">Biologi</option>
                                            <option value="Ekonomi">Ekonomi</option>
                                            <option value="Fikih">Fikih</option>
                                            <option value="Fisika">Fisika</option>
                                            <option value="Geografi">Geografi</option>
                                            <option value="Ibadah">Ibadah</option>
                                            <option value="Ilmu Pengetahuan Alam">Ilmu Pengetahuan Alam</option>
                                            <option value="Ilmu Pengetahuan Sosial">Ilmu Pengetahuan Sosial</option>
                                            <option value="Informatika">Informatika</option>
                                            <option value="Kemuhammadiyahan">Kemuhammadiyahan</option>
                                            <option value="Kemuhammadiyahan Tarikh">Kemuhammadiyahan Tarikh</option>
                                            <option value="Kimia">Kimia</option>
                                            <option value="Matematika">Matematika</option>
                                            <option value="Matematika Tingkat Lanjut SMA">Matematika Tingkat Lanjut SMA</option>
                                            <option value="Mentoring">Mentoring</option>
                                            <option value="Pendidikan Jasmani">Pendidikan Jasmani</option>
                                            <option value="PJOK">PJOK</option>
                                            <option value="PKN">PKN</option>
                                            <option value="PKWU">PKWU</option>
                                            <option value="PPkn">PPkn</option>
                                            <option value="Praktik Ibadah">Praktik Ibadah</option>
                                            <option value="Project">Project</option>
                                            <option value="Quran Hadist">Quran Hadist</option>
                                            <option value="Seni">Seni</option>
                                            <option value="Seni Budaya">Seni Budaya</option>
                                            <option value="Sejarah">Sejarah</option>
                                            <option value="Sosiologi">Sosiologi</option>
                                            <option value="TIK">TIK</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label small mb-2">Tingkat Kelas</label>
                                        <select class="form-select form-select-lg shadow-sm" name="tingkat" id="tingkat" onchange="loadSiswa(this.value)" required>
                                            <option value="">Pilih tingkat kelas</option>
                                            <option value="7">SMP Kelas 7</option>
                                            <option value="8">SMP Kelas 8</option>
                                            <option value="9">SMP Kelas 9</option>
                                            <option value="E">SMA Fase E</option>
                                            <option value="F">SMA Fase F</option>
                                            <option value="12">SMA Kelas 12</option>
                                            <option value="trial_smp">Trial Class SMP</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Daftar Siswa -->
                                <div class="col-12 col-md-6 bg-light border" style="border-radius: 15px;">
                                    <div class=" p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="form-label small mb-0">Daftar Siswa</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="pilih_semua">
                                                <label class="form-check-label small">Pilih Semua</label>
                                            </div>
                                        </div>

                                        <div id="daftar_siswa" class="overflow-auto" style="max-height: 300px;">
                                            <div class="text-center py-4 text-muted small">
                                                Pilih tingkat kelas terlebih dahulu
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer btn-group border-0 px-0 pt-4">
                                <button type="button" class="btn border px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="submit" class="btn color-web text-white px-4">Buat Kelas</button>
                            </div>
                        </div>

                        <!-- Form fields for public class -->
                        <div id="public_class_form" style="display: none;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label class="form-label small mb-2">Judul Kelas</label>
                                        <input type="text" class="form-control shadow-sm"
                                            name="judul_kelas"
                                            placeholder="Masukkan judul kelas umum"
                                            required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label small mb-2">Deskripsi Kelas</label>
                                        <textarea class="form-control shadow-sm"
                                            name="deskripsi"
                                            rows="3"
                                            placeholder="Jelaskan tentang kelas ini"
                                            required></textarea>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label small mb-2">Maksimal Siswa</label>
                                        <input type="number"
                                            class="form-control shadow-sm"
                                            name="max_siswa"
                                            placeholder="Jumlah maksimal siswa yang dapat bergabung"
                                            min="1"
                                            value="30"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer btn-group border-0 px-0 pt-4">
                                <button type="button" class="btn border px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="submit" class="btn color-web text-white px-4">Buat Kelas</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="jenis_kelas"]');
            const infoIcon = document.getElementById('jenis_kelas_icon');
            const infoTitle = document.getElementById('jenis_kelas_title');
            const infoText = document.getElementById('jenis_kelas_info');
            const privateForm = document.getElementById('private_class_form');
            const publicForm = document.getElementById('public_class_form');

            document.querySelectorAll('#public_class_form input, #public_class_form select, #public_class_form textarea').forEach(el => {
                el.disabled = true;
            });

            // Set initial active state for the privat button
            document.querySelector('label[for="kelas_privat"]').classList.add('active-tab');

            // Add event listeners for radio buttons
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all buttons
                    document.querySelectorAll('.kelas-btn').forEach(btn => {
                        btn.classList.remove('active-tab');
                    });

                    // Add active class to selected button
                    document.querySelector(`label[for="${this.id}"]`).classList.add('active-tab');

                    if (this.value === "1") {
                        // Public class
                        infoIcon.className = "bi bi-globe me-2 mt-1";
                        infoTitle.textContent = "Siapapun dapat bergabung";
                        infoText.textContent = "Seluruh siswa dapat bergabung dengan kelas ini tanpa persetujuan. Siswa mempunyai hak akses untuk membagikan postingan dalam grup. Cocok untuk kursus, ruang diskusi santai, dan lainya.";
                        privateForm.style.display = 'none';
                        publicForm.style.display = 'block';

                        // Disable semua elemen form privat
                        document.querySelectorAll('#private_class_form input, #private_class_form select, #private_class_form textarea').forEach(el => {
                            el.disabled = true;
                        });
                        // Enable semua elemen form publik
                        document.querySelectorAll('#public_class_form input, #public_class_form select, #public_class_form textarea').forEach(el => {
                            el.disabled = false;
                        });

                        // Remove required from private form fields
                        document.getElementById('mata_pelajaran').removeAttribute('required');
                        document.getElementById('tingkat').removeAttribute('required');

                        // Add required to public form fields
                        document.querySelector('input[name="judul_kelas"]').setAttribute('required', '');
                        document.querySelector('textarea[name="deskripsi"]').setAttribute('required', '');
                    } else {
                        // Private class
                        infoIcon.className = "bi bi-lock me-2 mt-1";
                        infoTitle.textContent = "Akses kelas terbatas";
                        infoText.textContent = "Pilih siswa secara manual untuk bergabung dengan kelas ini. Hak akses kelas sepenuhnya diberikan kepada Anda. Cocok untuk pembelajaran formal.";
                        privateForm.style.display = 'block';
                        publicForm.style.display = 'none';

                        // Enable semua elemen form privat
                        document.querySelectorAll('#private_class_form input, #private_class_form select, #private_class_form textarea').forEach(el => {
                            el.disabled = false;
                        });
                        // Disable semua elemen form publik
                        document.querySelectorAll('#public_class_form input, #public_class_form select, #public_class_form textarea').forEach(el => {
                            el.disabled = true;
                        });

                        // Add required to private form fields
                        document.getElementById('mata_pelajaran').setAttribute('required', '');
                        document.getElementById('tingkat').setAttribute('required', '');

                        // Remove required from public form fields
                        document.querySelector('input[name="judul_kelas"]').removeAttribute('required');
                        document.querySelector('textarea[name="deskripsi"]').removeAttribute('required');
                    }
                });
            });
        });
    </script>
    <style>
        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 12px;
        }

        .form-select {
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            font-size: 14px;
            border-radius: 8px;
        }

        .form-select:focus {
            border-color: #da7756;
            box-shadow: 0 0 0 0.25rem rgba(218, 119, 86, 0.25);
        }

        .form-check-input:checked {
            background-color: #da7756;
            border-color: #da7756;
        }

        /* Tab button styling */
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #dee2e6;
            transition: all 0.3s ease;
            border-radius: 15px;
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: rgb(218, 119, 86);
        }

        .active-tab {
            background-color: rgb(218, 119, 86) !important;
            color: white !important;
            border-color: rgb(218, 119, 86) !important;
            border-radius: 15px;
        }

        .btn-check:checked+.btn-outline-secondary {
            background-color: rgb(218, 119, 86);
            color: white;
            border-color: rgb(218, 119, 86);
        }

        /* Custom scrollbar */
        #daftar_siswa::-webkit-scrollbar {
            width: 6px;
        }

        #daftar_siswa::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #daftar_siswa::-webkit-scrollbar-thumb {
            background: #da7756;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                margin: 1rem;
            }

            .modal-body {
                padding: 0rem;
            }
        }
    </style>

    <!-- <script>
        // Script untuk mengubah teks info dan form yang ditampilkan sesuai jenis kelas
        document.querySelectorAll('input[name="jenis_kelas"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const infoText = document.getElementById('jenis_kelas_info');
                const infoIcon = document.getElementById('jenis_kelas_icon');
                const infoTitle = document.getElementById('jenis_kelas_title');
                const privateForm = document.getElementById('private_class_form');
                const publicForm = document.getElementById('public_class_form');

                if (this.value === "1") {
                    // Public class
                    infoIcon.className = "bi bi-globe me-2 mt-1";
                    infoTitle.textContent = "Siapapun dapat bergabung";
                    infoText.textContent = "Seluruh siswa dapat bergabung dengan kelas ini tanpa persetujuan. Cocok untuk kursus, ruang diskusi, dan lainnya.";

                    privateForm.style.display = 'none';
                    publicForm.style.display = 'block';
                } else {
                    // Private class
                    infoIcon.className = "bi bi-lock me-2 mt-1";
                    infoTitle.textContent = "Akses kelas terbatas";
                    infoText.textContent = "Pilih siswa secara manual untuk bergabung dengan kelas ini. Cocok untuk pembelajaran formal.";

                    privateForm.style.display = 'block';
                    publicForm.style.display = 'none';
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const materiContainer = document.getElementById('materi-container');
            const addMateriBtn = document.getElementById('add-materi');

            // Handle penambahan input materi
            addMateriBtn.addEventListener('click', function() {
                const newInput = document.createElement('div');
                newInput.className = 'input-group mb-2';
                newInput.innerHTML = `
            <input type="text" 
               class="form-control" 
               name="materi[]" 
               placeholder="Masukkan judul materi"
               style="border-radius: 8px 0 0 8px; border: 1px solid #dee2e6;"
               required>
            <button type="button" 
                class="btn btn-outline-light remove-materi text-muted"
                style="border: 1px solid #dee2e6; border-left: none; border-radius: 0 8px 8px 0;">
            <i class="bi bi-x"></i>
            </button>
        `;
                materiContainer.appendChild(newInput);

                // Tampilkan tombol hapus jika ada lebih dari 1 input
                document.querySelectorAll('.remove-materi').forEach(btn => {
                    btn.style.display = materiContainer.children.length > 1 ? 'block' : 'none';
                });
            });

            // Handle penghapusan input materi
            materiContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-materi') ||
                    e.target.parentElement.classList.contains('remove-materi')) {
                    const inputGroup = e.target.closest('.input-group');
                    inputGroup.remove();

                    // Update tampilan tombol hapus
                    document.querySelectorAll('.remove-materi').forEach(btn => {
                        btn.style.display = materiContainer.children.length > 1 ? 'block' : 'none';
                    });
                }
            });
        });
    </script> -->

    <!-- Modal Arsip Kelas -->
    <div class="modal fade" id="modal_arsip_kelas" tabindex="-1" aria-labelledby="label_arsip_kelas" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h1 class="modal-title fs-5 fw-bold" id="label_arsip_kelas">Kelas yang Diarsipkan</h1>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body px-4">
                    <?php
                    // Query untuk mengambil kelas yang diarsipkan
                    $query_arsip = "SELECT k.*, COUNT(ks.siswa_id) as jumlah_siswa 
                              FROM kelas k 
                              LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                              WHERE k.guru_id = '$userid' AND k.is_archived = 1
                              GROUP BY k.id";
                    $result_arsip = mysqli_query($koneksi, $query_arsip);

                    if (mysqli_num_rows($result_arsip) > 0) {
                    ?>
                        <div class="row g-4">
                            <?php while ($kelas_arsip = mysqli_fetch_assoc($result_arsip)) { ?>
                                <div class="col-12">
                                    <div class="card border-1 shadow-none">
                                        <div class="row g-0">
                                            <!-- Gambar Kelas -->
                                            <div class="col-md-4">
                                                <img src="<?php echo !empty($kelas_arsip['background_image']) ? htmlspecialchars($kelas_arsip['background_image']) : 'assets/bg.jpg'; ?>"
                                                    class="img-fluid rounded-start h-100"
                                                    style="object-fit: cover;"
                                                    alt="Background Image">
                                            </div>

                                            <!-- Informasi Kelas -->
                                            <div class="col-md-8">
                                                <div class="card-body shadow-none border-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h5 class="card-title fw-bold mb-1">
                                                                <?php echo htmlspecialchars($kelas_arsip['mata_pelajaran']); ?>
                                                            </h5>
                                                            <p class="card-text text-muted small">
                                                                Kelas <?php echo htmlspecialchars($kelas_arsip['tingkat']); ?>
                                                            </p>
                                                        </div>
                                                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                                            class="rounded-circle"
                                                            width="40"
                                                            height="40"
                                                            style="object-fit: cover;"
                                                            alt="Profile">
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    <div class="d-flex gap-2 mt-3">
                                                        <a href="unarchive_kelas.php?id=<?php echo $kelas_arsip['id']; ?>"
                                                            class="btn color-web text-white btn-sm flex-grow-1">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>
                                                            Keluarkan dari Arsip
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5">
                            <i class="bi bi-archive text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mb-0">Belum ada kelas yang diarsipkan</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>




    <style>
        /* Modal Archive Styling */
        #modal_arsip_kelas .modal-content {
            border-radius: 15px;
        }

        #modal_arsip_kelas .card {
            transition: transform 0.2s;
            border-radius: 12px;
        }

        #modal_arsip_kelas .card:hover {
            transform: translateY(-2px);
        }

        #modal_arsip_kelas .btn {
            border-radius: 8px;
            padding: 8px 16px;
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            #modal_arsip_kelas .modal-dialog {
                margin: 1rem;
            }

            #modal_arsip_kelas .col-md-4 img {
                height: 150px;
                width: 100%;
                border-radius: 12px 12px 0 0 !important;
            }

            #modal_arsip_kelas .card-body {
                padding: 1rem;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Notification button toggle functionality
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('show');

                    // If opening dropdown, refresh notifications
                    if (notificationDropdown.classList.contains('show')) {
                        loadHomeNotifications();
                    }
                });

                // Close when clicking outside
                document.addEventListener('click', function(e) {
                    if (notificationDropdown && !notificationDropdown.contains(e.target) &&
                        notificationBtn && !notificationBtn.contains(e.target)) {
                        notificationDropdown.classList.remove('show');
                    }
                });

                // Prevent dropdown from closing when clicking inside it
                notificationDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            const closeNotifBtn = document.getElementById('closeNotif');
            if (closeNotifBtn) {
                closeNotifBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (notificationDropdown) {
                        notificationDropdown.classList.remove('show');
                    }
                });
            }

            // Load notifications and set up refresh interval
            loadHomeNotifications();
            setInterval(loadHomeNotifications, 30000);

            // Mark all as read button functionality
            const markAllAsReadBtn = document.getElementById('markAllAsRead');
            if (markAllAsReadBtn) {
                markAllAsReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    fetch('mark_notification_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'mark_all=true'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadHomeNotifications();
                            }
                        });
                });
            }
        });

        // // Close when clicking outside
        // document.addEventListener('click', function(e) {
        //     if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
        //         notificationDropdown.classList.remove('show');
        //     }
        // });

        function loadSiswa(tingkat) {
            if (tingkat) {
                // Gunakan file khusus untuk buat kelas baru
                fetch(`get_siswa_buat_kelas.php?tingkat=${tingkat}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('daftar_siswa').innerHTML = data;

                        // Update event listener untuk checkbox "Pilih Semua"
                        updatePilihSemuaEvent();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('daftar_siswa').innerHTML =
                            '<div class="text-center py-4 text-danger">Gagal memuat daftar siswa</div>';
                    });
            } else {
                document.getElementById('daftar_siswa').innerHTML =
                    '<div class="text-center py-4 text-muted small">Pilih tingkat kelas terlebih dahulu</div>';
            }
        }

        // Fungsi untuk update event listener checkbox "Pilih Semua"
        function updatePilihSemuaEvent() {
            const pilihSemuaCheckbox = document.getElementById('pilih_semua');
            const siswaCheckboxes = document.querySelectorAll('.siswa-checkbox');

            // Reset checkbox "Pilih Semua"
            pilihSemuaCheckbox.checked = false;

            // Update event listener
            pilihSemuaCheckbox.onchange = function() {
                siswaCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            };
        }

        // Handle checkbox "Pilih Semua"
        document.getElementById('pilih_semua').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Load notifikasi saat halaman dimuat
            loadHomeNotifications();

            // Set interval untuk me-refresh notifikasi setiap 30 detik
            setInterval(loadHomeNotifications, 30000);



            // Add this fix at lines 2235-2249
            // Event handler untuk tombol "Tandai semua sudah dibaca"
            const markAllAsReadBtn = document.getElementById('markAllAsRead');
            if (markAllAsReadBtn) { // Check if the element exists
                markAllAsReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    fetch('mark_notification_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'mark_all=true'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadHomeNotifications();
                            }
                        });
                });
            }
        });

        // Function untuk memuat notifikasi
        function loadHomeNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    updateNotificationCard(data.notifications, data.total_unread);
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Update card notifikasi
        function updateNotificationCard(notifications, totalUnread) {
            const container = document.getElementById('notification-list');
            const notifCount = document.getElementById('notif-count');
            const notificationFooter = document.getElementById('notification-footer');

            // Pastikan elemen notifCount ada sebelum mencoba mengakses propertinya
            if (notifCount) {
                // Update badge jumlah notifikasi
                notifCount.textContent = totalUnread;
                if (totalUnread > 0) {
                    notifCount.style.display = 'inline-block';
                } else {
                    notifCount.style.display = 'none';
                }
            }

            // Show/hide the "Mark All as Read" button based on notifications
            if (notificationFooter) {
                notificationFooter.style.display = notifications.length > 0 ? 'block' : 'none';
            }

            // Pastikan container ada sebelum merender notifikasi
            if (!container) {
                console.error('Notification list container not found');
                return;
            }

            // Render notifikasi dalam container
            if (notifications.length === 0) {
                container.innerHTML = `
            <div class="notification-scroll">
                <div class="text-center py-5 text-muted d-flex flex-column align-items-center justify-content-center" style="height: 100%; border-radius:15px;">

                    <p class="mb-0 fw-bold small">Belum ada notifikasi</p>
                    <p class="mb-0" style="font-size:11px;">Seluruh notifikasi akan tampil di sini</p>
                </div>
            </div>
        `;
                return;
            }

            let html = `<div class="notification-scroll">`;

            notifications.forEach(notification => {
                // Determine text based on notification type
                let text;
                let iconClass = notification.jenis === 'like' ? 'like-border' : 'comment-border';

                if (notification.jenis === 'like') {
                    if (notification.jumlah > 1) {
                        text = `<strong>${notification.pelaku_nama}</strong> dan <strong>${notification.jumlah - 1} orang lainnya</strong> menyukai postingan Anda`;
                    } else {
                        text = `<strong>${notification.pelaku_nama}</strong> menyukai postingan Anda`;
                    }
                } else { // komentar
                    if (notification.jumlah > 1) {
                        text = `<strong>${notification.pelaku_nama}</strong> dan <strong>${notification.jumlah - 1} orang lainnya</strong> mengomentari postingan Anda`;
                    } else {
                        text = `<strong>${notification.pelaku_nama}</strong> mengomentari postingan Anda`;
                    }
                }

                html += `
            <div class="notification-item d-flex align-items-start unread" 
                data-id="${notification.id}" 
                onclick="viewNotification(${notification.id}, ${notification.kelas_id}, ${notification.postingan_id})">
                <div class="notification-profile">
                    <img src="${notification.foto_profil}" alt="Profil" class="profile-image">
                </div>
                <div class="flex-grow-1">
                    <div class="notification-content">
                        ${text}
                    </div>
                    <div class="notification-time">${notification.waktu_formatted}</div>
                    <span class="notification-class">
                        <i class="bi bi-book me-1"></i>${notification.nama_kelas}
                    </span>
                </div>
            </div>
        `;
            });

            html += `</div>`;
            container.innerHTML = html;
        }

        // Function untuk memuat notifikasi dengan penanganan error
        function loadHomeNotifications() {
            fetch('get_notifications.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Notifications data received:', data); // Debug
                    updateNotificationCard(data.notifications, data.total_unread);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    // Tampilkan error pada halaman
                    const container = document.getElementById('notification-list');
                    if (container) {
                        container.innerHTML = `
                    <div class="notification-scroll">
                        <div class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle fs-4 mb-2 d-block"></i>
                            <p class="mb-0 small">Error memuat notifikasi</p>
                        </div>
                    </div>
                `;
                    }
                });
        }

        // Handle notification click to view the relevant post
        function viewNotification(notificationId, kelasId, postinganId) {
            // Mark notification as read
            fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Navigate to the relevant post
                        window.location.href = `kelas_guru.php?id=${kelasId}&post=${postinganId}`;
                    }
                });
        }
    </script>


</body>

</html>