<?php
session_start();
require "koneksi.php";

// Cek apakah user sudah login dan merupakan siswa
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Fungsi untuk mendeteksi URL dan mengubahnya menjadi link yang dapat diklik
// Fungsi untuk mendeteksi URL dan mengubahnya menjadi card link
function makeLinksClickable($text)
{
    // Pattern untuk mendeteksi URL dalam teks
    $pattern = '/(https?:\/\/[^\s<]+)/';

    // Callback untuk membuat card link
    $callback = function ($matches) {
        $originalUrl = $matches[1];

        // Tentukan icon berdasarkan jenis URL
        $iconClass = "bi-link-45deg";
        $iconColor = "rgb(219, 106, 68)";

        // Cek jenis URL berdasarkan domain atau ekstensi
        if (strpos($originalUrl, 'youtube.com') !== false || strpos($originalUrl, 'youtu.be') !== false) {
            $iconClass = "bi-youtube";
            $linkType = "Link YouTube";
        } elseif (strpos($originalUrl, 'vimeo.com') !== false) {
            $iconClass = "bi-vimeo";
            $linkType = "Link Vimeo";
        } elseif (strpos($originalUrl, '.pdf') !== false) {
            $iconClass = "bi-filetype-pdf";
            $linkType = "Link PDF";
        } elseif (strpos($originalUrl, '.doc') !== false || strpos($originalUrl, '.docx') !== false) {
            $iconClass = "bi-filetype-doc";
            $linkType = "Link Word Document";
        } elseif (strpos($originalUrl, '.xls') !== false || strpos($originalUrl, '.xlsx') !== false) {
            $iconClass = "bi-filetype-xls";
            $linkType = "Link Excel Document";
        } elseif (strpos($originalUrl, '.ppt') !== false || strpos($originalUrl, '.pptx') !== false) {
            $iconClass = "bi-filetype-ppt";
            $linkType = "Link PowerPoint";
        } elseif (strpos($originalUrl, '.zip') !== false || strpos($originalUrl, '.rar') !== false || strpos($originalUrl, '.7z') !== false) {
            $iconClass = "bi-file-earmark-zip";
            $linkType = "Link File Terkompresi";
        } elseif (strpos($originalUrl, '.mp3') !== false || strpos($originalUrl, '.wav') !== false || strpos($originalUrl, '.ogg') !== false) {
            $iconClass = "bi-file-earmark-music";
            $linkType = "Link File Audio";
        } elseif (strpos($originalUrl, '.mp4') !== false || strpos($originalUrl, '.mov') !== false || strpos($originalUrl, '.avi') !== false) {
            $iconClass = "bi-file-earmark-play";
            $linkType = "Link File Video";
        } elseif (strpos($originalUrl, '.jpg') !== false || strpos($originalUrl, '.jpeg') !== false || strpos($originalUrl, '.png') !== false || strpos($originalUrl, '.gif') !== false) {
            $iconClass = "bi-file-earmark-image";
            $linkType = "Link Gambar";
        } elseif (strpos($originalUrl, 'drive.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Drive";
        } elseif (strpos($originalUrl, 'docs.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Docs";
        } elseif (strpos($originalUrl, 'sheets.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Sheets";
        } elseif (strpos($originalUrl, 'slides.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Slides";
        } elseif (strpos($originalUrl, 'forms.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Forms";
        } elseif (strpos($originalUrl, 'meet.google.com') !== false) {
            $iconClass = "bi-camera-video";
            $linkType = "Link Google Meet";
        } elseif (strpos($originalUrl, 'zoom.us') !== false) {
            $iconClass = "bi-camera-video";
            $linkType = "Link Zoom Meeting";
        } elseif (strpos($originalUrl, 'github.com') !== false) {
            $iconClass = "bi-github";
            $linkType = "Link GitHub";
        } elseif (strpos($originalUrl, 'instagram.com') !== false) {
            $iconClass = "bi-instagram";
            $linkType = "Link Instagram";
        } elseif (strpos($originalUrl, 'facebook.com') !== false || strpos($originalUrl, 'fb.com') !== false) {
            $iconClass = "bi-facebook";
            $linkType = "Link Facebook";
        } elseif (strpos($originalUrl, 'twitter.com') !== false || strpos($originalUrl, 'x.com') !== false) {
            $iconClass = "bi-twitter-x";
            $linkType = "Link Twitter/X";
        } elseif (strpos($originalUrl, 'linkedin.com') !== false) {
            $iconClass = "bi-linkedin";
            $linkType = "Link LinkedIn";
        } elseif (strpos($originalUrl, 'tiktok.com') !== false) {
            $iconClass = "bi-tiktok";
            $linkType = "Link TikTok";
        } elseif (strpos($originalUrl, 'classroom.google.com') !== false) {
            $iconClass = "bi-google";
            $linkType = "Link Google Classroom";
        } elseif (strpos($originalUrl, 'quizizz.com') !== false) {
            $iconClass = "bi-question-square";
            $linkType = "Link Quizizz";
        } elseif (strpos($originalUrl, 'kahoot.it') !== false || strpos($originalUrl, 'kahoot.com') !== false) {
            $iconClass = "bi-puzzle-fill";
            $linkType = "Link Kahoot";
        } elseif (strpos($originalUrl, 'quizlet.com') !== false) {
            $iconClass = "bi-card-heading";
            $linkType = "Link Quizlet";
        } elseif (strpos($originalUrl, 'canva.com') !== false) {
            $iconClass = "bi-brush";
            $linkType = "Link Canva";
        }

        // Potong URL jika terlalu panjang untuk tampilan
        $displayUrl = (strlen($originalUrl) > 50) ? substr($originalUrl, 0, 47) . '...' : $originalUrl;

        // Buat card HTML
        return '<div class="detected-link-card p-3 border bg-light" style="border-radius: 15px; cursor: pointer;" data-original-url="' . htmlspecialchars($originalUrl) . '">
            <div class="d-flex">
                <i class="bi ' . $iconClass . ' fs-4 me-3" style="font-size: 24px; color:' . $iconColor . '"></i>
                <div class="d-flex justify-content-between flex-grow-1">
                    <div>
                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">' . $linkType . '</p>
                        <p class="p-0 m-0 text-muted text-truncate" style="font-size: 12px; max-width: 90%;">' . htmlspecialchars($displayUrl) . '</p>
                    </div>
                </div>
                <div>
                    <button class="btn btn-light border rounded-pill">
                        <p style="font-size:12px;" class="p-0 m-0">Buka Link</p>
                    </button>
                </div>
            </div>
        </div>';
    };

    // Lakukan penggantian menggunakan regular expression dengan callback
    $text_with_links = preg_replace_callback($pattern, $callback, $text);

    return $text_with_links;
}

// Ambil ID kelas dari parameter URL
if (!isset($_GET['id'])) {
    header("Location: beranda.php");
    exit();
}
$kelas_id = $_GET['id'];

// Validasi akses kelas
$userid = $_SESSION['userid'];
$query_akses = "SELECT k.* FROM kelas k 
                JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                JOIN siswa s ON ks.siswa_id = s.id 
                WHERE s.username = '$userid' AND k.id = '$kelas_id'";
$result_akses = mysqli_query($koneksi, $query_akses);

if (mysqli_num_rows($result_akses) == 0) {
    header("Location: beranda.php");
    exit();
}

$kelas = mysqli_fetch_assoc($result_akses);

// Ambil informasi guru yang mengajar kelas ini
$guru_id = $kelas['guru_id'];
$query_guru = "SELECT * FROM guru WHERE username = '$guru_id'";
$result_guru = mysqli_query($koneksi, $query_guru);
$guru = mysqli_fetch_assoc($result_guru);

// Ambil postingan di kelas ini
// Modify the query_postingan part in kelas.php to include student posts
$query_postingan = "SELECT 
    p.*,
    COALESCE(g.namaLengkap, s.nama) as nama_poster,
    COALESCE(g.foto_profil, s.foto_profil) as foto_poster,
    g.jabatan as jabatan_guru,
    t.id as tugas_id,
    t.judul as judul_tugas,
    t.batas_waktu,
    t.status as tugas_status,
    p.user_type
FROM postingan_kelas p
LEFT JOIN guru g ON p.user_id = g.username AND p.user_type = 'guru'
LEFT JOIN siswa s ON p.user_id = s.username AND p.user_type = 'siswa'
LEFT JOIN tugas t ON p.id = t.postingan_id
WHERE p.kelas_id = '$kelas_id'
ORDER BY p.created_at DESC";
$result_postingan = mysqli_query($koneksi, $query_postingan);

// Ambil jumlah siswa di kelas ini
$query_jumlah_siswa = "SELECT COUNT(*) as total FROM kelas_siswa WHERE kelas_id = '$kelas_id'";
$result_jumlah = mysqli_query($koneksi, $query_jumlah_siswa);
$jumlah_siswa = mysqli_fetch_assoc($result_jumlah)['total'];

// Fungsi untuk mengecek apakah user sudah like postingan
function sudahLike($postingan_id, $user_id, $koneksi)
{
    $query = "SELECT * FROM likes_postingan 
              WHERE postingan_id = '$postingan_id' 
              AND user_id = '$user_id'";
    $result = mysqli_query($koneksi, $query);
    return mysqli_num_rows($result) > 0;
}

// Fungsi untuk menghitung jumlah like pada postingan
function hitungLike($postingan_id, $koneksi)
{
    $query = "SELECT COUNT(*) as total FROM likes_postingan 
              WHERE postingan_id = '$postingan_id'";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result)['total'];
}

// Fungsi untuk menghitung jumlah komentar pada postingan
function hitungKomentar($postingan_id, $koneksi)
{
    $query = "SELECT COUNT(*) as total FROM komentar_postingan 
              WHERE postingan_id = '$postingan_id'";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result)['total'];
}

// Ambil komentar untuk setiap postingan
function ambilKomentar($postingan_id, $koneksi)
{
    $query = "SELECT kp.*, s.nama as nama_siswa, s.foto_profil as foto_siswa,
              g.namaLengkap as nama_guru, g.foto_profil as foto_guru,
              IF(s.id IS NOT NULL, 'siswa', 'guru') as user_type
              FROM komentar_postingan kp
              LEFT JOIN siswa s ON kp.user_id = s.username
              LEFT JOIN guru g ON kp.user_id = g.username
              WHERE kp.postingan_id = '$postingan_id'
              ORDER BY kp.created_at DESC";
    return mysqli_query($koneksi, $query);
}

$query = "SELECT s.*, 
    k.nama_kelas AS kelas_saat_ini 
    FROM siswa s 
    LEFT JOIN kelas_siswa ks ON s.id = ks.siswa_id 
    LEFT JOIN kelas k ON ks.kelas_id = k.id 
    WHERE s.username = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <!-- Cropper.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <title>Kelas - SMAGAEdu</title>
</head>
<?php include 'includes/styles.php'; ?>
<style>
    body {
        font-family: Merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }

    .btnPrimary {
        background-color: rgb(218, 119, 86);
        border: 0;
    }

    .btnPrimary:hover {
        background-color: rgb(219, 106, 68);

    }

    .text-primary {
        color: rgb(218, 119, 86);
    }
</style>

<body>

    <!-- video player -->
    <style>
        /* Video styling */
        .responsive-video-wrapper {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;
            /* 16:9 Aspect Ratio */
            background-color: #000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .responsive-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-info {
            font-size: 0.9rem;
            color: #666;
        }

        .video-name {
            max-width: 80%;
        }

        /* Video preview in uploader */
        .video-preview {
            position: relative;
            margin-right: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .video-preview:hover {
            transform: scale(1.05);
        }

        .video-preview .play-icon {
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .video-preview:hover .play-icon {
            opacity: 1;
        }

        /* Fullscreen button styling */
        video::-webkit-media-controls-fullscreen-button {
            display: block !important;
        }

        /* Custom controls overlay on hover */
        .video-controls-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            padding: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .responsive-video-wrapper:hover .video-controls-overlay {
            opacity: 1;
        }
    </style>


    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav siswa.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>




            <!-- konten inti -->
            <div class="col col-inti p-0 p-md-3">
                <style>
                    .col-inti {
                        margin-left: 0;
                        padding-right: 0 !important;
                        /* Remove right padding */
                        max-width: 100%;
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
                        .col-inti {
                            margin-left: 13rem;
                            margin-top: 0;
                        }
                    }
                </style>

                <!-- Container untuk background dengan efek hover -->
                <div class="background-container position-relative rounded mx-2 mx-md-0">
                    <!-- Background image -->
                    <div style="background-image: url(<?php echo !empty($kelas['background_image']) ? $kelas['background_image'] : 'assets/bg.jpg'; ?>); 
                            height: 200px; 
                            padding-top: 120px; 
                            margin-top: 15px; 
                            background-position: center;
                            background-size: cover;"
                        class="rounded text-white shadow latar-belakang">
                    </div>

                    <!-- Overlay dengan tombol (akan muncul saat hover) -->
                    <div class="background-overlay rounded d-flex align-items-center justify-content-center">
                        <!-- <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalEditBackground">
                            <i class="fas fa-camera me-2"></i>Ganti Background
                        </button> -->
                    </div>

                    <!-- Konten (teks) dengan z-index lebih tinggi -->
                    <div class="position-absolute bottom-0 start-0 p-3" style="z-index: 2;">
                        <div>
                            <h5 class="display-5 p-0 m-0 text-white"
                                style="font-weight: bold; font-size: 28px; font-size: clamp(24px, 5vw, 35px);">
                                <?php
                                if (isset($kelas['is_public']) && $kelas['is_public']) {
                                    echo htmlspecialchars($kelas['nama_kelas']);
                                } else {
                                    echo htmlspecialchars($kelas['mata_pelajaran']);
                                }
                                ?>
                            </h5>
                            <h4 class="p-0 m-0 pb-3 text-white" style="font-size: clamp(16px, 4vw, 24px);">
                                Kelas <?php echo htmlspecialchars($kelas['tingkat']); ?>
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- hover untuk background kelas -->
                <!-- CSS untuk efek hover -->
                <style>
                    .latar-belakang {
                        filter: brightness(0.6);
                    }

                    .background-container {
                        position: relative;
                        cursor: pointer;


                    }

                    @media screen and (min-width: 768px) {
                        .background-container {
                            margin-right: 1.5rem !important;
                        }
                    }

                    .background-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: rgba(0, 0, 0, 0.5);
                        opacity: 0;
                        transition: opacity 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .background-container:hover .background-overlay {
                        opacity: 1;
                    }

                    /* Memastikan tombol tidak inherit opacity dari overlay */
                    .background-overlay .btn {
                        transform: translateY(20px);
                        transition: transform 0.3s ease;
                    }

                    .background-container:hover .background-overlay .btn {
                        transform: translateY(0);
                    }
                </style>

                <div class="row mt-4 p-0 m-0 p-2">
                    <div class="col-12 col-lg-8 p-0">

                        <!-- postingan, hanya untuk kelas publik -->
                        <!-- Student Posting Form (ONLY for public classes) -->
                        <!-- Student Post Creator Card (ONLY for public classes) -->
                        <?php if (isset($kelas['is_public']) && $kelas['is_public']): ?>
                            <div class="create-post-card bg-white rounded-3 p-3 mb-4 border">
                                <!-- Desktop View -->
                                <div class="d-none d-md-flex align-items-center gap-3">
                                    <img src="<?php
                                                if (!empty($siswa['photo_url'])) {
                                                    // If using avatar from DiceBear
                                                    if ($siswa['photo_type'] === 'avatar') {
                                                        echo $siswa['photo_url'];
                                                    }
                                                    // If using uploaded photo
                                                    else if ($siswa['photo_type'] === 'upload') {
                                                        echo $siswa['photo_url'];
                                                    }
                                                } else {
                                                    // Default image
                                                    echo 'assets/pp.png';
                                                }
                                                ?>" alt="Profile" class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <button class="btn w-100 text-start px-4 rounded-pill border bg-light hover-bg"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalTambahPostinganSiswa">
                                            <span class="text-muted">Apa yang ingin kamu bagikan dengan kelas?</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Mobile View -->
                                <div class="d-flex d-md-none gap-2">
                                    <img src="<?php
                                                if (!empty($siswa['photo_url'])) {
                                                    if ($siswa['photo_type'] === 'avatar') {
                                                        echo $siswa['photo_url'];
                                                    } else if ($siswa['photo_type'] === 'upload') {
                                                        echo $siswa['photo_url'];
                                                    }
                                                } else {
                                                    echo 'assets/pp.png';
                                                }
                                                ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                    <button class="flex-grow-1 btn text-start rounded-pill border bg-light"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalTambahPostinganSiswa">
                                        <span class="text-muted" style="font-size: 0.9rem;">Mulai diskusi...</span>
                                    </button>
                                </div>

                                <!-- Quick Actions -->
                                <div class="d-flex justify-content-around mt-3 pt-2 border-top">
                                    <button class="btn btn-light flex-grow-1 me-2 d-flex align-items-center justify-content-center gap-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalTambahPostinganSiswa">
                                        <i class="bi bi-image text-success"></i>
                                        <span class="d-none d-md-inline">Foto/Video</span>
                                    </button>
                                    <button class="btn btn-light flex-grow-1 me-2 d-flex align-items-center justify-content-center gap-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalTambahPostinganSiswa">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <span class="d-none d-md-inline">Dokumen</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Tambah Postingan Siswa -->
                            <div class="modal fade" id="modalTambahPostinganSiswa" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-semibold">Buat Postingan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body p-4">
                                            <form action="proses_postingan_siswa.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">

                                                <!-- Author Info -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="<?php
                                                                if (!empty($siswa['photo_url'])) {
                                                                    if ($siswa['photo_type'] === 'avatar') {
                                                                        echo $siswa['photo_url'];
                                                                    } else if ($siswa['photo_type'] === 'upload') {
                                                                        echo $siswa['photo_url'];
                                                                    }
                                                                } else {
                                                                    echo 'assets/pp.png';
                                                                }
                                                                ?>" alt="Profile" class="rounded-circle me-2" width="40" height="40">
                                                    <div>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                                                        <small class="text-muted">Siswa</small>
                                                    </div>
                                                </div>

                                                <!-- Content -->
                                                <div class="form-group mb-3">
                                                    <textarea class="form-control border-0 bg-light"
                                                        name="konten" rows="5"
                                                        placeholder="Apa yang ingin kamu bagikan?"
                                                        style="border-radius: 12px; resize: none;"
                                                        required></textarea>
                                                </div>

                                                <!-- File Preview -->
                                                <div id="previewContainer" class="mb-3 d-none">
                                                    <div id="imagePreview" class="d-flex flex-wrap gap-2"></div>
                                                </div>

                                                <!-- File Upload -->
                                                <div class="attachment-box bg-light rounded-3 p-3 mb-3"
                                                    onclick="document.getElementById('file_upload_siswa').click()">
                                                    <!-- Modifikasi di Modal Tambah Postingan Siswa di kelas.php -->
                                                    <input type="file" id="file_upload_siswa" name="lampiran[]"
                                                        class="d-none" multiple
                                                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,video/mp4,video/webm,video/ogg"
                                                        onchange="showSelectedFilesSiswa(this)">
                                                    <div class="text-center">
                                                        <i class="bi bi-cloud-upload fs-3 mb-2" style="color: rgb(218, 119, 86);"></i>
                                                        <p class="mb-0 text-muted">Klik untuk menambah lampiran</p>
                                                        <small class="text-muted">atau drag & drop file di sini</small>
                                                    </div>
                                                    <div id="selectedFilesSiswa" class="selected-files mt-2"></div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="d-grid">
                                                    <button type="submit" class="btn py-2 rounded-4"
                                                        style="background-color: rgb(218, 119, 86); color: white;">
                                                        Kirim Postingan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function showSelectedFilesSiswa(input) {
                                    const previewContainer = document.getElementById('previewContainer');
                                    const imagePreview = document.getElementById('imagePreview');
                                    const selectedFiles = document.getElementById('selectedFilesSiswa');

                                    if (input.files.length > 0) {
                                        previewContainer.classList.remove('d-none');
                                        imagePreview.innerHTML = ''; // Clear previous previews
                                        selectedFiles.innerHTML = ''; // Clear filename list

                                        // Validasi ukuran video
                                        const maxVideoSize = 100 * 1024 * 1024; // 100MB

                                        for (let i = 0; i < input.files.length; i++) {
                                            const file = input.files[i];

                                            // Create file item display
                                            const fileItem = document.createElement('div');
                                            fileItem.classList.add('file-item', 'd-flex', 'align-items-center', 'bg-white', 'p-2', 'rounded', 'mb-2');

                                            // Icon based on file type
                                            let iconClass = 'bi-file-earmark';
                                            if (file.type.includes('image')) {
                                                iconClass = 'bi-file-image text-success';
                                            } else if (file.type.includes('video')) {
                                                iconClass = 'bi-camera-video text-danger';
                                            } else if (file.name.endsWith('.pdf')) {
                                                iconClass = 'bi-file-pdf text-danger';
                                            } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                                                iconClass = 'bi-file-word text-primary';
                                            }

                                            // File size formatting
                                            const fileSize = file.size < 1024 * 1024 ?
                                                Math.round(file.size / 1024) + ' KB' :
                                                Math.round(file.size / (1024 * 1024) * 10) / 10 + ' MB';

                                            // Peringatan jika file video terlalu besar
                                            let sizeBadge = '';
                                            if (file.type.includes('video') && file.size > maxVideoSize) {
                                                sizeBadge = '<span class="badge bg-danger ms-2" style="font-size: 10px;">Terlalu besar</span>';
                                            }

                                            fileItem.innerHTML = `
                <div class="file-icon me-2">
                    <i class="bi ${iconClass} fs-4"></i>
                </div>
                <div class="file-info flex-grow-1">
                    <div class="file-name text-truncate">${file.name}</div>
                    <small class="text-muted">${fileSize} ${file.type.includes('video') ? '<span class="badge bg-info text-white ms-1">Video</span>' : ''} ${sizeBadge}</small>
                </div>
                <button type="button" class="btn-close btn-sm" 
                    onclick="removeFileSiswa(this, ${i})"></button>
            `;

                                            selectedFiles.appendChild(fileItem);

                                            // Create preview for images
                                            if (file.type.match('image.*')) {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                    const imgContainer = document.createElement('div');
                                                    imgContainer.classList.add('position-relative', 'img-preview');

                                                    const img = document.createElement('img');
                                                    img.src = e.target.result;
                                                    img.style.height = '100px';
                                                    img.style.width = '100px';
                                                    img.style.objectFit = 'cover';
                                                    img.classList.add('rounded');

                                                    imgContainer.appendChild(img);
                                                    imagePreview.appendChild(imgContainer);
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                            // Create preview for videos
                                            else if (file.type.match('video.*')) {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                    const videoContainer = document.createElement('div');
                                                    videoContainer.classList.add('position-relative', 'video-preview');

                                                    const video = document.createElement('video');
                                                    video.src = e.target.result;
                                                    video.style.height = '100px';
                                                    video.style.width = '150px';
                                                    video.style.objectFit = 'cover';
                                                    video.classList.add('rounded');
                                                    video.setAttribute('muted', 'true');

                                                    // Add play icon overlay
                                                    const playIcon = document.createElement('div');
                                                    playIcon.innerHTML = '<i class="bi bi-play-circle-fill fs-4"></i>';
                                                    playIcon.classList.add('play-icon');
                                                    playIcon.style.position = 'absolute';
                                                    playIcon.style.top = '50%';
                                                    playIcon.style.left = '50%';
                                                    playIcon.style.transform = 'translate(-50%, -50%)';
                                                    playIcon.style.color = 'white';
                                                    playIcon.style.textShadow = '0 0 5px rgba(0,0,0,0.5)';

                                                    videoContainer.appendChild(video);
                                                    videoContainer.appendChild(playIcon);
                                                    imagePreview.appendChild(videoContainer);
                                                };
                                                reader.readAsDataURL(file);
                                            }
                                        }
                                    } else {
                                        previewContainer.classList.add('d-none');
                                        selectedFiles.innerHTML = '';
                                    }
                                }

                                function removeFileSiswa(button, index) {
                                    const input = document.getElementById('file_upload_siswa');
                                    const dt = new DataTransfer();

                                    for (let i = 0; i < input.files.length; i++) {
                                        if (i !== index) {
                                            dt.items.add(input.files[i]);
                                        }
                                    }

                                    input.files = dt.files;
                                    button.closest('.file-item').remove();

                                    // Re-render the file preview
                                    showSelectedFilesSiswa(input);
                                }
                            </script>

                            <style>
                                .attachment-box {
                                    border: 2px dashed #ddd;
                                    border-radius: 12px;
                                    cursor: pointer;
                                    transition: all 0.2s ease;
                                }

                                .attachment-box:hover {
                                    background-color: #f8f9fa;
                                    border-color: rgb(218, 119, 86);
                                }

                                .file-item {
                                    border: 1px solid #eee;
                                    transition: all 0.2s ease;
                                }

                                .file-item:hover {
                                    background-color: #f8f9fa !important;
                                }

                                .file-name {
                                    max-width: 200px;
                                    font-size: 14px;
                                }

                                .img-preview {
                                    display: inline-block;
                                    margin-right: 8px;
                                    margin-bottom: 8px;
                                }

                                @media (max-width: 768px) {
                                    .file-name {
                                        max-width: 150px;
                                    }
                                }
                            </style>
                        <?php endif; ?>




                        <!-- Konten Utama -->
                        <!-- postingan guru -->
                        <?php
                        if (mysqli_num_rows($result_postingan) > 0) {
                            while ($post = mysqli_fetch_assoc($result_postingan)) {
                                // Format tanggal
                                $timestamp = strtotime($post['created_at']);
                                $today = strtotime('today');
                                $yesterday = strtotime('yesterday');
                                $time = date("h:i A", $timestamp);

                                if ($timestamp >= $today) {
                                    $tanggal = "Hari ini, " . $time;
                                } elseif ($timestamp >= $yesterday) {
                                    $tanggal = "Kemarin, " . $time;
                                } else {
                                    $tanggal = date("d F", $timestamp) . ", " . $time;
                                }
                        ?>
                                <div class="mt- p-md-3 mb-4 rounded-3 bg-white mx-md-0 postingan p-4"
                                    style="border: 1px solid rgb(226, 226, 226);">
                                    <div class="d-flex gap-3">
                                        <div>
                                            <img src="<?php
                                                        if ($post['user_type'] == 'guru') {
                                                            // For teachers (guru), use foto_poster from uploads/profil directory
                                                            echo !empty($post['foto_poster']) ? 'uploads/profil/' . $post['foto_poster'] : 'assets/pp.png';
                                                        } else {
                                                            // For students (siswa), we need to query for the student's photo info
                                                            $student_username = $post['user_id'];
                                                            $query_student = "SELECT photo_url, photo_type FROM siswa WHERE username = '$student_username'";
                                                            $result_student = mysqli_query($koneksi, $query_student);
                                                            $student_data = mysqli_fetch_assoc($result_student);

                                                            if (!empty($student_data) && !empty($student_data['photo_url'])) {
                                                                // Use photo_url directly since it already contains the full path
                                                                echo $student_data['photo_url'];
                                                            } else {
                                                                // Default image
                                                                echo 'assets/pp.png';
                                                            }
                                                        }
                                                        ?>" alt="Profile Image"
                                                class="profile-img rounded-circle border-0 bg-white"
                                                style="width: 40px; height: 40px; object-fit: cover;">

                                        </div>

                                        <div class="">
                                            <h6 class="p-0 m-0 fw-bold">
                                                <?php echo $post['nama_poster']; ?>
                                                <?php if ($post['user_type'] == 'guru'): ?>
                                                    <span class="badge ms-1" style="font-size: 10px; background-color:rgb(218, 119, 86); color:white;">Guru</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="p-0 m-0 text-muted" style="font-size: 12px;"><?php echo $tanggal; ?></p>
                                        </div>
                                    </div>
                                    <style>
                                        /* Animasi dropdown */
                                        .animate {
                                            animation-duration: 0.2s;
                                            animation-fill-mode: both;
                                            transform-origin: top center;
                                        }

                                        @keyframes slideIn {
                                            0% {
                                                transform: scaleY(0);
                                                opacity: 0;
                                            }

                                            100% {
                                                transform: scaleY(1);
                                                opacity: 1;
                                            }
                                        }

                                        .slideIn {
                                            animation-name: slideIn;
                                        }

                                        /* Style dropdown */
                                        .dropdown-menu {
                                            padding: 0.5rem 0;
                                            border-radius: 8px;
                                            border: 1px solid rgba(0, 0, 0, 0.08);
                                            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                                        }

                                        .dropdown-item {
                                            padding: 0.5rem 1rem;
                                            font-size: 14px;
                                        }

                                        .dropdown-item:hover {
                                            background-color: #f8f9fa;
                                        }
                                    </style>
                                    <div class="">
                                        <?php if ($post['jenis_postingan'] == 'tugas'): ?>
                                            <!-- UI for Tugas with minimalist iOS style -->
                                            <div class="tugas-container mt-3">
                                                <!-- Badge dan Judul -->
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="badge" style="background: rgb(218, 119, 86); padding: 6px 12px; border-radius: 20px; font-weight: 500;">TUGAS</span>
                                                    <h5 class="mb-0" style="font-weight: 600;"><?php echo htmlspecialchars($post['judul_tugas']); ?></h5>
                                                </div>

                                                <!-- Box Info Tugas -->
                                                <div class="tugas-info-box p-1 rounded-4 mb-3">
                                                    <!-- Batas Waktu -->
                                                    <div class="tugas-deadline d-flex align-items-center mb-3">
                                                        <div class="deadline-icon me-3 p-2 rounded-5" style="background: rgba(218, 119, 86, 0.1);">
                                                            <i class="bi bi-clock" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-muted" style="font-size: 13px;">Batas Pengumpulan</div>
                                                            <div style="font-weight: 500; font-size: 15px;">
                                                                <?php echo date("d M Y, H:i", strtotime($post['batas_waktu'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Status Pengumpulan -->
                                                    <?php
                                                    // Di bagian status pengumpulan
                                                    $now = new DateTime();
                                                    $deadline = new DateTime($post['batas_waktu']);

                                                    // Cek apakah tugas ditutup & telat
                                                    $is_late = $now > $deadline;
                                                    $is_closed = $is_late || ($post['tugas_status'] === 'closed');

                                                    // Cek status pengumpulan
                                                    $query_pengumpulan = "SELECT pt.*, t.poin_maksimal 
                                                    FROM pengumpulan_tugas pt
                                                    JOIN tugas t ON pt.tugas_id = t.id
                                                    WHERE pt.tugas_id = '{$post['tugas_id']}' 
                                                    AND pt.siswa_id = '$userid'";
                                                    $result_pengumpulan = mysqli_query($koneksi, $query_pengumpulan);
                                                    $sudah_mengumpulkan = mysqli_num_rows($result_pengumpulan) > 0;
                                                    $data_pengumpulan = $sudah_mengumpulkan ? mysqli_fetch_assoc($result_pengumpulan) : null;
                                                    ?>

                                                    <div class="tugas-progress d-flex align-items-center mb-3">
                                                        <div class="progress-icon me-3 p-2 rounded-5" style="background: rgba(218, 119, 86, 0.1);">
                                                            <i class="bi bi-clipboard-check" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-muted" style="font-size: 13px;">Status</div>
                                                            <div style="font-weight: 500; font-size: 15px;">
                                                                <?php if ($sudah_mengumpulkan): ?>
                                                                    <span class="text-success">Sudah Dikumpulkan</span>
                                                                <?php else: ?>
                                                                    <span class="text-<?php echo $is_late ? 'danger' : 'black'; ?>">
                                                                        <?php echo $is_late ? 'Terlambat' : 'Belum Dikumpulkan'; ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Deskripsi -->
                                                    <div class="tugas-deadline d-flex align-items-center mb-3">
                                                        <div class="deadline-icon me-3 p-2 rounded-5" style="background: rgba(218, 119, 86, 0.1);">
                                                            <i class="bi bi-file-text" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-muted" style="font-size: 13px;">Deskripsi Tugas</div>
                                                            <div style="font-weight: 500; font-size: 15px;">
                                                                <?php echo nl2br(htmlspecialchars($post['konten'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Lampiran Tugas dari Guru -->
                                                    <?php
                                                    $query_lampiran = "SELECT * FROM lampiran_tugas WHERE tugas_id = '{$post['tugas_id']}'";
                                                    $result_lampiran = mysqli_query($koneksi, $query_lampiran);

                                                    if (mysqli_num_rows($result_lampiran) > 0):
                                                    ?>
                                                        <div class="tugas-deadline d-flex align-items-center mb-3">
                                                            <div class="deadline-icon me-3 p-2 rounded-5" style="background: rgba(218, 119, 86, 0.1);">
                                                                <i class="bi bi-file-earmark-text" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                            </div>
                                                            <div class="w-100">
                                                                <div class="text-muted mb-2" style="font-size: 13px;">Lampiran dari Guru</div>
                                                                <div class="lampiran-list">
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
                                                                        <div class="ios-attachment mb-2">
                                                                            <a href="<?php echo $lampiran['path_file']; ?>"
                                                                                class="ios-attachment-item d-flex align-items-center text-decoration-none"
                                                                                download>
                                                                                <div class="ios-attachment-icon flex-shrink-0">
                                                                                    <i class="bi <?php echo $icon; ?>"></i>
                                                                                </div>
                                                                                <div class="ios-attachment-info flex-grow-1 min-w-0">
                                                                                    <div class="ios-attachment-name text-truncate" title="<?php echo htmlspecialchars($lampiran['nama_file']); ?>">
                                                                                        <?php
                                                                                        $ext = strtolower(pathinfo($lampiran['nama_file'], PATHINFO_EXTENSION));
                                                                                        switch ($ext) {
                                                                                            case 'pdf':
                                                                                                echo 'File PDF';
                                                                                                break;
                                                                                            case 'doc':
                                                                                            case 'docx':
                                                                                                echo 'Dokumen Word';
                                                                                                break;
                                                                                            case 'xls':
                                                                                            case 'xlsx':
                                                                                                echo 'File Excel';
                                                                                                break;
                                                                                            case 'ppt':
                                                                                            case 'pptx':
                                                                                                echo 'File Powerpoint';
                                                                                                break;
                                                                                            case 'jpg':
                                                                                            case 'jpeg':
                                                                                            case 'png':
                                                                                            case 'gif':
                                                                                                echo 'File Gambar';
                                                                                                break;
                                                                                            default:
                                                                                                echo 'File tidak terdeteksi';
                                                                                        }
                                                                                        ?>
                                                                                    </div>
                                                                                    <div class="ios-attachment-size"><?php echo formatFileSize($lampiran['ukuran_file']); ?></div>
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    <?php endwhile; ?>

                                                                </div>
                                                            </div>
                                                        </div>

                                                        <style>
                                                            .ios-attachment {
                                                                background: #f0f2f5;
                                                                border-radius: 12px;
                                                                overflow: hidden;
                                                                transition: all 0.2s ease;
                                                            }

                                                            .ios-attachment-item {
                                                                padding: 12px 16px;
                                                                color: inherit;
                                                            }

                                                            .ios-attachment-icon {
                                                                width: 40px;
                                                                height: 40px;
                                                                background: white;
                                                                border-radius: 10px;
                                                                display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                margin-right: 12px;
                                                                font-size: 1.2rem;
                                                            }

                                                            .ios-attachment-info {
                                                                min-width: 0;
                                                                padding-right: 12px;
                                                            }

                                                            .ios-attachment-name {
                                                                font-size: 14px;
                                                                font-weight: 500;
                                                                white-space: nowrap;
                                                                overflow: hidden;
                                                                text-overflow: ellipsis;
                                                                color: #000;
                                                                margin-bottom: 2px;
                                                                max-width: 100%;
                                                            }

                                                            .ios-attachment-size {
                                                                font-size: 12px;
                                                                color: #8e8e93;
                                                            }

                                                            .ios-attachment-download {
                                                                color: #8e8e93;
                                                                font-size: 1.2rem;
                                                            }

                                                            /* Mobile optimization */
                                                            @media (max-width: 576px) {
                                                                .ios-attachment-info {
                                                                    max-width: calc(100% - 100px);
                                                                    /* Account for icon and download button */
                                                                }

                                                                .ios-attachment-name {
                                                                    font-size: 13px;
                                                                    max-width: 100%;
                                                                }

                                                                .ios-attachment-size {
                                                                    font-size: 11px;
                                                                }

                                                                .ios-attachment-icon {
                                                                    width: 32px;
                                                                    height: 32px;
                                                                    font-size: 1rem;
                                                                    margin-right: 8px;
                                                                }

                                                                .ios-attachment-item {
                                                                    padding: 8px 12px;
                                                                }
                                                            }
                                                        </style>

                                                    <?php endif; ?>
                                                    <!-- Tombol Aksi -->
                                                    <?php if (!$sudah_mengumpulkan): ?>
                                                        <button type="button"
                                                            class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                                                            style="background: <?php echo $is_closed ? '#dc3545' : 'rgb(218, 119, 86)'; ?>; 
                   color: white; 
                   border-radius: 12px; 
                   padding: 12px; 
                   font-weight: 500;"
                                                            <?php echo $is_closed ? 'disabled' : ''; ?>
                                                            onclick="<?php echo !$is_closed ? 'kumpulkanTugas(' . $post['tugas_id'] . ')' : ''; ?>">
                                                            <?php if ($is_closed): ?>
                                                                <i class="bi bi-x-circle"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-upload"></i>
                                                            <?php endif; ?>
                                                            <?php
                                                            if ($is_closed) {
                                                                echo 'Tugas Telah Ditutup';
                                                            } else {
                                                                echo 'Kumpulkan Tugas';
                                                            }
                                                            ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <div class="submitted-info p-4 rounded-4 position-relative"
                                                            style="background: #f8f9fa; border: 1px solid #e9ecef;">

                                                            <!-- Header Section -->
                                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                                <div class="status-icon rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 40px; height: 40px; background: rgba(52, 199, 89, 0.1);">
                                                                    <i class="bi bi-check-circle-fill" style="color: #34c759;"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 fw-semibold">Tugasmu Terkirim</h6>
                                                                    <small class="text-muted">
                                                                        <?php echo date('d M Y, H:i', strtotime($data_pengumpulan['waktu_pengumpulan'])); ?>
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            <!-- File Information -->
                                                            <a href="<?php echo $data_pengumpulan['file_path']; ?>"
                                                                class="text-decoration-none"
                                                                target="_blank">
                                                                <div class="file-card p-3 rounded-4 d-flex align-items-center gap-3"
                                                                    style="background: white; border: 1px solid #e9ecef; transition: all 0.2s ease;">
                                                                    <div class="file-icon rounded-3 d-flex align-items-center justify-content-center"
                                                                        style="width: 48px; height: 48px; background: rgba(0,0,0,0.05);">
                                                                        <?php
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
                                                                        <i class="bi <?php echo $icon; ?> fs-4"></i>
                                                                    </div>
                                                                    <div class="file-info flex-grow-1">
                                                                        <div class="text-truncate fw-medium" style="color: #1c1c1e;">
                                                                            <?php
                                                                            $ext = pathinfo($data_pengumpulan['nama_file'], PATHINFO_EXTENSION);
                                                                            switch (strtolower($ext)) {
                                                                                case 'pdf':
                                                                                    echo 'PDF File';
                                                                                    break;
                                                                                case 'doc':
                                                                                case 'docx':
                                                                                    echo 'Word Document';
                                                                                    break;
                                                                                case 'jpg':
                                                                                case 'jpeg':
                                                                                case 'png':
                                                                                case 'gif':
                                                                                    echo 'Image File';
                                                                                    break;
                                                                                case 'xls':
                                                                                case 'xlsx':
                                                                                    echo 'Excel File';
                                                                                    break;
                                                                                case 'ppt':
                                                                                case 'pptx':
                                                                                    echo 'PowerPoint File';
                                                                                    break;
                                                                                default:
                                                                                    echo 'File';
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                        <small class="text-muted">
                                                                            <!-- Klik untuk membuka file -->
                                                                        </small>
                                                                    </div>
                                                                    <i class="bi bi-chevron-right text-muted"></i>
                                                                </div>
                                                            </a>

                                                            <style>
                                                                .file-card:hover {
                                                                    transform: translateY(-1px);
                                                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                                                                }

                                                                .file-card:active {
                                                                    transform: translateY(0);
                                                                    opacity: 0.8;
                                                                }

                                                                @media (max-width: 768px) {
                                                                    .submitted-info {
                                                                        padding: 1rem !important;
                                                                    }
                                                                }
                                                            </style>
                                                        </div>
                                                        <?php if ($data_pengumpulan['nilai'] !== null || $data_pengumpulan['komentar_guru'] !== null): ?>
                                                            <div class="assessment-info mt-3 rounded-4" style="background: #f8f9fa; border: 1px solid #e9ecef;">

                                                                <!-- Header Section -->
                                                                <div class="d-flex align-items-center gap-3 mb-3">
                                                                    <div class="status-icon rounded-circle d-flex align-items-center justify-content-center"
                                                                        style="width: 40px; height: 40px; background: rgba(52, 199, 89, 0.1);">
                                                                        <i class="bi bi-check-circle-fill" style="color: #34c759;"></i>
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-0 fw-semibold">Tugasmu Sudah Dinilai</h6>
                                                                        <small class="p-0 m-0 text-muted"><?php echo date('d M Y, H:i', strtotime($data_pengumpulan['tanggal_penilaian'])); ?></small>
                                                                    </div>
                                                                </div>

                                                                <!-- Nilai guru -->
                                                                <?php if ($data_pengumpulan['nilai'] !== null): ?>
                                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                                        <div class="assessment-icon p-2 rounded-circle"
                                                                            style="background: rgba(218, 119, 86, 0.1);">
                                                                            <i class="bi bi-star-fill"
                                                                                style="color: rgb(218, 119, 86); font-size: 1rem;"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block" style="font-size: 12px;">Nilai</small>
                                                                            <span class="fw-medium" style="font-size: 14px;">
                                                                                <?php echo $data_pengumpulan['nilai']; ?>/<?php echo $data_pengumpulan['poin_maksimal']; ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <!-- Komentar Guru -->
                                                                <?php if ($data_pengumpulan['komentar_guru'] !== null): ?>
                                                                    <div class="d-flex align-items-start gap-2">
                                                                        <div class="assessment-icon p-2 rounded-circle"
                                                                            style="background: rgba(218, 119, 86, 0.1);">
                                                                            <i class="bi bi-chat-left-text"
                                                                                style="color: rgb(218, 119, 86); font-size: 1rem;"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block" style="font-size: 12px;">Komentar Guru</small>
                                                                            <p class="mb-0" style="font-size: 14px;">
                                                                                <?php echo nl2br(htmlspecialchars($data_pengumpulan['komentar_guru'])); ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>




                                                </div>
                                            </div>

                                            <style>
                                                .btn {
                                                    transition: all 0.2s ease;
                                                }

                                                .btn:hover {
                                                    background: rgba(218, 119, 86, 0.9) !important;
                                                    transform: translateY(-1px);
                                                }

                                                .btn:active {
                                                    transform: translateY(0);
                                                }

                                                @media (max-width: 768px) {
                                                    .tugas-info-box {
                                                        padding: 0px !important;
                                                    }
                                                }

                                                .assessment-info {
                                                    padding: 18px;
                                                }
                                            </style>
                                        <?php else: ?>
                                            <!-- UI Normal untuk Postingan Biasa -->
                                            <div class="mt-3">
                                                <p class="textPostingan"><?php
                                                                            $content = htmlspecialchars($post['konten']);
                                                                            $content = nl2br($content);
                                                                            $content = makeLinksClickable($content);
                                                                            echo $content;
                                                                            ?></p>
                                            </div>
                                        <?php endif; ?>


                                        <!-- Modal Kumpulkan Tugas (iOS Style) -->
                                        <div class="modal fade" id="modalKumpulkanTugas" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content" style="border-radius: 16px; border: none;">
                                                    <div class="modal-header border-0 pb-0">
                                                        <div class="w-100 position-relative">
                                                            <h5 class="modal-title fw-semibold mb-0" style="font-size: 18px;">Kumpulkan Tugas</h5>
                                                            <button type="button" class="btn-close position-absolute top-50 translate-middle-y" style="right: 0;" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                    </div>

                                                    <form action="kumpulkan_tugas.php" method="POST" enctype="multipart/form-data">
                                                        <div class="modal-body px-4">
                                                            <input type="hidden" name="tugas_id" id="tugas_id_input">

                                                            <!-- File Upload Section -->
                                                            <div class="mb-4">
                                                                <label class="form-label fw-medium" style="font-size: 15px;">Upload File Tugas</label>
                                                                <p class="p-0 m-0 mb-2" style="font-size: 12px;">Pastikan jawabanmu yang akan dikirim telah final, seluruh jawaban yang sudah terkirim tidak akan bisa untuk di edit kembali</p>
                                                                <div class="upload-container p-4 rounded-4 d-flex flex-column align-items-center justify-content-center border"
                                                                    style="background: #f0f2f5; min-height: 120px;">
                                                                    <i class="bi-file-earmark fs-1 mb-2" style="color: #666;"></i>
                                                                    <input type="file"
                                                                        class="form-control visually-hidden"
                                                                        name="file_tugas[]"
                                                                        id="file_tugas"
                                                                        multiple
                                                                        required>
                                                                    <label for="file_tugas" class="btn btn-light border mb-2">Pilih File</label>
                                                                    <div id="selected-files" class="small text-muted text-center">Belum ada file dipilih</div>
                                                                    <div class="form-text text-center mt-2">Hanya mendukung file PDF, DOC, DOCX, JPG, PNG</div>
                                                                </div>
                                                            </div>

                                                            <!-- Preview File yang Dipilih -->
                                                            <div id="file-preview-container" class="mb-4" style="display: none;">
                                                                <label class="form-label fw-medium mb-2" style="font-size: 15px;">File Terpilih</label>
                                                                <div id="file-preview-list" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                                                    <!-- File preview akan ditampilkan di sini -->
                                                                </div>
                                                            </div>

                                                            <!-- Comment Section -->
                                                            <div class="mb-4">
                                                                <label class="form-label fw-medium mb-2" style="font-size: 15px;">
                                                                    Pesan untuk Guru
                                                                </label>
                                                                <textarea class="form-control border"
                                                                    name="pesan_siswa"
                                                                    rows="3"
                                                                    style="background: #f0f2f5; border-radius: 12px; resize: none;"
                                                                    placeholder="Tambahkan pesan untuk guru (tidak wajib)"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="mb-4 px-4">
                                                            <label class="form-label fw-medium mb-0" style="font-size: 15px;">
                                                                Pernyataan (Wajib centang)
                                                            </label>
                                                            <p style="font-size:12px" class="text-muted m-0 p-0 mb-2">KLIK UNTUK MENCENTANG</p>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="agreeCheck" required>
                                                                <label class="form-check-label" for="agreeCheck">
                                                                    <p style="font-size: 12px;">Saya telah memahami bahwa tugas yang telah di upload tidak bisa di batalkan atau di rubah redaksinya apapun kondisinya
                                                                    </p>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <!-- Footer -->
                                                        <div class="modal-footer border-0 px-4 py-3 pb-4">
                                                            <button type="submit"
                                                                class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                                                                style="background: rgb(218, 119, 86); color: white; border-radius: 12px; padding: 12px; font-weight: 500;">
                                                                <i class="bi bi-upload"></i>
                                                                Kirim
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
                                            // Tambahkan script untuk menangani multiple file upload
                                            document.getElementById('file_tugas').addEventListener('change', function() {
                                                const fileInput = this;
                                                const filePreviewContainer = document.getElementById('file-preview-container');
                                                const filePreviewList = document.getElementById('file-preview-list');
                                                const selectedFilesText = document.getElementById('selected-files');

                                                // Reset preview
                                                filePreviewList.innerHTML = '';

                                                if (fileInput.files.length > 0) {
                                                    // Tampilkan container preview
                                                    filePreviewContainer.style.display = 'block';
                                                    selectedFilesText.textContent = `${fileInput.files.length} file terpilih`;

                                                    // Buat preview untuk setiap file
                                                    for (let i = 0; i < fileInput.files.length; i++) {
                                                        const file = fileInput.files[i];
                                                        const fileSize = file.size < 1024 * 1024 ?
                                                            Math.round(file.size / 1024) + ' KB' :
                                                            Math.round(file.size / (1024 * 1024) * 10) / 10 + ' MB';

                                                        // Tentukan icon berdasarkan tipe file
                                                        let iconClass = 'bi-file-earmark';
                                                        if (file.type.includes('image')) {
                                                            iconClass = 'bi-file-image text-success';
                                                        } else if (file.name.endsWith('.pdf')) {
                                                            iconClass = 'bi-file-pdf text-danger';
                                                        } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                                                            iconClass = 'bi-file-word text-primary';
                                                        }

                                                        // Buat element preview
                                                        const fileItem = document.createElement('div');
                                                        fileItem.className = 'file-item d-flex align-items-center p-2 rounded mb-2 bg-light';
                                                        fileItem.innerHTML = `
                <i class="bi ${iconClass} me-2 fs-5"></i>
                <div class=" min-width-0">
                    <div class="text-truncate" style="max-width:100%">${file.name}</div>
                    <small class="text-muted">${fileSize}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${i}">
                    <i class="bi bi-x"></i>
                </button>
            `;

                                                        filePreviewList.appendChild(fileItem);
                                                    }

                                                    // Tambahkan handler untuk tombol hapus
                                                    document.querySelectorAll('.remove-file').forEach(button => {
                                                        button.addEventListener('click', function() {
                                                            const index = parseInt(this.getAttribute('data-index'));
                                                            removeFile(index);
                                                        });
                                                    });
                                                } else {
                                                    // Sembunyikan container preview jika tidak ada file
                                                    filePreviewContainer.style.display = 'none';
                                                    selectedFilesText.textContent = 'Belum ada file dipilih';
                                                }
                                            });

                                            function removeFile(index) {
                                                const fileInput = document.getElementById('file_tugas');
                                                const dt = new DataTransfer();

                                                // Salin semua file kecuali yang dihapus
                                                for (let i = 0; i < fileInput.files.length; i++) {
                                                    if (i !== index) dt.items.add(fileInput.files[i]);
                                                }

                                                // Update file input dengan DataTransfer baru
                                                fileInput.files = dt.files;

                                                // Trigger event change untuk memperbarui preview
                                                const event = new Event('change');
                                                fileInput.dispatchEvent(event);
                                            }
                                        </script>

                                        <style>
                                            .file-item {
                                                transition: all 0.2s ease;
                                            }

                                            .file-item:hover {
                                                background-color: #e9ecef !important;
                                            }

                                            .file-item .remove-file {
                                                opacity: 0.5;
                                                transition: opacity 0.2s;
                                            }

                                            .file-item:hover .remove-file {
                                                opacity: 1;
                                            }

                                            #file-preview-list {
                                                max-height: 200px;
                                                overflow-y: auto;
                                                scrollbar-width: thin;
                                            }

                                            #file-preview-list::-webkit-scrollbar {
                                                width: 6px;
                                            }

                                            #file-preview-list::-webkit-scrollbar-track {
                                                background: #f1f1f1;
                                            }

                                            #file-preview-list::-webkit-scrollbar-thumb {
                                                background: #ddd;
                                                border-radius: 3px;
                                            }
                                        </style>
                                        <style>
                                            .modal-content {
                                                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                                            }

                                            .upload-container {
                                                transition: all 0.3s ease;
                                            }

                                            .upload-container:hover {
                                                border-color: rgb(218, 119, 86);
                                                background: #f8f9fa;
                                            }

                                            .form-control:focus {
                                                box-shadow: none;
                                                background: #e8eaed !important;
                                            }

                                            /* Custom animation for modal */
                                            .modal.fade .modal-dialog {
                                                transition: transform 0.2s ease-out;
                                                transform: scale(0.95);
                                            }

                                            .modal.show .modal-dialog {
                                                transform: scale(1);
                                            }
                                        </style>

                                        <script>
                                            // Show selected filename(s)
                                            document.getElementById('file_tugas').addEventListener('change', function() {
                                                // Cari elemen untuk menampilkan informasi file
                                                const selectedFilesElement = document.getElementById('selected-files') || document.getElementById('selected-file');

                                                if (!selectedFilesElement) {
                                                    console.warn("Element to display selected files not found");
                                                    return;
                                                }

                                                if (this.files.length === 0) {
                                                    selectedFilesElement.textContent = 'Belum ada file dipilih';
                                                } else if (this.files.length === 1) {
                                                    selectedFilesElement.textContent = this.files[0].name;
                                                } else {
                                                    selectedFilesElement.textContent = `${this.files.length} file terpilih`;
                                                }
                                            });
                                        </script>

                                        <script>
                                            function kumpulkanTugas(tugasId) {
                                                document.getElementById('tugas_id_input').value = tugasId;
                                                const modal = new bootstrap.Modal(document.getElementById('modalKumpulkanTugas'));
                                                modal.show();
                                            }
                                        </script>

                                        <?php
                                        // Query untuk mengambil lampiran
                                        $postingan_id = $post['id'];
                                        $query_lampiran = "SELECT * FROM lampiran_postingan WHERE postingan_id = '$postingan_id'";
                                        $result_lampiran = mysqli_query($koneksi, $query_lampiran);

                                        if (mysqli_num_rows($result_lampiran) > 0) {
                                            echo '<div class="container mt-3 p-0 mb-4 bg-light rounded">';

                                            // Array untuk memisahkan gambar dan dokumen
                                            $images = [];
                                            $documents = [];
                                            $videos = [];

                                            while ($lampiran = mysqli_fetch_assoc($result_lampiran)) {
                                                if (strpos($lampiran['tipe_file'], 'image') !== false) {
                                                    $images[] = $lampiran;
                                                } else if (strpos($lampiran['tipe_file'], 'video') !== false) {
                                                    $videos[] = $lampiran;
                                                } else {
                                                    $documents[] = $lampiran;
                                                }
                                            }

                                            // Tampilkan video jika ada
                                            if (!empty($videos)) {
                                                echo '<div class="video-container p-2">';
                                                foreach ($videos as $video) {
                                                    echo '<div class="responsive-video-wrapper rounded overflow-hidden mb-3">';
                                                    echo '<video class="responsive-video" controls preload="metadata" poster="assets/video-poster.jpg">';
                                                    echo '<source src="' . $video['path_file'] . '" type="' . $video['tipe_file'] . '">';
                                                    echo 'Browser Anda tidak mendukung tag video.';
                                                    echo '</video>';
                                                    echo '</div>';
                                                    echo '<div class="video-info d-flex justify-content-between align-items-center px-2 mb-3">';
                                                    echo '<div class="video-name text-truncate">' . htmlspecialchars($video['nama_file']) . '</div>';
                                                    echo '<a href="' . $video['path_file'] . '" download class="btn btn-sm btn-light border" title="Download Video"><i class="bi bi-download"></i></a>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }

                                            // Tampilkan gambar jika ada
                                            if (!empty($images)) {
                                                $imageCount = count($images);
                                                echo '<div class="image-container-' . $imageCount . ' mt-2">';

                                                switch ($imageCount) {
                                                    case 1:
                                                        // Single image - full width
                                                        echo '<div class="single-image">';
                                                        echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '</div>';
                                                        break;

                                                    case 2:
                                                        // Two images side by side
                                                        echo '<div class="dual-images">';
                                                        foreach ($images as $image) {
                                                            echo '<img src="' . $image['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        }
                                                        echo '</div>';
                                                        break;

                                                    case 3:
                                                        // Two images top, one bottom
                                                        echo '<div class="triple-images">';
                                                        echo '<div class="top-images">';
                                                        echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '<img src="' . $images[1]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '</div>';
                                                        echo '<div class="bottom-image">';
                                                        echo '<img src="' . $images[2]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '</div>';
                                                        echo '</div>';
                                                        break;

                                                    case 4:
                                                        // Two rows of two images
                                                        echo '<div class="quad-images">';
                                                        echo '<div class="image-row">';
                                                        echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '<img src="' . $images[1]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '</div>';
                                                        echo '<div class="image-row">';
                                                        echo '<img src="' . $images[2]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '<img src="' . $images[3]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)">';
                                                        echo '</div>';
                                                        echo '</div>';
                                                        break;
                                                }
                                                echo '</div>';

                                                // Add CSS styles
                                                echo '<style>
                                            .single-image img {
                                                width: 100%;
                                                border-radius: 15px;
                                                cursor: pointer;
                                                height: 400px;
                                                object-fit: cover;
                                            }
                                            .dual-images {
                                                display: grid;
                                                grid-template-columns: 1fr 1fr;
                                                gap: 2px;
                                            }
                                            .dual-images img {
                                                width: 100%;
                                                height: 300px;
                                                object-fit: cover;
                                                cursor: pointer;
                                            }
                                            .dual-images img:first-child {
                                                border-radius: 15px 0 0 15px;
                                            }
                                            .dual-images img:last-child {
                                                border-radius: 0 15px 15px 0;
                                            }
                                            .triple-images .top-images {
                                                display: grid;
                                                grid-template-columns: 1fr 1fr;
                                                gap: 2px;
                                                margin-bottom: 2px;
                                            }
                                            .triple-images .top-images img {
                                                width: 100%;
                                                height: 200px;
                                                object-fit: cover;
                                                cursor: pointer;
                                            }
                                            .triple-images .top-images img:first-child {
                                                border-radius: 15px 0 0 0;
                                            }
                                            .triple-images .top-images img:last-child {
                                                border-radius: 0 15px 0 0;
                                            }
                                            .triple-images .bottom-image img {
                                                width: 100%;
                                                height: 300px;
                                                object-fit: cover;
                                                cursor: pointer;
                                                border-radius: 0 0 15px 15px;
                                            }
                                            .quad-images {
                                                display: grid;
                                                gap: 2px;
                                            }
                                            .quad-images .image-row {
                                                display: grid;
                                                grid-template-columns: 1fr 1fr;
                                                gap: 2px;
                                            }
                                            .quad-images img {
                                                width: 100%;
                                                height: 200px;
                                                object-fit: cover;
                                                cursor: pointer;
                                            }
                                            .quad-images .image-row:first-child img:first-child {
                                                border-radius: 15px 0 0 0;
                                            }
                                            .quad-images .image-row:first-child img:last-child {
                                                border-radius: 0 15px 0 0;
                                            }
                                            .quad-images .image-row:last-child img:first-child {
                                                border-radius: 0 0 0 15px;
                                            }
                                            .quad-images .image-row:last-child img:last-child {
                                                border-radius: 0 0 15px 0;
                                            }
                                            @media (max-width: 768px) {
                                                .single-image img {
                                                    height: 250px;
                                                }
                                                .dual-images img {
                                                    height: 200px;
                                                }
                                                .triple-images .top-images img {
                                                    height: 150px;
                                                }
                                                .triple-images .bottom-image img {
                                                    height: 200px;
                                                }
                                                .quad-images img {
                                                    height: 150px;
                                                }
                                            }
                                        </style>';
                                            }

                                            // Tampilkan dokumen non-gambar jika ada
                                            if (!empty($documents)) {
                                                echo '<div class="document-list">';
                                                foreach ($documents as $doc) {
                                                    $extension = pathinfo($doc['nama_file'], PATHINFO_EXTENSION);
                                                    $icon = '';

                                                    // Set icon berdasarkan tipe file
                                                    switch (strtolower($extension)) {
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
                                                        default:
                                                            $icon = 'bi-file-text-fill text-secondary';
                                                    }

                                                    echo '<div class="doc-item mb-2 p-2 bg-white d-flex align-items-center rounded border">';
                                                    echo '<a href="' . $doc['path_file'] . '" class="text-decoration-none text-dark d-flex align-items-center gap-2 flex-grow-1" target="_blank">';
                                                    echo '<i class="bi ' . $icon . ' fs-4"></i>';
                                                    echo '<div>';
                                                    echo '<div class="doc-name">' . htmlspecialchars($doc['nama_file']) . '</div>';
                                                    echo '<small class="text-muted">' . strtoupper($extension) . ' file</small>';
                                                    echo '</div>';
                                                    echo '</a>';
                                                    echo '<a href="' . $doc['path_file'] . '" class="text-decoration-none text-muted" download>';
                                                    echo '<i class="bi bi-download me-2"></i>';
                                                    echo '</a>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }

                                            echo '</div>';
                                        }
                                        ?>

                                        <style>
                                            .doc-item {
                                                transition: all 0.2s ease;
                                            }

                                            .doc-item:hover {
                                                background-color: #f8f9fa !important;
                                            }

                                            .doc-name {
                                                max-width: 200px;
                                                overflow: hidden;
                                                text-overflow: ellipsis;
                                                white-space: nowrap;
                                            }

                                            @media (max-width: 768px) {
                                                .doc-name {
                                                    max-width: 150px;
                                                }
                                            }
                                        </style>

                                        <!-- query untuk mendapatkan jumlah like, komen, dan status like user sedang login -->
                                        <?php
                                        // Query untuk mendapatkan jumlah dan detail reaksi
                                        $query_reactions = "SELECT emoji, COUNT(*) as count 
                                FROM emoji_reactions 
                                WHERE postingan_id = '{$post['id']}' 
                                GROUP BY emoji";
                                        $result_reactions = mysqli_query($koneksi, $query_reactions);

                                        $reactions_html = '';
                                        $total_reactions = 0;
                                        while ($reaction = mysqli_fetch_assoc($result_reactions)) {
                                            $reactions_html .= "{$reaction['emoji']} {$reaction['count']} ";
                                            $total_reactions += $reaction['count'];
                                        }

                                        // Cek reaksi user yang sedang login
                                        $check_reaction = "SELECT emoji FROM emoji_reactions 
                                WHERE postingan_id = '{$post['id']}' 
                                AND user_id = '$userid'";
                                        $reaction_result = mysqli_query($koneksi, $check_reaction);
                                        $user_reaction = mysqli_fetch_assoc($reaction_result);
                                        $current_emoji = $user_reaction ? $user_reaction['emoji'] : null;

                                        // Cek reaksi user yang sudah ada //button like
                                        $check_emoji = "SELECT emoji FROM emoji_reactions WHERE postingan_id = '{$post['id']}' AND user_id = '$userid'";
                                        $emoji_result = mysqli_query($koneksi, $check_emoji);
                                        $user_emoji = mysqli_fetch_assoc($emoji_result);
                                        $current_emoji = $user_emoji ? $user_emoji['emoji'] : null;

                                        // Query untuk mendapatkan jumlah komentar
                                        $query_comment_count = "SELECT COUNT(*) as total FROM komentar_postingan WHERE postingan_id = '{$post['id']}'";
                                        $result_comment_count = mysqli_query($koneksi, $query_comment_count);
                                        $comment_count = mysqli_fetch_assoc($result_comment_count)['total'];
                                        ?>

                                        <!-- informasi like dan komen -->
                                        <div class="d-flex gap-2 mt-3" style="font-size: 14px;">
                                            <div class="badge rounded-pill bg-light border px-3 py-2" style="cursor: pointer;" onclick="showReactionUsers(<?php echo $post['id']; ?>)">
                                                <span id="reactions-count-<?php echo $post['id']; ?>" class="reactions-count text-black">
                                                    <?php echo $reactions_html ?: "<i class='bi bi-hand-thumbs-up me-1'></i>$total_reactions"; ?>
                                                </span>
                                            </div>
                                            <div class="badge rounded-pill bg-light text-black border px-3 py-2">
                                                <i class="bi bi-chat me-1"></i>
                                                <span><strong><?php echo $comment_count; ?></strong></span>
                                            </div>
                                        </div>


                                        <!-- Ganti bagian tombol like dengan yang lebih sederhana -->
                                        <div class="d-flex gap-2 justify-content-between mt-3 ps-2 pe-2" style="font-size: 14px;">
                                            <button class="btn btn-light flex-fill py-1 py-md-2 d-flex align-items-center justify-content-center gap-2"
                                                id="like-btn-<?php echo $post['id']; ?>"
                                                onclick="toggleLike(<?php echo $post['id']; ?>)">
                                                <?php if ($current_emoji): ?>
                                                    <i class="bi bi-hand-thumbs-up-fill" style="color: rgb(218, 119, 86);"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-hand-thumbs-up"></i>
                                                <?php endif; ?>
                                                <span class="d-none d-md-inline">Suka</span>
                                            </button>

                                            <button class="btn btn-light flex-fill py-1 py-md-2 d-flex align-items-center justify-content-center gap-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#commentModal-<?php echo $post['id']; ?>">
                                                <i class="bi bi-chat"></i>
                                                <span class="d-none d-md-inline">Komentar</span>
                                            </button>

                                            <button class="btn btn-light flex-fill py-1 py-md-2 d-flex align-items-center justify-content-center gap-2"
                                                onclick='sharePost(<?php echo $post["id"]; ?>, <?php echo json_encode($post["konten"]); ?>)'>
                                                <i class="bi bi-share"></i>
                                                <span class="d-none d-md-inline">Bagikan</span>
                                            </button>
                                        </div>

                                        <!-- script dan animasi aksi like dan emoji -->

                                        <style>
                                            .reaction-bar {
                                                /* Style yang sudah ada */
                                                position: absolute;
                                                bottom: 100%;
                                                left: 50%;
                                                transform: translateX(-50%);
                                                background: white;
                                                border-radius: 20px;
                                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                                margin-bottom: 10px;
                                                z-index: 1000;
                                                width: max-content;
                                                min-width: 200px;

                                                /* Tambahkan animasi */
                                                opacity: 0;
                                                transform: translateX(-50%) scale(0.5);
                                                transition: all 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
                                            }

                                            .reaction-bar.show {
                                                opacity: 1;
                                                transform: translateX(-50%) scale(1);
                                            }

                                            .reaction-emoji {
                                                cursor: pointer;
                                                padding: 5px 10px;
                                                font-size: 1.5rem;
                                                opacity: 0;
                                                transform: translateY(20px);
                                                transition: all 0.2s ease-out;
                                            }

                                            /* Animasi untuk setiap emoji */
                                            .reaction-bar.show .reaction-emoji:nth-child(1) {
                                                transition-delay: 0.1s;
                                            }

                                            .reaction-bar.show .reaction-emoji:nth-child(2) {
                                                transition-delay: 0.15s;
                                            }

                                            .reaction-bar.show .reaction-emoji:nth-child(3) {
                                                transition-delay: 0.2s;
                                            }

                                            .reaction-bar.show .reaction-emoji:nth-child(4) {
                                                transition-delay: 0.25s;
                                            }

                                            .reaction-bar.show .reaction-emoji:nth-child(5) {
                                                transition-delay: 0.3s;
                                            }

                                            .reaction-bar.show .reaction-emoji {
                                                opacity: 1;
                                                transform: translateY(0);
                                            }

                                            .reaction-emoji:hover {
                                                transform: scale(1.3);
                                            }
                                        </style>

                                        <script>
                                            // suara like
                                            if (typeof likeSound === 'undefined') {
                                                const likeSound = new Audio('assets/like_rev.mp3');
                                            }

                                            function updateReactionDisplay(postId, reactions, currentEmoji) {
                                                const button = document.getElementById(`like-btn-${postId}`);
                                                const countElement = document.getElementById(`like-count-${postId}`);
                                                const reactionBar = document.getElementById(`reaction-bar-${postId}`);

                                                let totalCount = 0;
                                                let displayText = '';

                                                for (const [emoji, count] of Object.entries(reactions)) {
                                                    totalCount += count;
                                                    if (count > 0) {
                                                        displayText += `${emoji} ${count} `;
                                                    }
                                                }

                                                const buttonText = button.querySelector('span');
                                                if (currentEmoji) {
                                                    if (buttonText) {
                                                        buttonText.textContent = `${currentEmoji} Suka`;
                                                    }
                                                    button.querySelector('i').classList.add('text-primary');
                                                } else {
                                                    if (buttonText) {
                                                        buttonText.textContent = 'Suka';
                                                    }
                                                    button.querySelector('i').classList.remove('text-primary');
                                                }

                                                countElement.innerHTML = displayText || `${totalCount} Suka`;

                                                reactionBar.classList.remove('show');
                                                setTimeout(() => {
                                                    reactionBar.style.display = 'none';
                                                }, 300);
                                            }

                                            function showReactionBar(event, postId) {
                                                event.preventDefault();
                                                const reactionBar = document.getElementById(`reaction-bar-${postId}`);

                                                document.querySelectorAll('.reaction-bar').forEach(bar => {
                                                    if (bar.id !== `reaction-bar-${postId}`) {
                                                        bar.classList.remove('show');
                                                        setTimeout(() => {
                                                            bar.style.display = 'none';
                                                        }, 300);
                                                    }
                                                });

                                                if (reactionBar.style.display === 'none') {
                                                    reactionBar.style.display = 'block';
                                                    requestAnimationFrame(() => {
                                                        reactionBar.classList.add('show');
                                                    });

                                                    setTimeout(() => {
                                                        document.addEventListener('click', function closeReactionBar(e) {
                                                            if (!reactionBar.contains(e.target) &&
                                                                !document.getElementById(`like-btn-${postId}`).contains(e.target)) {
                                                                reactionBar.classList.remove('show');
                                                                setTimeout(() => {
                                                                    reactionBar.style.display = 'none';
                                                                }, 300);
                                                                document.removeEventListener('click', closeReactionBar);
                                                            }
                                                        });
                                                    }, 0);
                                                } else {
                                                    reactionBar.classList.remove('show');
                                                    setTimeout(() => {
                                                        reactionBar.style.display = 'none';
                                                    }, 300);
                                                }
                                            }

                                            function toggleLike(postId, emoji = '👍') {
                                                const button = document.getElementById(`like-btn-${postId}`);
                                                const icon = button.querySelector('i');

                                                fetch('toggle_like.php', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                        },
                                                        body: `postingan_id=${postId}&emoji=${emoji}`
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.success) {
                                                            // Update icon
                                                            if (data.is_liked) {
                                                                icon.classList.replace('bi-hand-thumbs-up', 'bi-hand-thumbs-up-fill');
                                                                icon.style.color = 'rgb(218, 119, 86)'; // Changed from text-primary to direct color

                                                                // Smooth scale up animation for like
                                                                icon.style.transition = 'transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28)';
                                                                icon.style.transform = 'scale(1.5)';
                                                                setTimeout(() => {
                                                                    icon.style.transform = 'scale(1)';
                                                                }, 300);

                                                                // Play like sound
                                                                likeSound.play().catch(error => console.log('Error playing sound:', error));
                                                            } else {
                                                                icon.classList.replace('bi-hand-thumbs-up-fill', 'bi-hand-thumbs-up');
                                                                icon.style.color = ''; // Reset color to default

                                                                // Push effect animation for unlike
                                                                icon.style.transition = 'transform 0.2s ease-in-out';
                                                                icon.style.transform = 'translateX(-4px)';
                                                                setTimeout(() => {
                                                                    icon.style.transform = 'translateX(4px)';
                                                                }, 100);
                                                                setTimeout(() => {
                                                                    icon.style.transform = 'translateX(0)';
                                                                }, 200);
                                                            }

                                                            // Update reaction display
                                                            const countElement = document.getElementById(`reactions-count-${postId}`);
                                                            if (data.reactions) {
                                                                let displayText = '';
                                                                for (const [emoji, count] of Object.entries(data.reactions)) {
                                                                    if (count > 0) {
                                                                        displayText += `${emoji} ${count} `;
                                                                    }
                                                                }
                                                                countElement.innerHTML = displayText || `<i class='bi bi-hand-thumbs-up me-1'></i>0`;
                                                            }
                                                        }
                                                    })
                                                    .catch(error => console.error('Error:', error));
                                            }
                                        </script>



                                        <!-- script untuk berbagi -->
                                        <script>
                                            async function sharePost(postId, content) {
                                                // Buat URL untuk postingan
                                                const postUrl = `${window.location.origin}/smagaBelajar/kelas_guru.php?id=${getUrlParameter('id')}&post=${postId}`;

                                                // Potong konten jika terlalu panjang
                                                const shortContent = content.length > 100 ? content.substring(0, 97) + '...' : content;

                                                // Cek apakah Web Share API tersedia (biasanya di mobile)
                                                if (navigator.share) {
                                                    try {
                                                        await navigator.share({
                                                            title: 'Bagikan Postingan',
                                                            text: shortContent,
                                                            url: postUrl
                                                        });
                                                    } catch (err) {
                                                        console.log('Error sharing:', err);
                                                    }
                                                } else {
                                                    // Jika Web Share API tidak tersedia (desktop), gunakan modal
                                                    showShareModal(postId, postUrl);
                                                }
                                            }

                                            function showShareModal(postId, postUrl) {
                                                // Cek apakah modal sudah ada
                                                let shareModal = document.getElementById(`shareModal-${postId}`);

                                                if (!shareModal) {
                                                    // Buat modal jika belum ada
                                                    const modalHtml = `
                                            <div class="modal fade" id="shareModal-${postId}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header border-0">
                                                            <h5 class="modal-title">Bagikan Postingan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="d-flex flex-column gap-2">
                                                                <button onclick="shareToWhatsApp('${postUrl}')" class="btn btn-success d-flex align-items-center gap-2">
                                                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                                                </button>
                                                                <button onclick="shareToTelegram('${postUrl}')" class="btn btn-primary d-flex align-items-center gap-2">
                                                                    <i class="bi bi-telegram"></i> Telegram
                                                                </button>
                                                                <button onclick="copyLink('${postUrl}')" class="btn btn-secondary d-flex align-items-center gap-2">
                                                                    <i class="bi bi-link-45deg"></i> Salin Link
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                                                    shareModal = document.getElementById(`shareModal-${postId}`);
                                                }

                                                // Tampilkan modal
                                                new bootstrap.Modal(shareModal).show();
                                            }

                                            // Fungsi helper untuk mendapatkan parameter dari URL
                                            function getUrlParameter(name) {
                                                const params = new URLSearchParams(window.location.search);
                                                return params.get(name);
                                            }

                                            // Fungsi untuk berbagi ke platform spesifik
                                            function shareToWhatsApp(url) {
                                                window.open(`https://wa.me/?text=${encodeURIComponent(url)}`, '_blank');
                                            }

                                            function shareToTelegram(url) {
                                                window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}`, '_blank');
                                            }

                                            function copyLink(url) {
                                                navigator.clipboard.writeText(url).then(() => {
                                                    // Tampilkan toast atau alert bahwa link berhasil disalin
                                                    alert('Link berhasil disalin!');
                                                });
                                            }
                                        </script>

                                        <!-- style modal berbagi -->
                                        <style>
                                            .share-option {
                                                transition: all 0.2s ease;
                                            }

                                            .share-option:hover {
                                                transform: translateY(-2px);
                                            }

                                            /* Animasi untuk toast */
                                            .toast {
                                                position: fixed;
                                                bottom: 20px;
                                                right: 20px;
                                                z-index: 1050;
                                            }

                                            @media (max-width: 768px) {
                                                .toast {
                                                    left: 20px;
                                                    right: 20px;
                                                }
                                            }
                                        </style>
                                        <!-- modal komentar -->
                                        <div class="modal fade" id="commentModal-<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="modalKomentar" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                                                <div class="modal-content">
                                                    <?php
                                                    // Query untuk mengambil komentar di awal
                                                    $query_komentar = "SELECT k.*, 
                                                                CASE 
                                                                    WHEN g.username IS NOT NULL THEN 'guru'
                                                                    ELSE 'siswa'
                                                                END as user_type,
                                                                g.foto_profil as foto_guru,
                                                                s.foto_profil as foto_siswa,
                                                                COALESCE(g.namaLengkap, s.nama) as nama_user 
                                                                FROM komentar_postingan k 
                                                                LEFT JOIN guru g ON k.user_id = g.username 
                                                                LEFT JOIN siswa s ON k.user_id = s.username 
                                                                WHERE k.postingan_id = '{$post['id']}' 
                                                                ORDER BY k.created_at ASC";
                                                    $result_komentar = mysqli_query($koneksi, $query_komentar);
                                                    ?>

                                                    <div class="modal-header border-0">
                                                        <h1 class="modal-title fs-5" id="modalKomentar" style="z-index: 1; ">
                                                            <div class="d-flex flex-column">
                                                                <strong>Komentar</strong>
                                                                <span class="text-muted fs-6" style="font-size: 12px !important;">Total <?php echo mysqli_num_rows($result_komentar); ?> Komentar</span>
                                                            </div>
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <!-- Body Komentar dengan Scroll -->
                                                    <div class="modal-body p-0">
                                                        <div class="komentar-container px-3" style="overflow-y: auto;">
                                                            <?php
                                                            if (mysqli_num_rows($result_komentar) > 0) {
                                                                while ($komentar = mysqli_fetch_assoc($result_komentar)) {
                                                            ?>
                                                                    <div class="comment-thread mb-3">
                                                                        <!-- Main comment -->
                                                                        <div class="d-flex gap-3">
                                                                            <div class="flex-shrink-0">
                                                                                <?php if ($komentar['user_type'] == 'guru'): ?>
                                                                                    <img src="<?php
                                                                                                if (!empty($komentar['foto_guru'])) {
                                                                                                    echo 'uploads/profil/' . $komentar['foto_guru'];
                                                                                                } else {
                                                                                                    echo 'assets/pp.png';
                                                                                                }
                                                                                                ?>"
                                                                                        alt="Teacher Profile"
                                                                                        class="profile-img rounded-4 border-0 bg-white"
                                                                                        style="width: 32px; height: 32px;">
                                                                                <?php else: ?>
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
                                                                                                ?>"
                                                                                        alt="Student Profile"
                                                                                        class="profile-img rounded-4 border-0 bg-white"
                                                                                        style="width: 32px; height: 32px;">
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <div class="comment-bubble p-2 rounded-3" style="background-color: #f0f2f5;">
                                                                                    <div class="fw-semibold" style="font-size: 13px;">
                                                                                        <?php echo htmlspecialchars($komentar['nama_user']); ?>
                                                                                    </div>
                                                                                    <div style="font-size: 13px;">
                                                                                        <?php echo nl2br(htmlspecialchars($komentar['konten'])); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comment-actions d-flex gap-3 mt-1" style="font-size: 12px; opacity: 1;">
                                                                                    <div class="comment-reactions position-relative">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <button class="btn btn-sm text-muted p-0"
                                                                                                id="comment-like-btn-<?php echo $komentar['id']; ?>"
                                                                                                onclick="showCommentReactionBar(event, <?php echo $komentar['id']; ?>)">
                                                                                                <?php
                                                                                                // Get user's current reaction
                                                                                                $user_reaction_query = "SELECT emoji FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
                                                                                                $stmt = mysqli_prepare($koneksi, $user_reaction_query);
                                                                                                mysqli_stmt_bind_param($stmt, "is", $komentar['id'], $_SESSION['userid']);
                                                                                                mysqli_stmt_execute($stmt);
                                                                                                $user_reaction = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                                                                                                if ($user_reaction) {
                                                                                                    echo "<p class='p-0 m-0' style='font-size: 12px;'>{$user_reaction['emoji']} ";
                                                                                                    switch ($user_reaction['emoji']) {
                                                                                                        case '👍':
                                                                                                            echo "Ok";
                                                                                                            break;
                                                                                                        case '❤️':
                                                                                                            echo "Cinta";
                                                                                                            break;
                                                                                                        case '😂':
                                                                                                            echo "Wkwk";
                                                                                                            break;
                                                                                                        case '😮':
                                                                                                            echo "GG";
                                                                                                            break;
                                                                                                        case '😢':
                                                                                                            echo "Ya Allah";
                                                                                                            break;
                                                                                                    }
                                                                                                    echo "</p>";
                                                                                                } else {
                                                                                                    echo "<p class='p-0 m-0' style='font-size: 12px;'>Suka</p>";
                                                                                                }
                                                                                                ?>
                                                                                            </button>

                                                                                            <!-- reaction bar -->
                                                                                            <!-- Add this right after the button -->
                                                                                            <div id="comment-reaction-bar-<?php echo $komentar['id']; ?>"
                                                                                                class="reaction-bar-comment"
                                                                                                style="display: none;">
                                                                                                <div class="d-flex justify-content-around p-2">
                                                                                                    <span onclick="toggleCommentReaction(<?php echo $komentar['id']; ?>, '👍')" class="reaction-emoji">👍</span>
                                                                                                    <span onclick="toggleCommentReaction(<?php echo $komentar['id']; ?>, '❤️')" class="reaction-emoji">❤️</span>
                                                                                                    <span onclick="toggleCommentReaction(<?php echo $komentar['id']; ?>, '😂')" class="reaction-emoji">😂</span>
                                                                                                    <span onclick="toggleCommentReaction(<?php echo $komentar['id']; ?>, '😮')" class="reaction-emoji">😮</span>
                                                                                                    <span onclick="toggleCommentReaction(<?php echo $komentar['id']; ?>, '😢')" class="reaction-emoji">😢</span>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Reaction counts -->
                                                                                            <?php
                                                                                            $count_query = "SELECT emoji, COUNT(*) as count FROM comment_reactions WHERE comment_id = ? GROUP BY emoji";
                                                                                            $stmt = mysqli_prepare($koneksi, $count_query);
                                                                                            mysqli_stmt_bind_param($stmt, "i", $komentar['id']);
                                                                                            mysqli_stmt_execute($stmt);
                                                                                            $reactions = mysqli_stmt_get_result($stmt);

                                                                                            if (mysqli_num_rows($reactions) > 0) {
                                                                                            ?>
                                                                                                <div class='ms-2 reaction-counts position-relative'>
                                                                                                    <div class='d-flex align-items-center' onclick="toggleReactionPopover(<?php echo $komentar['id']; ?>)">
                                                                                                        <?php
                                                                                                        $total = 0;
                                                                                                        $emoji_stack = [];
                                                                                                        while ($row = mysqli_fetch_assoc($reactions)) {
                                                                                                            $total += $row['count'];
                                                                                                            $emoji_stack[] = $row['emoji'];
                                                                                                        }
                                                                                                        foreach (array_slice($emoji_stack, 0, 3) as $emoji) {
                                                                                                            echo "<span class='reaction-icon'>$emoji</span>";
                                                                                                        }
                                                                                                        echo "<span class='ms-1 reaction-count'>$total</span>";
                                                                                                        ?>
                                                                                                    </div>

                                                                                                    <!-- Popover for reactions -->
                                                                                                    <div id="reaction-popover-<?php echo $komentar['id']; ?>" class="reaction-popover" style="z-index: 1080 !important;">
                                                                                                        <div id="reaction-content-<?php echo $komentar['id']; ?>" class="p-2">
                                                                                                            <!-- Content will be loaded here -->
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                                            <?php
                                                                                            }
                                                                                            ?>

                                                                                        </div>
                                                                                    </div>


                                                                                    <!-- Style for reaction bar -->
                                                                                    <style>
                                                                                        .comment-reactions {
                                                                                            position: relative;
                                                                                        }

                                                                                        .reaction-bar-comment {
                                                                                            position: absolute;
                                                                                            top: -40px;
                                                                                            left: 0;
                                                                                            background: white;
                                                                                            border-radius: 20px;
                                                                                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                                                                            z-index: 1000;
                                                                                            width: max-content;
                                                                                            min-width: 200px;
                                                                                            opacity: 0;
                                                                                            transform: translateY(10px);
                                                                                            transition: all 0.3s ease;
                                                                                        }

                                                                                        .reaction-bar-comment.show {
                                                                                            opacity: 1;
                                                                                            transform: translateY(0);
                                                                                        }

                                                                                        .reaction-emoji {
                                                                                            cursor: pointer;
                                                                                            padding: 5px 10px;
                                                                                            font-size: 1.2rem;
                                                                                            transition: all 0.2s ease;
                                                                                            opacity: 0;
                                                                                            transform: translateY(10px);
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji {
                                                                                            opacity: 1;
                                                                                            transform: translateY(0);
                                                                                        }

                                                                                        .reaction-emoji:hover {
                                                                                            transform: scale(1.4);
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji:nth-child(1) {
                                                                                            transition-delay: 0.1s;
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji:nth-child(2) {
                                                                                            transition-delay: 0.15s;
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji:nth-child(3) {
                                                                                            transition-delay: 0.2s;
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji:nth-child(4) {
                                                                                            transition-delay: 0.25s;
                                                                                        }

                                                                                        .reaction-bar-comment.show .reaction-emoji:nth-child(5) {
                                                                                            transition-delay: 0.3s;
                                                                                        }

                                                                                        .reaction-counts {
                                                                                            display: flex;
                                                                                            align-items: center;
                                                                                            background: #f0f2f5;
                                                                                            padding: 2px 8px;
                                                                                            border-radius: 10px;
                                                                                            cursor: pointer;
                                                                                        }

                                                                                        .reaction-icon {
                                                                                            margin-left: -4px;
                                                                                            font-size: 12px;
                                                                                        }

                                                                                        .reaction-count {
                                                                                            font-size: 12px;
                                                                                            color: #65676b;
                                                                                        }

                                                                                        .reaction-icon:first-child {
                                                                                            margin-left: 0;
                                                                                        }
                                                                                    </style>

                                                                                    <!-- style untuk popup emoji -->
                                                                                    <style>
                                                                                        .reaction-popover {
                                                                                            position: fixed;
                                                                                            top: 50%;
                                                                                            left: 50%;
                                                                                            transform: translate(-50%, -50%);
                                                                                            background: white;
                                                                                            border-radius: 12px;
                                                                                            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
                                                                                            width: 250px;
                                                                                            max-height: 300px;
                                                                                            overflow-y: auto;
                                                                                            z-index: 1080;
                                                                                            opacity: 0;
                                                                                            visibility: hidden;
                                                                                            transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
                                                                                        }

                                                                                        /* Add backdrop styling with fade */
                                                                                        .popover-backdrop {
                                                                                            position: fixed;
                                                                                            top: 0;
                                                                                            left: 0;
                                                                                            right: 0;
                                                                                            bottom: 0;
                                                                                            background: rgba(0, 0, 0, 0.5) !important;
                                                                                            z-index: 1070;
                                                                                            opacity: 0;
                                                                                            visibility: hidden;
                                                                                            transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
                                                                                        }

                                                                                        /* Modal backdrop darker */
                                                                                        .modal-backdrop {
                                                                                            background-color: rgba(0, 0, 0, 0.7) !important;
                                                                                        }

                                                                                        .reaction-popover .p-2 {
                                                                                            padding: 8px !important;
                                                                                        }

                                                                                        .reaction-popover h6 {
                                                                                            font-size: 13px;
                                                                                            color: #666;
                                                                                            margin: 0;
                                                                                        }

                                                                                        /* Clean scrollbar */
                                                                                        .reaction-popover::-webkit-scrollbar {
                                                                                            width: 4px;
                                                                                        }

                                                                                        .reaction-popover::-webkit-scrollbar-track {
                                                                                            background: transparent;
                                                                                        }

                                                                                        .reaction-popover::-webkit-scrollbar-thumb {
                                                                                            background: #ddd;
                                                                                            border-radius: 4px;
                                                                                        }

                                                                                        /* Reaction groups styling */
                                                                                        .reaction-group {
                                                                                            padding: 4px 0;
                                                                                        }

                                                                                        .reaction-group-header {
                                                                                            display: flex;
                                                                                            align-items: center;
                                                                                            gap: 6px;
                                                                                            padding: 4px 8px;
                                                                                        }

                                                                                        .reaction-group-emoji {
                                                                                            font-size: 16px;
                                                                                        }

                                                                                        .reaction-group-count {
                                                                                            font-size: 12px;
                                                                                            color: #888;
                                                                                        }

                                                                                        /* User list */
                                                                                        .reaction-user {
                                                                                            display: flex;
                                                                                            align-items: center;
                                                                                            padding: 6px 8px;
                                                                                            transition: all 0.2s;
                                                                                        }

                                                                                        .reaction-user:hover {
                                                                                            background-color: #f8f8f8;
                                                                                        }

                                                                                        .reaction-user img {
                                                                                            width: 28px;
                                                                                            height: 28px;
                                                                                            border-radius: 50%;
                                                                                            margin-right: 8px;
                                                                                        }

                                                                                        .reaction-user-name {
                                                                                            font-size: 13px;
                                                                                            color: #333;
                                                                                        }

                                                                                        /* Show states for fade effect */
                                                                                        .reaction-popover.show {
                                                                                            opacity: 1;
                                                                                            visibility: visible;
                                                                                        }

                                                                                        .popover-backdrop.show {
                                                                                            opacity: 1;
                                                                                            visibility: visible;
                                                                                        }
                                                                                    </style>

                                                                                    <!-- script suka komentar -->
                                                                                    <script>
                                                                                        // Add audio element for reaction sound
                                                                                        const reactionSound = new Audio('assets/like_rev.mp3');

                                                                                        function showCommentReactionBar(event, commentId) {
                                                                                            event.preventDefault();
                                                                                            event.stopPropagation();
                                                                                            const reactionBar = document.getElementById(`comment-reaction-bar-${commentId}`);

                                                                                            document.querySelectorAll('.reaction-bar-comment.show').forEach(bar => {
                                                                                                if (bar.id !== `comment-reaction-bar-${commentId}`) {
                                                                                                    closeReactionBar(bar);
                                                                                                }
                                                                                            });

                                                                                            if (reactionBar.style.display === 'none') {
                                                                                                reactionBar.style.display = 'block';
                                                                                                requestAnimationFrame(() => {
                                                                                                    reactionBar.classList.add('show');
                                                                                                });

                                                                                                setTimeout(() => {
                                                                                                    document.addEventListener('click', closeOnClickOutside);
                                                                                                }, 0);
                                                                                            } else {
                                                                                                closeReactionBar(reactionBar);
                                                                                            }
                                                                                        }

                                                                                        function closeReactionBar(reactionBar) {
                                                                                            reactionBar.classList.remove('show');
                                                                                            setTimeout(() => {
                                                                                                reactionBar.style.display = 'none';
                                                                                            }, 300);
                                                                                            document.removeEventListener('click', closeOnClickOutside);
                                                                                        }

                                                                                        function closeOnClickOutside(event) {
                                                                                            const openBars = document.querySelectorAll('.reaction-bar-comment.show');
                                                                                            openBars.forEach(bar => {
                                                                                                if (!bar.contains(event.target) &&
                                                                                                    !event.target.closest('.btn-sm')) {
                                                                                                    closeReactionBar(bar);
                                                                                                }
                                                                                            });
                                                                                        }

                                                                                        document.querySelectorAll('.reaction-bar-comment').forEach(bar => {
                                                                                            bar.addEventListener('click', (e) => e.stopPropagation());
                                                                                        });

                                                                                        function toggleCommentReaction(commentId, emoji) {
                                                                                            fetch('toggle_comment_reaction.php', {
                                                                                                    method: 'POST',
                                                                                                    headers: {
                                                                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                                                                    },
                                                                                                    body: `comment_id=${commentId}&emoji=${emoji}`
                                                                                                })
                                                                                                .then(response => response.json())
                                                                                                .then(data => {
                                                                                                    if (data.success) {
                                                                                                        const button = document.getElementById(`comment-like-btn-${commentId}`);
                                                                                                        const reactionBar = document.getElementById(`comment-reaction-bar-${commentId}`);

                                                                                                        // Play sound effect when adding reaction
                                                                                                        reactionSound.play().catch(error => console.log('Error playing sound:', error));

                                                                                                        let reactionText;
                                                                                                        switch (emoji) {
                                                                                                            case '👍':
                                                                                                                reactionText = 'Ok';
                                                                                                                break;
                                                                                                            case '❤️':
                                                                                                                reactionText = 'Cinta';
                                                                                                                break;
                                                                                                            case '😂':
                                                                                                                reactionText = 'Wkwk';
                                                                                                                break;
                                                                                                            case '😮':
                                                                                                                reactionText = 'GG';
                                                                                                                break;
                                                                                                            case '😢':
                                                                                                                reactionText = 'Ya Allah';
                                                                                                                break;
                                                                                                            default:
                                                                                                                reactionText = 'Suka';
                                                                                                        }

                                                                                                        button.innerHTML = `<p class="p-0 m-0" style="font-size: 12px; me-2 ">${emoji} ${reactionText}</p>`;
                                                                                                        closeReactionBar(reactionBar);
                                                                                                    }
                                                                                                });
                                                                                        }

                                                                                        function toggleReactionPopover(commentId) {
                                                                                            const popover = document.getElementById(`reaction-popover-${commentId}`);
                                                                                            const content = document.getElementById(`reaction-content-${commentId}`);

                                                                                            document.querySelectorAll('.reaction-popover.show').forEach(p => {
                                                                                                if (p.id !== `reaction-popover-${commentId}`) {
                                                                                                    p.classList.remove('show');
                                                                                                }
                                                                                            });

                                                                                            if (!popover.classList.contains('show')) {
                                                                                                fetch(`get_reaction_details.php?comment_id=${commentId}`)
                                                                                                    .then(response => response.text())
                                                                                                    .then(html => {
                                                                                                        content.innerHTML = html;
                                                                                                        popover.classList.add('show');
                                                                                                        setTimeout(() => {
                                                                                                            document.addEventListener('click', closePopoverOutside);
                                                                                                        }, 0);
                                                                                                    });
                                                                                            } else {
                                                                                                popover.classList.remove('show');
                                                                                                document.removeEventListener('click', closePopoverOutside);
                                                                                            }
                                                                                        }

                                                                                        function closePopoverOutside(event) {
                                                                                            const popover = document.querySelector('.reaction-popover.show');
                                                                                            if (popover && !popover.contains(event.target) &&
                                                                                                !event.target.closest('.reaction-counts')) {
                                                                                                popover.classList.remove('show');
                                                                                                document.removeEventListener('click', closePopoverOutside);
                                                                                            }
                                                                                        }
                                                                                    </script>
                                                                                    <!-- script untuk  -->
                                                                                    <button class="btn btn-sm p-0 text-muted text-decoration-none" onclick="replyToComment(<?php echo $komentar['id']; ?>, '<?php echo $komentar['nama_user']; ?>', <?php echo $post['id']; ?>)">
                                                                                        <p class="p-0 m-0" style="font-size: 12px;">Balas</p>
                                                                                    </button>
                                                                                </div>

                                                                                <!-- Reply section -->
                                                                                <div class="replies-section mt-2">
                                                                                    <?php
                                                                                    $query_replies = "SELECT r.*, COALESCE(g.namaLengkap, s.nama) as nama_user,
                                                                            CASE WHEN g.username IS NOT NULL THEN g.foto_profil ELSE s.foto_profil END as foto_profil,
                                                                            CASE WHEN g.username IS NOT NULL THEN 'guru' ELSE 'siswa' END as user_type
                                                                            FROM komentar_replies r
                                                                            LEFT JOIN guru g ON r.user_id = g.username
                                                                            LEFT JOIN siswa s ON r.user_id = s.username
                                                                            WHERE r.komentar_id = {$komentar['id']}
                                                                            ORDER BY r.created_at ASC";
                                                                                    $result_replies = mysqli_query($koneksi, $query_replies);
                                                                                    while ($reply = mysqli_fetch_assoc($result_replies)) {
                                                                                    ?>
                                                                                        <div class="d-flex gap-2 mb-2">
                                                                                            <div class="flex-shrink-0">
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
                                                                                                            ?>" alt="Profile Image"
                                                                                                    class="profile-img rounded-4 border-0 bg-white"
                                                                                                    style="width: 35px;">

                                                                                            </div>
                                                                                            <div class="flex-grow-1">
                                                                                                <div class="reply-bubble p-2 rounded-3" style="background-color: #f0f2f5; font-size: 12px;">
                                                                                                    <div class="fw-semibold">
                                                                                                        <?php echo htmlspecialchars($reply['nama_user']); ?>
                                                                                                    </div>
                                                                                                    <?php echo nl2br(htmlspecialchars($reply['konten'])); ?>
                                                                                                </div>
                                                                                                <div class="reply-actions mt-1" style="font-size: 11px;">
                                                                                                    <button class="btn btn-sm p-0 text-muted me-2">Reaksi</button>
                                                                                                    <button class="btn btn-sm p-0 text-muted">Balas</button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                <?php
                                                                }
                                                            } else {
                                                                ?>

                                                                <div class="text-center py-4">
                                                                    <div class="mb-3">
                                                                        <i class="fas fa-comments text-muted" style="font-size: 48px;"></i>
                                                                    </div>
                                                                    <p class="text-muted">Belum ada komentar</p>
                                                                </div>
                                                            <?php
                                                            }
                                                            ?>
                                                        </div>

                                                        <style>
                                                            .replies-section {
                                                                margin-left: 32px;
                                                            }

                                                            .comment-bubble,
                                                            .reply-bubble {
                                                                display: inline-block;
                                                                max-width: 100%;
                                                                word-wrap: break-word;
                                                            }

                                                            .comment-actions,
                                                            .reply-actions {
                                                                opacity: 1;
                                                            }

                                                            .comment-actions:hover,
                                                            .reply-actions:hover {
                                                                opacity: 1;
                                                            }

                                                            .reply-bubble {
                                                                background-color: #f0f2f5;
                                                            }
                                                        </style>


                                                        <script>
                                                            function replyToComment(commentId, userName) {
                                                                const textarea = document.querySelector(`#commentModal-${postId} textarea`);
                                                                textarea.value = `@${userName} `;
                                                                textarea.focus();

                                                                // Store the comment ID for reply
                                                                textarea.dataset.replyTo = commentId;
                                                            }

                                                            function submitComment(postId) {
                                                                const form = document.querySelector(`#commentModal-${postId} .komentar-form`);
                                                                const textarea = form.querySelector('textarea');
                                                                const replyTo = textarea.dataset.replyTo;

                                                                const data = new FormData();
                                                                data.append('postingan_id', postId);
                                                                data.append('konten', textarea.value);

                                                                const endpoint = replyTo ? 'tambah_balasan.php' : 'tambah_komentar.php';
                                                                if (replyTo) {
                                                                    data.append('komentar_id', replyTo);
                                                                }

                                                                fetch(endpoint, {
                                                                        method: 'POST',
                                                                        body: data
                                                                    })
                                                                    .then(response => response.json())
                                                                    .then(data => {
                                                                        if (data.status === 'success') {
                                                                            location.reload();
                                                                        }
                                                                    });
                                                            }
                                                        </script>
                                                        <script>
                                                            // Untuk currentPostId
                                                            if (typeof currentPostId === 'undefined') {
                                                                let currentPostId;
                                                            }

                                                            function replyToComment(commentId, userName, postId) {
                                                                currentPostId = postId;
                                                                const textarea = document.querySelector(`#commentModal-${postId} textarea`);
                                                                textarea.value = `@${userName} `;
                                                                textarea.focus();
                                                                textarea.dataset.replyTo = commentId;
                                                            }

                                                            function toggleReaction(commentId) {
                                                                const reactionBar = document.createElement('div');
                                                                reactionBar.className = 'reaction-bar bg-white shadow rounded p-2';
                                                                reactionBar.innerHTML = `
                                                    <div class="d-flex gap-2">
                                                        <span onclick="addReaction(${commentId}, '👍')" class="reaction-emoji">👍</span>
                                                        <span onclick="addReaction(${commentId}, '❤️')" class="reaction-emoji">❤️</span>
                                                        <span onclick="addReaction(${commentId}, '😊')" class="reaction-emoji">😊</span>
                                                        <span onclick="addReaction(${commentId}, '😢')" class="reaction-emoji">😢</span>
                                                    </div>
                                                `;

                                                                const button = event.currentTarget;
                                                                if (button.nextElementSibling?.classList.contains('reaction-bar')) {
                                                                    button.nextElementSibling.remove();
                                                                } else {
                                                                    button.parentElement.insertBefore(reactionBar, button.nextElementSibling);
                                                                }
                                                            }

                                                            function addReaction(commentId, emoji) {
                                                                // Add reaction handling logic here
                                                                console.log(`Added ${emoji} to comment ${commentId}`);
                                                            }
                                                        </script>



                                                        <style>
                                                            .replies-section {
                                                                font-size: 0.9em;
                                                            }

                                                            .reply-content {
                                                                background-color: #f8f9fa;
                                                                border-radius: 12px;
                                                                padding: 8px 12px;
                                                                margin-left: 40px;
                                                            }

                                                            .comment-actions {
                                                                opacity: 0;
                                                                transition: opacity 0.2s;
                                                            }

                                                            .comment-content:hover .comment-actions {
                                                                opacity: 1;
                                                            }

                                                            .reaction-emoji {
                                                                cursor: pointer;
                                                                padding: 4px;
                                                                transition: transform 0.2s;
                                                            }

                                                            .reaction-emoji:hover {
                                                                transform: scale(1.2);
                                                            }

                                                            .reaction-bar {
                                                                position: absolute;
                                                                margin-top: -40px;
                                                                z-index: 1000;
                                                            }
                                                        </style>

                                                    </div>

                                                    <!-- Footer dengan Input Komentar -->

                                                    <script>
                                                        function hapusKomentar(komentarId, postId) {
                                                            if (confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
                                                                fetch('hapus_komentar.php', {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                                        },
                                                                        body: `komentar_id=${komentarId}&post_id=${postId}`
                                                                    })
                                                                    .then(response => response.json())
                                                                    .then(data => {
                                                                        if (data.success) {
                                                                            // Refresh modal content
                                                                            location.reload();
                                                                        } else {
                                                                            alert('Gagal menghapus komentar: ' + data.message);
                                                                        }
                                                                    })
                                                                    .catch(error => {
                                                                        console.error('Error:', error);
                                                                        alert('Terjadi kesalahan saat menghapus komentar');
                                                                    });
                                                            }
                                                        }
                                                    </script>
                                                    <div class="modal-footer p-2 border-top">
                                                        <div class="d-flex gap-2 align-items-end w-100">
                                                            <div class="flex-shrink-0">
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
                                                                            ?>" alt="Profile Image"
                                                                    class="profile-img rounded-4 border-0 bg-white"
                                                                    style="width: 35px;">

                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <form class="komentar-form" data-postid="<?php echo $post['id']; ?>">
                                                                    <div class="form-group">
                                                                        <textarea class="form-control bg-light border-0"
                                                                            rows="1"
                                                                            placeholder="Tulis pendapat Anda..."
                                                                            style="resize: none; font-size: 14px;"
                                                                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                                                                            required></textarea>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <button class="btn color-web text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 35px; height: 35px;"
                                                                    onclick="submitKomentar(<?php echo $post['id']; ?>)">
                                                                    <i class="bi bi-send-fill"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <style>
                                            /* Style untuk modal komentar */
                                            .modal-fullscreen-sm-down {
                                                padding: 0;
                                            }

                                            @media (max-width: 576px) {
                                                .modal-fullscreen-sm-down {
                                                    margin: 0;
                                                }

                                                .modal-fullscreen-sm-down .modal-content {
                                                    border-radius: 0;
                                                    min-height: 100vh;
                                                    display: flex;
                                                    flex-direction: column;
                                                }

                                                .modal-fullscreen-sm-down .modal-body {
                                                    flex: 1;
                                                }
                                            }

                                            /* Style untuk textarea yang auto-expand */
                                            .form-control {
                                                min-height: 40px;
                                                padding: 8px 12px;
                                            }

                                            .form-control:focus {
                                                box-shadow: none;
                                                border-color: #ced4da;
                                            }

                                            /* Style untuk bubble chat */
                                            .bubble-chat {
                                                max-width: 85%;
                                            }

                                            /* Custom scrollbar */
                                            .komentar-container::-webkit-scrollbar {
                                                width: 6px;
                                            }

                                            .komentar-container::-webkit-scrollbar-track {
                                                background: #f1f1f1;
                                            }

                                            .komentar-container::-webkit-scrollbar-thumb {
                                                background: #ddd;
                                                border-radius: 3px;
                                            }

                                            .komentar-container::-webkit-scrollbar-thumb:hover {
                                                background: #ccc;
                                            }
                                        </style>
                                        <!-- logika komentar -->
                                        <script>
                                            // Tambahkan style untuk animasi
                                            if (typeof styleSheet === 'undefined') {
                                                const styleSheet = document.createElement("style");
                                                styleSheet.textContent = `
@keyframes highlightComment {
    0% {
        background-color: rgba(218, 119, 86, 0.2);
        transform: translateY(20px);
        opacity: 0;
    }
    50% {
        background-color: rgba(218, 119, 86, 0.2);
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        background-color: transparent;
        transform: translateY(0);
        opacity: 1;
    }
}

.new-comment {
    animation: highlightComment 2s ease-out forwards;
}
`;
                                                document.head.appendChild(styleSheet);
                                            }

                                            function submitKomentar(postId) {
                                                const form = document.querySelector(`.komentar-form[data-postid="${postId}"]`);
                                                const textarea = form.querySelector('textarea');
                                                const konten = textarea.value.trim();

                                                if (!konten) return;

                                                fetch('tambah_komentar.php', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                        },
                                                        body: `postingan_id=${postId}&konten=${encodeURIComponent(konten)}`
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.status === 'success') {
                                                            const container = document.querySelector(`#commentModal-${postId} .komentar-container`);

                                                            // Get the correct profile photo URL
                                                            let photoUrl = data.komentar.foto_profil || 'assets/pp.png';

                                                            const komentarHTML = `
                <div class="d-flex gap-3 mb-3 new-comment">
                    <div class="flex-shrink-0">
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
                                    ?>"
                        alt="" 
                        width="32px" 
                        height="32px" 
                        class="rounded-circle border"
                        style="object-fit: cover;">
                    </div>
                    <div class="bubble-chat flex-grow-1">
                        <div class="rounded-4 p-3" style="background-color: #f0f2f5;">
                            <h6 class="p-0 m-0 mb-1" style="font-size: 13px; font-weight: 600;">
                                ${data.komentar.nama_user}
                            </h6>
                            <p class="p-0 m-0" style="font-size: 13px; line-height: 1.4;">
                                ${data.komentar.konten}
                            </p>
                        </div>
                        <small class="text-muted ms-1" style="font-size: 11px;">
                            Baru saja terkirim
                        </small>
                    </div>
                </div>
            `;

                                                            // Tambahkan komentar baru di bagian bawah
                                                            container.insertAdjacentHTML('beforeend', komentarHTML);

                                                            // Ambil elemen komentar yang baru ditambahkan
                                                            const newComment = container.lastElementChild;

                                                            // Scroll ke komentar baru dengan animasi smooth
                                                            setTimeout(() => {
                                                                newComment.scrollIntoView({
                                                                    behavior: 'smooth',
                                                                    block: 'center'
                                                                });
                                                            }, 100);

                                                            // Reset textarea
                                                            textarea.value = '';
                                                            textarea.style.height = 'auto';

                                                            // Update jumlah komentar di modal
                                                            const countElement = document.querySelector(`#commentModal-${postId} .modal-title .text-muted`);
                                                            if (countElement) {
                                                                countElement.textContent = parseInt(countElement.textContent) + 1;
                                                            }
                                                        }
                                                    });
                                            }
                                        </script>

                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<div class="mt-4 text-center text-muted"><span class="bi bi-journal-x fs-1 d-block mb-2"></span>Belum ada postingan</div>';
                        }
                        ?>
                    </div>
                    <!-- fungsi untuk menghapus postingan di bagian bawah file -->
                    <script>
                        function hapusPostingan(id) {
                            if (confirm('Apakah Anda yakin ingin menghapus postingan ini?')) {
                                window.location.href = `hapus_postingan.php?id=${id}&kelas_id=<?php echo $kelas_id; ?>`;
                            }
                        }

                        function showImage(src) {
                            document.getElementById('modalImage').src = src;
                            var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
                            imageModal.show();
                        }
                    </script>
                    <!-- style untuk device grid -->
                    <style>
                        .image-grid {
                            display: grid;
                            gap: 8px;
                            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                        }

                        .image-grid img {
                            width: 100%;
                            height: 150px;
                            object-fit: cover;
                            cursor: pointer;
                            transition: transform 0.2s;
                        }

                        .image-grid img:hover {
                            transform: scale(1.02);
                        }

                        /* Style untuk item file */
                        .file-item {
                            background-color: white;
                            transition: all 0.2s;
                            min-width: 200px;
                        }

                        .file-item:hover {
                            background-color: #f8f9fa;
                            border-color: #dee2e6;
                        }

                        /* Responsive styling */
                        @media (max-width: 768px) {
                            .image-grid {
                                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                            }

                            .image-grid img {
                                height: 120px;
                            }

                            .file-item {
                                min-width: 100%;
                            }
                        }

                        /* Style untuk attachments container */
                        .attachments {
                            border: 1px solid #dee2e6;
                            background-color: #f8f9fa;
                        }
                    </style>
                    <!-- script untuk img container -->
                    <script>
                        // Example images (replace these with dynamic content if needed)
                        const images = [
                            "assets/kisi.jpg",
                            "assets/kisi2.webp",
                            "assets/kisi3.webp",
                        ];

                        const imageContainer = document.getElementById("imageContainer");

                        // Hanya lanjutkan jika elemen ditemukan
                        if (imageContainer) {
                            // Set grid class based on number of images
                            if (images.length === 1) {
                                imageContainer.classList.add("one");
                            } else if (images.length === 2) {
                                imageContainer.classList.add("two");
                            } else if (images.length === 3) {
                                imageContainer.classList.add("three");
                            } else if (images.length >= 4) {
                                imageContainer.classList.add("four");
                            }

                            // Add images to the grid
                            images.forEach(src => {
                                const img = document.createElement("img");
                                img.src = src;
                                img.alt = "Image";
                                img.setAttribute("data-bs-toggle", "modal");
                                img.setAttribute("data-bs-target", "#imageModal");
                                img.addEventListener("click", () => {
                                    document.getElementById("modalImage").src = src;
                                });
                                imageContainer.appendChild(img);
                            });
                        } else {
                            console.warn("Elemen postingan bergambar tidak ditemukan di dalam DOM");
                        }
                    </script>
                    <!-- Image Modal -->
                    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-body p-0 position-relative">
                                    <!-- Close button -->
                                    <button type="button" class="btn-close position-absolute end-0 m-3"
                                        data-bs-dismiss="modal" aria-label="Close"
                                        style="z-index: 1050; background-color: white;"></button>

                                    <!-- Image -->
                                    <img src="" id="modalImage" class="w-100 img-fluid" alt="Preview">

                                    <!-- Download button -->
                                    <button onclick="downloadImage(document.getElementById('modalImage').src)"
                                        class="btn border btn-sm position-absolute bottom-0 end-0 m-3"
                                        style="background-color: rgba(255,255,255,0.9);">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function downloadImage(imageUrl) {
                            fetch(imageUrl)
                                .then(response => response.blob())
                                .then(blob => {
                                    const url = window.URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = url;
                                    a.download = 'image.jpg';
                                    document.body.appendChild(a);
                                    a.click();
                                    window.URL.revokeObjectURL(url);
                                    document.body.removeChild(a);
                                })
                                .catch(error => console.error('Error:', error));
                        }
                    </script>

                    <style>
                        .modal-content {
                            border: none;
                            border-radius: 8px;
                            overflow: hidden;
                        }

                        .btn-close:hover {
                            background-color: rgba(255, 255, 255, 0.9) !important;
                        }

                        .modal-body button {
                            transition: opacity 0.2s;
                        }

                        .modal-body button:hover {
                            opacity: 0.8;
                        }
                    </style>
                    <div class="col">
                        <!-- modal untuk guru input deskripsi kelas -->
                        <div class="modal fade" id="deskripsimodal" tabindex="-1" aria-labelledby="modaldeskripsi" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="modaldeskripsi"><strong>Edit Deskripsi Kelas</strong></h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="update_deskripsi.php" method="POST">
                                        <div class="modal-body">
                                            <p style="font-size: 14px;">Apa ada deskripsi khusus untuk kelas Anda?</p>
                                            <div class="mt-3">
                                                <div class="form-floating">
                                                    <textarea class="form-control" name="deskripsi" placeholder="Apa pendapat Anda?" style="height: 100px;"><?php echo htmlspecialchars($kelas['deskripsi']); ?></textarea>
                                                    <label>Kelas ini bertujuan untuk ..</label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                        </div>
                                        <div class="modal-footer d-flex">
                                            <button type="submit" class="btn btnPrimary text-white flex-fill">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>



                        <div class="catatanGuru p-4 rounded-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-journal-text fs-4" style="color: rgb(218, 119, 86);"></i>
                                    <h5 class="m-0"><strong>Catatan Guru</strong></h5>
                                </div>
                            </div>

                            <?php
                            $query_catatan = "SELECT * FROM catatan_guru WHERE kelas_id = '$kelas_id' ORDER BY created_at DESC";
                            $result_catatan = mysqli_query($koneksi, $query_catatan);
                            ?>

                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo ($_GET['success'] == 'catatan_deleted') ? "Catatan berhasil dihapus!" : ""; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    <?php echo ($_GET['error'] == 'delete_failed') ? "Gagal menghapus catatan" : ""; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (mysqli_num_rows($result_catatan) > 0): ?>
                                <div class="catatan-list">
                                    <?php while ($catatan = mysqli_fetch_assoc($result_catatan)): ?>
                                        <div class="catatan-item p-4 rounded-2 mb-3 bg-light border">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-2 fw-bold"><?php echo htmlspecialchars($catatan['judul']); ?></h6>
                                                    <div class="d-flex align-items-center text-muted mb-3" style="font-size: 0.85rem;">
                                                        <i class="bi bi-calendar3 me-2"></i>
                                                        <?php echo date('d F Y', strtotime($catatan['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <style>
                                                    /* Animasi dropdown */
                                                    .animate {
                                                        animation-duration: 0.2s;
                                                        animation-fill-mode: both;
                                                        transform-origin: top center;
                                                    }

                                                    @keyframes slideIn {
                                                        0% {
                                                            transform: scaleY(0);
                                                            opacity: 0;
                                                        }

                                                        100% {
                                                            transform: scaleY(1);
                                                            opacity: 1;
                                                        }
                                                    }

                                                    .slideIn {
                                                        animation-name: slideIn;
                                                    }
                                                </style>
                                            </div>

                                            <div class="catatan-content">
                                                <p class="mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                                                    <?php echo nl2br(htmlspecialchars($catatan['konten'])); ?>
                                                </p>
                                                <div class="">
                                                    <div class="d-flex">
                                                        <?php if ($catatan['file_lampiran']): ?>
                                                            <a href="<?php echo htmlspecialchars($catatan['file_lampiran']); ?>"
                                                                class="text-decoration-none flex-fill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white border hover-shadow"
                                                                target="_blank">
                                                                <?php
                                                                $ext = pathinfo($catatan['file_lampiran'], PATHINFO_EXTENSION);
                                                                if ($ext === 'pdf') {
                                                                    $icon = 'bi-file-pdf-fill text-danger';
                                                                } elseif ($ext === 'doc' || $ext === 'docx') {
                                                                    $icon = 'bi-file-word-fill text-primary';
                                                                } elseif ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
                                                                    $icon = 'bi-file-image-fill text-success';
                                                                } else {
                                                                    $icon = 'bi-file-earmark-fill text-secondary';
                                                                }
                                                                ?>
                                                                <i class="bi <?php echo $icon; ?>"></i>
                                                                <span class="text-black">Unduh lampiran</span>
                                                            </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-5 rounded-4" style="background-color:rgb(255, 245, 240);">
                                    <i class="bi bi-journal-x fs-1 text-muted mb-3"></i>
                                    <h6 class="fw-bold mb-2">Belum Ada Catatan</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                        Guru belum menambahkan catatan apapun
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <style>
                    .catatan-item {
                        transition: all 0.2s ease;
                    }

                    .catatan-item:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                    }

                    .hover-shadow {
                        transition: all 0.2s ease;
                    }

                    .hover-shadow:hover {
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                    }

                    .catatanGuru {
                        border: 1px solid #dee2e6;
                    }

                    @media (max-width: 768px) {
                        .catatanGuru {
                            display: none;
                        }
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Auto hide alerts
                        setTimeout(() => {
                            document.querySelectorAll('.alert').forEach(alert => {
                                const bsAlert = new bootstrap.Alert(alert);
                                bsAlert.close();
                            });
                        }, 3000);
                    });
                </script>

                <style>
                    .catatan-item {
                        transition: all 0.2s ease;
                    }

                    .catatan-item:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                    }

                    .file-attachment {
                        transition: all 0.2s ease;
                    }

                    .file-attachment:hover {
                        background-color: #f8f9fa !important;
                    }

                    @media (max-width: 768px) {
                        .catatan-item {
                            transform: none !important;
                        }

                        .daftarSiswa {
                            display: none;
                        }
                    }
                </style>



                <style>
                    .student-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                        gap: 8px;
                    }

                    .student-card {
                        background-color: #f8f9fa;
                        border: 1px solid #e9ecef;
                        transition: all 0.2s ease;
                    }

                    .student-card:hover {
                        background-color: #fff;
                        transform: translateY(-2px);
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    }

                    .student-info {
                        overflow: hidden;
                    }

                    .student-name {
                        font-size: 14px;
                        font-weight: 500;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .empty-state {
                        opacity: 0.5;
                    }

                    @media (max-width: 768px) {
                        .student-grid {
                            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                        }
                    }
                </style>
                <!-- Floating Action Button -->
                <div class="floating-action-button d-block d-md-none">
                    <!-- Main FAB -->
                    <button class="btn btn-lg main-fab rounded-circle shadow" id="mainFab">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Mini FABs -->
                    <div class="mini-fabs">
                        <!-- Catatan Button -->
                        <button class="btn mini-fab rounded-circle shadow"
                            data-bs-toggle="modal"
                            data-bs-target="#semuaCatatanModal"
                            title="Catatan">
                            <i class="bi bi-journal-text"></i>
                            <span class="fab-label">Catatan Guru</span>
                        </button>

                        <!-- Agenda Button -->
                        <button class="btn mini-fab rounded-circle shadow"
                            data-bs-toggle="modal"
                            data-bs-target="#agendaSiswaModal"
                            title="Agenda">
                            <i class="bi bi-calendar-event"></i>
                            <span class="fab-label">Agenda Siswa</span>
                        </button>

                    </div>

                    <!-- Backdrop for FAB -->
                    <div class="fab-backdrop"></div>
                </div>

                <!-- Modal Agenda Siswa -->
                <div class="modal fade" id="agendaSiswaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Agenda Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center p-5">
                                <i class="bi bi-tools fs-1 text-muted mb-3"></i>
                                <h6 class="fw-bold mb-2">Fitur Sedang Maintenance</h6>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                    Kami sedang melakukan perbaikan pada fitur ini. Mohon kembali lagi nanti.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .floating-action-button {
                        position: fixed;
                        bottom: 6rem;
                        right: 2rem;
                        z-index: 1000;
                    }

                    .main-fab {
                        width: 56px;
                        height: 56px;
                        background-color: rgb(218, 119, 86);
                        color: white;
                        transition: transform 0.3s;
                        position: relative;
                        z-index: 1002;
                    }

                    .main-fab:hover {
                        background-color: rgb(219, 106, 68);
                        color: white;
                    }

                    .main-fab.active {
                        transform: rotate(45deg);
                    }

                    .mini-fabs {
                        position: absolute;
                        bottom: 70px;
                        right: 8px;
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                        opacity: 0;
                        transform: translateY(10px);
                        transition: all 0.3s;
                        pointer-events: none;
                        z-index: 1002;
                    }

                    .mini-fabs.show {
                        opacity: 1;
                        transform: translateY(0);
                        pointer-events: all;
                    }

                    .mini-fab {
                        width: 40px;
                        height: 40px;
                        background-color: white;
                        color: rgb(218, 119, 86);
                        border: 1px solid rgb(218, 119, 86);
                        position: relative;
                    }

                    .mini-fab:hover {
                        background-color: rgb(218, 119, 86);
                        color: white;
                    }

                    /* Label style */
                    .fab-label {
                        position: absolute;
                        right: 50px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: rgba(0, 0, 0, 0.7);
                        color: white;
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 12px;
                        white-space: nowrap;
                        opacity: 1;
                        transition: opacity 0.2s;
                        pointer-events: none;
                        z-index: 1003;
                    }

                    .mini-fabs.show .mini-fab:hover .fab-label {
                        opacity: 1;
                        visibility: visible;
                    }

                    /* Backdrop style */
                    .fab-backdrop {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.5);
                        opacity: 0;
                        visibility: hidden;
                        transition: all 0.3s;
                        z-index: 1001;
                    }

                    .fab-backdrop.show {
                        opacity: 1;
                        visibility: visible;
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const mainFab = document.getElementById('mainFab');
                        const miniFabs = document.querySelector('.mini-fabs');
                        const backdrop = document.querySelector('.fab-backdrop');

                        mainFab.addEventListener('click', function() {
                            this.classList.toggle('active');
                            miniFabs.classList.toggle('show');
                            backdrop.classList.toggle('show');
                        });

                        // Close FAB menu when clicking backdrop
                        backdrop.addEventListener('click', function() {
                            mainFab.classList.remove('active');
                            miniFabs.classList.remove('show');
                            backdrop.classList.remove('show');
                        });

                        // Close FAB menu when clicking outside
                        document.addEventListener('click', function(event) {
                            const isClickInsideFAB = event.target.closest('.floating-action-button');
                            if (!isClickInsideFAB && miniFabs.classList.contains('show')) {
                                mainFab.classList.remove('active');
                                miniFabs.classList.remove('show');
                                backdrop.classList.remove('show');
                            }
                        });
                    });
                </script>

                <!-- Modal Seluruh Catatan -->
                <div class="modal fade" id="semuaCatatanModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Catatan Kelas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-3">
                                <?php
                                $query_all_catatan = "SELECT * FROM catatan_guru WHERE kelas_id = '$kelas_id' ORDER BY created_at DESC";
                                $result_all_catatan = mysqli_query($koneksi, $query_all_catatan);

                                if (mysqli_num_rows($result_all_catatan) > 0):
                                ?>
                                    <div class="catatan-list">
                                        <?php while ($catatan = mysqli_fetch_assoc($result_all_catatan)): ?>
                                            <div class="catatan-item p-3 rounded mb-3 bg-light border">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-2 fw-bold"><?php echo htmlspecialchars($catatan['judul']); ?></h6>
                                                        <div class="d-flex align-items-center text-muted mb-2" style="font-size: 0.85rem;">
                                                            <i class="bi bi-calendar3 me-2"></i>
                                                            <?php echo date('d F Y', strtotime($catatan['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="mb-3 text-secondary" style="font-size: 0.95rem;">
                                                    <?php echo nl2br(htmlspecialchars($catatan['konten'])); ?>
                                                </p>

                                                <?php if ($catatan['file_lampiran']): ?>
                                                    <a href="<?php echo htmlspecialchars($catatan['file_lampiran']); ?>"
                                                        class="text-decoration-none d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white border"
                                                        target="_blank">
                                                        <?php
                                                        $ext = pathinfo($catatan['file_lampiran'], PATHINFO_EXTENSION);
                                                        if ($ext === 'pdf') {
                                                            $icon = 'bi-file-pdf-fill text-danger';
                                                        } elseif ($ext === 'doc' || $ext === 'docx') {
                                                            $icon = 'bi-file-word-fill text-primary';
                                                        } elseif ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
                                                            $icon = 'bi-file-image-fill text-success';
                                                        } else {
                                                            $icon = 'bi-file-earmark-fill text-secondary';
                                                        }
                                                        ?>
                                                        <i class="bi <?php echo $icon; ?>"></i>
                                                        <span>Lihat Lampiran</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center p-4">
                                        <i class="bi bi-journal-x fs-1 text-muted mb-3"></i>
                                        <h6 class="fw-bold mb-2">Belum Ada Catatan</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                            Mulai tambahkan catatan untuk kelas ini
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Update event listener for FAB catatan button
                    document.querySelector('.mini-fab[title="Catatan"]').addEventListener('click', function(e) {
                        e.preventDefault();
                        const semuaCatatanModal = new bootstrap.Modal(document.getElementById('semuaCatatanModal'));
                        semuaCatatanModal.show();
                    });

                    // Add event listener for modal close
                    document.getElementById('semuaCatatanModal').addEventListener('hidden.bs.modal', function() {
                        // Remove backdrop manually
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        // Remove modal-open class from body
                        document.body.classList.remove('modal-open');
                        // Remove inline styles from body
                        document.body.style.removeProperty('padding-right');
                        document.body.style.removeProperty('overflow');
                    });
                </script>
                <!-- Modal Kelola Siswa -->
                <div class="modal fade" id="kelolaSiswaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Kelola Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-light border d-flex align-items-center gap-2 p-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#lihatSemuaSiswaModal">
                                        <i class="bi bi-people-fill fs-4"></i>
                                        <div class="text-start">
                                            <h6 class="mb-0">Lihat Semua Siswa</h6>
                                            <small class="text-muted">Lihat daftar lengkap siswa dalam kelas</small>
                                        </div>
                                    </button>

                                    <button class="btn btn-light border d-flex align-items-center gap-2 p-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#tambahSiswaModal">
                                        <i class="bi bi-person-plus-fill fs-4"></i>
                                        <div class="text-start">
                                            <h6 class="mb-0">Tambah Siswa</h6>
                                            <small class="text-muted">Tambahkan siswa baru ke kelas</small>
                                        </div>
                                    </button>

                                    <button class="btn btn-light border d-flex align-items-center gap-2 p-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#hapusSiswaModal">
                                        <i class="bi bi-person-x-fill fs-4 text-danger"></i>
                                        <div class="text-start">
                                            <h6 class="mb-0">Keluarkan Siswa</h6>
                                            <small class="text-muted">Keluarkan siswa dari kelas ini</small>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal Tambah Siswa -->
                <div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Tambah Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form id="formTambahSiswa">
                                    <!-- Input Group Kelas -->
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Kelas</label>
                                        <select class="form-select" id="tingkatKelas" required>
                                            <option value="">Pilih salah satu</option>
                                            <option value="7">SMP Kelas 7</option>
                                            <option value="8">SMP Kelas 8</option>
                                            <option value="9">SMP Kelas 9</option>
                                            <option value="E">SMA Fase E</option>
                                            <option value="F">SMA Fase F</option>
                                            <option value="12">SMA Kelas 12</option>
                                        </select>
                                    </div>

                                    <!-- Daftar Siswa dengan Checkbox -->
                                    <div class="mb-3 p-3 rounded-2" style="background-color: rgb(238, 238, 238);">
                                        <label class="form-label">Daftar Siswa</label>
                                        <div class="daftar-siswa" style="max-height: 300px; overflow-y: auto;">
                                            <!-- Daftar siswa akan dimuat di sini -->
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn color-web text-white">Tambahkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- script tambah siswa -->
                <script>
                    // Ketika tingkat kelas berubah
                    document.getElementById('tingkatKelas').addEventListener('change', function() {
                        const tingkat = this.value;
                        const daftarSiswaDiv = document.querySelector('.daftar-siswa');

                        if (tingkat) {
                            // Fetch data siswa berdasarkan tingkat
                            fetch(`get_siswa.php?tingkat=${tingkat}`)
                                .then(response => response.text()) // Ubah ke text() karena response dari PHP adalah HTML
                                .then(html => {
                                    // Tambahkan checkbox "Pilih Semua" di atas daftar siswa
                                    const pilihSemuaHTML = `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="pilihSemua">
                        <label class="form-check-label" for="pilihSemua">
                            Pilih Semua
                        </label>
                    </div>
                    <hr>
                `;

                                    daftarSiswaDiv.innerHTML = pilihSemuaHTML + html;

                                    // Event listener untuk "Pilih Semua" checkbox
                                    document.getElementById('pilihSemua').addEventListener('change', function() {
                                        const checkboxes = document.querySelectorAll('.siswa-checkbox');
                                        checkboxes.forEach(checkbox => {
                                            checkbox.checked = this.checked;
                                        });
                                    });
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    daftarSiswaDiv.innerHTML = '<p class="text-danger">Terjadi kesalahan saat memuat data siswa</p>';
                                });
                        } else {
                            daftarSiswaDiv.innerHTML = '<p class="text-muted">Pilih tingkat kelas terlebih dahulu</p>';
                        }
                    });


                    document.getElementById('formTambahSiswa').addEventListener('submit', function(e) {
                        e.preventDefault();

                        const selectedSiswa = Array.from(document.querySelectorAll('.siswa-checkbox:checked'))
                            .map(checkbox => checkbox.value);

                        if (selectedSiswa.length === 0) {
                            alert('Pilih minimal satu siswa');
                            return;
                        }

                        // Ambil kelas_id dari parameter URL atau dari variabel PHP
                        const kelas_id = <?php echo $kelas_id; ?>;

                        // Kirim data ke proses_tambah_siswa.php
                        fetch('proses_tambah_siswa.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `siswa_ids=${JSON.stringify(selectedSiswa)}&kelas_id=${kelas_id}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Siswa berhasil ditambahkan ke kelas');
                                    // Tutup modal
                                    document.querySelector('#tambahSiswaModal .btn-close').click();
                                    // Refresh halaman
                                    location.reload();
                                } else {
                                    alert('Gagal menambahkan siswa: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat menambahkan siswa');
                            });
                    });
                </script>

                <!-- Modal Hapus Siswa -->
                <div class="modal fade" id="hapusSiswaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Tendang Siswa dari Kelas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-3">
                                <?php
                                $query_siswa_hapus = "SELECT s.id, s.nama, s.foto_profil, s.tingkat 
                                    FROM siswa s 
                                    JOIN kelas_siswa ks ON s.id = ks.siswa_id 
                                    WHERE ks.kelas_id = '$kelas_id' 
                                    ORDER BY s.nama ASC";
                                $result_siswa_hapus = mysqli_query($koneksi, $query_siswa_hapus);

                                if (mysqli_num_rows($result_siswa_hapus) > 0):
                                ?>
                                    <div class="list-siswa">
                                        <?php while ($siswa = mysqli_fetch_assoc($result_siswa_hapus)): ?>
                                            <div class="student-card p-2 rounded-3 d-flex align-items-center justify-content-between gap-2 mb-2"
                                                style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?php echo $siswa['foto_profil'] ? $siswa['foto_profil'] : 'assets/pp.png'; ?>"
                                                        alt="Profile" class="rounded-circle" width="40" height="40">
                                                    <div>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                                                        <small class="text-muted">Kelas <?php echo htmlspecialchars($siswa['tingkat']); ?></small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="hapusSiswa(<?php echo $siswa['id']; ?>, '<?php echo htmlspecialchars($siswa['nama']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-0">Belum ada siswa dalam kelas ini</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Fungsi untuk copy kode kelas
                    function copyKodeKelas(kode) {
                        navigator.clipboard.writeText(kode).then(() => {
                            alert('Kode kelas berhasil disalin!');
                        });
                    }

                    // Fungsi untuk konfirmasi dan hapus siswa
                    function hapusSiswa(siswaId, namaSiswa) {
                        if (confirm(`Apakah Anda yakin ingin menghapus ${namaSiswa} dari kelas ini?`)) {
                            window.location.href = `hapus_siswa.php?siswa_id=${siswaId}&kelas_id=<?php echo $kelas_id; ?>`;
                        }
                    }
                </script>

                <!-- Modal Lihat Semua Siswa -->
                <div class="modal fade" id="lihatSemuaSiswaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Daftar Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-3">
                                <!-- Statistik Siswa -->
                                <div class="student-stats p-3 rounded-3 mb-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stats-icon rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 48px; height: 48px; background-color: rgba(218, 119, 86, 0.1);">
                                            <i class="bi bi-people fs-4" style="color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Total Siswa</h6>
                                            <h4 class="m-0"><strong><?php echo $jumlah_siswa; ?></strong> siswa</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Daftar Siswa -->
                                <?php
                                $query_semua_siswa = "SELECT s.nama, s.foto_profil, s.tingkat 
                                    FROM siswa s 
                                    JOIN kelas_siswa ks ON s.id = ks.siswa_id 
                                    WHERE ks.kelas_id = '$kelas_id' 
                                    ORDER BY s.nama ASC";
                                $result_semua_siswa = mysqli_query($koneksi, $query_semua_siswa);

                                if (mysqli_num_rows($result_semua_siswa) > 0):
                                    while ($siswa = mysqli_fetch_assoc($result_semua_siswa)):
                                ?>
                                        <div class="student-card p-2 rounded-3 d-flex align-items-center gap-2 mb-2"
                                            style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                            <img src="<?php echo $siswa['foto_profil'] ? $siswa['foto_profil'] : 'assets/pp.png'; ?>"
                                                alt="Profile" class="rounded-circle" width="40" height="40">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                                                <small class="text-muted">Kelas <?php echo htmlspecialchars($siswa['tingkat']); ?></small>
                                            </div>
                                        </div>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-0">Belum ada siswa dalam kelas ini</p>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .student-card {
                        transition: all 0.2s ease;
                        border: 1px solid #e9ecef;
                    }

                    .student-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    }

                    .student-name {
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        max-width: calc(100% - 40px);
                        /* 40px untuk gambar + padding */
                    }
                </style>




                <!-- Modal Tambah Catatan -->
                <div class="modal fade" id="catatanModal" tabindex="-1" aria-labelledby="catatanModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="catatanModalLabel"><strong>Tambah Catatan</strong></h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="tambah_catatan.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Judul Catatan</label>
                                        <input type="text" class="form-control" name="judul" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Isi Catatan</label>
                                        <textarea class="form-control" name="konten" rows="4" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Lampiran (opsional)</label>
                                        <input type="file" class="form-control" name="file_lampiran">
                                        <div class="form-text">Format yang didukung: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG</div>
                                    </div>
                                    <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btnPrimary text-white">Simpan Catatan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Modal Notifikasi Presentasi -->
    <div class="modal fade" id="presentationNoticeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 position-relative" style="height: 120px; overflow: hidden; border-bottom: none;">
                    <img src="assets/presentasi_siswa.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h3 class="fw-bold mb-0 pb-0">Presentasi Akan Dimulai</h3>
                        <p class="text-muted mb-4 mt-0 pt-0" style="font-size: 14px;">Kamu akan masuk ke mode presentasi dalam:</p>

                        <div class="countdown-container mb-4">
                            <div class="countdown-timer d-flex align-items-center justify-content-center rounded-circle mx-auto"
                                style="width: 70px; height: 70px; border: 3px solid rgb(218, 119, 86, 0.2);">
                                <span id="countdownTimer" style="font-size: 1.8rem; font-weight: bold; color: rgb(218, 119, 86);">10</span>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 12px;">Presentasi akan dimulai otomatis</small>
                        </div>

                        <div class="p-3 border text-start bg-light mb-4" style="border-radius: 15px;">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                <div>
                                    <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Force Fulscreen Akan Diaktifkan</p>
                                    <p class="p-0 m-0 text-muted" style="font-size: 12px;">SAGA akan mengaktifkan paksa fullscreen, seluruh kendali presentasi akan diberikan kepada guru.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- debug timer presentasi -->
    <!-- <script>
        // Show modal immediately on page load for debugging
        document.addEventListener('DOMContentLoaded', function() {
            const presentationModal = new bootstrap.Modal(document.getElementById('presentationNoticeModal'));
            presentationModal.show();

            // Set up countdown timer
            let countdown = 10;
            document.getElementById('countdownTimer').textContent = countdown;

            const countdownInterval = setInterval(() => {
                countdown--;
                document.getElementById('countdownTimer').textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    presentationModal.hide();
                    // For debugging purposes, you can also start the presentation immediately
                    // Comment this out if you don't want to trigger the full presentation flow
                    // startStudentPresentation({current_slide: 1, total_slides: 10, file_path: ''});
                }
            }, 1000);
        });
    </script> -->

    <style>
        #forceFullscreenOverlay {
            animation: fadeIn 0.3s forwards;
            backdrop-filter: blur(5px);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 0.95;
            }
        }
    </style>

    <!-- Modal Presentasi Siswa -->
    <div class="modal fade" id="studentPresentationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-body p-0 bg-black">
                    <!-- Overlay Paksa Fullscreen -->
                    <div id="forceFullscreenOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-dark"
                        style="z-index: 2000; display: none; opacity: 0.95;">
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-white text-center">
                            <i class="bi bi-arrows-fullscreen fs-1 mb-3"></i>
                            <h2 class="mb-4">Mode Fullscreen Dibutuhkan</h2>
                            <p class="mb-4 px-5">Untuk mengikuti presentasi, Anda harus masuk ke mode fullscreen. <br>
                                Presentasi tidak akan berlanjut tanpa mode fullscreen.</p>
                            <button id="forceFullscreenBtn" class="btn btn-lg px-4 py-2 text-white" style="background-color: rgb(219, 106, 68); border-radius:15px;">
                                <i class="bi bi-fullscreen me-2"></i> Masuk Fullscreen
                            </button>
                            <div class="alert border mt-3 bg-light mb-0" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="p-0 m-0" style="font-size: 14px;">Dilarang Tekan Esc Pada Keyboard atau Keluar dari Fullscreen</p>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="presentation-container d-flex flex-column h-100">
                        <!-- Minimal Header -->
                        <div class="p-2 d-flex justify-content-between align-items-center" style="background-color: rgba(0,0,0,0.7);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-easel-fill text-white-50"></i>
                                <span class="text-white-50" style="font-size: 14px;">Presentasi Mode Siswa</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="studentSlideCounter" class="px-3 py-1 rounded-pill"
                                    style="background-color: rgba(255,255,255,0.15); color: white; font-size: 12px;">
                                    Slide 1/20
                                </span>
                                <span class="ms-2 px-2 py-1 rounded-pill d-flex align-items-center"
                                    style="background-color: rgba(40,167,69,0.2); font-size: 12px;">
                                    <span class="d-inline-block rounded-circle me-1"
                                        style="width: 6px; height: 6px; background-color: #28a745; animation: pulse 1s infinite;"></span>
                                    <span class="text-success">live</span>
                                </span>
                            </div>
                        </div>

                        <!-- Slide Content -->
                        <div class="flex-grow-1 d-flex justify-content-center align-items-center position-relative">
                            <div class="slide-container bg-white position-relative rounded"
                                style="width: 85%; height: 85%; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.2);">
                                <iframe id="studentSlideFrame" src=""
                                    style="width: 100%; height: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug script to always show the presentation modal -->
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create sample presentation data for testing
            const debugPresentationData = {
                current_slide: 1,
                total_slides: 20,
                file_path: 'uploads/presentations/sample.pdf', // Change this to a valid path if needed
                active: true
            };
            
            // Show the presentation modal after a short delay
            setTimeout(() => {
                studentPresentationActive = true;
                lastSlide = debugPresentationData.current_slide;
                
                // Show iframe with sample content
                const slideFrame = document.getElementById('studentSlideFrame');
                slideFrame.src = 'about:blank'; // Use a blank page or actual sample file
                
                // Update slide counter
                document.getElementById('studentSlideCounter').textContent = 
                    `Slide ${debugPresentationData.current_slide}/${debugPresentationData.total_slides}`;
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('studentPresentationModal'));
                modal.show();
                
                console.log('[DEBUG] Presentation modal shown for testing');
            }, 1000);
        });
    </script> -->

    <style>
        .slide-container {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes pulse {
            0% {
                opacity: 0.4;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.4;
            }
        }

        @keyframes slideTransition {
            from {
                opacity: 0;
                transform: scale(0.98);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .slide-transition {
            animation: slideTransition 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-backdrop.show {
            opacity: 0.95;
        }

        /* Add subtle texture to background */
        .modal-body.bg-black {
            background-color: #000;
            background-image: linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.8)),
                url("data:image/svg+xml,%3Csvg width='6' height='6' viewBox='0 0 6 6' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23222222' fill-opacity='0.4' fill-rule='evenodd'%3E%3Cpath d='M5 0h1L0 5v1H0V0h5z'/%3E%3C/g%3E%3C/svg%3E");
        }
    </style>

    <script>
        // Variables
        let studentPresentationActive = false;
        let studentPollInterval;
        let countdownInterval;
        let lastSlide = 0;
        let debugEnabled = true; // Set ke false di produksi
        let notificationActive = false; // Flag baru untuk mengontrol notifikasi
        let endingPresentation = false;

        // Di awal script, tambahkan reset untuk memastikan
        document.addEventListener('DOMContentLoaded', function() {
            // Reset overlay jika ada
            const overlay = document.getElementById('forceFullscreenOverlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        });

        // Debug function
        function logDebug(message, data) {
            if (debugEnabled && console) {
                console.log(`[PRESENTATION DEBUG] ${message}`, data || '');
            }
        }

        function checkForActivePresentation() {
            logDebug("Starting presentation polling");

            // Clear any existing interval first
            if (studentPollInterval) {
                clearInterval(studentPollInterval);
            }

            studentPollInterval = setInterval(() => {
                // Add a random parameter to prevent caching
                const cacheBuster = Math.random().toString(36).substring(2, 15);

                fetch(`ajax/check_presentation.php?kelas_id=<?php echo $kelas_id; ?>&_=${cacheBuster}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Cek zoom scale dan anotasi (jika ada)
                        let zoomInfo = data.zoom_scale ? `zoom: ${data.zoom_scale}` : 'no zoom';
                        let annotInfo = data.annotations ? `annotations: ${Array.isArray(data.annotations) ? data.annotations.length + ' items' : 'invalid'}` : 'no annotations';
                        logDebug(`Presentation check: active=${data.active}, slide=${data.current_slide}, ${zoomInfo}, ${annotInfo}`);

                        if (data.success) {
                            if (data.active && !studentPresentationActive && !notificationActive) {
                                // New presentation detected & notification not already showing
                                logDebug("New presentation detected", data);

                                // Store file type if available
                                if (data.file_path) {
                                    presentationFileType = data.file_path.split('.').pop().toLowerCase();
                                }

                                showPresentationNotice(data);
                            } else if (data.active && studentPresentationActive) {
                                // Presentation active, check for slide updates
                                if (data.current_slide != lastSlide) {
                                    logDebug(`Slide changed: ${lastSlide} -> ${data.current_slide}`);
                                    lastSlide = data.current_slide;
                                    updateStudentSlide(data.file_path, data.current_slide, data.total_slides);
                                }

                                // Check for zoom updates
                                if (data.zoom_scale && data.zoom_scale !== lastZoomScale) {
                                    logDebug(`Zoom changed: ${lastZoomScale} -> ${data.zoom_scale}`);
                                    lastZoomScale = data.zoom_scale;
                                    updateStudentZoom(data.zoom_scale);
                                }

                                // Check for annotation updates
                                if (data.annotations) {
                                    logDebug(`Annotation update received for slide ${data.current_slide}`);
                                    updateStudentAnnotations(data.annotations);
                                }
                            } else if (!data.active && studentPresentationActive) {
                                // Presentation ended
                                logDebug("Presentation ended");
                                endStudentPresentation();
                            }
                        } else {
                            logDebug("Error in presentation check:", data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking presentation:', error);
                        logDebug("Fetch error:", error.message);
                    });
            }, 2000); // Check every 2 seconds
        }


        function showPresentationNotice(presentationData) {
            if (notificationActive) return; // Skip if notification already showing

            notificationActive = true; // Set flag to prevent duplicate notifications
            logDebug("Showing presentation notice", presentationData);

            const modal = new bootstrap.Modal(document.getElementById('presentationNoticeModal'));
            modal.show();

            let countdown = 10;
            document.getElementById('countdownTimer').textContent = countdown;

            // Clear any existing interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            countdownInterval = setInterval(() => {
                countdown--;
                document.getElementById('countdownTimer').textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    modal.hide();
                    startStudentPresentation(presentationData);
                }
            }, 1000);
        }

        function startStudentPresentation(data) {
            logDebug("Starting student presentation", data);

            notificationActive = false;
            studentPresentationActive = true;
            lastSlide = data.current_slide;

            // Update slide frame
            updateStudentSlide(data.file_path, data.current_slide, data.total_slides);

            // Show presentation modal
            const modal = new bootstrap.Modal(document.getElementById('studentPresentationModal'));
            modal.show();

            setTimeout(() => {
                // Langsung cek status fullscreen
                const isFullScreen = document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement;

                // Log status fullscreen
                logDebug("Initial fullscreen check: " + (isFullScreen ? "active" : "inactive"));

                // Update overlay berdasarkan status
                if (isFullScreen) {
                    hideForceFullscreenOverlay();
                } else {
                    showForceFullscreenOverlay();
                }

                // Tambahkan flag agar kita tahu overlay sudah diinisialisasi
                window.overlayInitialized = true;
            }, 300);

            // Setelah modal muncul, cek fullscreen status & tambahkan event listeners
            document.getElementById('studentPresentationModal').addEventListener('shown.bs.modal', function() {
                // Tunggu sedikit agar modal fully rendered
                setTimeout(() => {
                    // Cek apakah sudah fullscreen
                    const isFullScreen = document.fullscreenElement ||
                        document.webkitFullscreenElement ||
                        document.mozFullScreenElement ||
                        document.msFullscreenElement;

                    if (!isFullScreen) {
                        // Jika belum fullscreen, tampilkan overlay
                        showForceFullscreenOverlay();
                    }
                }, 500);
            });

            // Add fullscreen listeners
            document.addEventListener('fullscreenchange', handleFullscreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.addEventListener('mozfullscreenchange', handleFullscreenChange);
            document.addEventListener('MSFullscreenChange', handleFullscreenChange);
        }

        // Variables (tambahkan ini)
        let presentationFileType = 'pdf'; // Default to pdf
        let lastZoomScale = 1.5; // Default zoom scale

        function getFileTypeFromPath(path) {
            if (!path) return '';
            return path.split('.').pop().toLowerCase();
        }

        // Update student slide view
        function updateStudentSlide(filePath, currentSlide, totalSlides) {
            logDebug(`Updating slide: ${currentSlide}/${totalSlides}`, filePath);

            document.getElementById('studentSlideCounter').textContent = `Slide ${currentSlide}/${totalSlides}`;

            // Update iframe src
            const slideFrame = document.getElementById('studentSlideFrame');

            // Tentukan tipe file dari ekstensi file
            const fileType = getFileTypeFromPath(filePath);
            const isPdfFile = fileType === 'pdf';

            if (isPdfFile) {
                // Jika iframe PDF belum ada atau beda file
                if (!slideFrame.src || slideFrame.src === 'about:blank' || !slideFrame.src.includes(encodeURIComponent(filePath))) {
                    slideFrame.src = `pdf_viewer.php?file=${encodeURIComponent(filePath)}&page=${currentSlide}&role=student`;

                    // Reset zoom scale saat load awal
                    lastZoomScale = 1.5;
                }
                // Update page di PDF yang sudah dimuat
                else {
                    try {
                        slideFrame.contentWindow.postMessage({
                            action: "setPage",
                            pageNumber: currentSlide
                        }, "*");
                    } catch (err) {
                        logDebug("Error communicating with PDF viewer:", err);
                        slideFrame.src = `pdf_viewer.php?file=${encodeURIComponent(filePath)}&page=${currentSlide}&role=student`;
                    }
                }
            }
            // Untuk file selain PDF (PPT/PPTX)
            else {
                // Jika iframe PowerPoint belum ada atau beda file
                if (!slideFrame.src || slideFrame.src === 'about:blank' || !slideFrame.src.includes(encodeURIComponent(filePath))) {
                    slideFrame.src = `show_slide.php?file=${encodeURIComponent(filePath)}&slide=${currentSlide}&role=student`;
                }
                // Update notifikasi slide di PowerPoint yang sudah dimuat
                else {
                    try {
                        slideFrame.contentWindow.postMessage({
                            action: "setSlide",
                            slideNumber: currentSlide
                        }, "*");
                    } catch (err) {
                        logDebug("Error communicating with PowerPoint viewer:", err);
                        slideFrame.src = `show_slide.php?file=${encodeURIComponent(filePath)}&slide=${currentSlide}&role=student`;
                    }
                }
            }

            // Add transition effect
            const slideContainer = document.querySelector('.slide-container');
            slideContainer.classList.remove('slide-transition');
            void slideContainer.offsetWidth; // Force reflow
            slideContainer.classList.add('slide-transition');
        }

        // Perbaiki fungsi updateStudentZoom
        // Perbaiki fungsi updateStudentZoom
        function updateStudentZoom(zoomScale) {
            logDebug(`Updating zoom scale: ${zoomScale}`);

            // Simpan nilai zoom terbaru
            lastZoomScale = parseFloat(zoomScale);

            const slideFrame = document.getElementById('studentSlideFrame');

            try {
                // Pastikan iframe sudah dimuat sebelum mengirim pesan
                if (slideFrame && slideFrame.contentWindow) {
                    // Kirim pesan ke iframe PDF viewer
                    slideFrame.contentWindow.postMessage({
                        action: "setZoom", // Pastikan ini sesuai dengan handler di pdf_viewer.php
                        scale: lastZoomScale
                    }, "*");

                    console.log(`[STUDENT] Sent zoom update: ${lastZoomScale}`);
                } else {
                    logDebug("Iframe belum siap untuk menerima zoom update");
                }
            } catch (err) {
                logDebug("Error communicating zoom update to viewer:", err);
                console.error("Zoom update error:", err);
            }
        }

        // Fungsi baru untuk update anotasi
        function updateStudentAnnotations(annotations) {
            logDebug(`Updating annotations`);

            const slideFrame = document.getElementById('studentSlideFrame');

            try {
                slideFrame.contentWindow.postMessage({
                    action: "setAnnotations",
                    annotations: annotations
                }, "*");

                // Log tambahan untuk debugging
                console.log(`[STUDENT] Sent annotations update: ${annotations.length} items`);
            } catch (err) {
                logDebug("Error communicating annotation update to viewer:", err);
                console.error("Annotation update error:", err);
            }
        }



        // Tambahkan listener untuk menerima pesan dari iframe
        window.addEventListener("message", function(event) {
            if (event.data && event.data.type === "viewerReady") {
                logDebug("Presentation viewer ready", event.data);
            }
            if (event.data && event.data.type === "pageRendered") {
                logDebug("Page rendered", event.data);
            }
            if (event.data && event.data.type === "debugLog") {
                logDebug("PDF Viewer Log: " + event.data.message);
            }
        });

        // Tambahkan fungsi ini
        function exitFullscreen() {
            logDebug("Exiting fullscreen");

            try {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            } catch (error) {
                logDebug("Error exiting fullscreen:", error);
            }
        }

        function endStudentPresentation() {
            logDebug("Ending student presentation");

            // Tandai bahwa presentasi sedang dalam proses diakhiri
            endingPresentation = true;
            studentPresentationActive = false;
            notificationActive = false;

            // Sembunyikan overlay jika masih tampil
            hideForceFullscreenOverlay();

            // Exit fullscreen
            exitFullscreen();

            // Remove fullscreen listeners
            document.removeEventListener('fullscreenchange', handleFullscreenChange);
            document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
            document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
            document.removeEventListener('MSFullscreenChange', handleFullscreenChange);

            // Close modal
            try {
                const modal = bootstrap.Modal.getInstance(document.getElementById('studentPresentationModal'));
                if (modal) modal.hide();
            } catch (error) {
                console.error('Error closing modal:', error);
            }

            // Reset flag setelah selesai
            setTimeout(() => {
                endingPresentation = false;
            }, 1000);
        }

        // Tambahkan fungsi-fungsi berikut di dalam script Anda

        // Fungsi untuk menampilkan overlay paksa fullscreen
        function showForceFullscreenOverlay() {
            const overlay = document.getElementById('forceFullscreenOverlay');
            if (overlay) {
                overlay.style.display = 'block';

                // Tambahkan event listener ke tombol
                const fullscreenBtn = document.getElementById('forceFullscreenBtn');
                if (fullscreenBtn) {
                    // Hapus event listener lama jika ada
                    fullscreenBtn.removeEventListener('click', requestFullscreen);
                    // Tambahkan event listener baru
                    fullscreenBtn.addEventListener('click', requestFullscreen);
                }
            }

            logDebug("Force fullscreen overlay shown");
        }

        // Fungsi untuk menyembunyikan overlay
        function hideForceFullscreenOverlay() {
            const overlay = document.getElementById('forceFullscreenOverlay');
            if (overlay) {
                overlay.style.display = 'none';
                logDebug("Force fullscreen overlay hidden successfully");
            } else {
                logDebug("WARNING: Could not find forceFullscreenOverlay element");
            }
        }

        // Ganti fungsi requestFullscreen
        function requestFullscreen() {
            logDebug("Requesting fullscreen");

            // Sembunyikan overlay sebelum request fullscreen
            // Ini memastikan overlay tidak menghalangi fullscreen request
            hideForceFullscreenOverlay();

            // Gunakan element modal content
            const element = document.querySelector('.modal-content');
            if (!element) {
                logDebug("ERROR: Modal content element not found");
                return;
            }

            try {
                if (element.requestFullscreen) {
                    element.requestFullscreen().then(() => {
                        logDebug("Fullscreen request succeeded");
                        // Sembunyikan overlay setelah fullscreen berhasil
                        setTimeout(hideForceFullscreenOverlay, 300);
                    }).catch(err => {
                        logDebug("Fullscreen request failed: " + err.message);
                        // Tampilkan overlay lagi jika fullscreen gagal
                        setTimeout(showForceFullscreenOverlay, 300);
                    });
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                    // Tidak bisa menggunakan Promise di sini
                    setTimeout(hideForceFullscreenOverlay, 500);
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                    setTimeout(hideForceFullscreenOverlay, 500);
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                    setTimeout(hideForceFullscreenOverlay, 500);
                }
            } catch (error) {
                logDebug("Fullscreen error: " + error.message);
                // Tampilkan overlay lagi jika ada error
                setTimeout(showForceFullscreenOverlay, 300);
            }
        }

        // Ganti definisi handleFullscreenChange agar tidak duplikat
        function handleFullscreenChange() {
            // Beri waktu sedikit untuk DOM update
            setTimeout(() => {
                const isFullScreen = document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement;

                logDebug("Fullscreen changed: " + (isFullScreen ? "active" : "inactive"));

                if (studentPresentationActive) {
                    if (isFullScreen) {
                        // Jika fullscreen aktif, sembunyikan overlay
                        logDebug("Fullscreen aktif, menyembunyikan overlay...");
                        hideForceFullscreenOverlay();
                    } else {
                        // Cek apakah presentasi memang sedang dalam proses berakhir
                        // Tambahkan variabel baru untuk menandai proses pengakhiran presentasi
                        if (!endingPresentation) {
                            // Jika keluar dari fullscreen dan bukan karena end presentation
                            logDebug("Fullscreen tidak aktif, menampilkan overlay...");
                            showForceFullscreenOverlay();
                        }
                    }
                }
            }, 100);
        }

        function handleFullscreenChange() {
            const isFullScreen = document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement;

            logDebug("Fullscreen changed:", isFullScreen ? "active" : "inactive");

            if (studentPresentationActive && !isFullScreen) {
                // Force back to fullscreen
                logDebug("Forcing fullscreen back on");
                setTimeout(requestFullscreen, 500);
            }
        }

        // Prevent keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (studentPresentationActive) {
                // Prevent Escape, F11, Ctrl+W, etc.
                if (e.key === 'Escape' || e.key === 'F11' ||
                    (e.ctrlKey && (e.key === 'w' || e.key === 'W'))) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        });

        // Manually trigger check for debugging
        function checkNow() {
            fetch(`ajax/check_presentation.php?kelas_id=<?php echo $kelas_id; ?>&_=${Math.random()}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Manual check result:", data);
                })
                .catch(error => console.error('Manual check error:', error));
        }

        // Start polling when page loads
        document.addEventListener('DOMContentLoaded', function() {
            logDebug("Page loaded, starting presentation checks");
            checkForActivePresentation();

            // // Add debug button
            // if (debugEnabled) {
            //     const debugBtn = document.createElement('button');
            //     debugBtn.textContent = 'Check Presentation Now';
            //     debugBtn.style.position = 'fixed';
            //     debugBtn.style.bottom = '20px';
            //     debugBtn.style.right = '20px';
            //     debugBtn.style.zIndex = '9999';
            //     debugBtn.style.padding = '10px';
            //     debugBtn.style.background = '#f0f0f0';
            //     debugBtn.style.border = '1px solid #ccc';
            //     debugBtn.style.borderRadius = '5px';
            //     debugBtn.onclick = checkNow;
            //     document.body.appendChild(debugBtn);
            // }
        });
    </script>

    <!-- Modal Daftar Pengguna yang Bereaksi -->
    <div class="modal fade" id="reactionUsersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Reaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="reactionUsersList" class="p-2">
                        <!-- Daftar pengguna akan dimuat di sini -->
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            <p class="mt-2 mb-0 text-muted">Memuat...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .reaction-tabs .btn {
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 12px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        .reaction-tabs .btn.active {
            background: rgb(218, 119, 86);
            color: white;
            border-color: rgb(218, 119, 86);
        }

        .reaction-user {
            transition: all 0.2s;
        }

        .reaction-user:last-child {
            border-bottom: none !important;
        }

        .reaction-user:hover {
            background-color: #f8f9fa;
        }

        .fw-medium {
            font-weight: 500;
            font-size: 14px;
        }
    </style>

    <script>
        // Tambahkan event listener ke badge reaksi
        document.addEventListener('DOMContentLoaded', function() {
            // Cari semua badge reaksi pada postingan
            document.querySelectorAll('.badge.rounded-pill.bg-light.border.px-3.py-2').forEach(badge => {
                if (badge.querySelector('.reactions-count')) {
                    // Tambahkan pointer cursor dan event listener
                    badge.style.cursor = 'pointer';
                    badge.onclick = function() {
                        // Cari post ID dari parent element
                        const postElement = this.closest('.postingan');
                        if (postElement) {
                            const likeBtn = postElement.querySelector('[id^="like-btn-"]');
                            if (likeBtn) {
                                const postId = likeBtn.id.replace('like-btn-', '');
                                showReactionUsers(postId);
                            }
                        }
                    };
                }
            });
        });

        function showReactionUsers(postId) {
            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('reactionUsersModal'));
            modal.show();

            // Ambil daftar pengguna yang memberi reaksi
            fetch(`get_reaction_users.php?post_id=${postId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const usersList = document.getElementById('reactionUsersList');
                    usersList.innerHTML = '';

                    if (data.error) {
                        console.error("API Error:", data.error);
                        usersList.innerHTML = `<div class="text-center p-4 text-danger">Error: ${data.error}</div>`;
                        return;
                    }

                    if (data.length === 0) {
                        usersList.innerHTML = '<div class="text-center p-4 text-muted">Belum ada reaksi</div>';
                        return;
                    }

                    // Kelompokkan pengguna berdasarkan jenis reaksi
                    const reactionGroups = {};
                    data.forEach(user => {
                        if (!reactionGroups[user.emoji]) {
                            reactionGroups[user.emoji] = [];
                        }
                        reactionGroups[user.emoji].push(user);
                    });

                    // Header tab untuk reaksi
                    let tabsHtml = '<div class="mb-3 border-bottom pb-2"><div class="d-flex gap-2 overflow-auto reaction-tabs">';
                    tabsHtml += `<button class="btn btn-sm px-3 py-1 active" onclick="showReactionTab(this, 'all')">Semua <span class="ms-1">${data.length}</span></button>`;

                    for (const [emoji, users] of Object.entries(reactionGroups)) {
                        tabsHtml += `<button class="btn btn-sm px-3 py-1" onclick="showReactionTab(this, '${emoji}')"><span class="me-1">${emoji}</span> ${users.length}</button>`;
                    }

                    tabsHtml += '</div></div>';
                    usersList.innerHTML = tabsHtml;

                    // Tambahkan container untuk daftar pengguna
                    const usersContainer = document.createElement('div');
                    usersContainer.className = 'reaction-users-container';
                    usersList.appendChild(usersContainer);

                    // Tambahkan semua pengguna (tab "Semua")
                    const allUsersTab = document.createElement('div');
                    allUsersTab.className = 'reaction-tab-content active';
                    allUsersTab.dataset.tab = 'all';

                    data.forEach(user => {
                        const userItem = document.createElement('div');
                        userItem.className = 'reaction-user py-2 border-bottom';
                        userItem.dataset.emoji = user.emoji;
                        userItem.innerHTML = `
                        <div class="d-flex align-items-center px-3">
                            <img src="${user.foto_profil}" alt="Profile" 
                                class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                            <div>
                                <div class="fw-medium">${user.nama}</div>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">${user.user_type === 'guru' ? 'Guru' : 'Siswa'}</small>
                                    <span>${user.emoji}</span>
                                </div>
                            </div>
                        </div>
                    `;
                        allUsersTab.appendChild(userItem);
                    });

                    usersContainer.appendChild(allUsersTab);

                    // Buat tab untuk setiap jenis reaksi
                    for (const [emoji, users] of Object.entries(reactionGroups)) {
                        const emojiTab = document.createElement('div');
                        emojiTab.className = 'reaction-tab-content';
                        emojiTab.dataset.tab = emoji;
                        emojiTab.style.display = 'none';

                        users.forEach(user => {
                            const userItem = document.createElement('div');
                            userItem.className = 'reaction-user py-2 border-bottom';
                            userItem.innerHTML = `
                            <div class="d-flex align-items-center px-3">
                                <img src="${user.foto_profil}" alt="Profile" 
                                    class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                <div>
                                    <div class="fw-medium">${user.nama}</div>
                                    <small class="text-muted">${user.user_type === 'guru' ? 'Guru' : 'Siswa'}</small>
                                </div>
                            </div>
                        `;
                            emojiTab.appendChild(userItem);
                        });

                        usersContainer.appendChild(emojiTab);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('reactionUsersList').innerHTML =
                        `<div class="text-center p-4 text-danger">
                        <p>Terjadi kesalahan saat memuat data</p>
                        <small class="d-block mt-2">${error.message}</small>
                    </div>`;
                });
        }

        function showReactionTab(button, tabId) {
            // Update active button
            document.querySelectorAll('.reaction-tabs .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');

            // Show selected tab content
            document.querySelectorAll('.reaction-tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            document.querySelector(`.reaction-tab-content[data-tab='${tabId}']`).style.display = 'block';
        }
    </script>

    <!-- pause di video preview -->
    <script>
        // Tambahkan di salah satu script tag di bawah
        document.addEventListener('DOMContentLoaded', function() {
            // Menangani klik pada video preview
            document.querySelectorAll('.video-preview').forEach(preview => {
                preview.addEventListener('click', function() {
                    const videoElement = this.querySelector('video');
                    if (videoElement) {
                        if (videoElement.paused) {
                            videoElement.play();
                            this.querySelector('.play-icon').style.display = 'none';
                        } else {
                            videoElement.pause();
                            this.querySelector('.play-icon').style.display = 'block';
                        }
                    }
                });
            });

            // Menghentikan event bubbling untuk klik pada video dalam postingan
            document.querySelectorAll('.responsive-video').forEach(video => {
                video.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        });
    </script>

    <!-- Modal Konfirmasi Link -->
    <div class="modal fade" id="linkConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <h5 class="mt-3 fw-bold">Buka Link External</h5>
                    <p class="mb-4">Apakah Anda yakin ingin membuka link berikut?</p>
                    <div class="alert alert-info d-flex align-items-center mb-4" style="font-size: 0.9rem; overflow-wrap: break-word;">
                        <i class="bi bi-link-45deg me-2 fs-5"></i>
                        <span id="linkUrl"></span>
                    </div>
                    <div class="d-flex gap-2 btn-group justify-content-center">
                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                        <a href="#" id="confirmOpenLink" target="_blank" class="btn btnPrimary text-white px-4" style="border-radius: 12px;">Buka Link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan modal ada di DOM
            function ensureModalExists() {
                // Cek apakah modal sudah ada
                if (!document.getElementById('linkConfirmModal')) {
                    // Jika tidak ada, buat dan tambahkan modal ke DOM
                    const modalHTML = `
            <div class="modal fade" id="linkConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px;">
                        <div class="modal-body text-center p-4">
                            <h5 class="mt-3 fw-bold">Buka Link External</h5>
                            <p class="mb-4">Apakah Anda yakin ingin membuka link berikut?</p>
                            <div class="alert alert-info d-flex align-items-center mb-4" style="font-size: 0.9rem; overflow-wrap: break-word;">
                                <i class="bi bi-link-45deg me-2 fs-5"></i>
                                <span id="linkUrl"></span>
                            </div>
                            <div class="d-flex gap-2 btn-group justify-content-center">
                                <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                <a href="#" id="confirmOpenLink" target="_blank" class="btn btnPrimary text-white px-4" style="border-radius: 12px;">Buka Link</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                }

                return {
                    modal: document.getElementById('linkConfirmModal'),
                    urlElement: document.getElementById('linkUrl'),
                    confirmBtn: document.getElementById('confirmOpenLink')
                };
            }

            // Fungsi untuk menangani klik pada link card yang terdeteksi
            function handleLinkClick(event) {
                // Cek apakah elemen yang diklik adalah card link atau bagian dari card link
                const linkCard = event.target.closest('.detected-link-card');

                if (linkCard) {
                    event.preventDefault(); // Mencegah navigasi langsung

                    // Dapatkan URL asli dari atribut data
                    const originalUrl = linkCard.getAttribute('data-original-url');

                    // Bersihkan modal yang mungkin ada
                    const oldModal = document.querySelector('.modal-backdrop');
                    if (oldModal) {
                        oldModal.remove();
                    }

                    // Pastikan body tidak memiliki class modal-open
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');

                    // Pastikan modal ada di DOM dan dapatkan elemennya
                    const elements = ensureModalExists();

                    // Set URL di modal
                    if (elements.urlElement) {
                        elements.urlElement.textContent = originalUrl;
                    }

                    // Set href untuk tombol konfirmasi
                    if (elements.confirmBtn) {
                        elements.confirmBtn.href = originalUrl;
                    }

                    // Tampilkan modal
                    if (elements.modal) {
                        const modal = new bootstrap.Modal(elements.modal);
                        modal.show();
                    }
                }
            }

            // Gunakan event delegation
            document.addEventListener('click', handleLinkClick);

            // Pastikan modal sudah ada di DOM saat halaman dimuat
            ensureModalExists();
        });
    </script>
</body>

</html>