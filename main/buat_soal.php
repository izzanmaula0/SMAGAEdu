    <?php
session_start();
require "koneksi.php";

// header untuk penetapan penyimpanan konten karakter unicode
header('Content-Type: text/html; charset=utf-8');
mysqli_set_charset($koneksi, 'utf8');

if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
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
                WHERE u.id = '$ujian_id' AND u.guru_id = '$userid'";
$result_ujian = mysqli_query($koneksi, $query_ujian);

// Cek apakah ujian ditemukan dan milik guru tersebut 
if (mysqli_num_rows($result_ujian) == 0) {
    header("Location: ujian_guru.php");
    exit();
}

$ujian = mysqli_fetch_assoc($result_ujian);


// Ambil jumlah soal yang sudah dibuat
$query_soal = "SELECT COUNT(*) as total_soal FROM bank_soal WHERE ujian_id = '$ujian_id'";
$result_soal = mysqli_query($koneksi, $query_soal);
$total_soal = mysqli_fetch_assoc($result_soal)['total_soal'];

// Cek apakah ada hasil ujian untuk ujian ini
$query_cek_hasil = "SELECT COUNT(*) as jumlah FROM jawaban_ujian WHERE ujian_id = '$ujian_id'";
$result_cek_hasil = mysqli_query($koneksi, $query_cek_hasil);
$data_hasil = mysqli_fetch_assoc($result_cek_hasil);
$ada_hasil_ujian = ($data_hasil['jumlah'] > 0);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <script src="https: //unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module">
    </script>
    <link rel="icon" type="image/png" href="assets/tab.png">

    <!-- MathJax for formula rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"
        onload="renderMathInElement(document.body);"></script>
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)']
                ],
                displayMath: [
                    ['$$', '$$']
                ],
                processEscapes: true,
                autoload: {
                    color: [],
                    colorV2: ['color']
                },
                packages: {
                    '[+]': ['noerrors']
                }
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'img'],
                ignoreHtmlClass: 'formula-image'
            }
        };
    </script>

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

    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        window.addEventListener('load', function() {
            if (typeof MathJax !== 'undefined') {
                MathJax.typeset();
            }
        });
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


    <!-- Main Content -->
    <div class="col p-4 col-utama">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Buat Soal</h3>
            <div class="d-flex gap-2">
                <!-- Soal Dropdown Button -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 15px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus-circle me-1"></i>
                        <span class="fw-medium d-none d-md-block" style="font-size: 13px;">Soal</span>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu rounded-3">
                        <li>
                            <h6 class="dropdown-header">Buat Soal</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" onclick="pilihTipeSoal('pilihan_ganda')">
                                <div>
                                    <i class="ti ti-pencil-plus me-2"></i>Buat Manual
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Buat soal secara manual dengan editor</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" data-bs-toggle="modal" data-bs-target="#aiSoalModal">
                                <div>
                                    <i class="ti ti-sparkles me-2"></i>Gunakan SAGA
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Buat soal otomatis dengan SAGA AI</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" data-bs-toggle="modal" data-bs-target="#uploadSoalModal">
                                <div>
                                    <i class="ti ti-file-word me-2"></i>Impor dari Word
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Impor soal dari file Word (.docx)</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <h6 class="dropdown-header">Edit Soal</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" onclick="bukaDeskripsiSoalModal()">
                                <div>
                                    <i class="ti ti-layers-linked me-2"></i>Tambah Deskripsi
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Tambahkan cerita/deskripsi untuk beberapa soal</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <h6 class="dropdown-header text-danger">Zona Bahaya</h6>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" style="font-size: 14px;" href="#" onclick="hapusSemuaSoal()">
                                <div>
                                    <i class="ti ti-trash me-2"></i>Hapus Semua Soal
                                    <div class="text-danger" style="font-size: 11px; margin-left: 22px;">Hapus seluruh soal dalam ujian ini</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Ujian Dropdown Button -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 15px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-pen me-1"></i>
                        <span class="fw-medium d-none d-md-block" style="font-size: 13px;">Ujian</span>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" data-bs-toggle="modal" data-bs-target="#editIdentitasModal">
                                <div>
                                    <i class="ti ti-pencil me-2"></i>Edit Ujian
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Ubah identitas dan pengaturan ujian</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" onclick="showExportOptions()">
                                <div>
                                    <i class="ti ti-file-download me-2"></i>Unduh Ujian
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Ekspor soal ujian ke dokumen Word</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" style="font-size: 14px;" href="#" onclick="openPreviewUjian()">
                                <div>
                                    <i class="ti ti-eye me-2"></i>Preview Ujian
                                    <div class="text-muted" style="font-size: 11px; margin-left: 22px;">Simulasikan ujian dari sisi siswa</div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <h6 class="dropdown-header text-danger">Zona Bahaya</h6>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" style="font-size: 14px;" href="#" onclick="hapusUjian()">
                                <div>
                                    <i class="ti ti-trash me-2"></i>Hapus Ujian
                                    <div class="text-danger" style="font-size: 11px; margin-left: 22px;">Hapus ujian ini permanen</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Add function to delete all questions -->
            <script>
                function hapusSemuaSoal() {
                    // Create confirmation modal
                    const modalHtml = `
                <div class="modal fade" id="hapusSemuaSoalModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px;">
                            <div class="modal-body text-center p-4">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem; color:rgb(220, 53, 69);"></i>
                                <h5 class="mt-3 fw-bold">Hapus Semua Soal</h5>
                                <p class="mb-4">Apakah Anda yakin ingin menghapus SEMUA soal dalam ujian ini? Tindakan ini tidak dapat dibatalkan.</p>
                                <div class="d-flex w-100 gap-0">
                                    <button type="button" class="btn border flex-fill me-2" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                    <button type="button" class="btn btn-danger flex-fill" id="konfirmasiHapusSemua" style="border-radius: 12px;">
                                        <span>Hapus Semua</span>
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
                    const modalElement = document.getElementById('hapusSemuaSoalModal');
                    const modal = new bootstrap.Modal(modalElement);

                    // Show modal
                    modal.show();

                    // Handle delete confirmation
                    document.getElementById('konfirmasiHapusSemua').addEventListener('click', async () => {
                        const button = document.getElementById('konfirmasiHapusSemua');
                        const spinner = button.querySelector('.spinner-border');
                        const buttonText = button.querySelector('span');

                        try {
                            // Update button state
                            button.disabled = true;
                            spinner.classList.remove('d-none');
                            buttonText.textContent = 'Menghapus...';

                            const response = await fetch('hapus_semua_soal.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    ujian_id: <?php echo $ujian_id; ?>
                                })
                            });

                            const result = await response.json();
                            if (result.status === 'success') {
                                location.reload();
                            } else {
                                throw new Error(result.message || 'Terjadi kesalahan saat menghapus soal');
                            }
                        } catch (error) {
                            alert('Gagal menghapus soal: ' + error.message);
                        } finally {
                            // Reset button state
                            button.disabled = false;
                            spinner.classList.add('d-none');
                            buttonText.textContent = 'Hapus Semua';

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

            <!-- script hapus semua ujian -->
            <script>
                // Fungsi untuk menghapus ujian secara permanen
                function hapusUjian() {
                    // Buat modal konfirmasi
                    const modalHtml = `
    <div class="modal fade" id="hapusUjianModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem; color:rgb(220, 53, 69);"></i>
                    <h5 class="mt-3 fw-bold">Hapus Ujian</h5>
                    <p class="mb-4">Apakah Anda yakin ingin menghapus ujian ini secara permanen? Semua soal dan data terkait akan dihapus.</p>
                    <div class="d-flex w-100 gap-0">
                        <button type="button" class="btn border flex-fill me-2" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <button type="button" class="btn btn-danger flex-fill" id="konfirmasiHapusUjian" style="border-radius: 12px;">
                            <span>Hapus Ujian</span>
                            <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;

                    // Tambahkan modal ke dokumen
                    document.body.insertAdjacentHTML('beforeend', modalHtml);

                    // Ambil elemen modal
                    const modalElement = document.getElementById('hapusUjianModal');
                    const modal = new bootstrap.Modal(modalElement);

                    // Tampilkan modal
                    modal.show();

                    // Handle delete confirmation
                    document.getElementById('konfirmasiHapusUjian').addEventListener('click', () => {
                        const button = document.getElementById('konfirmasiHapusUjian');
                        const spinner = button.querySelector('.spinner-border');
                        const buttonText = button.querySelector('span');

                        // Update status tombol
                        button.disabled = true;
                        spinner.classList.remove('d-none');
                        buttonText.textContent = 'Menghapus...';

                        // Redirect ke hapus_ujian.php dengan ID ujian
                        window.location.href = 'hapus_ujian.php?id=<?php echo $ujian_id; ?>';
                    });

                    // Hapus modal dari DOM saat disembunyikan
                    modalElement.addEventListener('hidden.bs.modal', () => {
                        modalElement.remove();
                    });
                }
            </script>

            <!-- script untuk preview ujian -->
            <script>
                // Fungsi untuk membuka preview ujian
                function openPreviewUjian() {
                    const ujianId = <?php echo $ujian_id; ?>;
                    const previewUrl = `preview_ujian.php?id=${ujianId}`;
                    window.open(previewUrl, '_blank');
                }

                // Tambah event listener untuk tombol preview
                document.addEventListener('DOMContentLoaded', function() {
                    const previewButton = document.querySelector('.btn-action i.bi-eye').closest('button');
                    if (previewButton) {
                        previewButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            openPreviewUjian();
                        });
                    }
                });
            </script>

            <style>
                .btn-action {
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                    border: 1px rgb(214, 214, 214) solid;
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

                /* Dropdown submenu */
                .dropdown-submenu {
                    position: relative;
                }

                .dropdown-submenu .dropdown-menu {
                    top: 0;
                    left: 100%;
                    margin-top: -1px;
                }

                .dropdown-submenu:hover .dropdown-menu {
                    display: block;
                }

                /* Animasi blur dan loader untuk card */
                .soal-card.ai-processing {
                    position: relative;
                    overflow: hidden;
                }

                .soal-card.ai-processing::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.8);
                    backdrop-filter: blur(5px);
                    z-index: 10;
                    animation: fadeIn 0.3s ease;
                }

                .soal-card .ai-loader-overlay {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 11;
                    display: none;
                }

                .soal-card.ai-processing .ai-loader-overlay {
                    display: block;
                }

                .ai-loader-overlay .spinner-border {
                    width: 3rem;
                    height: 3rem;
                    border-width: 0.3em;
                    color: rgb(218, 119, 86);
                }

                /* Hasil AI */
                .ai-result {
                    margin-top: 15px;
                    padding: 15px;
                    animation: slideDown 0.3s ease;
                }

                .ai-result-analysis {}

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }

                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes unblur {
                    from {
                        filter: blur(5px);
                        opacity: 0.5;
                    }

                    to {
                        filter: blur(0);
                        opacity: 1;
                    }
                }

                .soal-card.ai-complete {
                    animation: unblur 0.5s ease;
                }

                /* // Animasi slideUp untuk menutup */
                @keyframes slideUp {
                    to {
                        opacity: 0;
                        transform: translateY(-10 px);
                    }
                }
            </style>
            </style>
        </div>
        <!-- Popup for development notification -->
        <script>
            // Ganti fungsi showDevPopup() dengan fungsi baru
            function showExportOptions() {
                // Create modal dynamically
                const modalHtml = `
        <div class="modal fade" id="exportOptionsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" style="font-size: 20px;">Ekspor Ujian</h5>
                        <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Pilih jenis format ekspor sesuai tingkat sekolah:</p>

                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Mungkin Beberapa Elemen Tidak Sesuai</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Anda dapat menyesuaikan mandiri pada dokumen soal yang telah Anda terunduh.</p>
                            </div>
                        </div>
                    </div>
                        
                        <div class="d-flex flex-column flex-md-row gap-3 w-100">
                            <button type="button" class="btn border px-4 py-3 flex-fill" style="border-radius: 12px;" onclick="exportUjian('smp')">
                                <i class="bi bi-file-earmark-word mb-2" style="font-size: 24px;"></i>
                                <div>Format SMP</div>
                            </button>
                            <button type="button" class="btn border px-4 py-3 flex-fill" style="border-radius: 12px" onclick="exportUjian('sma')">
                                <i class="bi bi-file-earmark-word mb-2" style="font-size: 24px;"></i>
                                <div>Format SMA</div>
                                <small class="d-block mt-1"></small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('exportOptionsModal'));
                modal.show();

                // Remove modal from DOM when hidden
                document.getElementById('exportOptionsModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }

            // Fungsi untuk mengeksport ujian sesuai jenis sekolah
            function exportUjian(jenisSekolah) {
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('exportOptionsModal'));
                modal.hide();

                // Tampilkan loading spinner
                const overlay = document.getElementById('generateOverlay');
                overlay.classList.add('fade-in');
                overlay.style.display = 'flex';

                // Redirect ke halaman ekspor dengan parameter yang diperlukan
                window.location.href = `export_word.php?ujian_id=<?php echo $ujian_id; ?>&jenis=${jenisSekolah}`;

                // Sembunyikan overlay setelah 3 detik (asumsi file sudah diunduh)
                setTimeout(() => {
                    overlay.classList.remove('fade-in');
                    overlay.classList.add('fade-out');
                    setTimeout(() => {
                        overlay.style.display = 'none';
                        overlay.classList.remove('fade-out');
                    }, 500);
                }, 3000);
            }
        </script>

        <!-- Info Ujian -->
        <div class="card border mb-4" style="border-radius: 16px; background: #fff;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4"
                    style="cursor: pointer;"
                    data-bs-toggle="collapse"
                    data-bs-target="#detailUjian"
                    class="d-md-none">
                    <h5 class="card-title m-0 d-flex align-items-center gap-2" style="font-size: 18px; font-weight: 600;">
                        <?php echo htmlspecialchars($ujian['judul']); ?>
                    </h5>
                    <!-- Toggle button only shown on mobile -->
                    <button class="btn d-md-none rounded-circle p-2" type="button">
                        <i class="bi bi-chevron-down " style="color: rgb(218, 119, 86);"></i>
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
                                        <i class="ti ti-book"></i>
                                    </div>
                                    <div>
                                        <div class="">
                                            <div class="label m-0 p-0">Mata Pelajaran</div>
                                            <span class="bi bi-stars border rounded px-2 text-muted" style="font-size: 12px;">SAGA AI mengakses data ini</span>
                                        </div>
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
                                        <div class="value">
                                            <?php
                                            if (!empty($ujian['deskripsi'])) {
                                                echo htmlspecialchars($ujian['deskripsi']);
                                            } else {
                                                echo '<span class="text-muted">Anda tidak menambahkan deskripsi</span>';
                                            }
                                            ?>
                                        </div>
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
                                            <div class="label m-0">Materi Ujian</div>
                                            <span class="bi bi-stars border rounded px-2 text-muted" style="font-size: 12px;">SAGA AI mengakses data ini</span>
                                            <div class="value">
                                                <ul class="list-unstyled mb-0 mt-2">
                                                    <?php
                                                    $materi_list = json_decode($ujian['materi'], true);
                                                    if (is_array($materi_list) && !empty($materi_list)) {
                                                        echo '<ul class="list-unstyled mb-0 mt-2">';
                                                        foreach ($materi_list as $materi) {
                                                            echo "<li class='mb-2'><i class='bi bi-dot me-2'></i>" . htmlspecialchars($materi) . "</li>";
                                                        }
                                                        echo '</ul>';
                                                    } else {
                                                        echo '<span class="text-muted">Anda tidak menambahkan materi</span>';
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
                            <!-- kelas yang diujikan -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-wrapper">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <div class="label">Kelas</div>
                                        <?php
                                        // Get class name from kelas_id
                                        $kelas_id = $ujian['kelas_id'];
                                        $query_kelas = "SELECT * FROM kelas WHERE id = '$kelas_id'";
                                        $result_kelas = mysqli_query($koneksi, $query_kelas);
                                        $kelas_data = mysqli_fetch_assoc($result_kelas);
                                        $kelas_name = $kelas_data ? htmlspecialchars($kelas_data['tingkat'] . ' - ' . $kelas_data['nama_kelas']) : 'Tidak ditemukan';
                                        ?>
                                        <div class="value"><?php echo $kelas_name; ?></div>
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

            /* Style untuk tombol toggle versi */
            .ai-navigation .btn-outline-primary {
                border-color: rgb(218, 119, 86);
                color: rgb(218, 119, 86);
            }

            .ai-navigation .btn-outline-primary:hover {
                background-color: rgb(218, 119, 86);
                border-color: rgb(218, 119, 86);
                color: white;
            }

            /* Animasi transisi untuk perubahan konten */
            .soal-card p,
            .soal-card .pilihan {
                transition: all 0.3s ease;
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

            /* Style untuk map navigasi soal */
            .sticky-map {
                position: sticky;
                top: 20px;
                border-radius: 16px;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                margin-bottom: 20px;
                z-index: 100;
                /* Pastikan elemen berada di atas konten lain */
            }

            .soal-map-container {
                overflow: visible !important;
            }

            .col-md-3 .soal-map-container {
                min-height: 100px;
            }

            .soal-map {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                max-height: calc(100vh - 200px);
                overflow-y: auto;
                padding-right: 5px;
                justify-content: flex-start;
            }

            .soal-map::-webkit-scrollbar {
                width: 4px;
            }

            .soal-map::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .soal-map::-webkit-scrollbar-thumb {
                background: #d3d3d3;
                border-radius: 10px;
            }

            .soal-map::-webkit-scrollbar-thumb:hover {
                background: #c1c1c1;
            }

            .soal-number {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 8px;
                background: #f5f5f5;
                border: 1px solid #e0e0e0;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .soal-number:hover {
                background-color: rgba(218, 119, 86, 0.1);
                border-color: rgb(218, 119, 86);
                /* transform: translateY(-2px); */
            }

            .soal-number.with-description {
                background-color: rgba(218, 119, 86, 0.1);
                border-color: rgb(218, 119, 86);
            }

            .soal-description-indicator {
                position: absolute;
                top: -3px;
                right: -3px;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: rgb(218, 119, 86);
            }

            @media (max-width: 768px) {
                .sticky-map {
                    position: relative;
                    top: 0;
                    margin-bottom: 20px;
                }

                .soal-map {
                    max-height: 200px;
                }
            }

            /* Highlight effect untuk soal yang dipilih */
            @keyframes highlightPulse {
                0% {
                    background-color: #fff;
                    box-shadow: 0 0 0 rgba(218, 119, 86, 0);
                    border-color: rgba(218, 119, 86, 0.1);
                }

                50% {
                    background-color: rgba(218, 119, 86, 0.25);
                    box-shadow: 0 0 20px rgba(218, 119, 86, 0.4);
                    border-color: rgba(218, 119, 86, 0.8);
                    transform: translateY(-3px);
                }

                100% {
                    background-color: #fff;
                    box-shadow: 0 0 0 rgba(218, 119, 86, 0);
                    border-color: rgba(218, 119, 86, 0.1);
                }
            }

            .highlight-soal {
                animation: highlightPulse 1.5s ease;
                border: 1px solid rgba(218, 119, 86, 0.3);
                position: relative;
                z-index: 10;
            }

            #pertanyaan {
                resize: vertical;
                /* Izinkan resize manual juga */
                min-height: 100px;
                /* Tinggi minimum */
                overflow-y: hidden;
                /* Sembunyikan scrollbar vertikal */
                transition: height 0.2s ease;
                /* Animasi perubahan tinggi */
            }

            /* Style untuk soal yang dimodifikasi AI */
            .soal-card.ai-modified {
                border: 2px solid rgba(218, 119, 86, 0.3);
                box-shadow: 0 4px 12px rgba(218, 119, 86, 0.1);
                transition: all 0.3s ease;
            }

            /* Style untuk navigasi AI */
            .ai-navigation {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border: 1px solid rgba(218, 119, 86, 0.2);
                animation: fadeInUp 0.3s ease-out;
            }

            .ai-navigation .form-check-input:checked {
                background-color: rgb(218, 119, 86);
                border-color: rgb(218, 119, 86);
            }

            .ai-navigation .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            /* Animation */
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

            /* Style untuk alert sukses */
            .alert-success {
                border-radius: 12px;
                border: none;
                box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
            }
        </style>

        <div class="row">
            <div class="col-md-9">
                <!-- Daftar Soal -->
                <div id="daftarSoal" class="row">
                    <?php
                    // Query untuk mengambil deskripsi dan soal
                    $query_descriptions = "SELECT d.id, d.title, d.content FROM soal_descriptions d 
                  WHERE d.ujian_id = '$ujian_id'";
                    $result_descriptions = mysqli_query($koneksi, $query_descriptions);
                    $descriptions = [];
                    while ($desc = mysqli_fetch_assoc($result_descriptions)) {
                        $descriptions[$desc['id']] = $desc;
                    }

                    // Ambil soal dan kelompokkan berdasarkan deskripsi
                    $query_soal_list = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id' ORDER BY description_id ASC, id ASC";
                    $result_soal_list = mysqli_query($koneksi, $query_soal_list);

                    // Kelompokkan soal berdasarkan description_id
                    $soal_grouped = [];
                    $soal_without_desc = [];

                    while ($soal = mysqli_fetch_assoc($result_soal_list)) {
                        if (!empty($soal['description_id'])) {
                            $soal_grouped[$soal['description_id']][] = $soal;
                        } else {
                            $soal_without_desc[] = $soal;
                        }
                    }

                    // nomor soal global
                    $no = 1;

                    if (empty($soal_without_desc) && empty($soal_grouped)) {
                        echo '<div class="col-12 text-center py-5">';
                        echo '<div class="empty-state p-4">';
                        echo '<h5 class="mt-3 fw-bold">Belum Ada Soal</h5>';
                        echo '<p class="text-muted small mb-4">Mulai dengan membuat soal untuk ujian ini</p>';
                        echo '<button class="btn rounded-4 border px-3 py-2" onclick="pilihTipeSoal(\'pilihan_ganda\')"><i class="bi bi-plus-circle me-1"></i>Buat Soal</button>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        foreach ($descriptions as $desc_id => $desc) {
                            if (isset($soal_grouped[$desc_id])) {
                                echo '<div class="col-12 mb-4">';
                                echo '<div class="card description-group-card" style="border-radius: 20px; border: 2px solid rgb(218, 119, 86);">';
                                echo '<div class="card-header bg-light" style="border-bottom: 1px solid rgba(0,0,0,0.1); border-radius: 20px 20px 0 0;">';
                                echo '<div class="d-flex justify-content-between align-items-center">';
                                echo '<h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2" style="color: rgb(218, 119, 86);"></i>' . htmlspecialchars($desc['title']) . '</h5>';
                                echo '<div>';
                                echo '<button class="btn btn-sm btn-outline-primary text-black me-1" onclick="editDeskripsi(' . $desc_id . ')"><i class="ti ti-pencil"></i></button>';
                                echo '<button class="btn btn-sm btn-outline-danger" onclick="hapusDeskripsi(' . $desc_id . ')"><i class="ti ti-trash"></i></button>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="card-body p-3">';
                                echo '<div class="description-content p-3 mb-3 bg-white rounded-3 border">';
                                echo nl2br(htmlspecialchars($desc['content']));
                                echo '</div>';
                                echo '<div class="soal-list">';

                                $no_dalam_group = 1;
                                foreach ($soal_grouped[$desc_id] as $soal) {
                                    echo '<div class="soal-card mb-3 border" style="background-color: #f9f9f9;">';
                                    // Isi soal
                                    echo '<div class="d-flex justify-content-between align-items-center mb-3">';
                                    echo '<h5 class="d-flex align-items-center gap-2">Soal ' . $no++ . '</h5>'; // Menggunakan counter global
                                    // Di bagian tampilan soal, ganti div tombol action menjadi:
                                    echo '<div class="d-flex gap-2">';
                                    echo '<button class="btn btn-action edit-button" style="background-color: rgba(218, 119, 86, 0.1);" onclick="editSoal(' . $soal['id'] . ')">';
                                    echo '<i class="ti ti-pencil" style="color: #000; font-size: 14px;"></i>';
                                    echo '</button>';
                                    // Tombol Duplikasi
                                    echo '<button class="btn btn-action duplicate-button" style="background-color: rgba(218, 119, 86, 0.1);" onclick="duplikasiSoal(' . $soal['id'] . ')">';
                                    echo '<i class="ti ti-copy" style="color: #000; font-size: 14px;"></i>';
                                    echo '</button>';
                                    // Tombol AI dengan dropdown
                                    echo '<div class="dropdown">';
                                    echo '<button class="btn btn-action ai-button" style="background-color: rgba(218, 119, 86, 0.1);" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="aiBtn' . $soal['id'] . '">';
                                    echo '<i class="bi bi-stars" style="color: #000; font-size: 14px;"></i>';
                                    echo '</button>';
                                    // Ganti bagian dropdown menu menjadi:
                                    echo '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aiBtn' . $soal['id'] . '">';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiPerpanjang(' . $soal['id'] . ', event)"><i class="ti ti-arrows-maximize me-2"></i>Perpanjang</a></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiPerpendek(' . $soal['id'] . ', event)"><i class="ti ti-arrows-minimize me-2"></i>Perpendek</a></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiParafrase(' . $soal['id'] . ', event)"><i class="ti ti-refresh me-2"></i>Parafrase</a></li>';
                                    echo '<li class="dropdown-submenu">';
                                    echo '<a class="dropdown-item dropdown-toggle" href="javascript:void(0)"><i class="ti ti-language me-2"></i>Terjemahkan</a>';
                                    echo '<ul class="dropdown-menu">';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'en\', event)">🇬🇧 Inggris</a></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'jv\', event)">🇮🇩 Jawa</a></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'zh\', event)">🇨🇳 Tiongkok</a></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'id\', event)">🇮🇩 Indonesia</a></li>';
                                    echo '</ul>';
                                    echo '</li>';
                                    echo '<li><hr class="dropdown-divider"></li>';
                                    echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiAnalisa(' . $soal['id'] . ', event)"><i class="ti ti-analyze me-2"></i>Analisa</a></li>';
                                    echo '</ul>';
                                    echo '</div>';
                                    // Tombol hapus dan preview tetap sama
                                    echo '<button class="btn btn-action trash" style="background-color: rgba(218, 119, 86, 0.1);" onclick="hapusSoal(' . $soal['id'] . ')">';
                                    echo '<i class="ti ti-trash" style="color: #000; font-size: 14px;"></i>';
                                    echo '</button>';
                                    echo '<button class="btn btn-action border edit-button ms-1" style="background-color: rgba(218, 119, 86, 0.1);" onclick="previewSoal(' . $soal['id'] . ')">';
                                    echo '<i class="ti ti-eye" style="color: #000; font-size: 14px;"></i>';
                                    echo '</button>';
                                    echo '</div>';
                                    echo '</div>';

                                    if (!empty($soal['gambar_soal'])) {
                                        echo '<div class="mb-3">';
                                        echo '<img src="' . htmlspecialchars($soal['gambar_soal']) . '" class="img-fluid rounded-3 text-start border" style="max-height: 200px; width: auto;margin: 0 auto;">';
                                        echo '</div>';
                                    }

                                    echo '<p>' . $soal['pertanyaan'] . '</p>';


                                    if ($soal['jenis_soal'] == 'pilihan_ganda') {
                                        echo '<div class="d-flex flex-column gap-2">';

                                        // Pilihan A
                                        // Untuk soal tanpa deskripsi
                                        echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'A' ? 'correct' : '') . '">';
                                        echo 'A. ' . $soal['jawaban_a'];
                                        if (!empty($soal['gambar_jawaban_a']) && strpos($soal['jawaban_a'], '<img') === false) {
                                            echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_a']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                        }
                                        echo '</div>';
                                        // Untuk soal tanpa deskripsi
                                        echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'B' ? 'correct' : '') . '">';
                                        echo 'B. ' . $soal['jawaban_b'];
                                        if (!empty($soal['gambar_jawaban_b']) && strpos($soal['jawaban_b'], '<img') === false) {
                                            echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_b']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                        }
                                        echo '</div>';

                                        // Pilihan C
                                        echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'C' ? 'correct' : '') . '">';
                                        echo 'C. ' . $soal['jawaban_c'];
                                        if (!empty($soal['gambar_jawaban_c']) && strpos($soal['jawaban_c'], '<img') === false) {
                                            echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_c']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                        }
                                        echo '</div>';

                                        // Pilihan D
                                        echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'D' ? 'correct' : '') . '">';
                                        echo 'D. ' . $soal['jawaban_d'];
                                        if (!empty($soal['gambar_jawaban_d']) && strpos($soal['jawaban_d'], '<img') === false) {
                                            echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_d']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                        }
                                        echo '</div>';

                                        echo '</div>';
                                    }

                                    echo '</div>'; // end soal-card
                                }

                                echo '</div>'; // end soal-list
                                echo '</div>'; // end card-body
                                echo '</div>'; // end card
                                echo '</div>'; // end col-12
                            }
                        }

                        // Tampilkan soal tanpa deskripsi
                        foreach ($soal_without_desc as $soal) {
                            echo '<div class="col-12">';
                            echo '<div class="soal-card mb-3 border" style="background-color: #f9f9f9;">';
                            // Isi soal
                            echo '<div class="d-flex justify-content-between align-items-center mb-3">';
                            echo '<h5 class="d-flex align-items-center gap-2">Soal ' . $no++ . '</h5>'; // Menggunakan counter global
                            // Di bagian tampilan soal, ganti div tombol action menjadi:
                            echo '<div class="d-flex gap-2">';
                            echo '<button class="btn btn-action edit-button" style="background-color: rgba(218, 119, 86, 0.1);" onclick="editSoal(' . $soal['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Soal">';
                            echo '<i class="ti ti-pencil" style="color: #000; font-size: 14px;"></i>';
                            echo '</button>';
                            // Tombol Duplikasi  
                            echo '<button class="btn btn-action duplicate-button" style="background-color: rgba(218, 119, 86, 0.1);" onclick="duplikasiSoal(' . $soal['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplikasi Soal">';
                            echo '<i class="ti ti-copy" style="color: #000; font-size: 14px;"></i>';
                            echo '</button>';
                            // Tombol AI dengan dropdown
                            echo '<div class="dropdown">';
                            echo '<button class="btn btn-action ai-button" style="background-color: rgba(218, 119, 86, 0.1);" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true" id="aiBtn' . $soal['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="AI Assistant">';
                            echo '<i class="bi bi-stars" style="color: #000; font-size: 14px;"></i>';
                            echo '</button>';
                            // Ganti bagian dropdown menu menjadi:
                            echo '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aiBtn' . $soal['id'] . '">';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiPerpanjang(' . $soal['id'] . ', event)"><i class="ti ti-arrows-maximize me-2"></i>Perpanjang</a></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiPerpendek(' . $soal['id'] . ', event)"><i class="ti ti-arrows-minimize me-2"></i>Perpendek</a></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiParafrase(' . $soal['id'] . ', event)"><i class="ti ti-refresh me-2"></i>Sempurnakan</a></li>';
                            echo '<li class="dropdown-submenu">';
                            echo '<a class="dropdown-item dropdown-toggle" href="javascript:void(0)"><i class="ti ti-language me-2"></i>Terjemahkan</a>';
                            echo '<ul class="dropdown-menu">';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'en\', event)">🇬🇧 Inggris</a></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'jv\', event)">🇮🇩 Jawa</a></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'zh\', event)">🇨🇳 Tiongkok</a></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiTerjemah(' . $soal['id'] . ', \'id\', event)">🇮🇩 Indonesia</a></li>';
                            echo '</ul>';
                            echo '</li>';
                            echo '<li><hr class="dropdown-divider"></li>';
                            echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="aiAnalisa(' . $soal['id'] . ', event)"><i class="ti ti-analyze me-2"></i>Analisa</a></li>';
                            echo '</ul>';
                            echo '</div>';
                            // Tombol hapus dan preview tetap sama  
                            echo '<button class="btn btn-action trash" style="background-color: rgba(218, 119, 86, 0.1);" onclick="hapusSoal(' . $soal['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Soal">';
                            echo '<i class="ti ti-trash" style="color: #000; font-size: 14px;"></i>';
                            echo '</button>';
                            echo '<button class="btn btn-action border edit-button ms-1" style="background-color: rgba(218, 119, 86, 0.1);" onclick="previewSoal(' . $soal['id'] . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Preview Soal">';
                            echo '<i class="ti ti-eye" style="color: #000; font-size: 14px;"></i>';
                            echo '</button>';
                            echo '</div>';
                            echo '</div>';

                            if (!empty($soal['gambar_soal'])) {
                                echo '<div class="mb-3">';
                                echo '<img src="' . htmlspecialchars($soal['gambar_soal']) . '" class="img-fluid rounded-3 text-start border" style="max-height: 200px; width: auto;margin: 0 auto;">';
                                echo '</div>';
                            }

                            echo '<p>' . $soal['pertanyaan'] . '</p>';

                            if ($soal['jenis_soal'] == 'pilihan_ganda') {
                                echo '<div class="d-flex flex-column gap-2">';

                                // A
                                echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'A' ? 'correct' : '') . '">';
                                echo 'A. ' . $soal['jawaban_a'];
                                if (!empty($soal['gambar_jawaban_a']) && strpos($soal['jawaban_a'], '<img') === false) {
                                    echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_a']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                }
                                echo '</div>';

                                // Pilihan B
                                // Untuk soal tanpa deskripsi
                                echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'B' ? 'correct' : '') . '">';
                                echo 'B. ' . $soal['jawaban_b'];
                                if (!empty($soal['gambar_jawaban_b']) && strpos($soal['jawaban_b'], '<img') === false) {
                                    echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_b']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                }
                                echo '</div>';

                                // Pilihan C
                                // Untuk soal tanpa deskripsi
                                echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'C' ? 'correct' : '') . '">';
                                echo 'C. ' . $soal['jawaban_c'];
                                if (!empty($soal['gambar_jawaban_c']) && strpos($soal['jawaban_c'], '<img') === false) {
                                    echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_c']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                }
                                echo '</div>';

                                // Pilihan D
                                // Untuk soal tanpa deskripsi
                                echo '<div class="pilihan ' . ($soal['jawaban_benar'] == 'D' ? 'correct' : '') . '">';
                                echo 'D. ' . $soal['jawaban_d'];
                                if (!empty($soal['gambar_jawaban_d']) && strpos($soal['jawaban_d'], '<img') === false) {
                                    echo '<div class="mt-2"><img src="' . htmlspecialchars($soal['gambar_jawaban_d']) . '" class="img-fluid rounded border" style="max-height: 150px;"></div>';
                                }
                                echo '</div>';

                                echo '</div>';
                            }

                            echo '</div>'; // end soal-card
                            echo '</div>'; // end col-12
                        }
                    }
                    ?>
                </div>
            </div>


            <script>
                // Enhanced dropdown fix
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize all dropdowns properly
                    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle, [data-bs-toggle="dropdown"]'));
                    const dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                        return new bootstrap.Dropdown(dropdownToggleEl, {
                            autoClose: 'outside', // Close when clicking outside
                            boundary: 'viewport'
                        });
                    });

                    // Additional manual handling for stubborn dropdowns
                    document.addEventListener('click', function(event) {
                        // Get all currently open dropdowns
                        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');

                        openDropdowns.forEach(function(dropdownMenu) {
                            const dropdownParent = dropdownMenu.closest('.dropdown');

                            // If click is outside the dropdown parent
                            if (dropdownParent && !dropdownParent.contains(event.target)) {
                                // Find the toggle button and hide dropdown
                                const toggle = dropdownParent.querySelector('[data-bs-toggle="dropdown"]');
                                if (toggle) {
                                    const dropdown = bootstrap.Dropdown.getInstance(toggle);
                                    if (dropdown) {
                                        dropdown.hide();
                                    } else {
                                        // Fallback: remove show class manually
                                        dropdownMenu.classList.remove('show');
                                        toggle.setAttribute('aria-expanded', 'false');
                                    }
                                }
                            }
                        });
                    });
                });
            </script>


            <script>
                // Inisialisasi tooltip Bootstrap
                document.addEventListener('DOMContentLoaded', function() {
                    // Inisialisasi semua tooltip
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl, {
                            delay: {
                                show: 100,
                                hide: 100
                            }
                        });
                    });
                });
            </script>
            <style>
                .jawaban-benar {
                    background-color: #34c759;
                    color: white;

                }

                /* Force dropdown close behavior */
                .dropdown-menu {
                    pointer-events: auto;
                }

                .dropdown-menu.show {
                    display: block !important;
                }

                /* Ensure dropdown overlay doesn't interfere */
                .dropdown-backdrop {
                    position: fixed;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    z-index: 1040;
                }
            </style>
            <!-- Kolom map navigasi (3 kolom) -->
            <!-- Kolom map navigasi (3 kolom) -->
            <div class="col-md-3 d-none d-md-block">
                <div class="card shadow-none  sticky-map">
                    <div class="card-header d-flex gap-2">
                        <div class="icon-wrapper">
                            <i class="ti ti-map-pin" style="color: rgb(199, 99, 66);"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <h6 class="mb-0">Peta Soal</h6>
                            <p class="text-muted p-0 m-0" style="font-size: 12px;">Anda telah membuat <span id="totalSoalCount"></span></p>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="soal-map" id="soalMap">
                            <!-- Map soal akan diisi oleh JavaScript -->
                        </div>
                    </div>


                    <!-- Legend card with information about the border -->
                    <div class="card mt-3 p-3 border-0 mb-3">
                        <div class="card-body  p-3 border rounded-3 ">
                            <h6 class="card-title" style="font-size: 12px;">Keterangan:</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="soal-number with-description position-relative me-2">
                                    <!-- <div class="soal-description-indicator"></div> -->
                                    <span class="p-3"></span>
                                </div>
                                <span style="font-size: 10px;">Soal dengan deskripsi/cerita yang ditautkan</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="soal-number me-2">
                                    <span></span>
                                </div>
                                <span style="font-size: 10px;">Soal tanpa deskripsi/cerita</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Preview Soal -->
        <div class="modal fade" id="previewSoalModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header bg-white border-0">
                        <h5 class="modal-title fw-bold" style="font-size: 20px;">Preview Soal</h5>
                        <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-2">
                        <div id="previewSoalContent">
                            <!-- Konten soal akan diload di sini -->
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Mohon tunggu ...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex border-0">
                        <button type="button" class="btn flex-fill border text-white py-2" data-bs-dismiss="modal" style="border-radius: 12px; background-color:rgb(219, 106, 68);">Tutup</button>
                    </div>
                </div>
            </div>
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
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Impor Soal Word</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <!-- <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Unggah Soal dan Jawaban dengan Benar</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">File upload yang tidak sesuai dapat menyebabkan malfungsi SAGA dalam koreksi ujian.</p>
                            </div>
                        </div>
                    </div> -->

                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-question-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Merasa kebingungan?</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Silahkan untuk melihat panduan prosedur penggunaan Import Soal Word di SMAGAEdu.</p>
                                <a href="templates/panduan_import.pdf" download class="btn btn-sm mt-2 border flex-fill" style="border-radius: 12px;">
                                    <div class="d-flex text-start gap-2">
                                        <i class="bi bi-filetype-pdf"></i>
                                        <span>Panduan Import File Word Ujian</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-4 p-3 mb-4">
                        <div class="mb-4">
                            <p class="small  p-0 m-0 ">Unduh template soal border terlihat</p>
                            <p class="text-muted  p-0 m-0  mb-2" style="font-size: 12px;">Cocok untuk Anda berencana hanya import file kedalam SMAGAEdu</p>
                            <div class="d-flex gap-2 mb-2">
                                <a href="templates/template_soal_border.docx" download class="btn border flex-fill" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center gap-2 justify-content-center">
                                        <i class="bi bi-file-earmark-word"></i>
                                        <span>Template Soal</span>
                                    </div>
                                </a>
                                <a href="templates/template_jawaban_border.docx" download class="btn border flex-fill" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center gap-2 justify-content-center">
                                        <i class="bi bi-file-earmark-check"></i>
                                        <span>Template Jawaban</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="small  p-0 m-0 ">Unduh template soal border tersembunyi</p>
                            <p class="text-muted p-0 m-0  mb-2" style="font-size: 12px;">Cocok untuk Anda berencana untuk mengimpor kedalam SMAGAEdu sekaligus membagian kepada pihak lain</p>
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
                <div class="modal-header bg-white border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Buat Soal dengan SAGA</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <!-- <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Tetap bijak menggunakan SAGA</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Dimohon untuk tetap bijak dalam penggunakan SAGA AI, semakin Anda banyak meminta soal maka semakin banyak token atau penggunaan SAGA AI mengingat sumber data terbatas</p>
                            </div>
                        </div>
                    </div> -->

                    <form id="formAiSoal">
                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Jumlah Soal</label>
                            <input type="number" class="form-control" name="jumlah_soal" min="1" max="50" value="1"
                                onchange="this.value = this.value > 50 ? 50 : this.value"
                                style="border-radius: 12px; height: 50px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                            <div class="form-text mt-2" style="font-size: 12px; color: #8e8e93;">
                                Maksimal input 50 soal
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
                                    style="border-radius: 12px; font-size: 14px; border: 1px solid #e4e4e4; background-color: #f5f5f5; font-family: monospace; opacity: 0.8; height:3rem;"></textarea>
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

    <!-- Modal Edit Identitas Ujian -->
    <div class="modal fade" id="editIdentitasModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Edit Identitas Ujian</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <?php
                    // Cek apakah ada hasil ujian untuk ujian ini
                    $query_cek_hasil = "SELECT COUNT(*) as jumlah FROM jawaban_ujian WHERE ujian_id = '$ujian_id'";
                    $result_cek_hasil = mysqli_query($koneksi, $query_cek_hasil);
                    $data_hasil = mysqli_fetch_assoc($result_cek_hasil);
                    $ada_hasil_ujian = ($data_hasil['jumlah'] > 0);
                    ?>

                    <form id="formEditIdentitas">
                        <input type="hidden" name="ujian_id" value="<?php echo $ujian_id; ?>">

                        <div class="mb-3">
                            <label for="judul" class="form-label fw-medium mb-2" style="font-size: 15px;">Judul Ujian</label>
                            <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($ujian['judul']); ?>" required
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                        </div>

                        <div class="mb-3">
                            <label for="mata_pelajaran" class="form-label fw-medium mb-2" style="font-size: 15px;">Mata Pelajaran</label>
                            <input type="text" class="form-control" id="mata_pelajaran" name="mata_pelajaran" value="<?php echo htmlspecialchars($ujian['mata_pelajaran']); ?>" required
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label fw-medium mb-2" style="font-size: 15px;">Kelas</label>
                            <select class="form-control" id="kelas_id" name="kelas_id" required
                                <?php echo ($ada_hasil_ujian) ? 'disabled' : ''; ?>
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                                <?php
                                // Query untuk mengambil daftar kelas yang dibuat oleh guru ini
                                $userid = $_SESSION['userid']; // ID guru yang sedang login
                                $query_kelas = "SELECT * FROM kelas WHERE guru_id = '$userid' ORDER BY tingkat, nama_kelas";
                                $result_kelas = mysqli_query($koneksi, $query_kelas);

                                // Jika tidak ada kelas yang ditemukan
                                if (mysqli_num_rows($result_kelas) == 0) {
                                    echo "<option value='' disabled>-- Tidak ada kelas yang tersedia --</option>";
                                } else {
                                    while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                                        $selected = ($kelas['id'] == $ujian['kelas_id']) ? 'selected' : '';
                                        echo "<option value='" . $kelas['id'] . "' " . $selected . ">" . $kelas['tingkat'] . " - " . htmlspecialchars($kelas['nama_kelas']) . "</option>";
                                    }
                                }
                                ?>
                            </select>

                            <?php if (mysqli_num_rows($result_kelas) == 0 && !$ada_hasil_ujian): ?>
                                <div class="form-text mt-1" style="font-size: 12px; color: #dc3545;">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    Anda belum memiliki kelas. Silakan buat kelas terlebih dahulu.
                                </div>
                            <?php endif; ?>

                            <?php if ($ada_hasil_ujian): ?>
                                <div class="form-text mt-1" style="font-size: 12px; color: #dc3545;">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    Kelas tidak dapat diubah karena sudah ada siswa yang mengikuti ujian ini.
                                </div>
                                <input type="hidden" name="kelas_id" value="<?php echo $ujian['kelas_id']; ?>">
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-medium mb-2" style="font-size: 15px;">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;"><?php echo htmlspecialchars($ujian['deskripsi']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Materi Ujian</label>


                            <div class="alert border bg-light" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <i class="bi bi-stars fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Semakin Banyak Data Semakin Akurat</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Dengan menambahkan data mengenai pelajaran yang akan di ujikan, maka semakin mampu SAGA AI dalam memahami bentuk soal yang Anda inginkan</p>
                                    </div>
                                </div>
                            </div>

                            <div id="materiContainer">
                                <?php
                                $materi_list = json_decode($ujian['materi'], true);
                                if (is_array($materi_list)) {
                                    foreach ($materi_list as $index => $materi) {
                                        echo '<div class="input-group mb-2">';
                                        echo '<input type="text" class="form-control" name="materi[]" value="' . htmlspecialchars($materi) . '" style="border-radius: 12px 0 0 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">';
                                        echo '<button class="btn btn-outline-danger" type="button" onclick="hapusMateri(this)" style="border-radius: 0 12px 12px 0;"><i class="bi bi-trash"></i></button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="tambahMateri()" style="border-radius: 12px;">
                                <i class="bi bi-plus-circle"></i> Tambah Materi
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label fw-medium mb-2" style="font-size: 15px;">Tanggal Mulai</label>
                                <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($ujian['tanggal_mulai'])); ?>" required
                                    style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_selesai" class="form-label fw-medium mb-2" style="font-size: 15px;">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai"
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($ujian['tanggal_selesai'])); ?>" required
                                    style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="durasi" class="form-label fw-medium mb-2" style="font-size: 15px;">Durasi (menit)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="durasi" name="durasi" value="<?php echo $ujian['durasi']; ?>" readonly
                                    style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                                <span class="input-group-text border-0 bg-transparent text-muted" style="font-size: 12px;">
                                    <i class="bi bi-info-circle me-1"></i> Dihitung otomatis
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light flex-fill border" style="border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn color-web text-white flex-fill" id="btnSimpanIdentitas" style="border-radius: 12px; font-size: 15px;">
                        <span>Simpan</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- script untuk preview soal -->
    <script>
        // Fungsi untuk memuat dan menampilkan preview soal
        async function previewSoal(id) {
            try {
                // Tampilkan modal
                const previewModal = new bootstrap.Modal(document.getElementById('previewSoalModal'));
                previewModal.show();

                // Reset konten ke loading spinner
                document.getElementById('previewSoalContent').innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border" role="status" style="color: rgb(218, 119, 86);">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat preview soal...</p>
            </div>
        `;

                // Ambil data soal
                const response = await fetch(`get_soal.php?id=${id}`);
                const data = await response.json();

                if (data.status === 'success') {
                    const soal = data.soal;
                    let previewHTML = '';

                    // Cek apakah soal memiliki deskripsi/cerita terkait
                    if (soal.description_id) {
                        // Ambil data deskripsi
                        const descResponse = await fetch(`get_deskripsi.php?id=${soal.description_id}`);
                        const descData = await descResponse.json();

                        if (descData.status === 'success') {
                            // Tampilkan deskripsi soal seperti pada halaman ujian siswa
                            previewHTML += `
                    <div class="card mb-4 border" style="border-radius: 12px;">
                        <div class="card-body p-4 bg-light" style="border-radius: 12px;">
                            <h6 class="card-title fw-bold mb-3" style="color: #1c1c1e;">
                                <i class="bi bi-book me-2" style="color: #da7756;"></i>Cerita/Deskripsi Soal
                            </h6>
                            <div class="p-3 bg-white rounded-3 border">
                                ${nl2br(descData.deskripsi.content)}
                            </div>
                        </div>
                    </div>
                    `;
                        }
                    }

                    // Tambahkan gambar soal jika ada
                    if (soal.gambar_soal) {
                        previewHTML += `
                    <div class="mb-3">
                        <img src="${soal.gambar_soal}" alt="Gambar soal" class="img-fluid rounded-3 text-start border" style="max-height: 200px; width: auto; margin: 0 auto;">
                    </div>
                `;
                    }

                    // Tambahkan pertanyaan dengan class dinamis berdasarkan panjang
                    const textLength = soal.pertanyaan.replace(/<[^>]*>/g, '').length;
                    let fontSizeClass = '';

                    if (textLength > 150 && textLength <= 300) {
                        fontSizeClass = 'length-medium';
                    } else if (textLength > 300 && textLength <= 500) {
                        fontSizeClass = 'length-long';
                    } else if (textLength > 500) {
                        fontSizeClass = 'length-very-long';
                    }

                    previewHTML += `<p class="soal-text ${fontSizeClass} mb-4">${soal.pertanyaan}</p>`;

                    // Tambahkan pilihan jawaban untuk soal pilihan ganda
                    if (soal.jenis_soal === 'pilihan_ganda') {
                        // Opsi A
                        previewHTML += `
                    <div class="option-card p-3 rounded border mb-3 ${soal.jawaban_benar === 'A' ? 'jawaban-benar' : ''}">
                        A. ${soal.jawaban_a}
                        ${soal.gambar_jawaban_a ? `
                            <div class="mt-2">
                                <img src="${soal.gambar_jawaban_a}" alt="Gambar jawaban A" class="img-fluid rounded border" style="max-height: 150px; width: auto; display: block;">
                            </div>
                        ` : ''}
                    </div>
                `;

                        // Opsi B
                        previewHTML += `
                    <div class="option-card p-3 rounded border mb-3 ${soal.jawaban_benar === 'B' ? 'jawaban-benar' : ''}">
                        B. ${soal.jawaban_b}
                        ${soal.gambar_jawaban_b ? `
                            <div class="mt-2">
                                <img src="${soal.gambar_jawaban_b}" alt="Gambar jawaban B" class="img-fluid rounded border" style="max-height: 150px; width: auto; display: block;">
                            </div>
                        ` : ''}
                    </div>
                `;

                        // Opsi C
                        previewHTML += `
                    <div class="option-card p-3 rounded border mb-3 ${soal.jawaban_benar === 'C' ? 'jawaban-benar' : ''}">
                        C. ${soal.jawaban_c}
                        ${soal.gambar_jawaban_c ? `
                            <div class="mt-2">
                                <img src="${soal.gambar_jawaban_c}" alt="Gambar jawaban C" class="img-fluid rounded border" style="max-height: 150px; width: auto; display: block;">
                            </div>
                        ` : ''}
                    </div>
                `;

                        // Opsi D
                        previewHTML += `
                    <div class="option-card p-3 rounded border mb-3 ${soal.jawaban_benar === 'D' ? 'jawaban-benar' : ''}">
                        D. ${soal.jawaban_d}
                        ${soal.gambar_jawaban_d ? `
                            <div class="mt-2">
                                <img src="${soal.gambar_jawaban_d}" alt="Gambar jawaban D" class="img-fluid rounded border" style="max-height: 150px; width: auto; display: block;">
                            </div>
                        ` : ''}
                    </div>
                `;
                    }

                    // Tampilkan preview
                    document.getElementById('previewSoalContent').innerHTML = previewHTML;

                    // Render formula matematika jika ada
                    if (typeof MathJax !== 'undefined') {
                        try {
                            MathJax.typeset(['#previewSoalContent']);
                        } catch (error) {
                            console.error('MathJax error:', error);
                        }
                    }
                } else {
                    throw new Error(data.message || 'Gagal memuat data soal');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('previewSoalContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Gagal memuat preview soal: ${error.message}
            </div>
        `;
            }
        }

        // Fungsi helper untuk mengubah newline menjadi <br>
        function nl2br(str) {
            return str.replace(/\n/g, '<br>');
        }
    </script>

    <script>
        // Script untuk map navigasi soal
        // Tambahkan ke fungsi yang sudah ada
        document.addEventListener('DOMContentLoaded', function() {
            generateSoalMap();

            // Update map ketika window di-resize
            window.addEventListener('resize', adjustMapHeight);
            adjustMapHeight();

            // Debug untuk memeriksa apakah sticky bekerja
            window.addEventListener('scroll', function() {
                const stickyMap = document.querySelector('.sticky-map');
                const scrollY = window.scrollY;
                console.log('Scroll Y:', scrollY, 'Sticky map position:', stickyMap.getBoundingClientRect().top);
            });
        });

        // Function to generate soal map
        function generateSoalMap() {
            const soalMapContainer = document.getElementById('soalMap');
            const soalElements = document.querySelectorAll('.soal-card');
            const totalSoalCount = document.getElementById('totalSoalCount');

            if (!soalMapContainer) return;

            // Bersihkan container
            soalMapContainer.innerHTML = '';

            // Check if there are no soal elements
            if (!soalElements.length) {
                // Update total count to 0
                if (totalSoalCount) totalSoalCount.textContent = '0 Soal';

                // Display message for empty soal map
                const emptyMessage = document.createElement('div');
                emptyMessage.className = 'text-center p-3 text-muted';
                emptyMessage.innerHTML = 'Buat soal terlebih dahulu untuk melihat navigasi di sini.';
                emptyMessage.style.fontSize = '14px';
                soalMapContainer.appendChild(emptyMessage);
                return;
            }

            // Update total count
            if (totalSoalCount) totalSoalCount.textContent = `${soalElements.length} Soal`;

            // Tambahkan ID ke setiap soal jika belum ada
            soalElements.forEach((soal, index) => {
                const soalId = `soal-${index + 1}`;
                soal.id = soalId;

                // Buat elemen nomor soal untuk navigasi
                const soalNumber = document.createElement('div');
                soalNumber.className = 'soal-number position-relative';

                // Cek apakah soal memiliki deskripsi (berada dalam description-group-card)
                const hasDescription = soal.closest('.description-group-card') !== null;
                if (hasDescription) {
                    soalNumber.classList.add('with-description');

                    // Tambahkan indikator
                    const indicator = document.createElement('div');
                    indicator.className = 'soal-description-indicator';
                    soalNumber.appendChild(indicator);
                }

                soalNumber.textContent = index + 1;
                soalNumber.setAttribute('data-target', soalId);

                // Event listener untuk navigasi
                soalNumber.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const targetElement = document.getElementById(targetId);

                    if (targetElement) {
                        // Scroll ke soal dengan animasi smooth
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });

                        // Highlight soal yang dipilih
                        highlightSelectedSoal(targetElement);
                    }
                });

                soalMapContainer.appendChild(soalNumber);
            });
        }

        function highlightSelectedSoal(element) {
            // Hapus highlight dari semua soal
            document.querySelectorAll('.soal-card').forEach(soal => {
                soal.classList.remove('highlight-soal');
            });

            // Tambahkan highlight ke soal yang dipilih
            element.classList.add('highlight-soal');

            // Hapus highlight setelah 1.5 detik
            setTimeout(() => {
                element.classList.remove('highlight-soal');
            }, 1500);
        }

        function adjustMapHeight() {
            const soalMap = document.querySelector('.soal-map');
            if (!soalMap) return;

            // Sesuaikan tinggi maksimum berdasarkan tinggi viewport
            const viewportHeight = window.innerHeight;

            // Dapatkan posisi soalMap dari bagian atas halaman
            const topOffset = 80; // Nilai tetap untuk header, menu, dll.

            const maxHeight = viewportHeight - topOffset - 40; // 40px untuk margin bawah

            soalMap.style.maxHeight = `${Math.max(200, maxHeight)}px`;
        }
    </script>


    <!-- css untuk modal deskripsi -->
    <style>
        /* CSS untuk memperbaiki tampilan di modal deskripsi soal */
        .soal-item {
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .soal-item:hover {
            background-color: #f0f0f5;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .soal-item .text-break {
            max-height: none;
            /* Hapus batasan tinggi */
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            line-height: 1.4;
        }

        /* CSS untuk card soal yang dipilih */
        .soal-item.selected {
            background-color: rgba(218, 119, 86, 0.1);
            border-color: rgb(218, 119, 86);
        }

        .soal-item.selected .form-check-input {
            background-color: rgb(218, 119, 86);
            border-color: rgb(218, 119, 86);
        }
    </style>
    <style>
        .disabled-item {
            opacity: 0.5;
            pointer-events: none !important;
        }

        .soal-item {
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .soal-item:hover:not(.disabled-item .soal-item) {
            background-color: #f0f0f5;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .soal-item .text-break {
            max-height: none;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            line-height: 1.4;
        }

        .soal-item.selected {
            background-color: rgba(218, 119, 86, 0.1);
            border-color: rgb(218, 119, 86);
        }

        .soal-item.selected .form-check-input {
            background-color: rgb(218, 119, 86);
            border-color: rgb(218, 119, 86);
        }
    </style>


    <!-- Modal Tambah Deskripsi Soal Perbaikan -->
    <div class="modal fade" id="deskripsiSoalModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Tambah Cerita/Deskripsi Soal</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <form id="formDeskripsiSoal">
                        <input type="hidden" name="ujian_id" value="<?php echo $ujian_id; ?>">
                        <input type="hidden" name="description_id" value="">

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Judul Deskripsi/Cerita</label>
                            <input type="text" class="form-control" name="title" required
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
                            <div class="form-text" style="font-size: 12px;">Judul ini hanya untuk memudahkan identifikasi, tidak akan ditampilkan pada siswa</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Konten Deskripsi/Cerita</label>
                            <textarea class="form-control" name="content" rows="6" required
                                style="border-radius: 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;"></textarea>
                            <div class="form-text" style="font-size: 12px;">Deskripsi/cerita yang akan ditampilkan di atas soal-soal terkait</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium mb-2" style="font-size: 15px;">Pilih Soal yang Akan Ditautkan</label>
                            <div class="soal-selection border rounded-3 p-3" style="max-height: 300px; overflow-y: auto; background-color: #f5f5f5;">
                                <div class="d-flex flex-column flex-sm-row justify-content-between mb-3 gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAllSoal">
                                        <label class="form-check-label fw-medium" for="checkAllSoal">
                                            Pilih Semua
                                        </label>
                                    </div>
                                    <div class="form-text mt-0">Total: <span id="selectedCount">0</span> soal dipilih</div>
                                </div>

                                <div class="soal-list">
                                    <?php
                                    // Query untuk mengambil semua soal dalam ujian ini
                                    $query_soal_list = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id' ORDER BY id ASC";
                                    $result_soal_list = mysqli_query($koneksi, $query_soal_list);

                                    if (mysqli_num_rows($result_soal_list) == 0) {
                                        echo '<div class="alert alert-info mb-0">Belum ada soal yang dibuat untuk ujian ini.</div>';
                                    }

                                    while ($soal = mysqli_fetch_assoc($result_soal_list)) {
                                        // Cek apakah soal ini sudah terikat dengan deskripsi lain
                                        $is_linked = !empty($soal['description_id']);
                                        $current_desc_id = isset($_GET['description_id']) ? $_GET['description_id'] : '';
                                        $disabled = ($is_linked && $soal['description_id'] != $current_desc_id) ? true : false;
                                        $soal_id = $soal['id'];
                                    ?>
                                        <label for="soal<?php echo $soal_id; ?>" class="w-100 mb-2 <?php echo $disabled ? 'disabled-item' : ''; ?>"
                                            style="<?php echo $disabled ? 'opacity: 0.5; pointer-events: none;' : ''; ?>"
                                            data-soal-id="<?php echo $soal_id; ?>"
                                            data-description-id="<?php echo $soal['description_id']; ?>">
                                            <div class="soal-item card border" style="cursor: pointer; transition: all 0.2s ease;">
                                                <div class="card-body p-2 p-sm-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input soal-checkbox" type="checkbox"
                                                                name="selected_soal[]" value="<?php echo $soal_id; ?>"
                                                                id="soal<?php echo $soal_id; ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                                                        </div>
                                                        <div class="ms-2 ms-sm-3 flex-grow-1 overflow-hidden">
                                                            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 mb-1">
                                                                <!-- <span class="badge flex-shrink-0 text-white" style="background-color: rgb(199, 99, 66);">Soal <?php echo $no++; ?></span> -->
                                                                <?php if ($is_linked && $soal['description_id'] != $current_desc_id): ?>
                                                                    <?php
                                                                    // Fetch deskripsi judul
                                                                    $desc_id = $soal['description_id'];
                                                                    $query_desc = "SELECT title FROM soal_descriptions WHERE id = '$desc_id'";
                                                                    $desc_result = mysqli_query($koneksi, $query_desc);
                                                                    $desc_title = '';
                                                                    if ($desc_result && mysqli_num_rows($desc_result) > 0) {
                                                                        $desc_title = mysqli_fetch_assoc($desc_result)['title'];
                                                                    }
                                                                    ?>
                                                                    <span class="badge bg-warning text-dark">Terikat: <?php echo htmlspecialchars($desc_title); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-break" style="word-wrap: break-word; font-size: 13px; line-height: 1.4;">
                                                                <?php
                                                                // Limit the text length for better display
                                                                $pertanyaan = htmlspecialchars($soal['pertanyaan']);
                                                                echo (strlen($pertanyaan) > 150) ? substr($pertanyaan, 0, 150) . '...' : $pertanyaan;
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Mobile view helper styles -->
                            <style>
                                @media (max-width: 576px) {
                                    .soal-selection {
                                        max-height: 250px;
                                        padding: 12px !important;
                                    }

                                    .soal-item {
                                        margin-bottom: 8px;
                                    }

                                    .soal-item .badge {
                                        font-size: 10px;
                                        padding: 0.25rem 0.5rem;
                                    }

                                    .text-break {
                                        font-size: 12px !important;
                                        line-height: 1.3 !important;
                                    }

                                    .form-check-input {
                                        min-width: 16px;
                                        min-height: 16px;
                                    }
                                }
                            </style>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button type="button" class="btn btn-light flex-fill border" style="border-radius: 12px; font-size: 15px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn color-web text-white flex-fill" id="btnSimpanDeskripsi" style="border-radius: 12px; font-size: 15px;">
                        <span>Simpan</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .nav-pills {
            gap: 8px;
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-item .active {
            background-color: rgb(218, 119, 86) !important;
            border-color: rgb(218, 119, 86) !important;
        }

        .nav-item .nav-link {
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 13px;
            color: #333;
            border: 1px solid #e4e4e4;
        }

        .symbol-btn {
            min-width: 35px;
            height: 35px;
            font-size: 16px;
            padding: 0;
            margin: 2px;
        }

        .tab-content {
            height: 100px;

        }
    </style>

    <!-- Modal Simbol Matematika -->
    <div class="modal fade" id="symbolsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border" style="border-radius: 16px;">
                <div class="modal-body p-3">
                    <input type="hidden" id="currentSymbolTarget" value="pertanyaan">

                    <!-- Kategori Simbol -->
                    <ul class="nav nav-pills d-flex justify-content-center" id="symbol-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic-symbols" type="button">Dasar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="compare-tab" data-bs-toggle="pill" data-bs-target="#compare-symbols" type="button">Banding</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="greek-tab" data-bs-toggle="pill" data-bs-target="#greek-symbols" type="button">Yunani</button>
                        </li>
                    </ul>

                    <!-- Konten Simbol -->
                    <div class="tab-content" id="symbols-content">
                        <!-- Simbol Dasar -->
                        <div class="tab-pane fade show active" id="basic-symbols">
                            <div class="d-flex flex-wrap justify-content-center">
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('±')">±</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('×')">×</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('÷')">÷</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('√')">√</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∞')">∞</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∑')">∑</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∫')">∫</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∂')">∂</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∏')">∏</button>
                            </div>
                        </div>

                        <!-- Simbol Perbandingan -->
                        <div class="tab-pane fade" id="compare-symbols">
                            <div class="d-flex flex-wrap justify-content-center">
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('≤')">≤</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('≥')">≥</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('≠')">≠</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('≈')">≈</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∈')">∈</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('∉')">∉</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('⊂')">⊂</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('⊃')">⊃</button>
                            </div>
                        </div>

                        <!-- Huruf Yunani -->
                        <div class="tab-pane fade" id="greek-symbols">
                            <div class="d-flex flex-wrap justify-content-center">
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('α')">α</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('β')">β</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('γ')">γ</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('δ')">δ</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('π')">π</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('μ')">μ</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('σ')">σ</button>
                                <button class="btn btn-outline-secondary symbol-btn" onclick="insertSymbol('Ω')">Ω</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <script>
    function insertSymbolAndClose(symbol) {
        insertSymbol(symbol);
        const modal = bootstrap.Modal.getInstance(document.getElementById('symbolsModal'));
        modal.hide();
    }
    </script> -->

    <!-- Modal Formula (Visual Editor) -->
    <!-- Modal Formula (Visual Editor) -->
    <div class="modal fade" id="formulaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size: 20px;">Tambah Formula Matematika</h5>
                    <button type="button" class="btn-close rounded-circle" style="background-color: #e4e4e4; opacity: 1; padding: 12px;" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <!-- Hidden input untuk menyimpan target saat ini -->
                    <input type="hidden" id="currentFormulaTarget" value="pertanyaan">

                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Format Formula LaTex Digunakan</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Jangan panik, SMAGAEdu menggunakan jenis format penulisan LaTex untuk mensisipkan formula Anda. Silahkan hubungi Tim IT untuk bantuan lebih lanjut</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tipe Formula -->
                    <div class="mb-3">
                        <label class="form-label fw-medium mb-2" style="font-size: 15px;">Pilih Tipe Formula</label>
                        <select id="formulaType" class="form-select" style="border-radius: 12px;" onchange="changeFormulaType()">
                            <option value="pecahan"><i class="ti ti-divide"></i> Pecahan (a/b)</option>
                            <option value="pangkat"><i class="ti ti-superscript"></i> Pangkat (a^n)</option>
                            <option value="akar"><i class="ti ti-square-root"></i> Akar Kuadrat (√a)</option>
                            <option value="akar-n"><i class="ti ti-square-root-2"></i> Akar ke-n (∜a)</option>
                            <option value="subscript"><i class="ti ti-subscript"></i> Subskrip (a₁)</option>
                            <option value="integral"><i class="ti ti-integral"></i> Integral</option>
                            <option value="matriks"><i class="ti ti-table"></i> Matriks</option>
                            <option value="limit"><i class="ti ti-arrows-right-left"></i> Limit</option>
                            <option value="turunan"><i class="ti ti-math-function"></i> Turunan</option>
                            <option value="trigonometri"><i class="ti ti-wave-sine"></i> Fungsi Trigonometri</option>
                            <option value="sigma"><i class="ti ti-sum"></i> Penjumlahan (Sigma)</option>
                            <option value="product"><i class="ti ti-multiply"></i> Perkalian (Pi)</option>
                        </select>
                    </div>

                    <!-- Input Container (akan berubah sesuai tipe formula) -->
                    <div class="mb-4" id="formulaInputContainer">
                        <!-- Default: Pecahan -->
                        <div id="pecahanInput">
                            <div class="text-center mb-2">
                                <div class="card-body">
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">Pembilang</span>
                                        <input type="text" class="form-control" id="pecahan-pembilang" placeholder="Masukkan nilai" style="border-radius: 0 12px 12px 0;">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">Penyebut</span>
                                        <input type="text" class="form-control" id="pecahan-penyebut" placeholder="Masukkan nilai" style="border-radius: 0 12px 12px 0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Formula -->
                    <div class="mb-4">
                        <label class="form-label fw-medium mb-2" style="font-size: 15px;">Preview</label>
                        <div class="border rounded-3 p-3 text-center bg-light" id="formulaPreview" style="min-height: 60px;">
                            <!-- Preview akan ditampilkan di sini -->
                        </div>
                    </div>

                    <!-- Mode Formula -->
                    <div class="mb-3">
                        <label class="form-label fw-medium mb-2" style="font-size: 15px;">Mode Tampilan</label>

                        <div class=" border rounded-4 bg-light p-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="formulaMode" id="inlineMode" value="inline" checked>
                                <label class="form-check-label" for="inlineMode">
                                    Inline
                                </label>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Pilih ini jika Anda ingin menambahkan formula sebagai kalimat, cocok dipadukan dengan kalimat lain seperti +, -, :, x, aritmatika atau kalimat tambahan lainya</p>
                            </div>
                        </div>
                        <div class="border rounded-4 bg-light p-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="formulaMode" id="displayMode" value="display">
                                <label class="form-check-label" for="displayMode">
                                    Display
                                </label>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Pilih ini jika Anda ingin menginputkan 1 formula sebagai pertanyaan tanpa menambahkan kalimat apapun</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer btn-group border-0">
                    <button type="button" class="btn rounded btn-light border" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="button" class="btn rounded color-web text-white" onclick="insertVisualFormula(document.getElementById('inlineMode').checked)" style="border-radius: 12px;">Sisipkan Formula</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .formula-btn {
            height: 42px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .formula-btn:hover {
            background-color: rgba(218, 119, 86, 0.1);
            border-color: rgb(218, 119, 86);
        }
    </style>

    <!-- Script untuk modal formula -->
    <script>
        // Template input untuk berbagai tipe formula
        const formulaTemplates = {
            pecahan: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Pembilang</span>
                    <input type="text" class="form-control" id="pecahan-pembilang" placeholder="Masukkan nilai" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Penyebut</span>
                    <input type="text" class="form-control" id="pecahan-penyebut" placeholder="Masukkan nilai" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            pangkat: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Basis</span>
                    <input type="text" class="form-control" id="pangkat-basis" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Pangkat</span>
                    <input type="text" class="form-control" id="pangkat-eksponen" placeholder="n" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            akar: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text">Di dalam akar</span>
                    <input type="text" class="form-control" id="akar-nilai" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            'akar-n': `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Pangkat akar</span>
                    <input type="text" class="form-control" id="akar-n-pangkat" placeholder="n" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Di dalam akar</span>
                    <input type="text" class="form-control" id="akar-n-nilai" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            subscript: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Variabel</span>
                    <input type="text" class="form-control" id="subscript-variabel" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Subskrip</span>
                    <input type="text" class="form-control" id="subscript-indeks" placeholder="1" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            integral: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Batas bawah</span>
                    <input type="text" class="form-control" id="integral-bawah" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Batas atas</span>
                    <input type="text" class="form-control" id="integral-atas" placeholder="b" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Fungsi</span>
                    <input type="text" class="form-control" id="integral-fungsi" placeholder="f(x)" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,
            // Template untuk matriks
            matriks: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Jumlah Baris</span>
                    <select class="form-control" id="matriks-baris" onchange="updateMatriksInput()" style="border-radius: 0 12px 12px 0;">
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Jumlah Kolom</span>
                    <select class="form-control" id="matriks-kolom" onchange="updateMatriksInput()" style="border-radius: 0 12px 12px 0;">
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
                <div id="matriks-input-container" class="border p-3 rounded">
                    <!-- Elemen input matriks akan ditambahkan oleh JavaScript -->
                </div>
            </div>
        </div>
    `,

            // Template untuk limit
            limit: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Variabel</span>
                    <input type="text" class="form-control" id="limit-variabel" placeholder="x" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Mendekati</span>
                    <input type="text" class="form-control" id="limit-mendekati" placeholder="a" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Ekspresi</span>
                    <input type="text" class="form-control" id="limit-ekspresi" placeholder="f(x)" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,

            // Template untuk turunan
            turunan: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Fungsi</span>
                    <input type="text" class="form-control" id="turunan-fungsi" placeholder="f(x)" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Terhadap</span>
                    <input type="text" class="form-control" id="turunan-variabel" placeholder="x" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Tingkat</span>
                    <select class="form-control" id="turunan-tingkat" style="border-radius: 0 12px 12px 0;">
                        <option value="1">Pertama</option>
                        <option value="2">Kedua</option>
                        <option value="3">Ketiga</option>
                        <option value="n">Ke-n</option>
                    </select>
                </div>
                <div id="turunan-n-container" class="mt-2 d-none">
                    <div class="input-group">
                        <span class="input-group-text">Nilai n</span>
                        <input type="text" class="form-control" id="turunan-n-nilai" placeholder="n" style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>
            </div>
        </div>
    `,

            // Template untuk fungsi trigonometri
            trigonometri: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Fungsi</span>
                    <select class="form-control" id="trigonometri-fungsi" style="border-radius: 0 12px 12px 0;">
                        <option value="sin">sin</option>
                        <option value="cos">cos</option>
                        <option value="tan">tan</option>
                        <option value="csc">csc</option>
                        <option value="sec">sec</option>
                        <option value="cot">cot</option>
                        <option value="arcsin">arcsin</option>
                        <option value="arccos">arccos</option>
                        <option value="arctan">arctan</option>
                    </select>
                </div>
                <div class="input-group">
                    <span class="input-group-text">Argumen</span>
                    <input type="text" class="form-control" id="trigonometri-argumen" placeholder="x" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,

            // Template untuk sigma (penjumlahan)
            sigma: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Variabel</span>
                    <input type="text" class="form-control" id="sigma-variabel" placeholder="i" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Mulai dari</span>
                    <input type="text" class="form-control" id="sigma-mulai" placeholder="1" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Sampai</span>
                    <input type="text" class="form-control" id="sigma-sampai" placeholder="n" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Ekspresi</span>
                    <input type="text" class="form-control" id="sigma-ekspresi" placeholder="a_i" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `,

            // Template untuk product (perkalian)
            product: `
        <div class="text-center mb-2">
            <div class="card-body">
                <div class="input-group mb-2">
                    <span class="input-group-text">Variabel</span>
                    <input type="text" class="form-control" id="product-variabel" placeholder="i" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Mulai dari</span>
                    <input type="text" class="form-control" id="product-mulai" placeholder="1" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text">Sampai</span>
                    <input type="text" class="form-control" id="product-sampai" placeholder="n" style="border-radius: 0 12px 12px 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Ekspresi</span>
                    <input type="text" class="form-control" id="product-ekspresi" placeholder="a_i" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
        </div>
    `
        };

        // Fungsi untuk mengubah tipe formula
        // Ganti fungsi changeFormulaType
        // Ganti fungsi changeFormulaType
        function changeFormulaType() {
            const formulaType = document.getElementById('formulaType')?.value;
            const container = document.getElementById('formulaInputContainer');

            if (!formulaType || !container) return;

            // Nonaktifkan event listener sementara
            const inputs = container.querySelectorAll('input');
            inputs.forEach(input => {
                input.removeEventListener('input', updateFormulaPreview);
            });

            // Ubah input berdasarkan tipe formula dengan menggunakan innerHTML
            // Tapi hanya jika perlu (jika tipe berbeda)
            const currentTemplate = container.getAttribute('data-current-template');
            if (currentTemplate !== formulaType) {
                container.innerHTML = formulaTemplates[formulaType] || '';
                container.setAttribute('data-current-template', formulaType);

                // Set event listener pada semua input baru
                const newInputs = container.querySelectorAll('input');
                newInputs.forEach(input => {
                    input.addEventListener('input', updateFormulaPreview);
                });

                // Update preview setelah interval singkat
                setTimeout(updateFormulaPreview, 100);
            }
        }

        // Menambahkan fungsi baru setelah fungsi changeFormulaType
        function updateMatriksInput() {
            const baris = parseInt(document.getElementById('matriks-baris').value);
            const kolom = parseInt(document.getElementById('matriks-kolom').value);
            const container = document.getElementById('matriks-input-container');

            if (!container || isNaN(baris) || isNaN(kolom)) return;

            // Bersihkan container
            container.innerHTML = '';

            // Buat tabel untuk input matriks
            const table = document.createElement('table');
            table.className = 'table table-bordered mb-0';

            // Buat baris dan kolom dengan input
            for (let i = 0; i < baris; i++) {
                const tr = document.createElement('tr');

                for (let j = 0; j < kolom; j++) {
                    const td = document.createElement('td');
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control form-control-sm text-center';
                    input.id = `matriks-elemen-${i}-${j}`;
                    input.setAttribute('data-baris', i);
                    input.setAttribute('data-kolom', j);
                    input.placeholder = `a${i+1,j+1}`;
                    input.addEventListener('input', updateFormulaPreview);

                    td.appendChild(input);
                    tr.appendChild(td);
                }

                table.appendChild(tr);
            }

            container.appendChild(table);

            // Update preview
            updateFormulaPreview();
        }

        // Menambahkan event listener untuk turunan-tingkat
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener yang sudah ada tetap ada...

            // Tambahkan event listener untuk select turunan-tingkat
            document.body.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'turunan-tingkat') {
                    const container = document.getElementById('turunan-n-container');
                    if (container) {
                        if (e.target.value === 'n') {
                            container.classList.remove('d-none');
                        } else {
                            container.classList.add('d-none');
                        }
                        updateFormulaPreview();
                    }
                }
            });
        });

        let previewDebounceTimer = null;
        const MAX_FORMULA_LENGTH = 500; // Batasi panjang formula
        // Fungsi untuk memperbarui preview formula
        function updateFormulaPreview() {
            // cegah pembaruan sekaligus
            if (previewDebounceTimer) {
                clearTimeout(previewDebounceTimer);
                previewDebounceTimer = null;
            }

            previewDebounceTimer = setTimeout(() => {
                try {
                    const formulaType = document.getElementById('formulaType')?.value;
                    const preview = document.getElementById('formulaPreview');

                    if (!formulaType || !preview) return;

                    let formula = '';

                    // Generate formula berdasarkan tipe
                    switch (formulaType) {
                        case 'pecahan':
                            const pembilang = document.getElementById('pecahan-pembilang').value || 'a';
                            const penyebut = document.getElementById('pecahan-penyebut').value || 'b';
                            formula = `\\frac{${pembilang}}{${penyebut}}`;
                            break;

                        case 'pangkat':
                            const basis = document.getElementById('pangkat-basis').value || 'a';
                            const eksponen = document.getElementById('pangkat-eksponen').value || 'n';
                            formula = `${basis}^{${eksponen}}`;
                            break;

                        case 'akar':
                            const nilai = document.getElementById('akar-nilai').value || 'a';
                            formula = `\\sqrt{${nilai}}`;
                            break;

                        case 'akar-n':
                            const pangkat = document.getElementById('akar-n-pangkat').value || 'n';
                            const nilaiAkar = document.getElementById('akar-n-nilai').value || 'a';
                            formula = `\\sqrt[${pangkat}]{${nilaiAkar}}`;
                            break;

                        case 'subscript':
                            const variabel = document.getElementById('subscript-variabel').value || 'a';
                            const indeks = document.getElementById('subscript-indeks').value || '1';
                            formula = `${variabel}_{${indeks}}`;
                            break;

                        case 'integral':
                            const bawah = document.getElementById('integral-bawah').value || 'a';
                            const atas = document.getElementById('integral-atas').value || 'b';
                            const fungsi = document.getElementById('integral-fungsi').value || 'f(x)';
                            formula = `\\int_{${bawah}}^{${atas}} ${fungsi} \\, dx`;
                            break;

                        case 'matriks':
                            const baris = parseInt(document.getElementById('matriks-baris').value) || 2;
                            const kolom = parseInt(document.getElementById('matriks-kolom').value) || 2;

                            // Bangun formula matriks
                            formula = '\\begin{bmatrix}';

                            for (let i = 0; i < baris; i++) {
                                let rowElements = [];
                                for (let j = 0; j < kolom; j++) {
                                    const elem = document.getElementById(`matriks-elemen-${i}-${j}`);
                                    rowElements.push(elem?.value || `a_{${i+1}${j+1}}`);
                                }
                                formula += rowElements.join(' & ');

                                // Tambahkan pemisah baris kecuali untuk baris terakhir
                                if (i < baris - 1) {
                                    formula += ' \\\\';
                                }
                            }

                            formula += '\\end{bmatrix}';
                            break;

                        case 'limit':
                            const limitVar = document.getElementById('limit-variabel').value || 'x';
                            const limitTo = document.getElementById('limit-mendekati').value || 'a';
                            const limitExpr = document.getElementById('limit-ekspresi').value || 'f(x)';
                            formula = `\\lim_{${limitVar} \\to ${limitTo}} ${limitExpr}`;
                            break;

                        case 'turunan':
                            const turunanFunc = document.getElementById('turunan-fungsi').value || 'f(x)';
                            const turunanVar = document.getElementById('turunan-variabel').value || 'x';
                            const turunanOrder = document.getElementById('turunan-tingkat').value;

                            if (turunanOrder === 'n') {
                                const nValue = document.getElementById('turunan-n-nilai').value || 'n';
                                formula = `\\frac{d^{${nValue}}}{d${turunanVar}^{${nValue}}}\\left(${turunanFunc}\\right)`;
                            } else {
                                let notation = '';
                                if (turunanOrder === '1') {
                                    notation = '\\frac{d}{d' + turunanVar + '}';
                                } else {
                                    notation = '\\frac{d^{' + turunanOrder + '}}{d' + turunanVar + '^{' + turunanOrder + '}}';
                                }
                                formula = notation + '\\left(' + turunanFunc + '\\right)';
                            }
                            break;

                        case 'trigonometri':
                            const trigFunc = document.getElementById('trigonometri-fungsi').value || 'sin';
                            const trigArg = document.getElementById('trigonometri-argumen').value || 'x';
                            formula = `\\${trigFunc}\\left(${trigArg}\\right)`;
                            break;

                        case 'sigma':
                            const sigmaVar = document.getElementById('sigma-variabel').value || 'i';
                            const sigmaStart = document.getElementById('sigma-mulai').value || '1';
                            const sigmaEnd = document.getElementById('sigma-sampai').value || 'n';
                            const sigmaExpr = document.getElementById('sigma-ekspresi').value || 'a_i';
                            formula = `\\sum_{${sigmaVar}=${sigmaStart}}^{${sigmaEnd}} ${sigmaExpr}`;
                            break;

                        case 'product':
                            const prodVar = document.getElementById('product-variabel').value || 'i';
                            const prodStart = document.getElementById('product-mulai').value || '1';
                            const prodEnd = document.getElementById('product-sampai').value || 'n';
                            const prodExpr = document.getElementById('product-ekspresi').value || 'a_i';
                            formula = `\\prod_{${prodVar}=${prodStart}}^{${prodEnd}} ${prodExpr}`;
                            break;
                    }

                    if (formula.length > MAX_FORMULA_LENGTH) {
                        formula = formula.substring(0, MAX_FORMULA_LENGTH) + '...';
                        console.warn('Formula terlalu panjang, dipotong untuk keamanan');
                    }

                    // Tampilkan preview dengan MathJax
                    preview.innerHTML = `$$${formula}$$`;

                    // Render dengan MathJax hanya jika tersedia dan tab browser aktif
                    if (!document.hidden && typeof MathJax !== 'undefined') {
                        // Wrap in try-catch
                        try {
                            MathJax.typeset([preview]);
                        } catch (e) {
                            console.error('MathJax error:', e);
                            preview.innerHTML = '<div class="alert alert-warning">Error rendering formula</div>';
                        }
                    }
                } catch (error) {
                    console.error('Formula preview error:', error);
                }
            }, 500);
        }

        // Fungsi untuk menyisipkan formula dari editor visual
        // Fungsi untuk menyisipkan formula dari editor visual
        function insertVisualFormula(isInline = true) {
            const formulaType = document.getElementById('formulaType').value;

            // Identifikasi target: apakah pertanyaan atau jawaban
            const targetElement = document.getElementById('currentFormulaTarget').value;
            const textarea = document.querySelector(`[name="${targetElement}"]`);

            if (!textarea) return;

            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);

            let formula = '';

            // Generate formula berdasarkan tipe
            switch (formulaType) {
                case 'pecahan':
                    const pembilang = document.getElementById('pecahan-pembilang').value || 'a';
                    const penyebut = document.getElementById('pecahan-penyebut').value || 'b';
                    formula = `\\frac{${pembilang}}{${penyebut}}`;
                    break;

                case 'pangkat':
                    const basis = document.getElementById('pangkat-basis').value || 'a';
                    const eksponen = document.getElementById('pangkat-eksponen').value || 'n';
                    formula = `${basis}^{${eksponen}}`;
                    break;

                case 'akar':
                    const nilai = document.getElementById('akar-nilai').value || 'a';
                    formula = `\\sqrt{${nilai}}`;
                    break;

                case 'akar-n':
                    const pangkat = document.getElementById('akar-n-pangkat').value || 'n';
                    const nilaiAkar = document.getElementById('akar-n-nilai').value || 'a';
                    formula = `\\sqrt[${pangkat}]{${nilaiAkar}}`;
                    break;

                case 'subscript':
                    const variabel = document.getElementById('subscript-variabel').value || 'a';
                    const indeks = document.getElementById('subscript-indeks').value || '1';
                    formula = `${variabel}_{${indeks}}`;
                    break;

                case 'integral':
                    const bawah = document.getElementById('integral-bawah').value || 'a';
                    const atas = document.getElementById('integral-atas').value || 'b';
                    const fungsi = document.getElementById('integral-fungsi').value || 'f(x)';
                    formula = `\\int_{${bawah}}^{${atas}} ${fungsi} \\, dx`;
                    break;

                case 'matriks':
                    const baris = parseInt(document.getElementById('matriks-baris').value) || 2;
                    const kolom = parseInt(document.getElementById('matriks-kolom').value) || 2;

                    // Bangun formula matriks
                    formula = '\\begin{bmatrix}';

                    for (let i = 0; i < baris; i++) {
                        let rowElements = [];
                        for (let j = 0; j < kolom; j++) {
                            const elem = document.getElementById(`matriks-elemen-${i}-${j}`);
                            rowElements.push(elem?.value || `a_{${i+1}${j+1}}`);
                        }
                        formula += rowElements.join(' & ');

                        // Tambahkan pemisah baris kecuali untuk baris terakhir
                        if (i < baris - 1) {
                            formula += ' \\\\';
                        }
                    }

                    formula += '\\end{bmatrix}';
                    break;

                case 'limit':
                    const limitVar = document.getElementById('limit-variabel').value || 'x';
                    const limitTo = document.getElementById('limit-mendekati').value || 'a';
                    const limitExpr = document.getElementById('limit-ekspresi').value || 'f(x)';
                    formula = `\\lim_{${limitVar} \\to ${limitTo}} ${limitExpr}`;
                    break;

                case 'turunan':
                    const turunanFunc = document.getElementById('turunan-fungsi').value || 'f(x)';
                    const turunanVar = document.getElementById('turunan-variabel').value || 'x';
                    const turunanOrder = document.getElementById('turunan-tingkat').value;

                    if (turunanOrder === 'n') {
                        const nValue = document.getElementById('turunan-n-nilai').value || 'n';
                        formula = `\\frac{d^{${nValue}}}{d${turunanVar}^{${nValue}}}\\left(${turunanFunc}\\right)`;
                    } else {
                        let notation = '';
                        if (turunanOrder === '1') {
                            notation = '\\frac{d}{d' + turunanVar + '}';
                        } else {
                            notation = '\\frac{d^{' + turunanOrder + '}}{d' + turunanVar + '^{' + turunanOrder + '}}';
                        }
                        formula = notation + '\\left(' + turunanFunc + '\\right)';
                    }
                    break;

                case 'trigonometri':
                    const trigFunc = document.getElementById('trigonometri-fungsi').value || 'sin';
                    const trigArg = document.getElementById('trigonometri-argumen').value || 'x';
                    formula = `\\${trigFunc}\\left(${trigArg}\\right)`;
                    break;

                case 'sigma':
                    const sigmaVar = document.getElementById('sigma-variabel').value || 'i';
                    const sigmaStart = document.getElementById('sigma-mulai').value || '1';
                    const sigmaEnd = document.getElementById('sigma-sampai').value || 'n';
                    const sigmaExpr = document.getElementById('sigma-ekspresi').value || 'a_i';
                    formula = `\\sum_{${sigmaVar}=${sigmaStart}}^{${sigmaEnd}} ${sigmaExpr}`;
                    break;

                case 'product':
                    const prodVar = document.getElementById('product-variabel').value || 'i';
                    const prodStart = document.getElementById('product-mulai').value || '1';
                    const prodEnd = document.getElementById('product-sampai').value || 'n';
                    const prodExpr = document.getElementById('product-ekspresi').value || 'a_i';
                    formula = `\\prod_{${prodVar}=${prodStart}}^{${prodEnd}} ${prodExpr}`;
                    break;
            }

            // Gunakan delimiters yang sesuai berdasarkan mode
            const openDelimiter = isInline ? '\\(' : '$$';
            const closeDelimiter = isInline ? '\\)' : '$$';

            // Sisipkan formula dengan delimiters yang sesuai
            textarea.value = textBefore + openDelimiter + formula + closeDelimiter + textAfter;

            // Set kursor setelah formula
            const delimiterLength = isInline ? 4 : 4; // \( dan \) atau $$ dan $$
            textarea.selectionStart = cursorPos + formula.length + delimiterLength;
            textarea.selectionEnd = cursorPos + formula.length + delimiterLength;

            // Sembunyikan modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('formulaModal'));
            modal.hide();

            // Focus kembali ke textarea
            setTimeout(() => {
                textarea.focus();
            }, 300);
        }

        // Ganti fungsi showFormulaModal
        let formulaModalListener = null;

        function showFormulaModal(targetElement = 'pertanyaan') {
            // Bersihkan listener sebelumnya jika ada
            if (formulaModalListener) {
                document.getElementById('formulaModal')?.removeEventListener('shown.bs.modal', formulaModalListener);
                formulaModalListener = null;
            }

            // Set target
            document.getElementById('currentFormulaTarget').value = targetElement;

            const modal = new bootstrap.Modal(document.getElementById('formulaModal'));
            modal.show();

            // Init dengan tipe formula default
            const formulaTypeSelect = document.getElementById('formulaType');
            if (formulaTypeSelect) formulaTypeSelect.value = 'pecahan';

            // Buat listener baru dengan safeguard
            formulaModalListener = function() {
                try {
                    changeFormulaType();
                    // Delayed MathJax rendering
                    setTimeout(() => {
                        if (typeof MathJax !== 'undefined' && !document.hidden) {
                            try {
                                MathJax.typeset([document.getElementById('formulaPreview')]);
                            } catch (e) {
                                console.error("MathJax error:", e);
                            }
                        }
                    }, 500);
                } catch (e) {
                    console.error("Formula modal error:", e);
                }
            };

            // Pasang listener
            document.getElementById('formulaModal').addEventListener('shown.bs.modal', formulaModalListener, {
                once: true
            });
        }

        // Fungsi untuk menyisipkan formula dari template
        function insertFormula(formula) {
            const textarea = document.querySelector('textarea[name="pertanyaan"]');
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);

            // Sisipkan formula dengan tag delimiters
            textarea.value = textBefore + '$$' + formula + '$$' + textAfter;

            // Set kursor setelah formula
            textarea.selectionStart = cursorPos + formula.length + 4; // +4 untuk $$$$
            textarea.selectionEnd = cursorPos + formula.length + 4;

            // Sembunyikan modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('formulaModal'));
            modal.hide();

            // Focus kembali ke textarea
            setTimeout(() => {
                textarea.focus();
            }, 300);
        }

        // Fungsi untuk menyisipkan formula kustom
        function insertCustomFormula() {
            const formula = document.getElementById('customFormula').value.trim();
            if (formula) {
                insertFormula(formula);
                // Reset input
                document.getElementById('customFormula').value = '';
            }
        }

        // Ganti kode observer sebelumnya
        // Ganti kode observer sebelumnya
        // Ganti kode observer MathJax dengan ini
        document.addEventListener('DOMContentLoaded', function() {
            // Render formula pada halaman load
            if (typeof MathJax !== 'undefined') {
                MathJax.typeset();
            }

            // Observer yang lebih efisien
            let isRendering = false;
            let pendingChanges = false;
            let observerTimeout = null;

            const observer = new MutationObserver(function() {
                if (observerTimeout) {
                    clearTimeout(observerTimeout);
                }

                // Catat bahwa ada perubahan tertunda
                pendingChanges = true;

                // Tetapkan timeout untuk mengelompokkan perubahan bersama-sama
                observerTimeout = setTimeout(() => {
                    if (pendingChanges && !isRendering && typeof MathJax !== 'undefined' && !document.hidden) {
                        pendingChanges = false;
                        isRendering = true;

                        try {
                            MathJax.typeset();
                        } catch (e) {
                            console.error("MathJax rendering error:", e);
                        } finally {
                            isRendering = false;
                        }
                    }
                }, 500);
            });

            // Batasi scope observer
            const daftarSoal = document.getElementById('daftarSoal');
            if (daftarSoal) {
                observer.observe(daftarSoal, {
                    childList: true,
                    subtree: false, // Hanya perubahan langsung, bukan subtree
                    attributeFilter: ['class']
                });
            }

            // Disconnect observer saat halaman tidak terlihat
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && observer) {
                    observer.disconnect();
                } else if (!document.hidden && daftarSoal) {
                    observer.observe(daftarSoal, {
                        childList: true,
                        subtree: false,
                        attributeFilter: ['class']
                    });
                }
            });
        });
    </script>

    <!-- script untuk symbol -->
    <script>
        // Fungsi untuk menampilkan modal simbol
        function showSymbolsModal(targetElement = 'pertanyaan') {
            // Set target untuk menyisipkan simbol
            document.getElementById('currentSymbolTarget').value = targetElement;

            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('symbolsModal'));
            modal.show();
        }

        // Fungsi untuk menyisipkan simbol ke textarea atau input
        function insertSymbol(symbol) {
            const targetElement = document.getElementById('currentSymbolTarget').value;
            const textarea = document.querySelector(`[name="${targetElement}"]`);

            if (!textarea) return;

            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);

            // Sisipkan simbol pada posisi kursor
            textarea.value = textBefore + symbol + textAfter;

            // Atur kursor setelah simbol
            textarea.selectionStart = cursorPos + symbol.length;
            textarea.selectionEnd = cursorPos + symbol.length;

            // Focus kembali ke textarea
            textarea.focus();
            // Sembunyikan modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('symbolsModal'));
            if (modal) {
                modal.hide();
            }
        }
    </script>

    <script>
        // Tambahkan script ini pada bagian javascript Anda
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk menangani klik pada card soal
            const soalItems = document.querySelectorAll('.soal-item');
            soalItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Cek apakah item ini disabled
                    if (this.closest('label').classList.contains('disabled-item')) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    // Jangan lakukan apa-apa jika yang diklik adalah checkbox
                    if (e.target.type === 'checkbox') return;

                    // Cari checkbox di dalam item ini
                    const checkbox = this.querySelector('.soal-checkbox');
                    if (checkbox.disabled) return;

                    // Toggle status checkbox
                    checkbox.checked = !checkbox.checked;

                    // Trigger event change pada checkbox untuk update counter
                    const changeEvent = new Event('change');
                    checkbox.dispatchEvent(changeEvent);

                    // Toggle class selected pada card
                    this.classList.toggle('selected', checkbox.checked);
                });
            });

            // Tambahkan event listener juga pada checkboxes untuk toggle class selected
            const checkboxes = document.querySelectorAll('.soal-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Cek apakah checkbox di-disabled
                    if (this.disabled) {
                        return;
                    }

                    // Cari card parent
                    const card = this.closest('.soal-item');
                    // Toggle class selected berdasarkan status checkbox
                    card.classList.toggle('selected', this.checked);

                    // Update counter soal terpilih
                    updateSelectedCount();
                });
            });

            // Inisialisasi counter soal terpilih
            updateSelectedCount();
        });

        // Fungsi untuk update counter soal terpilih
        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('.soal-checkbox:checked').length;
            const countElement = document.getElementById('selectedCount');
            if (countElement) {
                countElement.textContent = selectedCount;
            }
        }
    </script>

    <!-- handling untuk menambahkan cerita atau deskripsi soal -->
    <script>
        // Fungsi untuk mengelola pemilihan soal
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi counter soal terpilih
            const updateSelectedCount = () => {
                const selectedCount = document.querySelectorAll('.soal-checkbox:checked').length;
                document.getElementById('selectedCount').textContent = selectedCount;
            };

            // Event listener untuk checkbox "Pilih Semua"
            document.getElementById('checkAllSoal')?.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.soal-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });

            // Event listener untuk setiap checkbox soal
            document.querySelectorAll('.soal-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Event listener untuk tombol simpan
            // Modify the existing btnSimpanDeskripsi click handler
            document.getElementById('btnSimpanDeskripsi').addEventListener('click', function() {
                // Validate form first
                const form = document.getElementById('formDeskripsiSoal');
                if (!form) return;

                const formData = new FormData(form);

                // Basic validation
                if (formData.get('title').trim() === '') {
                    alert('Judul deskripsi tidak boleh kosong!');
                    return;
                }

                if (formData.get('content').trim() === '') {
                    alert('Konten deskripsi tidak boleh kosong!');
                    return;
                }

                const selectedSoal = document.querySelectorAll('.soal-checkbox:checked');
                if (selectedSoal.length === 0) {
                    alert('Pilih minimal satu soal untuk ditautkan!');
                    return;
                }

                // Hide current modal
                const currentModal = bootstrap.Modal.getInstance(document.getElementById('deskripsiSoalModal'));
                currentModal.hide();

                // Show confirmation modal
                showKonfirmasiDeskripsiModal(form);
            });

            // Function to show confirmation modal
            function showKonfirmasiDeskripsiModal(form) {
                // Create the confirmation modal HTML
                const modalHTML = `
                <div class="modal fade" id="konfirmasiDeskripsiModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px;">
                            <div class="modal-body p-4">
                                <h5 class="fw-bold">Yakin Menambahkan Deskripsi?</h5>
                                <p class="mb-3" style="font-size:13px;">Anda akan menambahkan deskripsi cerita pada ${document.querySelectorAll('.soal-checkbox:checked').length} soal terpilih, periksa lebih lanjut di bawah</p>

                                            <div class="alert border bg-light" style="border-radius: 15px;">
                                                <div class="d-flex">
                                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                                    <div>
                                                        <p class="fw-bold p-0 m-0" style="font-size: 14px;">Perubahan Nomor Soal</p>
                                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Seluruh soal dengan deskripsi akan di urutkan menjadi soal nomor pertama, disusul kemudian oleh soal non deskripsi</p>
                                                    </div>
                                                </div>
                                            </div>

                                <div class="p-3 border rounded-4 mb-3">
                                                                <div class="mb-3 text-start">
                                    <p class="fw-medium mb-1" style="font-size: 15px;">Judul Deskripsi Anda</p>
                                    <p class="border p-2 rounded" style="font-size: 14px; background-color: #f5f5f5;">${form.querySelector('[name="title"]').value}</p>
                                </div>
                                
                                <div class="mb-3 text-start">
                                    <p class="fw-medium mb-1" style="font-size: 15px;">Konten Anda</p>
                                    <div class="border p-2 rounded" style="font-size: 14px; background-color: #f5f5f5; max-height: 150px; overflow-y: auto;">
                                        ${form.querySelector('[name="content"]').value.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                                
                                <div class="mb-3 text-start">
                                    <p class="fw-medium mb-1 d-flex align-items-center" style="font-size: 15px;">
                                        <i class="me-2" style="color:rgb(218, 119, 86);"></i>
                                        Soal yang Ditautkan
                                    </p>
                                    <div class="border rounded" style="max-height: 150px; overflow-y: auto;">
                                        <ul class="list-group list-group-flush">
                                            ${Array.from(document.querySelectorAll('.soal-checkbox:checked')).map((checkbox, index) => {
                                                const soalText = checkbox.closest('label').querySelector('.text-break').textContent.trim();
                                                const shortText = soalText.length > 60 ? soalText.substring(0, 60) + '...' : soalText;
                                                return `<li class="list-group-item py-2 px-3 d-flex align-items-center" style="background-color: #f5f5f5; border-color: #e9e9e9;">
                                                    <span class="badge bg-light text-dark me-2" style="font-size: 11px; border: 1px solid #ddd;">${index + 1}</span>
                                                    <span style="font-size: 13px;">${shortText}</span>
                                                </li>`;
                                            }).join('')}
                                        </ul>
                                    </div>
                                </div>
                                </div>
                                
                                
                                <div class="d-flex gap-2 btn-group">
                                    <button type="button" class="btn border px-4" id="btnKembaliDeskripsi" style="border-radius: 12px;">Kembali</button>
                                    <button type="button" class="btn color-web text-white px-4" id="btnKonfirmasiDeskripsi" style="border-radius: 12px;">
                                        <span>Simpan</span>
                                        <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                // Add the modal to the document body
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // Initialize and show the modal
                const konfirmasiModal = new bootstrap.Modal(document.getElementById('konfirmasiDeskripsiModal'));
                konfirmasiModal.show();

                // Handle "Kembali" button click - go back to the original modal
                document.getElementById('btnKembaliDeskripsi').addEventListener('click', function() {
                    konfirmasiModal.hide();

                    // When hidden, remove the confirmation modal and show the original modal again
                    document.getElementById('konfirmasiDeskripsiModal').addEventListener('hidden.bs.modal', function() {
                        document.getElementById('konfirmasiDeskripsiModal').remove();
                        const deskripsiModal = new bootstrap.Modal(document.getElementById('deskripsiSoalModal'));
                        deskripsiModal.show();
                    }, {
                        once: true
                    });
                });

                // Handle "Konfirmasi & Simpan" button click
                document.getElementById('btnKonfirmasiDeskripsi').addEventListener('click', function() {
                    // Call the original save function
                    simpanDeskripsiSoal();

                    // Remove this modal when done
                    document.getElementById('konfirmasiDeskripsiModal').addEventListener('hidden.bs.modal', function() {
                        document.getElementById('konfirmasiDeskripsiModal').remove();
                    }, {
                        once: true
                    });
                });

                // Also remove the modal when it's dismissed
                document.getElementById('konfirmasiDeskripsiModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('konfirmasiDeskripsiModal').remove();
                });
            }
        });

        // Fungsi untuk membuka modal deskripsi soal
        function bukaDeskripsiSoalModal() {
            // Reset form
            const form = document.getElementById('formDeskripsiSoal');
            if (form) {
                form.reset();
                const descInput = form.querySelector('[name="description_id"]');
                if (descInput) {
                    descInput.value = '';
                }
            }

            // Reset checkbox
            const checkboxes = document.querySelectorAll('.soal-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            const countElement = document.getElementById('selectedCount');
            if (countElement) {
                countElement.textContent = '0';
            }

            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('deskripsiSoalModal'));
            modal.show();
        }

        // Fungsi untuk mengelola pemilihan soal
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi counter soal terpilih
            const updateSelectedCount = function() {
                const selectedCount = document.querySelectorAll('.soal-checkbox:checked').length;
                const countElement = document.getElementById('selectedCount');
                if (countElement) {
                    countElement.textContent = selectedCount;
                }
            };

            // Event listener untuk checkbox "Pilih Semua"
            const checkAllSoal = document.getElementById('checkAllSoal');
            if (checkAllSoal) {
                checkAllSoal.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.soal-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedCount();
                });
            }

            // Event listener untuk setiap checkbox soal
            const checkboxes = document.querySelectorAll('.soal-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Event listener untuk tombol simpan
            const btnSimpan = document.getElementById('btnSimpanDeskripsi');
            if (btnSimpan) {
                btnSimpan.addEventListener('click', simpanDeskripsiSoal);
            }
        });


        // Fungsi untuk menyimpan deskripsi soal dan tautannya
        // Update the existing simpanDeskripsiSoal function to close any open modals
        async function simpanDeskripsiSoal() {
            const form = document.getElementById('formDeskripsiSoal');
            if (!form) return;

            const formData = new FormData(form);

            // Get the button from the currently visible modal (could be either modal)
            const visibleModal = document.querySelector('.modal.show');
            const button = visibleModal.querySelector('.btn.color-web');

            if (!button) return;

            const spinner = button.querySelector('.spinner-border');
            const buttonText = button.querySelector('span');
            button.disabled = true;

            if (spinner) spinner.classList.remove('d-none');
            if (buttonText) buttonText.textContent = 'Menyimpan...';

            try {
                const response = await fetch('simpan_deskripsi_soal.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Close any open modals
                    const openModals = document.querySelectorAll('.modal.show');
                    openModals.forEach(modalEl => {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    });

                    // Refresh halaman untuk menampilkan perubahan
                    location.reload();
                } else {
                    throw new Error(result.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan perubahan: ' + error.message);
            } finally {
                // Reset tombol
                if (button) button.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (buttonText) buttonText.textContent = 'Simpan';
            }
        }

        // Fungsi untuk membuka modal edit deskripsi
        async function editDeskripsi(id) {
            try {
                // Ambil data deskripsi
                const response = await fetch(`get_deskripsi.php?id=${id}`);
                const data = await response.json();

                if (data.status === 'success') {
                    // Reset form
                    const form = document.getElementById('formDeskripsiSoal');
                    if (!form) return;

                    form.reset();

                    // Isi form dengan data yang ada
                    const titleInput = form.querySelector('[name="title"]');
                    const contentInput = form.querySelector('[name="content"]');
                    const idInput = form.querySelector('[name="description_id"]');

                    if (titleInput) titleInput.value = data.deskripsi.title;
                    if (contentInput) contentInput.value = data.deskripsi.content;
                    if (idInput) idInput.value = id;

                    // Reset semua disabled item dan checkbox
                    document.querySelectorAll('.soal-item').forEach(item => {
                        item.classList.remove('selected');
                        const checkbox = item.querySelector('.soal-checkbox');
                        if (checkbox) checkbox.checked = false;
                    });

                    document.querySelectorAll('.disabled-item').forEach(item => {
                        // Periksa apakah item ini terkait dengan deskripsi yang sedang diedit
                        const checkbox = item.querySelector('.soal-checkbox');
                        if (checkbox && data.soal_terkait.includes(parseInt(checkbox.value))) {
                            // Jika ya, hapus disabled dan opacity
                            item.classList.remove('disabled-item');
                            item.style.opacity = '1';
                            item.style.pointerEvents = 'auto';
                            checkbox.disabled = false;
                        }
                    });

                    // Centang checkbox soal yang sudah ditautkan
                    const soalTerkait = data.soal_terkait;
                    document.querySelectorAll('.soal-checkbox').forEach(checkbox => {
                        const soalId = parseInt(checkbox.value);

                        // Jika soal ini terikat dengan deskripsi yang sedang diedit
                        if (soalTerkait.includes(soalId)) {
                            checkbox.checked = true;

                            // Tambahkan class selected pada card
                            const card = checkbox.closest('.soal-item');
                            if (card) card.classList.add('selected');

                            // Pastikan label tidak disabled
                            const label = checkbox.closest('label');
                            if (label) {
                                label.classList.remove('disabled-item');
                                label.style.opacity = '1';
                                label.style.pointerEvents = 'auto';
                            }
                        }
                    });

                    // Update counter
                    const countElement = document.getElementById('selectedCount');
                    if (countElement) countElement.textContent = soalTerkait.length;

                    // Tampilkan modal
                    const modal = new bootstrap.Modal(document.getElementById('deskripsiSoalModal'));
                    modal.show();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                alert('Gagal mengambil data deskripsi: ' + error.message);
            }
        }

        // Fungsi untuk menghapus deskripsi
        // Fungsi untuk menghapus deskripsi
        function hapusDeskripsi(id) {
            // Create modal konfirmasi
            const modalHtml = `
        <div class="modal fade" id="hapusDeskripsiModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color:rgb(218, 119, 86);"></i>
                        <h5 class="mt-3 fw-bold">Hapus Cerita/Deskripsi</h5>
                        <p class="mb-3">Apakah Anda yakin ingin menghapus deskripsi ini?</p>
                        <div class="form-check mb-3 d-flex justify-content-center">
                            <input class="form-check-input me-2" type="checkbox" id="deleteLinkedSoal">
                            <label class="form-check-label" for="deleteLinkedSoal">
                                Hapus juga semua soal yang terikat
                            </label>
                        </div>
                        <div class="alert alert-warning" role="alert">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Jika tidak dicentang, soal-soal terkait akan dilepaskan dari deskripsi ini.
                            </small>
                        </div>
                        <div class="d-flex gap-2 btn-group">
                            <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                            <button type="button" class="btn btn-danger px-4" id="konfirmasiHapusDeskripsi" style="border-radius: 12px;">
                                <span>Hapus</span>
                                <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Add modal ke document
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Get modal element
            const modalElement = document.getElementById('hapusDeskripsiModal');
            const modal = new bootstrap.Modal(modalElement);

            // Show modal
            modal.show();

            // Handle delete confirmation
            document.getElementById('konfirmasiHapusDeskripsi').addEventListener('click', async () => {
                const button = document.getElementById('konfirmasiHapusDeskripsi');
                const spinner = button.querySelector('.spinner-border');
                const buttonText = button.querySelector('span');
                const deleteLinkedSoal = document.getElementById('deleteLinkedSoal').checked;

                try {
                    // Update button state
                    button.disabled = true;
                    spinner.classList.remove('d-none');
                    buttonText.textContent = 'Menghapus...';

                    const response = await fetch('hapus_deskripsi.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            description_id: id,
                            delete_linked_soal: deleteLinkedSoal
                        })
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        location.reload();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    alert('Gagal menghapus deskripsi: ' + error.message);
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
        /* Style untuk preview soal */
        .soal-text {
            font-size: 1.75rem;
            font-weight: 500;
            line-height: 1.4;
            transition: font-size 0.3s ease;
        }

        .soal-text.length-medium {
            font-size: 1.5rem;
        }

        .soal-text.length-long {
            font-size: 1.25rem;
        }

        .soal-text.length-very-long {
            font-size: 1rem;
        }

        /* Style untuk preview jawaban */
        .jawaban-preview .card {
            transition: all 0.2s;
        }

        .jawaban-preview .card-header {
            font-weight: 500;
        }

        .jawaban-preview .border-success {
            border-width: 2px;
        }

        .jawaban-preview .bg-success {
            background-color: rgb(218, 119, 86) !important;
            border-color: rgb(218, 119, 86) !important;
        }

        /* Style untuk soal preview */
        .preview-section {
            margin-bottom: 1.5rem;
        }

        .preview-section h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .soal-text {
            font-size: 1.75rem;
            font-weight: 500;
            line-height: 1.4;
            transition: font-size 0.3s ease;
        }

        .soal-text.length-medium {
            font-size: 1.5rem;
        }

        .soal-text.length-long {
            font-size: 1.25rem;
        }

        .soal-text.length-very-long {
            font-size: 1rem;
        }

        /* Preview option styling */
        #previewSoalModal .option-card {
            transition: all 0.2s ease;
        }

        #previewSoalModal .option-card.selected {
            background-color: #da7756;
            color: white;
            border-color: #da7756;
        }

        #previewSoalModal .alert-success {
            background-color: rgba(52, 199, 89, 0.1);
            border-color: rgba(52, 199, 89, 0.3);
            color: #34c759;
        }

        #previewSoalModal img {
            max-width: 100%;
            height: auto;
        }

        /* Animation for preview modal */
        #previewSoalModal .modal-content {
            animation: fadeInUp 0.3s;
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

                        // Handle existing image for soal
                        if (data.soal.gambar_soal) {
                            const existingImageContainer = form.querySelector('#existing_image_container');
                            const existingImage = form.querySelector('#existing_image');
                            existingImage.src = data.soal.gambar_soal;
                            existingImageContainer.classList.remove('d-none');
                        }

                        // Handle existing images for jawaban options
                        const options = ['a', 'b', 'c', 'd'];
                        options.forEach(option => {
                            if (data.soal[`gambar_jawaban_${option}`]) {
                                const existingContainer = form.querySelector(`#existing_jawaban_${option}`);
                                const existingImage = form.querySelector(`#existing_image_jawaban_${option}`);
                                existingImage.src = data.soal[`gambar_jawaban_${option}`];
                                existingContainer.classList.remove('d-none');
                            }
                        });
                    }

                    // Tampilkan modal
                    const formModal = new bootstrap.Modal(document.querySelector('#formSoalModal'));
                    formModal.show();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                alert('Silahkan segarkan halaman, ERROR : ' + error.message);
            }
        }

        function insertLineBreak() {
            const textarea = document.querySelector('textarea[name="pertanyaan"]');
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);

            textarea.value = textBefore + '<br>' + textAfter;

            // Set cursor position after the inserted <br> tag
            textarea.selectionStart = cursorPos + 4;
            textarea.selectionEnd = cursorPos + 4;
            textarea.focus();
        }

        // Inisialisasi tooltips
        function initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover', // Tooltip muncul saat hover
                    html: true, // Mengizinkan HTML di dalam tooltip
                    delay: {
                        show: 300,
                        hide: 100
                    } // Delay untuk menampilkan dan menyembunyikan tooltip
                });
            });
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
            <div class="modal-header bg-white border-0">
                <h5 class="modal-title fw-bold">${isEdit ? 'Edit' : 'Buat'} Soal Pilihan Ganda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="formSoal">
                    <input type="hidden" name="soal_id" value="">
                   
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Pertanyaan</label>
                        <div class="position-relative">
                            <div class="toolbar border rounded-top d-flex p-2 bg-light">
                                <button type="button" class="btn btn-sm border me-1" onclick="formatText('bold')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Tebalkan teks (Ctrl + B)">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm border me-1" onclick="formatText('italic')" data-bs-toggle="tooltip" data-bs-placement="top" title="Miringkan teks (Ctrl + I)">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm border me-1" onclick="formatText('underline')" data-bs-toggle="tooltip" data-bs-placement="top" title="Garis bawahi teks (Ctrl + U)">
                                    <i class="bi bi-type-underline"></i>
                                </button>
                            </div>
                            <textarea class="form-control rounded-top-0" id="pertanyaan" name="pertanyaan" rows="3" required style="border-radius: 0 0 12px 12px; resize: none;"></textarea>
                                <div class="d-flex gap-2 mt-2 justify-content-start">
                                    <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" 
                                            onclick="insertLineBreak()" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Baris baru (Ctrl + Enter)">
                                        <i class="ti ti-corner-down-left"></i> Enter
                                    </button>
                                    <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" 
                                            onclick="showFormulaModal('pertanyaan')" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Input formula">
                                        <i class="bi bi-plus-slash-minus"></i> Formula
                                    </button>
                                    <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" 
                                            onclick="document.querySelector('input[name=gambar_soal]').click()" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Unggah gambar pertanyaan">
                                        <i class="bi bi-image"></i> Gambar
                                    </button>
                                    <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" 
                                            onclick="showSymbolsModal('pertanyaan')" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="Tambah simbol">
                                        <i class="ti ti-omega"></i> Simbol
                                    </button>
                                    <input type="file" name="gambar_soal" accept="image/*" onchange="previewImage(this)" style="display: none;">
                                </div>
                        </div>
                    </div>      

                    <div id="preview_container" class="d-none animate__animated border p-3 mb-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-3 fw-bold">Preview Gambar</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="text-center">
                            <img id="image_preview" class="img-fluid rounded border" style="max-height: 200px; object-fit: contain;">
                        </div>
                    </div>

                    <div id="existing_image_container" class="d-none animate__animated border p-3 mb-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-3 fw-bold">Gambar Saat Ini</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteExistingImage()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="text-center">
                            <img id="existing_image" class="img-fluid rounded border" style="max-height: 200px; object-fit: contain;">
                        </div>
                    </div>

