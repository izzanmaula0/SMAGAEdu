<?php
session_start();
require "koneksi.php";
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <title>Beranda - SMAGAEdu</title>
</head>
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
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
        transition: background-color 0.3s ease;
    }

    .color-web:hover {
        background-color: rgb(206, 100, 65);
    }

    .btn-ask-saga {
        font-size: 16px; /* Diambil dari style inline sebelumnya */
        background: rgb(206, 100, 65); /* Warna latar awal */
        color: white; /* Warna teks awal */
        transition: color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease; /* Transisi halus */
    }

    .btn-ask-saga:hover,
    .btn-ask-saga:focus { /* :focus ditambahkan untuk aksesibilitas dan konsistensi */
        background-color: rgb(252, 138, 100);        
        color: white; /* Warna teks gelap agar terbaca di atas gradient putih */
        box-shadow: 0 0 15px 5px rgba(206, 100, 65, 0.5); /* Efek menyala (glow) */
    }
</style>
<?php include 'includes/styles.php'; ?>

<style>
    /* Filter styling */
    .filter-container {
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .filter-pills-wrapper {
        width: 100%;
    }

    .filter-pills {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 5px;
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    .filter-pills::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera */
    }

    .btn-filter {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.2s;
        white-space: nowrap;
        border-radius: 30px;
    }

    .btn-filter:hover {
        background-color: #e9ecef;
    }

    .btn-filter.active {
        background-color: #da7756;
        border-color: #da7756;
        color: white;
    }

    .btn-filter .remove-filter {
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .btn-filter .remove-filter:hover {
        opacity: 1;
    }
</style>

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
                width: 60px;
                height: 60px;
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
                <button class="btn mini-fab rounded-circle shadow"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_arsip_kelas"
                    title="Arsip">
                    <i class="bi bi-archive"></i>
                </button>
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

        <!-- Jumbotron -->
        <div class="jumbotron jumbotron-fluid mb-4 mt-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h2 class="display-5">
                            <?php
                            date_default_timezone_set('Asia/Jakarta');
                            $hour = date('H');
                            if ($hour >= 5 && $hour < 12) {
                                echo "Selamat Pagi";
                            } else if ($hour >= 12 && $hour < 15) {
                                echo "Selamat Siang";
                            } else if ($hour >= 15 && $hour < 19) {
                                echo "Selamat Sore";
                            } else {
                                echo "Selamat Malam";
                            }
                            ?>,
                            <span style="color: rgb(218, 119, 86);"><?php echo ucwords($guru['namaLengkap'] ?? 'admin'); ?></span>
                        </h2>
                        <!-- query ambil qount kelas  -->

                        <?php
                        $query_count_kelas = "SELECT COUNT(*) as total FROM kelas WHERE is_archived = 0";
                        $result_count_kelas = mysqli_query($koneksi, $query_count_kelas);
                        $count_kelas = mysqli_fetch_assoc($result_count_kelas)['total'];
                        ?>

                        <?php
                        $query_count_guru = "SELECT COUNT(*) as total FROM guru";
                        $result_count_guru = mysqli_query($koneksi, $query_count_guru);
                        $count_guru = mysqli_fetch_assoc($result_count_guru)['total'];
                        ?>


                        <?php
                        $query_count_siswa = "SELECT COUNT(*) as total FROM siswa";
                        $result_count_siswa = mysqli_query($koneksi, $query_count_siswa);
                        $count_siswa = mysqli_fetch_assoc($result_count_siswa)['total'];
                        ?>

                        <p class="lead">Selamat datang di beranda, saat ini kelas aktif berjumlah <?php echo $count_kelas; ?>, guru sebanyak <?php echo $count_guru; ?>, dan total siswa internal maupun eksternal sebanyak <?php echo $count_siswa; ?>.</p>
                    </div>
                </div>

            </div>
        </div>


        <!-- FILTER -->
        <div class="filter-container mb-4">
            <div class="d-flex align-items-center">
                <div class="filter-pills-wrapper d-flex justify-content-start">
                    <button class="btn btn-light border rounded-circle ms-2 flex-shrink-0 me-2" id="addFilterBtn" data-bs-toggle="modal" data-bs-target="#addFilterModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <div class="filter-pills d-flex gap-2 p-0 m-0">
                        <button class="btn btn-filter active" data-filter="all">Semua</button>
                        <button class="btn btn-filter" data-filter="7">Kelas 7</button>
                        <button class="btn btn-filter" data-filter="8">Kelas 8</button>
                        <button class="btn btn-filter" data-filter="9">Kelas 9</button>
                        <button class="btn btn-filter" data-filter="E">Fase E</button>
                        <button class="btn btn-filter" data-filter="F">Fase F</button>
                        <button class="btn btn-filter" data-filter="12">Kelas 12</button>
                        <!-- Filter dinamis akan ditambahkan di sini -->
                        <div id="custom-filters-container" class="d-flex gap-2">
                            <!-- Custom filters will be generated here -->
                        </div>
                    </div>
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
                                <label class="form-label small mb-0">Pilih Kelas untuk Filter Ini</label>
                                <div class="form-check mb-0 d-none d-md-block">
                                    <input class="form-check-input" type="checkbox" id="select-all-classes">
                                    <label class="form-check-label small" for="select-all-classes">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            <div class="rounded mt-3 rounded border p-3" style="max-height: 250px; overflow-y: auto;">
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
        <!-- Classes Grid -->
        <div class="row g-4">
            <?php
            $query_kelas = "SELECT k.*, g.namaLengkap as nama_guru, g.foto_profil as foto_guru, COUNT(ks.siswa_id) as jumlah_siswa 
                FROM kelas k 
                LEFT JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                LEFT JOIN guru g ON k.guru_id = g.username
                WHERE k.is_archived = 0 AND k.is_public = 0
                GROUP BY k.id";
            $result_kelas = mysqli_query($koneksi, $query_kelas);

            if (mysqli_num_rows($result_kelas) > 0):
                while ($kelas = mysqli_fetch_assoc($result_kelas)):
                    $bg_image = !empty($kelas['background_image']) ? $kelas['background_image'] : 'assets/bg.jpg';
            ?>
                    <div class="col-12 col-md-6 col-lg-4 class-item" data-tingkat="<?php echo $kelas['tingkat']; ?>" data-id="<?php echo $kelas['id']; ?>">
                        <div class="class-card">
                            <div class="class-banner" style="background-image: url('<?php echo $bg_image; ?>')">
                                <img src="<?php echo !empty($kelas['foto_guru']) ? 'uploads/profil/' . $kelas['foto_guru'] : 'assets/pp.png'; ?>"
                                    class="profile-circle">
                            </div>
                            <div class="class-content">
                                <h4 class="class-title"><?php echo htmlspecialchars($kelas['mata_pelajaran']); ?></h4>
                                <div class="class-meta mb-2">
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <?php echo htmlspecialchars($kelas['nama_guru'] ?? 'Admin'); ?>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-mortarboard me-2"></i>
                                        <?php echo htmlspecialchars($kelas['tingkat']); ?>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-people me-2"></i>
                                        <?php echo $kelas['jumlah_siswa']; ?> Siswa
                                    </div>
                                    <div class="d-flex align-items-center text-muted small mt-1">
                                        <i class="bi bi-book me-2"></i>
                                        <?php echo !empty($kelas['deskripsi']) ? $kelas['deskripsi'] : 'Tidak ada deskripsi'; ?>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <a href="kelas_guru.php?id=<?php echo $kelas['id']; ?>"
                                        class="btn-enter text-decoration-none d-flex align-items-center justify-content-center">
                                        Masuk
                                    </a>
                                    <!-- <div class="dropdown">
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
                                    </div> -->
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
                <div class="position-absolute top-50 start-50 translate-middle text-center">
                    <i class="bi bi-journal-x" style="font-size: 2rem; color: #6c757d;"></i>
                    <p class="mt-3 mb-0">Belum ada kelas</p>
                    <small class="text-muted">Klik tombol "Buat Kelas" untuk membuat kelas baru</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get all filter buttons
            const filterButtons = document.querySelectorAll('.btn-filter');

            // Add click event to each filter button
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Get filter value
                    const filterValue = this.getAttribute('data-filter');

                    // Filter classes
                    filterClasses(filterValue);
                });
            });

            // Filter classes function
            function filterClasses(filter) {
                const classItems = document.querySelectorAll('.class-item');

                classItems.forEach(item => {
                    const tingkat = item.getAttribute('data-tingkat');

                    if (filter === 'all') {
                        item.style.display = '';
                    } else if (filter === tingkat) {
                        item.style.display = '';
                    } else if (filter.startsWith('custom-')) {
                        // Custom filter logic
                        const customFilter = customFilters.find(cf => cf.id === filter);
                        if (customFilter && customFilter.classes.includes(item.getAttribute('data-id'))) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Load existing custom filters from localStorage if any
            let customFilters = JSON.parse(localStorage.getItem('customFilters')) || [];

            // Render custom filters
            renderCustomFilters();

            // Save Filter button click
            document.getElementById('saveFilterBtn').addEventListener('click', function() {
                const filterName = document.getElementById('newFilterName').value.trim();

                if (filterName === '') {
                    alert('Nama filter tidak boleh kosong');
                    return;
                }

                // Get selected classes
                const selectedClasses = [];
                document.querySelectorAll('.class-checkbox:checked').forEach(checkbox => {
                    selectedClasses.push(checkbox.value);
                });

                if (selectedClasses.length === 0) {
                    alert('Pilih minimal satu kelas untuk filter ini');
                    return;
                }

                // Create new filter
                const newFilter = {
                    id: 'custom-' + Date.now(),
                    name: filterName,
                    classes: selectedClasses
                };

                // Add to custom filters
                customFilters.push(newFilter);

                // Save to localStorage
                localStorage.setItem('customFilters', JSON.stringify(customFilters));

                // Render custom filters
                renderCustomFilters();

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addFilterModal'));
                modal.hide();

                // Reset form
                document.getElementById('newFilterName').value = '';
                document.querySelectorAll('.class-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            });

            // Function to render custom filters
            function renderCustomFilters() {
                const container = document.getElementById('custom-filters-container');
                container.innerHTML = '';

                customFilters.forEach(filter => {
                    const button = document.createElement('button');
                    button.className = 'btn btn-filter';
                    button.setAttribute('data-filter', filter.id);
                    button.innerHTML = `
                ${filter.name}
                <span class="ms-2 remove-filter" data-id="${filter.id}">
                    <i class="bi bi-x"></i>
                </span>
            `;
                    container.appendChild(button);

                    // Add click event for the filter button
                    button.addEventListener('click', function(e) {
                        if (e.target.classList.contains('bi-x') ||
                            e.target.classList.contains('remove-filter') ||
                            e.target.parentElement.classList.contains('remove-filter')) {
                            e.stopPropagation();
                            removeCustomFilter(filter.id);
                            return;
                        }

                        // Remove active class from all buttons
                        document.querySelectorAll('.btn-filter').forEach(btn => {
                            btn.classList.remove('active');
                        });

                        // Add active class to clicked button
                        this.classList.add('active');

                        // Filter classes
                        filterClasses(filter.id);
                    });
                });
            }

            // Function to remove custom filter
            function removeCustomFilter(filterId) {
                customFilters = customFilters.filter(filter => filter.id !== filterId);
                localStorage.setItem('customFilters', JSON.stringify(customFilters));
                renderCustomFilters();

                // If the removed filter was active, switch to 'all'
                const activeButton = document.querySelector('.btn-filter.active');
                if (activeButton && activeButton.getAttribute('data-filter') === filterId) {
                    const allButton = document.querySelector('.btn-filter[data-filter="all"]');
                    allButton.click();
                }
            }

            // Select All Classes checkbox
            document.getElementById('select-all-classes').addEventListener('change', function() {
                document.querySelectorAll('.class-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Load kelas list in modal
            document.getElementById('addFilterBtn').addEventListener('click', loadKelasForFilter);

            function loadKelasForFilter() {
                const kelasList = document.getElementById('kelas-list');
                kelasList.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                // Use AJAX to get all classes
                fetch('get_kelas_for_filter.php')
                    .then(response => response.json())
                    .then(data => {
                        kelasList.innerHTML = '';

                        if (data.length === 0) {
                            kelasList.innerHTML = '<div class="text-center py-3">Tidak ada kelas ditemukan</div>';
                            return;
                        }

                        data.forEach(kelas => {
                            const kelasItem = document.createElement('div');
                            kelasItem.className = 'form-check py-2 border-bottom';
                            kelasItem.innerHTML = `
                        <input class="form-check-input class-checkbox" type="checkbox" value="${kelas.id}" id="kelas-${kelas.id}">
                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="kelas-${kelas.id}">
                            <span>${kelas.mata_pelajaran}</span>
                            <small class="text-muted">Kelas ${kelas.tingkat}</small>
                        </label>
                    `;
                            kelasList.appendChild(kelasItem);
                        });
                    })
                    .catch(error => {
                        kelasList.innerHTML = '<div class="text-center py-3 text-danger">Error: Tidak dapat memuat daftar kelas</div>';
                        console.error('Error loading classes:', error);
                    });
            }
        });
    </script>

    <!-- hapus kelas -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:rgb(218, 119, 86);"></i>
                    <h5 class="mt-3 fw-bold">Hapus Kelas</h5>
                    <p class="mb-4">Apakah Anda yakin ingin menghapus kelas ini? Tindakan ini akan menghapus kelas guru dan siswa.</p>
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
                <div class="modal-header border-0 pb-0">
                    <h1 class="modal-title fs-5 fw-bold" id="label_tambah_kelas">Buat Kelas</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <form action="tambah_kelas.php" method="POST">
                        <div class="row g-4">
                            <!-- Form Kelas -->
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label small mb-2">Mata Pelajaran</label>
                                    <select class="form-select form-select-lg shadow-sm" name="mata_pelajaran" id="mata_pelajaran" required>
                                        <option value="">Pilih salah satu</option>
                                        <option value="Matematika">Matematika</option>
                                        <option value="Ilmu Pengetahuan Alam">Ilmu Pengetahuan Alam</option>
                                        <option value="Informatika">Informatika</option>
                                        <option value="Akidah AKhlak">Akidah Akhlak</option>
                                        <option value="Quran Hadist">Quran Hadist</option>
                                        <option value="Fikih">Fikih</option>
                                        <option value="Bahasa Arab">Bahasa Arab</option>
                                        <option value="Kemuhammadiyahan Tarikh">Kemuhammadiyahan Tarikh</option>
                                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                                        <option value="Ilmu Pengetahuan Sosial">Ilmu Pengetahuan Sosial</option>
                                        <option value="TIK">TIK</option>
                                        <option value="Bahasa Jawa">Bahasa Jawa</option>
                                        <option value="Seni Budaya">Seni Budaya</option>
                                        <option value="PPkn">PPkn</option>
                                        <option value="PJOK">PJOK</option>
                                        <option value="Project">Project</option>
                                        <option value="Bimbingan Konseling">Bimbingan Konseling</option>
                                        <option value="Mentoring">Mentoring</option>
                                        <option value="Praktik Ibadah">Praktik Ibadah</option>
                                        <option value="Geografi">Geografi</option>
                                        <option value="Matematika Tingkat Lanjut SMA">Matematika Tingkat Lanjut SMA</option>
                                        <option value="Kemuhammadiyahan">Kemuhammadiyahan</option>
                                        <option value="PKN">PKN</option>
                                        <option value="PKWU">PKWU</option>
                                        <option value="Sosiologi">Sosiologi</option>
                                        <option value="Biologi">Biologi</option>
                                        <option value="Pendidikan Jasmani">Pendidikan Jasmani</option>
                                        <option value="Kimia">Kimia</option>
                                        <option value="Ekonomi">Ekonomi</option>
                                        <option value="Ibadah">Ibadah</option>
                                        <option value="Sejarah">Sejarah</option>
                                        <option value="Seni">Seni</option>
                                        <option value="Akutansi">Akutansi</option>

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
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="form-label small p-0 m-0">Materi Pembelajaran</label>
                                    <p class="text-muted p-0 m-0  mb-2" style="font-size: 12px;">- Optional</p>
                                    <div id="materi-container">
                                        <div class="input-group mb-2">
                                            <input type="text"
                                                class="form-control"
                                                name="materi[]"
                                                placeholder="Masukkan judul materi">
                                            <button type="button"
                                                class="btn btn-outline-light remove-materi text-muted border"
                                                style="display:none;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button"
                                        class="btn w-100 text-muted border-dotted d-flex align-items-center justify-content-center py-2"
                                        id="add-materi"
                                        style="border: 2px dashed #dee2e6; background: transparent;">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Tambah materi
                                    </button>
                                </div>
                            </div>

                            <!-- Daftar Siswa -->
                            <div class="col-12 col-md-6">
                                <div class="bg-light rounded-3 p-3">
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
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                padding: 1rem;
            }
        }
    </style>

    <script>
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
    </script>

    <!-- Modal Arsip Kelas -->
    <div class="modal fade" id="modal_arsip_kelas" tabindex="-1" aria-labelledby="label_arsip_kelas" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h1 class="modal-title fs-5 fw-bold" id="label_arsip_kelas">Kelas yang Diarsipkan</h1>
                        <p class="text-muted small mb-0">Daftar kelas yang telah diarsipkan</p>
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
                                                        <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus kelas ini?')) window.location.href='hapus_kelas.php?id=<?php echo $kelas_arsip['id']; ?>'">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
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
        function loadSiswa(tingkat) {
            if (tingkat) {
                // Gunakan AJAX untuk mengambil daftar siswa
                fetch(`get_siswa.php?tingkat=${tingkat}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('daftar_siswa').innerHTML = data;
                    });
            } else {
                document.getElementById('daftar_siswa').innerHTML = '<p class="text-muted text-center mt-5" style="font-size: 14px;">Pilih tingkat kelas terlebih dahulu</p>';
            }
        }

        // Handle checkbox "Pilih Semua"
        document.getElementById('pilih_semua').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    </script>


</body>

</html>