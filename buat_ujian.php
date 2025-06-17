<?php
include 'includes/session_config.php';
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

$totalSteps = 4; // Total langkah dalam pembuatan ujian

$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Ambil daftar kelas
$query_kelas = "SELECT * FROM kelas WHERE guru_id = '$userid'";
$result_kelas = mysqli_query($koneksi, $query_kelas);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <title>Buat Ujian - SMAGAEdu</title>
    <style>
        body {
            font-family: merriweather;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
        }

        .color-web:hover {
            background-color: rgb(218, 119, 86);
        }

        .btn {
            transition: background-color 0.3s ease;
            border: 0;
            border-radius: 5px;
        }


        .menu-samping {
            position: fixed;
            width: 13rem;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .menu-samping {
                display: none;
            }

            .col-utama {
                margin-left: 0 !important;
            }
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

        .card-container {
            position: relative;
            padding-bottom: 70px;
            /* Ruang untuk tombol navigasi */
        }

        .question-card {
            min-height: 400px;
            /* Memastikan semua card memiliki tinggi minimum */
        }

        .navigation-buttons {
            position: fixed;
            bottom: 0;
            left: 13rem;
            /* Menyesuaikan dengan lebar sidebar */
            right: 0;
            background-color: white;
            padding: 1rem;
            z-index: 900;
            /* Di bawah sidebar z-index */
            display: flex;
            justify-content: space-between;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .navigation-buttons .btn {
            display: inline-block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Pastikan padding bawah pada container utama */
        .col-md-6:first-child {
            padding-bottom: 80px;
        }

        /* Khusus halaman ini, agar tidak mengganggu halaman lain */
        .col-utama {
            padding-bottom: 70px !important;
        }

        @media (max-width: 768px) {
            .col-utama {
                padding-bottom: 80px !important;
            }

            .navigation-buttons {
                left: 0;
                bottom: 60px;
                /* Memberikan ruang untuk navbar mobile di bawah */
                padding: 0.75rem;
            }
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

    <!-- Validation Modal -->
    <div class="modal fade" id="validationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <h5 class="mt-3 fw-bold">Data Belum Lengkap</h5>
                    <p class="mb-4" id="validationMessage">Mohon lengkapi semua input yang diperlukan.</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn color-web flex-fill text-white px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Ok, Saya Mengerti</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col p-4 col-utama">
        <!-- <h3 class="mb-4 fw-bold">Buat Ujian Baru</h3> -->
        <div class="row">
            <!-- Form Section (Left) -->
            <div class="col-md-6">
                <form action="proses_buat_ujian.php" method="POST">
                    <div class="card-container">
                        <!-- Card 1 -->
                        <div class="question-card" data-step="1">
                            <div class="card kartu-buat-ujian border-0 p-4 mb-3">
                                <!-- nama judul -->
                                <div class="gap-3 mb-3">
                                    <div class="d-inline-flex mb-3 px-3 py-2 rounded-4 border border-opacity-75 icon-ujian ">
                                        <img src="assets/judul-ujian.gif" alt="Judul Ujian" style="width: 4rem; height: 4rem;">

                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold" style="font-size: 2rem;">Berikan Identitas <br> Ujian Anda</h5>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Apa judul ujian Anda?</label>
                                    <input type="text" class="form-control border" name="judul"
                                        onchange="updatePreview(this)" data-preview="judul-preview" placeholder="Cth : Ujian Harian Bahasa Indonesia" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label p-0 m-0">Apa deskripsi ujian Anda?</label>
                                    <p class="text-muted p-0 m-0 mb-2" style="font-size:12px;">TIDAK WAJIB</p>
                                    <textarea class="form-control border" name="deskripsi" rows="3"
                                        onchange="updatePreview(this)" data-preview="deskripsi-preview" placeholder="Cth : Evaluasi pemahaman tentang Teks Eksposisi dan Struktur Paragraf, Bab 1-3"></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="question-card d-none" data-step="2">
                            <div class="card kartu-buat-ujian border-0 p-4 mb-3">
                                <!-- nama judul -->
                                <div class="gap-3 mb-3">
                                    <div class="d-inline-flex mb-3 px-3 py-2 rounded-4 border border-opacity-75 icon-ujian">
                                        <img src="assets/kelas-ujian.gif" alt="Judul Ujian" style="width: 4rem; height: 4rem;">
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold" style="font-size: 2rem;">Kelas apa yang <br> ingin Anda ujikan?</h5>
                                    </div>

                                    <div class="alert border bg-light" style="border-radius: 15px;">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                            <div>
                                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Pastikan Anda Telah Membuat Kelas</p>
                                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Anda hanya dapat mengujikan kelas yang telah Anda buat</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <label for="kelas" class="form-label fw-semibold">Pilih Kelas</label>
                                <select class="form-select border" id="kelas" name="kelas_id" required onchange="updatePreviewSelect(this)">
                                    <option value="">Pilih kelas</option>
                                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)) { ?>
                                        <option value="<?php echo $kelas['id']; ?>">
                                            <?php echo $kelas['tingkat'] . ' - ' . $kelas['mata_pelajaran']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <!-- // Tambahkan di dalam form -->
                                <input type="hidden" id="background_image" name="background_image" value="">
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="question-card  d-none" data-step="3">
                            <div class="card rounded-3 border-0">
                                <div class="card-body  kartu-buat-ujian p-4">
                                    <div class="gap-3 mb-3">
                                        <div>
                                            <div class="d-inline-flex mb-3 px-3 py-2 rounded-4 border border-opacity-75 icon-ujian">
                                                <img src="assets/ai-ujian.gif" alt="Judul Ujian" style="width: 4rem; height: 4rem;">
                                            </div>

                                            <h5 class="card-title mb-1 fw-bold" style="font-size:2rem">Ah, Membuat Soal Tidak Pernah Semudah Ini</h5>
                                            <div class="alert border bg-light mb-4" style="border-radius: 15px;">
                                                <div class="d-flex">
                                                    <i class="bi bi-stars fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                                    <div>
                                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Semakian Banyak Data Semakin Akurat</p>
                                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Semakin lengkap materi yang Anda berikan, semakin akurat SAGA AI dalam menyusun soal sesuai dengan tujuan pembelajaran Anda.</p>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div id="materi-container">
                                        <div class="materi-item mb-2">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">1</span>
                                                <input type="text" class="form-control" name="materi[]"
                                                    placeholder="Masukkan materi ujian">
                                                <button type="button" class="btn btn-outline-danger border" onclick="hapusMateri(this)">
                                                    <span class="bi bi-trash"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-outline-secondary w-100 mt-3" onclick="tambahMateri()">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Tambah Materi</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="question-card  kartu-buat-ujian d-none" data-step="4">
                            <div class="row g-3 mb-4 p-4">
                                <div class="gap-3 mb-3">
                                    <div class="d-inline-flex mb-3 px-3 py-2 rounded-4 border border-opacity-75 icon-ujian">
                                        <img src="assets/waktu-ujian.gif" alt="Judul Ujian" style="width: 4rem; height: 4rem;">
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold" style="font-size: 2rem;">Tentukan kapan siswa mengerjakan ujian Anda</h5>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12">
                                    <label for="tanggal_mulai" class="form-label">Ujian saya mulai dikerjakan pada</label>
                                    <input type="datetime-local" class="form-control shadow-sm"
                                        id="tanggal_mulai" name="tanggal_mulai" required
                                        onchange="calculateDuration()">
                                </div>
                                <div class="col-12 col-md-12">
                                    <label for="tanggal_selesai" class="form-label">Ujian saya selesai dikerjakan pada</label>
                                    <input type="datetime-local" class="form-control shadow-sm"
                                        id="tanggal_selesai" name="tanggal_selesai" required
                                        onchange="calculateDuration()">
                                </div>
                            </div>
                        </div>



                        <!-- Navigation Buttons -->
                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-secondary rounded" onclick="previousStep()" data-bs-toggle="popoer" data-bs-placement="hover" data-bs-trigger="hover focus" data-bs-content="Kembali ke langkah sebelumnya">
                                <i class="bi bi-chevron-left" style="font-size: 16px;"></i>
                            </button>
                            <div class="d-flex gap-2">
                                <div class="d-none d-md-flex gap-2 align-items-center animate_animated animate_fadeIn">
                                    <span class="ti ti-info-circle text-muted"></span>
                                    <span class="text-muted" style="font-size: 12px;">Anda dapat mengubah Identitas ini nanti</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 animate_animated animate_fadeIn">
                                    <span class="d-none d-md-block me-1">|</span>
                                    <span class="text-muted" style="font-size: 12px;">Langkah <span id="current-step">1</span> dari <span id="total-steps"><?php echo $totalSteps; ?></span></span>
                                </div>
                            </div>

                            <button type="button" class="btn color-web rounded text-white" onclick="nextStep()" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover focus" data-bs-content="Lanjut ke langkah berikutnya">
                                <i class="bi bi-chevron-right" style="font-size: 16px;"></i>
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Preview Section (Right) -->
            <div class="col-md-6 d-none d-md-block">
                <div class="card border-0 p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-eye-fill text-muted"></i>
                        <h5 class="mb-0">Pratinjau Ujian</h5>
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
                            background-image: url('assets/bg.jpg');
                        }

                        .profile-circle {
                            width: 70px;
                            height: 70px;
                            border-radius: 50%;
                            border: 3px solid #fff;
                            position: absolute;
                            bottom: -35px;
                            right: 20px;
                            object-fit: cover;
                            background: #fff;
                        }

                        .class-content {
                            padding: 20px;
                        }

                        .class-title {
                            font-size: 18px;
                            font-weight: 600;
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
                    </style>

                    <div class="class-card border">
                        <div class="class-banner">
                            <img src="<?php echo ($is_guru || $is_admin) ?
                                            (!empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png') : (!empty($siswa['photo_url']) ? $siswa['photo_url'] : 'assets/pp.png'); ?>"
                                class="profile-circle">
                        </div>
                        <div class="class-content">
                            <h4 class="class-title mb-3" id="judul-preview">-</h4>

                            <div class="class-meta" style="font-size: 12px;">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex">
                                            <i class="bi bi-file-text me-2 text-muted"></i>
                                            <span class="text-secondary" id="deskripsi-preview">-</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-book me-2 text-muted"></i>
                                            <span class="text-dark" id="kelas-preview">-</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex">
                                            <i class="bi bi-list-check me-2 text-muted"></i>
                                            <span class="text-secondary" id="materi-preview">-</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event me-2 text-muted"></i>
                                            <span class="text-secondary" id="waktu-preview">-</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock me-2 text-muted"></i>
                                            <span class="text-secondary" id="duration-info">
                                                Waktu ujian akan muncul setelah Anda memilih waktu mulai dan selesai
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button class="btn px-3 py-2 w-100"
                                    data-bs-toggle="popover"
                                    data-bs-placement="top"
                                    data-bs-trigger="hover focus"
                                    data-bs-content="Tombol ini hanyalah simulasi gambaran ujian Anda pada halaman siswa"
                                    style="background: rgb(218, 119, 86); border: none; color: white;">
                                    <i class="bi bi-play-circle me-1"></i> Mulai Ujian
                                </button>
                            </div>

                            <script>
                                // Initialize all popovers
                                document.addEventListener('DOMContentLoaded', function() {
                                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                                    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                                        return new bootstrap.Popover(popoverTriggerEl);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .preview-section {
            position: relative;
        }

        .preview-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
            display: block;
        }

        .preview-value {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            min-height: 45px;
            color: #495057;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .preview-value:empty::before {
            content: '-';
            color: #adb5bd;
        }

        .preview-value.highlight {
            background-color: #fff;
            border-color: rgb(218, 119, 86);
            box-shadow: 0 0 0 3px rgba(218, 119, 86, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-indicator {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .step-indicator.completed {
            background-color: rgb(218, 119, 86);
            color: white;
        }

        .step-progress {
            height: 4px;
            background-color: #e9ecef;
            margin: 20px 0;
            border-radius: 2px;
            position: relative;
        }

        .step-progress-bar {
            height: 100%;
            background-color: rgb(218, 119, 86);
            border-radius: 2px;
            transition: width 0.3s ease;
        }
    </style>

    <script>
        // script ambil waktu 
        function calculateDuration() {
            const start = new Date(document.getElementById('tanggal_mulai').value);
            const end = new Date(document.getElementById('tanggal_selesai').value);

            if (start && end) {
                const diffInMinutes = Math.floor((end - start) / (1000 * 60));
                if (diffInMinutes > 0) {
                    document.getElementById('duration-info').innerHTML =
                        `Durasi ujian selama ${diffInMinutes} menit`;
                } else {
                    document.getElementById('duration-info').innerHTML =
                        'Waktu selesai harus lebih besar dari waktu mulai';
                }
            }
        }




        let currentStep = 1;
        const totalSteps = document.querySelectorAll('.question-card').length;

        function updatePreview(element) {
            const previewId = element.dataset.preview;
            const previewElement = document.getElementById(previewId);
            const oldValue = previewElement.textContent;
            const newValue = element.value || '-';

            previewElement.textContent = newValue;

            if (oldValue !== newValue) {
                previewElement.style.animation = 'fadeIn 0.3s ease';
                previewElement.classList.add('highlight');

                setTimeout(() => {
                    previewElement.style.animation = '';
                    previewElement.classList.remove('highlight');
                }, 1500);
            }
        }

        function updateMateriPreview() {
            const materiInputs = document.querySelectorAll('input[name="materi[]"]');
            const materiValues = Array.from(materiInputs)
                .map((input, index) => input.value ? `${index + 1}. ${input.value}` : null)
                .filter(value => value);

            const previewElement = document.getElementById('materi-preview');
            previewElement.innerHTML = materiValues.length ? materiValues.join('<br>') : '-';

            previewElement.style.animation = 'fadeIn 0.3s ease';
            previewElement.classList.add('highlight');
            setTimeout(() => {
                previewElement.style.animation = '';
                previewElement.classList.remove('highlight');
            }, 1500);
        }

        function updateWaktuPreview() {
            const tanggalMulai = document.getElementById('tanggal_mulai').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai').value;

            const formatDate = (date) => {
                return date ? new Date(date).toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';
            };

            const previewElement = document.getElementById('waktu-preview');
            previewElement.innerHTML = `
        Mulai: ${formatDate(tanggalMulai)}<br>
        Selesai: ${formatDate(tanggalSelesai)}
    `;

            previewElement.style.animation = 'fadeIn 0.3s ease';
            previewElement.classList.add('highlight');
            setTimeout(() => {
                previewElement.style.animation = '';
                previewElement.classList.remove('highlight');
            }, 1500);
        }

        // Add event listeners
        document.getElementById('tanggal_mulai').addEventListener('change', updateWaktuPreview);
        document.getElementById('tanggal_selesai').addEventListener('change', updateWaktuPreview);

        // popover untuk tombol navigasi
        const initPopovers = () => {
            const nextButton = document.querySelector('.navigation-buttons .color-web');
            const prevButton = document.querySelector('.navigation-buttons .btn-secondary');

            // Destroy existing popovers
            const popovers = [nextButton, prevButton].map(button => bootstrap.Popover.getInstance(button));
            popovers.forEach(popover => popover?.dispose());

            // Initialize new popovers
            new bootstrap.Popover(prevButton, {
                placement: 'top',
                trigger: 'hover'
            });

            // Update next/submit button popover based on current step
            new bootstrap.Popover(nextButton, {
                content: currentStep === totalSteps ? 'Kirim dan buat soal' : 'Lanjut ke langkah berikutnya',
                placement: 'top',
                trigger: 'hover'
            });
        };

        function showStep(step) {
            const cards = document.querySelectorAll('.question-card');
            const nextButton = document.querySelector('.navigation-buttons .color-web');
            const prevButton = document.querySelector('.navigation-buttons .btn-secondary');

            // Hide all cards
            cards.forEach(card => {
                card.style.opacity = '0';
                card.classList.add('d-none');
            });

            // Show current card
            const currentCard = document.querySelector(`[data-step="${step}"]`);
            currentCard.classList.remove('d-none');

            // Update button visibility
            prevButton.style.visibility = step > 1 ? 'visible' : 'hidden';
            prevButton.style.opacity = step > 1 ? '1' : '0';

            // Change button text and type on last step
            if (step === totalSteps) {
                nextButton.innerHTML = '<i class="bi bi-send"></i>';
                nextButton.type = 'submit';
            } else {
                nextButton.innerHTML = '<i class="bi bi-chevron-right" style="font-size: 16px;"></i>';
                nextButton.type = 'button';
            }

            // Make sure navigation buttons are visible
            document.querySelector('.navigation-buttons').style.display = 'flex';

            initPopovers();

            setTimeout(() => {
                currentCard.style.opacity = '1';
                currentCard.style.transition = 'opacity 0.3s ease';
            }, 50);
        }

        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
            initPopovers();
        });

        // Panggil showStep(1) segera setelah DOM loaded
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
        });

        function updateStepDisplay() {
            document.getElementById('current-step').textContent = currentStep;

            // Update progress indication
            const progress = (currentStep / totalSteps) * 100;

            // Optional: Add visual progress indicator
            document.querySelectorAll('.step-indicator').forEach((step, index) => {
                if (index < currentStep) {
                    step.classList.add('completed');
                } else {
                    step.classList.remove('completed');
                }
            });
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                if (validateCurrentStep()) {
                    currentStep++;
                    showStep(currentStep);
                    updateStepDisplay();
                }
            } else if (currentStep === totalSteps) {
                // Handle form submission
                if (validateCurrentStep()) {
                    document.querySelector('form').submit();
                }
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                updateStepDisplay();
            }
        }

        // Add validation for each step
        function validateCurrentStep() {
            const currentCard = document.querySelector(`[data-step="${currentStep}"]`);
            const requiredFields = currentCard.querySelectorAll('input[required], select[required], textarea[required]');

            let isValid = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                document.getElementById('validationMessage').textContent = 'Harap lengkapi semua field yang diperlukan';
                const validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
                validationModal.show();
            }

            return isValid;
        }

        // Initialize first step when page loads
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
            updateStepDisplay();
        });

        // Initialize first step
        showStep(1);
    </script>

    <script>
        // Simpan data background kelas dalam objek JavaScript
        const kelasBackgrounds = <?php
                                    mysqli_data_seek($result_kelas, 0); // Reset pointer hasil query
                                    $backgrounds = array();
                                    while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                                        $backgrounds[$kelas['id']] = $kelas['background_image'];
                                    }
                                    echo json_encode($backgrounds);
                                    ?>;


        // Modifikasi fungsi updatePreviewSelect
        // Modifikasi bagian perubahan background di fungsi updatePreviewSelect
        function updatePreviewSelect(element) {
            const previewElement = document.getElementById('kelas-preview');
            const selectedOption = element.options[element.selectedIndex];
            const newValue = selectedOption.text || '-';

            previewElement.textContent = newValue;

            previewElement.style.animation = 'fadeIn 0.3s ease';
            previewElement.classList.add('highlight');

            // Update background berdasarkan kelas yang dipilih
            const kelasId = element.value;
            let backgroundUrl = 'assets/bg.jpg'; // Default background

            if (kelasId && kelasBackgrounds[kelasId] && kelasBackgrounds[kelasId] !== '') {
                backgroundUrl = kelasBackgrounds[kelasId];
            }

            // Set background image
            document.querySelector('.class-banner').style.backgroundImage = `url('${backgroundUrl}')`;

            // Update hidden input untuk background
            if (document.getElementById('background_image')) {
                document.getElementById('background_image').value = backgroundUrl;
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'background_image';
                hiddenInput.name = 'background_image';
                hiddenInput.value = backgroundUrl;
                document.querySelector('form').appendChild(hiddenInput);
            }

            setTimeout(() => {
                previewElement.style.animation = '';
                previewElement.classList.remove('highlight');
            }, 1500);
        }
    </script>

    <!-- materi ujian -->
    <script>
        function tambahMateri() {
            const container = document.getElementById('materi-container');
            const materiCount = container.getElementsByClassName('materi-item').length + 1;

            const newMateri = document.createElement('div');
            newMateri.className = 'materi-item mb-2';
            newMateri.innerHTML = `
            <div class="input-group">
                <span class="input-group-text bg-light">${materiCount}</span>
                <input type="text" class="form-control" name="materi[]" placeholder="Masukkan materi ujian">
                <button type="button" class="btn btn-outline-danger" onclick="hapusMateri(this)">
                    <span class="bi bi-trash"></span>
                </button>
            </div>
        `;
            container.appendChild(newMateri);
            updateMateriNumbers();

            // Add event listener to new input
            newMateri.querySelector('input').addEventListener('input', updateMateriPreview);
        }

        document.querySelector('input[name="materi[]"]').addEventListener('input', updateMateriPreview);

        function hapusMateri(btn) {
            const materiItems = document.getElementsByClassName('materi-item');
            if (materiItems.length > 1) {
                btn.closest('.materi-item').remove();
                updateMateriNumbers();
                updateMateriPreview();
            } else {
                // Show modal instead of alert
                const modalHtml = `
                <div class="modal fade" id="materiMinimumModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px;">
                            <div class="modal-body text-center p-4">
                                <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                                <h5 class="mt-3 fw-bold">Peringatan</h5>
                                <p class="mb-4">Harus ada minimal satu materi</p>
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn color-web flex-fill text-white px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Ok</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                // Append modal to body if it doesn't exist
                if (!document.getElementById('materiMinimumModal')) {
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                }

                // Show the modal
                const minimumModal = new bootstrap.Modal(document.getElementById('materiMinimumModal'));
                minimumModal.show();
            }
        }

        function updateMateriNumbers() {
            const materiItems = document.getElementsByClassName('materi-item');
            Array.from(materiItems).forEach((item, index) => {
                const numberSpan = item.querySelector('.input-group-text');
                numberSpan.textContent = index + 1;
            });
        }
    </script>

    <style>
        .materi-item .input-group-text {
            min-width: 45px;
            justify-content: center;
            font-weight: 500;
        }

        .materi-item .input-group {
            /* box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); */
            /* border: 1px solid #dee2e6; */
            border-radius: 6px;
        }

        /* .materi-item .input-group:focus-within {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        } */

        .materi-item .form-control {
            border-left: none;
        }

        .materi-item .form-control:focus {
            border-color: #dee2e6;
            /* box-shadow: none; */
        }

        .btn-outline-danger {
            border: none;
        }

        .btn-outline-danger:hover {
            background-color: #ffe5e5;
        }

        .btn-outline-secondary {
            border: 1px solid #6c757d;
            background-color: #f8f9fa;
        }

        .btn-outline-secondary:hover {
            background-color: #e9ecef;
            border: 1px solid #6c757d;
            color: #6c757d;
        }
    </style>

    <script>
        // Modify your form submit event handler
        document.querySelector('form').addEventListener('submit', function(e) {
            const form = e.target;

            // First check specific validation for date/time fields
            const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
            const tanggalSelesai = new Date(document.getElementById('tanggal_selesai').value);

            if (tanggalSelesai <= tanggalMulai) {
                e.preventDefault();
                document.getElementById('validationMessage').textContent = 'Tanggal & waktu selesai harus lebih besar dari tanggal & waktu mulai!';
                const validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
                validationModal.show();
                return;
            }

            // Only validate non-materi required fields
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
            let hasError = false;
            let firstInvalidElement = null;

            requiredFields.forEach(field => {
                // Skip materi[] fields
                if (field.name === 'materi[]') return;

                if (!field.value.trim()) {
                    hasError = true;
                    if (!firstInvalidElement) firstInvalidElement = field;
                }
            });

            if (hasError) {
                e.preventDefault();
                e.stopPropagation();
                if (firstInvalidElement) {
                    firstInvalidElement.focus();
                    document.getElementById('validationMessage').textContent = 'Data Belum Lengkap';
                    const validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
                    validationModal.show();
                }
            }
        });
    </script>
</body>

</html>