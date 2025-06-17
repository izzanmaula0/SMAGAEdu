<?php
session_start();
require "koneksi.php";

// Cek session - izinkan guru dan admin
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

$ujian_id = $_GET['ujian_id'];

// Query informasi ujian
$query_ujian = "SELECT u.*, k.tingkat 
                FROM ujian u
                JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.id = '$ujian_id'";
$result_ujian = mysqli_query($koneksi, $query_ujian);

if (!$result_ujian || mysqli_num_rows($result_ujian) == 0) {
    die("Ujian tidak ditemukan");
}

$ujian = mysqli_fetch_assoc($result_ujian);
$ujian['judul'] = $ujian['judul'] ?? 'Judul Tidak Tersedia';
$ujian['mata_pelajaran'] = $ujian['mata_pelajaran'] ?? 'Mata Pelajaran Tidak Tersedia';
$ujian['tingkat'] = $ujian['tingkat'] ?? 'Tidak Diketahui';

// Total questions query
$query_total = "SELECT COUNT(*) as total FROM bank_soal WHERE ujian_id = '$ujian_id'";
$result_total = mysqli_query($koneksi, $query_total);
$total_questions = mysqli_fetch_assoc($result_total)['total'];

// Peserta query
$query_peserta = "
    SELECT 
        s.id as siswa_id,
        s.nama,
        s.photo_type,
        s.photo_url,
        s.foto_profil,
        COUNT(DISTINCT ju.id) as attempted_questions,
        SUM(CASE WHEN ju.jawaban = bs.jawaban_benar THEN 1 ELSE 0 END) as correct_answers,
        SUM(CASE WHEN ju.jawaban != bs.jawaban_benar AND ju.jawaban IS NOT NULL THEN 1 ELSE 0 END) as wrong_answers
    FROM siswa s
    JOIN kelas_siswa ks ON s.id = ks.siswa_id
    LEFT JOIN jawaban_ujian ju ON s.id = ju.siswa_id AND ju.ujian_id = '$ujian_id'
    LEFT JOIN bank_soal bs ON bs.id = ju.soal_id
    WHERE ks.kelas_id = '{$ujian['kelas_id']}'
    GROUP BY s.id
";
$result_peserta = mysqli_query($koneksi, $query_peserta);

if (!$result_peserta || mysqli_num_rows($result_peserta) == 0) {
    die("Tidak ada peserta ujian");
}

$peserta = array();
while ($row = mysqli_fetch_assoc($result_peserta)) {
    $peserta[] = $row;
}

// Menghitung persentase nilai
// Replace the original calculation block with this:
$rata_rata = 0;
$nilai_tertinggi = 0;
$nilai_terendah = 0;

if ($total_questions > 0 && count($peserta) > 0) {
    foreach ($peserta as $p) {
        $nilai = ($p['correct_answers'] / $total_questions) * 100;
        $rata_rata += $nilai;
        $nilai_tertinggi = max($nilai_tertinggi, $nilai);
        $nilai_terendah = min($nilai_terendah == 0 ? $nilai : $nilai_terendah, $nilai);
    }
    $rata_rata /= count($peserta);
}

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);


// ambil soal 
// Query untuk mendapatkan statistik jawaban per soal
// Query untuk mendapatkan statistik jawaban per soal
// Query untuk mendapatkan statistik jawaban per soal
$query_soal_stats = "
    SELECT 
        bs.id as soal_id,
        bs.pertanyaan,
        bs.jawaban_benar,
        bs.jawaban_a,
        bs.jawaban_b,
        bs.jawaban_c,
        bs.jawaban_d,
        bs.gambar_soal,
        COUNT(ju.id) as total_menjawab,
        COUNT(CASE WHEN UPPER(ju.jawaban) = UPPER(bs.jawaban_benar) THEN 1 END) as total_benar,
        COUNT(CASE WHEN ju.jawaban IS NOT NULL AND UPPER(ju.jawaban) != UPPER(bs.jawaban_benar) THEN 1 END) as total_salah,
        COUNT(CASE WHEN UPPER(ju.jawaban) = 'A' THEN 1 END) as jawab_a,
        COUNT(CASE WHEN UPPER(ju.jawaban) = 'B' THEN 1 END) as jawab_b,
        COUNT(CASE WHEN UPPER(ju.jawaban) = 'C' THEN 1 END) as jawab_c,
        COUNT(CASE WHEN UPPER(ju.jawaban) = 'D' THEN 1 END) as jawab_d
    FROM bank_soal bs
    LEFT JOIN jawaban_ujian ju ON bs.id = ju.soal_id AND ju.ujian_id = '$ujian_id'
    WHERE bs.ujian_id = '$ujian_id'
    GROUP BY bs.id
    ORDER BY total_salah DESC";

