<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// // Cek apakah ada ujian_id
// if (!isset($_GET['ujian_id'])) {
//     header("Location: ujian_guru.php");
//     exit();
// }

$ujian_id = $_GET['ujian_id'];
$userid = $_SESSION['userid'];

// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Ambil data ujian beserta informasi kelas
// Perbaiki query untuk mengambil data yang benar
$query_ujian = "SELECT u.*, k.tingkat 
FROM ujian u 
INNER JOIN kelas k ON u.kelas_id = k.id 
WHERE u.id = '$ujian_id'";
$result_ujian = mysqli_query($koneksi, $query_ujian);

// Cek apakah ujian ditemukan
if (mysqli_num_rows($result_ujian) == 0) {
    if ($_SESSION['level'] == 'admin') {
        header("Location: ujian_admin.php"); // Pastikan file ini ada
    } else {
        header("Location: ujian_guru.php");
    }
    exit();
}

// // Cek apakah ujian ditemukan dan milik guru tersebut 
// if (mysqli_num_rows($result_ujian) == 0) {
//     header("Location: ujian_guru.php");
//     exit();
// }

$ujian = mysqli_fetch_assoc($result_ujian);


// Ambil jumlah soal yang sudah dibuat
$query_soal = "SELECT COUNT(*) as total_soal FROM bank_soal WHERE ujian_id = '$ujian_id'";
$result_soal = mysqli_query($koneksi, $query_soal);
$total_soal = mysqli_fetch_assoc($result_soal)['total_soal'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="assets/smagaedu.png" type="image/png">
    <script src="https: //unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module">
    </script>


    <title>Buat Soal - SMAGAEdu</title>
    <style>
        /* Style yang sama dengan sebelumnya */
        body {
            font-family: merriweather;
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

        .menu-samping {
            position: fixed;
            width: 13rem;
            z-index: 1000;
        }

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
            .menu-samping {
                display: none;
            }

            .col-utama {
                margin-left: 0 !important;
            }
        }

        .soal-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .ai-button {
            position: relative;
            width: 45px;
            height: 45px;
        }

        .ai-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(218, 119, 86, 0.9);
            border-radius: 12px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .ai-loader.show {
            display: flex;
        }

        .ai-loader::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 3px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        .generate-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;

        }

        .generate-message {
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 12px;
        }

        .generate-message i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .generate-overlay.fade-in {
            animation: fadeIn 0.5s ease-in-out forwards;
        }

        .generate-overlay.fade-out {
            animation: fadeOut 0.5s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }


        .pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
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
</head>

