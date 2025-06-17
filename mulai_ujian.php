<?php
include 'includes/session_config.php';
require "koneksi.php";

if (!isset($_SESSION['userid']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$ujian_id = $_GET['id'];
$user_id = $_SESSION['userid'];

// Debug: Cek apakah session ada
if (!$user_id) {
    die("Error: Session userid tidak ditemukan");
}

// Ambil ID siswa dari username terlebih dahulu
$query_get_siswa_id = "SELECT id FROM siswa WHERE username = ?";
$stmt_get_siswa = $koneksi->prepare($query_get_siswa_id);

if (!$stmt_get_siswa) {
    die("Error prepare statement: " . $koneksi->error);
}

$stmt_get_siswa->bind_param("s", $user_id);
$stmt_get_siswa->execute();
$result_get_siswa = $stmt_get_siswa->get_result();
$siswa_data = $result_get_siswa->fetch_assoc();

// Debug: Cek apakah siswa ditemukan
if (!$siswa_data) {
    die("Error: Siswa dengan username '$user_id' tidak ditemukan");
}

$siswa_id = $siswa_data['id'];

// Debug: Cek siswa_id
echo "<!-- DEBUG: siswa_id = $siswa_id -->";

// Pengecekan apakah siswa sudah pernah ujian
$query_cek_ujian = "SELECT COUNT(*) as sudah_ujian FROM hasil_ujian 
                    WHERE ujian_id = ? AND siswa_id = ?";
$stmt_cek = $koneksi->prepare($query_cek_ujian);

if (!$stmt_cek) {
    die("Error prepare statement cek ujian: " . $koneksi->error);
}

$stmt_cek->bind_param("ii", $ujian_id, $siswa_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$cek_ujian = $result_cek->fetch_assoc();

$sudah_ujian = $cek_ujian['sudah_ujian'] > 0;

echo "<!-- DEBUG: ujian_id = $ujian_id, user_id = $user_id, sudah_ujian = " . ($sudah_ujian ? 'true' : 'false') . ", count = " . $cek_ujian['sudah_ujian'] . " -->";

// Query untuk mengambil data ujian
$query_ujian = "SELECT u.*, k.nama_kelas as kelas 
                FROM ujian u 
                LEFT JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.id = ?";
$stmt_ujian = $koneksi->prepare($query_ujian);
$stmt_ujian->bind_param("i", $ujian_id);
$stmt_ujian->execute();
$result_ujian = $stmt_ujian->get_result();
$data_ujian = $result_ujian->fetch_assoc();

$tanggal_selesai = $data_ujian['tanggal_selesai'];


// Query untuk mengambil data siswa
$userid = $_SESSION['userid'];
$query_siswa = "SELECT s.*,
    k.nama_kelas AS kelas_saat_ini,
    COALESCE(AVG(pg.nilai_akademik), 0) as nilai_akademik,
    COALESCE(AVG(pg.keaktifan), 0) as keaktifan,
    COALESCE(AVG(pg.pemahaman), 0) as pemahaman,
    COALESCE(AVG(pg.kehadiran_ibadah), 0) as kehadiran_ibadah, 
    COALESCE(AVG(pg.kualitas_ibadah), 0) as kualitas_ibadah,
    COALESCE(AVG(pg.pemahaman_agama), 0) as pemahaman_agama,
    COALESCE(AVG(pg.minat_bakat), 0) as minat_bakat,
    COALESCE(AVG(pg.prestasi), 0) as prestasi,
    COALESCE(AVG(pg.keaktifan_ekskul), 0) as keaktifan_ekskul,
    COALESCE(AVG(pg.partisipasi_sosial), 0) as partisipasi_sosial,
    COALESCE(AVG(pg.empati), 0) as empati,
    COALESCE(AVG(pg.kerja_sama), 0) as kerja_sama,
    COALESCE(AVG(pg.kebersihan_diri), 0) as kebersihan_diri,
    COALESCE(AVG(pg.aktivitas_fisik), 0) as aktivitas_fisik,
    COALESCE(AVG(pg.pola_makan), 0) as pola_makan,
    COALESCE(AVG(pg.kejujuran), 0) as kejujuran,
    COALESCE(AVG(pg.tanggung_jawab), 0) as tanggung_jawab,
    COALESCE(AVG(pg.kedisiplinan), 0) as kedisiplinan
    FROM siswa s 
    LEFT JOIN kelas_siswa ks ON s.id = ks.siswa_id 
    LEFT JOIN kelas k ON ks.kelas_id = k.id 
    LEFT JOIN pg ON s.id = pg.siswa_id 
    WHERE s.username = ?
    GROUP BY s.id, k.nama_kelas";

$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "i", $userid);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);


// Mengambil semua soal untuk ujian tersebut
$query = "SELECT * FROM bank_soal WHERE ujian_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $ujian_id);
$stmt->execute();
$result = $stmt->get_result();
$soal_array = $result->fetch_all(MYSQLI_ASSOC);

// Query untuk mengambil data deskripsi soal
$query_desc = "SELECT d.id, d.content FROM soal_descriptions d 
              WHERE d.ujian_id = ?";
$stmt_desc = $koneksi->prepare($query_desc);
$stmt_desc->bind_param("i", $ujian_id);
$stmt_desc->execute();
$result_desc = $stmt_desc->get_result();

// Simpan deskripsi dalam array
$descriptions = [];
while ($desc = $result_desc->fetch_assoc()) {
    $descriptions[$desc['id']] = $desc['content'];
}

// Tambahkan description_id ke setiap soal dalam $soal_array
foreach ($soal_array as &$soal) {
    // Jika soal memiliki description_id dan deskripsi tersebut ada
    if (!empty($soal['description_id']) && isset($descriptions[$soal['description_id']])) {
        $soal['description'] = $descriptions[$soal['description_id']];
    } else {
        $soal['description'] = null;
    }
}
unset($soal); // Hapus referensi

// Mengacak urutan soal
shuffle($soal_array);

// Menyimpan urutan soal yang teracak dalam session
$_SESSION['soal_order_' . $ujian_id] = array_column($soal_array, 'id');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laman Ujian - SMAGAEdu</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5XXM5FLKYE"></script>
    <!-- MathJax for formula rendering -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
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
            startup: {
                ready: function() {
                    MathJax.startup.defaultReady();
                    // Atur flag global bahwa MathJax siap
                    window.mathJaxReady = true;
                }
            },
            options: {
                enableMenu: false, // Nonaktifkan menu konteks
                processing: {
                    limit: 10 // Batasi operasi per langkah rendering
                }
            }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>


    <style>
        @keyframes warning-background {
            0% {
                background: red;
            }

            50% {
                background: white;
            }

            100% {
                background: red;
            }
        }

        .warning-active {
            display: none;
            /* Hide by default */
            animation: warning-background 0.5s infinite;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1040;
            /* Changed from 9999 to be lower than modal */
            opacity: 0.7;
        }

        /* Tambahkan CSS ini di bagian style */
        .soal-number[data-status="answered"] {
            background-color: #da7756 !important;
            color: white !important;
        }

        .soal-number[data-status="marked"] {
            background-color: #dc3545 !important;
            color: white !important;
        }
    </style>

    <!-- modal animasi -->
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

        .alert-info {
            background-color: rgba(0, 123, 255, 0.1) !important;
            border: 1px solid rgba(0, 123, 255, 0.2) !important;
        }
    </style>
    <script>
        let warningAudio;
        let warningDiv;

        document.addEventListener('DOMContentLoaded', () => {
            const sudahUjian = <?php echo $sudah_ujian ? 'true' : 'false'; ?>;

            console.log('DOMContentLoaded fired, sudahUjian:', sudahUjian);

            if (sudahUjian) {
                // Debug: Cek apakah modal element ada
                const modalElement = document.getElementById('alreadyExamModal');
                console.log('Modal element:', modalElement);

                if (modalElement) {
                    // Jika sudah ujian, tampilkan modal peringatan
                    const alreadyExamModal = new bootstrap.Modal(modalElement);
                    alreadyExamModal.show();

                    // PENTING: Hanya hide konten ujian, bukan seluruh body
                    const examContent = document.querySelector('.container-fluid');
                    if (examContent) {
                        examContent.style.display = 'none';
                    }

                    // Stop semua timer dan fungsi ujian
                    if (typeof timer !== 'undefined') {
                        clearInterval(timer);
                    }
                    if (typeof timeoutChecker !== 'undefined') {
                        clearInterval(timeoutChecker);
                    }
                } else {
                    console.error('Modal alreadyExamModal tidak ditemukan!');
                    // Fallback: redirect langsung
                    alert('Anda sudah pernah mengerjakan ujian ini.');
                    window.location.href = 'ujian.php';
                }

                return; // Stop eksekusi script lainnya
            }

            // KODE UNTUK SISWA YANG BELUM UJIAN
            console.log('Siswa belum ujian, menampilkan modal welcome');

            // Inisialisasi warningDiv - pastikan elemen ini ada di HTML
            warningDiv = document.getElementById('warningOverlay');

            // Jika warningOverlay tidak ada di HTML, buat elemen baru
            if (!warningDiv) {
                warningDiv = document.createElement('div');
                warningDiv.id = 'warningOverlay';
                warningDiv.className = 'warning-active';
                document.body.appendChild(warningDiv);
            }

            warningAudio = new Audio('assets/warning.mp3');

            // TAMPILKAN MODAL WELCOME
            const startExamModalElement = document.getElementById('startExamModal');
            console.log('StartExamModal element:', startExamModalElement);

            if (startExamModalElement) {
                const modal = new bootstrap.Modal(startExamModalElement);
                modal.show();
                console.log('Modal welcome ditampilkan');
            } else {
                console.error('Modal startExamModal tidak ditemukan!');
            }

            // EVENT LISTENER UNTUK TOMBOL START FULLSCREEN
            document.getElementById('startFullscreenExam').addEventListener('click', function(e) {
                // Penting: Panggil fullscreen LANGSUNG dari event klik
                try {
                    const element = document.documentElement;

                    // Coba semua versi API fullscreen
                    if (element.requestFullscreen) {
                        element.requestFullscreen();
                    } else if (element.webkitRequestFullscreen) {
                        element.webkitRequestFullscreen();
                    } else if (element.mozRequestFullScreen) {
                        element.mozRequestFullScreen();
                    } else if (element.msRequestFullscreen) {
                        element.msRequestFullscreen();
                    } else {
                        alert("Browser Anda tidak mendukung mode fullscreen. Silakan tekan F11 untuk masuk mode fullscreen secara manual.");
                    }

                    // Sembunyikan modal setelah fullscreen berhasil dengan penundaan yang lebih baik
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('startExamModal'));
                        if (modal) {
                            modal.hide();

                            // Hapus backdrop secara manual jika masih ada
                            setTimeout(() => {
                                const backdrops = document.querySelectorAll('.modal-backdrop');
                                backdrops.forEach(backdrop => {
                                    backdrop.remove();
                                });

                                // Reset body classes
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, 300);
                        }
                    }, 1000); // Beri waktu lebih lama untuk fullscreen transition
                } catch (error) {
                    console.error("Fullscreen error:", error);
                    alert("Tidak dapat masuk mode fullscreen. Silakan izinkan fitur ini di browser Anda atau tekan F11.");
                }
            });

            // Event listener untuk perubahan fullscreen
            document.addEventListener('fullscreenchange', handleFullscreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.addEventListener('mozfullscreenchange', handleFullscreenChange);
            document.addEventListener('MSFullscreenChange', handleFullscreenChange);
        });





        function handleFullscreenChange() {
            console.log("Fullscreen change detected!");

            if (!document.fullscreenElement &&
                !document.webkitFullscreenElement &&
                !document.mozFullScreenElement &&
                !document.msFullscreenElement) {

                console.log("User keluar dari fullscreen mode");

                // Show visual warning
                if (warningDiv) {
                    warningDiv.style.display = 'flex';
                }

                // Play warning sound
                try {
                    if (warningAudio) {
                        warningAudio.pause();
                        warningAudio.currentTime = 0;
                    }
                    warningAudio = new Audio('assets/warning.mp3');
                    warningAudio.loop = true;
                    warningAudio.volume = 1.0;
                    warningAudio.play().catch(e => console.error("Error playing audio:", e));
                } catch (e) {
                    console.error("Audio error:", e);
                }

                // Show warning modal
                try {
                    const modalElement = document.getElementById('fullscreenWarningModal');
                    document.getElementById('supervisorPassword').value = '';
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    console.log("Warning modal displayed");
                } catch (e) {
                    console.error("Error displaying warning modal:", e.message);
                    console.error(e); // Log the full error
                    alert("Peringatan: Anda keluar dari mode fullscreen! Harap hubungi pengawas ujian.");
                }
            } else {
                // Back to fullscreen mode
                console.log("User masuk ke fullscreen mode");

                if (warningDiv) {
                    warningDiv.style.display = 'none';
                }

                if (warningAudio) {
                    warningAudio.pause();
                    warningAudio.currentTime = 0;
                    warningAudio = null;
                }
            }
        }

        function checkPassword() {
            const password = document.getElementById('supervisorPassword')?.value || '';
            const errorElement = document.getElementById('passwordError');

            // Sembunyikan pesan error sebelumnya (jika ada)
            errorElement.classList.add('d-none');

            if (password === 'sayaIzinkanKumpulkan') {
                // Stop the warning sound
                if (warningAudio) {
                    warningAudio.pause();
                    warningAudio.currentTime = 0;
                    warningAudio = null;
                }

                try {
                    enableFullscreen();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('fullscreenWarningModal'));
                    if (modal) modal.hide();
                } catch (e) {
                    console.error("Error when re-enabling fullscreen:", e);
                    errorElement.textContent = "Terjadi kesalahan saat mencoba masuk mode fullscreen kembali";
                    errorElement.classList.remove('d-none');
                }
            } else {
                // Tampilkan pesan error di dalam modal
                errorElement.classList.remove('d-none');
                // Kosongkan field password
                document.getElementById('supervisorPassword').value = '';
            }
        }

        // Fungsi untuk masuk mode fullscreen
        function enableFullscreen() {
            const element = document.documentElement;

            try {
                if (element.requestFullscreen) {
                    element.requestFullscreen().catch(err => {
                        console.error("Fullscreen error:", err);
                        alert("Tidak dapat masuk mode fullscreen. Harap izinkan fitur ini di browser Anda.");
                    });
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            } catch (e) {
                console.error("Error enabling fullscreen:", e);
            }

            // Sembunyikan peringatan ketika fullscreen diaktifkan
            if (warningDiv) {
                warningDiv.style.display = 'none';
            }
        }

        // Block keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Block Esc key
            if (e.key === 'Escape') {
                e.preventDefault();
                return false;
            }

            // Block combinations with Ctrl, Alt, Windows key
            if (e.ctrlKey || e.altKey || e.metaKey) {
                e.preventDefault();
                return false;
            }

            // Block F1-F12 keys
            if (e.key.match(/F\d+/)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
    <style>
        body {
            overflow-y: auto !important;
            background-color: #f8f9fa;
            font-family: merriweather;
        }

        .soal-numbers {
            height: calc(100vh - 70px);
            overflow-y: auto;
            padding: 10px;
        }

        .soal-number {
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .soal-number:hover {
            transform: scale(1.1);
        }

        .soal-content {
            height: calc(90vh - 70px);
            overflow-y: auto;
            position: relative;
            overflow: hidden;
        }

        .option-card {
            cursor: pointer;
            transition: all 0.2s;
            padding: 15px !important;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .option-card:hover {
            background-color: #e9ecef;
        }

        .option-card.selected {
            background-color: #da7756;
            color: white;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
            transition: background-color 0.3s ease;
        }

        .color-web:hover {
            background-color: rgb(206, 100, 65);
        }

        .soal-numbers .soal-number[data-status="answered"] {
            background-color: #da7756 !important;
            color: white !important;
        }

        .soal-numbers .soal-number[data-status="marked"] {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .soal-number {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .soal-numbers {
                height: auto;
                max-height: 200px;
                margin-bottom: 1rem;
            }

            .soal-content {
                height: auto;
                margin-bottom: 100px;
                padding: 10px;
            }

            .bottom-navigation {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 15px;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
                margin-left: 0 !important;
                width: 100%;
                z-index: 1000;
                border-radius: 15px;
            }

            .bottom-navigation button {
                padding: 12px;
                min-width: 44px;
                font-size: 20px;
            }

            .bottom-navigation button p {
                display: none;
            }

            .option-card {
                padding: 20px !important;
            }

            .navbar {
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .col-md-3 {
                padding: 0;
            }
        }

        /* Tambahkan styling baru untuk mobile */
        @media (max-width: 768px) {

            /* Pastikan area collapse tidak menyebabkan scrollbar horizontal */
            #mobileInfoCollapse {
                width: 100%;
                padding: 0;
            }

            /* Sesuaikan ukuran soal pada tampilan mobile */
            .soal-content {
                height: auto;
                padding: 10px 15px;
                margin-bottom: 100px;
                /* Margin untuk bottom navigation */
            }

            /* Pastikan bottom navigation tetap di bawah */
            .bottom-navigation {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
                background-color: white;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            }

            /* Pastikan nomor soal tidak terlalu besar di mobile */
            .soal-number {
                width: 35px;
                height: 35px;
                font-size: 13px;
            }
        }

        /* Tambahkan ini ke bagian style */
        .soal-number {
            position: relative;
        }

        .soal-number .status-indicator {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            font-size: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .status-saved {
            background-color: #34C759;
        }

        .status-error {
            background-color: #FF3B30;
        }

        /* style animasi ke kanan atau kiri */
        /* Efek transisi slide */
        .soal-page {
            position: absolute;
            width: 100%;
            transition: transform 0.3s ease;
            left: 0;
        }

        /* CSS untuk Image Zoom Functionality */
        .image-container {
            position: relative;
            display: inline-block;
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .zoomable-image {
            transition: transform 0.2s ease;
            cursor: zoom-in;
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Full Screen Zoom Overlay */
        /* Full Screen Zoom Overlay */
        .zoom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .zoom-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .zoom-overlay img {
            max-width: 90vw;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            cursor: zoom-in;
            user-select: none;
            -webkit-user-drag: none;
        }

        .zoom-overlay img.zoomed {
            cursor: grab;
            transform: scale(2);
        }

        .zoom-overlay img.zoomed:active {
            cursor: grabbing;
        }

        .zoom-instructions {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            transition: opacity 0.3s ease;
            pointer-events: none;
            text-align: center;
        }

        .zoom-instructions.hidden {
            opacity: 0;
        }

        .zoom-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.2s ease;
        }

        .zoom-close:hover {
            background: white;
        }

        .zoom-controls {
            position: absolute;
            bottom: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            /* opacity: 0; */
            transition: opacity 0.3s ease;
            z-index: 11;
        }

        /* .image-container:hover .zoom-controls {
            opacity: 1;
        } */

        .zoom-btn {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            /* transition: background 0.2s ease; */
            font-size: 16px;
        }

        /* .zoom-btn:hover {
            background: rgba(0, 0, 0, 0.9);
        } */

        .soal-page.slide-left {
            transform: translateX(-100%);
        }

        .soal-page.slide-right {
            transform: translateX(100%);
        }

        .soal-page.current {
            transform: translateX(0);
            position: relative;
            z-index: 1;
        }

        /* Efek getar untuk tombol */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        /* Container untuk soal dengan overflow hidden */
        .soal-content {
            position: relative;
            overflow: auto !important;
        }

        #exam-form {
            padding-bottom: 50px;
        }
    </style>
</head>

<!-- style untuk zoom in dan out soal jawaban -->
<style>
    /* CSS untuk Zoom Functionality */
    .zoom-content {
        transition: font-size 0.3s ease, line-height 0.3s ease;
    }

    /* CSS Zoom dengan specificity tinggi - REPLACE yang lama */
    .soal-text.zoom-tiny,
    .soal-text.zoom-tiny.length-medium,
    .soal-text.zoom-tiny.length-long,
    .soal-text.zoom-tiny.length-very-long {
        font-size: 1rem !important;
        line-height: 1.2 !important;
    }

    .soal-text.zoom-small,
    .soal-text.zoom-small.length-medium,
    .soal-text.zoom-small.length-long,
    .soal-text.zoom-small.length-very-long {
        font-size: 1.25rem !important;
        line-height: 1.3 !important;
    }

    .soal-text.zoom-medium,
    .soal-text.zoom-medium.length-medium,
    .soal-text.zoom-medium.length-long,
    .soal-text.zoom-medium.length-very-long {
        font-size: 1.4rem !important;
        line-height: 1.3 !important;
    }

    .soal-text.zoom-normal,
    .soal-text.zoom-normal.length-medium,
    .soal-text.zoom-normal.length-long,
    .soal-text.zoom-normal.length-very-long {
        font-size: 1.75rem !important;
        line-height: 1.4 !important;
    }

    .soal-text.zoom-large,
    .soal-text.zoom-large.length-medium,
    .soal-text.zoom-large.length-long,
    .soal-text.zoom-large.length-very-long {
        font-size: 2rem !important;
        line-height: 1.5 !important;
    }

    .soal-text.zoom-extra-large,
    .soal-text.zoom-extra-large.length-medium,
    .soal-text.zoom-extra-large.length-long,
    .soal-text.zoom-extra-large.length-very-long {
        font-size: 2.5rem !important;
        line-height: 1.6 !important;
    }

    .soal-text.zoom-huge,
    .soal-text.zoom-huge.length-medium,
    .soal-text.zoom-huge.length-long,
    .soal-text.zoom-huge.length-very-long {
        font-size: 3rem !important;
        line-height: 1.7 !important;
    }

    /* Option cards - lebih simple karena tidak ada konflik */
    .option-card.zoom-tiny {
        font-size: 12px !important;
        padding: 10px !important;
    }

    .option-card.zoom-small {
        font-size: 14px !important;
        padding: 12px !important;
    }

    .option-card.zoom-medium {
        font-size: 15px !important;
        padding: 14px !important;
    }

    .option-card.zoom-normal {
        font-size: 16px !important;
        padding: 15px !important;
    }

    .option-card.zoom-large {
        font-size: 18px !important;
        padding: 18px !important;
    }

    .option-card.zoom-extra-large {
        font-size: 20px !important;
        padding: 22px !important;
    }

    .option-card.zoom-huge {
        font-size: 22px !important;
        padding: 25px !important;
    }

    /* Nomor soal */
    .soal-number-text.zoom-tiny {
        font-size: 1rem !important;
    }

    .soal-number-text.zoom-small {
        font-size: 1.2rem !important;
    }

    .soal-number-text.zoom-medium {
        font-size: 1.3rem !important;
    }

    .soal-number-text.zoom-normal {
        font-size: 1.5rem !important;
    }

    .soal-number-text.zoom-large {
        font-size: 1.8rem !important;
    }

    .soal-number-text.zoom-extra-large {
        font-size: 2.2rem !important;
    }

    .soal-number-text.zoom-huge {
        font-size: 2.5rem !important;
    }

    /* Ensure smooth transition untuk semua elemen zoom */
    .soal-text,
    .option-card,
    .soal-number-text {
        transition: font-size 0.3s ease, padding 0.3s ease, line-height 0.3s ease;
    }

    /* Override responsive font jika zoom aktif */
    @media (max-width: 768px) {

        .soal-text.zoom-large,
        .soal-text.zoom-extra-large {
            font-size: 1.8rem !important;
        }

        .option-card.zoom-large,
        .option-card.zoom-extra-large {
            font-size: 16px !important;
            padding: 18px !important;
        }
    }
</style>

<!-- style untuk mengatur responsifitas font size pada pertanyaan -->
<style>
    .soal-text {
        font-size: 1.75rem;
        font-weight: 500;
        line-height: 1.4;
        transition: font-size 0.3s ease;
    }

    /* Ukuran font berbeda untuk konten soal berdasarkan panjang soal */
    .soal-text.length-medium {
        font-size: 1.5rem;
    }

    .soal-text.length-long {
        font-size: 1.25rem;
    }

    .soal-text.length-very-long {
        font-size: 1rem;
    }
</style>


<body id="examBody" class="pt-md-4">

    <div id="warningOverlay" class="warning-active"></div>
    <nav class="navbar d-md-none" style="background-color: rgba(255, 255, 255, 0.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0, 0, 0, 0.1); padding: 12px 0;">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between w-100">
                <!-- Logo for desktop only -->
                <!-- <div class="d-none d-md-flex align-items-center gap-2">
                    <img src="assets/smagaedu.png" alt="SMAGAEdu" width="30px" style="border-radius: 8px;">
                    <h1 class="m-0" style="font-size: 17px; font-weight: 600; color: #1c1c1e;">SMAGAEdu</h1>
                </div> -->

                <!-- Timer in center for mobile -->
                <!-- <div class="d-flex d-md-none align-items-center justify-content-center mx-auto">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock" style="color:rgb(218, 119, 86);"></i>
                        <span id="mobile-countdown" style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;">00:00:00</span><span style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;"> tersisa</span>
                    </div>
                </div> -->

                <!-- Status badge for desktop only -->
                <!-- <div class="d-none d-md-block">
                    <span class="badge" style="background: rgba(218, 119, 86, 0.15); color: rgb(218, 119, 86); font-weight: 500; padding: 6px 10px; border-radius: 12px; font-size: 13px;">
                        Sedang Ujian
                    </span>
                </div> -->
            </div>
        </div>
    </nav>

    <!-- Modal untuk siswa yang sudah ujian -->
    <div class="modal fade" id="alreadyExamModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="ti ti-alert-square-rounded" style="font-size: 4rem; color:rgb(206, 100, 65);"></i>
                    </div>
                    <h5 class="fw-bold mb-3" style="font-size: 1.4rem;">Lo kan kamu sudah ngerjain ujian ini</h5>
                    <p class="mb-4 text-muted">
                        Ujian <strong><?php echo htmlspecialchars($data_ujian['judul'] ?? 'ini'); ?></strong> sudah kamu kerjain. Klik tombol di bawah untuk kembali ke beranda ya
                    </p>


                    <div class="d-grid mt-4">
                        <a href="ujian.php" class="btn text-white px-4"
                            style="border-radius: 12px; background-color: rgb(218, 119, 86);">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Ujian
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal peringatan keluar dari fullscreen dengan iOS style -->
    <div class="modal fade" id="fullscreenWarningModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.1);">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <span class="bi bi-exclamation-circle" style="font-size: 3rem; color:#FF3B30;"></span>
                    </div>
                    <h5 class="fw-semibold mb-2">Keluar dari mode layar penuh</h5>
                    <p class="text-secondary mb-4" style="font-size: 0.95rem;">Sesi ujian terkunci. Masukkan password pengawas untuk melanjutkan.</p>

                    <div id="passwordError" class="d-none mb-3" style="background-color: #FEE2E2; border-radius: 12px; padding: 12px 15px; border-left: 4px solid #EF4444;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle" style="color: #EF4444; font-size: 16px;"></i>
                            <p class="mb-0" style="color: #B91C1C; font-size: 14px;">Password salah, panggil pengawas untuk membuka kunci</p>
                        </div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="supervisorPassword" placeholder="Password" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.1);">
                        <label for="supervisorPassword" class="text-secondary">Password Pengawas</label>
                    </div>
                    <div class="d-grid">
                        <button class="btn py-2" onclick="checkPassword()"
                            style="border-radius: 12px; background-color: rgb(206, 100, 65); color: white; font-weight: 500;">
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan ini sebelum penutup </body> -->
    <div class="modal fade" id="startExamModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <img src="<?php
                                if (!empty($siswa['photo_url'])) {
                                    // Jika menggunakan avatar dari DiceBear
                                    if ($siswa['photo_type'] === 'avatar') {
                                        echo $siswa['photo_url'];
                                    }
                                    // Jika menggunakan foto upload
                                    else if ($siswa['photo_type'] === 'upload') {
                                        echo $siswa['photo_url'];
                                    }
                                } else {
                                    // Gambar default
                                    echo 'assets/pp.png';
                                }
                                ?>" width="120px" class="rounded-circle border mb-3">
                    <h5 class="fw-bold">Halo, <?php echo $_SESSION['nama']; ?></h5>
                    <p class="fw-bold mb-4">Sudah siap untuk ujian kali ini?</p>

                    <div class="alert border text-start bg-light mb-4" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-shield fs-4 me-3" id="securityIcon" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="p-0 m-0 fw-bold" id="securityTitle" style="font-size: 14px;">Menyiapkan keamanan ujian...</p>
                                <p class="p-0 m-0 text-muted" id="securityText" style="font-size: 12px;">
                                    <span class="spinner-border spinner-border-sm me-1" id="securitySpinner"></span>
                                    Memuat keamanan ujian, harap tunggu...
                                </p>
                                <p class="p-0 m-0 text-muted d-none" id="securityReady" style="font-size: 12px;">
                                    Setelah menekan tombol <span class="rounded px-2 border mx-1">Mulai</span>, Kamu tidak dapat keluar dari halaman ini. Jika kedapatan keluar dari layar penuh maka nanti ujian kamu akan terkunci.
                                </p>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Handle security loading animation
                        document.addEventListener('DOMContentLoaded', function() {
                            // Security loading animation
                            setTimeout(function() {
                                document.getElementById('securityTitle').textContent = 'Keamanan Ujian Telah Siap';
                                document.getElementById('securityIcon').classList.remove('bi-shield');
                                document.getElementById('securityIcon').classList.add('bi-shield-fill-check');
                                document.getElementById('securitySpinner').classList.add('d-none');
                                document.getElementById('securityText').classList.add('d-none');
                                document.getElementById('securityReady').classList.remove('d-none');
                            }, 2000);
                        });
                    </script>

                    <div class="d-flex gap-2">
                        <a href="ujian.php" class="btn text-black border px-4 flex-fill"
                            style="border-radius: 12px; background-color: white">
                            Saya belum siap
                        </a>
                        <!-- <button class="btn text-white px-4 flex-fill" id="backButton" data-bs-dismiss="modal"
                            style="border-radius: 12px; background-color: rgb(206, 100, 65);">
                            Kembali
                        </button> -->
                        <button type="button" class="btn text-white px-4 flex-fill" id="startFullscreenExam"
                            style="border-radius: 12px; background-color: rgb(218, 119, 86);">
                            Mulai <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- untuk menjaga sesion tetap aktif, ping ke server -->
    <script>
        // Session keeper yang menggunakan save_jawaban.php
        // Session keeper yang menggunakan save_jawaban.php
        let sessionKeeperInterval = setInterval(function() {
            $.ajax({
                url: 'save_jawaban.php',
                method: 'POST',
                data: {
                    ujian_id: <?php echo $ujian_id; ?>,
                    soal_index: -999,
                    jawaban: 'session_ping'
                },
                timeout: 15000,
                success: function(response) {
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;

                        if (result.success) {
                            console.log('✅ Session kept alive at:', result.time);

                            // debug session
                            if (result.session_info) {
                                const info = result.session_info;
                                const progressBar = "█".repeat(Math.min(10, Math.floor(info.age_hours * 2))) +
                                    "░".repeat(Math.max(0, 10 - Math.floor(info.age_hours * 2)));

                                console.log(`✅ Session kept alive at: ${result.time}`);
                                console.log(`📊 Session Status:
    - Durasi ujian: ${info.age_hours} jam [${progressBar}]
    - Status: ${info.status}
    - ${info.note}`);
                            }
                        } else {
                            console.warn('⚠️ Session keeper warning:', result.error);

                            if (result.error && result.error.includes('Session')) {
                                clearInterval(sessionKeeperInterval);
                                alert('Session expired, halaman akan dialihkan');
                                window.location.href = 'index.php';
                            }
                        }
                    } catch (e) {
                        console.warn('Session keeper response parsing error:', e);
                        console.log('Raw response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('❌ Session keeper request failed:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error
                    });

                    if (xhr.status === 401 || xhr.status === 403) {
                        clearInterval(sessionKeeperInterval);
                        alert('Session expired, halaman akan dialihkan');
                        window.location.href = 'index.php';
                    }
                }
            });
        }, 300000); // 5 menit

        // Clear interval saat window/tab ditutup
        window.addEventListener('beforeunload', function() {
            if (sessionKeeperInterval) {
                clearInterval(sessionKeeperInterval);
            }
        });

        // Optional: Tambahkan session keeper manual saat user aktif berinteraksi
        let lastUserActivity = Date.now();
        document.addEventListener('click', function() {
            lastUserActivity = Date.now();
        });

        document.addEventListener('keypress', function() {
            lastUserActivity = Date.now();
        });

        // Session keeper tambahan jika user idle terlalu lama
        setInterval(function() {
            const idleTime = Date.now() - lastUserActivity;

            // Jika user idle lebih dari 4 menit, ping session
            if (idleTime > 240000) { // 4 menit
                $.post('save_jawaban.php', {
                    ujian_id: <?php echo $ujian_id; ?>,
                    soal_index: -999,
                    jawaban: 'session_ping'
                });

                lastUserActivity = Date.now(); // Reset idle timer
            }
        }, 60000); // Check setiap 1 menit
    </script>

    <!-- Script to sync mobile countdown with the main countdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update the mobile countdown every second
            setInterval(function() {
                const mainCountdown = document.getElementById('countdown');
                const mobileCountdown = document.getElementById('mobile-countdown');
                if (mainCountdown && mobileCountdown) {
                    mobileCountdown.textContent = mainCountdown.textContent;
                }
            }, 1000);
        });
    </script>

    <!-- Area collapse yang akan muncul di atas soal -->
    <div class="collapse d-md-none" id="mobileInfoCollapse">
        <div class="p-2">
            <!-- Info Siswa Card -->
            <div class="card mb-3 border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                <div class="card-body p-3">
                    <!-- Konten info siswa -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div>
                            <img src="<?php echo !empty($siswa['photo_url']) ?
                                            ($siswa['photo_type'] === 'avatar' ? $siswa['photo_url'] : ($siswa['photo_type'] === 'upload' ? $siswa['photo_url'] : 'assets/pp.png'))
                                            : 'assets/pp.png'; ?>"
                                class="rounded-circle border"
                                style="width: 50px; height: 50px; object-fit: cover;">
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold" style="color: #1c1c1e; font-size: 14px;"><?php echo $_SESSION['nama']; ?></h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="background: rgba(218, 119, 86, 0.1); color: rgb(218, 119, 86); font-weight: normal; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                    <?php echo $data_ujian['judul']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Soal Card -->
            <div class="card border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3" style="color: #1c1c1e; font-weight: 600; font-size: 14px;">Daftar Soal</h6>
                    <div class="d-flex flex-wrap gap-2 justify-content-start">
                        <?php foreach ($soal_array as $index => $soal): ?>
                            <div class="soal-number rounded-3 border-0 d-flex align-items-center justify-content-center"
                                data-soal="<?php echo $index; ?>"
                                data-status="unanswered"
                                style="background: #f2f2f7; color:black; box-shadow: 0 1px 2px rgba(0,0,0,0.1); width: 35px; height: 35px; font-size: 13px;">
                                <?php echo $index + 1; ?>
                                <!-- Indikator status akan ditambahkan di sini oleh JavaScript -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- scrip untuk collapse informasi soal -->
    <script>
        // Modifikasi event handler untuk menandai soal
        $(document).ready(function() {
            loadAnswers();

            // Load zoom level dari localStorage
            const savedZoomLevel = localStorage.getItem(zoomStorageKey);
            if (savedZoomLevel !== null) {
                currentZoomLevel = parseInt(savedZoomLevel);
            }

            // Aplikasikan zoom yang tersimpan
            applyZoomToContent(currentZoomLevel);


            // Inisialisasi tampilan tombol mark
            updateMarkButtonUI(currentSoal);


            $(`.soal-page[data-index="0"]`).addClass('current').removeClass('d-none');

            // Handler untuk klik nomor soal
            $('.soal-number').click(function() {
                const index = $(this).data('soal');
                showSoal(index);

                // Jika di mobile, tutup collapse
                if (window.innerWidth < 768) {
                    $('#mobileInfoCollapse').collapse('hide');
                }
            });

            // Fungsi untuk memperbarui status soal di SEMUA elemen
            function updateSoalStatus(soalIndex, status) {
                // Update semua elemen soal-number dengan data-soal yang sama
                $(`.soal-number[data-soal="${soalIndex}"]`).attr('data-status', status);
            }

            // Perbarui event handler untuk tombol mark
            $('#mark').click(function() {
                const soalIndex = currentSoal;

                if (markedQuestions.has(soalIndex)) {
                    // Unmark soal
                    markedQuestions.delete(soalIndex);
                    updateSoalStatus(soalIndex, answers[soalIndex] ? 'answered' : 'unanswered');
                } else {
                    // Mark soal
                    markedQuestions.add(soalIndex);
                    updateSoalStatus(soalIndex, 'marked');
                }

                // Update tampilan tombol
                updateMarkButtonUI(soalIndex);
            });

            // Handler untuk tombol clear
            $('#clear').click(function() {
                const soalIndex = currentSoal;

                // Hapus visual selection
                $(`.soal-page[data-index="${soalIndex}"] .option-card`).removeClass('selected');

                // Hapus jawaban
                saveAnswer(soalIndex, null);

                // Hapus dari database
                $.post('save_jawaban.php', {
                    ujian_id: <?php echo $ujian_id; ?>,
                    soal_index: soalIndex,
                    jawaban: null
                });
            });

            // Event handler untuk tombol zoom in
            $('#zoom-in').click(function() {
                if (currentZoomLevel < zoomLevels.length - 1) {
                    currentZoomLevel++;
                    applyZoomToContent(currentZoomLevel);
                }
            });

            // Event handler untuk tombol zoom out  
            $('#zoom-out').click(function() {
                if (currentZoomLevel > 0) {
                    currentZoomLevel--;
                    applyZoomToContent(currentZoomLevel);
                }
            });
        });
    </script>

    <!-- tampilan pada pc, normal -->
    <?php if (!$sudah_ujian): ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 collapse d-none d-md-block">
                    <div class="soal-numbers">
                        <!-- Logo card -->
                        <!-- <div class="card mb-3 border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="assets/smagaedu.png" alt="SMAGAEdu Logo" class="img-fluid"
                                    style="height: 50px; width: auto; border-radius: 10px;">
                                <div>
                                    <h4 class="mb-0 fw-bold" style="color: #1c1c1e;">SMAGA<span style="color: rgb(218, 119, 86);">Edu</span></h4>
                                    <p class="text-muted mb-0" style="font-size: 14px;">EXAM</p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                        <!-- Info Siswa -->
                        <div class="card mb-3 border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div>
                                        <img src="<?php echo !empty($siswa['photo_url']) ?
                                                        ($siswa['photo_type'] === 'avatar' ? $siswa['photo_url'] : ($siswa['photo_type'] === 'upload' ? $siswa['photo_url'] : 'assets/pp.png'))
                                                        : 'assets/pp.png'; ?>"
                                            class="rounded-circle border"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold" style="color: #1c1c1e;"><?php echo $_SESSION['nama']; ?></h6>
                                        <div class="d-flex flex-column">
                                            <span class="badge text-truncate mb-1" style="background: rgba(218, 119, 86, 0.1); color: rgb(218, 119, 86); font-weight: normal; padding: 5px 10px; border-radius: 12px; max-width: 150px;" title="<?php echo $data_ujian['judul']; ?>">
                                                <?php echo $data_ujian['judul']; ?>
                                            </span>
                                            <small class="text-muted d-block" style="font-size: 12px;">
                                                <i class="bi bi-layers-half me-1"></i>Tingkat/Kelas : <?php echo !empty($siswa['tingkat']) ? $siswa['tingkat'] : (!empty($_SESSION['tingkat']) ? $_SESSION['tingkat'] : 'Semua Tingkat'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Countdown Timer -->
                                <div class="p-3 rounded-4 d-none d-md-flex" style="background: #f2f2f7;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-clock" style="color:rgb(218, 119, 86);"></i>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-secondary">Sisa Waktu:</small>
                                            <span id="countdown" style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- script untuk siswa waktu  -->
                                <script>
                                    <?php if (!$sudah_ujian): ?>
                                        // Ambil waktu selesai dari PHP
                                        const endTime = new Date('<?php echo $tanggal_selesai; ?>').getTime();

                                        // Update countdown setiap detik
                                        const timer = setInterval(function() {
                                            // Waktu sekarang
                                            const now = new Date().getTime();

                                            // Selisih waktu
                                            const distance = endTime - now;

                                            // Hitung waktu untuk jam, menit dan detik
                                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                            // Format tampilan countdown
                                            const countdownDisplay = document.getElementById('countdown');
                                            countdownDisplay.innerHTML = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                                            // Modifikasi script timeout di mulai_ujian.php
                                            // Cari bagian ini di dalam file mulai_ujian.php

                                            // Jika waktu habis
                                            if (distance < 0) {
                                                clearInterval(timer);
                                                countdownDisplay.innerHTML = "00:00:00";

                                                // Tampilkan modal waktu habis
                                                const timeoutModal = new bootstrap.Modal(document.getElementById('timeoutModal'));
                                                timeoutModal.show();

                                                // Submit jawaban otomatis
                                                const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
                                                const answers = JSON.parse(localStorage.getItem(storageKey) || '{}');

                                                $.post('submit_ujian.php', {
                                                    ujian_id: <?php echo $ujian_id; ?>,
                                                    answers: JSON.stringify(answers)
                                                }, function(response) {
                                                    console.log('Response dari submit:', response);
                                                    try {
                                                        const result = JSON.parse(response);
                                                        if (result.success && result.redirect) {
                                                            setTimeout(() => {
                                                                window.location.href = result.redirect;
                                                            }, 3000);
                                                        } else {
                                                            setTimeout(() => {
                                                                // Matikan audio terlebih dahulu
                                                                if (warningAudio) {
                                                                    warningAudio.pause();
                                                                    warningAudio.currentTime = 0;
                                                                    warningAudio = null;
                                                                }
                                                                // Baru pindah halaman setelah audio dimatikan
                                                                window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                                                            }, 3000);
                                                        }
                                                    } catch (e) {
                                                        setTimeout(() => {
                                                            window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                                                            warningAudio = new Audio('assets/warning.mp3');
                                                            warningAudio.volume = 0;
                                                            warningAudio.pause().catch(e => console.error("tidak bisa pause saat keluar ke waiting room"))

                                                            window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                                                        }, 3000);
                                                    }
                                                }).fail(function() {
                                                    setTimeout(() => {
                                                        window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                                                        warningAudio = new Audio('assets/warning.mp3');
                                                        warningAudio.volume = 0;
                                                        warningAudio.pause().catch(e => console.error("tidak bisa pause saat keluar ke waiting room"))

                                                        window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                                                    }, 3000);
                                                });
                                            }
                                        }, 1000);

                                    <?php endif; ?>

                                    // Styling untuk countdown yang mendekati habis
                                    function updateCountdownStyle(minutes) {
                                        const countdownElement = document.getElementById('countdown');
                                        if (minutes <= 5) { // Jika sisa 5 menit atau kurang
                                            countdownElement.style.color = '#dc3545'; // Warna merah
                                            countdownElement.style.fontWeight = 'bold';

                                            if (minutes <= 2) { // Jika sisa 2 menit atau kurang
                                                // Tambahkan animasi berkedip
                                                countdownElement.style.animation = 'blink 1s infinite';
                                            }
                                        }
                                    }

                                    // Tambahkan CSS untuk animasi berkedip
                                    const style = document.createElement('style');
                                    style.textContent = `
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
`;
                                    document.head.appendChild(style);
                                </script>
                            </div>
                        </div>

                        <div class="card mb-3 border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                            <div class="card-body p-3">
                                <div id="save-status" class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill me-2" style="color: #34C759; font-size: 16px;"></i>
                                            <span>Jawabanmu tersimpan</span>
                                        </div>
                                        <p class="m-0 p-0 ms-4 text-muted" style="font-size: 12px;">Kalau SMAGAEdu error atau komputer mati, jawaban ini tetap aman.</p>
                                    </div>
                                    <div id="refresh-button" class="d-none">
                                        <button class="btn btn-sm btn-light border rounded-circle" onclick="retrySaveAllAnswers()">
                                            <span class="refresh bi bi-arrow-clockwise"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Soal -->
                        <div class="card border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                            <div class="card-body p-4">
                                <h6 class="card-title mb-4" style="color: #1c1c1e; font-weight: 600;">Daftar Soal</h6>
                                <div class="d-flex flex-wrap gap-2 justify-content-start">
                                    <?php foreach ($soal_array as $index => $soal): ?>
                                        <div class="soal-number rounded-3 border-0 d-flex align-items-center justify-content-center"
                                            data-soal="<?php echo $index; ?>"
                                            data-status="unanswered"
                                            style="background: #f2f2f7;color:black; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                            <?php echo $index + 1; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>


                                <!-- Keterangan Status yang Diperbaiki -->
                                <div class="mt-4 p-3 rounded-4" style="background: #f2f2f7;">
                                    <p style="font-size: 12px;">Keterangan :</p>
                                    <div class="d-flex gap-3 align-items-center mb-2">
                                        <div class="soal-number rounded-3 border"
                                            style="width:24px; height:24px; background:#f2f2f7;"></div>
                                        <small style="font-size:12px;color: #3c3c43;">Belum dijawab</small>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center mb-2">
                                        <div class="soal-number rounded-3"
                                            style="width:24px; height:24px; background:#da7756;"></div>
                                        <small style="font-size:12px;color: #3c3c43;">Sudah dijawab</small>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center mb-2">
                                        <div class="soal-number rounded-3"
                                            style="width:24px; height:24px; background:#dc3545;"></div>
                                        <small style="font-size:12px;color: #3c3c43;">Ditandai</small>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center mb-2">
                                        <div class="position-relative rounded-3 border d-flex align-items-center justify-content-center"
                                            style="width:24px; height:24px; background:#da7756;">
                                            <div class="position-absolute" style="bottom: -6px; right: -6px; background-color: #34C759; border-radius: 50%; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-check-lg text-white" style="font-size: 10px;"></i>
                                            </div>
                                        </div>
                                        <small style="font-size:12px;color: #3c3c43;">Jawaban tersimpan</small>
                                    </div>
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="position-relative rounded-3 border d-flex align-items-center justify-content-center"
                                            style="width:24px; height:24px; background:#da7756;">
                                            <div class="position-absolute" style="bottom: -6px; right: -6px; background-color: #FF3B30; border-radius: 50%; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-x-lg text-white" style="font-size: 10px;"></i>
                                            </div>
                                        </div>
                                        <small style="font-size:12px;color: #3c3c43;">Gagal menyimpan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-9">
                    <div class="soal-content">
                        <!-- Modifikasi tampilan soal -->
                        <form id="exam-form">
                            <?php foreach ($soal_array as $index => $soal):
                                $has_description = !empty($soal['description']);
                            ?>
                                <div class="soal-page <?php echo $index === 0 ? '' : 'd-none'; ?>"
                                    data-index="<?php echo $index; ?>">

                                    <?php if ($has_description): ?>
                                        <div class="card mb-4 border" style="border-radius: 12px;">
                                            <div class="card-body p-4 bg-light" style="border-radius: 12px;">
                                                <h6 class="card-title fw-bold mb-3" style="color: #1c1c1e;">
                                                    <i class="bi bi-book me-2" style="color: #da7756;"></i>Cerita/Deskripsi Soal
                                                </h6>
                                                <div class="p-3 bg-white rounded-3 border">
                                                    <?php echo nl2br(htmlspecialchars($soal['description'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <h5 class="mb-4 soal-number-text">Soal <?php echo $index + 1; ?></h5>

                                    <?php if (!empty($soal['gambar_soal'])): ?>
                                        <div class="mb-3">
                                            <div class="image-container">
                                                <img src="<?php echo htmlspecialchars($soal['gambar_soal']); ?>"
                                                    alt="Gambar soal <?php echo $index + 1; ?>"
                                                    class="zoomable-image img-fluid rounded shadow-sm text-start"
                                                    style="max-height: 300px; width: auto;">
                                                <div class="zoom-controls">
                                                    <button type="button" class="zoom-btn" data-action="zoom" title="Perbesar Gambar">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mb-4 soal-text" id="soal-text-<?php echo $index; ?>"><?php echo htmlspecialchars_decode($soal['pertanyaan']); ?></p>
                                    <?php
                                    $options = [
                                        'a' => $soal['jawaban_a'],
                                        'b' => $soal['jawaban_b'],
                                        'c' => $soal['jawaban_c'],
                                        'd' => $soal['jawaban_d']
                                    ];
                                    foreach ($options as $key => $value):
                                    ?>
                                        <div class="option-card p-3 rounded border mb-3" data-value="<?php echo $key; ?>">
                                            <?php echo strtoupper($key) . ". " . $value; ?>

                                            <?php if (!empty($soal['gambar_jawaban_' . $key])): ?>
                                                <div class="mt-2">
                                                    <div class="image-container">
                                                        <img src="<?php echo htmlspecialchars($soal['gambar_jawaban_' . $key]); ?>"
                                                            alt="Gambar jawaban <?php echo strtoupper($key); ?>"
                                                            class="zoomable-image img-fluid rounded shadow-sm"
                                                            style="max-height: 150px; width: auto;">
                                                        <div class="zoom-controls">
                                                            <button type="button" class="zoom-btn" data-action="zoom" title="Perbesar Gambar">
                                                                <i class="bi bi-zoom-in"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                    <div class="bottom-navigation d-flex justify-content-between bg-white p-3 border" style="border-radius: 15px;">
                        <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="prev" style="border: 1px solid rgba(0,0,0,0.1) !important;">
                            <i class="ti ti-arrow-left" style="font-size: 2rem;margin-bottom:0.5rem; color: rgb(218, 119, 86);"></i>
                            <span class="d-none d-md-block" style="font-size: 12px;">Sebelumnya</span>
                        </button>
                        <div class="d-flex gap-3">
                            <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="mark">
                                <i class="ti ti-question-mark" style="font-size: 2rem;margin-bottom:0.5rem; color: #FF3B30;"></i>
                                <span class="d-none d-md-block text-muted" id="mark-ket" style="font-size: 12px;">Tandai</span>
                            </button>
                            <button class="btn border-0 border-md-1 d-flex flex-column align-items-center d-none d-md-flex" id="zoom-out">
                                <i class="ti ti-zoom-out" style="font-size: 2rem;margin-bottom:0.5rem; color: blue;"></i>
                                <span class="d-none d-md-block text-muted" style="font-size: 12px;">Perkecil</span>
                            </button>
                            <button class="btn border-0 border-md-1 d-flex flex-column align-items-center d-none d-md-flex" id="zoom-in">
                                <i class="ti ti-zoom-in" style="font-size: 2rem;margin-bottom:0.5rem; color: blue;"></i>
                                <span class="d-none d-md-block text-muted" style="font-size: 12px;">Perbesar</span>
                            </button>
                            <button class="btn border-0 d-none d-md-block border-md-1 d-flex flex-column align-items-center" id="clear">
                                <i class="ti ti-eraser" style="font-size: 2rem;margin-bottom:0.5rem; color: #8E8E93;"></i>
                                <span class="d-none d-md-block text-muted" style="font-size: 12px;">Hapus</span>
                            </button>
                            <button class="btn d-md-none border-0 border-md-1 d-flex flex-column align-items-center" data-bs-toggle="collapse" data-bs-target="#mobileInfoCollapse">
                                <i class="ti ti-info-circle" style="font-size: 2rem;margin-bottom:0.5rem; color: #007AFF;"></i>
                                <span class="d-none d-md-block text-muted" style="font-size: 12px;">Info</span>
                            </button>
                            <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="finish">
                                <i class="ti ti-rosette-discount-check" style="font-size: 2rem;margin-bottom:0.5rem; color: #34C759;"></i>
                                <span class="d-none d-md-block text-muted" style="font-size: 12px;">Selesai</span>
                            </button>
                        </div>
                        <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="next" style="border: 1px solid rgba(0,0,0,0.1) !important;">
                            <i class="ti ti-arrow-right" style="font-size: 2rem;margin-bottom:0.5rem; color: rgb(218, 119, 86);"></i>
                            <span class="d-none d-md-block" style="font-size: 12px;">Selanjutnya</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Zoom Overlay -->
    <div id="zoomOverlay" class="zoom-overlay">
        <button type="button" class="zoom-close" id="closeZoom">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="zoom-container" id="zoomContainer">
            <img id="zoomedImage" src="" alt="Zoomed Image">
        </div>

        <div class="zoom-instructions" id="zoomInstructions">
            <div class="d-flex align-items-center gap-3 text-white">
                <div>
                    <i class="bi bi-mouse2 me-1"></i>
                    Klik gambar untuk memperbesar
                </div>
                <div>
                    <i class="bi bi-hand-index me-1"></i>
                    Seret mouse untuk menggeser gambar
                </div>
                <div>
                    <i class="bi bi-arrows-fullscreen me-1"></i>
                    Klik area kosong untuk menutup
                </div>
            </div>
        </div>
    </div>

    <!-- untuk mengatur font size pada pertanyaan -->
    <script>
        // Fungsi untuk menyesuaikan ukuran font berdasarkan panjang soal
        function adjustFontSize() {
            const soalElements = document.querySelectorAll('.soal-text');

            soalElements.forEach(element => {
                // Reset class terlebih dahulu
                element.classList.remove('length-medium', 'length-long', 'length-very-long');

                // Hitung panjang teks (tidak termasuk HTML tags)
                const textLength = element.textContent.trim().length;

                // Sesuaikan berdasarkan panjang
                if (textLength > 150 && textLength <= 300) {
                    element.classList.add('length-medium');
                } else if (textLength > 300 && textLength <= 500) {
                    element.classList.add('length-long');
                } else if (textLength > 500) {
                    element.classList.add('length-very-long');
                }

                console.log(`Soal length: ${textLength}, applied class: ${element.className}`);
            });
        }

        // Panggil fungsi saat dokumen dimuat dan saat mengganti soal
        document.addEventListener('DOMContentLoaded', function() {
            adjustFontSize();

            // Observer untuk mendeteksi penggantian soal yang ditampilkan
            const soalContent = document.querySelector('.soal-content');
            if (soalContent) {
                const observer = new MutationObserver(adjustFontSize);
                observer.observe(soalContent, {
                    childList: true,
                    subtree: true
                });
            }
        });

        // Panggil ulang saat navigasi soal
        document.querySelectorAll('#prev, #next, .soal-number').forEach(element => {
            element.addEventListener('click', function() {
                // Beri waktu untuk DOM diperbarui
                setTimeout(adjustFontSize, 100);
            });
        });
    </script>

    <div class="modal fade" id="finishModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <h5 class="mt-3 fw-bold">Selesaikan Ujian</h5>
                    <p class="mb-4">Apakah kamu yakin ingin menyelesaikan ujian ini? Setelah ujian diselesaikan, kamu tidak dapat mengubah jawaban lagi.</p>
                    <div class="d-flex gap-2 btn-group">
                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Nanti</button>
                        <button type="button" id="confirmFinish" class="btn text-white px-4" style="border-radius: 12px; background-color:rgb(218, 119, 86);">Kumpulkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 5rem; color:rgb(218, 119, 86)"></i>
                    </div>
                    <h5 class="mt-3 fw-bold">Ujian Berhasil Diselesaikan</h5>
                    <p class="mb-4">Terima kasih telah mengerjakan ujian dengan baik. Jawaban Anda telah tersimpan.</p>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border" role="status" style="color: rgb(218, 119, 86);">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentSoal = 0;
        const totalSoal = <?php echo count($soal_array); ?>;
        let answers = {};
        const markedQuestions = new Set();

        // Variable untuk zoom functionality
        let currentZoomLevel = 3; // Default zoom normal (di tengah)
        const zoomLevels = [
            'zoom-tiny', // Level 0 - paling kecil
            'zoom-small', // Level 1 
            'zoom-medium', // Level 2
            'zoom-normal', // Level 3 - default di tengah
            'zoom-large', // Level 4
            'zoom-extra-large', // Level 5
            'zoom-huge' // Level 6 - paling besar
        ];
        const zoomStorageKey = `ujian_zoom_${<?php echo $ujian_id; ?>}`;

        // Function untuk mengaplikasikan zoom ke semua elemen
        function applyZoomToContent(zoomLevel) {
            console.log('Applying zoom level:', zoomLevel, 'Class:', zoomLevels[zoomLevel]);

            // Remove SEMUA class zoom
            $('.soal-text').removeClass('zoom-tiny zoom-small zoom-medium zoom-normal zoom-large zoom-extra-large zoom-huge');
            $('.option-card').removeClass('zoom-tiny zoom-small zoom-medium zoom-normal zoom-large zoom-extra-large zoom-huge');
            $('.soal-number-text').removeClass('zoom-tiny zoom-small zoom-medium zoom-normal zoom-large zoom-extra-large zoom-huge');

            // Aplikasikan class zoom yang baru
            const zoomClass = zoomLevels[zoomLevel];
            $('.soal-text').addClass(zoomClass);
            $('.option-card').addClass(zoomClass);
            $('.soal-number-text').addClass(zoomClass);

            console.log('Applied class:', zoomClass);

            // Simpan ke localStorage
            localStorage.setItem(zoomStorageKey, zoomLevel.toString());

            // Update tampilan tombol zoom
            updateZoomButtonState();
        }

        // Function untuk update state tombol zoom
        function updateZoomButtonState() {
            const zoomInBtn = $('#zoom-in');
            const zoomOutBtn = $('#zoom-out');

            // Disable zoom in jika sudah maksimal
            if (currentZoomLevel >= zoomLevels.length - 1) {
                zoomInBtn.find('i').css('color', 'blue');
                zoomInBtn.prop('disabled', true);
            } else {
                zoomInBtn.find('i').css('color', 'blue');
                zoomInBtn.prop('disabled', false);
            }

            // Disable zoom out jika sudah minimal
            if (currentZoomLevel <= 0) {
                zoomOutBtn.find('i').css('color', 'blue');
                zoomOutBtn.prop('disabled', true);
            } else {
                zoomOutBtn.find('i').css('color', 'blue');
                zoomOutBtn.prop('disabled', false);
            }
        }

        // Variabel untuk melacak status penyimpanan soal
        let savingStatus = {};
        let lastSavedSoalIndex = null;
        let lastSavedJawaban = null;

        let animationInProgress = false;


        function showSoal(index, direction = null) {
            // Validasi indeks soal
            if (index < 0 || index >= totalSoal) {
                // Soal mentok, berikan efek getar pada tombol
                if (index < 0) {
                    $('#prev').addClass('shake');
                    setTimeout(() => $('#prev').removeClass('shake'), 500);
                } else {
                    $('#next').addClass('shake');
                    setTimeout(() => $('#next').removeClass('shake'), 500);
                }
                return;
            }

            // Jika animasi sedang berlangsung, jangan mulai animasi baru
            if (animationInProgress && direction) {
                console.log('Animasi sedang berlangsung, menolak pergantian soal');
                return;
            }

            // Set flag animasi sedang berlangsung
            if (direction) {
                animationInProgress = true;
            }

            console.log(`Bergeser dari soal ${currentSoal} ke soal ${index}, arah: ${direction}`);

            // Jika tidak ada animasi (klik nomor soal)
            if (!direction) {
                console.log('Mode tanpa animasi');
                // Sembunyikan semua soal
                $('.soal-page').addClass('d-none').removeClass('current');
                // Hapus semua transformasi yang tersisa
                $('.soal-page').css('transform', '');
                $('.soal-page').css('position', '');
                $('.soal-page').css('top', '');
                $('.soal-page').css('left', '');
                $('.soal-page').css('width', '');

                // Tampilkan soal target
                $(`.soal-page[data-index="${index}"]`).removeClass('d-none').addClass('current');

                // Update indeks soal saat ini
                currentSoal = index;
            }
            // Mode dengan animasi slide
            else {
                // Dapatkan halaman saat ini dan target
                const currentPage = $(`.soal-page[data-index="${currentSoal}"]`);
                const targetPage = $(`.soal-page[data-index="${index}"]`);

                // Simpan indeks untuk keperluan callback
                const oldIndex = currentSoal;
                // Update indeks soal saat ini
                currentSoal = index;

                console.log(`Animasi dari soal ${oldIndex} ke soal ${index}`);

                // Sembunyikan semua soal kecuali current dan target
                $('.soal-page').not(currentPage).not(targetPage).addClass('d-none');

                // Reset semua properti CSS yang mungkin tersisa dari animasi sebelumnya
                $('.soal-page').not(currentPage).not(targetPage).css({
                    'transform': '',
                    'position': '',
                    'top': '',
                    'left': '',
                    'width': ''
                });

                // Persiapkan animasi
                if (direction === 'next') {
                    console.log('Animasi slide ke kanan');

                    // Tampilkan kedua halaman
                    currentPage.removeClass('d-none');
                    targetPage.removeClass('d-none');

                    // Posisikan halaman target di luar viewport (kanan)
                    targetPage.css({
                        'position': 'absolute',
                        'top': '0',
                        'left': '0',
                        'width': '100%',
                        'transform': 'translateX(100%)',
                        'transition': 'transform 0.3s ease'
                    });

                    // Pastikan halaman saat ini di posisi awal
                    currentPage.css({
                        'transform': 'translateX(0)',
                        'transition': 'transform 0.3s ease'
                    });

                    // Beri waktu untuk rendering
                    setTimeout(() => {
                        console.log('Menjalankan animasi slide-next');

                        // Animasikan perpindahan
                        currentPage.css('transform', 'translateX(-100%)');
                        targetPage.css('transform', 'translateX(0)');

                        // Setelah animasi selesai
                        setTimeout(() => {
                            console.log('Animasi selesai');

                            // Bersihkan properti CSS
                            currentPage.addClass('d-none').css({
                                'transform': '',
                                'transition': '',
                                'position': '',
                                'top': '',
                                'left': '',
                                'width': ''
                            });

                            targetPage.removeClass('d-none').addClass('current').css({
                                'transform': '',
                                'transition': '',
                                'position': '',
                                'top': '',
                                'left': '',
                                'width': ''
                            });

                            // Reset flag animasi
                            animationInProgress = false;
                        }, 350); // Durasi animasi CSS + sedikit margin
                    }, 50);
                } else if (direction === 'prev') {
                    console.log('Animasi slide ke kiri');

                    // Tampilkan kedua halaman
                    currentPage.removeClass('d-none');
                    targetPage.removeClass('d-none');

                    // Posisikan halaman target di luar viewport (kiri)
                    targetPage.css({
                        'position': 'absolute',
                        'top': '0',
                        'left': '0',
                        'width': '100%',
                        'transform': 'translateX(-100%)',
                        'transition': 'transform 0.3s ease'
                    });

                    // Pastikan halaman saat ini di posisi awal
                    currentPage.css({
                        'transform': 'translateX(0)',
                        'transition': 'transform 0.3s ease'
                    });

                    // Beri waktu untuk rendering
                    setTimeout(() => {
                        console.log('Menjalankan animasi slide-prev');

                        // Animasikan perpindahan
                        currentPage.css('transform', 'translateX(100%)');
                        targetPage.css('transform', 'translateX(0)');

                        // Setelah animasi selesai
                        setTimeout(() => {
                            console.log('Animasi selesai');

                            // Bersihkan properti CSS
                            currentPage.addClass('d-none').css({
                                'transform': '',
                                'transition': '',
                                'position': '',
                                'top': '',
                                'left': '',
                                'width': ''
                            });

                            targetPage.removeClass('d-none').addClass('current').css({
                                'transform': '',
                                'transition': '',
                                'position': '',
                                'top': '',
                                'left': '',
                                'width': ''
                            });

                            // Reset flag animasi
                            animationInProgress = false;
                        }, 350); // Durasi animasi CSS + sedikit margin
                    }, 50);
                }
            }

            setTimeout(() => {
                applyZoomToContent(currentZoomLevel);
            }, 100);

            // Update UI tombol mark
            updateMarkButtonUI(index);
        }

        // Tambahkan handler ini untuk tombol-tombol navigasi
        $('#prev').click(() => {
            if (!animationInProgress && currentSoal > 0) {
                showSoal(currentSoal - 1, 'prev');
            } else if (currentSoal === 0) {
                // Efek getar jika sudah di soal pertama
                $('#prev').addClass('shake');
                setTimeout(() => $('#prev').removeClass('shake'), 500);
            }
        });

        $('#next').click(() => {
            if (!animationInProgress && currentSoal < totalSoal - 1) {
                showSoal(currentSoal + 1, 'next');
            } else if (currentSoal === totalSoal - 1) {
                // Efek getar jika sudah di soal terakhir
                $('#next').addClass('shake');
                setTimeout(() => $('#next').removeClass('shake'), 500);
            }
        });

        // Modifikasi klik nomor soal untuk mencegah klik selama animasi
        $('.soal-number').click(function() {
            if (!animationInProgress) {
                const index = $(this).data('soal');
                showSoal(index); // Tanpa animasi

                // Jika di mobile, tutup collapse
                if (window.innerWidth < 768) {
                    $('#mobileInfoCollapse').collapse('hide');
                }
            }
        });

        // Fungsi untuk memperbarui status soal di SEMUA elemen
        function updateSoalStatus(soalIndex, status) {
            // Update semua elemen soal-number dengan data-soal yang sama
            $(`.soal-number[data-soal="${soalIndex}"]`).attr('data-status', status);
        }

        $('.option-card').click(function() {
            const soalIndex = $(this).closest('.soal-page').data('index');
            const jawaban = $(this).data('value');

            $(this).closest('.soal-page').find('.option-card').removeClass('selected');
            $(this).addClass('selected');

            if (answers[soalIndex]) {
                $(`.soal-number[data-soal="${soalIndex}"]`).attr('data-status', 'answered');
            }

            answers[soalIndex] = jawaban;

            $.post('save_jawaban.php', {
                ujian_id: <?php echo $ujian_id; ?>,
                soal_index: soalIndex,
                jawaban: jawaban
            });
        });

        // $('#prev').click(() => {
        //     if (currentSoal > 0) {
        //         showSoal(currentSoal - 1);
        //     }
        // });

        // $('#next').click(() => {
        //     if (currentSoal < totalSoal - 1) {
        //         showSoal(currentSoal + 1);
        //     }
        // });

        // Fungsi untuk memperbarui UI status penyimpanan
        function updateSaveStatus(status, message = '') {
            const saveStatus = $('#save-status');
            const refreshButton = $('#refresh-button');

            saveStatus.removeClass('text-success text-danger text-warning');
            saveStatus.find('i').removeClass('bi-check-circle-fill bi-x-circle-fill bi-hourglass-split');

            if (status === 'saved') {
                saveStatus.addClass('text-success');
                saveStatus.find('i').addClass('bi-check-circle-fill').css('color', '#34C759').css({
                    'animation': 'none'
                }); // Stop animation if it was spinning
                saveStatus.find('span').not('.refresh').text('Jawaban tersimpan');
                saveStatus.find('p').text('Kalau SMAGAEdu error atau komputer mati, jawaban ini tetap aman.');
                refreshButton.addClass('d-none');
            } else if (status === 'saving') {
                saveStatus.addClass('text-warning');
                saveStatus.find('i')
                    .attr('class', 'bi bi-arrow-repeat me-2')
                    .css({
                        'color': '#FF9500',
                        'animation': 'spin 1s linear infinite',
                        'font-size': '16px'
                    });
                saveStatus.find('span').not('.refresh').text('Menyimpan, mohon tunggu...');
                saveStatus.find('p').text(message || '');
                refreshButton.addClass('d-none');

                // Add the spin animation style if it doesn't exist yet
                if (!$('#spinAnimationStyle').length) {
                    $('head').append(`
            <style id="spinAnimationStyle">
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `);
                }
            } else if (status === 'error') {
                saveStatus.addClass('text-danger');
                saveStatus.find('i').removeClass('bi-check-circle-fill bi-x-circle-fill bi-hourglass-split bi-spinner-grow bi-arrow-repeat')
                    .addClass('bi-exclamation-circle-fill')
                    .css({
                        'color': '#FF3B30',
                        'animation': 'none' // Stop animation if it was spinning
                    });
                saveStatus.find('span').not('.refresh').text('Gagal menyimpan');
                saveStatus.find('p').text('Cek koneksimu atau coba klik tombol segarkan di samping.');
                refreshButton.removeClass('d-none');
            }
        }

        // Fungsi untuk memperbarui indikator status pada nomor soal
        function updateSoalStatusIndicator(soalIndex, status) {
            // Temukan semua elemen dengan data-soal yang sama
            const soalElements = $(`.soal-number[data-soal="${soalIndex}"]`);

            // Hapus indikator yang sudah ada
            soalElements.find('.status-indicator').remove();

            // Tambahkan indikator baru berdasarkan status
            if (status === 'saved') {
                soalElements.append('<span class="status-indicator status-saved"><i class="bi bi-check"></i></span>');
            } else if (status === 'error') {
                soalElements.append('<span class="status-indicator status-error"><i class="bi bi-x"></i></span>');
            }

            // Simpan status di savingStatus
            savingStatus[soalIndex] = status;
        }

        // Modifikasi fungsi saveAnswer untuk menangani status penyimpanan
        function saveAnswer(soalIndex, jawaban) {
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            let storedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            if (jawaban === null) {
                delete storedAnswers[soalIndex];
                delete answers[soalIndex]; // Hapus dari variable answers juga
            } else {
                storedAnswers[soalIndex] = jawaban;
                answers[soalIndex] = jawaban; // Simpan di variable answers juga
            }

            localStorage.setItem(storageKey, JSON.stringify(storedAnswers));

            // Update status elemen soal-number
            if (jawaban === null) {
                updateSoalStatus(soalIndex, 'unanswered');
            } else {
                updateSoalStatus(soalIndex, markedQuestions.has(parseInt(soalIndex)) ? 'marked' : 'answered');
            }

            // Simpan status penyimpanan terakhir
            lastSavedSoalIndex = soalIndex;
            lastSavedJawaban = jawaban;

            // Update UI status penyimpanan ke "saving"
            updateSaveStatus('saving');

            // Simpan ke database
            $.post('save_jawaban.php', {
                    ujian_id: <?php echo $ujian_id; ?>,
                    soal_index: soalIndex,
                    jawaban: jawaban
                })
                .done(function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Jawaban berhasil disimpan
                            updateSaveStatus('saved');
                            updateSoalStatusIndicator(soalIndex, 'saved');
                        } else {
                            // Error saat menyimpan
                            updateSaveStatus('error', result.error || 'Gagal menyimpan jawaban');
                            updateSoalStatusIndicator(soalIndex, 'error');
                        }
                    } catch (e) {
                        // Error parsing response
                        updateSaveStatus('error', 'Gagal memproses respons server');
                        updateSoalStatusIndicator(soalIndex, 'error');
                    }
                })
                .fail(function() {
                    // Error koneksi
                    updateSaveStatus('error', 'Koneksi terputus');
                    updateSoalStatusIndicator(soalIndex, 'error');
                });
        }

        function retrySaveAllAnswers() {
            // Ambil semua jawaban dari localStorage
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            const storedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            // Update UI status penyimpanan ke "saving"
            updateSaveStatus('saving', 'Menyimpan semua jawaban...');

            // Tambahkan animasi spinner pada tombol refresh
            $('#refresh-button .refresh').addClass('spinner-border spinner-border-sm').removeClass('bi-arrow-clockwise');

            // Siapkan counter untuk pelacakan progress
            let totalAnswers = Object.keys(storedAnswers).length;
            let savedCount = 0;
            let failedCount = 0;

            // Jika tidak ada jawaban untuk disimpan
            if (totalAnswers === 0) {
                updateSaveStatus('saved', 'Tidak ada jawaban untuk disimpan');
                $('#refresh-button .refresh').removeClass('spinner-border spinner-border-sm').addClass('bi-arrow-clockwise');
                $('#refresh-button').addClass('d-none');
                return;
            }

            // Simpan semua jawaban satu per satu
            for (const soalIndex in storedAnswers) {
                const jawaban = storedAnswers[soalIndex];

                // Kirim ke server
                $.post('save_jawaban.php', {
                        ujian_id: <?php echo $ujian_id; ?>,
                        soal_index: soalIndex,
                        jawaban: jawaban
                    })
                    .done(function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                // Jawaban berhasil disimpan
                                savedCount++;
                                updateSoalStatusIndicator(soalIndex, 'saved');
                            } else {
                                // Error saat menyimpan
                                failedCount++;
                                updateSoalStatusIndicator(soalIndex, 'error');
                            }
                        } catch (e) {
                            // Error parsing response
                            failedCount++;
                            updateSoalStatusIndicator(soalIndex, 'error');
                        }
                    })
                    .fail(function() {
                        // Error koneksi
                        failedCount++;
                        updateSoalStatusIndicator(soalIndex, 'error');
                    })
                    .always(function() {
                        // Periksa apakah semua jawaban sudah diproses
                        if (savedCount + failedCount >= totalAnswers) {
                            // Semua jawaban sudah diproses
                            $('#refresh-button .refresh').removeClass('spinner-border spinner-border-sm').addClass('bi-arrow-clockwise');

                            if (failedCount === 0) {
                                // Semua berhasil
                                updateSaveStatus('saved', `${savedCount} jawaban berhasil disimpan`);
                                $('#refresh-button').addClass('d-none');
                            } else {
                                // Ada yang gagal
                                updateSaveStatus('error', `${savedCount} berhasil, ${failedCount} gagal disimpan`);
                            }
                        }
                    });
            }
        }

        // Load jawaban saat halaman dimuat

        // Modifikasi pada fungsi loadAnswers() di mulai_ujian.php
        function loadAnswers() {
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;

            // Pertama, coba ambil dari localStorage (sebagai fallback)
            const storedAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            // Muat jawaban dari database
            updateSaveStatus('saving');

            $.getJSON('load_jawaban.php', {
                ujian_id: <?php echo $ujian_id; ?>
            }, function(response) {
                if (response.success && response.answers) {
                    updateSaveStatus('saved');

                    // Gabungkan dengan jawaban dari localStorage (prioritaskan database)
                    const combinedAnswers = {
                        ...storedAnswers,
                        ...response.answers
                    };

                    // Simpan kembali ke localStorage
                    localStorage.setItem(storageKey, JSON.stringify(combinedAnswers));

                    // Update variable answers dan UI
                    Object.keys(combinedAnswers).forEach(key => {
                        answers[key] = combinedAnswers[key];

                        // Update tampilan pilihan jawaban
                        const intIndex = parseInt(key);
                        $(`.soal-page[data-index="${intIndex}"] .option-card[data-value="${combinedAnswers[key]}"]`).addClass('selected');

                        // Update status tombol
                        updateSoalStatus(intIndex, markedQuestions.has(intIndex) ? 'marked' : 'answered');

                        // Tandai sebagai tersimpan
                        updateSoalStatusIndicator(intIndex, 'saved');
                    });
                } else {
                    // Jika gagal ambil dari database, gunakan localStorage saja
                    updateSaveStatus('error', 'Gagal memuat jawaban dari server');

                    Object.keys(storedAnswers).forEach(key => {
                        answers[key] = storedAnswers[key];

                        // Update tampilan
                        const intIndex = parseInt(key);
                        $(`.soal-page[data-index="${intIndex}"] .option-card[data-value="${storedAnswers[key]}"]`).addClass('selected');
                        updateSoalStatus(intIndex, markedQuestions.has(intIndex) ? 'marked' : 'answered');

                        // Tandai sebagai error
                        updateSoalStatusIndicator(intIndex, 'error');
                    });
                }
            }).fail(function() {
                // Jika request gagal, gunakan localStorage saja
                updateSaveStatus('error', 'Gagal terhubung ke server');

                Object.keys(storedAnswers).forEach(key => {
                    answers[key] = storedAnswers[key];

                    // Update tampilan
                    const intIndex = parseInt(key);
                    $(`.soal-page[data-index="${intIndex}"] .option-card[data-value="${storedAnswers[key]}"]`).addClass('selected');
                    updateSoalStatus(intIndex, markedQuestions.has(intIndex) ? 'marked' : 'answered');

                    // Tandai sebagai error
                    updateSoalStatusIndicator(intIndex, 'error');
                });
            });
        }

        // Handler untuk klik opsi jawaban
        $('.option-card').click(function() {
            const soalIndex = $(this).closest('.soal-page').data('index');
            const jawaban = $(this).data('value');

            $(this).closest('.soal-page').find('.option-card').removeClass('selected');
            $(this).addClass('selected');

            // Simpan jawaban - ini akan memperbarui UI status penyimpanan secara otomatis
            saveAnswer(soalIndex, jawaban);
        });

        // Panggil loadAnswers saat halaman dimuat
        $(document).ready(loadAnswers);

        // Handler untuk tombol clear
        $('#clear').click(function() {
            const soalIndex = currentSoal;

            // Hapus visual selection
            $(`.soal-page[data-index="${soalIndex}"] .option-card`).removeClass('selected');

            // Hapus jawaban - ini akan memperbarui UI status penyimpanan secara otomatis
            saveAnswer(soalIndex, null);
        });

        $('#finish').click(() => {
            const finishModal = new bootstrap.Modal(document.getElementById('finishModal'));
            finishModal.show();
        });


        // Modifikasi script confirmFinish di mulai_ujian.php
        // Cari bagian ini di dalam file mulai_ujian.php

        $('#confirmFinish').click(() => {

            if (warningAudio) {
                warningAudio.pause();
                warningAudio.currentTime = 0;
                warningAudio = null;
            }

            // Ambil data dari localStorage sebagai cadangan
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            const localAnswers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            // Tambahkan spinner atau indikator loading
            $('#confirmFinish').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...');
            $('#confirmFinish').prop('disabled', true);



            $.post('submit_ujian.php', {
                ujian_id: <?php echo $ujian_id; ?>,
                answers: JSON.stringify(localAnswers) // Ini sebenarnya tidak terlalu digunakan oleh submit_ujian.php yang baru, tapi tetap dikirim untuk kompatibilitas
            }, function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#finishModal').modal('hide');
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();

                        // Bersihkan localStorage
                        localStorage.removeItem(storageKey);

                        // Gunakan timeout sebelum redirect ke waiting_room.php
                        setTimeout(() => {
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            } else {
                                window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                            }
                        }, 2000);
                    } else {
                        alert('Terjadi kesalahan: ' + (result.error || 'Undefined error'));
                        // Reset tombol
                        $('#confirmFinish').html('Kumpulkan');
                        $('#confirmFinish').prop('disabled', false);
                    }
                } catch (e) {
                    alert('Terjadi kesalahan parsing');
                    // Reset tombol
                    $('#confirmFinish').html('Kumpulkan');
                    $('#confirmFinish').prop('disabled', false);
                }
            }).fail(() => {
                alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                // Reset tombol
                $('#confirmFinish').html('Kumpulkan');
                $('#confirmFinish').prop('disabled', false);
            });
        });


        // Tambahkan fungsi untuk memperbarui tampilan tombol mark
        function updateMarkButtonUI(soalIndex) {
            const markButton = $('#mark');
            const markKet = $('#mark-ket');
            const markIcon = markButton.find('i');

            if (markedQuestions.has(soalIndex)) {
                // Soal ditandai -> tampilkan status ON
                markButton.addClass('bg-danger');
                markKet.removeClass('text-muted').addClass('text-white');
                // Gunakan Tabler Icons yang konsisten
                markIcon.removeClass('ti-question-mark').addClass('ti ti-question-mark text-white');
                markKet.text('Tertandai');
            } else {
                // Soal tidak ditandai -> tampilkan status OFF
                markButton.removeClass('bg-danger');
                markKet.removeClass('text-white').addClass('text-muted');
                // Kembalikan ke icon awal
                markIcon.removeClass('ti-bookmark-filled text-white').addClass('ti-question-mark');
                markKet.text('Tandai');
            }
        }
    </script>

    <!-- script untuk status jawaban sudah tersimpan atau belum -->
    <script>

    </script>


    <script>
        // // Handle back button click to remove all backdrops
        // document.getElementById('backButton').addEventListener('click', function(e) {
        //     // Remove all modal backdrops
        //     const backdrops = document.querySelectorAll('.modal-backdrop');
        //     backdrops.forEach(backdrop => {
        //         backdrop.classList.remove('show');
        //         backdrop.classList.remove('fade');
        //         backdrop.remove();
        //     });

        //     // Reset body classes
        //     document.body.classList.remove('modal-open');
        //     document.body.style.overflow = '';
        //     document.body.style.paddingRight = '';

        //     // Allow default navigation
        //     // No need to prevent default since we want to navigate to ujian.php
        // });
    </script>

    <!-- modal waktu habis -->
    <div class="modal fade" id="timeoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <h5 class="mt-3 fw-bold">Ups, Waktu Ujian Telah Berakhir</h5>
                    <p class="mb-4">Jawaban Anda akan dikumpulkan secara otomatis, terima kasih telah mengikuti ujian</p>
                    <div class="d-flex gap-2 btn-group">
                        <a href="ujian.php" class="btn text-white flex-fill px-4" id="confirmTimeout" style="border-radius: 12px; background-color: rgb(218, 119, 86);">
                            Mengerti
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- script untuk fungsi batas waktu ujian -->

    <script>
        function checkTimeout() {
            const now = new Date().getTime();
            console.log('Waktu sekarang:', new Date(now));
            console.log('Sisa waktu:', Math.floor((endTime - now) / 1000), 'detik');

            if (now >= endTime) {
                console.log('Waktu habis!');
                const timeoutModal = new bootstrap.Modal(document.getElementById('timeoutModal'));
                timeoutModal.show();
                clearInterval(timeoutChecker);
            }
        }

        // Debug setiap 5 detik (lebih lama untuk memudahkan debugging)
        const timeoutChecker = setInterval(checkTimeout, 5000);

        // Debug untuk tombol Ok
        // Modifikasi script confirmTimeout di mulai_ujian.php
        // Cari bagian ini di dalam file mulai_ujian.php

        // Update confirmTimeout handler
        document.getElementById('confirmTimeout').addEventListener('click', function() {
            console.log('Tombol Ok diklik');

            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            const answers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            $.post('submit_ujian.php', {
                ujian_id: <?php echo $ujian_id; ?>,
                answers: JSON.stringify(answers)
            }, function(response) {
                console.log('Response dari submit:', response);
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        cleanupData(); // Hapus data setelah submit berhasil
                        if (result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                        }
                    } else {
                        window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                    }
                } catch (e) {
                    window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
                }
            }).fail(function(error) {
                console.error('Error saat submit:', error);
                alert('Terjadi kesalahan saat submit jawaban');
                window.location.href = 'waiting_room.php?ujian_id=<?php echo $ujian_id; ?>';
            });
        });
    </script>

    <!-- waktu untuk menghapus jawaban siswa jika refresh atau kembali -->
    <script>
        // Tambahkan event handler untuk window beforeunload dan unload
        window.addEventListener('beforeunload', function(e) {
            // Hapus data dari localStorage
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            localStorage.removeItem(storageKey);

            // Kirim request untuk menghapus jawaban dari database
            navigator.sendBeacon('delete_jawaban.php', JSON.stringify({
                ujian_id: <?php echo $ujian_id; ?>,
                siswa_id: <?php echo $_SESSION['userid']; ?>
            }));

            e.preventDefault();
            e.returnValue = 'Dilarang menutup tab ujian!';
        });

        // Handler saat halaman akan di-refresh atau ditutup
        window.addEventListener('unload', function() {
            // Hapus data dari localStorage
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            localStorage.removeItem(storageKey);
        });

        // Function untuk menghapus data saat timeout atau selesai ujian
        function cleanupData() {
            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            localStorage.removeItem(storageKey);

            $.post('delete_jawaban.php', {
                ujian_id: <?php echo $ujian_id; ?>,
                siswa_id: <?php echo $_SESSION['userid']; ?>
            });
        }

        // Update confirmTimeout handler
        document.getElementById('confirmTimeout').addEventListener('click', function() {
            console.log('Tombol Ok diklik');

            const storageKey = `ujian_${<?php echo $ujian_id; ?>}`;
            const answers = JSON.parse(localStorage.getItem(storageKey) || '{}');

            $.post('submit_ujian.php', {
                ujian_id: <?php echo $ujian_id; ?>,
                answers: JSON.stringify(answers)
            }, function(response) {
                console.log('Response dari submit:', response);
                cleanupData(); // Hapus data setelah submit berhasil
                window.location.href = 'ujian.php';
            }).fail(function(error) {
                console.error('Error saat submit:', error);
                alert('Terjadi kesalahan saat submit jawaban');
            });
        });

        function applyStyles() {
            // Pastikan style diaplikasikan ke semua elemen soal-number
            $('.soal-number').each(function() {
                const soalIndex = $(this).data('soal');
                if (markedQuestions.has(soalIndex)) {
                    $(this).attr('data-status', 'marked');
                } else if (answers[soalIndex]) {
                    $(this).attr('data-status', 'answered');
                } else {
                    $(this).attr('data-status', 'unanswered');
                }
            });
        }
    </script>

    <!-- fitur zoom gambar di soal dan jawaban -->
    <script>
        // Advanced Image Zoom with Pan Functionality
        $(document).ready(function() {
            let isZoomed = false;
            let isDragging = false;
            let startX, startY;
            let currentX = 0,
                currentY = 0;

            setTimeout(function() {
                initAdvancedImageZoom();
            }, 1000);

            function initAdvancedImageZoom() {
                console.log('Initializing advanced image zoom...');

                // Handle zoom button click
                $(document).off('click', '[data-action="zoom"]').on('click', '[data-action="zoom"]', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const container = $(this).closest('.image-container');
                    const image = container.find('.zoomable-image');
                    const imageSrc = image.attr('src');

                    if (imageSrc) {
                        showZoomOverlay(imageSrc);
                    }

                    return false;
                });

                // Handle image click untuk zoom
                $(document).off('click', '.zoomable-image').on('click', '.zoomable-image', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const imageSrc = $(this).attr('src');
                    if (imageSrc) {
                        showZoomOverlay(imageSrc);
                    }

                    return false;
                });

                // Handle close zoom - klik area kosong
                $(document).off('click', '#zoomOverlay').on('click', '#zoomOverlay', function(e) {
                    if (e.target === this || e.target.id === 'zoomContainer') {
                        hideZoomOverlay();
                    }
                });

                // Handle close button
                $(document).off('click', '#closeZoom').on('click', '#closeZoom', function(e) {
                    hideZoomOverlay();
                });

                // Handle image click di overlay untuk zoom in/out
                $(document).off('click', '#zoomedImage').on('click', '#zoomedImage', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (!isZoomed) {
                        zoomInImage(e);
                    } else {
                        zoomOutImage();
                    }

                    return false;
                });

                // Handle mouse events untuk dragging
                $(document).off('mousedown', '#zoomedImage').on('mousedown', '#zoomedImage', function(e) {
                    if (isZoomed) {
                        isDragging = true;
                        startX = e.clientX - currentX;
                        startY = e.clientY - currentY;
                        $(this).css('cursor', 'grabbing');
                        e.preventDefault();
                    }
                });

                $(document).off('mousemove', '#zoomOverlay').on('mousemove', '#zoomOverlay', function(e) {
                    if (isDragging && isZoomed) {
                        currentX = e.clientX - startX;
                        currentY = e.clientY - startY;

                        $('#zoomedImage').css('transform', `scale(2) translate(${currentX}px, ${currentY}px)`);
                        e.preventDefault();
                    }
                });

                $(document).off('mouseup', '#zoomOverlay').on('mouseup', '#zoomOverlay', function(e) {
                    if (isDragging) {
                        isDragging = false;
                        if (isZoomed) {
                            $('#zoomedImage').css('cursor', 'grab');
                        }
                    }
                });

                // Handle ESC key
                $(document).off('keydown.zoom').on('keydown.zoom', function(e) {
                    if (e.keyCode === 27) { // ESC key
                        hideZoomOverlay();
                    }
                });

                // Prevent context menu pada gambar
                $(document).off('contextmenu', '#zoomedImage').on('contextmenu', '#zoomedImage', function(e) {
                    e.preventDefault();
                    return false;
                });
            }

            function showZoomOverlay(imageSrc) {
                console.log('Showing zoom overlay for:', imageSrc);

                // Reset state
                isZoomed = false;
                isDragging = false;
                currentX = 0;
                currentY = 0;

                $('#zoomedImage').attr('src', imageSrc);
                $('#zoomedImage').removeClass('zoomed');
                $('#zoomedImage').css({
                    'transform': 'scale(1)',
                    'cursor': 'zoom-in'
                });

                $('#zoomInstructions').removeClass('hidden');
                $('#zoomOverlay').css('display', 'flex').hide().fadeIn(300);

                // Disable scroll
                $('body').css('overflow', 'hidden');
            }

            function hideZoomOverlay() {
                console.log('Hiding zoom overlay');

                $('#zoomOverlay').fadeOut(300, function() {
                    $('#zoomedImage').attr('src', '');
                    isZoomed = false;
                    isDragging = false;
                    currentX = 0;
                    currentY = 0;
                });

                // Enable scroll
                $('body').css('overflow', '');
            }

            function zoomInImage(e) {
                console.log('Zooming in...');

                isZoomed = true;

                // Hitung posisi mouse relatif terhadap gambar
                const img = $('#zoomedImage')[0];
                const rect = img.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                // Set posisi awal berdasarkan klik mouse
                currentX = -x;
                currentY = -y;

                $('#zoomedImage').addClass('zoomed');
                $('#zoomedImage').css({
                    'transform': `scale(2) translate(${currentX}px, ${currentY}px)`,
                    'cursor': 'grab'
                });

                // Sembunyikan instruksi
                $('#zoomInstructions').addClass('hidden');
            }

            function zoomOutImage() {
                console.log('Zooming out...');

                isZoomed = false;
                isDragging = false;
                currentX = 0;
                currentY = 0;

                $('#zoomedImage').removeClass('zoomed');
                $('#zoomedImage').css({
                    'transform': 'scale(1)',
                    'cursor': 'zoom-in'
                });

                // Tampilkan instruksi kembali
                $('#zoomInstructions').removeClass('hidden');
            }
        });
    </script>


</body>

</html>