$result_soal_stats = mysqli_query($koneksi, $query_soal_stats);
$soal_stats = [];
while ($row = mysqli_fetch_assoc($result_soal_stats)) {
    $soal_stats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hasil Ujian</title>

    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- CSS kustom setelah Bootstrap -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <body>

        <style>
            .navbar {
                display: none;
            }

            body {
                font-family: 'Merriweather', serif;
            }

            @media screen and (max-width: 768px) {
                .navbar {
                    display: block !important;
                }

                .menu-samping {
                    display: none;
                }
            }

            @media screen and (max-width: 768px) {
                /* .container-fluid {
                    display: none;
                } */
            }

            /* ANALISIS CANVAS */
            /* CSS untuk Canvas Analisis */
            #analisisCanvas {
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            }

            #hasilAnalisis {
                font-size: 0.95rem;
                line-height: 1.6;
            }

            #hasilAnalisis h3,
            #hasilAnalisis h4 {
                color: rgb(218, 119, 86);
                margin-top: 1.5rem;
                margin-bottom: 1rem;
            }

            #hasilAnalisis ul {
                padding-left: 1.5rem;
                margin-bottom: 1rem;
            }

            #hasilAnalisis li {
                margin-bottom: 0.5rem;
            }

            .typing-effect {
                border-right: 2px solid rgb(218, 119, 86);
                white-space: nowrap;
                overflow: hidden;
                animation: typing 3.5s steps(40, end), blink-caret 0.75s step-end infinite;
            }

            @keyframes typing {
                from {
                    width: 0
                }

                to {
                    width: 100%
                }
            }

            @keyframes blink-caret {

                from,
                to {
                    border-color: transparent
                }

                50% {
                    border-color: rgb(218, 119, 86)
                }
            }

            /* Tambahkan ke dalam CSS Anda */
            .analisis-result {
                padding: 10px;
            }

            .analisis-result h2,
            .analisis-result h3,
            .analisis-result h4 {
                color: rgb(0, 0, 0);
                font-weight: 600;
            }

            .analisis-result p {
                line-height: 1.7;
                color: #333;
            }

            .analisis-result ul {
                padding-left: 1.25rem;
            }

            .analisis-result ul li {
                margin-bottom: 0.75rem;
                line-height: 1.6;
            }

            .analisis-result strong {
                color: rgb(0, 0, 0);
                font-weight: 600;
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

        <!-- Modal Pengaturan Excel -->
        <div class="modal fade" id="excelConfigModal" tabindex="-1" aria-labelledby="excelConfigModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold" style="font-size: 1.3rem;" id="excelConfigModalLabel">
                                Sebelum menguduh, seperti apa data nilai siswa ingin dimasukkan dalam Excel?
                            </h5>
                        </div>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="border rounded-4 p-3 mb-4 bg-light d-flex">
                            <span class="bi bi-exclamation-circle pe-3"></span>
                            <div>
                                <p style="font-size:14px;" class="p-0 m-0 fw-bold">Salin Data Nilai Siswa Ke Lembar Kerja Anda</p>
                                <p style="font-size: 12px;" class="p-0 m-0 text-start">Setelah Anda mendapatkan file excel nilai siswa dari kami, silahkan untuk segera mensalin hasil nilai ke dalam lembar kerja excel pribadi Anda. Tindakan keamanan MS Excel membuat file excel dari kami berpotensi <span class="fst-italic">corrupt</span> atau rusak</p>
                            </div>
                        </div>

                        <!-- Pengaturan 1: Pembulatan Nilai -->
                        <div class="card mb-3" style="border-radius: 12px; border: 1px solid #e0e0e0;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">Apakah nilai siswa perlu di bulatkan?</h6>
                                        <p class="text-muted mb-0" style="font-size: 12px;" id="statusBulatkan">
                                            Anda akan menerima nilai murni dengan koma
                                        </p>
                                    </div>
                                    <div class="form-check form-switch ms-4">
                                        <input class="form-check-input" type="checkbox" id="bulatkanNilai" style="transform: scale(1.2);">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengaturan 2: Sembunyikan Siswa yang Tidak Mengerjakan -->
                        <div class="card mb-3" style="border-radius: 12px; border: 1px solid #e0e0e0;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">Apakah siswa yang tidak mengerjakan tidak perlu di input?</h6>
                                        <p class="text-muted mb-0" style="font-size: 12px;" id="statusSembunyikan">
                                            Anda akan menerima seluruh data siswa
                                        </p>
                                    </div>
                                    <div class="form-check form-switch ms-4">
                                        <input class="form-check-input" type="checkbox" id="sembunyikanSiswa" style="transform: scale(1.2);">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer btn-group border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-4 px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" class="btn rounded-4 px-4" id="downloadExcel"
                            style="background-color: rgb(218, 119, 86); color: white;">
                            Download Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- animasi modal -->
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

        <!-- Mobile view blocker (iOS style)
        <div class="mobile-blocker d-md-none position-fixed top-0 start-0 w-100 h-100 bg-white" style="z-index: 9999;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100 px-4">
                <div class="text-center mb-4">
                    <i class="bi bi-laptop display-1 text-secondary"></i>
                </div>
                <h4 class="mb-3 fw-bold text-dark">Akses Ditolak</h4>
                <p class="text-center text-secondary mb-4" style="font-size: 12px;">
                    Halaman ini hanya dapat diakses pada perangkat laptop atau tablet.
                    Silakan gunakan perangkat dengan layar yang lebih besar.
                </p>
                <a href="ujian_guru.php" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" style="background-color: rgb(218, 119, 86); border: none;">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali
                </a>
            </div>
        </div> -->



        <!-- ini isi kontennya -->
        <div class="col p-md-4 col-utama">
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
                        /* Untuk memberikan space dari fixed navbar mobile */
                    }
                }
            </style>

            <!-- Top Section -->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 mb-4">
                            <div class="card-header bg-white border-0 pb-0">
                                <div class="d-flex border-0 justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?php echo htmlspecialchars($ujian['mata_pelajaran']); ?></h3>
                                        <p class="text-muted mb-0" style="font-size: 12px;">
                                            <?php echo htmlspecialchars($ujian['judul']); ?> |
                                            Kelas <?php echo htmlspecialchars($ujian['tingkat']); ?>
                                        </p>
                                    </div>
                                    <div class="d-flex">
                                        <button data-bs-toggle="modal" data-bs-target="#excelConfigModal" class="btn d-flex align-items-center bg-light rounded-4 border me-2">
                                            <i class="ti ti-file-spreadsheet me-1"></i>
                                            <p class="d-none p-0 m-0 d-md-block">Unduh Excel</p>
                                        </button>
                                        <button id="btnAnalisis" class="btn bg-light d-flex justify-items-center align-items-center rounded-4 border">
                                            <i class="ti ti-sparkles me-1"></i>
                                            <p class="d-none p-0 m-0 d-md-block">Analisis</p>
                                        </button>
                                    </div>
                                </div>

                            </div>


                            <div class="card-body">
                                <?php if ($total_questions > 0): ?>
                                    <div class="row mb-4 g-3">
                                        <div class="col-6 col-md-3">
                                            <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                                <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                                    <i class="bi bi-people" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div>
                                                        <h6 class="mb-2">Total Peserta</h6>
                                                        <h3 class="m-0 fw-bold">
                                                            <?php echo mysqli_num_rows($result_peserta); ?>
                                                            <span class="fs-6 fw-normal text-muted">Siswa</span>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                                <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                                    <i class="bi bi-spellcheck" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div>
                                                        <h6 class="mb-2">Rata-rata Nilai</h6>
                                                        <h3 class="m-0 fw-bold">
                                                            <?php echo number_format($rata_rata, 1); ?>
                                                            <span class="fs-6 fw-normal text-muted">/100</span>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                                <div style="position: absolute; right: -20px; bottom: -100px; opacity: 0.1;">
                                                    <i class="bi bi-trophy" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div>
                                                        <h6 class="mb-2">Nilai Tertinggi</h6>
                                                        <h3 class="m-0 fw-bold">
                                                            <?php echo number_format($nilai_tertinggi, 1); ?>
                                                            <span class="fs-6 fw-normal text-muted">/100</span>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                                <div style="position: absolute; right: -20px; bottom: -100px; opacity: 0.1;">
                                                    <i class="bi bi-flag" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div>
                                                        <h6 class="mb-2">Nilai Terendah</h6>
                                                        <h3 class="m-0 fw-bold">
                                                            <?php echo number_format($nilai_terendah, 1); ?>
                                                            <span class="fs-6 fw-normal text-muted">/100</span>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grafik Analisis Soal -->
                                    <div class="mt-4 d-none d-md-block">
                                        <div class="card border" style="border-radius: 15px;">
                                            <div class="card-body p-4">
                                                <div class="chart-container">
                                                    <canvas id="soalChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <style>
                                        .chart-container {
                                            position: relative;
                                            height: 200px;
                                            overflow-x: auto;
                                            overflow-y: hidden;
                                            border-radius: 12px;
                                        }

                                        #soalChart {
                                            min-width: 100px;
                                            height: 100% !important;
                                        }

                                        .btn-group .btn {
                                            transition: all 0.3s ease;
                                        }

                                        .btn-group .btn.active {
                                            background-color: rgb(218, 119, 86);
                                            color: white;
                                            transform: scale(1.02);
                                            box-shadow: 0 2px 8px rgba(218, 119, 86, 0.3);
                                        }

                                        .btn-group .btn:not(.active) {
                                            color: #666;
                                        }

                                        .btn-group .btn:hover:not(.active) {
                                            background-color: rgba(218, 119, 86, 0.1);
                                            color: rgb(218, 119, 86);
                                        }

                                        /* Custom scrollbar for chart container */
                                        .chart-container::-webkit-scrollbar {
                                            height: 8px;
                                        }

                                        .chart-container::-webkit-scrollbar-track {
                                            background: #f1f1f1;
                                            border-radius: 4px;
                                        }

                                        .chart-container::-webkit-scrollbar-thumb {
                                            background: rgba(218, 119, 86, 0.5);
                                            border-radius: 4px;
                                        }

                                        .chart-container::-webkit-scrollbar-thumb:hover {
                                            background: rgba(218, 119, 86, 0.7);
                                        }
                                    </style>
                                    <!-- Modal Detail Soal -->
                                    <div class="modal fade" id="detailSoalModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-semibold">Detail Soal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body px-4">
                                                    <!-- Pertanyaan dengan header yang jelas -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Pertanyaan</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="modalPertanyaan" class="text-dark"></div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Statistik jawaban -->
                                                        <div class="col-lg-6">
                                                            <div class="card mb-4">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistik Jawaban</h6>
                                                                </div>
                                                                <div class="card-body shadow-none border">
                                                                    <!-- Chart jawaban -->
                                                                    <div class="chart-container mb-3">
                                                                        <canvas id="jawabanChart" height="200"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Pilihan jawaban -->
                                                        <div class="col-lg-6">
                                                            <div class="card mb-4">
                                                                <div class="card-header bg-light">
                                                                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Pilihan Jawaban</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group" id="pilihanJawaban">
                                                                        <!-- Pilihan jawaban akan diisi oleh JavaScript -->
                                                                    </ul>
                                                                    <div class="mt-3 small text-muted">
                                                                        <i class="bi bi-info-circle"></i> Pilihan yang benar ditandai dengan warna.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        .modal-content {
                                            border-radius: 16px;
                                            border: none;
                                        }

                                        .modal-header {
                                            padding: 1.25rem 1.5rem;
                                        }

                                        .modal-header .btn-close {
                                            background-size: 12px;
                                            opacity: 0.5;
                                        }

                                        .card {
                                            border-radius: 12px;
                                            border: 1px solid rgba(0, 0, 0, 0.08);
                                            overflow: hidden;
                                        }

                                        .card-header {
                                            padding: 0.75rem 1.25rem;
                                            background-color: white !important;
                                            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                                        }

                                        .list-group-item {
                                            border: none;
                                            background: #f8f9fa;
                                            margin-bottom: 8px;
                                            border-radius: 10px !important;
                                            font-size: 0.95rem;
                                            padding: 0.75rem 1rem;
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                        }

                                        .list-group-item-success {
                                            background-color: rgba(86, 218, 121, 0.1);
                                            color: rgb(86, 218, 110);
                                            border-left: 4px solid rgb(112, 218, 86);
                                        }

                                        .badge {
                                            font-size: 0.75rem;
                                            font-weight: 500;
                                            padding: 0.35rem 0.65rem;
                                            border-radius: 8px;
                                        }

                                        .chart-container {
                                            position: relative;
                                            height: 200px;
                                        }

                                        .progress {
                                            height: 8px;
                                            border-radius: 4px;
                                            margin-bottom: 8px;
                                        }

                                        .progress-bar {
                                            border-radius: 4px;
                                        }

                                        @media (max-width: 992px) {
                                            .modal-dialog {
                                                max-width: 95%;
                                                margin: 1rem auto;
                                            }
                                        }
                                    </style>
                                    <!-- style tabel -->
                                    <style>
                                        .ios-table {
                                            border-radius: 16px;
                                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                                            background: white;
                                            overflow: hidden;
                                        }

                                        .ios-table thead th {
                                            background: #f8f9fa;
                                            font-weight: 600;
                                            font-size: 0.9rem;
                                            padding: 16px;
                                            border: none;
                                        }

                                        .ios-table tbody td {
                                            padding: 16px;
                                            border-bottom: 1px solid #f1f1f1;
                                            vertical-align: middle;
                                        }

                                        .ios-table tbody tr:last-child td {
                                            border-bottom: none;
                                        }

                                        .ios-badge {
                                            padding: 6px 12px;
                                            border-radius: 20px;
                                            font-size: 0.8rem;
                                            font-weight: 500;
                                        }

                                        .ios-btn {
                                            padding: 8px 16px;
                                            border-radius: 15px;
                                            border: none;
                                            font-size: 0.9rem;
                                            font-weight: 500;
                                            transition: all 0.2s;
                                        }

                                        .ios-btn:active {
                                            transform: scale(0.95);
                                        }

                                        .profile-pic {
                                            width: 40px;
                                            height: 40px;
                                            border-radius: 50%;
                                            object-fit: cover;
                                            margin-right: 12px;
                                        }

                                        .student-info {
                                            display: flex;
                                            align-items: center;
                                        }

                                        /* Search Bar Styles */
                                        .search-container {
                                            max-width: 400px;
                                        }

                                        .search-input {
                                            padding: 12px 16px 12px 45px;
                                            border-radius: 15px;
                                            border: 1px solid #e0e0e0;
                                            font-size: 0.95rem;
                                            transition: all 0.3s ease;
                                        }

                                        .search-input:focus {
                                            border-color: rgb(218, 119, 86);
                                            box-shadow: 0 0 0 3px rgba(218, 119, 86, 0.1);
                                        }

                                        .search-icon {
                                            left: 16px;
                                            top: 50%;
                                            transform: translateY(-50%);
                                            color: #999;
                                            font-size: 1.1rem;
                                        }

                                        /* Mobile Card Styles */
                                        .student-card {
                                            background: white;
                                            border-radius: 16px;
                                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                                            border: 1px solid #f0f0f0;
                                            overflow: hidden;
                                            transition: all 0.3s ease;
                                        }

                                        .student-card:hover {
                                            transform: translateY(-2px);
                                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                        }

                                        .student-card .card-header {
                                            padding: 16px;
                                            background: #fafafa;
                                            border-bottom: 1px solid #f0f0f0;
                                            position: relative;
                                        }

                                        .card-profile-pic {
                                            width: 48px;
                                            height: 48px;
                                            border-radius: 50%;
                                            object-fit: cover;
                                            border: 2px solid white;
                                            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                                        }

                                        .status-badge {
                                            font-size: 0.75rem;
                                            padding: 4px 10px;
                                            border-radius: 20px;
                                            font-weight: 500;
                                        }

                                        .status-selesai {
                                            background-color: #e8f5e9;
                                            color: #2e7d32;
                                        }

                                        .status-belum {
                                            background-color: #f5f5f5;
                                            color: #757575;
                                        }

                                        .nilai-badge {
                                            position: absolute;
                                            top: 16px;
                                            right: 16px;
                                            background: rgb(218, 119, 86);
                                            color: white;
                                            font-size: 1.5rem;
                                            font-weight: bold;
                                            width: 60px;
                                            height: 60px;
                                            border-radius: 50%;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            box-shadow: 0 4px 8px rgba(218, 119, 86, 0.3);
                                        }

                                        .card-stats {
                                            padding: 16px;
                                            display: flex;
                                            gap: 15px;
                                            justify-content: space-around;
                                            border-bottom: 1px solid #f0f0f0;
                                        }

                                        .stat-item {
                                            text-align: center;
                                            flex: 1;
                                        }

                                        .stat-item i {
                                            display: block;
                                            font-size: 1.5rem;
                                            margin-bottom: 8px;
                                        }

                                        .stat-icon-success {
                                            color: #2e7d32;
                                        }

                                        .stat-icon-danger {
                                            color: #d32f2f;
                                        }

                                        .stat-icon-muted {
                                            color: #757575;
                                        }

                                        .stat-value {
                                            display: block;
                                            font-size: 1.25rem;
                                            font-weight: bold;
                                            color: #333;
                                            margin-bottom: 4px;
                                        }

                                        .stat-label {
                                            display: block;
                                            font-size: 0.8rem;
                                            color: #666;
                                        }

                                        .student-card .card-footer {
                                            padding: 16px;
                                            background: white;
                                        }

                                        .detail-button {
                                            background-color: rgb(218, 119, 86);
                                            border: none;
                                            padding: 12px 24px;
                                            border-radius: 12px;
                                            font-weight: 500;
                                            transition: all 0.3s ease;
                                        }

                                        .detail-button:hover {
                                            background-color: rgb(200, 100, 70);
                                            transform: translateY(-1px);
                                        }

                                        .detail-button:active {
                                            transform: translateY(0);
                                        }

                                        /* Responsiveness untuk desktop */
                                        @media (min-width: 768px) {
                                            .search-container {
                                                max-width: 300px;
                                            }
                                        }
                                    </style>

                                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                    <script>
                                        // Data dari PHP
                                        const soalData = <?php echo json_encode($soal_stats); ?>;
                                        let soalChart;

                                        // Fungsi untuk menginisialisasi grafik batang
                                        function initSoalChart() {
                                            const ctx = document.getElementById('soalChart').getContext('2d');

                                            if (soalChart) {
                                                soalChart.destroy();
                                            }

                                            const labels = soalData.map((_, index) => `Soal ${index + 1}`);
                                            const correctData = soalData.map(item => parseInt(item.total_benar));
                                            const incorrectData = soalData.map(item => parseInt(item.total_salah));

                                            soalChart = new Chart(ctx, {
                                                type: 'bar',
                                                data: {
                                                    labels: labels,
                                                    datasets: [{
                                                            label: 'Jawaban Benar',
                                                            data: correctData,
                                                            backgroundColor: 'rgba(79, 192, 75, 0.8)',
                                                            // borderColor: 'rgb(0, 0, 0)',
                                                            // borderWidth: 1
                                                        },
                                                        {
                                                            label: 'Jawaban Salah',
                                                            data: incorrectData,
                                                            backgroundColor: 'rgba(255, 0, 55, 0.8)',
                                                            // borderColor: 'rgb(0, 0, 0)',
                                                            // borderWidth: 1
                                                        }
                                                    ]
                                                },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    scales: {
                                                        y: {
                                                            beginAtZero: true,
                                                            ticks: {
                                                                stepSize: 1
                                                            }
                                                        },
                                                        x: {
                                                            grid: {
                                                                display: false
                                                            }
                                                        }
                                                    },
                                                    plugins: {
                                                        tooltip: {
                                                            callbacks: {
                                                                label: function(context) {
                                                                    const total = parseInt(soalData[context.dataIndex].total_menjawab);
                                                                    const value = context.raw;
                                                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                                    return [
                                                                        `${context.dataset.label}: ${value} siswa`,
                                                                        `Persentase: ${percentage}%`
                                                                    ];
                                                                }
                                                            }
                                                        },
                                                        legend: {
                                                            position: 'top',
                                                            labels: {
                                                                usePointStyle: true,
                                                                padding: 20
                                                            }
                                                        }
                                                    },
                                                    onClick: (event, elements) => {
                                                        if (elements.length > 0) {
                                                            const index = elements[0].index;
                                                            showSoalDetail(soalData[index]);
                                                        }
                                                    }
                                                }
                                            });
                                        }

                                        // Inisialisasi grafik
                                        initSoalChart();

                                        // Fungsi untuk menampilkan detail soal
                                        function showSoalDetail(soal) {
                                            console.log('Detail soal:', soal);

                                            const modalElement = document.getElementById('detailSoalModal');
                                            if (!modalElement) {
                                                console.error('Modal element not found!');
                                                return;
                                            }

                                            // Inisialisasi modal menggunakan Bootstrap 5
                                            const modal = new bootstrap.Modal(modalElement);

                                            // Set pertanyaan
                                            let pertanyaanHtml = soal.pertanyaan;
                                            if (soal.gambar_soal) {
                                                pertanyaanHtml += `<br><img src="${soal.gambar_soal}" class="img-fluid mt-2" alt="Gambar Soal">`;
                                            }

                                            // Hitung persentase
                                            const totalMenjawab = parseInt(soal.total_menjawab) || 0;
                                            const persentaseBenar = totalMenjawab > 0 ?
                                                ((parseInt(soal.total_benar) / totalMenjawab) * 100).toFixed(1) : 0;
                                            const persentaseSalah = totalMenjawab > 0 ?
                                                ((parseInt(soal.total_salah) / totalMenjawab) * 100).toFixed(1) : 0;

                                            pertanyaanHtml += `
                                                    <div class="card mt-3" style="border-radius: 16px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                                        <div class="card-body p-4">
                                                            <h6 class="fw-bold mb-3" style="color: rgb(218, 119, 86);">Statistik Jawaban</h6>
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <span class="text-muted">Total Menjawab</span>
                                                                <span class="fw-bold">${totalMenjawab} siswa</span>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="text-muted">Benar</span>
                                                                    <span class="fw-bold" style="color: rgb(218, 119, 86);">${soal.total_benar || 0} siswa</span>
                                                                </div>
                                                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f0f0f0;">
                                                                    <div class="progress-bar" style="width: ${persentaseBenar}%; background-color: rgb(218, 119, 86); border-radius: 4px;"></div>
                                                                </div>
                                                                <div class="text-end mt-1">
                                                                    <small style="color: rgb(218, 119, 86);">${persentaseBenar}%</small>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="text-muted">Salah</span>
                                                                    <span class="fw-bold" style="color: #dc3545;">${soal.total_salah || 0} siswa</span>
                                                                </div>
                                                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f0f0f0;">
                                                                    <div class="progress-bar bg-danger" style="width: ${persentaseSalah}%; border-radius: 4px;"></div>
                                                                </div>
                                                                <div class="text-end mt-1">
                                                                    <small class="text-danger">${persentaseSalah}%</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;

                                            const pertanyaanElement = document.getElementById('modalPertanyaan');
                                            if (pertanyaanElement) {
                                                pertanyaanElement.innerHTML = pertanyaanHtml;
                                            }

                                            // Set pilihan jawaban dengan informasi jumlah pemilih
                                            const pilihanList = document.getElementById('pilihanJawaban');
                                            if (pilihanList) {
                                                pilihanList.innerHTML = `
            <li class="list-group-item ${soal.jawaban_benar === 'A' ? 'list-group-item-success' : ''}">
                A. ${soal.jawaban_a} 
                <span class="badge bg-secondary float-end">${soal.jawab_a || 0} siswa</span>
            </li>
            <li class="list-group-item ${soal.jawaban_benar === 'B' ? 'list-group-item-success' : ''}">
                B. ${soal.jawaban_b}
                <span class="badge bg-secondary float-end">${soal.jawab_b || 0} siswa</span>
            </li>
            <li class="list-group-item ${soal.jawaban_benar === 'C' ? 'list-group-item-success' : ''}">
                C. ${soal.jawaban_c}
                <span class="badge bg-secondary float-end">${soal.jawab_c || 0} siswa</span>
            </li>
            <li class="list-group-item ${soal.jawaban_benar === 'D' ? 'list-group-item-success' : ''}">
                D. ${soal.jawaban_d}
                <span class="badge bg-secondary float-end">${soal.jawab_d || 0} siswa</span>
            </li>
        `;
                                            }

                                            // Perbarui chart setelah modal ditampilkan
                                            modalElement.addEventListener('shown.bs.modal', function() {
                                                updateJawabanChart(soal);
                                            });

                                            // Tampilkan modal
                                            modal.show();
                                        }

                                        function updateJawabanChart(soal) {
                                            const chartCanvas = document.getElementById('jawabanChart');
                                            if (!chartCanvas) {
                                                console.error('jawabanChart canvas not found!');
                                                return;
                                            }

                                            // Destroy chart lama jika ada
                                            if (jawabanChart instanceof Chart) {
                                                jawabanChart.destroy();
                                            }

                                            const ctx = chartCanvas.getContext('2d');
                                            jawabanChart = new Chart(ctx, {
                                                type: 'bar',
                                                data: {
                                                    labels: ['A', 'B', 'C', 'D'],
                                                    datasets: [{
                                                        label: 'Jumlah Siswa',
                                                        data: [
                                                            parseInt(soal.jawab_a) || 0,
                                                            parseInt(soal.jawab_b) || 0,
                                                            parseInt(soal.jawab_c) || 0,
                                                            parseInt(soal.jawab_d) || 0
                                                        ],
                                                        backgroundColor: [
                                                            'rgba(218, 119, 86, 0.9)',
                                                            'rgba(218, 119, 86, 0.7)',
                                                            'rgba(218, 119, 86, 0.5)',
                                                            'rgba(218, 119, 86, 0.3)'
                                                        ]
                                                    }]
                                                },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: {
                                                        legend: {
                                                            display: false // Sembunyikan legend karena tidak terlalu diperlukan
                                                        }
                                                    },
                                                    scales: {
                                                        y: {
                                                            beginAtZero: true,
                                                            ticks: {
                                                                stepSize: 1
                                                            }
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    </script>
                                    <!-- Search Bar -->
                                    <div class="mt-4 mb-4">
                                        <div class="search-container">
                                            <div class="position-relative">
                                                <i class="bi bi-search position-absolute search-icon"></i>
                                                <input type="text" id="searchSiswa" class="form-control search-input" placeholder="Cari nama siswa...">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Desktop View (Tabel) -->
                                    <div class="table-responsive mt-4 border rounded-4 d-none d-md-block">
                                        <div class="ios-table">
                                            <table class="table table-hover align-middle mb-0" id="tableSiswa">
                                                <thead>
                                                    <tr>
                                                        <th>Siswa</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Benar</th>
                                                        <th class="text-center">Salah</th>
                                                        <th class="text-center">Belum</th>
                                                        <th class="text-center">Nilai</th>
                                                        <th class="text-center">Detail</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $result_peserta = mysqli_query($koneksi, $query_peserta);
                                                    while ($peserta = mysqli_fetch_assoc($result_peserta)):
                                                        $unattempted = $total_questions - ($peserta['correct_answers'] + $peserta['wrong_answers']);
                                                        $nilai = ($peserta['correct_answers'] / $total_questions) * 100;
                                                    ?>
                                                        <tr data-nama="<?php echo strtolower(htmlspecialchars($peserta['nama'])); ?>">
                                                            <td>
                                                                <div class="student-info">
                                                                    <img src="<?php
                                                                                if (!empty($peserta['photo_type'])) {
                                                                                    if ($peserta['photo_type'] === 'avatar') {
                                                                                        echo $peserta['photo_url'];
                                                                                    } else if ($peserta['photo_type'] === 'upload') {
                                                                                        echo 'uploads/profil/' . $peserta['foto_profil'];
                                                                                    }
                                                                                } else {
                                                                                    echo 'assets/pp.png';
                                                                                }
                                                                                ?>"
                                                                        class="profile-pic rounded-circle border shadow-sm"
                                                                        alt="Profile"
                                                                        style="object-fit: cover;">
                                                                    <span class="fw-medium"><?php echo htmlspecialchars($peserta['nama']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($peserta['attempted_questions'] > 0): ?>
                                                                    <span class="ios-badge" style="background-color: #e8f5e9; color: #2e7d32;">Selesai</span>
                                                                <?php else: ?>
                                                                    <span class="ios-badge" style="background-color: #f5f5f5; color: #757575;">Belum</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <span style="color: #2e7d32; font-weight: 600;"><?php echo $peserta['correct_answers']; ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span style="color: #d32f2f; font-weight: 600;"><?php echo $peserta['wrong_answers']; ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span style="color: #757575; font-weight: 600;"><?php echo $unattempted; ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="ios-badge" style="background-color: #fff3e0; color: rgb(218, 119, 86);">
                                                                    <?php echo number_format($nilai, 1); ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button class="ios-btn" style="background-color: rgb(218, 119, 86); color: white;"
                                                                    onclick="window.location.href='detail_jawaban.php?ujian_id=<?php echo $ujian_id; ?>&siswa_id=<?php echo $peserta['siswa_id']; ?>'">
                                                                    Lihat Detail
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Mobile View (Cards) -->
                                    <div class="d-md-none mt-4" id="cardContainer">
                                        <?php
                                        $result_peserta = mysqli_query($koneksi, $query_peserta);
                                        while ($peserta = mysqli_fetch_assoc($result_peserta)):
                                            $unattempted = $total_questions - ($peserta['correct_answers'] + $peserta['wrong_answers']);
                                            $nilai = ($peserta['correct_answers'] / $total_questions) * 100;
                                        ?>
                                            <div class="student-card mb-3" data-nama="<?php echo strtolower(htmlspecialchars($peserta['nama'])); ?>">
                                                <div class="card-header">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php
                                                                    if (!empty($peserta['photo_type'])) {
                                                                        if ($peserta['photo_type'] === 'avatar') {
                                                                            echo $peserta['photo_url'];
                                                                        } else if ($peserta['photo_type'] === 'upload') {
                                                                            echo 'uploads/profil/' . $peserta['foto_profil'];
                                                                        }
                                                                    } else {
                                                                        echo 'assets/pp.png';
                                                                    }
                                                                    ?>"
                                                            class="card-profile-pic"
                                                            alt="Profile">
                                                        <div class="ms-3">
                                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($peserta['nama']); ?></h6>
                                                            <?php if ($peserta['attempted_questions'] > 0): ?>
                                                                <span class="badge status-badge status-selesai">
                                                                    <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge status-badge status-belum">
                                                                    <i class="bi bi-clock me-1"></i>Belum Mengerjakan
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-stats">
                                                    <div class="stat-item border rounded-3 p-2 align-items-center" style="background-color:rgb(200, 100, 70); color:white;">
                                                        <span class="stat-value text-white"><?php echo number_format($nilai, 1); ?></span>
                                                        <span class="stat-label text-white">Nilai</span>
                                                    </div>
                                                    <div class="stat-item border rounded-3 p-2 align-items-center">
                                                        <span class="stat-value"><?php echo $peserta['correct_answers']; ?></span>
                                                        <span class="stat-label">Benar</span>
                                                    </div>
                                                    <div class="stat-item border rounded-3 p-2 align-items-center">
                                                        <span class="stat-value"><?php echo $peserta['wrong_answers']; ?></span>
                                                        <span class="stat-label">Salah</span>
                                                    </div>
                                                    <div class="stat-item border rounded-3 p-2 align-items-center">
                                                        <span class="stat-value"><?php echo $unattempted; ?></span>
                                                        <span class="stat-label">Belum</span>
                                                    </div>
                                                </div>
                                                <!-- <div class="card-footer">
                                                    <button class="btn btn-primary w-100 detail-button"
                                                        onclick="window.location.href='detail_jawaban.php?ujian_id=<?php echo $ujian_id; ?>&siswa_id=<?php echo $peserta['siswa_id']; ?>'">
                                                        <i class="bi bi-eye me-2"></i>Lihat Detail Jawaban
                                                    </button>
                                                </div> -->
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Data tidak tersedia</h4>
                <p>Tidak ada hasil ujian untuk ujian ini, pastikan ujian Anda telah selesai.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Floating Action Button for Mobile -->
    <div class="position-fixed bottom-0 end-0 me-4 d-md-none" style="z-index: 1000; margin-bottom: 5rem;">
        <button onclick="location.reload()" class="btn rounded-pill shadow-lg" style="width: 110px; height: 40px; background-color: rgb(218, 119, 86); color: white;">
            <i class="bi bi-arrow-clockwise fst-normal" style="font-size: 0.9rem;"><span class="me-1"></span>Perbarui</i>
        </button>
    </div>

    <!-- Canvas Analisis AI -->
    <div id="analisisCanvas" class="position-fixed top-0 end-0 h-100 bg-white shadow-lg" style="width: 350px; transform: translateX(100%); transition: transform 0.5s ease; z-index: 1050;">
        <div class="d-flex flex-column h-100">
            <div class="card-header d-flex p-4 pb-3 bg-white rounded-top-4 justify-content-between">
                <div class="d-flex">
                    <span class="bi bi-stars me-2" style="font-size: 30px; color:rgb(218, 119, 86)"></span>
                    <div>
                        <h5 class="mb-0">Analisis Hasil Ujian</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Integrasi Layanan SAGA AI</p>
                    </div>
                </div>
                <button id="closeAnalisis" class="btn-close"></button>
            </div>
            <div class="p-4 flex-grow-1 overflow-auto" id="analisisContent">
                <div id="konfirmasiAnalisis" class="py-2 px-2 text-center">
                    <div class="mb-4">
                        <img src="assets/analisis_wide.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
                    </div>
                    <h3 class="mb-3 fw-bold">Evaluasi Pembelajaran Anda Dengan Mudah</h3>
                    <p class="text-muted mb-4">Dapatkan kondisi, evaluasi, dan rekomendasi hasil ujian Anda dengan bantuan SAGA AI</p>
                    <div class="border rounded-4 p-3 mb-4 bg-light d-flex align-items-center">
                        <span class="bi bi-exclamation-circle pe-3"></span>
                        <p style="font-size: 12px;" class="p-0 m-0 text-start">Untuk hasil analisis sempurna, pastikan ujian Anda telah selesai dilaksanakan</p>
                    </div>
                    <div class="d-flex">
                        <button id="startAnalisis" class="btn btn-sm px-4 py-2 rounded-4 flex-fill" style="background-color: rgb(218, 119, 86); color: white;">
                            Analisis Sekarang
                        </button>
                    </div>
                </div>
                <div id="loadingAnalisis" class="text-center py-5 d-none">
                    <!-- <div class="spinner-border" style="color: rgb(218, 119, 86);" role="status"> -->
                    <img src="assets/ai_loading.gif" width="80px" alt="">
                    <!-- </div> -->
                    <p class="mt-3 text-muted" style="font-size:12px;">Sedang menganalisis data ujian Anda, sebentar lagi</p>
                </div>
                <div id="hasilAnalisis" class="d-none">
                    <!-- Hasil analisis akan muncul di sini dengan efek typing -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- script pencarian ujian siswa -->
    <script>
        // Search functionality
        document.getElementById('searchSiswa').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            // Untuk tabel desktop
            const tableRows = document.querySelectorAll('#tableSiswa tbody tr');
            tableRows.forEach(row => {
                const nama = row.getAttribute('data-nama');
                if (nama.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Untuk card mobile
            const cards = document.querySelectorAll('.student-card');
            cards.forEach(card => {
                const nama = card.getAttribute('data-nama');
                if (nama.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
    <script>
        // Variabel untuk API key Groq - ganti dengan API key Anda
        const groqApiKey = "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";

        // Fungsi untuk menampilkan canvas analisis
        document.getElementById('btnAnalisis').addEventListener('click', function() {
            document.getElementById('analisisCanvas').style.transform = 'translateX(0)';
        });

        // Fungsi untuk menutup canvas analisis
        document.getElementById('closeAnalisis').addEventListener('click', function() {
            document.getElementById('analisisCanvas').style.transform = 'translateX(100%)';
        });

        // Fungsi untuk memulai analisis
        document.getElementById('startAnalisis').addEventListener('click', async function() {
            document.getElementById('konfirmasiAnalisis').classList.add('d-none');
            document.getElementById('loadingAnalisis').classList.remove('d-none');

            try {
                // Mengumpulkan data untuk analisis
                const ujianData = {
                    judul: "<?php echo htmlspecialchars($ujian['judul']); ?>",
                    mataPelajaran: "<?php echo htmlspecialchars($ujian['mata_pelajaran']); ?>",
                    tingkat: "<?php echo htmlspecialchars($ujian['tingkat']); ?>",
                    rataRata: "<?php echo number_format($rata_rata, 1); ?>",
                    totalSiswa: "<?php echo mysqli_num_rows($result_peserta); ?>",
                    nilaiTertinggi: "<?php echo number_format($nilai_tertinggi, 1); ?>",
                    nilaiTerendah: "<?php echo number_format($nilai_terendah, 1); ?>",
                    soalStats: <?php echo json_encode($soal_stats); ?>
                };

                // Memanggil API Groq
                const hasil = await analyzeWithGroq(ujianData);

                // Menampilkan hasil dengan efek typing
                document.getElementById('loadingAnalisis').classList.add('d-none');
                document.getElementById('hasilAnalisis').classList.remove('d-none');
                typeWriter(hasil, 'hasilAnalisis');
            } catch (error) {
                console.error("Gagal menganalisis:", error);
                document.getElementById('loadingAnalisis').classList.add('d-none');
                document.getElementById('hasilAnalisis').classList.remove('d-none');
                document.getElementById('hasilAnalisis').innerHTML = `
            <div class="alert alert-danger">
                <h5>Terjadi Kesalahan</h5>
                <p>Tidak dapat melakukan analisis saat ini. Silakan coba lagi nanti.</p>
                <p>Detail error: ${error.message}</p>
            </div>
            <button id="retryAnalisis" class="btn btn-outline-secondary px-4 py-2 rounded-4">
                Coba Lagi
            </button>
        `;

                document.getElementById('retryAnalisis').addEventListener('click', function() {
                    document.getElementById('hasilAnalisis').classList.add('d-none');
                    document.getElementById('konfirmasiAnalisis').classList.remove('d-none');
                });
            }
        });

        async function analyzeWithGroq(ujianData) {
            // Mengumpulkan statistik soal yang paling banyak salah
            const topProblemSoal = ujianData.soalStats
                .sort((a, b) => parseInt(b.total_salah) - parseInt(a.total_salah))
                .slice(0, 5);

            // Mempersiapkan prompt untuk AI dengan format yang lebih jelas
            const prompt = `
    Analisis hasil ujian berikut dan berikan rekomendasi pembelajaran:
    
    Judul Ujian: ${ujianData.judul}
    Mata Pelajaran: ${ujianData.mataPelajaran}
    Tingkat Kelas: ${ujianData.tingkat}
    Nilai Rata-rata: ${ujianData.rataRata}/100
    Nilai Tertinggi: ${ujianData.nilaiTertinggi}/100
    Nilai Terendah: ${ujianData.nilaiTerendah}/100
    Total Siswa: ${ujianData.totalSiswa}
    
    Detail soal-soal dengan kesalahan terbanyak:
    ${topProblemSoal.map((soal, index) => `
    Soal ${index + 1}: ${soal.pertanyaan}
    Jawaban benar: ${soal.jawaban_benar}
    Total menjawab: ${soal.total_menjawab} siswa
    Total benar: ${soal.total_benar} siswa
    Total salah: ${soal.total_salah} siswa
    `).join('\n')}
    
    Berikan analisis lengkap dengan format berikut (gunakan format markdown dengan bold untuk poin penting):
    1. Materi yang sudah dikuasai dengan baik
    2. Materi yang perlu ditingkatkan
    3. Rekomendasi metode pengajaran untuk meningkatkan pemahaman pada materi yang kurang
    
    Berikan analisis yang spesifik berdasarkan data soal-soal, bukan analisis generik.
    `;

            try {
                const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${groqApiKey}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        model: "gemma2-9b-it",
                        messages: [{
                                role: "system",
                                content: "Kamu adalah asisten guru yang ahli dalam menganalisis hasil ujian dan memberikan rekomendasi pengajaran yang spesifik dan praktis. Analisis yang kamu berikan harus berdasarkan data statistik ujian dan soal-soal yang diberikan. Gunakan format markdown dengan headings dan list yang terstruktur."
                            },
                            {
                                role: "user",
                                content: prompt
                            }
                        ],
                        temperature: 0.5,
                        max_tokens: 1500
                    })
                });

                if (!response.ok) {
                    throw new Error(`API error: ${response.status}`);
                }

                const data = await response.json();
                return data.choices[0].message.content;
            } catch (error) {
                console.error("Error calling Groq API:", error);
                throw error;
            }
        }

        function typeWriter(text, elementId) {
            const element = document.getElementById(elementId);
            element.innerHTML = '';

            // Persiapkan container
            element.innerHTML = `
        <div class="analisis-result">
            <h3 class="mb-4" style="color: rgb(218, 119, 86);">Hasil Analisis</h3>
            <div id="typed-content"></div>
        </div>
    `;

            const typedContent = document.getElementById('typed-content');

            // Konversi markdown ke HTML dan simpan
            const htmlContent = marked.parse(text);

            // Tampilkan sedikit demi sedikit
            let i = 0;
            const speed = 7; // kecepatan ketik
            const htmlWithoutTags = htmlContent.replace(/<[^>]*>/g, '');

            function typeCharacter() {
                if (i < htmlWithoutTags.length) {
                    typedContent.innerHTML = marked.parse(text.substring(0, i));
                    i++;
                    setTimeout(typeCharacter, speed);
                }
            }

            typeCharacter();
        }

        function processMarkdownText(text) {
            // Tangani bold: **text** menjadi <strong>text</strong>
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Tangani italic: *text* menjadi <em>text</em>
            text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');

            return text;
        }
    </script>
    <script>
        // Script untuk Modal Pengaturan Excel
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil elemen switch dan status
            const bulatkanSwitch = document.getElementById('bulatkanNilai');
            const sembunyikanSwitch = document.getElementById('sembunyikanSiswa');
            const statusBulatkan = document.getElementById('statusBulatkan');
            const statusSembunyikan = document.getElementById('statusSembunyikan');
            const downloadBtn = document.getElementById('downloadExcel');

            // Event listener untuk switch pembulatan nilai
            bulatkanSwitch.addEventListener('change', function() {
                if (this.checked) {
                    statusBulatkan.textContent = 'Nilai belakang koma akan kami bulatkan, nilai di belakang koma di atas 6 maka akan dibulatkan ke nilai selanjutnya. Cth: 87.6 menjadi 88';
                    statusBulatkan.style.color = 'rgb(218, 119, 86)';
                    statusBulatkan.style.fontSize = '12px';
                } else {
                    statusBulatkan.textContent = 'Anda akan menerima nilai murni dengan koma';
                    statusBulatkan.style.color = '#6c757d';
                    style.statusBulatkan.style.fontSize = '12px';
                }
            });

            // Event listener untuk switch sembunyikan siswa
            sembunyikanSwitch.addEventListener('change', function() {
                if (this.checked) {
                    statusSembunyikan.textContent = 'Siswa yang tidak mengerjakan tidak terinput pada excel Anda';
                    statusSembunyikan.style.color = 'rgb(218, 119, 86)';
                    statusSembunyikan.style.fontSize = '12px';
                } else {
                    statusSembunyikan.textContent = 'Anda akan menerima seluruh data siswa';
                    statusSembunyikan.style.color = '#6c757d';
                    statusSembunyikan.style.fontSize = '12px';
                }
            });

            // Event listener untuk tombol download
            // Event listener untuk tombol download
            downloadBtn.addEventListener('click', function() {
                // Ambil nilai dari kedua switch
                const bulatkan = bulatkanSwitch.checked ? 1 : 0;
                const sembunyikan = sembunyikanSwitch.checked ? 1 : 0;

                // Buat URL dengan parameter pengaturan
                const ujianId = '<?php echo $ujian_id; ?>';
                const downloadUrl = `export_excel_ujian.php?ujian_id=${ujianId}&bulatkan=${bulatkan}&sembunyikan=${sembunyikan}`;

                // Tutup modal dengan cara yang lebih proper
                const modalElement = document.getElementById('excelConfigModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                // Event listener untuk memastikan modal benar-benar tertutup
                modalElement.addEventListener('hidden.bs.modal', function() {
                    // Download file setelah modal benar-benar tertutup
                    window.location.href = downloadUrl;

                    // Hapus backdrop yang mungkin masih nempel
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }

                    // Pastikan body tidak ada class modal-open
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, {
                    once: true
                }); // { once: true } memastikan event listener hanya jalan sekali

                // Tutup modal
                modal.hide();
            });
        });
    </script>
    </body>

</html>