<body>
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

    <?php
    // Check if cookie exists to hide admin notification
    $showAdminModal = true;
    if (isset($_COOKIE['hide_admin_notification']) && $_COOKIE['hide_admin_notification'] == 'true') {
        $showAdminModal = false;
    }

    // Get teacher's name for admin view
    if ($_SESSION['level'] == 'admin') {
        $query_guru_info = "SELECT g.namaLengkap as nama_guru FROM kelas k JOIN guru g ON k.guru_id = g.username WHERE k.id = '$ujian_id'";
        $result_guru_info = mysqli_query($koneksi, $query_guru_info);
        $guru_info = mysqli_fetch_assoc($result_guru_info);
        $nama_guru = $guru_info['nama_guru'] ?? 'Tidak diketahui';

        if ($showAdminModal):
    ?>
            <!-- Admin Mode Modal -->
            <div class="modal fade" id="adminModeModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px;">
                        <div class="modal-header p-0 position-relative" style="height: 120px; overflow: hidden; border-bottom: none;">
                            <img src="assets/vision.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                        </div>
                        <div class="modal-body text-center p-4">
                            <h5 class="mt-3 fw-bold">Mode Pengawas Aktif</h5>
                            <p class="mb-4" style="font-size: 14px;">Kami mendeteksi Anda adalah administrator SMAGAEdu. Anda dapat mengawasi seluruh aktivitas di kelas ini namun beberapa fitur akan kami batasi.</p>

                            <div class="p-3 border text-start bg-light mb-4" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div class="text-start">
                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Merasa Kelas Ini Tidak Beres?</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">silahkan hubungi <strong><?php echo htmlspecialchars($nama_guru); ?></strong> untuk mendapatkan akses penuh.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mt-3 mb-4 d-flex">
                                <input class="form-check-input" type="checkbox" id="dontShowAgain">
                                <label class="form-check-label text-muted ms-2" style="font-size: 14px;" for="dontShowAgain">
                                    Jangan tampilkan pesan ini lagi
                                </label>
                            </div>
                            <div class="d-flex gap-2 btn-group">
                                <button type="button" class="btn px-4 text-white" id="closeAdminModal" style="border-radius: 12px; background-color:rgb(219, 106, 68);">Saya Mengerti</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Show the modal when page loads
                document.addEventListener('DOMContentLoaded', function() {
                    const adminModal = new bootstrap.Modal(document.getElementById('adminModeModal'));
                    adminModal.show();

                    // Handle the "don't show again" checkbox
                    document.getElementById('closeAdminModal').addEventListener('click', function() {
                        if (document.getElementById('dontShowAgain').checked) {
                            // Set cookie to hide notification for 30 days
                            const expiryDate = new Date();
                            expiryDate.setDate(expiryDate.getDate() + 30);
                            document.cookie = "hide_admin_notification=true; expires=" + expiryDate.toUTCString() + "; path=/";
                        }
                        adminModal.hide();
                    });
                });
            </script>
    <?php
        endif;
    }
    ?>


    <!-- Main Content -->
    <div class="col p-4 col-utama">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Buat Soal</h3>
            <!-- <div class="d-flex gap-2"> -->
            <!-- Import dari Word button -->
            <!-- <button type="button"
                    class="btn btn-action d-flex align-items-center gap-2 px-3 py-2"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadSoalModal">
                    <i class="bi bi-file-earmark-word fs-5"></i>
                    <div class="d-none text-start d-md-block">
                        <div class="d-flex gap-2">
                            <span class="fw-medium" style="font-size: 13px;">Import Word</span>
                            <span class=" badge rounded-pill bg-warning text-dark" style="font-size: 10px;">
                                BETA
                            </span>
                        </div>
                        <small class="d-block text-white-50" style="font-size: 11px;">Upload file soal</small>
                    </div>
                </button> -->

            <!-- Buat Soal dengan AI button -->
            <!-- <button type="button"
                    class="btn btn-action d-flex align-items-center gap-2 px-3 py-2"
                    data-bs-toggle="modal"
                    data-bs-target="#aiSoalModal">
                    <i class="bi bi-stars fs-5"></i>
                    <div class="d-none text-start d-md-block">
                        <span class="fw-medium" style="font-size: 13px;">Bantuan SAGA</span>
                        <small class="d-block text-white-50" style="font-size: 11px;">Generate soal otomatis</small>
                    </div>
                </button> -->

            <!-- Tambah Soal Manual button -->
            <!-- <button type="button"
                    class="btn btn-action d-flex align-items-center gap-2 px-3 py-2"
                    data-bs-toggle="modal"
                    onclick="pilihTipeSoal('pilihan_ganda')">
                    <i class="bi bi-plus-circle fs-5"></i>
                    <div class="d-none text-start d-md-block">
                        <span class="fw-medium" style="font-size: 13px;">Tambah Soal</span>
                        <small class="d-block text-white-50" style="font-size: 11px;">Input soal manual</small>
                    </div>
                </button>
            </div> -->

            <style>
                .btn-action {
                    background: rgb(218, 119, 86);
                    color: white;
                    border: none;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                }

                .btn-action:hover {
                    background: rgb(199, 99, 66);
                    color: white;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(218, 119, 86, 0.2);
                }

                .btn-action:active {
                    transform: translateY(0);
                }

                .btn-action::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(rgba(255, 255, 255, 0.1), transparent);
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .btn-action:hover::after {
                    opacity: 1;
                }

                @media (max-width: 768px) {
                    .btn-action {
                        padding: 0.5rem !important;
                        justify-content: center;
                    }

                    .btn-action i {
                        margin: 0;
                        font-size: 1.2rem !important;
                    }
                }
            </style>
        </div>

        <!-- Info Ujian -->
        <div class="card border mb-4" style="border-radius: 16px; background: #fff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title m-0 d-flex align-items-center gap-2" style="font-size: 18px; font-weight: 600;">
                        <i class="bi bi-journal-text" style="color: rgb(218, 119, 86);"></i>
                        <?php echo htmlspecialchars($ujian['judul']); ?>
                    </h5>
                    <!-- Toggle button with iOS style -->
                    <button class="btn d-md-none rounded-circle p-2" type="button" data-bs-toggle="collapse" data-bs-target="#detailUjian">
                        <i class="bi bi-chevron-down" style="color: rgb(218, 119, 86);"></i>
                    </button>
                </div>

                <div class="collapse d-md-block" id="detailUjian">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Mata Pelajaran -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div>
                                        <div class="label">Mata Pelajaran</div>
                                        <div class="value"><?php echo htmlspecialchars($ujian['mata_pelajaran']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-wrapper flex-shrink-0">
                                        <i class="bi bi-card-text"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="label">Deskripsi</div>
                                        <div class="value"><?php echo htmlspecialchars($ujian['deskripsi']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Materi -->
                            <?php if (!empty($ujian['materi'])): ?>
                                <div class="info-item">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="icon-wrapper">
                                            <i class="bi bi-list-check"></i>
                                        </div>
                                        <div>
                                            <div class="label">Materi Ujian</div>
                                            <div class="value">
                                                <ul class="list-unstyled mb-0 mt-2">
                                                    <?php
                                                    $materi_list = json_decode($ujian['materi'], true);
                                                    if (is_array($materi_list)) {
                                                        foreach ($materi_list as $materi) {
                                                            echo "<li class='mb-2'><i class='bi bi-dot me-2'></i>" . htmlspecialchars($materi) . "</li>";
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Waktu Pelaksanaan -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div>
                                        <div class="label">Waktu Pelaksanaan</div>
                                        <div class="value">
                                            <div class="mb-1">Mulai: <?php echo date('d M Y - H:i', strtotime($ujian['tanggal_mulai'])); ?> WIB</div>
                                            <div>Selesai: <?php echo date('d M Y - H:i', strtotime($ujian['tanggal_selesai'])); ?> WIB</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Durasi -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                    <div>
                                        <div class="label">Durasi</div>
                                        <div class="value"><?php echo $ujian['durasi']; ?> menit</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Soal -->
                            <div class="info-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-question-circle"></i>
                                    </div>
                                    <div>
                                        <div class="label">Total Soal</div>
                                        <div class="value"><?php echo $total_soal; ?> soal</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .icon-wrapper {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: rgba(218, 119, 86, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .icon-wrapper i {
                color: rgb(218, 119, 86);
                font-size: 18px;
            }

            .info-item .label {
                font-size: 13px;
                color: #8e8e93;
                margin-bottom: 4px;
            }

            .info-item .value {
                color: #1c1c1e;
                font-size: 15px;
                line-height: 1.4;
            }

            @media (max-width: 768px) {
                .card-body {
                    padding: 16px !important;
                }

                .info-item {
                    margin-bottom: 20px !important;
                }
            }
        </style>

        <script>
            // Toggle icon when collapse is shown/hidden
            document.getElementById('detailUjian').addEventListener('show.bs.collapse', function() {
                document.querySelector('[data-bs-target="#detailUjian"] i').classList.replace('bi-chevron-down', 'bi-chevron-up');
            });

            document.getElementById('detailUjian').addEventListener('hide.bs.collapse', function() {
                document.querySelector('[data-bs-target="#detailUjian"] i').classList.replace('bi-chevron-up', 'bi-chevron-down');
            });
        </script>

        <!-- Daftar Soal -->
        <style>
            .soal-card {
                background: #fff;
                border-radius: 16px;
                padding: 20px;
                margin-bottom: 16px;
                animation: fadeIn 0.5s ease-out;
                transition: all 0.2s ease;
                border: 1px solid rgba(0, 0, 0, 0.08);
            }

            .soal-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            }

            .soal-card h5 {
                font-size: 15px;
                font-weight: 600;
                color: #1c1c1e;
                margin: 0;
            }

            .soal-card p {
                font-size: 14px;
                color: #3a3a3c;
                line-height: 1.5;
                margin: 12px 0;
            }

            .soal-card .pilihan {
                font-size: 14px;
                color: #3a3a3c;
                padding: 8px 12px;
                border-radius: 10px;
                /* background: #f2f2f7; */
                margin-bottom: 8px;
                transition: background 0.2s ease;
            }

            .soal-card .pilihan.correct {
                background: rgba(52, 199, 89, 0.12);
                color: #34c759;
                font-weight: 500;
            }

            /* .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: all 0.2s ease;
            } */

            .btn-action:active {
                transform: scale(0.95);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>

        <div id="daftarSoal" class="row">
            <?php
            $query_soal_list = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id' ORDER BY id ASC";
            $result_soal_list = mysqli_query($koneksi, $query_soal_list);
            $no = 1;
            while ($soal = mysqli_fetch_assoc($result_soal_list)) {
            ?>
                <div class="col-12">
                    <div class="soal-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Soal <?php echo $no++; ?></h5>
                            <div class="d-flex gap-2">
                                <!-- <button class="btn btn-action" style="background-color: #f2f2f7;" onclick="editSoal(<?php echo $soal['id']; ?>)">
                                    <i class="bi bi-pencil" style="color: #3a3a3c; font-size: 14px;"></i>
                                </button>
                                <button class="btn btn-action" style="background-color: #ffe5e5;" onclick="hapusSoal(<?php echo $soal['id']; ?>)">
                                    <i class="bi bi-trash" style="color: #ff3b30; font-size: 14px;"></i>
                                </button> -->
                            </div>
                        </div>

                        <?php if (!empty($soal['gambar_soal'])): ?>
                            <div class="mb-3 ">
                                <img src="<?php echo htmlspecialchars($soal['gambar_soal']); ?>"
                                    class="img-fluid rounded-3 text-start border"
                                    style="max-height: 200px; width: auto;margin: 0 auto;">
                            </div>
                        <?php endif; ?>

                        <p><?php echo htmlspecialchars($soal['pertanyaan']); ?></p>

                        <?php if ($soal['jenis_soal'] == 'pilihan_ganda'): ?>
                            <div class="d-flex flex-column gap-2">
                                <div class="pilihan <?php echo $soal['jawaban_benar'] == 'A' ? 'correct' : ''; ?>">
                                    A. <?php echo htmlspecialchars($soal['jawaban_a']); ?>
                                </div>
                                <div class="pilihan <?php echo $soal['jawaban_benar'] == 'B' ? 'correct' : ''; ?>">
                                    B. <?php echo htmlspecialchars($soal['jawaban_b']); ?>
                                </div>
                                <div class="pilihan <?php echo $soal['jawaban_benar'] == 'C' ? 'correct' : ''; ?>">
                                    C. <?php echo htmlspecialchars($soal['jawaban_c']); ?>
                                </div>
                                <div class="pilihan <?php echo $soal['jawaban_benar'] == 'D' ? 'correct' : ''; ?>">
                                    D. <?php echo htmlspecialchars($soal['jawaban_d']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Modal Pilih Tipe Soal -->
        <div class="modal fade" id="pilihTipeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Tipe Soal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body d-flex gap-2">
                        <button class="btn color-web text-white flex-grow-1" onclick="pilihTipeSoal('pilihan_ganda')">
                            Pilihan Ganda
                        </button>
                        <button class="btn btn-secondary flex-grow-1" onclick="pilihTipeSoal('uraian')">
                            Uraian
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form Soal -->
        <div class="modal fade" id="formSoalModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Isi modal akan diload melalui JavaScript -->
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <div class="modal fade" id="uploadSoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Import Soal Word</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-stars fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Bantu SAGA memahami soal Anda</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Direkomendasikan untuk mengikuti template soal dan jawaban yang telah di sediakan, tindakan berikut mempermudah proses analisis SAGA dalam dokumen Anda</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="small text-muted mb-2">Unduh template soal</p>
                        <div class="d-flex gap-2 mb-2">
                            <a href="templates/template_soal.docx" download class="btn border flex-fill" style="border-radius: 12px;">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <i class="bi bi-file-earmark-word"></i>
                                    <span>Template Soal</span>
                                </div>
                            </a>
                            <a href="templates/template_jawaban.docx" download class="btn border flex-fill" style="border-radius: 12px;">
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <span>Template Jawaban</span>
                                </div>
                            </a>
                        </div>
                    </div>


                    <form id="formUploadSoal" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="fileSoal" class="form-label fw-medium mb-2" style="font-size: 15px;">Upload File Soal (.docx)</label>
                            <input type="file" class="form-control" id="fileSoal" name="fileSoal" accept=".docx" required
                                style="border-radius: 12px;font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                        </div>
                        <div class="mb-4">
                            <label for="fileJawaban" class="form-label fw-medium mb-2" style="font-size: 15px;">Upload File Kunci Jawaban (.docx)</label>
                            <input type="file" class="form-control" id="fileJawaban" name="fileJawaban" accept=".docx" required
                                style="border-radius: 12px;font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                        </div>
                        <input type="hidden" name="ujian_id" value="<?php echo $ujian_id; ?>">
                    </form>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light flex-fill border" style="border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn color-web text-white flex-fill" style="border-radius: 12px; font-size: 15px;" id="uploadButton">
                        <span>Upload</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal AI Soal yang sudah ada -->
    <!-- Tambahkan modal untuk Buat Soal dengan AI dengan iOS-style UI -->
    <div class="modal fade" id="aiSoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Buat Soal dengan SAGA</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Tetap bijak menggunakan SAGA</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Dimohon untuk tetap bijak dalam penggunakan SAGA AI, semakin Anda banyak meminta soal maka semakin banyak token atau penggunaan SAGA AI mengingat sumber data terbatas</p>
                            </div>
                        </div>
                    </div>

                    <form id="formAiSoal">
                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Jumlah Soal</label>
                            <input type="number" class="form-control" name="jumlah_soal" min="1" max="10" value="1"
                                onchange="this.value = this.value > 10 ? 10 : this.value"
                                style="border-radius: 12px; height: 50px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                            <div class="form-text mt-2" style="font-size: 12px; color: #8e8e93;">
                                Maksimal input 10 soal
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Kesulitan</label>
                            <select class="form-select" name="kesulitan"
                                style="border-radius: 12px; height: 50px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                                <option value="mudah">Mudah</option>
                                <option value="sedang">Sedang</option>
                                <option value="sulit">Sulit</option>
                                <option value="sangat_sulit">Sangat Sulit</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Tipe Soal</label>
                            <select class="form-select" name="tipe_soal"
                                style="border-radius: 12px; height: 50px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                                <option value="pilihan_ganda">Pilihan Ganda</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Preview Baris Perintah</label>
                            <!-- <div class="alert border bg-light" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <div>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;"><span class="fw-bold text-black">PERHATIAN</span> Baris perintah di bawah merupakan promt SAGA AI dalam memproses soal Anda. </p>
                                    </div>
                                </div>
                            </div> -->
                            <div class="position-relative">
                                <textarea id="promptPreview" name="custom_prompt" class="form-control" rows="6"
                                    style="border-radius: 12px; font-size: 14px; border: 1px solid #e4e4e4; background-color: #f5f5f5; font-family: monospace;"></textarea>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light flex-fill border" style="border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn color-web text-white bi bi-stars flex-fill" style="border-radius: 12px; font-size: 15px;" onclick="generateMultipleSoal()">
                        <span>Mulai Keajaiban</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- script untuk menampilkan promt sebelum buat soal -->
    <script>
        // Script untuk menampilkan preview prompt
        document.addEventListener('DOMContentLoaded', function() {
            const formAiSoal = document.getElementById('formAiSoal');
            const promptPreview = document.getElementById('promptPreview');

            // Fungsi untuk mengupdate preview prompt
            function updatePromptPreview() {
                const jumlahSoal = formAiSoal.querySelector('[name="jumlah_soal"]').value;
                const kesulitan = formAiSoal.querySelector('[name="kesulitan"]').value;
                const tipeSoal = formAiSoal.querySelector('[name="tipe_soal"]').value;
                const mataPelajaran = "<?php echo addslashes($ujian['mata_pelajaran']); ?>";
                const tingkat = "<?php echo $ujian['tingkat']; ?>";

                // Ambil materi ujian (jika ada)
                const materiList = <?php
                                    $materi_json = "[]";
                                    if (!empty($ujian['materi'])) {
                                        $materi_json = $ujian['materi'];
                                    }
                                    echo $materi_json;
                                    ?>;

                // Buat prompt dasar
                let prompt = `Buatkan ${jumlahSoal} soal ` +
                    (tipeSoal == 'pilihan_ganda' ? "pilihan ganda" : "uraian") +
                    ` untuk mata pelajaran ${mataPelajaran} kelas ${tingkat} dengan tingkat kesulitan ` +
                    kesulitan.toUpperCase() + `.\n\n`;

                // Tambahkan panduan tingkat kesulitan
                const difficultyGuidelines = {
                    'mudah': "- Gunakan konsep dasar dan pengetahuan faktual\n" +
                        "- Soal bersifat ingatan dan pemahaman sederhana\n" +
                        "- Jawaban mudah ditemukan dalam materi dasar",
                    'sedang': "- Gabungkan beberapa konsep dasar\n" +
                        "- Butuh analisis sederhana\n" +
                        "- Memerlukan pemahaman konseptual",
                    'sulit': "- Gunakan analisis kompleks\n" +
                        "- Kombinasikan multiple konsep\n" +
                        "- Membutuhkan pemecahan masalah\n" +
                        "- Aplikasi konsep dalam konteks baru",
                    'sangat_sulit': "- Membutuhkan analisis sangat mendalam\n" +
                        "- Evaluasi dan sintesis konsep\n" +
                        "- Penalaran tingkat tinggi\n" +
                        "- Pemecahan masalah kompleks"
                };

                prompt += `Panduan tingkat kesulitan:\n${difficultyGuidelines[kesulitan]}\n\n`;

                // Tambahkan daftar materi jika ada
                if (materiList && materiList.length > 0) {
                    prompt += "Materi yang harus dicakup:\n";
                    materiList.forEach(materi => {
                        prompt += `- ${materi}\n`;
                    });
                    prompt += "\n";
                }

                // Tambahkan format berdasarkan tipe soal
                if (tipeSoal == 'pilihan_ganda') {
                    prompt += `format yang diinginkan:
1. setiap soal harus memiliki 4 pilihan jawaban (A, B, C, D)
2. sertakan kunci jawaban untuk setiap soal
3. jawaban harus bervariasi (tidak selalu A atau B)
4. tingkat kesulitan soal harus bervariasi
5. soal harus sesuai dengan tingkat pemahaman siswa kelas ${tingkat}

--- WAJIB IKUTI FORMAT INI DENGAN SANGAT TEPAT: ---

Soal: [pertanyaan tanpa nomor]
A. [jawaban]
B. [jawaban] 
C. [jawaban]
D. [jawaban]
Jawaban: [A/B/C/D]
---

Contoh:
Soal: Berapakah hasil dari 5 x 5?
A. 15
B. 20
C. 25
D. 30
Jawaban: C
---

ATURAN PENTING:
- JANGAN GUNAKAN PENOMORAN PADA SOAL (1., 2., dst)
- DILARANG menggunakan format lain (tidak boleh ada '**', 'Soal 1:', dll)
- WAJIB gunakan separator '---' di antara soal
- WAJIB mulai tiap soal dengan kata 'Soal:' (tanpa nomor)
- DILARANG memberikan komentar atau teks tambahan`;
                } else {
                    prompt += `Format yang diinginkan:
1. Soal harus dalam bentuk uraian yang menguji pemahaman
2. Tingkat kesulitan soal harus bervariasi
3. Soal harus sesuai dengan tingkat pemahaman siswa kelas ${tingkat}

Berikan output dengan format TEPAT seperti ini untuk setiap soal:
1. [tuliskan pertanyaan pertama]
2. [tuliskan pertanyaan kedua]
(dan seterusnya)

Contoh format output yang diharapkan:
1. Jelaskan proses fotosintesis pada tumbuhan dan sebutkan faktor-faktor yang mempengaruhinya!
2. Mengapa mata pelajaran matematika penting dalam kehidupan sehari-hari? Berikan contoh penerapannya!`;
                }

                // Tampilkan prompt di textarea
                promptPreview.value = prompt;
            }

            // Update preview saat halaman dimuat
            if (formAiSoal && promptPreview) {
                updatePromptPreview();

                // Update preview saat nilai form berubah
                const formInputs = formAiSoal.querySelectorAll('input, select');
                formInputs.forEach(input => {
                    input.addEventListener('change', updatePromptPreview);
                });
            }
        });
    </script>
    <!-- Tambahkan script untuk mengatur pesan yang berubah -->
    <!-- <script>
        // Array pesan SAGA
        const messages = [
            "Halo <?php echo htmlspecialchars($guru['namaLengkap']); ?>, saya SAGA, kesulitan membuat soal?",
            "Saya bisa buat soal dalam hitungan detik lo!",
            "Jadi bagaimana? Mau coba buat soal dengan bantuan saya?"
        ];

        let currentMessageIndex = 0;
        let sagaBubble = null;
        let isMouseOver = false;
        let messageShown = false;

        document.addEventListener('DOMContentLoaded', function() {
            const sagaCharacter = document.getElementById('sagaCharacter');
            sagaBubble = sagaCharacter.querySelector('.saga-bubble');
            const messageElement = document.getElementById('sagaMessage');

            // Fungsi untuk menampilkan pesan secara berurutan
            function showNextMessage() {
                if (currentMessageIndex < messages.length) {
                    sagaBubble.classList.remove('d-none');
                    fadeTransition(messageElement, messages[currentMessageIndex]);

                    // Jika masih ada pesan berikutnya, atur timer
                    if (currentMessageIndex < messages.length - 1) {
                        setTimeout(() => {
                            currentMessageIndex++;
                            showNextMessage();
                        }, 4000);
                    }
                }
            }

            // Mulai menampilkan pesan jika belum ditampilkan
            if (!messageShown) {
                showNextMessage();
                messageShown = true;
            }

            // Event listener untuk hover
            sagaCharacter.addEventListener('mouseenter', () => {
                isMouseOver = true;
                sagaBubble.classList.remove('d-none');
                // Reset pesan ke awal saat hover
                currentMessageIndex = 0;
                showNextMessage();
            });

            sagaCharacter.addEventListener('mouseleave', () => {
                isMouseOver = false;
                setTimeout(() => {
                    if (!isMouseOver) {
                        sagaBubble.classList.add('d-none');
                    }
                }, 300);
            });

            // Buka modal AI saat diklik
            sagaCharacter.addEventListener('click', () => {
                const aiModal = new bootstrap.Modal(document.getElementById('aiSoalModal'));
                aiModal.show();
            });
        });

        // Fungsi untuk transisi fade antar pesan
        function fadeTransition(element, newText) {
            element.style.opacity = '0';

            setTimeout(() => {
                element.textContent = newText;
                element.style.transition = 'opacity 0.5s ease-in-out';
                element.style.opacity = '1';
            }, 500);
        }
    </script> -->


    <!-- overlay untuk generate soal -->
    <div id="generateOverlay" class="generate-overlay">
        <div class="generate-message">
            <img src="assets/ai.gif" style="width:100px;" alt="Deskripsi gambar">
            <h5 class="mb-1">Sedang Menciptakan Keajaiban</h5>
            <p class="mb-0" style="font-size: 12px;">Meramu soal sesuai permintaan Anda</p>
        </div>
    </div>

    <style>

    </style>
    <!-- script untuk generate multiple soal -->
    <script>
        async function generateMultipleSoal() {
            const form = document.getElementById('formAiSoal');
            const formData = new FormData(form);
            const button = form.closest('.modal').querySelector('.modal-footer .btn.color-web');
            const spinner = button.querySelector('.spinner-border');
            const buttonText = button.querySelector('span');
            const overlay = document.getElementById('generateOverlay');
            const customPrompt = document.getElementById('promptPreview').value;

            try {
                // Update button state
                button.disabled = true;
                spinner.classList.remove('d-none');
                buttonText.textContent = 'Generating...';

                // overlay muncul
                overlay.classList.add('fade-in');
                overlay.style.display = 'flex';

                const response = await fetch('generate_multiple_soal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        jumlah_soal: formData.get('jumlah_soal'),
                        tipe_soal: formData.get('tipe_soal'),
                        kesulitan: formData.get('kesulitan'),
                        ujian_id: <?php echo $ujian_id; ?>,
                        mata_pelajaran: "<?php echo addslashes($ujian['mata_pelajaran']); ?>",
                        tingkat: "<?php echo $ujian['tingkat']; ?>",
                        custom_prompt: customPrompt // Tambahkan prompt kustom
                    }).toString()
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Response from server:', result);

                if (result.status === 'success') {
                    console.log('Soal berhasil di-generate:', result.data);
                    location.reload();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error:', error);

                // Sembunyikan overlay jika terjadi error
                overlay.classList.remove('fade-in');
                overlay.classList.add('fade-out');
                setTimeout(() => {
                    overlay.style.display = 'none';
                    overlay.classList.remove('fade-out');
                }, 500);

                alert('Gagal generate soal: ' + error.message);
            } finally {
                // Reset button state
                button.disabled = false;
                spinner.classList.add('d-none');
                buttonText.textContent = 'Generate Soal';

                // Sembunyikan overlay dengan animasi fade out
                overlay.classList.remove('fade-in');
                overlay.classList.add('fade-out');
                setTimeout(() => {
                    overlay.style.display = 'none';
                    overlay.classList.remove('fade-out');
                }, 500);

                // Tutup modal
                const modal = document.getElementById('aiSoalModal');
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
            }
        }
    </script>

    <!-- script upload soal pake word -->
    <script>
        // Separate script section
        document.getElementById('uploadButton').addEventListener('click', function(e) {
            const form = document.getElementById('formUploadSoal');
            const formData = new FormData(form);
            formData.append('ujian_id', '<?php echo $ujian_id; ?>');

            // Log form data
            for (let pair of formData.entries()) {
                console.log(pair[0], pair[1]);
            }


            const button = this;
            const spinner = button.querySelector('.spinner-border');
            const buttonText = button.querySelector('span');
            const overlay = document.getElementById('generateOverlay');

            button.disabled = true;
            spinner.classList.remove('d-none');
            buttonText.textContent = 'Uploading...';
            overlay.style.display = 'flex';

            fetch('process_word.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        location.reload();
                    } else {
                        console.error('Upload error:', result);
                        throw new Error(result.message || 'Gagal mengupload file');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengupload file: ' + error.message);
                })
                .finally(() => {
                    button.disabled = false;
                    spinner.classList.add('d-none');
                    buttonText.textContent = 'Upload';
                    overlay.style.display = 'none';

                    const modal = document.getElementById('uploadSoalModal');
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                });
        });
    </script>

    <script>
        let currentTipeSoal = '';

        function pilihTipeSoal(tipe) {
            currentTipeSoal = tipe;
            loadFormSoal();
            $('#pilihTipeModal').modal('hide');
            $('#formSoalModal').modal('show');
        }

        // Tambahkan function untuk edit soal
        async function editSoal(id) {
            try {
                // Ambil data soal
                const response = await fetch(`get_soal.php?id=${id}`);
                const data = await response.json();

                if (data.status === 'success') {
                    currentTipeSoal = data.soal.jenis_soal;
                    loadFormSoal(true); // Parameter true menandakan ini mode edit

                    // Isi form dengan data yang ada
                    const form = document.getElementById('formSoal');
                    form.querySelector('[name="pertanyaan"]').value = data.soal.pertanyaan;
                    form.querySelector('[name="soal_id"]').value = id; // Hidden input untuk ID soal

                    if (data.soal.jenis_soal === 'pilihan_ganda') {
                        form.querySelector('[name="jawaban_a"]').value = data.soal.jawaban_a;
                        form.querySelector('[name="jawaban_b"]').value = data.soal.jawaban_b;
                        form.querySelector('[name="jawaban_c"]').value = data.soal.jawaban_c;
                        form.querySelector('[name="jawaban_d"]').value = data.soal.jawaban_d;
                        form.querySelector('[name="jawaban_benar"]').value = data.soal.jawaban_benar;


                        // Handle existing image
                        if (data.soal.gambar_soal) {
                            const existingImageContainer = form.querySelector('#existing_image_container');
                            const existingImage = form.querySelector('#existing_image');
                            existingImage.src = data.soal.gambar_soal;
                            existingImageContainer.classList.remove('d-none');
                        }
                    }

                    // Tampilkan modal
                    const formModal = new bootstrap.Modal(document.querySelector('#formSoalModal'));
                    formModal.show();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                alert('Gagal mengambil data soal: ' + error.message);
            }
        }


        function loadFormSoal(isEdit = false) {
            const modalContent = document.querySelector('#formSoalModal .modal-content');
            modalContent.style.position = 'relative'; // Tambahkan ini

            // Tambahkan overlay di awal konten modal
            const overlayHtml = `
        <div class="generate-overlay">
            <div class="generate-message">
            <img src="assets/ai.gif" style="width:100px;" alt="Deskripsi gambar">
                <h5 class="mb-1">Sedang Membuat Soal</h5>
                <p class="mb-0">Mohon tunggu sebentar</p>
            </div>
        </div>
    `;

            if (currentTipeSoal === 'pilihan_ganda') {
                modalContent.innerHTML = overlayHtml + `
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">${isEdit ? 'Edit' : 'Buat'} Soal Pilihan Ganda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="formSoal">
                    <input type="hidden" name="soal_id" value="">
                   
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Pertanyaan</label>
                        <div class="d-flex gap-2 position-relative">
                            <textarea class="form-control" name="pertanyaan" rows="3" required style="border-radius: 12px; resize: none;"></textarea>                               
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Gambar Soal (Opsional)</label>
                        <div class="d-flex flex-column gap-3">
                            <div class="upload-container">
                                <input type="file" class="form-control" name="gambar_soal" accept="image/*" onchange="previewImage(this)" 
                                    style="border-radius: 12px;">
                            </div>
                            
                            <div id="preview_container" class="d-none animate__animated border">
                                <div class="position-relative d-inline-block p-3">
                                    <img id="image_preview" class="img-fluid shadow-sm" 
                                        style="max-height: 200px; border-radius: 12px; object-fit: cover;">
                                    <button type="button" class="btn-close-custom" onclick="removeImage()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="existing_image_container" class="d-none animate__animated">
                                <div class="position-relative d-inline-block border rounded-2 p-3">
                                    <img id="existing_image" class="img-fluid shadow-sm" 
                                        style="max-height: 100px; object-fit: cover;">
                                    <button type="button" class="btn-close-custom" onclick="deleteExistingImage()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                    .btn-close-custom {
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        width: 24px;
                        height: 24px;
                        border-radius: 12px;
                        background: rgba(0, 0, 0, 0.5);
                        border: none;
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s ease;
                        backdrop-filter: blur(4px);
                    }

                    .btn-close-custom:hover {
                        background: rgba(0, 0, 0, 0.7);
                        transform: scale(1.1);
                    }

                    .btn-close-custom i {
                        font-size: 16px;
                    }

                    .animate__animated {
                        animation-duration: 0.3s;
                    }

                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    @keyframes fadeOutDown {
                        from {
                            opacity: 1;
                            transform: translateY(0);
                        }
                        to {
                            opacity: 0;
                            transform: translateY(10px);
                        }
                    }

                    .fadeInUp {
                        animation: fadeInUp 0.3s ease-out;
                    }

                    .fadeOutDown {
                        animation: fadeOutDown 0.3s ease-out;
                    }
                    </style>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Pilihan Jawaban</label>
                        <div class="row row-cols-1 row-cols-md-2 g-3">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">A</span>
                                    <input type="text" class="form-control" name="jawaban_a" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">B</span>
                                    <input type="text" class="form-control" name="jawaban_b" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">C</span>
                                    <input type="text" class="form-control" name="jawaban_c" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">D</span>
                                    <input type="text" class="form-control" name="jawaban_d" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Jawaban Benar</label>
                        <select class="form-select" name="jawaban_benar" required style="border-radius: 12px;">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer btn-group bg-light border-0">
                <button type="button" class="btn border" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                <button type="button" class="btn color-web text-white" onclick="simpanSoal()" style="border-radius: 12px;">Simpan</button>
            </div>
        `;
            } else {
                modalContent.innerHTML = overlayHtml + `
        <div class="modal-header bg-white border-0">
            <h5 class="modal-title fw-bold">${isEdit ? 'Edit' : 'Buat'} Soal Uraian</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
            <form id="formSoal">
                <input type="hidden" name="soal_id" value="">
                <div class="mb-4">
                    <label class="form-label small fw-bold">Pertanyaan</label>
                    <div class="d-flex gap-2">
                        <textarea class="form-control" name="pertanyaan" rows="3" required style="border-radius: 12px; resize: none;"></textarea>
                        <button type="button" class="btn color-web text-white ai-button" onclick="generateSoal('uraian')" style="border-radius: 12px; height: 45px;">
                            <i class="bi bi-stars"></i>
                            <div class="ai-loader"></div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer bg-white border-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn color-web text-white" onclick="simpanSoal">Simpan</button>
        </div>
    `;
            }

            // Tambahkan style untuk modal
            modalContent.style.borderRadius = '16px';
            modalContent.style.overflow = 'hidden';

            // Tambahkan style ini setelah modal content
            const style = document.createElement('style');
            style.textContent = `
        .dot-pulse {
            position: relative;
            left: -9999px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: rgb(218, 119, 86);
            color: rgb(218, 119, 86);
            box-shadow: 9999px 0 0 -5px;
            animation: dot-pulse 1.5s infinite linear;
            animation-delay: 0.25s;
        }
        .dot-pulse::before, .dot-pulse::after {
            content: '';
            display: inline-block;
            position: absolute;
            top: 0;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: rgb(218, 119, 86);
            color: rgb(218, 119, 86);
        }
        .dot-pulse::before {
            box-shadow: 9984px 0 0 -5px;
            animation: dot-pulse-before 1.5s infinite linear;
            animation-delay: 0s;
        }
        .dot-pulse::after {
            box-shadow: 10014px 0 0 -5px;
            animation: dot-pulse-after 1.5s infinite linear;
            animation-delay: 0.5s;
        }
        @keyframes dot-pulse-before {
            0% { box-shadow: 9984px 0 0 -5px; }
            30% { box-shadow: 9984px 0 0 2px; }
            60%, 100% { box-shadow: 9984px 0 0 -5px; }
        }
        @keyframes dot-pulse {
            0% { box-shadow: 9999px 0 0 -5px; }
            30% { box-shadow: 9999px 0 0 2px; }
            60%, 100% { box-shadow: 9999px 0 0 -5px; }
        }
        @keyframes dot-pulse-after {
            0% { box-shadow: 10014px 0 0 -5px; }
            30% { box-shadow: 10014px 0 0 2px; }
            60%, 100% { box-shadow: 10014px 0 0 -5px; }
        }
    `;
            document.head.appendChild(style);
        }

        // Add after existing loadFormSoal() code:
        function previewImage(input) {
            const preview = document.getElementById('image_preview');
            const container = document.getElementById('preview_container');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            const fileInput = document.querySelector('input[name="gambar_soal"]');
            const preview = document.getElementById('image_preview');
            const container = document.getElementById('preview_container');

            fileInput.value = '';
            preview.src = '';
            container.classList.add('d-none');
        }

        async function deleteExistingImage() {
            const form = document.getElementById('formSoal');
            const soalId = form.querySelector('[name="soal_id"]').value;

            try {
                const response = await fetch('hapus_gambar_soal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        soal_id: soalId
                    })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    // Hide the existing image container
                    const container = document.getElementById('existing_image_container');
                    container.classList.add('d-none');
                    // Clear the image
                    document.getElementById('existing_image').src = '';
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert('Gagal menghapus gambar: ' + error.message);
            }
        }



        // Modifikasi fungsi generateSoal untuk menambahkan animasi
        async function generateSoal(jenis) {
            try {
                // Show loading
                const button = document.querySelector('.ai-button');
                const icon = button.querySelector('.bi-stars');
                const loader = button.querySelector('.ai-loader');
                const overlay = document.getElementById('generateOverlay');

                button.disabled = true;
                icon.style.display = 'none';
                loader.classList.add('show');

                overlay.classList.add('fade-in');
                overlay.style.display = 'flex';


                const response = await fetch('generate_soal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        jenis_soal: jenis,
                        ujian_id: <?php echo $ujian_id; ?>,
                        mata_pelajaran: "<?php echo addslashes($ujian['mata_pelajaran']); ?>",
                        tingkat: "<?php echo $ujian['tingkat']; ?>"
                    }).toString()
                });

                const result = await response.json();

                if (result.status === 'success') {
                    const form = document.getElementById('formSoal');
                    form.querySelector('[name="pertanyaan"]').value = result.data.pertanyaan;

                    if (jenis === 'pilihan_ganda') {
                        form.querySelector('[name="jawaban_a"]').value = result.data.jawaban_a;
                        form.querySelector('[name="jawaban_b"]').value = result.data.jawaban_b;
                        form.querySelector('[name="jawaban_c"]').value = result.data.jawaban_c;
                        form.querySelector('[name="jawaban_d"]').value = result.data.jawaban_d;
                        form.querySelector('[name="jawaban_benar"]').value = result.data.jawaban_benar.toUpperCase();
                    }

                    // Sembunyikan overlay dengan animasi fade out
                    overlay.classList.remove('fade-in');
                    overlay.classList.add('fade-out');
                    setTimeout(() => {
                        overlay.style.display = 'none';
                        overlay.classList.remove('fade-out');
                    }, 500);


                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert('Gagal generate soal: ' + error.message);
            } finally {
                // Hide loading
                const button = document.querySelector('.ai-button');
                const icon = button.querySelector('.bi-stars');
                const loader = button.querySelector('.ai-loader');



                loader.classList.remove('show');
                icon.style.display = 'block';
                button.disabled = false;
            }
        }


        // Modifikasi fungsi simpanSoal untuk mendukung edit
        async function simpanSoal() {
            const form = document.getElementById('formSoal');
            const formData = new FormData(form);
            formData.append('jenis_soal', currentTipeSoal);
            formData.append('ujian_id', <?php echo $ujian_id; ?>);

            const soalId = form.querySelector('[name="soal_id"]').value;
            const url = soalId ? 'update_soal.php' : 'simpan_soal.php';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    location.reload(); // Refresh halaman untuk menampilkan perubahan
                } else {
                    alert('Gagal menyimpan soal: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
        }


        async function hapusSoal(id) {
            // Create modal HTML dynamically
            const modalHtml = `
                <div class="modal fade" id="hapusSoalModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px;">
                            <div class="modal-body text-center p-4">
                                <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:rgb(218, 119, 86);"></i>
                                <h5 class="mt-3 fw-bold">Hapus Soal</h5>
                                <p class="mb-4">Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.</p>
                                <div class="d-flex gap-2 btn-group">
                                    <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                    <button type="button" class="btn btn-danger px-4" id="konfirmasiHapus" style="border-radius: 12px;">
                                        <span>Hapus</span>
                                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to document
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Get modal element
            const modalElement = document.getElementById('hapusSoalModal');
            const modal = new bootstrap.Modal(modalElement);

            // Show modal
            modal.show();

            // Handle delete confirmation
            document.getElementById('konfirmasiHapus').addEventListener('click', async () => {
                const button = document.getElementById('konfirmasiHapus');
                const spinner = button.querySelector('.spinner-border');
                const buttonText = button.querySelector('span');

                try {
                    // Update button state
                    button.disabled = true;
                    spinner.classList.remove('d-none');
                    buttonText.textContent = 'Menghapus...';

                    const response = await fetch('hapus_soal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            soal_id: id
                        })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        location.reload();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    alert('Gagal menghapus soal: ' + error.message);
                } finally {
                    // Reset button state
                    button.disabled = false;
                    spinner.classList.add('d-none');
                    buttonText.textContent = 'Hapus';

                    // Remove modal when done
                    modal.hide();
                    modalElement.addEventListener('hidden.bs.modal', () => {
                        modalElement.remove();
                    });
                }
            });

            // Remove modal from DOM when hidden
            modalElement.addEventListener('hidden.bs.modal', () => {
                modalElement.remove();
            });
        }
    </script>
</body>

</html>