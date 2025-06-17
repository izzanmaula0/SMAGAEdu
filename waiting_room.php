<?php
include 'includes/session_config.php';
require "koneksi.php";

if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}

// Check if there's an active exam for this student
if (isset($_GET['ujian_id'])) {
    $ujian_id = $_GET['ujian_id'];

    // Get exam end time
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
} else {
    // Redirect if no exam ID
    header("Location: ujian.php");
    exit();
}

// Query untuk mengambil data siswa - sama seperti di mulai_ujian.php
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
    <title>Ruang Tunggu Ujian</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5XXM5FLKYE"></script>
    <link rel="icon" type="image/png" href="assets/tab.png">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Merriweather', serif;
            overflow-y: auto !important;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
            transition: background-color 0.3s ease;
        }

        .color-web:hover {
            background-color: rgb(206, 100, 65);
        }

        /* Full screen warning styles - copied from mulai_ujian.php */
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
            opacity: 0.7;
        }

        .game-container {
            background-color: white;
            border: rgba(64, 64, 64, 0.1) 1px solid;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .game-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: #1c1c1e;
        }

        /* .game-area {
            width: 100%;
            border-radius: 8px;
            background-color: #f2f2f7;
            min-height: 300px;
            position: relative;
            overflow: hidden;
        } */

        .btn-game {
            background-color: rgb(218, 119, 86);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-game:hover {
            background-color: rgb(206, 100, 65);
        }

        .countdown-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .countdown {
            font-size: 2.5rem;
            font-weight: 700;
            color: rgb(218, 119, 86);
        }

        .exit-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Modal game styles */
        .game-modal .modal-dialog {
            max-width: 90%;
            height: 90%;
            margin: 2% auto;
        }

        .game-modal .modal-content {
            height: 100%;
            border-radius: 20px;
            overflow: hidden;
        }

        .game-modal .modal-body {
            display: flex;
            flex-direction: column;
            padding: 20px;
            height: 100%;
            overflow: hidden;
        }

        .game-modal .game-area {
            flex: 1;
            min-height: 0;
            margin-bottom: 15px;
        }

        /* Tic Tac Toe specific styles */
        .tictactoe-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            grid-gap: 10px;
            background-color: #333;
            padding: 10px;
            border-radius: 8px;
            max-width: 350px;
            margin: 0 auto;
        }

        .tictactoe-cell {
            aspect-ratio: 1;
            background-color: #f2f2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Typing game styles */
        .car-racing {
            position: relative;
            width: 100%;
            height: 300px;
            background-color: #333;
            overflow: hidden;
            border-radius: 8px;
        }

        .racing-lane {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .player-car,
        .opponent-car {
            position: absolute;
            width: 60px;
            height: 40px;
            background-size: contain;
            background-repeat: no-repeat;
            transition: left 0.5s linear;
        }

        .player-car {
            bottom: 50px;
            left: 10%;
            background-color: #da7756;
            border-radius: 5px;
        }

        .opponent-car {
            bottom: 100px;
            right: 70%;
            background-color: #4285F4;
            border-radius: 5px;
        }

        .typing-text {
            position: absolute;
            bottom: 10px;
            left: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 0 0 8px 8px;
        }

        .typing-input {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }

        .typing-prompt {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .highlight {
            background-color: #ffc107;
        }

        .typing-wrong {
            background-color: #ff3b30;
            color: white;
        }

        /* Blink animation for countdown */
        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        /* Tambahan style untuk Tic Tac Toe */
        .tictactoe-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 5px;
            background-color: #333;
            padding: 10px;
            border-radius: 8px;
            max-width: 300px;
            margin: 0 auto;
        }

        .tictactoe-cell {
            background-color: #f2f2f7;
            aspect-ratio: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
        }

        /* Style untuk game mengetik (pengganti 2048) */
        .typing-game {
            position: relative;
            background-color: #333;
            border-radius: 8px;
            overflow: hidden;
            height: 300px;
        }

        .racing-track {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .player-car,
        .opponent-car {
            position: absolute;
            width: 60px;
            height: 30px;
            background-color: #da7756;
            border-radius: 5px;
            transition: left 0.3s linear;
        }

        .player-car {
            bottom: 40px;
            left: 10%;
        }

        .opponent-car {
            bottom: 100px;
            right: 70%;
            background-color: #4285F4;
        }

        .typing-text {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 10px;
        }

        .typing-prompt {
            margin-bottom: 10px;
            font-size: 15px;
            line-height: 1.5;
        }

        .typing-input {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .highlight {
            background-color: #ffc107;
        }

        .typed-correct {
            color: #28a745;
        }

        .typed-wrong {
            color: #dc3545;
            background-color: rgba(255, 0, 0, 0.1);
        }

        /* Modal game styles */
        .game-modal .modal-dialog {
            max-width: 700px;
            margin: 30px auto;
        }

        .game-modal .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        .game-modal .modal-body {
            padding: 20px;
        }

        .game-modal .game-area {
            margin-bottom: 20px;
        }

        .btn-close:focus {
            box-shadow: none;
        }

        /* Adjust Flappy Bird styling */
        .flappy-container {
            width: 100%;
            height: 300px;
            position: relative;
            overflow: hidden;
        }

        /* Memory card flip animation - Update dari sebelumnya */
        .memory-card {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
            cursor: pointer;
            margin: 5px;
            /* Kurangi margin */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            /* Shadow lebih subtle */
        }

        .memory-card.flipped {
            transform: rotateY(180deg);
        }

        .memory-card .front,
        .memory-card .back {
            width: 100%;
            height: 100%;
            position: absolute;
            backface-visibility: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
        }

        .memory-card .front {
            background-color: #da7756;
        }

        .memory-card .back {
            background-color: #e9ecef;
            transform: rotateY(180deg);
            font-size: 28px;
        }

        .memory-card.matched .front {
            background-color: #4caf50;
            opacity: 0.8;
        }

        .memory-card.matched .back {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        /* Tambahkan untuk container */
        .memory-board-container {
            overflow: visible;
            padding: 5px;
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            /* Center kartu dalam modal */
        }

        /* Style untuk tombol tingkat kesulitan */
        .difficulty-select {
            transition: all 0.3s;
            min-width: 100px;
        }

        .difficulty-select:hover {
            transform: scale(1.05);
        }

        .difficulty-info {
            color: #6c757d;
            font-size: 14px;
        }

        #breakoutModal .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }

        #breakoutModal .game-area {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <!-- Modal for exam completion confirmation -->
    <div class="modal fade" id="examCompletionModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: rgb(218, 119, 86);"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Ujian Telah Selesai</h4>
                    <p class="text-muted">Terima kasih sudah mengerjakan ujian dengan baik, ujianmu sudah tersimpan.</p>

                    <div class="alert border mt-3 bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-device-gamepad-2 fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div class="text-start">
                                <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Santai Namun Tetap Tenang</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Kamu dapat bersantai dengan bermain melalui game kami yang telah tersedia atau keluar dengan menekan tombol keluar di kanan bawah layar</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex border-0">
                        <button type="button" class="btn flex-fill text-center mt-3" data-bs-dismiss="modal"
                            style="background-color: rgb(218, 119, 86); color: white; border-radius: 12px; padding: 10px 30px;">
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show modal when page loads
            const examCompletionModal = new bootstrap.Modal(document.getElementById('examCompletionModal'));
            examCompletionModal.show();
        });
    </script>


    <div id="warningOverlay" class="warning-active"></div>

    <!-- Navbar with student info -->
    <nav class="navbar sticky-top" style="background-color: rgba(255, 255, 255, 0.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0, 0, 0, 0.1); padding: 12px 0;">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <img src="<?php echo !empty($siswa['photo_url']) ?
                                ($siswa['photo_type'] === 'avatar' ? $siswa['photo_url'] : ($siswa['photo_type'] === 'upload' ? $siswa['photo_url'] : 'assets/pp.png'))
                                : 'assets/pp.png'; ?>"
                    class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                <div>
                    <h6 class="mb-0 fw-bold" style="color: #1c1c1e; font-size: 15px;"><?php echo $_SESSION['nama']; ?></h6>
                    <span class="badge" style="background: rgba(218, 119, 86, 0.15); color: rgb(218, 119, 86); font-weight: 500; font-size: 12px;">
                        <?php echo $data_ujian['judul']; ?> - Selesai
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock" style="color:rgb(218, 119, 86);"></i>
                    <span id="countdown" style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;">00:00:00</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border p-4 mb-4" style="border-radius: 16px;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: rgb(218, 119, 86);"></i>
                        <div>
                            <p class="p-0 m-0 fw-bold">Ujian Telah Selesai</p>
                            <p class="p-0 m-0">Ini adalah halaman kamu telah sukses mengirim ujianmu ke database, kamu bisa bermain game yang telah kami sediakan atau keluar dari halaman ini.</p>
                        </div>
                    </div>
                </div>

                <!-- AI Analysis Section -->
                <div class="card border" style="border-radius: 16px; margin-bottom:5rem" id="aiAnalysisCard">
                    <div class="card-body p-0 m-0" id="aiAnalysisContent">
                        <!-- Blur Overlay untuk Loading -->
                        <div class="ai-blur-overlay" id="aiBlurOverlay">
                            <div class="ai-enhanced-loader" id="aiLoader">
                                <img src="assets/ai_card.gif" alt="AI Loading" onerror="handleLoaderError(this)">
                            </div>
                            <h6 class="fw-bold mt-3 mb-2" style="color: rgb(218, 119, 86);">AI sedang menganalisis...</h6>
                            <p class="text-muted text-center mb-0" style="font-size: 14px;">Tunggu sebentar, ini tidak akan lama</p>
                        </div>

                        <!-- Initial State -->
                        <div id="aiInitialState" class="" style="position: relative; overflow: hidden;">
                            <div style="position: absolute; right: -20px; bottom: -30px; opacity: 0.7;">
                                <i class="ti ti-sparkles" style="font-size: 180px; color: rgb(218, 119, 86);"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="fw-bold mb-2 " style="font-size: 1.5rem;">Pengen Tau Hasil Ujianmu Barusan?</h5>
                                <p class=" mb-4 opacity-75">Dapatkan analisis SAGA tentang materi yang perlu dipelajari lagi dan yang sudah dikuasai</p>
                                <button class="btn btn-light border px-4 py-2" id="startAnalysisBtn" style="border-radius: 12px; font-weight: 500;">
                                    Analisis Sekarang
                                </button>
                            </div class="p-4">
                        </div>

                        <!-- Loading State -->
                        <div id="aiLoadingState" class="text-center d-none">
                            <div class="mb-3">
                                <div class="spinner-border" style="color: rgb(218, 119, 86);" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <h6 class="fw-bold mb-2">SAGA sedang menganalisis hasil ujianmu...</h6>
                            <p class="text-muted mb-0">Tunggu sebentar ya, ini tidak akan lama</p>
                        </div>

                        <!-- Analysis Result State -->
                        <!-- Analysis Result State -->
                        <div id="aiResultState" class="d-none p-4">
                            <div class="ai-content-container">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="p-2 rounded-circle" style="background: rgba(218, 119, 86, 0.1);">
                                        <i class="ti ti-sparkles" style="font-size: 1.5rem; color: rgb(218, 119, 86);"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Analisis SAGA untuk <?php echo $_SESSION['nama']; ?></h6>
                                        <small class="text-muted">Berdasarkan hasil ujian <?php echo $data_ujian['judul']; ?></small>
                                    </div>
                                </div>

                                <div class="ai-analysis-content border mb-4" id="analysisContent">
                                    <!-- Analysis content will be loaded here -->
                                </div>

                                <!-- Chat Section -->
                                <div class="pt-4 ai-chat-section">
                                    <h6 class="fw-bold mb-3">Punya pertanyaan tentang materi?</h6>

                                    <!-- Chat Messages -->
                                    <div class="chat-messages" id="chatMessages" style="max-height: 450px; overflow-y: auto;">
                                        <!-- Chat messages will appear here -->
                                    </div>

                                    <!-- Quick Questions -->
                                    <div class="mb-3">
                                        <div class="d-flex flex-wrap gap-2" id="quickQuestions">
                                            <!-- Quick questions will be generated based on exam topic -->
                                        </div>
                                    </div>

                                    <!-- Chat Input -->
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control" id="chatInput" placeholder="Tanya tentang materi yang belum dipahami..." style="border-radius: 20px;">
                                        <button class="btn" id="sendChatBtn" style="background-color: rgb(218, 119, 86); color: white; border-radius: 50%; width: 40px; height: 40px; padding: 0;">
                                            <i class="ti ti-send"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .chat-messages {
                        margin-bottom: 1rem;
                        display: none;
                        /* Sembunyikan di awal */
                        opacity: 0;
                        max-height: 0;
                        overflow: hidden;
                        transition: all 0.3s ease-in-out;
                    }

                    .chat-messages.active {
                        display: block;
                        opacity: 1;
                        max-height: 450px;
                        overflow-y: auto;
                    }

                    /* Animasi expand yang smooth */
                    .chat-messages.expanding {
                        display: block;
                        opacity: 1;
                        max-height: 450px;
                        overflow-y: auto;
                    }

                    /* AI Analysis Styles */
                    /* AI Analysis Styles - Enhanced */
                    #aiAnalysisCard {
                        background: radial-gradient(circle at bottom right,
                                rgba(218, 119, 86, 0.1) 0%,
                                rgba(255, 255, 255, 1) 70%);
                        position: relative;
                        overflow: hidden;
                        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                    }

                    /* Blur Overlay untuk Loading State */
                    .ai-blur-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255, 255, 255, 0.9);
                        backdrop-filter: blur(10px);
                        -webkit-backdrop-filter: blur(10px);
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        z-index: 20;
                        opacity: 0;
                        visibility: hidden;
                        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                        border-radius: 16px;
                    }

                    .ai-blur-overlay.active {
                        opacity: 1;
                        visibility: visible;
                    }

                    /* AI Loader dengan GIF */
                    .ai-enhanced-loader {
                        position: relative;
                        width: 80px;
                        height: 80px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }

                    .ai-enhanced-loader img {
                        width: 100%;
                        height: 100%;
                        object-fit: contain;
                        border-radius: 12px;
                    }

                    /* Fallback animation jika gif tidak load */
                    .ai-enhanced-loader.fallback {
                        width: 60px;
                        height: 60px;
                    }

                    .ai-enhanced-loader.fallback::before {
                        content: '';
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        border: 4px solid rgba(218, 119, 86, 0.2);
                        border-top: 4px solid rgb(218, 119, 86);
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }

                    @keyframes spin {
                        0% {
                            transform: rotate(0deg);
                        }

                        100% {
                            transform: rotate(360deg);
                        }
                    }

                    /* Card Expansion Animation */
                    #aiAnalysisCard.expanding {
                        min-height: 600px;
                        transition: min-height 1s cubic-bezier(0.4, 0, 0.2, 1);
                    }

                    #aiAnalysisCard.analysis-complete {
                        box-shadow: 0 12px 40px rgba(218, 119, 86, 0.2);
                        border: 1px solid rgba(255, 255, 255, 0.2);
                    }

                    /* Smooth Fade Down Animation untuk Content */
                    .ai-content-container {
                        opacity: 0;
                        transform: translateY(-30px);
                        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                    }

                    .ai-content-container.fade-in {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    /* Enhanced Analysis Content */
                    .ai-analysis-content {
                        backdrop-filter: blur(10px);
                        padding: 25px;
                        border-radius: 16px;
                        line-height: 1.7;
                        opacity: 0;
                        transform: translateY(20px);
                        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                    }

                    .ai-analysis-content.visible {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    /* Enhanced Chat Section */
                    .ai-chat-section {
                        opacity: 0;
                        transform: translateY(30px);
                        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                        transition-delay: 0.3s;
                    }

                    .ai-chat-section.visible {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    #aiAnalysisCard::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        left: -50%;
                        width: 200%;
                        height: 200%;
                        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                        animation: shimmer 3s infinite;
                        pointer-events: none;
                    }

                    @keyframes shimmer {
                        0% {
                            transform: translateX(-100%) translateY(-100%) rotate(45deg);
                        }

                        100% {
                            transform: translateX(100%) translateY(100%) rotate(45deg);
                        }
                    }

                    #aiAnalysisCard.analyzing {
                        background: white;
                        border: 1px solid rgba(0, 0, 0, 0.1);
                    }

                    #aiAnalysisCard.analyzing::before {
                        display: none;
                    }

                    .chat-message {
                        margin-bottom: 1rem;
                    }

                    .chat-message.user {
                        text-align: right;
                    }

                    .chat-message.ai {
                        text-align: left;
                    }

                    .chat-bubble {
                        display: inline-block;
                        padding: 8px 16px;
                        border-radius: 18px;
                        max-width: 80%;
                        word-wrap: break-word;
                    }

                    .chat-bubble.user {
                        background-color: rgb(218, 119, 86);
                        color: white;
                    }

                    .chat-bubble.ai {
                        background-color: #f1f3f5;
                        color: #333;
                    }

                    .quick-question-btn {
                        background: rgba(218, 119, 86, 0.1);
                        color: rgb(218, 119, 86);
                        border: 1px solid rgba(218, 119, 86, 0.3);
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 14px;
                        cursor: pointer;
                        transition: all 0.2s;
                    }

                    .quick-question-btn:hover {
                        background: rgb(218, 119, 86);
                        color: white;
                    }

                    .ai-analysis-content {
                        padding: 20px;
                        border-radius: 12px;
                        line-height: 1.6;
                    }

                    #sparkIcon {
                        animation: sparkle 2s ease-in-out infinite;
                    }

                    @keyframes sparkle {

                        0%,
                        100% {
                            transform: scale(1) rotate(0deg);
                            opacity: 1;
                        }

                        50% {
                            transform: scale(1.1) rotate(5deg);
                            opacity: 0.8;
                        }
                    }

                    /* Typing cursor animation */
                    .typing-cursor {
                        animation: blink 1s infinite;
                        color: rgb(218, 119, 86);
                        font-weight: bold;
                    }

                    @keyframes blink {

                        0%,
                        50% {
                            opacity: 1;
                        }

                        51%,
                        100% {
                            opacity: 0;
                        }
                    }

                    /* Card expansion animation */
                    #aiAnalysisCard {
                        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                        overflow: hidden;
                    }

                    #aiAnalysisCard.analysis-complete {
                        box-shadow: 0 8px 25px rgba(218, 119, 86, 0.15);
                    }

                    /* Smooth content reveal */
                    .ai-analysis-content {
                        opacity: 0;
                        animation: fadeInUp 0.8s ease forwards;
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

                    /* Chat section smooth reveal */
                    .border-top {
                        opacity: 0;
                        animation: fadeIn 1s ease forwards;
                        animation-delay: 0.5s;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                        }

                        to {
                            opacity: 1;
                        }
                    }

                    #aiBlurOverlay h6 {
                        transition: opacity 0.3s ease-in-out;
                    }
                </style>

                <script>
                    let chatStarted = false;
                    let examData = null;
                    let isAnalyzing = false;

                    // Handle error jika gif tidak bisa dimuat
                    function handleLoaderError(img) {
                        console.log('GIF loader gagal dimuat, menggunakan fallback animation');
                        const loaderContainer = img.parentElement;

                        // Hapus img dan gunakan CSS animation sebagai fallback
                        img.remove();
                        loaderContainer.classList.add('fallback');
                    }

                    // Preload gif untuk memastikan smooth loading
                    function preloadAIGif() {
                        const img = new Image();
                        img.src = 'assets/ai_card.gif';

                        img.onload = function() {
                            console.log('AI GIF berhasil dimuat');
                        };

                        img.onerror = function() {
                            console.log('AI GIF gagal dimuat, akan menggunakan fallback');
                        };
                    }

                    // Animated loading text
                    function animateLoadingText() {
                        const loadingTexts = [
                            'SAGA sedang menganalisis hasil ujianmu',
                            'SAGA pun bingung dengan jawabanmu',
                            'Menyiapkan rekomendasi',
                            'Hampir selesai',
                            'Sedikit lagi, sabar ya',
                        ];

                        let currentIndex = 0;
                        const textElement = document.querySelector('#aiBlurOverlay h6');

                        if (!textElement) return;

                        const interval = setInterval(() => {
                            if (!document.querySelector('#aiBlurOverlay.active')) {
                                clearInterval(interval);
                                return;
                            }

                            textElement.style.opacity = '0';

                            setTimeout(() => {
                                currentIndex = (currentIndex + 1) % loadingTexts.length;
                                textElement.textContent = loadingTexts[currentIndex];
                                textElement.style.opacity = '1';
                            }, 300);

                        }, 2000);
                    }


                    $(document).ready(function() {
                        preloadAIGif();
                        // Start Analysis Button
                        $('#startAnalysisBtn').click(function() {
                            if (isAnalyzing) return;

                            isAnalyzing = true;
                            startAIAnalysis();
                        });

                        // Send Chat Button
                        $('#sendChatBtn').click(function() {
                            sendChatMessage();
                        });

                        // Chat Input Enter Key
                        $('#chatInput').keypress(function(e) {
                            if (e.which === 13) {
                                sendChatMessage();
                            }

                            // sembunyikan quick questions saat mengetik
                            $('.quick-question-btn').fadeOut(300);
                        });

                        // Quick Questions Click
                        $(document).on('click', '.quick-question-btn', function() {
                            const question = $(this).text();
                            $('#chatInput').val(question);
                            sendChatMessage();

                            // Sembunyikan quick questions setelah digunakan
                            $(this).fadeOut(300);
                        });
                    });

                    // Tambahkan function ini di bagian script
                    function debugElements() {
                        console.log('=== DEBUG ELEMENTS ===');
                        console.log('aiInitialState exists:', $('#aiInitialState').length > 0);
                        console.log('aiLoadingState exists:', $('#aiLoadingState').length > 0);
                        console.log('aiResultState exists:', $('#aiResultState').length > 0);
                        console.log('analysisContent exists:', $('#analysisContent').length > 0);

                        console.log('aiInitialState visible:', $('#aiInitialState').is(':visible'));
                        console.log('aiLoadingState visible:', $('#aiLoadingState').is(':visible'));
                        console.log('aiResultState visible:', $('#aiResultState').is(':visible'));

                        console.log('aiResultState classes:', $('#aiResultState').attr('class'));
                        console.log('analysisContent HTML:', $('#analysisContent').html());
                    }

                    function startAIAnalysis() {
                        console.log('=== START AI ANALYSIS ===');

                        // Step 1: Hide initial state dengan fade out
                        $('#aiInitialState').fadeOut(300, function() {
                            // Step 2: Show blur overlay dengan fade in
                            $('#aiBlurOverlay').addClass('active');

                            animateLoadingText();

                            // Step 3: Start card expansion
                            $('#aiAnalysisCard').addClass('expanding analyzing');

                            // Step 4: Setelah expansion selesai, mulai fetch data
                            setTimeout(function() {
                                fetchAnalysisData();
                            }, 800); // Tunggu expansion selesai
                        });
                    }

                    function fetchAnalysisData() {
                        // Get exam results
                        $.ajax({
                            url: 'get_exam_results.php',
                            method: 'POST',
                            data: {
                                ujian_id: '<?php echo $ujian_id; ?>'
                            },
                            dataType: 'json',
                            success: function(response) {
                                console.log('Exam results response:', response);
                                if (response.success) {
                                    examData = response.data;

                                    // Get AI analysis dengan error handling yang lebih baik
                                    $.ajax({
                                        url: 'ai_analysis_ujian.php',
                                        method: 'POST',
                                        data: JSON.stringify({
                                            exam_data: examData
                                        }),
                                        contentType: 'application/json',
                                        dataType: 'json',
                                        timeout: 30000, // 30 detik timeout
                                        beforeSend: function(xhr) {
                                            // Tambah header tambahan jika diperlukan
                                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                                        },
                                        success: function(response) {
                                            console.log('AI analysis response:', response);
                                            if (response.success) {
                                                showAnalysisResult(response.analysis);
                                            } else {
                                                if (response.redirect) {
                                                    alert('Session expired, halaman akan dialihkan');
                                                    window.location.href = response.redirect;
                                                } else {
                                                    showError('Gagal mendapatkan analisis: ' + response.error);
                                                }
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('AI Analysis Error:', {
                                                status: xhr.status,
                                                statusText: xhr.statusText,
                                                responseText: xhr.responseText,
                                                error: error
                                            });

                                            if (xhr.status === 401) {
                                                alert('Session expired, halaman akan dialihkan');
                                                window.location.href = 'index.php';
                                            } else if (xhr.status === 405) {
                                                showError('Method tidak diperbolehkan - ada masalah dengan request');
                                            } else {
                                                showError('Terjadi kesalahan saat menganalisis: ' + error);
                                            }
                                        }
                                    });
                                } else {
                                    if (response.redirect) {
                                        alert('Session expired, halaman akan dialihkan');
                                        window.location.href = response.redirect;
                                    } else {
                                        showError('Gagal mengambil data ujian: ' + response.error);
                                    }
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Get exam results error:', error);
                                if (xhr.status === 401) {
                                    alert('Session expired, halaman akan dialihkan');
                                    window.location.href = 'index.php';
                                } else {
                                    showError('Terjadi kesalahan saat mengambil data ujian');
                                }
                            }
                        });
                    }

                    function showAnalysisResult(analysis) {
                        chatStarted = false;
                        // Generate quick questions
                        const quickQuestions = generateQuickQuestions(examData);
                        $('#quickQuestions').html(quickQuestions.map(q =>
                            `<span class="quick-question-btn">${q}</span>`
                        ).join(''));

                        // Step 1: Fade out blur overlay
                        $('#aiBlurOverlay').removeClass('active');

                        // Step 2: Show result container dengan smooth fade
                        setTimeout(function() {
                            $('#aiResultState').removeClass('d-none');

                            // Step 3: Fade in content container
                            setTimeout(function() {
                                $('.ai-content-container').addClass('fade-in');

                                // Step 4: Start typing effect setelah content muncul
                                setTimeout(function() {
                                    $('.ai-analysis-content').addClass('visible');
                                    typeWriterEffect(analysis, '#analysisContent', 15);
                                }, 400);

                            }, 200);
                        }, 300);
                    }

                    // Function untuk typing animation
                    function typeWriterEffect(text, elementId, speed = 15) {
                        const element = $(elementId);
                        const formattedText = formatAnalysisText(text);

                        // Clear content first
                        element.html('');

                        let i = 0;
                        let currentHtml = '';
                        let isInTag = false;
                        let tagBuffer = '';

                        function typeChar() {
                            if (i < formattedText.length) {
                                const char = formattedText.charAt(i);

                                if (char === '<') {
                                    isInTag = true;
                                    tagBuffer = '<';
                                } else if (char === '>') {
                                    isInTag = false;
                                    tagBuffer += '>';
                                    currentHtml += tagBuffer;
                                    tagBuffer = '';
                                    element.html(currentHtml + '<span class="typing-cursor">|</span>');
                                } else if (isInTag) {
                                    tagBuffer += char;
                                } else {
                                    currentHtml += char;
                                    element.html(currentHtml + '<span class="typing-cursor">|</span>');
                                }

                                i++;
                                setTimeout(typeChar, speed);
                            } else {
                                // Remove cursor when done
                                element.html(currentHtml);

                                // Show chat section dengan delay
                                setTimeout(function() {
                                    $('.ai-chat-section').addClass('visible');
                                    $('#aiAnalysisCard').addClass('analysis-complete');
                                }, 500);
                            }
                        }

                        typeChar();
                    }

                    // Function untuk expand card dengan smooth animation
                    function expandCard() {
                        $('#aiAnalysisCard').animate({
                            'min-height': '+=50px'
                        }, 500, function() {
                            // Animation complete, maybe add some bounce effect
                            $(this).addClass('analysis-complete');
                        });
                    }

                    function generateQuickQuestions(examData) {
                        const subject = examData.ujian.mata_pelajaran;
                        const title = examData.ujian.judul;

                        const baseQuestions = [
                            `Bisa jelaskan lagi tentang ${subject}?`,
                            'Apa tips belajar yang efektif?',
                            'Materi mana yang paling penting?',
                            'Bagaimana cara mengingat materi dengan baik?'
                        ];

                        return baseQuestions;
                    }

                    function formatAnalysisText(text) {
                        // Format text with better styling
                        return text.replace(/\n/g, '<br>')
                            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\*(.*?)\*/g, '<em>$1</em>');
                    }

                    function sendChatMessage() {
                        const message = $('#chatInput').val().trim();
                        if (!message) return;

                        // Tampilkan chat container jika belum ditampilkan
                        if (!chatStarted) {
                            showChatContainer();
                            chatStarted = true;
                        }

                        // Add user message to chat
                        addChatMessage(message, 'user');
                        $('#chatInput').val('');

                        // Show typing indicator
                        const typingId = 'typing-' + Date.now();
                        addTypingIndicator(typingId);

                        // Send to AI
                        $.ajax({
                            url: 'ai_chat_ujian.php',
                            method: 'POST',
                            data: JSON.stringify({
                                message: message,
                                exam_context: examData
                            }),
                            contentType: 'application/json',
                            success: function(response) {
                                removeTypingIndicator(typingId);

                                if (response.success) {
                                    addChatMessage(response.response, 'ai', true);
                                } else {
                                    addChatMessage('Maaf, terjadi kesalahan: ' + response.error, 'ai');
                                }
                            },
                            error: function() {
                                removeTypingIndicator(typingId);
                                addChatMessage('Maaf, koneksi bermasalah. Coba lagi ya!', 'ai');
                            }
                        });
                    }

                    function showChatContainer() {
                        const chatContainer = $('#chatMessages');

                        // Tambahkan class expanding dengan animasi smooth
                        chatContainer.addClass('expanding');

                        // Setelah animasi selesai, tambahkan class active
                        setTimeout(() => {
                            chatContainer.addClass('active');
                        }, 50);
                    }

                    function addChatMessage(message, sender, useTyping = false) {
                        if (sender === 'ai' && useTyping) {
                            // Untuk AI dengan typing animation
                            addAIMessageWithTyping(message);
                        } else {
                            // Untuk user atau AI tanpa typing
                            const messageHtml = `
            <div class="chat-message ${sender}">
                <div class="chat-bubble ${sender}">
                    ${formatAnalysisText(message)}
                </div>
                <small class="text-muted d-block mt-1">${new Date().toLocaleTimeString()}</small>
            </div>
        `;

                            $('#chatMessages').append(messageHtml);
                            $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                        }
                    }

                    function addAIMessageWithTyping(message) {
                        // Buat message container
                        const messageId = 'ai-msg-' + Date.now();
                        const messageHtml = `
        <div class="chat-message ai" id="${messageId}">
            <div class="chat-bubble ai typing">
                <span class="ai-message-content"></span>
                <span class="ai-message-typing"></span>
            </div>
            <small class="text-muted d-block mt-1">${new Date().toLocaleTimeString()}</small>
        </div>
    `;

                        $('#chatMessages').append(messageHtml);
                        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);

                        // Mulai typing animation
                        startTypingAnimation(messageId, message);
                    }

                    function startTypingAnimation(messageId, text) {
                        const messageElement = $(`#${messageId} .ai-message-content`);
                        const typingIndicator = $(`#${messageId} .ai-message-typing`);
                        const formattedText = formatAnalysisText(text);

                        let i = 0;
                        let currentHtml = '';
                        let isInTag = false;
                        let tagBuffer = '';
                        const speed = 15; // Kecepatan typing (ms per karakter)

                        function typeChar() {
                            if (i < formattedText.length) {
                                const char = formattedText.charAt(i);

                                if (char === '<') {
                                    isInTag = true;
                                    tagBuffer = '<';
                                } else if (char === '>') {
                                    isInTag = false;
                                    tagBuffer += '>';
                                    currentHtml += tagBuffer;
                                    tagBuffer = '';
                                    messageElement.html(currentHtml);
                                } else if (isInTag) {
                                    tagBuffer += char;
                                } else {
                                    currentHtml += char;
                                    messageElement.html(currentHtml);

                                    // Auto scroll saat mengetik
                                    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                                }

                                i++;
                                setTimeout(typeChar, speed);
                            } else {
                                // Typing selesai, hapus cursor
                                typingIndicator.remove();
                                $(`#${messageId} .chat-bubble`).removeClass('typing');
                            }
                        }

                        // Mulai typing
                        typeChar();
                    }

                    function addTypingIndicator(id) {
                        const typingHtml = `
        <div class="chat-message ai" id="${id}">
            <div class="chat-bubble ai">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    `;

                        $('#chatMessages').append(typingHtml);
                        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                    }

                    function removeTypingIndicator(id) {
                        $(`#${id}`).remove();
                    }

                    function showError(message) {
                        $('#aiLoadingState').html(`
        <div class="text-center">
            <div class="mb-3">
                <i class="ti ti-exclamation-circle" style="font-size: 3rem; color: #dc3545;"></i>
            </div>
            <h6 class="fw-bold mb-2 text-danger">Oops, ada masalah!</h6>
            <p class="text-muted mb-3">${message}</p>
            <button class="btn btn-outline-primary" onclick="location.reload()">Coba Lagi</button>
        </div>
    `);

                        isAnalyzing = false;
                    }
                </script>

                <style>
                    .typing-indicator {
                        display: flex;
                        gap: 4px;
                        align-items: center;
                    }

                    .typing-indicator span {
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background-color: #999;
                        animation: typing 1.4s infinite ease-in-out;
                    }

                    .typing-indicator span:nth-child(1) {
                        animation-delay: -0.32s;
                    }

                    .typing-indicator span:nth-child(2) {
                        animation-delay: -0.16s;
                    }

                    @keyframes typing {

                        0%,
                        80%,
                        100% {
                            transform: scale(0.8);
                            opacity: 0.5;
                        }

                        40% {
                            transform: scale(1);
                            opacity: 1;
                        }
                    }

                    /* Tambahkan ini di bagian <style> untuk debugging */
                    /* #aiResultState.debug-visible {
                        display: block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    }

                    #analysisContent.debug-visible {
                        display: block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    } */
                </style>

                <h4 class="fw-bold mb-4 text-center">Mini Games</h4>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 1: Snake</h5>
                            <div class="game-area" id="snake-game">
                                <img src="assets/game1_snake.png" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game flex-fill" id="startSnake">Buka Game</button>
                                <div class="d-none">Score: <span id="snakeScore">0</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 2: Memory Cards</h5>
                            <div class="game-area" id="memory-game">
                                <img src="assets/game2_memory.png" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game flex-fill" id="startMemory">Mulai Game</button>
                                <div class="d-none">Matches: <span id="memoryScore">0</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 3: Tic Tac Toe</h5>
                            <div class="game-area" id="tictactoe-game"></div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game" id="startTicTacToe">Mulai Game</button>
                                <div>Skor: <span id="ticTacToeScore">0</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 4: Typing Race</h5>
                            <div class="game-area" id="typing-game"></div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game" id="startTyping">Mulai Game</button>
                                <div>WPM: <span id="scoreTyping">0</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 5: Flappy Bird</h5>
                            <div class="game-area" id="flappy-game">
                                <canvas id="flappyCanvas" style="background-color: #70c5ce; border-radius: 8px;"></canvas>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game" id="startFlappy">Mulai Game</button>
                                <div>Skor: <span id="flappyScore">0</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="game-container">
                            <h5 class="game-title">Game 6: Breakout</h5>
                            <div class="game-area" id="breakout-game">
                                <img src="assets/game6_breakout.png" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button class="btn-game flex-fill" id="startBreakout">Mulai Game</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exit button with password protection -->
    <button class="btn exit-button color-web text-white" id="exitButton" data-bs-toggle="modal" data-bs-target="#exitConfirmModal">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </button>


    <!-- Simple exit confirmation modal -->
    <div class="modal fade" id="exitConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.1);">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: #da7756;"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Konfirmasi Keluar</h5>
                    <p class="text-secondary mb-4" style="font-size: 0.95rem;">Apakah Anda yakin ingin kembali ke halaman ujian?</p>
                    <div class="d-flex gap-2">
                        <button class="btn flex-fill" data-bs-dismiss="modal" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.1);">
                            Batal
                        </button>
                        <button class="btn flex-fill" onclick="window.location.href='ujian.php'" style="border-radius: 12px; background-color: rgb(206, 100, 65); color: white; font-weight: 500;">
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <script>
        // Setup countdown timer
        function setupCountdown() {
            // Get end time from PHP
            const endTime = new Date('<?php echo $tanggal_selesai; ?>').getTime();

            // Update countdown every second
            const timer = setInterval(function() {
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance <= 0) {
                    // Time is up, redirect to ujian.php
                    clearInterval(timer);
                    document.getElementById('countdown').innerHTML = "00:00:00";
                    document.getElementById('main-countdown').innerHTML = "00:00:00";

                    // Show a message before redirecting
                    alert("Waktu ujian telah berakhir. Anda akan dikembalikan ke halaman ujian.");
                    window.location.href = 'ujian.php';
                    return;
                }

                // Calculate hours, minutes, seconds
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Format countdown display
                const countdownStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                document.getElementById('countdown').innerHTML = countdownStr;
                document.getElementById('main-countdown').innerHTML = countdownStr;

                // Update countdown style if time is running out
                if (minutes <= 5) {
                    document.getElementById('main-countdown').style.color = '#dc3545';
                    if (minutes <= 2) {
                        document.getElementById('main-countdown').style.animation = 'blink 1s infinite';
                    }
                }
            }, 1000);
        }
    </script>

    <script>
        // Hapus semua kode yang di-comment dan gunakan struktur yang benar

        // Variables for game functions
        let snakeGameLoop, memoryGameRunning, pingPongGameLoop, typingGameActive, flappyGameLoop, breakoutGameLoop;

        document.addEventListener('DOMContentLoaded', function() {
            // Set up countdown timer
            setupCountdown();

            // Set up game button handlers (LANGSUNG DEFINISIKAN DI SINI)
            setupGameButtons();
        });

        // DEFINISIKAN FUNGSI setupGameButtons
        function setupGameButtons() {
            // Snake Game
            document.getElementById('startSnake').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('snakeModal'));
                modal.show();
            });

            // Memory Game
            document.getElementById('startMemory').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('memoryModal'));
                modal.show();
            });

            // Tic Tac Toe
            document.getElementById('startTicTacToe').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('pingPongModal'));
                modal.show();
            });

            // Typing Game
            document.getElementById('startTyping').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('typingModal'));
                modal.show();
            });

            // Flappy Bird
            document.getElementById('startFlappy').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('flappyModal'));
                modal.show();
            });

            // Breakout game
            document.getElementById('startBreakout').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('breakoutModal'));
                modal.show();
            });

            // Inisialisasi game di modal
            initSnakeGameModal();
            initMemoryGameModal();
            initPingPongGameModal();
            initTypingGameModal();
            initFlappyBirdGameModal();
            initBreakoutGameModal();
        }


        // Fungsi untuk menyiapkan tombol game
        document.addEventListener('DOMContentLoaded', function() {
            // Snake Game
            document.getElementById('startSnake').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('snakeModal'));
                modal.show();
            });

            // Memory Game
            document.getElementById('startMemory').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('memoryModal'));
                modal.show();
            });

            // Tic Tac Toe
            document.getElementById('startTicTacToe').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('pingPongModal'));
                modal.show();
            });

            // Typing Game
            document.getElementById('startTyping').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('typingModal'));
                modal.show();
            });

            // Flappy Bird
            document.getElementById('startFlappy').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('flappyModal'));
                modal.show();
            });

            // breakout game
            document.getElementById('startBreakout').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('breakoutModal'));
                modal.show();
            });

            // Inisialisasi game di modal
            initSnakeGameModal();
            initMemoryGameModal();
            initPingPongGameModal();
            initTypingGameModal();
            initFlappyBirdGameModal();
            initBreakoutGameModal();
        });

        // SNAKE GAME MODAL
        function initSnakeGameModal() {
            const canvas = document.getElementById('snakeCanvasModal');
            const ctx = canvas.getContext('2d');
            const startBtn = document.getElementById('startSnakeModal');
            const scoreElement = document.getElementById('snakeScoreModal');

            // Game variables
            let snake = [];
            let food = {};
            let direction = 'right';
            let score = 0;
            let gameRunning = false;
            let gameLoop;
            const gridSize = 20;

            // Set canvas size
            function resizeCanvas() {
                const parentWidth = canvas.parentElement.clientWidth;
                canvas.width = parentWidth - 20;
                canvas.height = 600;
            }

            // Initialize game
            function initGame() {
                resizeCanvas();

                // Reset snake
                snake = [{
                        x: 5,
                        y: 10
                    },
                    {
                        x: 4,
                        y: 10
                    },
                    {
                        x: 3,
                        y: 10
                    }
                ];

                // Generate food
                generateFood();

                // Reset direction and score
                direction = 'right';
                score = 0;
                scoreElement.innerHTML = score;

                // Start game loop
                if (gameLoop) clearInterval(gameLoop);
                gameLoop = setInterval(gameStep, 100);
                gameRunning = true;
            }

            // Generate random food position
            function generateFood() {
                const maxX = Math.floor(canvas.width / gridSize) - 1;
                const maxY = Math.floor(canvas.height / gridSize) - 1;

                food = {
                    x: Math.floor(Math.random() * maxX) + 1,
                    y: Math.floor(Math.random() * maxY) + 1
                };

                // Make sure food doesn't appear on snake
                for (let i = 0; i < snake.length; i++) {
                    if (food.x === snake[i].x && food.y === snake[i].y) {
                        generateFood();
                        break;
                    }
                }
            }

            // Game step function
            function gameStep() {
                // Move snake
                const head = {
                    x: snake[0].x,
                    y: snake[0].y
                };

                switch (direction) {
                    case 'up':
                        head.y--;
                        break;
                    case 'down':
                        head.y++;
                        break;
                    case 'left':
                        head.x--;
                        break;
                    case 'right':
                        head.x++;
                        break;
                }

                // Check wall collision
                if (head.x < 0 || head.y < 0 || head.x >= canvas.width / gridSize || head.y >= canvas.height / gridSize) {
                    gameOver();
                    return;
                }

                // Check self collision
                for (let i = 0; i < snake.length; i++) {
                    if (head.x === snake[i].x && head.y === snake[i].y) {
                        gameOver();
                        return;
                    }
                }

                // Check food collision
                if (head.x === food.x && head.y === food.y) {
                    snake.unshift(head);
                    generateFood();
                    score += 10;
                    scoreElement.innerHTML = score;
                } else {
                    snake.unshift(head);
                    snake.pop();
                }

                // Draw game
                drawGame();
            }

            // Draw game function
            function drawGame() {
                // Clear canvas
                ctx.fillStyle = '#111';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Draw snake
                snake.forEach((segment, index) => {
                    ctx.fillStyle = index === 0 ? '#da7756' : '#e9a395';
                    ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize - 2, gridSize - 2);
                });

                // Draw food
                ctx.fillStyle = '#4CAF50';
                ctx.fillRect(food.x * gridSize, food.y * gridSize, gridSize - 2, gridSize - 2);
            }

            // Game over function
            function gameOver() {
                clearInterval(gameLoop);
                gameRunning = false;

                // Draw game over text
                ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.font = '20px Merriweather';
                ctx.fillStyle = 'white';
                ctx.textAlign = 'center';
                ctx.fillText('Game Over!', canvas.width / 2, canvas.height / 2);
                ctx.fillText(`Score: ${score}`, canvas.width / 2, canvas.height / 2 + 30);
            }

            // Event listeners
            startBtn.addEventListener('click', initGame);

            // Keyboard control handler
            function handleKeyDown(e) {
                if (!gameRunning) return;

                switch (e.key) {
                    case 'ArrowUp':
                        if (direction !== 'down') direction = 'up';
                        break;
                    case 'ArrowDown':
                        if (direction !== 'up') direction = 'down';
                        break;
                    case 'ArrowLeft':
                        if (direction !== 'right') direction = 'left';
                        break;
                    case 'ArrowRight':
                        if (direction !== 'left') direction = 'right';
                        break;
                }

                // Prevent arrow keys from scrolling the page
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    e.preventDefault();
                }
            }

            // Add/remove event listeners when modal is shown/hidden
            document.getElementById('snakeModal').addEventListener('shown.bs.modal', function() {
                document.addEventListener('keydown', handleKeyDown);
                resizeCanvas();
                drawGame(); // Draw initial state
            });

            document.getElementById('snakeModal').addEventListener('hidden.bs.modal', function() {
                document.removeEventListener('keydown', handleKeyDown);
                if (gameLoop) clearInterval(gameLoop);
                gameRunning = false;
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (document.getElementById('snakeModal').classList.contains('show')) {
                    resizeCanvas();
                    drawGame();
                }
            });

            // Initial drawing
            resizeCanvas();
            drawGame();
        }

        function initMemoryGameModal() {
            const memoryGame = document.getElementById('memory-game-modal');
            const startBtn = document.getElementById('startMemoryModal');
            const scoreElement = document.getElementById('memoryScoreModal');
            const difficultyBtns = document.querySelectorAll('.difficulty-btn');
            const difficultyContainer = document.querySelector('.difficulty-container');

            // Game variables
            let cards = [];
            let flippedCards = [];
            let matchedPairs = 0;
            let score = 0;
            let gameRunning = false;
            let currentDifficulty = null;
            let gameStarted = false;

            // Card symbols (emojis)
            const symbols = ['🍎', '🍌', '🍇', '🍊', '🍓', '🍉', '🚗', '🚕', '🚌', '🚢', '🚁', '⚽', '🏀', '🎸', '🎮', '📱', '💻', '🧸', '🎁', '🍕', '🍦', '🌮', '⌚', '🔑'];

            // Difficulty settings
            const difficultySettings = {
                easy: {
                    pairs: 10, // 16 kartu (lebih banyak dari sebelumnya)
                    columns: 4,
                    rows: 4,
                    cardSize: 60
                },
                medium: {
                    pairs: 18, // 24 kartu (lebih banyak dari sebelumnya)
                    columns: 6,
                    rows: 4,
                    cardSize: 50 // Ukuran kartu lebih kecil karena lebih banyak kartu
                },
                hard: {
                    pairs: 30, // 30 kartu (lebih banyak dari sebelumnya)
                    columns: 6,
                    rows: 5,
                    cardSize: 45 // Ukuran kartu paling kecil untuk muat di layar
                }
            };

            // Show only difficulty selection at start
            function showDifficultySelection() {
                // Clear the game area
                memoryGame.innerHTML = '';
                scoreElement.innerHTML = '0';

                // Hide the restart button until a game is started
                startBtn.style.display = 'none';

                // Show difficulty selection message
                const selectionMsg = document.createElement('div');
                selectionMsg.className = 'text-center mb-3 mt-5';
                selectionMsg.innerHTML = '<h5>Pilih Tingkat Kesulitan</h5><p class="text-muted small">Klik salah satu tingkat kesulitan untuk mulai bermain</p>';
                memoryGame.appendChild(selectionMsg);

                // Show difficulty buttons with icons
                const difficultyContainer = document.createElement('div');
                difficultyContainer.className = 'difficulty-container';
                difficultyContainer.innerHTML = `
                    <div class="d-flex justify-content-center gap-2 my-4">
                        <button class="btn btn-lg border rounded-3 px-4 difficulty-select" data-difficulty="easy" data-info="Santai aja! Cocok buat pemanasan. 5 menit paling juga kelar">
                            <i class="bi bi-emoji-smile me-2"></i>Mudah
                        </button>
                        <button class="btn btn-lg border rounded-3 px-4 difficulty-select" data-difficulty="medium" data-info="Lumayan menantang! Butuh fokus dikit">
                            <i class="bi bi-emoji-neutral me-2"></i>Sedang
                        </button>
                        <button class="btn btn-lg border rounded-3 px-4 difficulty-select" data-difficulty="hard" data-info="Yakin?">
                            <i class="bi bi-emoji-dizzy me-2"></i>Sulit
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <p id="difficulty-info" class="difficulty-info fst-italic">Arahkan kursor ke pilihan untuk info lebih lanjut...</p>
                    </div>
                `;
                memoryGame.appendChild(difficultyContainer);

                // Add event listeners to difficulty buttons
                const difficultySelectBtns = document.querySelectorAll('.difficulty-select');
                const difficultyInfo = document.getElementById('difficulty-info');

                difficultySelectBtns.forEach(btn => {
                    // Click event
                    btn.addEventListener('click', function() {
                        currentDifficulty = this.dataset.difficulty;
                        startGame();
                    });

                    // Hover events for desktop
                    btn.addEventListener('mouseenter', function() {
                        difficultyInfo.textContent = this.dataset.info;
                        difficultyInfo.style.color = this.dataset.difficulty === 'easy' ? '#198754' :
                            this.dataset.difficulty === 'medium' ? '#ffc107' : '#dc3545';
                        difficultyInfo.style.fontWeight = '500';
                        difficultyInfo.style.fontSize = '1.1rem';
                    });

                    btn.addEventListener('mouseleave', function() {
                        difficultyInfo.textContent = 'Arahkan kursor ke pilihan untuk info lebih lanjut...';
                        difficultyInfo.style.color = '#6c757d';
                        difficultyInfo.style.fontWeight = '400';
                        difficultyInfo.style.fontSize = '1.1rem';
                    });

                    // Touch events for mobile
                    btn.addEventListener('touchstart', function() {
                        difficultyInfo.textContent = this.dataset.info;
                        difficultyInfo.style.color = this.dataset.difficulty === 'easy' ? '#198754' :
                            this.dataset.difficulty === 'medium' ? '#ffc107' : '#dc3545';
                        difficultyInfo.style.fontWeight = '500';
                        difficultyInfo.style.fontSize = '1.1rem';
                    });
                });

                // Reset game state
                gameStarted = false;
                gameRunning = false;
                cards = [];
                flippedCards = [];
                matchedPairs = 0;
                score = 0;
            }

            // Start game with selected difficulty
            function startGame() {
                // Show restart button
                startBtn.style.display = 'block';

                // Set game as started
                gameStarted = true;

                // Initialize game with selected difficulty
                initGame();
            }

            // Initialize game
            function initGame() {
                if (!currentDifficulty) {
                    showDifficultySelection();
                    return;
                }

                // Clear the game area
                memoryGame.innerHTML = '';

                // Reset variables
                flippedCards = [];
                matchedPairs = 0;
                score = 0;
                scoreElement.innerHTML = score;

                // Get difficulty settings
                const difficulty = difficultySettings[currentDifficulty];

                // Create cards array with pairs of symbols
                cards = [];
                const cardSymbols = symbols.slice(0, difficulty.pairs);

                // Create pairs
                for (let symbol of cardSymbols) {
                    cards.push({
                        symbol,
                        matched: false
                    });
                    cards.push({
                        symbol,
                        matched: false
                    });
                }

                // Shuffle cards
                cards = shuffleArray(cards);

                // Create the memory game board
                renderMemoryBoard(difficulty.columns, difficulty.rows);

                gameRunning = true;
            }

            // Render the memory game board
            function renderMemoryBoard(columns, rows) {
                // Create a grid container
                const gridContainer = document.createElement('div');
                gridContainer.className = 'memory-board-container';
                gridContainer.style.display = '';
                gridContainer.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
                gridContainer.style.gap = '8px';
                gridContainer.style.padding = '10px';
                gridContainer.style.maxWidth = '100%';
                gridContainer.style.margin = '0 auto';
                gridContainer.style.marginBottom = '20px'; // Add bottom margin to avoid overlap

                // Calculate card size based on columns and container width
                // This ensures cards fit within the modal
                const containerWidth = memoryGame.clientWidth - 40; // Subtract padding
                const cardWidth = Math.floor((containerWidth / columns) - 10); // Subtract gap

                // Create card elements
                cards.forEach((card, index) => {
                    const cardElement = document.createElement('div');
                    cardElement.className = 'memory-card';
                    if (card.matched) cardElement.classList.add('matched');
                    if (flippedCards.includes(index)) cardElement.classList.add('flipped');

                    cardElement.dataset.index = index;

                    // Create front (hidden) side
                    const frontSide = document.createElement('div');
                    frontSide.className = 'front';

                    // Create back (symbol) side
                    const backSide = document.createElement('div');
                    backSide.className = 'back';
                    backSide.textContent = card.symbol;

                    // Add sides to card
                    cardElement.appendChild(frontSide);
                    cardElement.appendChild(backSide);

                    // Set card size dari difficulty settings (bukan dihitung dari container)
                    const cardSize = difficultySettings[currentDifficulty].cardSize;
                    cardElement.style.width = `${cardSize}px`;
                    cardElement.style.height = `${cardSize}px`;

                    // Sesuaikan ukuran font emoji sesuai ukuran kartu
                    backSide.style.fontSize = `${cardSize / 2}px`;

                    // Add click event
                    cardElement.addEventListener('click', () => {
                        // Ignore if already matched or already flipped or too many cards flipped
                        if (!gameRunning || card.matched || flippedCards.includes(index) || flippedCards.length >= 2) return;

                        // Flip card
                        flipCard(index);
                    });

                    gridContainer.appendChild(cardElement);
                });

                memoryGame.appendChild(gridContainer);

                // Add difficulty indicator
                const difficultyIndicator = document.createElement('div');
                difficultyIndicator.className = 'text-center mb-3';

                // Set appropriate difficulty text and color
                let difficultyText, difficultyColor;
                switch (currentDifficulty) {
                    case 'easy':
                        difficultyText = 'Mudah';
                        difficultyColor = 'success';
                        break;
                    case 'medium':
                        difficultyText = 'Sedang';
                        difficultyColor = 'warning';
                        break;
                    case 'hard':
                        difficultyText = 'Sulit';
                        difficultyColor = 'danger';
                        break;
                }

                difficultyIndicator.innerHTML = `<span class="badge bg-${difficultyColor} px-3 py-2">Level: ${difficultyText}</span>`;

                // Insert at the beginning of the memory game element
                memoryGame.insertBefore(difficultyIndicator, memoryGame.firstChild);
            }

            // Flip a card
            function flipCard(index) {
                flippedCards.push(index);

                // Render to show flipped card
                document.querySelector(`.memory-card[data-index="${index}"]`).classList.add('flipped');

                // If two cards are flipped, check for match
                if (flippedCards.length === 2) {
                    setTimeout(() => {
                        checkForMatch();
                    }, 800);
                }
            }

            // Check if flipped cards match
            function checkForMatch() {
                const firstIndex = flippedCards[0];
                const secondIndex = flippedCards[1];

                if (cards[firstIndex].symbol === cards[secondIndex].symbol) {
                    // Match!
                    cards[firstIndex].matched = true;
                    cards[secondIndex].matched = true;

                    // Add matched class to cards
                    const firstCard = document.querySelector(`.memory-card[data-index="${firstIndex}"]`);
                    const secondCard = document.querySelector(`.memory-card[data-index="${secondIndex}"]`);

                    if (firstCard && secondCard) {
                        firstCard.classList.add('matched');
                        secondCard.classList.add('matched');
                    }

                    matchedPairs++;
                    score++;
                    scoreElement.innerHTML = score;

                    // Check if game is complete
                    if (matchedPairs === cards.length / 2) {
                        gameOver();
                    }
                } else {
                    // No match, flip cards back
                    const firstCard = document.querySelector(`.memory-card[data-index="${firstIndex}"]`);
                    const secondCard = document.querySelector(`.memory-card[data-index="${secondIndex}"]`);

                    if (firstCard && secondCard) {
                        setTimeout(() => {
                            firstCard.classList.remove('flipped');
                            secondCard.classList.remove('flipped');
                        }, 500);
                    }
                }

                // Reset flipped cards
                flippedCards = [];
            }

            // Game over
            function gameOver() {
                gameRunning = false;

                // Show game over message
                const gameOverMsg = document.createElement('div');
                gameOverMsg.style.position = 'absolute';
                gameOverMsg.style.top = '0';
                gameOverMsg.style.left = '0';
                gameOverMsg.style.width = '100%';
                gameOverMsg.style.height = '100%';
                gameOverMsg.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                gameOverMsg.style.display = 'flex';
                gameOverMsg.style.flexDirection = 'column';
                gameOverMsg.style.justifyContent = 'center';
                gameOverMsg.style.alignItems = 'center';
                gameOverMsg.style.borderRadius = '8px';
                gameOverMsg.style.zIndex = '10';

                const msgText = document.createElement('div');
                msgText.textContent = 'Semua Pasangan Ditemukan!';
                msgText.style.color = 'white';
                msgText.style.fontSize = '20px';
                msgText.style.fontWeight = 'bold';
                msgText.style.marginBottom = '10px';

                const scoreText = document.createElement('div');
                scoreText.textContent = `Skor: ${score}`;
                scoreText.style.color = 'white';
                scoreText.style.fontSize = '18px';
                scoreText.style.marginBottom = '15px';

                const newGameBtn = document.createElement('button');
                newGameBtn.textContent = 'Level Baru';
                newGameBtn.className = 'btn btn-light';
                newGameBtn.addEventListener('click', function() {
                    gameOverMsg.remove();
                    showDifficultySelection();
                });

                gameOverMsg.appendChild(msgText);
                gameOverMsg.appendChild(scoreText);
                gameOverMsg.appendChild(newGameBtn);
                memoryGame.appendChild(gameOverMsg);
            }

            // Helper function to shuffle array
            function shuffleArray(array) {
                const newArray = [...array];
                for (let i = newArray.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
                }
                return newArray;
            }

            // Event listeners
            startBtn.addEventListener('click', function() {
                showDifficultySelection();
            });

            // Initialize when modal is shown
            document.getElementById('memoryModal').addEventListener('shown.bs.modal', function() {
                // Reset and show difficulty selection
                showDifficultySelection();
            });
        }
        // PING PONG GAME MODAL
        // PING PONG GAME MODAL
        function initPingPongGameModal() {
            const canvas = document.getElementById('pingPongCanvas');
            const ctx = canvas.getContext('2d');
            const startBtn = document.getElementById('startPingPongModal');
            const scoreElement = document.getElementById('pingPongScoreModal');

            // Set canvas size
            function resizeCanvas() {
                canvas.width = canvas.parentElement.clientWidth - 20;
                canvas.height = 400;
            }

            // Game variables
            let ball = {
                x: 0,
                y: 0,
                dx: 5,
                dy: 5,
                radius: 8
            };
            let leftPaddle = {
                x: 10,
                y: 150,
                width: 10,
                height: 80,
                dy: 0
            };
            let rightPaddle = {
                x: 0,
                y: 150,
                width: 10,
                height: 80,
                dy: 0
            };
            let score = 0;
            let gameRunning = false;
            let gameLoop;

            // AI Settings - TAMBAHKAN INI
            const AI_SPEED = 5; // Kecepatan AI (semakin tinggi semakin sulit)
            const AI_REACTION_ZONE = 200; // Jarak dari paddle di mana AI mulai bereaksi

            // Initialize game
            function initGame() {
                resizeCanvas();

                // Reset positions
                ball.x = canvas.width / 2;
                ball.y = canvas.height / 2;
                ball.dx = Math.random() > 0.5 ? 5 : -5; // Random arah bola
                ball.dy = (Math.random() - 0.5) * 10; // Random sudut bola

                rightPaddle.x = canvas.width - 20;
                leftPaddle.y = canvas.height / 2 - 40;
                rightPaddle.y = canvas.height / 2 - 40;

                score = 0;
                scoreElement.textContent = score;

                if (gameLoop) cancelAnimationFrame(gameLoop);
                gameRunning = true;
                gameStep();
            }

            // AI Logic - TAMBAHKAN FUNGSI INI
            function updateAI() {
                // AI hanya bereaksi ketika bola mendekat
                if (ball.x > canvas.width - AI_REACTION_ZONE && ball.dx > 0) {
                    const paddleCenter = rightPaddle.y + rightPaddle.height / 2;
                    const ballY = ball.y;

                    // Hitung perbedaan posisi
                    const diff = ballY - paddleCenter;

                    // Gerakan AI dengan kecepatan terbatas
                    if (Math.abs(diff) > 5) { // Tambah dead zone agar AI tidak terlalu sempurna
                        if (diff > 0) {
                            rightPaddle.y += Math.min(AI_SPEED, diff);
                        } else {
                            rightPaddle.y += Math.max(-AI_SPEED, diff);
                        }
                    }

                    // Tambahkan sedikit error random agar AI tidak sempurna
                    rightPaddle.y += (Math.random() - 0.5) * 2;
                } else {
                    // Kembali ke tengah ketika bola menjauh
                    const centerY = canvas.height / 2 - rightPaddle.height / 2;
                    const diff = centerY - rightPaddle.y;
                    rightPaddle.y += diff * 0.1; // Kembali ke tengah perlahan
                }
            }

            // Game loop
            function gameStep() {
                if (!gameRunning) return;

                // Clear canvas
                ctx.fillStyle = '#000';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Update player paddle
                leftPaddle.y += leftPaddle.dy;

                // Update AI paddle - GANTI BAGIAN INI
                updateAI();

                // Boundary checks for paddles
                leftPaddle.y = Math.max(0, Math.min(canvas.height - leftPaddle.height, leftPaddle.y));
                rightPaddle.y = Math.max(0, Math.min(canvas.height - rightPaddle.height, rightPaddle.y));

                // Update ball
                ball.x += ball.dx;
                ball.y += ball.dy;

                // Ball boundaries (top/bottom)
                if (ball.y - ball.radius < 0 || ball.y + ball.radius > canvas.height) {
                    ball.dy = -ball.dy;
                }

                // Ball collision with paddles
                if (ball.x - ball.radius < leftPaddle.x + leftPaddle.width &&
                    ball.y > leftPaddle.y && ball.y < leftPaddle.y + leftPaddle.height &&
                    ball.dx < 0) {
                    ball.dx = -ball.dx;
                    // Tambahkan variasi sudut pantulan
                    ball.dy += (Math.random() - 0.5) * 4;
                    // Percepat bola sedikit setiap pantulan
                    ball.dx *= 1.05;
                    ball.dy *= 1.05;
                }

                if (ball.x + ball.radius > rightPaddle.x &&
                    ball.y > rightPaddle.y && ball.y < rightPaddle.y + rightPaddle.height &&
                    ball.dx > 0) {
                    ball.dx = -ball.dx;
                    // Tambahkan variasi sudut pantulan
                    ball.dy += (Math.random() - 0.5) * 4;
                    // Percepat bola sedikit setiap pantulan
                    ball.dx *= 1.05;
                    ball.dy *= 1.05;
                }

                // Ball out of bounds
                if (ball.x < 0) {
                    // Komputer mencetak skor
                    gameOver(false);
                    return;
                } else if (ball.x > canvas.width) {
                    // Player mencetak skor
                    score++;
                    scoreElement.textContent = score;
                    resetBall();
                }

                // Draw paddles
                ctx.fillStyle = '#fff';
                ctx.fillRect(leftPaddle.x, leftPaddle.y, leftPaddle.width, leftPaddle.height);
                ctx.fillRect(rightPaddle.x, rightPaddle.y, rightPaddle.width, rightPaddle.height);

                // Draw ball
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
                ctx.fill();

                // Draw center line
                ctx.setLineDash([5, 15]);
                ctx.beginPath();
                ctx.moveTo(canvas.width / 2, 0);
                ctx.lineTo(canvas.width / 2, canvas.height);
                ctx.strokeStyle = '#fff';
                ctx.stroke();

                // Draw scores
                ctx.font = '40px Arial';
                ctx.fillText(score, canvas.width / 4, 50);
                ctx.fillText('AI', 3 * canvas.width / 4, 50);

                gameLoop = requestAnimationFrame(gameStep);
            }

            // Reset ball after score - TAMBAHKAN FUNGSI INI
            function resetBall() {
                ball.x = canvas.width / 2;
                ball.y = canvas.height / 2;
                ball.dx = Math.random() > 0.5 ? 5 : -5;
                ball.dy = (Math.random() - 0.5) * 10;
            }

            // Game over - MODIFIKASI FUNGSI INI
            function gameOver(playerWon) {
                gameRunning = false;
                ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.font = '30px Arial';
                ctx.fillStyle = 'white';
                ctx.textAlign = 'center';

                if (playerWon === false) {
                    ctx.fillText('AI Menang!', canvas.width / 2, canvas.height / 2);
                } else {
                    ctx.fillText('Game Over!', canvas.width / 2, canvas.height / 2);
                }
                ctx.fillText(`Scoremu: ${score}`, canvas.width / 2, canvas.height / 2 + 40);
            }

            // Keyboard controls - MODIFIKASI (hapus kontrol untuk paddle kanan)
            function handleKeyDown(e) {
                if (!gameRunning) return;

                // Hanya player paddle (W/S)
                if (e.key === 'w' || e.key === 'W') leftPaddle.dy = -8;
                if (e.key === 's' || e.key === 'S') leftPaddle.dy = 8;
            }

            function handleKeyUp(e) {
                if (e.key === 'w' || e.key === 'W' || e.key === 's' || e.key === 'S') {
                    leftPaddle.dy = 0;
                }
            }

            // Event listeners
            startBtn.addEventListener('click', initGame);

            document.getElementById('pingPongModal').addEventListener('shown.bs.modal', function() {
                document.addEventListener('keydown', handleKeyDown);
                document.addEventListener('keyup', handleKeyUp);
                resizeCanvas();
            });

            document.getElementById('pingPongModal').addEventListener('hidden.bs.modal', function() {
                document.removeEventListener('keydown', handleKeyDown);
                document.removeEventListener('keyup', handleKeyUp);
                gameRunning = false;
                if (gameLoop) cancelAnimationFrame(gameLoop);
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (document.getElementById('pingPongModal').classList.contains('show')) {
                    resizeCanvas();
                }
            });
        }

        // TYPING GAME
        function initTypingGameModal() {
            const playerCar = document.querySelector('#typingModal .player-car');
            const opponentCar = document.querySelector('#typingModal .opponent-car');
            const typingPrompt = document.getElementById('typing-prompt');
            const typingInput = document.getElementById('typing-input');
            const startBtn = document.getElementById('startTypingModal');
            const scoreElement = document.getElementById('typingScoreModal');

            // Game variables
            let gameActive = false;
            let startTime;
            let currentPrompt = '';
            let currentIndex = 0;
            let wpm = 0;

            // Collection of typing prompts
            const prompts = [
                "Teknologi telah mengubah cara kita berkomunikasi dengan orang lain di seluruh dunia.",
                "Belajar adalah proses yang berkelanjutan dan tidak pernah berhenti sepanjang hidup kita.",
                "Membaca buku dapat membuka jendela pengetahuan dan memperluas wawasan kita.",
                "Latihan yang konsisten adalah kunci untuk menguasai keterampilan baru dengan baik.",
                "Keberhasilan tidak datang dalam semalam, tetapi membutuhkan kerja keras dan dedikasi.",
                "Kegagalan adalah bagian dari perjalanan menuju kesuksesan yang harus kita hadapi.",
                "Internet telah merevolusi cara kita mengakses informasi dan pengetahuan.",
                "Masa depan teknologi akan terus berkembang dengan kecepatan yang semakin tinggi.",
                "Lingkungan yang sehat sangat penting untuk menjaga kesehatan fisik dan mental kita."
            ];

            // Initialize game
            function initGame() {
                // Reset variables
                gameActive = true;
                startTime = null;
                currentIndex = 0;
                wpm = 0;
                scoreElement.textContent = wpm;

                // Reset car positions
                playerCar.style.left = '10%';
                opponentCar.style.right = '70%';

                // Choose a random prompt
                const randomIndex = Math.floor(Math.random() * prompts.length);
                currentPrompt = prompts[randomIndex];

                // Display the prompt with formatting
                renderPrompt();

                // Clear and focus input
                typingInput.value = '';
                typingInput.disabled = false;
                typingInput.focus();
            }

            // Render the typing prompt with highlighting
            function renderPrompt() {
                let html = '';
                for (let i = 0; i < currentPrompt.length; i++) {
                    if (i < currentIndex) {
                        // Already typed correctly
                        html += `<span class="typed-correct">${currentPrompt[i]}</span>`;
                    } else if (i === currentIndex) {
                        // Current character
                        html += `<span class="highlight">${currentPrompt[i]}</span>`;
                    } else {
                        // Not yet typed
                        html += `<span>${currentPrompt[i]}</span>`;
                    }
                }
                typingPrompt.innerHTML = html;
            }

            // Update car positions based on progress
            function updateCars() {
                // Player progress (0-100%)
                const progress = (currentIndex / currentPrompt.length) * 100;
                playerCar.style.left = `${10 + progress * 0.8}%`; // 10% to 90%

                // Opponent progress (simulated to make it challenging)
                // Randomly make the opponent faster or slower
                const opponentSpeed = Math.random() * 0.5 + 0.7; // 0.7 to 1.2 speed factor
                const opponentProgress = Math.min(progress * opponentSpeed, 100);
                opponentCar.style.right = `${70 - opponentProgress * 0.6}%`; // 70% to 10%
            }

            // Calculate WPM (Words Per Minute)
            function calculateWPM() {
                if (!startTime || !gameActive) return 0;

                const timeElapsed = (new Date() - startTime) / 1000 / 60; // in minutes
                const wordCount = currentIndex / 5; // standard word length is 5 characters

                return Math.round(wordCount / timeElapsed);
            }

            // Handle typing events
            function handleInput() {
                if (!gameActive) return;

                // Start timer on first keypress
                if (!startTime) {
                    startTime = new Date();
                }

                const inputText = typingInput.value;
                // Only check the last character typed
                const lastCharTyped = inputText[inputText.length - 1];
                const expectedChar = currentPrompt[currentIndex];

                // If correct, advance
                if (lastCharTyped === expectedChar) {
                    currentIndex++;

                    // Update WPM score
                    wpm = calculateWPM();
                    scoreElement.textContent = wpm;

                    // Clear input to simplify tracking
                    typingInput.value = '';

                    // Update visuals
                    renderPrompt();
                    updateCars();

                    // Check for completion
                    if (currentIndex >= currentPrompt.length) {
                        gameComplete();
                    }
                } else {
                    // Wrong character typed - flash input red
                    typingInput.classList.add('typed-wrong');
                    setTimeout(() => {
                        typingInput.classList.remove('typed-wrong');
                    }, 200);

                    // Clear input to retry
                    typingInput.value = '';
                }
            }

            // Game complete
            function gameComplete() {
                gameActive = false;
                typingInput.disabled = true;

                // Determine winner
                const playerReachedEnd = currentIndex >= currentPrompt.length;
                const playerPosition = parseFloat(playerCar.style.left) || 10;
                const opponentPosition = 100 - (parseFloat(opponentCar.style.right) || 70);

                if (playerReachedEnd) {
                    if (playerPosition > opponentPosition) {
                        // Player wins
                        alert(`Kamu menang! Kecepatan: ${wpm} WPM`);
                    } else {
                        // Opponent wins by tiny margin
                        alert(`Kamu kalah tipis! Kecepatan: ${wpm} WPM`);
                    }
                }
            }

            // Event listeners
            startBtn.addEventListener('click', initGame);
            typingInput.addEventListener('input', handleInput);

            // Initialize the game when modal is shown
            document.getElementById('typingModal').addEventListener('shown.bs.modal', function() {
                initGame();
            });

            // Mini typing game for main page
            document.getElementById('startTyping').addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('typingModal'));
                modal.show();
            });
        }

        // FLAPPY BIRD GAME MODAL
        function initFlappyBirdGameModal() {
            const canvas = document.getElementById('flappyCanvasModal');
            const ctx = canvas.getContext('2d');
            const startBtn = document.getElementById('startFlappyModal');
            const scoreElement = document.getElementById('flappyScoreModal');

            // Set canvas size
            function resizeCanvas() {
                const container = document.querySelector('#flappyModal .flappy-container');
                canvas.width = container.clientWidth;
                canvas.height = container.clientHeight;
            }

            // Game variables
            let bird = {};
            let pipes = [];
            let score = 0;
            let gameRunning = false;
            let gameLoop;
            let spawnPipeInterval;

            // Load images
            const birdImg = new Image();
            birdImg.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAFFmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDAgNzkuMTYwNDUxLCAyMDE3LzA1LzA2LTAxOjA4OjIxICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOCAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMTgtMDctMDlUMTQ6MjU6NDErMDg6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDE4LTA3LTA5VDE0OjI2OjAzKzA4OjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDE4LTA3LTA5VDE0OjI2OjAzKzA4OjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgcGhvdG9zaG9wOklDQ1Byb2ZpbGU9InNSR0IgSUVDNjE5NjYtMi4xIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjk3ZDE0ZjFhLWNkZmItNGRiMC1iNTllLTNhMmU5NGNjMzg1MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5N2QxNGYxYS1jZGZiLTRkYjAtYjU5ZS0zYTJlOTRjYzM4NTAiIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo5N2QxNGYxYS1jZGZiLTRkYjAtYjU5ZS0zYTJlOTRjYzM4NTAiPiA8eG1wTU06SGlzdG9yeT4gPHJkZjpTZXE+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjcmVhdGVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOjk3ZDE0ZjFhLWNkZmItNGRiMC1iNTllLTNhMmU5NGNjMzg1MCIgc3RFdnQ6d2hlbj0iMjAxOC0wNy0wOVQxNDoyNTo0MSswODowMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKE1hY2ludG9zaCkiLz4gPC9yZGY6U2VxPiA8L3htcE1NOkhpc3Rvcnk+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+0HqSTwAAAsxJREFUSImtlM1rE1EUxc+bmUwm+Zg0JpkmtbVJU7QYbLViRUGooLQutCi2daGbglDQQheu/AP8XHShCzcuhLoQoZt+IYouBCtUDEGTlH5p0iRN2ny08dU3nRemIZKmteAbntzLu2fOu/fOHUIpxf+0Rf8hMgLgZg/Qr4QyCUoJCKEAAJrjIJgJJmGgrmgyLrDVD+5YM9ykbVOB5bEvuH/nNtyDQ9ixcyT8YuyN4+iFCzAaDZAkCfGfEXz6+AFvn41UJRJJKw3r6vJ97g7fv4crV68jl8uDUgpRFCGKAjRNkwXVVYWnExO4fesG3r9+0s25ebWqwOsb8ePxkydQVQWqqsqCnZ5ehEKh5XHfwUNKMBgwrInQQmkN+UQhqFaWcPnSRYiigEgkAp7jFEopaLOJSqWCclld9+cJpUCtVvuHC0G2nRCaZhm9LoeysuQRFHo7YTJ0tXRisRgYhkGxWIQgiEunIAgQBAGSJNWfzlwEhmFQKBTkUP8U/DlOHJbr9fpyqm5wHIdsNguXy4VWPrJA6W8wGAzwPM8QQiCXhKCVVfBgWRYOhwPhcBiTk5MoFosrNFaWGNvS00x2/vSIYrfb0e/xQJIkuKwWCQDicfMCkx/wGo1GDAwMyALLLMdEo98x5PNtTRKJDG8qlTFujjWXTfNTU9cOHD4Cs9kM3dPMTU1DZMkzulUK2GyL9sXUY1xcXJRdFgs5KQB4nnr44PZsOBwkDkcfHI5+OBwOZFJJ/JgZX1E9DMOwzcK7dKq9lbfzpVKJlss5lEozyGaThCyVJomwXHrTvP3Bix+RSITS2TClM58opSnyp1T69y+w7fvA5XLpWJaFLMtbEWyO0N2uFVMU0+kuEIZhQKp7t6/QmkAbm0L/P/G15kCvr6KFUwihbfK+XfBX4JebqaQ+kGsAAAAASUVORK5CYII=';

            // Initialize game
            function initGame() {
                resizeCanvas();

                // Reset bird
                bird = {
                    x: 50,
                    y: canvas.height / 2,
                    width: 30,
                    height: 24,
                    velocity: 0,
                    gravity: 0.5,
                    jump: -8
                };

                // Reset pipes
                pipes = [];

                // Reset score
                score = 0;
                scoreElement.innerHTML = score;

                // Start game loop
                if (gameLoop) clearInterval(gameLoop);
                if (spawnPipeInterval) clearInterval(spawnPipeInterval);

                gameLoop = setInterval(gameStep, 20);
                spawnPipeInterval = setInterval(spawnPipe, 2000);

                gameRunning = true;

                // Create initial pipes
                spawnPipe();
            }

            // Spawn a new pipe
            function spawnPipe() {
                const gap = 120; // Gap between top and bottom pipes
                const pipeWidth = 50;
                const minHeight = 50;
                const maxHeight = canvas.height - gap - minHeight;

                const topHeight = Math.floor(Math.random() * (maxHeight - minHeight)) + minHeight;

                // Create pipes
                pipes.push({
                    x: canvas.width,
                    width: pipeWidth,
                    topHeight: topHeight,
                    bottomY: topHeight + gap,
                    passed: false
                });
            }

            // Game step function
            function gameStep() {
                // Update bird position
                bird.velocity += bird.gravity;
                bird.y += bird.velocity;

                // Check for collision with ground or ceiling
                if (bird.y + bird.height > canvas.height) {
                    bird.y = canvas.height - bird.height;
                    gameOver();
                }

                if (bird.y < 0) {
                    bird.y = 0;
                    bird.velocity = 0;
                }

                // Update pipes
                for (let i = pipes.length - 1; i >= 0; i--) {
                    const pipe = pipes[i];
                    pipe.x -= 2;

                    // Remove off-screen pipes
                    if (pipe.x + pipe.width < 0) {
                        pipes.splice(i, 1);
                        continue;
                    }

                    // Check for collision
                    if (
                        bird.x < pipe.x + pipe.width &&
                        bird.x + bird.width > pipe.x &&
                        (bird.y < pipe.topHeight || bird.y + bird.height > pipe.bottomY)
                    ) {
                        gameOver();
                        return;
                    }

                    // Increment score when passing pipe
                    if (!pipe.passed && pipe.x + pipe.width < bird.x) {
                        pipe.passed = true;
                        score++;
                        scoreElement.innerHTML = score;
                    }
                }

                // Draw game
                drawGame();
            }

            // Draw game function
            function drawGame() {
                // Clear canvas
                ctx.fillStyle = '#70c5ce';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Draw bird
                try {
                    if (birdImg.complete) {
                        ctx.drawImage(birdImg, bird.x, bird.y, bird.width, bird.height);
                    } else {
                        // Fallback if image not loaded
                        ctx.fillStyle = '#da7756';
                        ctx.fillRect(bird.x, bird.y, bird.width, bird.height);
                    }
                } catch (e) {
                    // Fallback if error
                    ctx.fillStyle = '#da7756';
                    ctx.fillRect(bird.x, bird.y, bird.width, bird.height);
                }

                // Draw pipes
                pipes.forEach(pipe => {
                    // Top pipe
                    ctx.fillStyle = '#75b91d';
                    ctx.fillRect(pipe.x, 0, pipe.width, pipe.topHeight);

                    // Bottom pipe
                    ctx.fillRect(pipe.x, pipe.bottomY, pipe.width, canvas.height - pipe.bottomY);

                    // Pipe borders
                    ctx.strokeStyle = '#543800';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(pipe.x, 0, pipe.width, pipe.topHeight);
                    ctx.strokeRect(pipe.x, pipe.bottomY, pipe.width, canvas.height - pipe.bottomY);
                });

                // Draw score
                ctx.fillStyle = 'white';
                ctx.font = 'bold 20px Merriweather';
                ctx.fillText(`Score: ${score}`, 10, 30);
            }

            // Game over function
            function gameOver() {
                clearInterval(gameLoop);
                clearInterval(spawnPipeInterval);
                gameRunning = false;

                // Draw game over text
                ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.font = '20px Merriweather';
                ctx.fillStyle = 'white';
                ctx.textAlign = 'center';
                ctx.fillText('Game Over!', canvas.width / 2, canvas.height / 2);
                ctx.fillText(`Score: ${score}`, canvas.width / 2, canvas.height / 2 + 30);
            }

            // Jump function
            function jump() {
                if (gameRunning) {
                    bird.velocity = bird.jump;
                }
            }

            // Event listeners
            startBtn.addEventListener('click', initGame);

            // Keyboard control handler
            function handleKeyDown(e) {
                if (e.key === ' ' || e.key === 'ArrowUp') {
                    jump();
                    e.preventDefault();
                }
            }

            // Add/remove event listeners when modal is shown/hidden
            document.getElementById('flappyModal').addEventListener('shown.bs.modal', function() {
                resizeCanvas();
                document.addEventListener('keydown', handleKeyDown);
                canvas.addEventListener('click', jump);

                // Initialize the game when modal is shown
                initGame();
            });

            document.getElementById('flappyModal').addEventListener('hidden.bs.modal', function() {
                document.removeEventListener('keydown', handleKeyDown);
                canvas.removeEventListener('click', jump);

                // Clear game loops
                if (gameLoop) clearInterval(gameLoop);
                if (spawnPipeInterval) clearInterval(spawnPipeInterval);
                gameRunning = false;
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (document.getElementById('flappyModal').classList.contains('show')) {
                    resizeCanvas();
                    drawGame();
                }
            });

            // Preload bird image
            birdImg.onload = function() {
                resizeCanvas();
                drawGame();
            };

            // Initial setup
            resizeCanvas();
            drawGame();
        }

        // Fungsi untuk membersihkan backdrop modal
        function cleanupModalBackdrop() {
            // Hapus backdrop modal yang tersisa
            const modalBackdrops = document.querySelectorAll('.modal-backdrop');
            modalBackdrops.forEach(backdrop => {
                backdrop.remove();
            });

            // Hapus class modal-open dari body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        // Tambahkan event listener untuk membersihkan backdrop setelah modal ditutup
        document.addEventListener('DOMContentLoaded', function() {
            const modalElements = document.querySelectorAll('.modal');
            modalElements.forEach(modal => {
                modal.addEventListener('hidden.bs.modal', function() {
                    cleanupModalBackdrop();
                });
            });
        });



        function initBreakoutGameModal() {
            const canvas = document.getElementById('breakoutCanvasModal');
            const context = canvas.getContext('2d');
            const startBtn = document.getElementById('startBreakoutModal');

            function resizeCanvas() {
                canvas.width = canvas.parentElement.clientWidth - 20; // Perbaiki typo
                canvas.height = 400;
            }

            // Set canvas dimensions
            canvas.width = 400;
            canvas.height = 500;

            // Game variables
            let breakoutGameRunning = false;
            let breakoutAnimationId;
            let score = 0;
            let lives = 3;

            // Level definition
            const level1 = [
                [],
                [],
                [],
                [],
                [],
                [],
                ['R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'],
                ['R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'],
                ['O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O'],
                ['O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O'],
                ['G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G'],
                ['G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G', 'G'],
                ['Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y'],
                ['Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y']
            ];

            const colorMap = {
                'R': 'red',
                'O': 'orange',
                'G': 'green',
                'Y': 'yellow'
            };

            const brickGap = 2;
            const brickWidth = 25;
            const brickHeight = 12;
            const wallSize = 12;
            let bricks = [];

            const paddle = {
                x: 0,
                y: 0,
                width: brickWidth * 2,
                height: brickHeight,
                dx: 0
            };

            const ball = {
                x: 0,
                y: 0,
                width: 8,
                height: 8,
                speed: 4,
                dx: 0,
                dy: 0
            };

            function initBreakout() {
                resizeCanvas();

                // Reset game state
                bricks = [];
                score = 0;
                lives = 3;
                breakoutGameRunning = false;

                // Update score display
                const scoreElement = document.getElementById('breakoutScoreModal');
                if (scoreElement) scoreElement.textContent = score;

                // Initialize bricks - PERBAIKAN DI SINI
                const brickRows = level1.length;

                // Cari baris pertama yang tidak kosong untuk menghitung kolom
                let brickCols = 0;
                for (let i = 0; i < level1.length; i++) {
                    if (level1[i].length > 0) {
                        brickCols = level1[i].length;
                        break;
                    }
                }

                const totalBrickWidth = (brickWidth + brickGap) * brickCols;
                const offsetX = (canvas.width - totalBrickWidth) / 2;

                for (let row = 0; row < brickRows; row++) {
                    for (let col = 0; col < brickCols; col++) {
                        const colorCode = level1[row][col];
                        if (colorCode) {
                            bricks.push({
                                x: offsetX + (brickWidth + brickGap) * col,
                                y: wallSize + (brickHeight + brickGap) * row,
                                color: colorMap[colorCode],
                                width: brickWidth,
                                height: brickHeight,
                                visible: true
                            });
                        }
                    }
                }

                // Reset paddle position
                paddle.x = canvas.width / 2 - paddle.width / 2;
                paddle.y = canvas.height - 60;
                paddle.dx = 0;

                // Reset ball position dan langsung mulai bergerak
                ball.x = canvas.width / 2;
                ball.y = paddle.y - 10;
                ball.dx = ball.speed * 0.7; // Langsung bergerak
                ball.dy = -ball.speed; // Langsung bergerak ke atas

                // Langsung mulai game
                breakoutGameRunning = true;
                requestAnimationFrame(gameLoop);

                // Draw initial state
                draw();
            }

            function collides(obj1, obj2) {
                return obj1.x < obj2.x + obj2.width &&
                    obj1.x + obj1.width > obj2.x &&
                    obj1.y < obj2.y + obj2.height &&
                    obj1.y + obj1.height > obj2.y;
            }

            function draw() {
                // Clear canvas
                context.clearRect(0, 0, canvas.width, canvas.height);

                // Draw walls
                context.fillStyle = 'lightgrey';
                context.fillRect(0, 0, canvas.width, wallSize);
                context.fillRect(0, 0, wallSize, canvas.height);
                context.fillRect(canvas.width - wallSize, 0, wallSize, canvas.height);

                // Draw paddle
                context.fillStyle = 'cyan';
                context.fillRect(paddle.x, paddle.y, paddle.width, paddle.height);

                // Draw ball
                context.fillStyle = 'white';
                context.fillRect(ball.x, ball.y, ball.width, ball.height);

                // Draw bricks
                bricks.forEach(function(brick) {
                    if (brick.visible) {
                        context.fillStyle = brick.color;
                        context.fillRect(brick.x, brick.y, brick.width, brick.height);
                    }
                });

                // Draw score and lives
                context.fillStyle = 'white';
                context.font = '16px Arial';
                context.fillText('Score: ' + score, 20, 30);
                context.fillText('Lives: ' + lives, canvas.width - 80, 30);
            }

            function update() {
                if (!breakoutGameRunning) return;

                // Move paddle
                paddle.x += paddle.dx;

                // Keep paddle within bounds
                if (paddle.x < wallSize) {
                    paddle.x = wallSize;
                } else if (paddle.x + paddle.width > canvas.width - wallSize) {
                    paddle.x = canvas.width - wallSize - paddle.width;
                }

                // Move ball
                ball.x += ball.dx;
                ball.y += ball.dy;

                // Ball collision with walls
                if (ball.x < wallSize) {
                    ball.x = wallSize;
                    ball.dx *= -1;
                } else if (ball.x + ball.width > canvas.width - wallSize) {
                    ball.x = canvas.width - wallSize - ball.width;
                    ball.dx *= -1;
                }

                if (ball.y < wallSize) {
                    ball.y = wallSize;
                    ball.dy *= -1;
                }

                // Ball goes below screen
                if (ball.y > canvas.height) {
                    lives--;
                    if (lives <= 0) {
                        gameOver();
                    } else {
                        // Reset ball dan langsung mulai lagi
                        ball.x = canvas.width / 2;
                        ball.y = paddle.y - 10;
                        ball.dx = ball.speed * 0.7;
                        ball.dy = -ball.speed;
                    }
                }

                // Ball collision with paddle
                if (collides(ball, paddle)) {
                    ball.dy *= -1;
                    ball.y = paddle.y - ball.height;

                    // Add some angle to the bounce
                    const hitPos = (ball.x + ball.width / 2) - (paddle.x + paddle.width / 2);
                    ball.dx = hitPos * 0.1;
                }

                // Ball collision with bricks
                for (let i = 0; i < bricks.length; i++) {
                    const brick = bricks[i];

                    if (brick.visible && collides(ball, brick)) {
                        brick.visible = false;
                        score += 10;

                        // Update score display
                        const scoreElement = document.getElementById('breakoutScoreModal');
                        if (scoreElement) scoreElement.textContent = score;

                        // Change ball direction
                        if (ball.y + ball.height - ball.speed <= brick.y ||
                            ball.y >= brick.y + brick.height - ball.speed) {
                            ball.dy *= -1;
                        } else {
                            ball.dx *= -1;
                        }

                        // Check if all bricks are destroyed
                        if (bricks.every(b => !b.visible)) {
                            gameWon();
                        }

                        break;
                    }
                }
            }

            function gameLoop() {
                update();
                draw();

                if (breakoutGameRunning) {
                    breakoutAnimationId = requestAnimationFrame(gameLoop);
                }
            }

            function gameOver() {
                breakoutGameRunning = false;
                context.fillStyle = 'rgba(0, 0, 0, 0.75)';
                context.fillRect(0, 0, canvas.width, canvas.height);

                context.fillStyle = 'white';
                context.font = '36px Arial';
                context.textAlign = 'center';
                context.fillText('Game Over', canvas.width / 2, canvas.height / 2);
                context.font = '24px Arial';
                context.fillText('Final Score: ' + score, canvas.width / 2, canvas.height / 2 + 40);
            }

            function gameWon() {
                breakoutGameRunning = false;
                context.fillStyle = 'rgba(0, 0, 0, 0.75)';
                context.fillRect(0, 0, canvas.width, canvas.height);

                context.fillStyle = 'white';
                context.font = '36px Arial';
                context.textAlign = 'center';
                context.fillText('You Win!', canvas.width / 2, canvas.height / 2);
                context.font = '24px Arial';
                context.fillText('Final Score: ' + score, canvas.width / 2, canvas.height / 2 + 40);
            }

            // Keyboard event handlers
            function handleKeyDown(e) {
                if (e.key === 'ArrowLeft') {
                    paddle.dx = -6;
                } else if (e.key === 'ArrowRight') {
                    paddle.dx = 6;
                }
            }

            function handleKeyUp(e) {
                if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    paddle.dx = 0;
                }
            }

            // Button event handler
            startBtn.addEventListener('click', function() {
                if (breakoutAnimationId) cancelAnimationFrame(breakoutAnimationId);
                initBreakout();
            });

            // Modal event handlers
            document.getElementById('breakoutModal').addEventListener('shown.bs.modal', function() {
                document.addEventListener('keydown', handleKeyDown);
                document.addEventListener('keyup', handleKeyUp);
                resizeCanvas();
                initBreakout();
            });

            document.getElementById('breakoutModal').addEventListener('hidden.bs.modal', function() {
                document.removeEventListener('keydown', handleKeyDown);
                document.removeEventListener('keyup', handleKeyUp);
                breakoutGameRunning = false;
                if (breakoutAnimationId) cancelAnimationFrame(breakoutAnimationId);
            });

            window.addEventListener('resize', function() {
                if (document.getElementById('breakoutModal').classList.contains('show')) {
                    resizeCanvas();
                }
            });
        }
    </script>


    <!-- Game Modals -->
    <!-- Snake Game Modal -->
    <div class="modal fade game-modal" id="snakeModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Snake Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body mt-0 pt-0">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-info-square-rounded-filled fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Tips Bermain</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                    Gunakan tombol keyboard <span class="border rounded px-1 mx-1">←</span> <span class="border rounded px-1 mx-1">→</span> <span class="border rounded px-1 mx-1">↑</span> <span class="border rounded px-1 mx-1">↓</span> untuk menggerakkan ular. Klik tombol
                                    <span class="border rounded px-1 mx-1">Mulai</span> kapanpun kamu siap bermain
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="game-area">
                        <canvas id="snakeCanvasModal" style="background-color: #111; border-radius: 8px; width: 100%; height: 350px;"></canvas>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn-game" id="startSnakeModal">Mulai Game</button>
                        </div>
                        <div>Score: <span id="snakeScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Memory Game Modal -->
    <div class="modal fade game-modal" id="memoryModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Memory Cards</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-info-square-rounded-filled fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Tips Bermain</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                    Jodohkan pasangan kartu yang sama untuk mengungkap semua kartu. Klik setiap kartu untuk membaliknya.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="game-area" id="memory-game-modal"></div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn-game" id="startMemoryModal">Mulai Baru</button>
                        <div>Terhubung: <span id="memoryScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Ping Pong Game Modal -->
    <div class="modal fade game-modal" id="pingPongModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ping Pong</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-info-square-rounded-filled fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Tips Bermain</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                    Gunakan W/S untuk menggerakkan paddle kiri. Lawan komputer yang mengontrol paddle kanan!
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="game-area">
                        <canvas id="pingPongCanvas" style="background: #000; border-radius: 8px; width: 100%; height: 350px;"></canvas>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn-game" id="startPingPongModal">Mulai Baru</button>
                        <div>Skor: <span id="pingPongScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Game Modal -->
    <div class="modal fade game-modal" id="typingModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Typing Race</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body">
                    <div class="game-area">
                        <div class="typing-game">
                            <div class="racing-track" style="height: 150px; margin-bottom: 15px;">
                                <div class="player-car"></div>
                                <div class="opponent-car"></div>
                            </div>
                        </div>
                        <div class="typing-text" style="position: relative; background-color: #f8f9fa; border-radius: 8px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <div class="typing-prompt" id="typing-prompt" style="margin-bottom: 12px;"></div>
                            <input type="text" class="typing-input" id="typing-input" placeholder="Ketik di sini untuk memulai..." style="width: 100%;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn-game" id="startTypingModal">Mulai Baru</button>
                        <div>WPM: <span id="typingScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flappy Bird Modal -->
    <div class="modal fade game-modal" id="flappyModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flappy Bird</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body">
                    <div class="game-area">
                        <div class="flappy-container">
                            <canvas id="flappyCanvasModal" style="background-color: #70c5ce; border-radius: 8px; width: 100%; height: 100%;"></canvas>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <button class="btn-game" id="startFlappyModal">Mulai Baru</button>
                            <span class="ms-3 d-none d-md-inline">Klik atau tekan spasi untuk terbang</span>
                        </div>
                        <div>Skor: <span id="flappyScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- breakout Modal -->
    <div class="modal fade game-modal" id="breakoutModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Breakout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cleanupModalBackdrop()"></button>
                </div>
                <div class="modal-body">
                    <div class="alert border bg-light" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-info-square-rounded-filled fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="fw-bold p-0 m-0" style="font-size: 14px;">Tips Bermain</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                    Gunakan tombol ← → untuk menggerakkan paddle. Tekan spasi untuk meluncurkan bola.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="game-area">
                        <canvas id="breakoutCanvasModal" style="background-color: #000; border-radius: 8px; width: 100%; height: 350px;"></canvas>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <button class="btn-game" id="startBreakoutModal">Mulai Baru</button>
                        </div>
                        <div>Skor: <span id="breakoutScoreModal">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>