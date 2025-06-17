<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: beranda_guru.php");
    exit();
}

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);


$tugas_id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil detail tugas
$query_tugas = "SELECT t.*, p.kelas_id, p.user_id as guru_id, k.mata_pelajaran, k.tingkat
                FROM tugas t
                JOIN postingan_kelas p ON t.postingan_id = p.id
                JOIN kelas k ON p.kelas_id = k.id
                WHERE t.id = '$tugas_id'";
$result_tugas = mysqli_query($koneksi, $query_tugas);

if (mysqli_num_rows($result_tugas) == 0) {
    header("Location: beranda_guru.php");
    exit();
}

$data_tugas = mysqli_fetch_assoc($result_tugas);

// Cek apakah guru yang login adalah pemilik tugas
if ($data_tugas['guru_id'] != $_SESSION['userid']) {
    header("Location: beranda_guru.php");
    exit();
}

$kelas_id = $data_tugas['kelas_id'];

// Ambil data pengumpulan
$query_pengumpulan = "SELECT p.*, s.id as siswa_id, s.nama as nama_siswa, s.foto_profil, s.photo_type, s.photo_url
                     FROM pengumpulan_tugas p
                     JOIN siswa s ON p.siswa_id = s.username  
                     WHERE p.tugas_id = '$tugas_id'
                     ORDER BY p.waktu_pengumpulan DESC";
$result_pengumpulan = mysqli_query($koneksi, $query_pengumpulan);

// Ambil semua siswa di kelas 
$query_semua_siswa = "SELECT s.id, s.nama, s.foto_profil, s.photo_type, s.photo_url 
                     FROM siswa s
                     JOIN kelas_siswa ks ON s.id = ks.siswa_id
                     WHERE ks.kelas_id = '$kelas_id'
                     AND s.id NOT IN (
                         SELECT siswa_id 
                         FROM pengumpulan_tugas 
                         WHERE tugas_id = '$tugas_id'
                     )";
$result_semua_siswa = mysqli_query($koneksi, $query_semua_siswa);

// Buat array siswa yang sudah mengumpulkan
$siswa_mengumpulkan = [];
$pengumpulan_list = [];
while ($pengumpulan = mysqli_fetch_assoc($result_pengumpulan)) {
    $siswa_mengumpulkan[] = $pengumpulan['siswa_id'];
    $pengumpulan_list[] = $pengumpulan;
}

// Buat array siswa yang belum mengumpulkan
$siswa_belum_mengumpulkan = [];
while ($siswa = mysqli_fetch_assoc($result_semua_siswa)) {
    if (!in_array($siswa['id'], $siswa_mengumpulkan)) {
        $siswa_belum_mengumpulkan[] = $siswa;
    }
}

function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Tambahkan query untuk mengambil file-file dari tabel file_pengumpulan_tugas
function getSubmissionFiles($pengumpulan_id, $koneksi)
{
    $query = "SELECT * FROM file_pengumpulan_tugas WHERE pengumpulan_id = '$pengumpulan_id'";
    $result = mysqli_query($koneksi, $query);

    $files = [];
    while ($file = mysqli_fetch_assoc($result)) {
        $files[] = [
            'id' => $file['id'],
            'name' => $file['nama_file'],
            'path' => $file['file_path'],
            'type' => $file['tipe_file'],
            'size' => $file['ukuran_file'],
            'url' => $file['file_path'] // URL untuk akses file
        ];
    }

    return $files;
}

// Reset pointer result_pengumpulan
mysqli_data_seek($result_pengumpulan, 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - SMAGAEdu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.0/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <style>
        body {
            font-family: 'Merriweather', serif;
            /* background-color: #f5f5f5; */
        }

        .color-web {
            background-color: rgb(218, 119, 86);
        }

        .btnPrimary {
            background-color: rgb(218, 119, 86);
            border: 0;
            color: white;
        }

        .btnPrimary:hover {
            background-color: rgb(219, 106, 68);
            color: white;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .nav-pills .nav-link.active {
            background-color.nav-pills .nav-link.active {
                background-color: rgb(218, 119, 86);
            }
        }

        .submission-card {
            transition: all 0.2s;
        }

        .submission-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            object-fit: cover;
        }

        .deadline-indicator {
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.35rem 0.6rem;
            font-size: 0.75rem;
        }

        .col-utama {
            padding-left: 0rem;
            padding: 1rem;
            /* Ensure content doesn't overflow */
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
                padding-left: 9rem !important;
            }
        }



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

        @media (min-width: 992px) {
            .fixed-card {
                position: sticky;
                top: 20px;
                width: 100%;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
                z-index: 100;
            }
        }
    </style>
</head>