<div class="mb-4">
    <label class="form-label small fw-bold">Pilihan Jawaban</label>
    <div class="row row-cols-1 row-cols-md-2 g-3">
        <div class="col">
            <div class="mb-3 border mt-2 rounded-4 p-3">
                <label class="form-label small fw-bold">Jawaban A</label>
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold">A</span>
                        <input type="text" class="form-control" name="jawaban_a" required>
                    </div>
                    <div class="d-flex gap-2 mt-2 justify-content-start">
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showFormulaModal('jawaban_a')" data-bs-title="Tambah Formula" data-bs-toggle="tooltip" data-bs-placement="top" dataambah Formula" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="bi bi-plus-slash-minus"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" data-bs-title="Tambah Gambar" data-bs-toogle="tooltip" data-bs-placement="top" onclick="document.querySelector('input[name=gambar_jawaban_a]').click()">
                            <i class="bi bi-image"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" data-bs-title="Tambah Symbol" data-bs-toogle="tooltip" data-bs-placement="top" onclick="showSymbolsModal('jawaban_a')" title="Tambah Simbol">
                            <i class="ti ti-omega"></i>
                        </button>
                        <input type="file" name="gambar_jawaban_a" accept="image/*" onchange="previewJawabanImage(this, 'a')" style="display: none;">
                    </div>
                </div>
                <div id="preview_jawaban_a" class="preview-jawaban d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="image_jawaban_a" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="removeJawabanImage('a')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
                <div id="existing_jawaban_a" class="d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="existing_image_jawaban_a" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="deleteExistingJawabanImage('a')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="mb-3 border mt-2 rounded-4 p-3">
                <label class="form-label small fw-bold">Jawaban B</label>
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold">B</span>
                        <input type="text" class="form-control" name="jawaban_b" required>
                    </div>
                    <div class="d-flex gap-2 mt-2 justify-content-start">
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showFormulaModal('jawaban_b')" data-bs-title="Tambah Formula" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="bi bi-plus-slash-minus"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" title="Tambah Gambar" onclick="document.querySelector('input[name=gambar_jawaban_b]').click()">
                            <i class="bi bi-image"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showSymbolsModal('jawaban_b')" title="Tambah Simbol">
                            <i class="ti ti-omega"></i>
                        </button>
                        <input type="file" name="gambar_jawaban_b" accept="image/*" onchange="previewJawabanImage(this, 'b')" style="display: none;">
                    </div>
                </div>
                <div id="preview_jawaban_b" class="preview-jawaban d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="image_jawaban_b" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="removeJawabanImage('b')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
                <div id="existing_jawaban_b" class="d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="existing_image_jawaban_b" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="deleteExistingJawabanImage('b')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="mb-3 border mt-2 rounded-4 p-3">
                <label class="form-label small fw-bold">Jawaban C</label>
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold">C</span>
                        <input type="text" class="form-control" name="jawaban_c" required>
                    </div>
                    <div class="d-flex gap-2 mt-2 justify-content-start">
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showFormulaModal('jawaban_c')" data-bs-title="Tambah Formula" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="bi bi-plus-slash-minus"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" title="Tambah Gambar" onclick="document.querySelector('input[name=gambar_jawaban_c]').click()">
                            <i class="bi bi-image"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showSymbolsModal('jawaban_c')" title="Tambah Simbol">
                            <i class="ti ti-omega"></i>
                        </button>
                        <input type="file" name="gambar_jawaban_c" accept="image/*" onchange="previewJawabanImage(this, 'c')" style="display: none;">
                    </div>
                </div>
                <div id="preview_jawaban_c" class="preview-jawaban d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="image_jawaban_c" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="removeJawabanImage('c')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
                <div id="existing_jawaban_c" class="d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="existing_image_jawaban_c" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="deleteExistingJawabanImage('c')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="mb-3 border mt-2 rounded-4 p-3">
                <label class="form-label small fw-bold">Jawaban D</label>
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold">D</span>
                        <input type="text" class="form-control" name="jawaban_d" required>
                    </div>
                    <div class="d-flex gap-2 mt-2 justify-content-start">
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showFormulaModal('jawaban_d')" data-bs-title="Tambah Formula" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="bi bi-plus-slash-minus"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" title="Tambah Gambar" onclick="document.querySelector('input[name=gambar_jawaban_d]').click()">
                            <i class="bi bi-image"></i>
                        </button>
                        <button type="button" class="btn btn-sm border rounded-3 btn-outline-secondary" onclick="showSymbolsModal('jawaban_d')" title="Tambah Simbol">
                            <i class="ti ti-omega"></i>
                        </button>
                        <input type="file" name="gambar_jawaban_d" accept="image/*" onchange="previewJawabanImage(this, 'd')" style="display: none;">
                    </div>
                </div>
                <div id="preview_jawaban_d" class="preview-jawaban d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="image_jawaban_d" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="removeJawabanImage('d')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
                <div id="existing_jawaban_d" class="d-none mt-2">
                    <div class="d-flex flex-column align-items-stretch w-100">
                        <img id="existing_image_jawaban_d" class="img-fluid rounded border mb-2 w-100" style="max-height: 100px; object-fit: contain;">
                        <button type="button" class="btn btn-sm border border-danger text-danger w-100" onclick="deleteExistingJawabanImage('d')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </div>
                </div>
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
        // In the loadFormSoal function where the modal content is defined
        // For pilihan_ganda mode, update the save button in the modal footer:
        <div class="modal-footer btn-group bg-light border-0">
            <button type="button" class="btn border" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
            <button type="button" class="btn color-web text-white" onclick="simpanSoal()" style="border-radius: 12px;">
                <span>Simpan</span>
                <div class="spinner-border spinner-border-sm ms-2 d-none" role="status"></div>
            </button>
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

            // delay untuk memastikan modal sudah sepenuhnya dirender
            setTimeout(function() {
                // Setup clipboard paste
                setupClipboardPaste();

                // Setup auto-resize untuk textarea
                const textarea = document.getElementById('pertanyaan');
                if (textarea) {
                    // Setup initial height
                    autoResizeTextarea(textarea);

                    // Add event listener untuk input
                    textarea.addEventListener('input', function() {
                        autoResizeTextarea(this);
                    });
                }
            }, 300); // Delay untuk memastikan modal sudah sepenuhnya dirender

            // Setup auto-resize dan shortcut untuk textarea
            const textarea = document.getElementById('pertanyaan');
            if (textarea) {
                // Setup initial height
                autoResizeTextarea(textarea);

                // Add event listener untuk input
                textarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });

                // Setup keyboard shortcuts
                setupTextareaShortcuts(textarea);
            }

            setTimeout(function() {
                initTooltips();
            }, 400); // Beri sedikit delay untuk memastikan DOM sudah siap

        }

        // Fungsi untuk menangani keyboard shortcuts pada textarea
        function setupTextareaShortcuts(textarea) {
            if (!textarea) return;

            // Stack untuk undo/redo
            const undoStack = [];
            const redoStack = [];

            // Simpan state awal
            undoStack.push(textarea.value);

            // Fungsi untuk menyimpan state untuk undo
            function saveState() {
                // Simpan state saat ini ke undoStack jika berbeda dari state terakhir
                if (undoStack.length === 0 || undoStack[undoStack.length - 1] !== textarea.value) {
                    undoStack.push(textarea.value);
                    // Reset redoStack saat ada perubahan baru
                    redoStack.length = 0;

                    // Batasi ukuran stack
                    if (undoStack.length > 100) undoStack.shift();
                }
            }

            // Event listener untuk keydown
            textarea.addEventListener('keydown', function(e) {
                // Ctrl+Enter untuk <br>
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    // Simpan state sebelum perubahan
                    saveState();
                    insertTag('<br>', '', this);
                    return;
                }

                // Ctrl+B untuk <b>
                if (e.ctrlKey && e.key === 'b') {
                    e.preventDefault();
                    // Simpan state sebelum perubahan
                    saveState();
                    formatText('bold');
                    return;
                }

                // Ctrl+I untuk <i>
                if (e.ctrlKey && e.key === 'i') {
                    e.preventDefault();
                    // Simpan state sebelum perubahan
                    saveState();
                    formatText('italic');
                    return;
                }

                // Ctrl+U untuk <u>
                if (e.ctrlKey && e.key === 'u') {
                    e.preventDefault();
                    // Simpan state sebelum perubahan
                    saveState();
                    formatText('underline');
                    return;
                }

                // Undo (Ctrl+Z) - custom implementation
                if (e.ctrlKey && e.key === 'z') {
                    e.preventDefault();
                    // Pastikan ada state yang bisa di-undo
                    if (undoStack.length > 1) { // Minimal harus ada 2 item (state saat ini dan sebelumnya)
                        // Simpan state saat ini ke redoStack
                        redoStack.push(textarea.value);
                        // Hapus state saat ini dari undoStack
                        undoStack.pop();
                        // Terapkan state sebelumnya
                        textarea.value = undoStack[undoStack.length - 1];
                        // Update tinggi textarea
                        autoResizeTextarea(textarea);
                    }
                    return;
                }

                // Redo (Ctrl+Y) - custom implementation
                if (e.ctrlKey && e.key === 'y') {
                    e.preventDefault();
                    if (redoStack.length > 0) {
                        // Simpan state saat ini ke undoStack
                        undoStack.push(textarea.value);
                        // Ambil state terakhir dari redoStack
                        textarea.value = redoStack.pop();
                        // Update tinggi textarea
                        autoResizeTextarea(textarea);
                    }
                    return;
                }
            });

            // Simpan state setiap kali ada perubahan
            textarea.addEventListener('input', function() {
                saveState();
            });

            // Simpan state sebelum operasi format
            const originalFormatText = window.formatText;
            window.formatText = function(format) {
                saveState();
                originalFormatText(format);
            };

            // Simpan state sebelum operasi insert
            const originalInsertLineBreak = window.insertLineBreak;
            if (originalInsertLineBreak) {
                window.insertLineBreak = function() {
                    saveState();
                    originalInsertLineBreak();
                };
            }
        }

        // Modifikasi pada fungsi insertTag untuk menambahkan autoResize
        function insertTag(openTag, closeTag, element) {
            const startPos = element.selectionStart;
            const endPos = element.selectionEnd;
            const selectedText = element.value.substring(startPos, endPos);

            // Jika ada teks yang dipilih
            if (startPos !== endPos) {
                element.value = element.value.substring(0, startPos) +
                    openTag + selectedText + closeTag +
                    element.value.substring(endPos);
                element.selectionStart = startPos;
                element.selectionEnd = startPos + openTag.length + selectedText.length + closeTag.length;
            } else {
                // Jika tidak ada teks yang dipilih
                element.value = element.value.substring(0, startPos) +
                    openTag + closeTag +
                    element.value.substring(endPos);
                element.selectionStart = startPos + openTag.length;
                element.selectionEnd = startPos + openTag.length;
            }

            // Update tinggi textarea
            autoResizeTextarea(element);
            element.focus();
        }

        // Perbarui fungsi formatText yang sudah ada
        function formatText(format) {
            const textarea = document.getElementById('pertanyaan');
            if (!textarea) return;

            const selection = {
                start: textarea.selectionStart,
                end: textarea.selectionEnd,
                text: textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
            };

            let prefix, suffix;

            switch (format) {
                case 'bold':
                    prefix = '<b>';
                    suffix = '</b>';
                    break;
                case 'italic':
                    prefix = '<i>';
                    suffix = '</i>';
                    break;
                case 'underline':
                    prefix = '<u>';
                    suffix = '</u>';
                    break;
            }

            // Jika tidak ada teks yang dipilih, tambahkan tag kosong dan posisikan kursor di antara tag
            if (selection.start === selection.end) {
                const newText = textarea.value.substring(0, selection.start) + prefix + suffix + textarea.value.substring(selection.end);
                textarea.value = newText;
                textarea.selectionStart = selection.start + prefix.length;
                textarea.selectionEnd = selection.start + prefix.length;
            } else {
                // Jika ada teks yang dipilih, bungkus dengan tag format
                const newText = textarea.value.substring(0, selection.start) + prefix + selection.text + suffix + textarea.value.substring(selection.end);
                textarea.value = newText;
                textarea.selectionStart = selection.start;
                textarea.selectionEnd = selection.end + prefix.length + suffix.length;
            }

            // Update tinggi dan fokus
            autoResizeTextarea(textarea);
            textarea.focus();

            insertTag(prefix, suffix, textarea);
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

        // auto rezize textarea
        // Fungsi untuk auto-resize textarea
        function autoResizeTextarea(elem) {
            // Reset height terlebih dahulu
            elem.style.height = 'auto';
            // Set height baru berdasarkan konten
            elem.style.height = (elem.scrollHeight) + 'px';
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

        // Fungsi untuk preview gambar jawaban
        function previewJawabanImage(input, option) {
            const preview = document.getElementById(`image_jawaban_${option}`);
            const container = document.getElementById(`preview_jawaban_${option}`);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Fungsi untuk menghapus preview gambar jawaban
        function removeJawabanImage(option) {
            const fileInput = document.querySelector(`input[name="gambar_jawaban_${option}"]`);
            const preview = document.getElementById(`image_jawaban_${option}`);
            const container = document.getElementById(`preview_jawaban_${option}`);

            fileInput.value = '';
            preview.src = '';
            container.classList.add('d-none');
        }

        // Fungsi untuk menghapus gambar jawaban yang sudah ada
        async function deleteExistingJawabanImage(option) {
            const form = document.getElementById('formSoal');
            const soalId = form.querySelector('[name="soal_id"]').value;

            try {
                const response = await fetch('hapus_gambar_jawaban.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        soal_id: soalId,
                        option: option
                    })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    // Hide the existing image container
                    const container = document.getElementById(`existing_jawaban_${option}`);
                    container.classList.add('d-none');
                    // Clear the image
                    document.getElementById(`existing_image_jawaban_${option}`).src = '';
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert('Gagal menghapus gambar: ' + error.message);
            }
        }

        // Fungsi untuk setup event paste pada semua input
        function setupClipboardPaste() {
            // Setup paste event untuk textbox pertanyaan
            const pertanyaanTextbox = document.querySelector('textarea[name="pertanyaan"]');
            if (pertanyaanTextbox) {
                pertanyaanTextbox.addEventListener('paste', function(e) {
                    handlePasteImage(e, 'gambar_soal');
                });
            }

            // Tambahkan handler untuk otomatis mengubah simbol dari Word
            const allTextInputs = document.querySelectorAll('textarea, input[type="text"]');
            allTextInputs.forEach(input => {
                input.addEventListener('paste', function(e) {
                    // Ambil teks dari clipboard
                    const clipboardData = e.clipboardData || window.clipboardData;
                    const pastedText = clipboardData.getData('text');

                    // Jika ada simbol yang mungkin bermasalah, tangani secara khusus
                    if (/[≤≥≠≈∞∫∑∏∂±×÷√α-ωΑ-Ω]/.test(pastedText)) {
                        // Secara manual menyisipkan teks dan mencegah default paste
                        e.preventDefault();

                        const cursorPos = this.selectionStart;
                        const textBefore = this.value.substring(0, cursorPos);
                        const textAfter = this.value.substring(cursorPos);

                        // Sisipkan teks yang disanitasi
                        this.value = textBefore + pastedText + textAfter;

                        // Atur kursor setelah teks yang disisipkan
                        this.selectionStart = cursorPos + pastedText.length;
                        this.selectionEnd = cursorPos + pastedText.length;
                    }
                });
            });

            // Setup paste event untuk textbox jawaban
            const jawabanInputs = ['jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d'];
            jawabanInputs.forEach(jawaban => {
                const input = document.querySelector(`input[name="${jawaban}"]`);
                if (input) {
                    input.addEventListener('paste', function(e) {
                        const option = jawaban.split('_')[1]; // Ambil 'a', 'b', 'c', atau 'd'
                        handlePasteImage(e, `gambar_jawaban_${option}`);
                    });
                }
            });
        }

        // Fungsi untuk menangani paste gambar
        function handlePasteImage(e, targetInputName) {
            // Cek jika clipboard berisi item
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;

            if (!items) return;

            let hasImage = false;

            for (let i = 0; i < items.length; i++) {
                // Cek jika item berisi gambar
                if (items[i].type.indexOf('image') === 0) {
                    hasImage = true;

                    // Cegah default paste text jika ada gambar
                    e.preventDefault();

                    // Ambil blob dari clipboard
                    const blob = items[i].getAsFile();

                    // Buat nama file
                    const fileExtension = blob.type.split('/')[1] || 'png';
                    const fileName = `pasted_image_${new Date().getTime()}.${fileExtension}`;

                    // Buat file dari blob
                    const file = new File([blob], fileName, {
                        type: blob.type
                    });

                    // Set file ke input yang sesuai
                    const fileInput = document.querySelector(`input[name="${targetInputName}"]`);

                    if (!fileInput) return;

                    // Buat FileList (ini trik untuk membuat FileList-like object)
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    // Trigger event change untuk mengaktifkan fungsi preview
                    if (targetInputName === 'gambar_soal') {
                        previewImage(fileInput);
                    } else {
                        const option = targetInputName.split('_')[2]; // Ambil 'a', 'b', 'c', atau 'd'
                        previewJawabanImage(fileInput, option);
                    }

                    // Hanya proses satu gambar
                    break;
                }
            }
        }

        // mengatur bold, italic, dan underline pada soal
        function formatText(format) {
            const textarea = document.getElementById('pertanyaan');
            const selection = {
                start: textarea.selectionStart,
                end: textarea.selectionEnd,
                text: textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
            };

            let prefix, suffix;

            switch (format) {
                case 'bold':
                    prefix = '<b>';
                    suffix = '</b>';
                    break;
                case 'italic':
                    prefix = '<i>';
                    suffix = '</i>';
                    break;
                case 'underline':
                    prefix = '<u>';
                    suffix = '</u>';
                    break;
            }

            // Jika tidak ada teks yang dipilih, tambahkan tag kosong dan posisikan kursor di antara tag
            if (selection.start === selection.end) {
                const newText = textarea.value.substring(0, selection.start) + prefix + suffix + textarea.value.substring(selection.end);
                textarea.value = newText;
                textarea.selectionStart = selection.start + prefix.length;
                textarea.selectionEnd = selection.start + prefix.length;
            } else {
                // Jika ada teks yang dipilih, bungkus dengan tag format
                const newText = textarea.value.substring(0, selection.start) + prefix + selection.text + suffix + textarea.value.substring(selection.end);
                textarea.value = newText;
                textarea.selectionStart = selection.start;
                textarea.selectionEnd = selection.end + prefix.length + suffix.length;
            }

            textarea.focus();
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

            // Get the button more reliably
            const button = document.querySelector('#formSoalModal .modal-footer .btn.color-web');

            // Make sure button exists
            if (!button) {
                console.error('Button not found!');
                return;
            }

            // Get spinner and text elements
            const spinner = button.querySelector('.spinner-border');
            const buttonText = button.querySelector('span');

            // Debug check to console
            console.log('Button:', button);
            console.log('Spinner:', spinner);
            console.log('Text:', buttonText);

            // Show loader, hide text
            button.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (buttonText) buttonText.textContent = 'Menyimpan...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    location.reload(); // Refresh halaman untuk menampilkan perubahan
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);

                // Reset button state if there's an error
                button.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (buttonText) buttonText.textContent = 'Simpan';
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



    <script>
        // Script untuk menangani modal edit identitas
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi tombol Edit Identitas Ujian
            const editIdentitasBtn = document.querySelector('.btn[style*="background-color:rgb(199, 99, 66)"]');
            if (editIdentitasBtn) {
                editIdentitasBtn.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(document.getElementById('editIdentitasModal'));
                    modal.show();
                });
            }

            // Tambahkan event listener untuk tombol Simpan
            const btnSimpanIdentitas = document.getElementById('btnSimpanIdentitas');
            if (btnSimpanIdentitas) {
                btnSimpanIdentitas.addEventListener('click', simpanIdentitasUjian);
            }
        });

        // Fungsi untuk menambah input materi baru
        function tambahMateri() {
            console.log('Menambah input materi baru');
            const materiContainer = document.getElementById('materiContainer');
            const newInputGroup = document.createElement('div');
            newInputGroup.className = 'input-group mb-2';
            newInputGroup.innerHTML = `
        <input type="text" class="form-control" name="materi[]" placeholder="Masukkan materi" style="border-radius: 12px 0 0 12px; font-size: 16px; border: 1px solid #e4e4e4; background-color: #f5f5f5;">
        <button class="btn btn-outline-danger" type="button" onclick="hapusMateri(this)" style="border-radius: 0 12px 12px 0;"><i class="bi bi-trash"></i></button>
    `;
            materiContainer.appendChild(newInputGroup);
        }

        // Fungsi untuk menghapus input materi
        function hapusMateri(button) {
            console.log('Menghapus input materi');
            const inputGroup = button.parentElement;
            inputGroup.remove();
        }

        // Validasi tanggal
        function validasiTanggal() {
            const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
            const tanggalSelesai = new Date(document.getElementById('tanggal_selesai').value);

            if (tanggalSelesai < tanggalMulai) {
                alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
                // Reset tanggal selesai ke nilai yang sama dengan tanggal mulai
                document.getElementById('tanggal_selesai').value = document.getElementById('tanggal_mulai').value;
                hitungDurasiOtomatis();
                return false;
            }
            return true;
        }

        // Tambahkan ke event listener tanggal selesai
        tanggalSelesai.addEventListener('change', function() {
            if (validasiTanggal()) {
                hitungDurasiOtomatis();
            }
        });

        // Fungsi untuk menyimpan perubahan identitas ujian
        async function simpanIdentitasUjian() {
            const form = document.getElementById('formEditIdentitas');
            const formData = new FormData(form);

            // Validasi sederhana
            const judul = formData.get('judul');
            const mata_pelajaran = formData.get('mata_pelajaran');
            const durasi = formData.get('durasi');
            const tanggal_mulai = formData.get('tanggal_mulai');
            const tanggal_selesai = formData.get('tanggal_selesai');

            if (!judul || !mata_pelajaran || !durasi || !tanggal_mulai || !tanggal_selesai) {
                alert('Mohon lengkapi semua field yang diperlukan.');
                return;
            }

            // Update UI tombol
            const button = document.getElementById('btnSimpanIdentitas');
            const spinner = button.querySelector('.spinner-border');
            const buttonText = button.querySelector('span');
            button.disabled = true;
            spinner.classList.remove('d-none');
            buttonText.textContent = 'Menyimpan...';

            try {
                const response = await fetch('update_identitas_ujian.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Tutup modal dan refresh halaman
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editIdentitasModal'));
                    modal.hide();

                    // Tampilkan pesan sukses
                    alert('Identitas ujian berhasil diperbarui.');

                    // Refresh halaman
                    location.reload();
                } else {
                    throw new Error(result.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan perubahan: ' + error.message);
            } finally {
                // Reset tombol
                button.disabled = false;
                spinner.classList.add('d-none');
                buttonText.textContent = 'Simpan';
            }
        }
    </script>

    <script>
        document.getElementById('editIdentitasModal').addEventListener('hidden.bs.modal', function() {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        });

        // Fungsi untuk menghitung durasi otomatis
        function hitungDurasiOtomatis() {
            const tanggalMulai = document.getElementById('tanggal_mulai').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai').value;

            // Pastikan kedua nilai tanggal ada
            if (tanggalMulai && tanggalSelesai) {
                // Convert ke objek Date
                const mulai = new Date(tanggalMulai);
                const selesai = new Date(tanggalSelesai);

                // Hitung selisih dalam milidetik
                const selisihMilidetik = selesai - mulai;

                // Validasi jika tanggal selesai lebih dulu dari tanggal mulai
                if (selisihMilidetik < 0) {
                    alert('Error: Waktu selesai tidak boleh lebih awal dari waktu mulai!');
                    return;
                }

                // Konversi ke menit
                const selisihMenit = Math.round(selisihMilidetik / (1000 * 60));

                // Update field durasi
                document.getElementById('durasi').value = selisihMenit;
            }
        }

        // Tambahkan event listener saat DOM selesai dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener yang sudah ada tetap dipertahankan

            // Tambahkan event listener untuk menghitung durasi otomatis
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalSelesai = document.getElementById('tanggal_selesai');

            if (tanggalMulai && tanggalSelesai) {
                tanggalMulai.addEventListener('change', hitungDurasiOtomatis);
                tanggalSelesai.addEventListener('change', hitungDurasiOtomatis);
            }

            // Buat field durasi menjadi readonly
            const durasiField = document.getElementById('durasi');
            if (durasiField) {
                durasiField.readOnly = true;
            }
        });
    </script>

    <script>
        // Variabel untuk menyimpan konten asli soal
        const originalSoalContent = new Map();

        // Fungsi untuk menyimpan konten asli soal
        function saveOriginalContent(soalId, content) {
            originalSoalContent.set(soalId, content);
        }

        // Fungsi untuk mendapatkan konten asli soal
        function getOriginalContent(soalId) {
            return originalSoalContent.get(soalId);
        }

        // Modifikasi fungsi showAIResult untuk handle replacement
        function showAIResult(soalCard, result, type, originalContent) {
            if (!soalCard) return;

            hideAILoader(soalCard);

            if (type === 'analysis') {
                // Untuk analisis, tetap seperti sebelumnya (menambah card baru)
                const resultHTML = `
            <div class="ai-result ai-result-analysis alert border bg-light">
                <h6 class="fw-bold mb-3"><i class="ti ti-analyze me-2"></i>Hasil Analisa AI</h6>
                <div class="mb-3">
                    <strong>Bab/Topik:</strong>
                    <p class="mb-2">${result.bab || 'Tidak teridentifikasi'}</p>
                </div>
                <div class="mb-3">
                    <strong>Alur Tujuan Pembelajaran:</strong>
                    <p class="mb-2">${result.alur_tujuan || 'Tidak teridentifikasi'}</p>
                </div>
                <div class="mb-3">
                    <strong>Capaian Pembelajaran:</strong>
                    <p class="mb-2">${result.capaian || 'Tidak teridentifikasi'}</p>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn flex-fill text-white btn-close-ai" style="border-radius: 12px; background-color: rgb(218, 119, 86);">
                        <p class="m-0 p-0 text-white">Tutup</p>
                    </button>
                </div>
            </div>
        `;
                soalCard.insertAdjacentHTML('beforeend', resultHTML);
                setupAIResultEventListeners(soalCard);
            } else {
                // Untuk fitur lain, replace konten soal
                replaceQuestionContent(soalCard, result, type, originalContent);
            }
        }

        // Fungsi baru untuk replace konten soal
        function replaceQuestionContent(soalCard, aiResult, type, originalContent) {
            const soalId = aiResult.soal_id;

            // Simpan konten asli jika belum disimpan
            if (!getOriginalContent(soalId)) {
                const originalData = {
                    pertanyaan: originalContent.pertanyaan,
                    jawaban_a: originalContent.jawaban_a,
                    jawaban_b: originalContent.jawaban_b,
                    jawaban_c: originalContent.jawaban_c,
                    jawaban_d: originalContent.jawaban_d,
                    jawaban_benar: originalContent.jawaban_benar
                };
                saveOriginalContent(soalId, originalData);
            }

            // Replace pertanyaan
            const pertanyaanElement = soalCard.querySelector('p');
            if (pertanyaanElement && aiResult.pertanyaan) {
                pertanyaanElement.innerHTML = aiResult.pertanyaan;
                pertanyaanElement.setAttribute('data-original', originalContent.pertanyaan);
                pertanyaanElement.setAttribute('data-ai', aiResult.pertanyaan);
            }

            // Replace jawaban
            const jawabanElements = soalCard.querySelectorAll('.pilihan');
            if (jawabanElements.length >= 4) {
                // Jawaban A
                if (aiResult.jawaban_a) {
                    const aElement = jawabanElements[0];
                    aElement.innerHTML = 'A. ' + aiResult.jawaban_a;
                    aElement.setAttribute('data-original', 'A. ' + originalContent.jawaban_a);
                    aElement.setAttribute('data-ai', 'A. ' + aiResult.jawaban_a);
                }

                // Jawaban B
                if (aiResult.jawaban_b) {
                    const bElement = jawabanElements[1];
                    bElement.innerHTML = 'B. ' + aiResult.jawaban_b;
                    bElement.setAttribute('data-original', 'B. ' + originalContent.jawaban_b);
                    bElement.setAttribute('data-ai', 'B. ' + aiResult.jawaban_b);
                }

                // Jawaban C
                if (aiResult.jawaban_c) {
                    const cElement = jawabanElements[2];
                    cElement.innerHTML = 'C. ' + aiResult.jawaban_c;
                    cElement.setAttribute('data-original', 'C. ' + originalContent.jawaban_c);
                    cElement.setAttribute('data-ai', 'C. ' + aiResult.jawaban_c);
                }

                // Jawaban D
                if (aiResult.jawaban_d) {
                    const dElement = jawabanElements[3];
                    dElement.innerHTML = 'D. ' + aiResult.jawaban_d;
                    dElement.setAttribute('data-original', 'D. ' + originalContent.jawaban_d);
                    dElement.setAttribute('data-ai', 'D. ' + aiResult.jawaban_d);
                }
            }

            // Tambahkan tombol navigasi AI
            addAINavigationButtons(soalCard, soalId, type, aiResult);

            // Tambahkan class untuk styling
            soalCard.classList.add('ai-modified');

            // Render MathJax jika ada formula
            if (typeof MathJax !== 'undefined') {
                try {
                    MathJax.typeset([soalCard]);
                } catch (error) {
                    console.error('MathJax error:', error);
                }
            }
        }

        // Fungsi untuk menambahkan tombol navigasi AI
        // Update fungsi addAINavigationButtons untuk label tombol yang lebih jelas
        function addAINavigationButtons(soalCard, soalId, type, aiResult) {
            // Hapus navigation buttons yang sudah ada
            const existingNav = soalCard.querySelector('.ai-navigation');
            if (existingNav) existingNav.remove();

            const typeLabels = {
                'perpanjang': 'Perpanjang',
                'perpendek': 'Perpendek',
                'parafrase': 'Parafrase',
                'terjemah': 'Terjemahan'
            };

            const navigationHTML = `
        <div class="ai-navigation mt-3 p-3 bg-light rounded-3 border animate__animated animate__fadeInUp">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-stars me-2" style="color: rgb(218, 119, 86);"></i>
                <small class="fw-bold text-muted" id="status_${soalId}">Hasil ${typeLabels[type] || 'AI'} - Sedang menampilkan versi AI</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm border btn-outline-secondary flex-fill" onclick="toggleVersions(${soalId})" style="border-radius: 8px;" id="toggleBtn_${soalId}">
                    <i class="ti ti-arrow-back-up me-1"></i>Versi Sebelumnya
                </button>
                <button class="btn btn-sm text-white flex-fill" onclick="saveAIChanges(${soalId}, '${type}')" style="border-radius: 8px; background-color: rgb(218, 119, 86);" id="saveBtn_${soalId}">
                    <i class="ti ti-check me-1"></i>Simpan Versi Ini
                </button>
            </div>
        </div>
    `;

            soalCard.insertAdjacentHTML('beforeend', navigationHTML);

            // Simpan data AI result untuk nanti digunakan saat save
            soalCard.setAttribute('data-ai-result', JSON.stringify(aiResult));
            soalCard.setAttribute('data-ai-type', type);
            soalCard.setAttribute('data-showing-ai', 'true'); // Track state: true = AI, false = original
        }

        // Fungsi untuk toggle antara versi AI dan original (sudah diperbaiki)
        function toggleVersions(soalId) {
            const soalCard = getSoalCard(soalId);
            const toggleBtn = document.getElementById(`toggleBtn_${soalId}`);
            const saveBtn = document.getElementById(`saveBtn_${soalId}`);
            const statusText = document.getElementById(`status_${soalId}`);

            if (!soalCard || !toggleBtn) return;

            const showingAI = soalCard.getAttribute('data-showing-ai') === 'true';

            if (showingAI) {
                // Sedang tampil AI, switch ke original
                showOriginalContentInPlace(soalCard);
                soalCard.setAttribute('data-showing-ai', 'false');

                // Update tombol dan status
                toggleBtn.innerHTML = '<i class="ti ti-arrow-forward me-1"></i>Versi AI';
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-outline-primary');

                if (statusText) statusText.textContent = 'Sedang menampilkan versi asli';
                if (saveBtn) saveBtn.innerHTML = '<i class="ti ti-check me-1"></i>Simpan Versi Asli';

            } else {
                // Sedang tampil original, switch ke AI
                showAIContentInPlace(soalCard);
                soalCard.setAttribute('data-showing-ai', 'true');

                // Update tombol dan status
                toggleBtn.innerHTML = '<i class="ti ti-arrow-back-up me-1"></i>Versi Sebelumnya';
                toggleBtn.classList.remove('btn-outline-primary');
                toggleBtn.classList.add('btn-outline-secondary');

                if (statusText) statusText.textContent = 'Sedang menampilkan versi AI';
                if (saveBtn) saveBtn.innerHTML = '<i class="ti ti-check me-1"></i>Simpan Versi AI';
            }
        }



        // Fungsi untuk menampilkan konten original di tempat
        function showOriginalContentInPlace(soalCard) {
            if (!soalCard) return;

            // Restore pertanyaan
            const pertanyaanElement = soalCard.querySelector('p[data-original]');
            if (pertanyaanElement) {
                pertanyaanElement.innerHTML = pertanyaanElement.getAttribute('data-original');
            }

            // Restore jawaban
            const jawabanElements = soalCard.querySelectorAll('.pilihan[data-original]');
            jawabanElements.forEach(element => {
                element.innerHTML = element.getAttribute('data-original');
            });

            // Render MathJax jika ada formula
            if (typeof MathJax !== 'undefined') {
                try {
                    MathJax.typeset([soalCard]);
                } catch (error) {
                    console.error('MathJax error:', error);
                }
            }
        }

        // Fungsi untuk menampilkan konten AI di tempat
        function showAIContentInPlace(soalCard) {
            if (!soalCard) return;

            // Restore pertanyaan AI
            const pertanyaanElement = soalCard.querySelector('p[data-ai]');
            if (pertanyaanElement) {
                pertanyaanElement.innerHTML = pertanyaanElement.getAttribute('data-ai');
            }

            // Restore jawaban AI
            const jawabanElements = soalCard.querySelectorAll('.pilihan[data-ai]');
            jawabanElements.forEach(element => {
                element.innerHTML = element.getAttribute('data-ai');
            });

            // Render MathJax jika ada formula
            if (typeof MathJax !== 'undefined') {
                try {
                    MathJax.typeset([soalCard]);
                } catch (error) {
                    console.error('MathJax error:', error);
                }
            }
        }

        // Fungsi untuk menyimpan perubahan AI (yang sudah diperbaiki)
        async function saveAIChanges(soalId, type) {
            const soalCard = getSoalCard(soalId);
            if (!soalCard) return;

            const button = soalCard.querySelector('.ai-navigation .btn[onclick*="saveAIChanges"]');

            // Make sure button exists
            if (!button) {
                console.error('Button not found!');
                return;
            }

            // Update button state
            const originalHTML = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            button.disabled = true;

            try {
                // **PERBAIKAN: Cek versi mana yang sedang ditampilkan**
                const showingAI = soalCard.getAttribute('data-showing-ai') === 'true';
                let updateData;

                if (showingAI) {
                    // Jika sedang menampilkan versi AI, simpan versi AI
                    const aiResultData = JSON.parse(soalCard.getAttribute('data-ai-result'));
                    updateData = {
                        soal_id: soalId,
                        type: type,
                        pertanyaan: aiResultData.pertanyaan,
                        jawaban_a: aiResultData.jawaban_a,
                        jawaban_b: aiResultData.jawaban_b,
                        jawaban_c: aiResultData.jawaban_c,
                        jawaban_d: aiResultData.jawaban_d
                    };
                } else {
                    // Jika sedang menampilkan versi asli, simpan versi asli
                    const originalData = getOriginalContent(soalId);
                    updateData = {
                        soal_id: soalId,
                        type: 'restore_original', // Tandai sebagai restore
                        pertanyaan: originalData.pertanyaan,
                        jawaban_a: originalData.jawaban_a,
                        jawaban_b: originalData.jawaban_b,
                        jawaban_c: originalData.jawaban_c,
                        jawaban_d: originalData.jawaban_d
                    };
                }

                const response = await fetch('update_soal_ai.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(updateData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Hapus navigasi AI dan data temporary
                    const navigation = soalCard.querySelector('.ai-navigation');
                    if (navigation) navigation.remove();

                    soalCard.classList.remove('ai-modified');
                    soalCard.removeAttribute('data-ai-result');
                    soalCard.removeAttribute('data-ai-type');
                    soalCard.removeAttribute('data-showing-ai');

                    // Hapus data attributes untuk toggle
                    const elements = soalCard.querySelectorAll('[data-original], [data-ai]');
                    elements.forEach(el => {
                        el.removeAttribute('data-original');
                        el.removeAttribute('data-ai');
                    });

                    // Hapus data original dari Map
                    originalSoalContent.delete(soalId);

                    // Tampilkan pesan sukses yang lebih spesifik
                    const message = showingAI ? 'Perubahan AI berhasil disimpan!' : 'Soal berhasil dikembalikan ke versi asli!';
                    showSuccessMessage(message);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error saving changes:', error);
                alert('Gagal menyimpan perubahan: ' + error.message);
            } finally {
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
        }

        // Fungsi untuk menampilkan pesan sukses
        function showSuccessMessage(message) {
            const alertHTML = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
            document.body.insertAdjacentHTML('beforeend', alertHTML);

            // Auto dismiss after 3 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 3000);
        }

        // Update fungsi setupAIResultEventListeners untuk handle analisis saja
        function setupAIResultEventListeners(soalCard) {
            // Event listener untuk tombol tutup (hanya untuk analisis)
            const closeButtons = soalCard.querySelectorAll('.btn-close-ai');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeAIResult(this);
                });
            });
        }
    </script>



    <script>
        // Konfigurasi Groq API
        const GROQ_API_KEY = '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'; // Ganti dengan API key Anda

        // Perbaikan error tanggalSelesai
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan elemen tanggalSelesai ada sebelum digunakan
            const tanggalSelesaiElement = document.getElementById('tanggal_selesai');
            if (tanggalSelesaiElement) {
                tanggalSelesaiElement.addEventListener('change', function() {
                    if (validasiTanggal()) {
                        hitungDurasiOtomatis();
                    }
                });
            }
        });

        // Fungsi duplikasi soal
        async function duplikasiSoal(id) {
            try {
                const response = await fetch('duplikasi_soal.php', {
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
                    // Reload halaman untuk melihat soal yang sudah diduplikasi
                    location.reload();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                alert('Gagal menduplikasi soal: ' + error.message);
            }
        }

        // Fungsi untuk mendapatkan elemen soal card berdasarkan ID
        function getSoalCard(soalId) {
            // Cari elemen yang mengandung soal dengan ID tertentu
            const buttons = document.querySelectorAll(`button[onclick*="editSoal(${soalId})"]`);
            if (buttons.length > 0) {
                // Cari parent .soal-card
                let element = buttons[0];
                while (element && !element.classList.contains('soal-card')) {
                    element = element.parentElement;
                }
                return element;
            }
            return null;
        }

        // Fungsi untuk menambahkan loader ke card
        function showAILoader(soalCard) {
            if (!soalCard) return;

            soalCard.classList.add('ai-processing');

            // Tambahkan loader overlay jika belum ada
            if (!soalCard.querySelector('.ai-loader-overlay')) {
                const loaderHTML = `
        <div class="ai-loader-overlay">
            <img src="assets/ai_card.gif" style="width:80px;" alt="AI sedang bekerja">
            <p class="mt-2 text-white" style="font-size: 14px;">AI sedang bekerja...</p>
        </div>
    `;
                soalCard.insertAdjacentHTML('beforeend', loaderHTML);
            }
        }

        // Fungsi untuk menghilangkan loader
        function hideAILoader(soalCard) {
            if (!soalCard) return;

            soalCard.classList.remove('ai-processing');
            soalCard.classList.add('ai-complete');

            setTimeout(() => {
                soalCard.classList.remove('ai-complete');
            }, 500);
        }


        // Fungsi untuk menutup hasil AI
        function closeAIResult(button) {
            const aiResult = button.closest('.ai-result');
            const soalCard = button.closest('.soal-card');

            if (!aiResult) return;

            aiResult.style.animation = 'slideUp 0.3s ease';

            setTimeout(() => {
                aiResult.remove();

                // Reset margin untuk card berikutnya
                if (soalCard) {
                    const nextCard = soalCard.nextElementSibling;
                    if (nextCard && nextCard.classList.contains('soal-card')) {
                        nextCard.style.marginTop = '';
                    }
                }
            }, 300);
        }

        // Fungsi AI Perpanjang
        async function aiPerpanjang(soalId, event) {
            if (event) event.preventDefault();

            const soalCard = getSoalCard(soalId);
            showAILoader(soalCard);

            try {
                const soalData = await getSoalData(soalId);

                // Buat prompt untuk pertanyaan + jawaban
                const prompt = `Perpanjang soal pilihan ganda berikut ini dengan menambahkan detail dan konteks yang relevan tanpa mengubah inti pertanyaan. Pertahankan format dan struktur aslinya.

SOAL ASLI:
Pertanyaan: ${soalData.pertanyaan}
A. ${soalData.jawaban_a}
B. ${soalData.jawaban_b}
C. ${soalData.jawaban_c}
D. ${soalData.jawaban_d}
Jawaban Benar: ${soalData.jawaban_benar}

Berikan output dalam format JSON seperti ini:
{
    "pertanyaan": "pertanyaan yang diperpanjang",
    "jawaban_a": "jawaban A yang diperpanjang",
    "jawaban_b": "jawaban B yang diperpanjang", 
    "jawaban_c": "jawaban C yang diperpanjang",
    "jawaban_d": "jawaban D yang diperpanjang"
}`;

                const result = await callGroqAPI(prompt, true); // true untuk parse JSON
                showAIResult(soalCard, {
                    soal_id: soalId,
                    ...result
                }, 'perpanjang', soalData);
            } catch (error) {
                hideAILoader(soalCard);
                alert('Gagal memproses dengan AI: ' + error.message);
            }
        }

        // Fungsi AI Perpendek - memproses pertanyaan + jawaban
        async function aiPerpendek(soalId, event) {
            if (event) event.preventDefault();

            const soalCard = getSoalCard(soalId);
            if (!soalCard) {
                console.error('Soal card tidak ditemukan untuk ID:', soalId);
                return;
            }

            showAILoader(soalCard);

            try {
                const soalData = await getSoalData(soalId);

                // Buat prompt untuk pertanyaan + jawaban
                const prompt = `Perpendek soal pilihan ganda berikut ini dengan tetap mempertahankan makna dan informasi penting. Buat lebih ringkas tapi tetap jelas.

SOAL ASLI:
Pertanyaan: ${soalData.pertanyaan}
A. ${soalData.jawaban_a}
B. ${soalData.jawaban_b}
C. ${soalData.jawaban_c}
D. ${soalData.jawaban_d}
Jawaban Benar: ${soalData.jawaban_benar}

Berikan output dalam format JSON seperti ini:
{
    "pertanyaan": "pertanyaan yang diperpendek",
    "jawaban_a": "jawaban A yang diperpendek",
    "jawaban_b": "jawaban B yang diperpendek", 
    "jawaban_c": "jawaban C yang diperpendek",
    "jawaban_d": "jawaban D yang diperpendek"
}`;

                const result = await callGroqAPI(prompt, true); // true untuk parse JSON
                showAIResult(soalCard, {
                    soal_id: soalId,
                    ...result
                }, 'perpendek', soalData);
            } catch (error) {
                hideAILoader(soalCard);
                alert('Gagal memproses dengan AI: ' + error.message);
            }
        }

        // Fungsi AI Parafrase - memproses pertanyaan + jawaban
        async function aiParafrase(soalId, event) {
            if (event) event.preventDefault();

            const soalCard = getSoalCard(soalId);
            if (!soalCard) {
                console.error('Soal card tidak ditemukan untuk ID:', soalId);
                return;
            }

            showAILoader(soalCard);

            try {
                const soalData = await getSoalData(soalId);

                // Buat prompt untuk pertanyaan + jawaban
                const prompt = `Sempurnakan soal berikut agar lebih jelas, mudah di pahami oleh siswa, dan sesuaikan dengan bahasa yang baik dan benar. Jangan ubah maksud utama soal, kalau memang soal sudah baik, maka cukup perbaiki sedikit jika perlu.

SOAL ASLI:
Pertanyaan: ${soalData.pertanyaan}
A. ${soalData.jawaban_a}
B. ${soalData.jawaban_b}
C. ${soalData.jawaban_c}
D. ${soalData.jawaban_d}
Jawaban Benar: ${soalData.jawaban_benar}

Berikan output dalam format JSON seperti ini:
{
    "pertanyaan": "pertanyaan yang diparafrase",
    "jawaban_a": "jawaban A yang diparafrase",
    "jawaban_b": "jawaban B yang diparafrase", 
    "jawaban_c": "jawaban C yang diparafrase",
    "jawaban_d": "jawaban D yang diparafrase"
}`;

                const result = await callGroqAPI(prompt, true); // true untuk parse JSON
                showAIResult(soalCard, {
                    soal_id: soalId,
                    ...result
                }, 'parafrase', soalData);
            } catch (error) {
                hideAILoader(soalCard);
                alert('Gagal memproses dengan AI: ' + error.message);
            }
        }

        // Fungsi AI Terjemahan - memproses pertanyaan + jawaban
        async function aiTerjemah(soalId, targetLang, event) {
            if (event) event.preventDefault();

            const soalCard = getSoalCard(soalId);
            if (!soalCard) {
                console.error('Soal card tidak ditemukan untuk ID:', soalId);
                return;
            }

            showAILoader(soalCard);

            const languages = {
                'en': 'Bahasa Inggris',
                'jv': 'Bahasa Jawa',
                'zh': 'Bahasa Mandarin (Simplified Chinese)',
                'id' : 'Bahasa Indonesia'
            };

            try {
                const soalData = await getSoalData(soalId);

                // Buat prompt untuk pertanyaan + jawaban
                const prompt = `Terjemahkan soal pilihan ganda berikut ke ${languages[targetLang]}. Pertahankan format dan struktur aslinya. Pastikan terjemahan akurat dan natural.

SOAL ASLI:
Pertanyaan: ${soalData.pertanyaan}
A. ${soalData.jawaban_a}
B. ${soalData.jawaban_b}
C. ${soalData.jawaban_c}
D. ${soalData.jawaban_d}
Jawaban Benar: ${soalData.jawaban_benar}

Berikan output dalam format JSON seperti ini:
{
    "pertanyaan": "pertanyaan yang diterjemahkan",
    "jawaban_a": "jawaban A yang diterjemahkan",
    "jawaban_b": "jawaban B yang diterjemahkan", 
    "jawaban_c": "jawaban C yang diterjemahkan",
    "jawaban_d": "jawaban D yang diterjemahkan"
}`;

                const result = await callGroqAPI(prompt, true); // true untuk parse JSON
                showAIResult(soalCard, {
                    soal_id: soalId,
                    ...result
                }, 'terjemah', soalData);
            } catch (error) {
                hideAILoader(soalCard);
                alert('Gagal memproses dengan AI: ' + error.message);
            }
        }

        // Fungsi AI Analisa
        async function aiAnalisa(soalId, event) {
            if (event) event.preventDefault();

            const soalCard = getSoalCard(soalId);
            if (!soalCard) {
                console.error('Soal card tidak ditemukan untuk ID:', soalId);
                return;
            }

            showAILoader(soalCard);

            try {
                const soalData = await getSoalData(soalId);
                const mataPelajaran = "<?php echo addslashes($ujian['mata_pelajaran']); ?>";
                const tingkat = "<?php echo $ujian['tingkat']; ?>";

                const prompt = `Analisa soal berikut untuk mata pelajaran ${mataPelajaran} kelas ${tingkat}:

"${soalData.pertanyaan}"

Berikan analisa dalam format:
1. Bab/Topik: [identifikasi bab atau topik yang sesuai]
2. Alur Tujuan Pembelajaran: [jelaskan alur tujuan pembelajaran yang sesuai dengan soal ini]
3. Capaian Pembelajaran: [identifikasi capaian pembelajaran yang ingin dicapai melalui soal ini]

Format output JSON:
{
    "bab": "...",
    "alur_tujuan": "...",
    "capaian": "..."
}`;

                const result = await callGroqAPI(prompt, true); // true untuk parse JSON
                showAIResult(soalCard, result, 'analysis', soalData.pertanyaan);
            } catch (error) {
                hideAILoader(soalCard);
                alert('Gagal memproses dengan AI: ' + error.message);
            }
        }

        // Fungsi untuk mendapatkan data soal
        async function getSoalData(soalId) {
            const response = await fetch(`get_soal.php?id=${soalId}`);
            const data = await response.json();
            if (data.status === 'success') {
                return data.soal;
            }
            throw new Error('Gagal mendapatkan data soal');
        }

        // Fungsi untuk memanggil Groq API
        async function callGroqAPI(prompt, parseJSON = false) {
            const response = await fetch('groq_api_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    prompt: prompt,
                    api_key: GROQ_API_KEY,
                    parse_json: parseJSON
                })
            });

            const result = await response.json();
            if (result.status === 'success') {
                return result.content;
            }
            throw new Error(result.message || 'Gagal memanggil AI');
        }


        // Tambah fungsi baru untuk setup event listeners
        function setupAIResultEventListeners(soalCard) {
            // Event listener untuk tombol tutup
            const closeButtons = soalCard.querySelectorAll('.btn-close-ai');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeAIResult(this);
                });
            });

            // Event listener untuk tombol simpan
            const saveButton = soalCard.querySelector('.btn-save-ai');
            if (saveButton) {
                saveButton.addEventListener('click', function() {
                    const soalId = this.getAttribute('data-soal-id');
                    const type = this.getAttribute('data-type');
                    const aiResultElement = this.closest('.ai-result');
                    const resultData = JSON.parse(aiResultElement.getAttribute('data-result'));

                    saveAIResult(soalId, type, resultData);
                });
            }
        }

        // CSS Animation untuk slideUp
        const styleSheet = document.createElement('style');
        styleSheet.textContent = `
@keyframes slideUp {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
`;
        document.head.appendChild(styleSheet);
    </script>

    <!-- crash detection -->
    <script>
        // Tambahkan kode ini di bagian script
        window.addEventListener('error', function(event) {
            // Log dan tangani error
            console.error("Global error:", event.error);

            // Khusus untuk stack overflow error, coba untuk recovery
            if (event.error && event.error.toString().includes("stack")) {
                // Reset status rendering
                isRendering = false;

                // Hapus event listener sementara
                if (formulaModalListener) {
                    document.getElementById('formulaModal')?.removeEventListener('shown.bs.modal', formulaModalListener);
                    formulaModalListener = null;
                }

                // Coba bersihkan debounce timer
                if (previewDebounceTimer) {
                    clearTimeout(previewDebounceTimer);
                    previewDebounceTimer = null;
                }

                // Tampilkan pesan error yang lebih user-friendly
                alert("Terjadi kesalahan saat memproses formula. Mohon coba lagi dengan formula yang lebih sederhana.");
            }
        });

        // Tambahkan ke modal formula saat ditutup
        document.getElementById('formulaModal').addEventListener('hidden.bs.modal', function() {
            // Bersihkan timer dan listener
            if (previewDebounceTimer) {
                clearTimeout(previewDebounceTimer);
                previewDebounceTimer = null;
            }

            // Hapus input listener untuk menghindari memori leak
            const inputs = document.querySelectorAll('#formulaInputContainer input');
            inputs.forEach(input => {
                input.removeEventListener('input', updateFormulaPreview);
            });
        });

        // Tambahkan di bagian akhir skrip
        // Deteksi performa dan crash recovery
        let lastPerformanceCheck = Date.now();
        let slowOperationCount = 0;

        // Fungsi untuk memeriksa performa
        function checkPerformance() {
            const now = Date.now();
            const timeSinceLastCheck = now - lastPerformanceCheck;

            // Jika interval terlalu lama, ada risiko kinerja lambat
            if (timeSinceLastCheck > 1000) {
                slowOperationCount++;
                console.warn(`Operasi lambat terdeteksi: ${timeSinceLastCheck}ms`);

                // Jika terlalu banyak operasi lambat, coba bersihkan resources
                if (slowOperationCount > 3) {
                    console.warn("Terlalu banyak operasi lambat, membersihkan resources...");
                    cleanupResources();
                    slowOperationCount = 0;
                }
            } else {
                // Reset counter jika operasi cepat
                slowOperationCount = Math.max(0, slowOperationCount - 1);
            }

            lastPerformanceCheck = now;

            // Periksa lagi nanti
            setTimeout(checkPerformance, 5000);
        }

        // Fungsi untuk membersihkan resources
        function cleanupResources() {
            // Reset semua flag dan timer
            isRendering = false;

            if (previewDebounceTimer) {
                clearTimeout(previewDebounceTimer);
                previewDebounceTimer = null;
            }

            // Force garbage collection jika browser mendukung
            if (window.gc) {
                try {
                    window.gc();
                } catch (e) {
                    console.error("GC error:", e);
                }
            }

            console.log("Resources dibersihkan");
        }

        // Mulai pemeriksaan performa
        setTimeout(checkPerformance, 5000);
    </script>
</body>

</html>