<body>

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

        <!-- Mobile view blocker (iOS style) -->
        <div class="mobile-blocker d-md-none position-fixed top-0 start-0 w-100 h-100 bg-white" style="z-index: 9999;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100 px-4">
                <div class="text-center mb-4">
                    <i class="bi bi-laptop display-1 text-secondary"></i>
                </div>
                <h4 class="mb-3 fw-bold text-dark">Akses Ditolak</h4>
                <p class="text-center text-secondary mb-4" style="font-size: 12px;">
                    Halaman detail tugas hanya dapat diakses pada perangkat laptop atau tablet.
                    Silakan gunakan perangkat dengan layar yang lebih besar.
                </p>
                <a href="kelas_guru.php?id=<?php echo $kelas_id; ?>" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" style="background-color: rgb(218, 119, 86); border: none;">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Kelas
                </a>
            </div>
        </div>

        <!-- Main content (only visible on desktop) -->
        <div class="container col-utama mt-4 mb-5 d-none d-md-block">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h2 class="p-0 m-0"><?php echo htmlspecialchars($data_tugas['judul']); ?></h2>
                        <a href="kelas_guru.php?id=<?php echo $kelas_id; ?>" class="btn bg-white btn-outline-secondary" style="border-radius: 15px;">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Detail Tugas -->
                <div class="col-lg-4 mb-4">
                    <div class="card border shadow-none rounded-4 fixed-card">
                        <div class="card-body shadow-none p-4">
                            <!-- Title with iOS-style heading -->
                            <div class="d-flex align-items-center mb-0 mb-md-4">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <h5 class="fw-semibold mb-0">Informasi Tugas</h5>
                                <button class="btn btn-link ms-auto d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#detailTugasCollapse" aria-expanded="false" aria-controls="detailTugasCollapse">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>

                            <div class="collapse d-lg-block mt-3" id="detailTugasCollapse">
                                <?php
                                // Di bagian pengecekan status
                                $now = new DateTime();
                                $deadline = new DateTime($data_tugas['batas_waktu']);

                                // Cek status dari database
                                $query_status = "SELECT status FROM tugas WHERE id = '$tugas_id'";
                                $result_status = mysqli_query($koneksi, $query_status);
                                $tugas_status = mysqli_fetch_assoc($result_status);

                                // Status bisa 'Ditutup' karena 3 kondisi:
                                // 1. Lewat deadline
                                // 2. Di-set manual jadi 'closed' 
                                // 3. Di-kunci guru
                                $is_closed = $now > $deadline || $tugas_status['status'] === 'closed';
                                $status = $is_closed ? 'Ditutup' : 'Aktif';
                                $status_color = $is_closed ? 'danger' : 'success';
                                $status_icon = $is_closed ? 'bi-lock-fill' : 'bi-unlock-fill';
                                ?>

                                <!-- Status display -->
                                <div class="d-flex align-items-center mb-4">
                                    <div class="d-flex align-items-center me-auto">
                                        <i class="bi bi-circle-fill text-<?php echo $status_color; ?> me-2" style="font-size: 0.5rem;"></i>
                                        Status
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge rounded-pill bg-<?php echo $status_color; ?> bg-opacity-10 text-<?php echo $status_color; ?> px-3 py-2">
                                            <i class="bi <?php echo $status_icon; ?> me-1"></i>
                                            <?php echo $status; ?>
                                        </span>
                                        <?php if (!$is_closed): ?>
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm rounded-pill"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalTutupTugas">
                                                <i class="bi bi-lock-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- iOS-style list items -->
                                <div class="list-group list-group-flush">
                                    <!-- Created Date -->
                                    <div class="list-group-item px-0 py-3 border-top">
                                        <div class="d-flex align-items-center text-secondary small mb-1">
                                            <i class="bi bi-calendar2-plus me-2"></i>
                                            Tanggal Dibuat
                                        </div>
                                        <div><?php echo date('d F Y, H:i', strtotime($data_tugas['created_at'])); ?></div>
                                    </div>

                                    <!-- Deadline -->
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex align-items-center text-secondary small mb-1">
                                            <i class="bi bi-calendar2-check me-2"></i>
                                            Batas Pengumpulan
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2"><?php echo date('d F Y, H:i', strtotime($data_tugas['batas_waktu'])); ?></div>
                                            <?php if ($now <= $deadline): ?>
                                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success small">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?php
                                                    $interval = $now->diff($deadline);
                                                    if ($interval->days > 0) {
                                                        echo $interval->format('%a hari %h jam lagi');
                                                    } else {
                                                        echo $interval->format('%h jam %i menit lagi');
                                                    }
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger small">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    Sudah lewat
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Maximum Points -->
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex align-items-center text-secondary small mb-1">
                                            <i class="bi bi-trophy me-2"></i>
                                            Poin Maksimal
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <?php echo $data_tugas['poin_maksimal']; ?> poin
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex align-items-center text-secondary small mb-1">
                                            <i class="bi bi-file-text me-2"></i>
                                            Deskripsi
                                        </div>
                                        <div class="text-wrap"><?php echo nl2br(htmlspecialchars($data_tugas['deskripsi'])); ?></div>
                                    </div>

                                    <!-- Attachments -->
                                    <?php
                                    $query_lampiran = "SELECT * FROM lampiran_tugas WHERE tugas_id = '$tugas_id'";
                                    $result_lampiran = mysqli_query($koneksi, $query_lampiran);

                                    if (mysqli_num_rows($result_lampiran) > 0):
                                    ?>
                                        <div class="list-group-item px-0 py-3">
                                            <div class="d-flex align-items-center text-secondary small mb-2">
                                                <i class="bi bi-paperclip me-2"></i>
                                                Lampiran
                                            </div>
                                            <div class="rounded-3 bg-light p-2">
                                                <?php while ($lampiran = mysqli_fetch_assoc($result_lampiran)):
                                                    $ext = strtolower(pathinfo($lampiran['nama_file'], PATHINFO_EXTENSION));
                                                    switch ($ext) {
                                                        case 'pdf':
                                                            $icon = 'bi-file-pdf-fill text-danger';
                                                            break;
                                                        case 'doc':
                                                        case 'docx':
                                                            $icon = 'bi-file-word-fill text-primary';
                                                            break;
                                                        case 'xls':
                                                        case 'xlsx':
                                                            $icon = 'bi-file-excel-fill text-success';
                                                            break;
                                                        case 'ppt':
                                                        case 'pptx':
                                                            $icon = 'bi-file-ppt-fill text-warning';
                                                            break;
                                                        case 'jpg':
                                                        case 'jpeg':
                                                        case 'png':
                                                        case 'gif':
                                                            $icon = 'bi-file-image-fill text-info';
                                                            break;
                                                        default:
                                                            $icon = 'bi-file-earmark-fill text-secondary';
                                                            break;
                                                    }
                                                ?>
                                                    <a href="<?php echo $lampiran['path_file']; ?>"
                                                        class="d-flex align-items-center text-decoration-none p-2 mb-1 rounded hover-bg-white transition"
                                                        target="_blank" download>
                                                        <i class="bi <?php echo $icon; ?> fs-4 me-2"></i>
                                                        <div class="flex-grow-1 min-width-0">
                                                            <div class="text-truncate text-dark"><?php echo htmlspecialchars($lampiran['nama_file']); ?></div>
                                                            <small class="text-secondary">
                                                                <i class="bi bi-hdd me-1"></i>
                                                                <?php echo formatFileSize($lampiran['ukuran_file']); ?>
                                                            </small>
                                                        </div>
                                                        <i class="bi bi-download text-secondary ms-2"></i>
                                                    </a>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .transition {
                            transition: all 0.2s ease-in-out;
                        }

                        .hover-bg-white:hover {
                            background-color: white !important;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                        }
                    </style>
                </div>

                <!-- Tab Pengumpulan -->
                <div class="col-lg-8">
                    <div class="card border shadow-none rounded-4">
                        <div class="card-header bg-white p-2" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <div class="tab-scroll-container">
                                <button class="btn btn-sm scroll-button scroll-left" onclick="scrollTabs('left')">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button class="btn btn-sm scroll-button scroll-right" onclick="scrollTabs('right')">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                                <ul class="nav nav-pills flex-nowrap overflow-auto hide-scrollbar" id="submissionTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active rounded-pill mx-1" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-submissions" type="button" role="tab">
                                            Semua
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary ms-1">
                                                <?php echo count($siswa_mengumpulkan) + count($siswa_belum_mengumpulkan); ?>
                                            </span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill mx-1" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button" role="tab">
                                            Dikumpulkan
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success ms-1">
                                                <?php echo count($siswa_mengumpulkan); ?>
                                            </span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill mx-1" id="missing-tab" data-bs-toggle="tab" data-bs-target="#missing" type="button" role="tab">
                                            Belum Mengumpulkan
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger ms-1">
                                                <?php echo count($siswa_belum_mengumpulkan); ?>
                                            </span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill mx-1" id="graded-tab" data-bs-toggle="tab" data-bs-target="#graded" type="button" role="tab">
                                            Sudah Dinilai
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info ms-1">
                                                <?php
                                                $dinilai = array_filter($pengumpulan_list, function ($item) {
                                                    return $item['nilai'] !== null;
                                                });
                                                echo count($dinilai);
                                                ?>
                                            </span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <style>
                                .tab-scroll-container {
                                    position: relative;
                                    padding: 0 30px;
                                }

                                .scroll-button {
                                    position: absolute;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    z-index: 1;
                                    background: white;
                                    border: 1px solid #dee2e6;
                                    border-radius: 50%;
                                    width: 24px;
                                    height: 24px;
                                    padding: 0;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                    color: #6c757d;
                                    opacity: 0.8;
                                    transition: opacity 0.3s ease;
                                }

                                .scroll-button:hover {
                                    background-color: #f8f9fa;
                                    opacity: 1;
                                }

                                .scroll-button.disabled {
                                    opacity: 0.5;
                                    cursor: not-allowed;
                                }

                                .scroll-left {
                                    left: 0;
                                }

                                .scroll-right {
                                    right: 0;
                                }

                                .hide-scrollbar {
                                    -ms-overflow-style: none;
                                    scrollbar-width: none;
                                    padding-bottom: 5px;
                                    scroll-behavior: smooth;
                                }

                                .hide-scrollbar::-webkit-scrollbar {
                                    display: none;
                                }

                                .nav-pills .nav-link {
                                    color: #6c757d;
                                    background-color: #f8f9fa;
                                    border: 1px solid #dee2e6;
                                    padding: 0.5rem 1rem;
                                    font-size: 0.875rem;
                                    transition: all 0.15s ease-in-out;
                                    white-space: nowrap;
                                }

                                .nav-pills .nav-link:hover {
                                    background-color: #e9ecef;
                                }

                                .nav-pills .nav-link.active {
                                    background-color: rgb(218, 119, 86);
                                    border-color: rgb(218, 119, 86);
                                    color: white;
                                }

                                .badge {
                                    font-weight: 500;
                                    padding: 0.35em 0.65em;
                                }
                            </style>
                            <script>
                                function updateScrollButtons() {
                                    const tabContainer = document.getElementById('submissionTabs');
                                    const leftButton = document.querySelector('.scroll-left');
                                    const rightButton = document.querySelector('.scroll-right');

                                    // Remove disabled class first
                                    leftButton.classList.remove('disabled');
                                    rightButton.classList.remove('disabled');

                                    // Add disabled class based on scroll position
                                    if (tabContainer.scrollLeft <= 0) {
                                        leftButton.classList.add('disabled');
                                    }

                                    if (Math.ceil(tabContainer.scrollLeft + tabContainer.clientWidth) >= tabContainer.scrollWidth) {
                                        rightButton.classList.add('disabled');
                                    }
                                }

                                function scrollTabs(direction) {
                                    const tabContainer = document.getElementById('submissionTabs');
                                    const scrollAmount = tabContainer.clientWidth / 2; // Scroll half the container width

                                    if (direction === 'left') {
                                        tabContainer.scrollTo({
                                            left: tabContainer.scrollLeft - scrollAmount,
                                            behavior: 'smooth'
                                        });
                                    } else {
                                        tabContainer.scrollTo({
                                            left: tabContainer.scrollLeft + scrollAmount,
                                            behavior: 'smooth'
                                        });
                                    }
                                }

                                // Add event listeners
                                document.addEventListener('DOMContentLoaded', function() {
                                    const tabContainer = document.getElementById('submissionTabs');
                                    tabContainer.addEventListener('scroll', updateScrollButtons);
                                    window.addEventListener('resize', updateScrollButtons);
                                    updateScrollButtons(); // Initial check
                                });
                            </script>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="submissionTabContent">
                                <!-- Tab Semua -->
                                <div class="tab-pane fade show active" id="all-submissions" role="tabpanel" aria-labelledby="all-tab">
                                    <?php if (count($pengumpulan_list) > 0 || count($siswa_belum_mengumpulkan) > 0): ?>
                                        <div class="row g-3">
                                            <!-- Siswa yang sudah mengumpulkan -->
                                            <?php foreach ($pengumpulan_list as $pengumpulan): ?>
                                                <div class="col-12">
                                                    <div class="card submission-card">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?php
                                                                            if (!empty($pengumpulan['photo_url']) && $pengumpulan['photo_type'] === 'avatar') {
                                                                                echo $pengumpulan['photo_url'];
                                                                            } elseif (!empty($pengumpulan['foto_profil']) && $pengumpulan['photo_type'] === 'upload') {
                                                                                echo 'uploads/profil/' . $pengumpulan['foto_profil'];
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                            ?>" alt="Profile" class="student-avatar rounded-circle">

                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <h6 class="mb-0"><?php echo ucwords(htmlspecialchars($pengumpulan['nama_siswa'])); ?></h6>
                                                                        <div class="text-end">
                                                                            <?php
                                                                            $submission_time = new DateTime($pengumpulan['waktu_pengumpulan']);
                                                                            $status_text = $submission_time <= $deadline ? 'Tepat Waktu' : 'Terlambat';
                                                                            $status_class = $submission_time <= $deadline ? 'success' : 'warning';
                                                                            ?>
                                                                            <span class="badge bg-<?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>

                                                                            <?php if ($pengumpulan['nilai'] !== null): ?>
                                                                                <span class="badge bg-info status-badge ms-1">
                                                                                    <?php echo $pengumpulan['nilai']; ?>/<?php echo $data_tugas['poin_maksimal']; ?>
                                                                                </span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-secondary status-badge ms-1">Belum Dinilai</span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>

                                                                    <small class="text-muted d-block mb-2">
                                                                        Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($pengumpulan['waktu_pengumpulan'])); ?>
                                                                    </small>

                                                                    <div class="d-flex gap-2">
                                                                        <a href="<?php echo $pengumpulan['file_path']; ?>" class="btn bg-white border btn-sm" style="border-radius: 12px;" target="_blank" download>
                                                                            <i class="bi bi-download me-1"></i>
                                                                            Unduh Tugas
                                                                        </a>
                                                                        <button type="button" class="btn border btn-sm" style="border-radius: 12px;" onclick="nilaiTugas(<?php echo $pengumpulan['id']; ?>, '<?php echo htmlspecialchars($pengumpulan['nama_siswa']); ?>', <?php echo $pengumpulan['nilai'] ?? 'null'; ?>)">
                                                                            <i class="bi bi-check-circle me-1"></i>
                                                                            <?php echo $pengumpulan['nilai'] !== null ? 'Edit Nilai' : 'Beri Nilai'; ?>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                            <!-- Siswa yang belum mengumpulkan -->
                                            <?php foreach ($siswa_belum_mengumpulkan as $siswa): ?>
                                                <div class="col-12">
                                                    <div class="card submission-card bg-light">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?php
                                                                            if (!empty($siswa['photo_url']) && $siswa['photo_type'] === 'avatar') {
                                                                                echo $siswa['photo_url'];
                                                                            } elseif (!empty($siswa['foto_profil']) && $siswa['photo_type'] === 'upload') {
                                                                                echo 'uploads/profil/' . $siswa['foto_profil'];
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                            ?>" alt="Profile" class="student-avatar rounded-circle opacity-75">

                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <h6 class="mb-0"><?php echo ucwords(htmlspecialchars($siswa['nama'])); ?></h6>
                                                                        <span class="badge bg-danger status-badge">
                                                                            <i class="bi bi-exclamation-circle me-1"></i>
                                                                            Belum Mengumpulkan
                                                                        </span>
                                                                    </div>
                                                                    <small class="text-muted">Tidak ada tugas</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-file-earmark-x fs-1 text-muted mb-3"></i>
                                            <h5>Belum ada siswa di kelas ini</h5>
                                            <p class="text-muted">Tambahkan siswa ke kelas terlebih dahulu</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab Sudah Mengumpulkan -->
                                <div class="tab-pane fade" id="submitted" role="tabpanel" aria-labelledby="submitted-tab">
                                    <?php if (count($pengumpulan_list) > 0): ?>
                                        <div class="row g-3">
                                            <?php foreach ($pengumpulan_list as $pengumpulan): ?>
                                                <div class="col-12">
                                                    <div class="card submission-card">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?php
                                                                            if (!empty($pengumpulan['photo_url']) && $pengumpulan['photo_type'] === 'avatar') {
                                                                                echo $pengumpulan['photo_url'];
                                                                            } elseif (!empty($pengumpulan['foto_profil']) && $pengumpulan['photo_type'] === 'upload') {
                                                                                echo 'uploads/profil/' . $pengumpulan['foto_profil'];
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                            ?>" alt="Profile" class="student-avatar rounded-circle">

                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <h6 class="mb-0"><?php echo ucwords(htmlspecialchars($pengumpulan['nama_siswa'])); ?></h6>

                                                                        <div>
                                                                            <?php
                                                                            $submission_time = new DateTime($pengumpulan['waktu_pengumpulan']);
                                                                            $status_text = $submission_time <= $deadline ? 'Tepat Waktu' : 'Terlambat';
                                                                            $status_class = $submission_time <= $deadline ? 'success' : 'warning';
                                                                            ?>
                                                                            <span class="badge bg-<?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>

                                                                            <?php if ($pengumpulan['nilai'] !== null): ?>
                                                                                <span class="badge bg-info status-badge ms-1">
                                                                                    <?php echo $pengumpulan['nilai']; ?>/<?php echo $data_tugas['poin_maksimal']; ?>
                                                                                </span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-secondary status-badge ms-1">Belum Dinilai</span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>

                                                                    <small class="text-muted d-block mb-2">
                                                                        Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($pengumpulan['waktu_pengumpulan'])); ?>
                                                                    </small>

                                                                    <div class="d-flex gap-2">
                                                                        <a href="<?php echo $pengumpulan['file_path']; ?>" class="btn bg-white border btn-sm" style="border-radius: 12px;" target="_blank" download>
                                                                            <i class="bi bi-download me-1"></i>
                                                                            Unduh Tugas
                                                                        </a>
                                                                        <button type="button" class="btn border btn-sm" style="border-radius: 12px;" onclick="nilaiTugas(<?php echo $pengumpulan['id']; ?>, '<?php echo htmlspecialchars($pengumpulan['nama_siswa']); ?>', <?php echo $pengumpulan['nilai'] ?? 'null'; ?>)">
                                                                            <i class="bi bi-check-circle me-1"></i>
                                                                            <?php echo $pengumpulan['nilai'] !== null ? 'Edit Nilai' : 'Beri Nilai'; ?>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-file-earmark-x fs-1 text-muted mb-3"></i>
                                            <h5>Belum ada pengumpulan</h5>
                                            <p class="text-muted">Belum ada siswa yang mengumpulkan tugas ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab Belum Mengumpulkan -->
                                <div class="tab-pane fade" id="missing" role="tabpanel" aria-labelledby="missing-tab">
                                    <?php if (count($siswa_belum_mengumpulkan) > 0): ?>
                                        <div class="row g-3">
                                            <?php foreach ($siswa_belum_mengumpulkan as $siswa): ?>
                                                <div class="col-12">
                                                    <div class="card submission-card bg-light">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?php
                                                                            if (!empty($siswa['photo_url']) && $siswa['photo_type'] === 'avatar') {
                                                                                echo $siswa['photo_url'];
                                                                            } elseif (!empty($siswa['foto_profil']) && $siswa['photo_type'] === 'upload') {
                                                                                echo 'uploads/profil/' . $siswa['foto_profil'];
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                            ?>" alt="Profile" class="student-avatar rounded-circle opacity-75">

                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <h6 class="mb-0"><?php echo ucwords(htmlspecialchars($siswa['nama'])); ?></h6>

                                                                        <span class="badge bg-danger status-badge">
                                                                            <i class="bi bi-exclamation-circle me-1"></i>
                                                                            Belum Mengumpulkan
                                                                        </span>
                                                                    </div>
                                                                    <small class="text-muted">Tidak ada tugas</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-check-circle fs-1 text-success mb-3"></i>
                                            <h5>Semua siswa telah mengumpulkan</h5>
                                            <p class="text-muted">Seluruh siswa di kelas sudah mengumpulkan tugas ini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab Sudah Dinilai -->
                                <div class="tab-pane fade" id="graded" role="tabpanel" aria-labelledby="graded-tab">
                                    <?php
                                    $dinilai = array_filter($pengumpulan_list, function ($item) {
                                        return $item['nilai'] !== null;
                                    });

                                    if (count($dinilai) > 0):
                                    ?>
                                        <div class="row g-3">
                                            <?php foreach ($dinilai as $pengumpulan): ?>
                                                <div class="col-12">
                                                    <div class="card submission-card">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?php
                                                                            if (!empty($pengumpulan['photo_url']) && $pengumpulan['photo_type'] === 'avatar') {
                                                                                echo $pengumpulan['photo_url'];
                                                                            } elseif (!empty($pengumpulan['foto_profil']) && $pengumpulan['photo_type'] === 'upload') {
                                                                                echo 'uploads/profil/' . $pengumpulan['foto_profil'];
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                            ?>" alt="Profile" class="student-avatar rounded-circle">

                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($pengumpulan['nama_siswa']); ?></h6>
                                                                        <div>
                                                                            <?php
                                                                            $submission_time = new DateTime($pengumpulan['waktu_pengumpulan']);
                                                                            $status_text = $submission_time <= $deadline ? 'Tepat Waktu' : 'Terlambat';
                                                                            $status_class = $submission_time <= $deadline ? 'success' : 'warning';
                                                                            ?>
                                                                            <span class="badge bg-<?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>
                                                                            <span class="badge bg-info status-badge ms-1">
                                                                                <?php echo $pengumpulan['nilai']; ?>/<?php echo $data_tugas['poin_maksimal']; ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <small class="text-muted d-block mb-2">
                                                                        Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($pengumpulan['waktu_pengumpulan'])); ?>
                                                                    </small>

                                                                    <div class="d-flex gap-2">
                                                                        <a href="<?php echo $pengumpulan['file_path']; ?>" class="btn bg-white border btn-sm" style="border-radius: 12px;" target="_blank" download>
                                                                            <i class="bi bi-download me-1"></i>
                                                                            Unduh Tugas
                                                                        </a>
                                                                        <button type="button" class="btn border btn-sm" style="border-radius: 12px;" onclick="nilaiTugas(<?php echo $pengumpulan['id']; ?>, '<?php echo htmlspecialchars($pengumpulan['nama_siswa']); ?>', <?php echo $pengumpulan['nilai']; ?>)">
                                                                            <i class="bi bi-check-circle me-1"></i>
                                                                            Edit Nilai
                                                                        </button>
                                                                    </div>

                                                                    <?php if ($pengumpulan['komentar_guru']): ?>
                                                                        <div class="mt-2 p-2 bg-light rounded">
                                                                            <small class="text-muted d-block mb-1">Komentar:</small>
                                                                            <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($pengumpulan['komentar_guru'])); ?></p>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-clipboard-x fs-1 text-muted mb-3"></i>
                                            <h5>Anda belum menilai tugas Apapun</h5>
                                            <p class="text-muted">Belum ada pengumpulan yang telah diberi nilai</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Penilaian -->
        <div class="modal fade" id="modalPenilaian" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centere modal-fullscreen"> <!-- Ubah ukuran modal jadi extra large -->
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Bagian Preview Dokumen (Kiri) -->
                            <div class="col-lg-8">
                                <div class="card border shadow">
                                    <div class="card-body p-0">
                                        <!-- Navigation untuk multiple file -->
                                        <div class="bg-white px-3 py-2 d-flex border-bottom justify-content-between align-items-center position-relative" style="border-radius: 10px 10px 0 0;">
                                            <!-- Left chevron -->
                                            <button class="btn btn-sm rounded-circle position-absolute start-0 ms-3" id="prevFile" style="width: 32px; height: 32px;">
                                                <i class="bi bi-chevron-left"></i>
                                            </button>

                                            <!-- Center file info -->
                                            <div class="w-100 text-center">
                                                <span id="currentFileInfo" class="fw-medium" style="font-size: 0.9rem;">File 1 dari 1</span>
                                            </div>

                                            <!-- Right chevron -->
                                            <button class="btn btn-sm  rounded-circle position-absolute end-0 me-3" id="nextFile" style="width: 32px; height: 32px;">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                        </div>

                                        <style>
                                            /* iOS style button hover/active states */
                                            .btn-light {
                                                background: rgba(242, 242, 247, 0.9);
                                                border: none;
                                                transition: all 0.2s;
                                            }

                                            .btn-light:hover {
                                                background: rgba(242, 242, 247, 1);
                                            }

                                            .btn-light:active {
                                                transform: scale(0.95);
                                                background: rgba(229, 229, 234, 1);
                                            }
                                        </style>

                                        <!-- Area Preview -->
                                        <div id="filePreview" class="position-relative" style="height: 550px;">
                                            <!-- Preview akan dimuat di sini -->
                                            <!-- Ganti bagian pdfViewer Anda dengan kode ini -->
                                            <div id="pdfViewer" class="h-100 d-none">
                                                <iframe id="pdfFrame" width="100%" height="100%" frameborder="0" style="border-radius: 20px;"></iframe>
                                            </div>
                                            <div id="imageViewer" class="h-100 d-none">
                                                <div id="imageViewerContainer" class="h-100" style="border-radius: 20px;"></div>
                                                <div class="image-controls position-absolute bottom-0 start-50 translate-middle-x mb-3 bg-dark bg-opacity-75 rounded-pill px-3 py-2">
                                                    <button class="btn btn-sm btn-light rounded-circle me-2" onclick="rotateImage(-90)">
                                                        <i class="bi bi-arrow-counterclockwise" style="color: rgb(218, 119, 86);"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light rounded-circle me-2" onclick="rotateImage(90)">
                                                        <i class="bi bi-arrow-clockwise" style="color: rgb(218, 119, 86);"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light rounded-circle me-2" onclick="zoomIn()">
                                                        <i class="bi bi-zoom-in" style="color: rgb(218, 119, 86);"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light rounded-circle me-2" onclick="zoomOut()">
                                                        <i class="bi bi-zoom-out" style="color: rgb(218, 119, 86);"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light rounded-circle" onclick="resetView()">
                                                        <i class="bi bi-arrows-angle-contract" style="color: rgb(218, 119, 86);"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Add OpenSeadragon library -->
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/openseadragon/4.1.0/openseadragon.min.js"></script>
                                            <script>
                                                let viewer = null;

                                                function initImageViewer(imageUrl) {
                                                    if (viewer) {
                                                        viewer.destroy();
                                                    }

                                                    viewer = OpenSeadragon({
                                                        id: "imageViewerContainer",
                                                        tileSources: {
                                                            type: 'image',
                                                            url: imageUrl
                                                        },
                                                        showNavigationControl: false,
                                                        showHomeControl: false,
                                                        defaultZoomLevel: 1,
                                                        minZoomLevel: 0.1,
                                                        maxZoomLevel: 10
                                                    });

                                                    // Tambahkan event listener
                                                    viewer.addHandler('open', function() {
                                                        console.log('Viewer ready');
                                                    });
                                                }

                                                function rotateImage(degrees) {
                                                    if (viewer && viewer.isOpen()) { // tambahkan pengecekan
                                                        const viewport = viewer.viewport;
                                                        viewport.setRotation((viewport.getRotation() + degrees) % 360);
                                                    }
                                                }

                                                function zoomIn() {
                                                    if (viewer) {
                                                        viewer.viewport.zoomBy(1.5);
                                                    }
                                                }

                                                function zoomOut() {
                                                    if (viewer) {
                                                        viewer.viewport.zoomBy(0.75);
                                                    }
                                                }

                                                function resetView() {
                                                    if (viewer) {
                                                        viewer.viewport.goHome();
                                                        viewer.viewport.setRotation(0);
                                                    }
                                                }
                                            </script>
                                            <div id="docViewer" class="h-100 d-none">
                                                <div id="docContent" class="h-100 p-4 overflow-auto"></div>
                                            </div>
                                            <div id="unsupportedViewer" class="h-100 d-none d-flex align-items-center justify-content-center">
                                                <div class="text-center">
                                                    <i class="bi bi-file-earmark-x display-4 text-muted"></i>
                                                    <p class="mt-2">Preview tidak tersedia untuk tipe file ini</p>
                                                    <a href="#" id="downloadFile" class="btn btn-primary mt-2">
                                                        <i class="bi bi-download"></i> Download File
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Penilaian (Kanan) -->
                            <div class="col-lg-4">
                                <form id="formPenilaian" action="proses_nilai_tugas.php" method="POST">
                                    <input type="hidden" name="pengumpulan_id" id="pengumpulan_id">
                                    <input type="hidden" name="tugas_id" value="<?php echo $tugas_id; ?>">

                                    <!-- iOS Style Card -->
                                    <div class="card border rounded-4">
                                        <div class="card-body p-4">
                                            <!-- Student Info -->
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h4 class="mb-0" id="namaSiswa"></h4>
                                                </div>
                                                <small class="text-secondary d-block" id="waktuPengumpulan"></small>
                                            </div>

                                            <!-- Message Section -->
                                            <div class="mb-4">
                                                <label class="form-label text-secondary mb-2">
                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                    Pesan dari Siswa
                                                </label>
                                                <div id="pesanSiswa" class="p-3 border rounded-3" style="font-size: 0.95rem; min-height: 80px; max-height: 120px; overflow-y: auto;">
                                                    <!-- Message will be loaded here -->
                                                </div>
                                            </div>

                                            <!-- Score Input -->
                                            <div class="mb-4">
                                                <label for="nilaiInput" class="form-label text-secondary mb-2">
                                                    <i class="bi bi-star me-1"></i>
                                                    Beri Nilai (1-<?php echo $data_tugas['poin_maksimal']; ?>)
                                                </label>
                                                <input type="number"
                                                    class="form-control text-start form-control-lg rounded-3"
                                                    id="nilaiInput"
                                                    name="nilai"
                                                    min="1"
                                                    max="<?php echo $data_tugas['poin_maksimal']; ?>"
                                                    required>
                                            </div>

                                            <!-- Comment Input -->
                                            <div class="mb-4">
                                                <label for="komentarInput" class="form-label text-secondary mb-2">
                                                    <i class="bi bi-pencil me-1"></i>
                                                    Beri Komentar
                                                </label>
                                                <textarea class="form-control rounded-3"
                                                    id="komentarInput"
                                                    name="komentar"
                                                    rows="2"></textarea>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="d-flex gap-2 mt-5">
                                                <button type="button" class="btn btn-outline-secondary rounded-3 py-3 w-50"
                                                    data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-2"></i>
                                                    Tutup
                                                </button>
                                                <button type="submit" class="btn rounded-3 py-3 w-50"
                                                    style="background-color:rgb(218, 119, 86); color: white; font-weight: 500;">
                                                    <i class="bi bi-check-circle me-2"></i>
                                                    Simpan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <style>
                                /* iOS Style Form Elements */
                                .form-control {
                                    border: 1px solid rgba(0, 0, 0, 0.1);
                                    transition: all 0.2s;
                                    font-size: 0.95rem;
                                }

                                .form-control:focus {
                                    border-color: #007AFF;
                                    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
                                }

                                .form-control-lg {
                                    height: 3.2rem;
                                    font-size: 1.5rem;
                                    text-align: center;
                                    font-weight: 500;
                                }

                                textarea.form-control {
                                    line-height: 1.5;
                                }

                                /* iOS Style Labels */
                                .form-label {
                                    font-size: 0.9rem;
                                    font-weight: 500;
                                }

                                /* iOS Style Button */
                                .btn {
                                    transition: all 0.2s;
                                }

                                .btn:active {
                                    transform: scale(0.98);
                                }

                                /* iOS Style Card */
                                .card {
                                    transition: all 0.3s;
                                }

                                /* Custom Scrollbar */
                                ::-webkit-scrollbar {
                                    width: 8px;
                                }

                                ::-webkit-scrollbar-track {
                                    background: #f1f1f1;
                                    border-radius: 4px;
                                }

                                ::-webkit-scrollbar-thumb {
                                    background: #c1c1c1;
                                    border-radius: 4px;
                                }

                                ::-webkit-scrollbar-thumb:hover {
                                    background: #a8a8a8;
                                }
                            </style>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function nilaiTugas(id, nama, nilai) {
                document.getElementById('pengumpulan_id').value = id;
                document.getElementById('namaSiswa').textContent = nama;

                // Ambil data pengumpulan tugas
                fetch(`get_pengumpulan_detail.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response data:', data); // Tambahkan ini
                        if (data.success) {
                            // Set nilai dan komentar jika ada
                            document.getElementById('nilaiInput').value = data.nilai || '';
                            document.getElementById('komentarInput').value = data.komentar || '';

                            // Set waktu pengumpulan
                            document.getElementById('waktuPengumpulan').textContent =
                                `Dikumpulkan pada ${new Date(data.waktu_pengumpulan).toLocaleString()}`;

                            // Set pesan siswa
                            document.getElementById('pesanSiswa').textContent = data.pesan || 'Siswa tidak memberikan pesan';

                            // Setup file preview
                            setupFilePreview(data.files);
                        }
                    });

                const modalPenilaian = new bootstrap.Modal(document.getElementById('modalPenilaian'));
                modalPenilaian.show();
            }

            function setupFilePreview(files) {
                console.log('Files received:', files);
                let currentFileIndex = 0;
                const totalFiles = files.length;

                function updateFileInfo() {
                    document.getElementById('currentFileInfo').textContent =
                        `File ${currentFileIndex + 1} dari ${totalFiles}`;
                }

                function showFile(index) {
                    const file = files[index];
                    console.log('Current file:', file);

                    // Determine file type from file name extension
                    const fileName = file.name.toLowerCase();

                    // Hide all viewers
                    document.querySelectorAll('#filePreview > div').forEach(div => div.classList.add('d-none'));

                    // Show appropriate viewer based on file extension
                    // Show appropriate viewer based on file extension
                    // Modifikasi di fungsi setupFilePreview, bagian PDF handler
                    if (fileName.endsWith('.pdf')) {
                        const pdfViewer = document.getElementById('pdfViewer');
                        pdfViewer.classList.remove('d-none');

                        // Direct embedding - lets browser handle PDF display
                        const pdfContainer = document.createElement('div');
                        pdfContainer.style.width = '100%';
                        pdfContainer.style.height = '100%';
                        pdfContainer.style.overflow = 'hidden';

                        const pdfEmbed = document.createElement('embed');
                        pdfEmbed.src = file.url;
                        pdfEmbed.type = 'application/pdf';
                        pdfEmbed.style.width = '100%';
                        pdfEmbed.style.height = '100%';
                        pdfEmbed.style.border = 'none';

                        pdfContainer.appendChild(pdfEmbed);
                        pdfViewer.innerHTML = '';
                        pdfViewer.appendChild(pdfContainer);
                    } else if (/\.(jpg|jpeg|png|gif)$/i.test(fileName)) {
                        const imageViewer = document.getElementById('imageViewer');
                        imageViewer.classList.remove('d-none');
                        initImageViewer(file.url);
                    } else if (/\.(doc|docx)$/i.test(fileName)) {
                        const docViewer = document.getElementById('docViewer');
                        docViewer.classList.remove('d-none');

                        fetch(file.url)
                            .then(response => response.arrayBuffer())
                            .then(arrayBuffer => {
                                mammoth.convertToHtml({
                                        arrayBuffer: arrayBuffer
                                    })
                                    .then(result => {
                                        document.getElementById('docContent').innerHTML = result.value;
                                    });
                            });
                    } else {
                        const unsupportedViewer = document.getElementById('unsupportedViewer');
                        unsupportedViewer.classList.remove('d-none');
                        document.getElementById('downloadFile').href = file.url;
                    }

                    updateFileInfo();
                }
                // Setup navigation buttons
                document.getElementById('prevFile').onclick = () => {
                    if (currentFileIndex > 0) {
                        currentFileIndex--;
                        showFile(currentFileIndex);
                    }
                };

                document.getElementById('nextFile').onclick = () => {
                    if (currentFileIndex < totalFiles - 1) {
                        currentFileIndex++;
                        showFile(currentFileIndex);
                    }
                };

                // Show first file
                if (totalFiles > 0) {
                    showFile(0);
                }
            }
        </script>
        <style>
            #docLoadingIndicator {
                background: rgba(255, 255, 255, 0.9);
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            #docViewer iframe {
                background: white;
                border-radius: 8px;
            }
        </style>

        <!-- Modal Konfirmasi Tutup Tugas -->
        <div class="modal fade" id="modalTutupTugas" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <!-- Icon -->
                        <div class="text-center mb-4">
                            <div class="icon-circle bg-danger bg-opacity-10 mx-auto mb-3">
                                <i class="bi bi-lock-fill text-danger"></i>
                            </div>
                            <h5 class="modal-title mb-2">Tutup Penugasan</h5>
                            <p class="text-secondary mb-0">Apakah Anda yakin ingin menutup penugasan ini?</p>
                        </div>

                        <!-- Warning Box -->
                        <div class="info-box mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                <span class="fw-medium">Setelah ditutup:</span>
                            </div>
                            <ul class="mb-0 ps-4">
                                <li>Siswa tidak dapat mengumpulkan tugas lagi</li>
                                <li>Status akan berubah menjadi "Ditutup"</li>
                                <li>Anda masih dapat melihat & menilai tugas yang sudah dikumpulkan</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <form action="tutup_tugas.php" method="POST">
                            <input type="hidden" name="tugas_id" value="<?php echo $tugas_id; ?>">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger ios-btn">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Tutup Penugasan
                                </button>
                            </div>
                        </form>
                        <div class="text-center mt-1">
                            <p class="text-muted" style="font-size: 12px;">Tekan <span style="background-color: grey; font-size:12px;" class="rounded px-2 text-white">Esc</span> atau tekan layar abu-abu di luar modal</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- iOS Style CSS -->
        <style>
            .modal-content {
                border: none;
                border-radius: 14px;
            }

            .icon-circle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .icon-circle i {
                font-size: 24px;
            }

            .info-box {
                background-color: #fff9e6;
                border-radius: 12px;
                padding: 16px;
                font-size: 0.9rem;
            }

            .info-box ul {
                color: #666;
                line-height: 1.6;
            }

            .ios-btn {
                border-radius: 12px;
                padding: 12px;
                font-size: 1rem;
                font-weight: 500;
                transition: all 0.2s;
            }

            .ios-btn:active {
                transform: scale(0.98);
            }

            .btn-danger {
                background-color: #ff3b30;
                border: none;
            }

            .btn-danger:hover {
                background-color: #ff2d55;
            }

            .btn-light {
                background-color: #f2f2f7;
                border: none;
                color: #007aff;
            }

            .btn-light:hover {
                background-color: #e5e5ea;
            }

            .modal-title {
                font-size: 1.25rem;
                font-weight: 600;
            }

            .text-secondary {
                color: #8e8e93 !important;
            }
        </style>

        <!-- Tambahkan script untuk konfirmasi -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('#modalTutupTugas form');
                form.addEventListener('submit', function(e) {
                    const confirmed = confirm('Tindakan ini tidak dapat dibatalkan. Lanjutkan?');
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });

                // Tambahkan ini untuk melihat ketika tombol penilaian diklik
                document.querySelectorAll('[onclick*="nilaiTugas"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        console.log('Nilai button clicked');
                    });
                });
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function nilaiTugas(id, nama, nilai) {
                console.log('nilaiTugas called with:', {
                    id,
                    nama,
                    nilai
                }); // Log parameter yang diterima

                document.getElementById('pengumpulan_id').value = id;
                document.getElementById('namaSiswa').textContent = nama;

                // Log URL yang akan di-fetch
                const url = `get_pengumpulan_detail.php?id=${id}`;
                console.log('Fetching from:', url);

                fetch(url)
                    .then(response => {
                        console.log('Response received:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Data received:', data);
                        if (data.success) {
                            // Set nilai dan komentar
                            document.getElementById('nilaiInput').value = data.nilai || '';
                            document.getElementById('komentarInput').value = data.komentar || '';

                            // Format waktu pengumpulan sesuai permintaan
                            const waktuPengumpulan = new Date(data.waktu_pengumpulan);
                            const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const formattedDate = `${hari[waktuPengumpulan.getDay()]}, ${waktuPengumpulan.getDate()} ${bulan[waktuPengumpulan.getMonth()]} ${waktuPengumpulan.getFullYear()} pada pukul ${String(waktuPengumpulan.getHours()).padStart(2, '0')}:${String(waktuPengumpulan.getMinutes()).padStart(2, '0')}:${String(waktuPengumpulan.getSeconds()).padStart(2, '0')} WIB`;

                            document.getElementById('waktuPengumpulan').textContent = `Dikumpulkan pada ${formattedDate}`;

                            // Set pesan siswa
                            document.getElementById('pesanSiswa').textContent = data.pesan || 'Tidak ada pesan';

                            // Setup file preview
                            if (data.files && data.files.length > 0) {
                                console.log('Setting up preview for files:', data.files);
                                setupFilePreview(data.files);
                            } else {
                                console.log('No files found in response');
                            }
                        } else {
                            console.error('Data fetch failed:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });

                const modalPenilaian = new bootstrap.Modal(document.getElementById('modalPenilaian'));
                modalPenilaian.show();
            }

            // Helper function untuk format ukuran file
            function formatFileSize(bytes) {
                if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(2) + ' MB';
                } else if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(2) + ' KB';
                } else {
                    return bytes + ' bytes';
                }
            }
        </script>
    </body>

</html>