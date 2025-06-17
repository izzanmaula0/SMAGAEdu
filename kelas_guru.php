<?php
session_start();
require "koneksi.php";

// Fungsi untuk mendeteksi URL dan mengubahnya menjadi link yang dapat diklik
// Fungsi untuk mendeteksi URL dan mengubahnya menjadi card link
function makeLinksClickable($text)
{
    // Pattern untuk mendeteksi URL dalam teks
    $pattern = '/(https?:\/\/[^\s<]+)/';

    // Callback untuk membuat card link
    $callback = function ($matches) {
        $originalUrl = $matches[1];

        // Default values
        $iconClass = "bi-link-45deg";
        $iconColor = "rgb(219, 106, 68)";
        $linkType = "Link Website";

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
            <div class="d-flex align-items-center">
            <i class="bi ' . $iconClass . ' fs-4 me-3" style="font-size: 24px; color:' . $iconColor . '"></i>
            <div class="d-flex justify-content-between flex-grow-1 overflow-hidden">
                <div class="overflow-hidden" style="max-width: 70%;">
                <p class="p-0 m-0 fw-bold" style="font-size: 14px;">' . $linkType . '</p>
                <p class="p-0 m-0 text-muted text-truncate" style="font-size: 12px;">' . htmlspecialchars($displayUrl) . '</p>
                </div>
            </div>
            <div class="ms-auto">
                <button class="btn btn-light border rounded-pill">
                    <p style="font-size:12px;" class="p-0 m-0">
                        <span class="d-none d-md-inline">Buka Link</span>
                        <span class="d-inline d-md-none">Buka</span>
                    </p>
                </button>
            </div>
            </div>
        </div>';
    };

    // Lakukan penggantian menggunakan regular expression dengan callback
    $text_with_links = preg_replace_callback($pattern, $callback, $text);

    return $text_with_links;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek session - izinkan guru dan admin
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

// Ambil ID kelas dari parameter URL
if (!isset($_GET['id'])) {
    header("Location: " . ($_SESSION['level'] == 'admin' ? 'beranda_admin.php' : 'beranda_guru.php'));
    exit();
}

$kelas_id = mysqli_real_escape_string($koneksi, $_GET['id']);


// Query untuk mengambil informasi kelas (jika admin, hilangkan filter guru_id)
if ($_SESSION['level'] == 'admin') {
    $query_kelas = "SELECT * FROM kelas WHERE id = '$kelas_id'";
} else {
    $query_kelas = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '{$_SESSION['userid']}'";
}

$result_kelas = mysqli_query($koneksi, $query_kelas);

if (mysqli_num_rows($result_kelas) == 0) {
    header("Location: " . ($_SESSION['level'] == 'admin' ? 'beranda_admin.php' : 'beranda_guru.php'));
    exit();
}

$data_kelas = mysqli_fetch_assoc($result_kelas);

$guru_id = $data_kelas['guru_id']; // Ambil guru_id dari data kelas
$query_guru_pengampu = "SELECT * FROM guru WHERE username = '$guru_id'";
$result_guru_pengampu = mysqli_query($koneksi, $query_guru_pengampu);
$guru_pengampu = mysqli_fetch_assoc($result_guru_pengampu);


// Query untuk mengambil postingan dari kelas ini
$query_postingan = "SELECT 
    p.*,
    COALESCE(g.namaLengkap, s.nama) as nama_pembuat,
    g.jabatan,
    t.id as tugas_id,
    t.judul as judul_tugas,
    t.batas_waktu,
    CASE WHEN g.username IS NOT NULL THEN 'guru' ELSE 'siswa' END as user_type,
    CASE WHEN g.username IS NOT NULL THEN g.foto_profil ELSE s.foto_profil END as foto_profil,
    s.photo_url, s.photo_type
FROM postingan_kelas p
LEFT JOIN guru g ON p.user_id = g.username AND p.user_type = 'guru'
LEFT JOIN siswa s ON p.user_id = s.username AND p.user_type = 'siswa'
LEFT JOIN tugas t ON p.id = t.postingan_id
WHERE p.kelas_id = '$kelas_id'
ORDER BY p.created_at DESC";

$result_postingan = mysqli_query($koneksi, $query_postingan);

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Query untuk menghitung jumlah siswa
$query_jumlah = "SELECT COUNT(*) as total FROM kelas_siswa WHERE kelas_id = '$kelas_id'";
$result_jumlah = mysqli_query($koneksi, $query_jumlah);
$jumlah_siswa = mysqli_fetch_assoc($result_jumlah)['total'];

// Query untuk mengambil semua siswa di kelas ini
$query_siswa_all = "SELECT s.nama, s.foto_profil FROM siswa s 
                    JOIN kelas_siswa ks ON s.id = ks.siswa_id 
                    WHERE ks.kelas_id = '$kelas_id'";
$result_siswa = mysqli_query($koneksi, $query_siswa_all);

// helper foto profil siswa
function getProfilePhoto($user_type, $data)
{
    if ($user_type == 'siswa') {
        if (!empty($data['photo_url']) && $data['photo_type'] === 'avatar') {
            return $data['photo_url'];
        } elseif (!empty($data['foto_siswa']) && $data['photo_type'] === 'upload') {
            return 'uploads/profil/' . $data['foto_siswa'];
        }
    } else if ($user_type == 'guru') {
        if (!empty($data['foto_guru'])) {
            return 'uploads/profil/' . $data['foto_guru'];
        }
    }
    return 'assets/pp.png';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
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
    <title>Kelas - SMAGAEdu</title>
</head>
<?php include 'includes/styles.php'; ?>
<style>
    body {
        font-family: merriweather;
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

    .detected-link-card {
        display: block;
        margin: 0;
    }
</style>

<style>
    .student-checkbox-card {
        transition: all 0.2s ease;
    }

    .student-checkbox-card:hover {
        background-color: #e9ecef !important;
    }

    .form-check-input.siswa-checkbox {
        cursor: pointer;
    }

    /* Loader animation */
    #siswaLoader {
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Better scrollbar for student list */
    .student-list-checkbox::-webkit-scrollbar,
    #daftarSiswaFull::-webkit-scrollbar {
        width: 6px;
    }

    .student-list-checkbox::-webkit-scrollbar-track,
    #daftarSiswaFull::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .student-list-checkbox::-webkit-scrollbar-thumb,
    #daftarSiswaFull::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .student-list-checkbox::-webkit-scrollbar-thumb:hover,
    #daftarSiswaFull::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi deteksi zoom level dan auto-adjust
        function adjustZoomLevel() {
            // Deteksi zoom level browser
            const zoomLevel = Math.round(window.devicePixelRatio * 100);

            // Jika zoom level lebih dari 100% (pengguna telah melakukan zoom in)
            if (zoomLevel > 100) {
                // Tampilkan modal zoom warning alih-alih confirm
                showZoomModal(zoomLevel);
            }
        }

        // Fungsi untuk menampilkan modal peringatan zoom
        function showZoomModal(zoomLevel) {
            // Cek jika modal sudah ada di body
            if (!document.getElementById('zoomWarningModal')) {
                // Buat elemen modal
                // Ganti kelas alert dengan custom-alert untuk menghindari behavior auto-dismiss dari Bootstrap
                const modalHTML = `
            <div class="modal fade" id="zoomWarningModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px;">
                        <div class="modal-body text-center p-4">
                            <i class="bi bi-display" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 fw-bold">Ukuran Layar Anda Terlalu Besar</h5>
                            <p class="mb-4" style="font-size:13px;">Beberapa elemen mungkin tidak optimal pada zoom level saat ini (${zoomLevel}%). Hal ini mungkin menyebabkan beberapa
                            tampilan tidak sesuai. Kecilkan ukuran layar hingga 90-100% untuk mendapatkan pengalaman optimal</p>

                            <div class="custom-info-box text-start border bg-light mb-4" style="border-radius: 15px; padding: 12px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Ingin merubah ukuran layar Anda?</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Gunakan kombinasi Keyboard <span class="rounded px-2 border" style="">Ctrl</span> + <span class="rounded px-2 border" style=""> - </span> untuk memperkecil dan <span class="rounded px-2 border" style="">Ctrl</span> + <span class="rounded px-2 border" style=""> = </span> untuk memperbesar ukuran layar.
                                        Gunakan sesuai dengan kebutuhan Anda.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 btn-group justify-content-center">
                                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Ok</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;

                // Tambahkan modal ke body
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = modalHTML;
                document.body.appendChild(modalContainer.firstElementChild);

                // Inisialisasi modal
                const zoomModal = new bootstrap.Modal(document.getElementById('zoomWarningModal'));
                zoomModal.show();

                // Hapus modal dari DOM saat ditutup
                const modalElement = document.getElementById('zoomWarningModal');
                modalElement.addEventListener('hidden.bs.modal', function() {
                    if (modalElement && modalElement.parentNode) {
                        modalElement.parentNode.removeChild(modalElement);
                    }
                });
            }
        }

        // Jalankan saat halaman dimuat
        adjustZoomLevel();

        // Tambahkan event listener untuk resize
        window.addEventListener('resize', function() {
            const currentZoom = Math.round(window.devicePixelRatio * 100);

            // Jika zoom level naik di atas 105%
            if (currentZoom > 105) {
                // Tampilkan modal peringatan alih-alih pesan sederhana
                showResizeWarningModal();
            }
        });

        // Fungsi untuk menampilkan modal peringatan resize
        function showResizeWarningModal() {
            // Cek jika modal sudah ada dan tampil
            const existingModal = document.getElementById('resizeWarningModal');
            if (existingModal && existingModal.classList.contains('show')) {
                return; // Jangan buat modal baru jika sudah ada
            }

            // Cek jika modal sudah ada di body
            if (!document.getElementById('resizeWarningModal')) {
                // Buat elemen modal dengan custom-info-box sebagai pengganti alert
                const modalHTML = `
            <div class="modal fade" id="resizeWarningModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px;">
                        <div class="modal-body text-center p-4">
                            <i class="bi bi-display" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 fw-bold">Perubahan Tampilan Terdeteksi</h5>
                            <p class="mb-4">Terdeteksi perubahan layar, silahkan gunakan zoom 90-100% untuk tampilan web paling optimal</p>
                            <div class="custom-info-box text-start border bg-light mb-4" style="border-radius: 15px; padding: 12px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Ingin merubah ukuran layar Anda?</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Gunakan kombinasi Keyboard <span class="rounded px-2 border" style="">Ctrl</span> + <span class="rounded px-2 border" style=""> - </span> untuk memperkecil dan <span class="rounded px-2 border" style="">Ctrl</span> + <span class="rounded px-2 border" style=""> = </span> untuk memperbesar ukuran layar.
                                        Gunakan sesuai dengan kebutuhan Anda.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 btn-group justify-content-center">
                                <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Mengerti</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;

                // Tambahkan modal ke body
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = modalHTML;
                document.body.appendChild(modalContainer.firstElementChild);

                // Inisialisasi modal
                const resizeModal = new bootstrap.Modal(document.getElementById('resizeWarningModal'));
                resizeModal.show();

                // Otomatis tutup modal setelah 8 detik
                setTimeout(() => {
                    resizeModal.hide();

                    // Hapus modal dari DOM setelah ditutup
                    const modalElement = document.getElementById('resizeWarningModal');
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        if (modalElement && modalElement.parentNode) {
                            modalElement.parentNode.removeChild(modalElement);
                        }
                    });
                });
            }
        }

        // Menonaktifkan semua fungsi JavaScript Bootstrap Alert yang mungkin menyebabkan auto-dismiss
        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
            // Mencegah event yang terkait dengan auto-dismiss alert
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('alert-dismissible')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
        }
    });
</script> -->
<style>
    .nav-pills .nav-link {
        color: #666;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .nav-pills .nav-link.active {
        background-color: rgb(218, 119, 86);
        color: white;
    }

    .nav-pills .nav-link:hover:not(.active) {
        background-color: rgba(218, 119, 86, 0.1);
    }

    .btnPrimary {
        background-color: rgb(218, 119, 86);
        color: white;
    }
</style>

<body>


    <?php
    // Check if cookie exists to hide admin notification
    $showAdminModal = true;
    if (isset($_COOKIE['hide_admin_notification']) && $_COOKIE['hide_admin_notification'] == 'true') {
        $showAdminModal = false;
    }

    // Get teacher's name for admin view
    if ($_SESSION['level'] == 'admin') {
        $query_guru_info = "SELECT g.namaLengkap as nama_guru FROM kelas k JOIN guru g ON k.guru_id = g.username WHERE k.id = '$kelas_id'";
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
                                <button type="button" class="btn btn-primary px-4" id="closeAdminModal" style="border-radius: 12px;">Saya Mengerti</button>
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
        <div class="background-container position-relative rounded mx-2 mx-md-3">
            <!-- Background image -->
            <div style="background-image: url(<?php
                                                echo !empty($data_kelas['background_image']) ?
                                                    htmlspecialchars($data_kelas['background_image']) :
                                                    'assets/bg.jpg';
                                                ?>); 
                            height: 200px; 
                            padding-top: 120px; 
                            margin-top: 15px; 
                            background-position: center;
                            background-size: cover;"
                class="rounded text-white shadow latar-belakang">

                <?php if (!empty($data_kelas['photographer_name'])): ?>
                    <div class="attribution-overlay">
                        <small>Photo by
                            <a href="<?php echo htmlspecialchars($data_kelas['photographer_url']); ?>" target="_blank" class="text-white">
                                <?php echo htmlspecialchars($data_kelas['photographer_name']); ?>
                            </a>
                            on
                            <a href="https://unsplash.com" target="_blank" class="text-white">Unsplash</a>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Overlay dengan tombol (akan muncul saat hover) -->
            <div class="background-overlay rounded d-flex align-items-center justify-content-center">
                <button class="btn btn-light <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>" style="z-index: 9999;" data-bs-toggle="modal" data-bs-target="#modalEditBackground">
                    <i class="fas fa-camera me-2"></i>Ganti Background
                </button>
            </div>

            <!-- Konten (teks) dengan z-index lebih tinggi -->
            <div class="position-absolute bottom-0 start-0 p-3" style="z-index: 2;">
                <div>
                    <h5 class="display-5 p-0 m-0 text-white"
                        style="font-weight: bold; font-size: 28px; font-size: clamp(24px, 5vw, 35px);">
                        <?php
                        if ($data_kelas['is_public']) {
                            echo htmlspecialchars($data_kelas['nama_kelas']);
                        } else {
                            echo htmlspecialchars($data_kelas['mata_pelajaran']);
                        }
                        ?>

                    </h5>
                    <h4 class="p-0 m-0 pb-3 text-white" style="font-size: clamp(16px, 4vw, 24px);">
                        Kelas/Fase <?php echo htmlspecialchars($data_kelas['tingkat']); ?>
                    </h4>
                </div>
            </div>
        </div>

        <!-- hover untuk background kelas -->
        <!-- CSS untuk efek hover -->
        <style>
            .attribution-overlay {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.5);
                color: white;
                padding: 4px 8px;
                font-size: 10px;
                z-index: 1;
                border-bottom-left-radius: 10px;
                border-bottom-right-radius: 10px;
            }

            .attribution-overlay a {
                text-decoration: underline;
            }

            .latar-belakang {
                filter: brightness(0.6);
            }

            .background-container {
                position: relative;
                cursor: pointer;

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

        <div class="row mt-4 p-3 m-0 pt-0">
            <div class="col-12 col-lg-8 p-0 position-relative">
                <!-- Post Creator Card -->
                <div class="create-post-card bg-white rounded-3 p-3 mb-4 border <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>">
                    <!-- Desktop View -->
                    <div class="d-none d-md-flex align-items-center gap-3">
                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                            alt="Profile" class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <button class="btn w-100 text-start px-4 rounded-pill border bg-light hover-bg"
                                data-bs-toggle="modal"
                                data-bs-target="#modalTambahPostingan">
                                <span class="text-muted">Apa yang ingin Anda diskusikan dengan siswa?</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile View -->
                    <div class="d-flex d-md-none gap-2">
                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                            alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        <button class="flex-grow-1 btn text-start rounded-pill border bg-light"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambahPostingan">
                            <span class="text-muted" style="font-size: 0.9rem;">Mulai diskusi...</span>
                        </button>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-flex justify-content-around mt-3 pt-2">
                        <button class="btn btn-light border flex-grow-1 me-2 d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambahPostingan">
                            <i class="ti ti-library-photo text-success"></i>
                            <span class="d-none d-md-inline">Foto/Dokumen</span>
                        </button>
                        <button class="btn btn-light border flex-grow-1 me-2 d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalPresentasi">
                            <i class="ti ti-presentation-analytics text-primary"></i>
                            <span class="d-none d-md-inline">Presentasi</span>
                        </button>
                        <!-- Tambahkan di Quick Actions pada postingan -->
                        <button class="btn btn-light border flex-grow-1 me-2 d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambahTugas">
                            <i class="ti ti-checklist text-info"></i>
                            <span class="d-none d-md-inline">Tugas</span>
                        </button>
                    </div>
                </div>

                <style>
                    .create-post-card {
                        transition: all 0.2s ease;
                    }

                    .hover-bg:hover {
                        background-color: #f0f0f0 !important;
                    }

                    .btn-light:hover {
                        background-color: #e9ecef;
                    }

                    @media (max-width: 768px) {
                        .create-post-card {
                            padding: 12px !important;
                            margin-bottom: 12px !important;
                        }

                        .quick-actions button {
                            padding: 8px !important;
                        }
                    }
                </style>

                <!-- Modal Tambah Tugas dengan iOS Style -->
                <div class="modal fade" id="modalTambahTugas" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header p-0 position-relative" style="height: 120px; overflow: hidden; border-bottom: none;">
                                <img src="assets/header_tugas_guru.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                            </div>
                            <div class="modal-body p-4">
                                <div>
                                    <h3 class="fw-bold">Buat Tugas Untuk Siswa</h3>
                                    <p class="text-muted" style="font-size: 12px;">Buat tugas untuk siswa tanpa harus berpindah platform lainya, semua tugas Anda tersimpan dalam SMAGAEdu.</p>
                                </div>
                                <form action="tambah_tugas.php" method="POST" enctype="multipart/form-data" class="mt-4">
                                    <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">

                                    <!-- Judul Tugas -->
                                    <div class="form-group-ios mb-4">
                                        <label class="form-label mb-2">Judul Tugas</label>
                                        <input type="text" class="form-control form-control-ios"
                                            name="judul_tugas" placeholder="Masukkan judul tugas" required>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div class="form-group-ios mb-4">
                                        <label class="form-label m-0 p-0">Deskripsi</label>
                                        <p class="text-muted p-0 mt-0 me-0 ms-0 mb-2" style="font-size: 10px;">TIDAK WAJIB</p>
                                        <textarea class="form-control form-control-ios"
                                            name="deskripsi_tugas" rows="4"
                                            placeholder="Jelaskan detail tugas"></textarea>
                                    </div>

                                    <!-- Poin -->
                                    <div class="form-group-ios mb-4">
                                        <label class="form-label mb-2">Poin Maksimal</label>
                                        <input type="number" class="form-control form-control-ios"
                                            name="poin_tugas" value="100" min="0" required>
                                    </div>

                                    <!-- Batas Waktu -->
                                    <div class="form-group-ios mb-4">
                                        <label class="form-label mb-2">Batas Pengumpulan</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="date" class="form-control form-control-ios"
                                                    name="batas_tanggal" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-ios"
                                                    name="batas_jam" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- File Upload -->
                                    <div class="form-group-ios mb-4">
                                        <label class="form-label m-0 p-0">Lampiran</label>
                                        <p class="text-muted p-0 mt-0 me-0 ms-0 mb-2" style="font-size: 10px;">TIDAK WAJIB</p>
                                        <div class="attachment-box" onclick="document.getElementById('file_tugas').click()">
                                            <input type="file" id="file_tugas" name="file_tugas[]"
                                                class="d-none" multiple onchange="showSelectedFiles(this)">
                                            <div class="attachment-placeholder text-center p-4">
                                                <i class="bi bi-cloud-upload fs-3 mb-2" style="color: rgb(218, 119, 86);"></i>
                                                <p class="mb-0 text-muted">Klik untuk menambah lampiran</p>
                                                <small class="text-muted">atau drag & drop file di sini</small>
                                            </div>
                                            <div id="selectedFiles" class="selected-files mt-2"></div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary py-2 rounded-4">
                                            Buat Tugas
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* iOS Style Modal */
                    .modal-content {
                        border-radius: 16px;
                    }

                    .modal-header {
                        padding: 16px 20px;
                    }

                    .modal-title {
                        font-size: 18px;
                        color: #000;
                    }


                    .form-group-ios label {
                        font-size: 14px;
                        font-weight: 500;
                        color: #374151;
                    }

                    .attachment-box {
                        border: 1px solid #E5E5EA;
                        border-radius: 12px;
                        transition: all 0.2s;
                        cursor: pointer;
                    }

                    .attachment-box:hover {
                        border-color: rgb(218, 119, 86);
                        background-color: rgba(0, 122, 255, 0.05);
                    }

                    .selected-files {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 8px;
                        padding: 8px;
                    }

                    .file-item {
                        background: #F0F0F0;
                        padding: 4px 12px;
                        border-radius: 16px;
                        font-size: 13px;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }

                    .btn-primary {
                        background-color: rgb(218, 119, 86);
                        border: none;
                    }

                    .btn-primary:hover {
                        background-color: #0056b3;
                    }

                    /* Animation */
                    .modal.fade .modal-dialog {
                        transition: transform 0.2s ease-out;
                        transform: scale(0.95);
                    }

                    .modal.show .modal-dialog {
                        transform: scale(1);
                    }

                    /* Responsive */
                    @media (max-width: 576px) {
                        .modal-dialog {
                            margin: 1rem;
                        }

                        .modal-content {
                            border-radius: 14px;
                        }
                    }
                </style>


                <script>
                    function showSelectedFiles(input) {
                        const container = document.getElementById('selectedFiles');
                        container.innerHTML = '';

                        Array.from(input.files).forEach(file => {
                            const fileItem = document.createElement('div');
                            fileItem.className = 'file-item';
                            fileItem.innerHTML = `
                            <i class="bi bi-paperclip"></i>
                            <span>${file.name}</span>
                        `;
                            container.appendChild(fileItem);
                        });
                    }

                    // Drag and drop functionality
                    const dropZone = document.querySelector('.attachment-box');

                    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                        dropZone.addEventListener(eventName, preventDefaults, false);
                    });

                    function preventDefaults(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropZone.addEventListener(eventName, highlight, false);
                    });

                    ['dragleave', 'drop'].forEach(eventName => {
                        dropZone.addEventListener(eventName, unhighlight, false);
                    });

                    function highlight(e) {
                        dropZone.classList.add('border-primary', 'bg-light');
                    }

                    function unhighlight(e) {
                        dropZone.classList.remove('border-primary', 'bg-light');
                    }

                    dropZone.addEventListener('drop', handleDrop, false);

                    function handleDrop(e) {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        const fileInput = document.getElementById('file_tugas');

                        fileInput.files = files;
                        showSelectedFiles(fileInput);
                    }
                </script>

                <!-- modal presentasi -->
                <div class="modal fade" id="modalPresentasi" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header p-0 position-relative" style="height: 120px; overflow: hidden; border-bottom: none;">
                                <img src="assets/header_presentasi_guru.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                            </div>
                            <div class="modal-body">
                                <div>
                                    <h3 class="fw-bold">Presentasikan File Anda</h3>
                                    <p class="text-muted" style="font-size: 12px;">Presentasikan file PDF ataupun PPT Anda, SAGA akan memaksa komputer siswa membuka presentasi Anda tanpa kompromi.</p>
                                </div>

                                <div class="p-3 border bg-light mb-4" style="border-radius: 15px;">
                                    <div class="d-flex">
                                        <i class="bi bi-filetype-pdf fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                        <div>
                                            <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Gunakan File PDF Untuk Presentasi Anda</p>
                                            <p class="p-0 m-0 text-muted" style="font-size: 12px;">Kami sangat merekomendasikan menggunakan file PDF guna fleksibilitas dan kompibilitas server dan sistem presentasi SAGA.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border bg-light mb-4" style="border-radius: 15px;">
                                    <div class="d-flex">
                                        <i class="bi bi-wifi fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                        <div>
                                            <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Pastikan Koneksi Anda dan Siswa Stabil</p>
                                            <p class="p-0 m-0 text-muted" style="font-size: 12px;">Koneksi lambat dapat menyembabkan masalah perpindahan frame yang lambat, serta kegagalan memulai atau mengakhiri presentasi.</p>
                                        </div>
                                    </div>
                                </div>
                                <form id="presentationForm" enctype="multipart/form-data" class="">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Upload File Presentasi</label>
                                        <input type="file" class="form-control" id="presentationFile" name="presentationFile" accept=".pdf,.ppt,.pptx" required>
                                        <div class="form-text" style="font-size: 12px;">Format yang didukung: PDF, PPT, PPTX (Ukuran Maksimal. 20MB)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Total Slide</label>
                                        <input type="number" class="form-control" id="totalSlides" name="totalSlides" min="1" value="20">
                                        <div class="form-text" style="font-size: 12px;">Perkiraan jumlah slide dalam presentasi</div>
                                    </div>

                                    <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                </form>
                            </div>
                            <div class="modal-footer d-flex btn-group justify-content-center">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btnPrimary text-white" id="startPresentationBtn">Mulai Presentasi</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Presentasi View -->
                <div class="modal fade" id="presentationViewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content">
                            <div class="modal-body p-0 bg-dark">
                                <div class="presentation-container d-flex flex-column h-100">
                                    <!-- Header Controls - Minimalist design -->
                                    <div class="p-3 bg-black bg-opacity-75 text-white d-flex justify-content-between align-items-center">
                                        <div class="gap-1">
                                            <h5 class="mb-0" id="presentationTitle">Presentasi Kelas</h5>
                                            <p class="p-0 m-0" style="font-size: 12px;">SMAGAEdu</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span id="slideCounter" class="badge bg-light text-dark px-3 py-2 rounded-pill">Slide 1/20</span>
                                            <button type="button" id="endPresentationBtn" class="btn btn-danger btn-sm px-3 py-2 rounded-pill">
                                                Akhiri Presentasi
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Slide Content - Clean centered design -->
                                    <div class="flex-grow-1 d-flex justify-content-center align-items-center position-relative">
                                        <div class="slide-container bg-white rounded-3" style="width: 85%; height: 85%; position: relative; overflow: hidden;">
                                            <iframe id="slideFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>

                                            <!-- Navigation - Subtle floating buttons -->
                                            <div class="slide-controls">
                                                <button id="prevSlideBtn" class="slide-nav-btn prev-btn">
                                                    <i class="bi bi-chevron-left"></i>
                                                </button>
                                                <button id="nextSlideBtn" class="slide-nav-btn next-btn">
                                                    <i class="bi bi-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer with keyboard shortcuts help -->
                                    <div class="py-2 px-3 bg-black bg-opacity-75 text-white">
                                        <div class="d-flex justify-content-center gap-4 align-items-center">
                                            <span class="d-flex align-items-center gap-2">
                                                <kbd>←</kbd>
                                                <small>Slide sebelumnya</small>
                                            </span>
                                            <span class="d-flex align-items-center gap-2">
                                                <kbd>→</kbd>
                                                <small>Slide berikutnya</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Status Presentasi -->
                <div class="modal fade" id="presentationStatusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                            <div class="modal-header" style="border-bottom: none; padding: 20px 20px 5px 20px;">
                                <h5 class="modal-title fw-bold">Status Presentasi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4">
                                <div class="presentation-status-info mb-4">
                                    <div id="presentationStatusInfo" class="p-3 rounded-4" style="background-color: rgba(218, 119, 86, 0.1);">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 p-2 rounded-circle" style="background: rgba(218, 119, 86, 0.2);">
                                                <i class="bi bi-info-circle" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-medium">Memeriksa status presentasi...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div id="statusLastUpdate" class="text-muted" style="font-size: 13px;">
                                        Pembaruan terakhir: -
                                    </div>
                                    <button id="refreshStatusBtn" class="btn btn-sm px-3 py-1 d-flex align-items-center gap-2"
                                        style="background-color: #f5f5f7; border-radius: 20px; border: none; font-size: 13px;">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer d-flex gap-2 border-top-0 px-4 pb-4">
                                <button type="button" class="btn flex-fill py-2"
                                    style="border-radius: 12px; background-color: #f5f5f7; font-weight: 500; border: none;"
                                    data-bs-dismiss="modal">
                                    Tutup
                                </button>
                                <button type="button" id="forceEndPresentationBtn" class="btn flex-fill py-2 text-white"
                                    style="border-radius: 12px; background-color: rgb(218, 119, 86); font-weight: 500; border: none;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Paksa Akhiri
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Minimalist Presentation View Styles */
                    #presentationViewModal .modal-content {
                        background-color: #121212;
                    }

                    .presentation-container {
                        background-color: #121212;
                    }

                    .slide-container {
                        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.4);
                        transition: all 0.3s ease;
                    }

                    /* Navigation buttons */
                    .slide-nav-btn {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        width: 40px;
                        height: 40px;
                        border: none;
                        background-color: rgba(0, 0, 0, 0.5);
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0.3;
                        transition: opacity 0.2s, background-color 0.2s;
                        z-index: 100;
                    }

                    .slide-nav-btn:hover {
                        opacity: 1;
                        background-color: rgba(0, 0, 0, 0.7);
                    }

                    .slide-nav-btn i {
                        font-size: 1.2rem;
                    }

                    .prev-btn {
                        left: 10px;
                    }

                    .next-btn {
                        right: 10px;
                    }

                    /* Keyboard shortcut styles */
                    kbd {
                        display: inline-block;
                        padding: 2px 6px;
                        font-size: 12px;
                        font-family: monospace;
                        color: #333;
                        background-color: #f8f9fa;
                        border: 1px solid #d3d3d3;
                        border-radius: 3px;
                        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
                    }

                    /* Make navigation buttons more visible on hover over the slide area */
                    .slide-container:hover .slide-nav-btn {
                        opacity: 0.7;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .slide-container {
                            width: 95%;
                            height: 80%;
                        }

                        .slide-nav-btn {
                            width: 36px;
                            height: 36px;
                        }

                        kbd {
                            padding: 1px 4px;
                            font-size: 10px;
                        }
                    }
                </style>




                <script>
                    // Variables
                    let currentSlide = 1;
                    let totalSlides = 20;
                    let presentationFilePath = '';
                    let presentationInterval;

                    // Start Presentation
                    document.getElementById('startPresentationBtn').addEventListener('click', function() {
                        const fileInput = document.getElementById('presentationFile');
                        if (!fileInput.files.length) {
                            alert('Silakan pilih file presentasi terlebih dahulu');
                            return;
                        }

                        // For new file, we need to upload it first
                        const formData = new FormData(document.getElementById('presentationForm'));

                        fetch('upload_presentation.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    presentationFilePath = data.file_path;
                                    presentationFileType = data.file_type || presentationFilePath.split('.').pop().toLowerCase();
                                    if (data.total_slides) {
                                        document.getElementById('totalSlides').value = data.total_slides;
                                    }
                                    startPresentation();
                                } else {
                                    alert('Gagal mengupload file: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat mengupload file');
                            });
                    });


                    // Variables (tambahkan ini)
                    let presentationFileType = 'pdf'; // Default to pdf

                    // Function untuk mendeteksi jenis file dari path
                    function getFileTypeFromPath(path) {
                        if (!path) return '';
                        return path.split('.').pop().toLowerCase();
                    }

                    // Listener untuk pesan dari iframe PDF viewer
                    window.addEventListener("message", function(event) {
                        if (event.data && event.data.type === "viewerReady") {
                            console.log("Presentation viewer ready, slide:", event.data.currentPage || event.data.currentSlide);
                            if (event.data.totalPages) {
                                totalSlides = event.data.totalPages;
                                document.getElementById('slideCounter').textContent = `Slide ${currentSlide}/${totalSlides}`;
                            }
                        } else if (event.data && event.data.type === "pageRendered") {
                            console.log("Page rendered:", event.data.currentPage);
                        } else if (event.data && event.data.type === "zoomChanged") {
                            // Terima perubahan zoom dari PDF viewer
                            presentationZoomScale = event.data.scale;
                            console.log("Zoom changed in viewer:", presentationZoomScale);

                            // Broadcast zoom change to students
                            broadcastZoomChange();
                        } else if (event.data && event.data.type === "annotationChanged") {
                            // Terima perubahan anotasi dari PDF viewer
                            const annotations = event.data.annotations;
                            const page = event.data.page;
                            console.log("Annotations changed in viewer for page", page, annotations);

                            // Broadcast annotation change to students
                            broadcastAnnotationChange(page, annotations);
                        } else if (event.data && event.data.type === "debugLog") {
                            console.log("PDF Viewer Log: " + event.data.message);
                        }
                    });

                    // Fungsi broadcast perubahan zoom
                    function broadcastZoomChange() {
                        console.log(`Broadcasting zoom change to ${presentationZoomScale}...`);

                        fetch('ajax/update_zoom.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `kelas_id=<?php echo $kelas_id; ?>&scale=${presentationZoomScale}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Update zoom response:', data);
                                if (!data.success) {
                                    console.error('Error updating zoom:', data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error updating zoom:', error);
                            });
                    }

                    // Fungsi broadcast perubahan anotasi
                    function broadcastAnnotationChange(page, annotations) {
                        console.log(`Broadcasting annotation change for page ${page}...`, annotations);

                        // Perbaikan: Kirim dengan format yang benar
                        fetch('ajax/update_annotation.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    kelas_id: '<?php echo $kelas_id; ?>',
                                    page: page,
                                    annotations: annotations
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Update annotation response:', data);
                                if (!data.success) {
                                    console.error('Error updating annotation:', data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error updating annotation:', error);
                            });
                    }

                    let presentationZoomScale = 1.5;

                    // Modifikasi startPresentation() untuk menyimpan tipe file
                    function startPresentation() {
                        // Get values
                        totalSlides = parseInt(document.getElementById('totalSlides').value) || 20;
                        currentSlide = 1;
                        presentationZoomScale = 1.5; // Reset zoom scale to default

                        // Show loading
                        const loadingDiv = document.createElement('div');
                        loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75';
                        loadingDiv.style.zIndex = '9999';
                        loadingDiv.innerHTML = '<div class="spinner-border text-light" role="status"></div><span class="text-light ms-3">Memulai presentasi...</span>';
                        document.body.appendChild(loadingDiv);

                        // Update UI
                        document.getElementById('slideCounter').textContent = `Slide ${currentSlide}/${totalSlides}`;

                        // Identify file type
                        presentationFileType = getFileTypeFromPath(presentationFilePath);
                        console.log("Presentation file type:", presentationFileType);

                        // Close input modal
                        bootstrap.Modal.getInstance(document.getElementById('modalPresentasi')).hide();

                        // Broadcast to all students
                        console.log('Broadcasting presentation start...');

                        // Call AJAX endpoint with proper URL
                        fetch('start_presentation_baru.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `kelas_id=<?php echo $kelas_id; ?>&presentation_id=${encodeURIComponent(presentationFilePath)}&total_slides=${totalSlides}&file_type=${presentationFileType}&zoom_scale=${presentationZoomScale}`
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.text().then(text => {
                                        console.error(`HTTP error ${response.status}: ${text}`);
                                        throw new Error(`Error ${response.status}: ${text}`);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Start presentation response:', data);

                                if (data.success) {
                                    // Show presentation view
                                    updateSlideView();

                                    // Open presentation view
                                    const presentationModal = new bootstrap.Modal(document.getElementById('presentationViewModal'));
                                    presentationModal.show();

                                    // Setup slide controls
                                    document.getElementById('prevSlideBtn').addEventListener('click', prevSlide);
                                    document.getElementById('nextSlideBtn').addEventListener('click', nextSlide);
                                    document.getElementById('endPresentationBtn').addEventListener('click', endPresentation);

                                    // Setup keyboard controls
                                    document.addEventListener('keydown', handleKeyboardControls);
                                } else {
                                    alert('Gagal memulai presentasi: ' + (data.message || 'Unknown error'));
                                }

                                // Remove loading overlay
                                document.body.removeChild(loadingDiv);
                            })
                            .catch(error => {
                                console.error('Error starting presentation:', error);
                                alert('Terjadi kesalahan saat memulai presentasi');
                                document.body.removeChild(loadingDiv);
                            });
                    }



                    // Update fungsi updateSlideView
                    function updateSlideView() {
                        document.getElementById('slideCounter').textContent = `Slide ${currentSlide}/${totalSlides}`;

                        const iframe = document.getElementById('slideFrame');

                        // Jika presentationFileType belum diset, ambil dari path file
                        if (!presentationFileType) {
                            presentationFileType = getFileTypeFromPath(presentationFilePath);
                        }

                        const isPdfFile = presentationFileType === 'pdf';

                        // Untuk file PDF
                        if (isPdfFile) {
                            // Jika iframe PDF belum ada atau beda file
                            if (!iframe.src || iframe.src === 'about:blank' || !iframe.src.includes(encodeURIComponent(presentationFilePath))) {
                                iframe.src = `pdf_viewer.php?file=${encodeURIComponent(presentationFilePath)}&page=${currentSlide}&role=teacher`;
                            }
                            // Update page di PDF yang sudah dimuat
                            else {
                                try {
                                    iframe.contentWindow.postMessage({
                                        action: "setPage",
                                        pageNumber: currentSlide
                                    }, "*");
                                } catch (err) {
                                    console.error("Error communicating with PDF viewer:", err);
                                    iframe.src = `pdf_viewer.php?file=${encodeURIComponent(presentationFilePath)}&page=${currentSlide}&role=teacher`;
                                }
                            }
                        }
                        // Untuk PowerPoint files
                        else {
                            // Jika iframe PowerPoint belum ada
                            if (!iframe.src || iframe.src === 'about:blank' || !iframe.src.includes(encodeURIComponent(presentationFilePath))) {
                                iframe.src = `show_slide.php?file=${encodeURIComponent(presentationFilePath)}&slide=${currentSlide}&role=teacher`;
                            }
                            // Update notifikasi slide di PowerPoint yang sudah dimuat
                            else {
                                try {
                                    iframe.contentWindow.postMessage({
                                        action: "setSlide",
                                        slideNumber: currentSlide
                                    }, "*");
                                } catch (err) {
                                    console.error("Error communicating with PowerPoint viewer:", err);
                                }
                            }
                        }

                        // Broadcast slide change to students - Tambahkan retry mechanism
                        broadcastSlideChange();
                    }



                    // Navigation functions
                    function prevSlide() {
                        if (currentSlide > 1) {
                            currentSlide--;
                            updateSlideView();
                        }
                    }

                    function nextSlide() {
                        if (currentSlide < totalSlides) {
                            currentSlide++;
                            updateSlideView();
                        }
                    }

                    // Handle keyboard navigation
                    function handleKeyboardControls(e) {
                        if (document.getElementById('presentationViewModal').classList.contains('show')) {
                            if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
                                nextSlide();
                                e.preventDefault();
                            } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                                prevSlide();
                                e.preventDefault();
                            } else if (e.key === 'Escape') {
                                // Prevent default ESC behavior in presentation mode
                                e.preventDefault();
                                if (confirm('Akhiri presentasi?')) {
                                    endPresentation();
                                }
                            }
                        }
                    }

                    // End presentation
                    // End presentation
                    function endPresentation() {
                        // Tandai bahwa kita sedang dalam proses mengakhiri presentasi
                        let isEndingPresentation = true;
                        let loadingDiv = null;

                        try {
                            // Tampilkan loading
                            loadingDiv = document.createElement('div');
                            loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75';
                            loadingDiv.style.zIndex = '9999';
                            loadingDiv.innerHTML = '<div class="spinner-border text-light" role="status"></div><span class="text-light ms-3">Mengakhiri presentasi...</span>';
                            document.body.appendChild(loadingDiv);
                        } catch (error) {
                            console.error('Error creating loading overlay:', error);
                        }

                        // Broadcast end to students with retry mechanism
                        console.log('Broadcasting end presentation...');

                        // Fungsi untuk retry end presentation
                        const sendEndRequest = (retryCount = 0, maxRetries = 3) => {
                            fetch('ajax/end_presentation.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `kelas_id=<?php echo $kelas_id; ?>`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('End presentation response:', data);

                                    // Remove keyboard listener
                                    document.removeEventListener('keydown', handleKeyboardControls);

                                    // Tutup modal dengan cara yang tepat
                                    const presentationModal = bootstrap.Modal.getInstance(document.getElementById('presentationViewModal'));
                                    if (presentationModal) {
                                        presentationModal.hide();

                                        // Bersihkan backdrop secara manual
                                        setTimeout(() => {
                                            const backdrops = document.querySelectorAll('.modal-backdrop');
                                            backdrops.forEach(backdrop => {
                                                backdrop.remove();
                                            });
                                            document.body.classList.remove('modal-open');
                                            document.body.style.removeProperty('padding-right');
                                            document.body.style.overflow = '';
                                        }, 300); // Sedikit penundaan untuk memastikan animasi modal selesai
                                    }

                                    // Hentikan loading jika masih ada
                                    try {
                                        if (loadingDiv && document.body.contains(loadingDiv)) {
                                            document.body.removeChild(loadingDiv);
                                            loadingDiv = null;
                                        }
                                    } catch (error) {
                                        console.error('Error removing loading overlay:', error);
                                    }

                                    // Tampilkan alert sukses
                                    alert('Presentasi berhasil diakhiri.');

                                    // Cek status presentasi untuk memastikan
                                    showPresentationStatus();
                                })
                                .catch(error => {
                                    console.error('Error ending presentation:', error);

                                    // Retry jika masih ada kesempatan
                                    if (retryCount < maxRetries) {
                                        console.log(`Retrying end presentation (${retryCount + 1}/${maxRetries})...`);
                                        setTimeout(() => {
                                            sendEndRequest(retryCount + 1, maxRetries);
                                        }, 1000);
                                    } else {
                                        // Gagal setelah semua retry
                                        try {
                                            if (loadingDiv && document.body.contains(loadingDiv)) {
                                                document.body.removeChild(loadingDiv);
                                                loadingDiv = null;
                                            }
                                        } catch (error) {
                                            console.error('Error removing loading overlay:', error);
                                        }

                                        alert('Terjadi kesalahan saat mengakhiri presentasi. Mohon coba lagi.');

                                        // Tampilkan modal status untuk pengecekan terakhir
                                        checkPresentationStatus();
                                    }
                                });
                        };

                        // Mulai request dengan retry count 0
                        sendEndRequest();
                    }

                    // Fungsi untuk memeriksa status presentasi
                    function checkPresentationStatus() {
                        // Pastikan elemen ada sebelum mengakses
                        const statusInfoElement = document.getElementById('presentationStatusInfo');

                        // Jika elemen tidak ditemukan, coba buat modal terlebih dahulu
                        if (!statusInfoElement) {
                            console.log('Status info element not found, trying to create modal');
                            try {
                                createStatusModal();
                                // Coba dapatkan elemen lagi setelah modal dibuat
                                const newStatusInfoElement = document.getElementById('presentationStatusInfo');
                                if (!newStatusInfoElement) {
                                    console.error('Failed to create presentationStatusInfo element');
                                    alert('Tidak dapat memeriksa status presentasi. Silahkan refresh halaman.');
                                    return;
                                }
                            } catch (error) {
                                console.error('Error creating status modal:', error);
                                alert('Tidak dapat memeriksa status presentasi. Silahkan refresh halaman.');
                                return;
                            }
                        }

                        // Update UI to loading state
                        const updatedStatusInfoElement = document.getElementById('presentationStatusInfo');
                        if (updatedStatusInfoElement) {
                            updatedStatusInfoElement.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span>Memeriksa status presentasi...</span>
            </div>
        `;
                        }

                        // Add random parameter to prevent caching
                        const cacheBuster = Math.random().toString(36).substring(2, 15);

                        fetch(`ajax/check_presentation_status.php?kelas_id=<?php echo $kelas_id; ?>&_=${cacheBuster}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Presentation status:', data);

                                // Pastikan elemen masih ada sebelum mengakses
                                const statusDiv = document.getElementById('presentationStatusInfo');
                                const lastUpdateDiv = document.getElementById('statusLastUpdate');
                                const forceEndBtn = document.getElementById('forceEndPresentationBtn');

                                if (!statusDiv || !lastUpdateDiv || !forceEndBtn) {
                                    console.error('One or more required elements not found');
                                    // Fallback dengan alert untuk menampilkan hasil
                                    if (data.success) {
                                        const statusMessage = data.active ?
                                            'Presentasi masih aktif! Beberapa siswa mungkin masih terjebak dalam mode presentasi.' :
                                            'Presentasi telah berakhir. Semua siswa seharusnya telah keluar dari mode presentasi.';
                                        alert(statusMessage);
                                    } else {
                                        alert('Error: ' + (data.message || 'Tidak dapat memeriksa status presentasi.'));
                                    }
                                    return;
                                }

                                if (data.success) {
                                    // Format timestamp
                                    const updatedAt = new Date(data.updated_at);
                                    const formattedDate = updatedAt.toLocaleString('id-ID');

                                    // Update last update timestamp
                                    lastUpdateDiv.textContent = `Pembaruan terakhir: ${formattedDate}`;

                                    // Check if presentation is still active
                                    if (data.active) {
                                        statusDiv.className = 'alert alert-warning';
                                        statusDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Presentasi masih aktif!</strong> 
                        <p class="mb-0 mt-1">Beberapa siswa mungkin masih terjebak dalam mode presentasi. 
                        Klik tombol "Paksa Akhiri" untuk menutup presentasi pada semua siswa.</p>
                    `;
                                        forceEndBtn.removeAttribute('disabled');
                                    } else {
                                        statusDiv.className = 'alert alert-success';
                                        statusDiv.innerHTML = `
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Presentasi telah berakhir</strong>
                        <p class="mb-0 mt-1">Semua siswa seharusnya telah keluar dari mode presentasi.</p>
                    `;
                                        forceEndBtn.setAttribute('disabled', 'disabled');
                                    }
                                } else {
                                    statusDiv.className = 'alert alert-danger';
                                    statusDiv.innerHTML = `
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <strong>Error!</strong> ${data.message || 'Tidak dapat memeriksa status presentasi.'}
                `;
                                }
                            })
                            .catch(error => {
                                console.error('Error checking presentation status:', error);

                                // Fallback dengan alert jika modal elements tidak ditemukan
                                const statusDiv = document.getElementById('presentationStatusInfo');
                                if (!statusDiv) {
                                    alert('Error: Gagal terhubung ke server saat memeriksa status presentasi.');
                                    return;
                                }

                                statusDiv.className = 'alert alert-danger';
                                statusDiv.innerHTML = `
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>Error!</strong> Gagal terhubung ke server.
            `;
                            });
                    }

                    // Tambahkan di bagian DOMContentLoaded atau setelah definisi modal
                    document.getElementById('presentationStatusModal').addEventListener('hidden.bs.modal', function() {
                        console.log('Status modal hidden, performing cleanup');
                        // Anda bisa melakukan pembersihan di sini jika diperlukan

                        // Contoh: Jika statusnya sudah sukses dan tidak aktif, tawarkan refresh halaman
                        const statusDiv = document.getElementById('presentationStatusInfo');
                        if (statusDiv && statusDiv.classList.contains('alert-success')) {
                            // Create and show a modal instead of using confirm()
                            const refreshModalHTML = `
                            <div class="modal fade" id="refreshConfirmModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius: 16px;">
                                        <div class="modal-body text-center p-4">
                                            <i class="bi bi-check-circle" style="font-size: 3rem; color:rgb(218, 119, 86);"></i>
                                            <h5 class="mt-3 fw-bold">Presentasi Berhasil Diakhiri</h5> 
                                            <p class="mb-4">Presentasi telah berakhir. Refresh halaman untuk memastikan semua siswa sudah keluar dari mode presentasi?</p>

                                            <div class="alert border text-start bg-light mb-4" style="border-radius: 15px;">
                                                <div class="d-flex">
                                                    <i class="bi bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                                    <div>
                                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Mulai Ulang Halaman Diperlukan</p>
                                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Guna memastikan status akhiri presentasi Anda menyeluruh ke semua siswa dalam database, maka kami sangat merekomendasikan Anda untuk menyegarkan halaman kelas Anda.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 btn-group justify-content-center">
                                                <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Nanti saja</button>
                                                <button type="button" id="confirmRefreshBtn" class="btn btnPrimary text-white px-4" style="border-radius: 12px;">Segarkan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                            // Add modal to DOM if it doesn't exist
                            if (!document.getElementById('refreshConfirmModal')) {
                                document.body.insertAdjacentHTML('beforeend', refreshModalHTML);

                                // Add event listener to refresh button
                                document.getElementById('confirmRefreshBtn').addEventListener('click', function() {
                                    window.location.reload();
                                });
                            }

                            // Show the modal
                            const refreshModal = new bootstrap.Modal(document.getElementById('refreshConfirmModal'));
                            refreshModal.show();
                        }
                    });

                    // Fungsi untuk membuat modal status secara dinamis jika belum ada
                    function createStatusModal() {
                        const modalHTML = `
    <!-- Modal Status Presentasi -->
    <div class="modal fade" id="presentationStatusModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Status Presentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="presentation-status-info mb-3">
                        <div id="presentationStatusInfo" class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Memeriksa status presentasi...
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div id="statusLastUpdate" class="text-muted small">
                            Last update: -
                        </div>
                        <button id="refreshStatusBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="forceEndPresentationBtn" class="btn btn-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Paksa Akhiri untuk Semua Siswa
                    </button>
                </div>
            </div>
        </div>
    </div>`;

                        // Tambahkan modal ke body
                        document.body.insertAdjacentHTML('beforeend', modalHTML);

                        // Tambahkan event listener setelah modal dibuat
                        document.getElementById('refreshStatusBtn').addEventListener('click', checkPresentationStatus);
                        document.getElementById('forceEndPresentationBtn').addEventListener('click', function() {
                            if (confirm('Apakah Anda yakin ingin paksa mengakhiri presentasi untuk semua siswa?')) {
                                forceEndPresentation();
                            }
                        });
                    }

                    // Tambahkan event listener untuk tombol refresh status
                    document.addEventListener('DOMContentLoaded', function() {
                        // Pastikan element sudah ada sebelum menambahkan event listener
                        if (document.getElementById('refreshStatusBtn')) {
                            document.getElementById('refreshStatusBtn').addEventListener('click', checkPresentationStatus);
                        }

                        // Event listener untuk tombol force end
                        if (document.getElementById('forceEndPresentationBtn')) {
                            document.getElementById('forceEndPresentationBtn').addEventListener('click', function() {
                                if (confirm('Apakah Anda yakin ingin paksa mengakhiri presentasi untuk semua siswa?')) {
                                    forceEndPresentation();
                                }
                            });
                        }
                    });

                    function forceEndPresentation() {
                        // Tampilkan loading
                        const loadingDiv = document.createElement('div');
                        loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75';
                        loadingDiv.style.zIndex = '9999';
                        loadingDiv.innerHTML = '<div class="spinner-border text-light" role="status"></div><span class="text-light ms-3">Mengakhiri presentasi...</span>';
                        document.body.appendChild(loadingDiv);

                        // Lakukan request dengan retry mechanism
                        const sendForceEndRequest = (retryCount = 0, maxRetries = 3) => {
                            fetch('ajax/force_end_presentation.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `kelas_id=<?php echo $kelas_id; ?>&cacheBuster=${Date.now()}`
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Force end presentation response:', data);

                                    // Double check status with additional verification request
                                    setTimeout(() => {
                                        verifyPresentationEnded();
                                    }, 1000);

                                    // Remove loading overlay
                                    if (document.body.contains(loadingDiv)) {
                                        document.body.removeChild(loadingDiv);
                                    }

                                    if (data.success) {
                                        alert('Presentasi berhasil diakhiri untuk semua siswa.');

                                        // Refresh status display if status modal is open
                                        if (document.getElementById('presentationStatusModal').classList.contains('show')) {
                                            checkPresentationStatus();
                                        }
                                    } else {
                                        alert('Error: ' + (data.message || 'Gagal mengakhiri presentasi.'));

                                        // Retry jika masih ada kesempatan
                                        if (retryCount < maxRetries) {
                                            setTimeout(() => {
                                                sendForceEndRequest(retryCount + 1, maxRetries);
                                            }, 1000);
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error force ending presentation:', error);

                                    // Remove loading overlay
                                    if (document.body.contains(loadingDiv)) {
                                        document.body.removeChild(loadingDiv);
                                    }

                                    // Retry jika masih ada kesempatan
                                    if (retryCount < maxRetries) {
                                        console.log(`Retrying force end (${retryCount + 1}/${maxRetries})...`);
                                        setTimeout(() => {
                                            sendForceEndRequest(retryCount + 1, maxRetries);
                                        }, 1000);
                                    } else {
                                        alert('Gagal mengakhiri presentasi setelah beberapa kali mencoba. Silakan refresh halaman dan coba lagi.');
                                    }
                                });
                        };

                        // Start dengan retry count 0
                        sendForceEndRequest();
                    }

                    // Fungsi tambahan untuk verifikasi status presentasi
                    function verifyPresentationEnded() {
                        fetch(`ajax/check_presentation_status.php?kelas_id=<?php echo $kelas_id; ?>&_=${Math.random()}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Verification status:', data);

                                if (data.success && data.active) {
                                    // Jika masih aktif, coba paksa sekali lagi
                                    console.log('Presentation still active after force end, trying again...');

                                    fetch('ajax/force_end_presentation.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded',
                                            },
                                            body: `kelas_id=<?php echo $kelas_id; ?>&forcedRetry=true&cacheBuster=${Date.now()}`
                                        })
                                        .then(response => response.json())
                                        .then(secondData => {
                                            console.log('Second force end attempt:', secondData);
                                        })
                                        .catch(error => {
                                            console.error('Error in second force end attempt:', error);
                                        });
                                }
                            })
                            .catch(error => {
                                console.error('Error verifying presentation status:', error);
                            });
                    }

                    // Tambahkan di bawah definisi modal presentasi atau dalam document.ready
                    document.getElementById('presentationViewModal').addEventListener('hidden.bs.modal', function() {
                        // Bersihkan backdrop secara manual
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => {
                            backdrop.remove();
                        });
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                        document.body.style.overflow = '';
                    });

                    // Broadcast functions
                    function broadcastPresentationStart() {
                        fetch('start_presentation_baru.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `kelas_id=<?php echo $kelas_id; ?>&file_path=${encodeURIComponent(presentationFilePath)}&total_slides=${totalSlides}`
                        });
                    }

                    // Fungsi terpisah untuk broadcast slide change dengan retry
                    function broadcastSlideChange() {
                        console.log(`Broadcasting slide change to ${currentSlide}...`);

                        // Buat fungsi untuk retry request
                        const sendRequest = (retryCount = 0, maxRetries = 3) => {
                            fetch('ajax/update_slide.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `kelas_id=<?php echo $kelas_id; ?>&current_slide=${currentSlide}`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Update slide response:', data);
                                    if (!data.success) {
                                        console.error('Error updating slide:', data.message);

                                        // Jika "No active presentation found", dan masih ada retry tersisa
                                        if (data.message.includes("No active presentation found") && retryCount < maxRetries) {
                                            console.log(`Retrying slide update (${retryCount + 1}/${maxRetries})...`);
                                            // Tunggu 500ms sebelum retry
                                            setTimeout(() => {
                                                sendRequest(retryCount + 1, maxRetries);
                                            }, 500);
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating slide:', error);

                                    // Retry jika masih ada kesempatan
                                    if (retryCount < maxRetries) {
                                        console.log(`Retrying slide update after error (${retryCount + 1}/${maxRetries})...`);
                                        setTimeout(() => {
                                            sendRequest(retryCount + 1, maxRetries);
                                        }, 500);
                                    }
                                });
                        };

                        // Mulai request dengan retry count 0
                        sendRequest();
                    }

                    // Check for active presentation when page loads
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('Checking for active presentation on page load...');
                        checkForTeacherActivePresentation();

                        // Existing event listeners...
                        if (document.getElementById('refreshStatusBtn')) {
                            document.getElementById('refreshStatusBtn').addEventListener('click', checkPresentationStatus);
                        }

                        if (document.getElementById('forceEndPresentationBtn')) {
                            document.getElementById('forceEndPresentationBtn').addEventListener('click', function() {
                                if (confirm('Apakah Anda yakin ingin paksa mengakhiri presentasi untuk semua siswa?')) {
                                    forceEndPresentation();
                                }
                            });
                        }
                    });

                    // Check if teacher has an active presentation
                    function checkForTeacherActivePresentation() {
                        // Add random parameter to prevent caching
                        const cacheBuster = Math.random().toString(36).substring(2, 15);

                        fetch(`ajax/check_teacher_presentation.php?kelas_id=<?php echo $kelas_id; ?>&_=${cacheBuster}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Teacher active presentation check:', data);

                                if (data.success && data.active) {
                                    // Presentasi aktif ditemukan, muat kembali modal presentasi
                                    console.log('Active presentation found, restoring presentation view...');

                                    // Set variabel global yang dibutuhkan
                                    presentationFilePath = data.file_path;
                                    presentationFileType = getFileTypeFromPath(data.file_path);
                                    totalSlides = data.total_slides;
                                    currentSlide = data.current_slide;
                                    presentationZoomScale = data.zoom_scale || 1.5;

                                    // Perbaharui UI slide counter
                                    document.getElementById('slideCounter').textContent = `Slide ${currentSlide}/${totalSlides}`;

                                    // Tampilkan modal presentasi
                                    updateSlideView();
                                    const presentationModal = new bootstrap.Modal(document.getElementById('presentationViewModal'));
                                    presentationModal.show();

                                    // Setup slide controls
                                    document.getElementById('prevSlideBtn').addEventListener('click', prevSlide);
                                    document.getElementById('nextSlideBtn').addEventListener('click', nextSlide);
                                    document.getElementById('endPresentationBtn').addEventListener('click', endPresentation);

                                    // Setup keyboard controls
                                    document.addEventListener('keydown', handleKeyboardControls);

                                    console.log('Presentation view restored successfully');
                                } else {
                                    console.log('No active presentation found for this teacher');
                                }
                            })
                            .catch(error => {
                                console.error('Error checking for teacher active presentation:', error);
                            });
                    }

                    // Fungsi fallback untuk memeriksa status presentasi dengan alert
                    function checkPresentationStatusWithAlert() {
                        alert('Memeriksa status presentasi...');

                        // Add random parameter to prevent caching
                        const cacheBuster = Math.random().toString(36).substring(2, 15);

                        fetch(`ajax/check_presentation_status.php?kelas_id=<?php echo $kelas_id; ?>&_=${cacheBuster}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Presentation status (alert mode):', data);

                                if (data.success) {
                                    const updatedAt = new Date(data.updated_at);
                                    const formattedDate = updatedAt.toLocaleString('id-ID');

                                    if (data.active) {
                                        if (confirm(`Presentasi masih aktif! (Update: ${formattedDate})\n\nBeberapa siswa mungkin masih terjebak dalam mode presentasi.\n\nApakah Anda ingin paksa mengakhiri presentasi untuk semua siswa?`)) {
                                            forceEndPresentationWithAlert();
                                        }
                                    } else {
                                        alert(`Presentasi telah berakhir (Update: ${formattedDate})\n\nSemua siswa seharusnya telah keluar dari mode presentasi.`);
                                    }
                                } else {
                                    alert('Error: ' + (data.message || 'Tidak dapat memeriksa status presentasi.'));
                                }
                            })
                            .catch(error => {
                                console.error('Error checking presentation status:', error);
                                alert('Error: Gagal terhubung ke server saat memeriksa status presentasi.');
                            });
                    }

                    // Fungsi fallback untuk paksa mengakhiri presentasi dengan alert
                    function forceEndPresentationWithAlert() {
                        alert('Memproses permintaan untuk mengakhiri presentasi...');

                        fetch('ajax/force_end_presentation.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `kelas_id=<?php echo $kelas_id; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Force end presentation response (alert mode):', data);

                                if (data.success) {
                                    alert('Berhasil! Presentasi telah diakhiri untuk semua siswa.');
                                    // Refresh halaman setelah konfirmasi
                                    if (confirm('Presentasi berhasil diakhiri. Refresh halaman sekarang?')) {
                                        window.location.reload();
                                    }
                                } else {
                                    alert('Error: ' + (data.message || 'Gagal mengakhiri presentasi.'));
                                }
                            })
                            .catch(error => {
                                console.error('Error force ending presentation:', error);
                                alert('Error: Gagal terhubung ke server. Silahkan coba lagi.');
                            });
                    }

                    // Fungsi untuk menampilkan modal status presentasi
                    function showPresentationStatus() {
                        // Pastikan modal ada
                        const modalElement = document.getElementById('presentationStatusModal');
                        if (!modalElement) {
                            console.log('Modal not found, trying to create modal');
                            try {
                                createStatusModal();
                            } catch (error) {
                                console.error('Error creating status modal:', error);
                                alert('Tidak dapat menampilkan status presentasi. Silahkan refresh halaman.');
                                return;
                            }
                        }

                        // Tampilkan modal terlebih dahulu
                        const statusModal = new bootstrap.Modal(document.getElementById('presentationStatusModal'));
                        statusModal.show();

                        // Kemudian periksa status untuk mengisi konten modal
                        checkPresentationStatus();
                    }
                </script>

                <!-- Modal Edit Background -->
                <div class="modal fade" id="modalEditBackground" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-4">
                                <!-- Simplified Tab Navigation -->
                                <ul class="nav nav-tabs d-flex gap-3 mb-4 border-0">
                                    <li class="nav-item flex-grow-1">
                                        <button class="btn w-100 position-relative tab-btn active"
                                            id="upload-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#upload"
                                            type="button"
                                            role="tab"
                                            aria-controls="upload"
                                            aria-selected="true">
                                            <i class="bi bi-upload me-2"></i>Upload
                                            <div class="tab-indicator"></div>
                                        </button>
                                    </li>
                                    <li class="nav-item flex-grow-1">
                                        <button class="btn w-100 position-relative tab-btn"
                                            id="preset-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#preset"
                                            type="button"
                                            role="tab"
                                            aria-controls="preset"
                                            aria-selected="false">
                                            <i class="bi bi-grid me-2"></i>Template
                                            <div class="tab-indicator"></div>
                                        </button>
                                    </li>
                                </ul>

                                <style>
                                    .tab-btn {
                                        padding: 10px;
                                        border: none;
                                        background: transparent;
                                        color: #666;
                                        font-weight: 500;
                                        transition: all 0.3s ease;
                                    }

                                    .tab-btn:hover {
                                        background: rgba(218, 119, 86, 0.1);
                                    }

                                    .tab-btn.active {
                                        color: rgb(218, 119, 86);
                                    }

                                    .tab-indicator {
                                        position: absolute;
                                        bottom: 0;
                                        left: 0;
                                        width: 100%;
                                        height: 2px;
                                        background: rgb(218, 119, 86);
                                        transform: scaleX(0);
                                        transition: transform 0.3s ease;
                                    }

                                    .tab-btn.active .tab-indicator {
                                        transform: scaleX(1);
                                    }
                                </style>

                                <style>
                                    /* Additional responsive styles */
                                    .nav-pills {
                                        gap: 8px;
                                    }

                                    .nav-pills .nav-link {
                                        white-space: nowrap;
                                        font-size: 14px;
                                    }

                                    @media (max-width: 576px) {
                                        .nav-pills .nav-link {
                                            padding: 8px 12px;
                                        }

                                        .nav-pills .nav-link i {
                                            font-size: 16px;
                                        }
                                    }
                                </style>


                                <!-- Tab content -->
                                <div class="tab-content">
                                    <!-- Upload Tab -->
                                    <div class="tab-pane fade show active" id="upload">
                                        <!-- File Upload Zone -->
                                        <div class="upload-zone p-4 text-center mb-4 rounded-3 border-2 border-dashed">
                                            <p class="mb-2"></p>
                                            <div class="d-flex justify-content-center">
                                                <input type="file" class="form-control d-none" id="imageUpload" accept="image/*">
                                                <label for="imageUpload" class="btn btnPrimary text-white">
                                                    <i class="bi bi-image me-2"></i>Pilih File
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">Format yang didukung: JPG, PNG (Max. 5MB)</small>
                                        </div>

                                        <!-- Image Cropper Container -->
                                        <div class="cropper-container rounded-3 overflow-hidden" style="display: none;">
                                            <div class="img-container bg-light" style="max-height: 400px;">
                                                <img id="image" src="" alt="Preview">
                                            </div>

                                            <!-- Cropper Controls -->
                                            <div class="cropper-controls p-3 bg-light border-top">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="btn btn-light border" id="rotateLeft" title="Rotate Left">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                    <button class="btn btn-light border" id="rotateRight" title="Rotate Right">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                    <button class="btn btn-light border" id="zoomIn" title="Zoom In">
                                                        <i class="bi bi-zoom-in"></i>
                                                    </button>
                                                    <button class="btn btn-light border" id="zoomOut" title="Zoom Out">
                                                        <i class="bi bi-zoom-out"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preset Tab -->
                                    <div class="tab-pane fade" id="preset">
                                        <!-- Category Selection -->
                                        <div class="mb-4">
                                            <select class="form-select" id="wallpaperCategory">
                                                <option value="abstract">Abstrak</option>
                                                <option value="nature">Pemandangan</option>
                                                <option value="city">Kota</option>
                                                <option value="education">Sekolah</option>
                                                <option value="geometric">Geometris</option>
                                                <option value="animals">Binatang</option>
                                                <option value="people">Orang</option>
                                                <option value="3D">3 Dimensi</option>
                                            </select>
                                        </div>

                                        <div class="row g-3" id="templateContainer">
                                            <!-- Templates will be loaded here -->
                                        </div>

                                        <!-- Loading State -->
                                        <div id="loadingState" class="text-center py-3" style="display: none;">
                                            <div class="dot-loader">
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                                <div class="dot"></div>
                                            </div>
                                            <small class="text-muted">Memuat gambar...</small>
                                        </div>

                                        <style>
                                            .dot-loader {
                                                display: flex;
                                                justify-content: center;
                                                align-items: center;
                                                gap: 6px;
                                                margin-bottom: 8px;
                                            }

                                            .dot {
                                                width: 8px;
                                                height: 8px;
                                                background: rgb(218, 119, 86);
                                                border-radius: 50%;
                                                animation: dot-pulse 1s infinite ease-in-out;
                                            }

                                            .dot:nth-child(2) {
                                                animation-delay: 0.2s;
                                            }

                                            .dot:nth-child(3) {
                                                animation-delay: 0.4s;
                                            }

                                            @keyframes dot-pulse {

                                                0%,
                                                100% {
                                                    transform: scale(0.8);
                                                    opacity: 0.5;
                                                }

                                                50% {
                                                    transform: scale(1.2);
                                                    opacity: 1;
                                                }
                                            }
                                        </style>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer btn-group border-0">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btnPrimary text-white" id="saveBackground">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    const ACCESS_KEY = 'R6vdUxS6wLBLRtUyNid3QG7mHGaZ9GJXKB6KurAw-X0';
                    let cropper;
                    const imageElement = document.getElementById('image');
                    const cropperContainer = document.querySelector('.cropper-container');
                    const loadingState = document.getElementById('loadingState');
                    const templateContainer = document.getElementById('templateContainer');
                    const categorySelect = document.getElementById('wallpaperCategory');

                    // Load templates when category changes
                    categorySelect.addEventListener('change', loadBackgroundTemplates);

                    async function loadBackgroundTemplates() {
                        const category = categorySelect.value;
                        templateContainer.style.display = 'none';
                        loadingState.style.display = 'block';

                        try {
                            const response = await fetch(`https://api.unsplash.com/photos/random?query=${category}&count=9&orientation=landscape`, {
                                headers: {
                                    'Authorization': `Client-ID ${ACCESS_KEY}`
                                }
                            });

                            // Cek rate limit dari response headers
                            const rateLimit = {
                                limit: response.headers.get('x-ratelimit-limit'),
                                remaining: response.headers.get('x-ratelimit-remaining')
                            };

                            if (!response.ok) {
                                if (response.status === 403) {
                                    templateContainer.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center p-3 rounded-3 mb-3" style="background-color: #fff3cd; border: 1px solid #ffeeba;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="warning-icon rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 32px; height: 32px; background: rgba(255, 193, 7, 0.2);">
                                <i class="bi bi-hourglass-split text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1" style="font-size: 14px;">Mohon Maaf, Batas Permintaan Telah Tercapai</h6>
                                <p class="mb-0 text-muted" style="font-size: 12px;">
                                    Mohon tunggu 1 jam lagi untuk memuat mengambil data
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                                    return;
                                }
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const images = await response.json();

                            // Tambahkan peringatan jika sisa kuota sedikit (misalnya kurang dari 10)
                            let warningMessage = '';
                            if (rateLimit.remaining < 10) {
                                warningMessage = `
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span>Sisa kuota API terbatas: ${rateLimit.remaining}/${rateLimit.limit} requests</span>
            </div>
            `;
                            }

                            templateContainer.innerHTML = warningMessage + `
        <div class="template-grid">
        ${images.map(image => `
        <div class="template-image-container" 
             data-image-url="${image.urls.regular}" 
             data-photographer="${image.user.name}" 
             data-profile-url="${image.user.links.html}">
            <img src="${image.urls.small}" 
             class="template-image" 
             alt="Background template"
             loading="lazy">
            <div class="template-overlay">
                <span class="select-text">Pilih</span>
            </div>
            <div class="attribution">
                <small>Photo by <a href="${image.user.links.html}" target="_blank">${image.user.name}</a> on <a href="https://unsplash.com" target="_blank">Unsplash</a></small>
            </div>
        </div>
        `).join('')}
        </div>

        <style>
        .template-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); 
        gap: 8px;
        padding: 8px;
        }

        .template-image-container {
        position: relative;
        aspect-ratio: 16/9;
        overflow: hidden;
        border-radius: 8px;
        cursor: pointer;
        }

        .template-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
        }

        .template-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        }

        .attribution {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 4px 8px;
            font-size: 10px;
            z-index: 2;
        }
        
        .attribution a {
            color: #fff;
            text-decoration: underline;
        }

        .template-image-container:hover .template-image {
        transform: scale(1.1);
        }

        .template-image-container:hover .template-overlay {
        opacity: 1;
        }

        .select-text {
        color: white;
        font-size: 14px;
        padding: 6px 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
        }

        @media (max-width: 768px) {
        .template-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        }
        </style>
    `;

                            // Event listeners
                            const containers = templateContainer.querySelectorAll('.template-image-container');
                            containers.forEach(container => {
                                container.addEventListener('click', function() {
                                    const imageUrl = this.dataset.imageUrl;
                                    const photographer = this.dataset.photographer;
                                    const profileUrl = this.dataset.profileUrl;
                                    selectPresetBackground(imageUrl, photographer, profileUrl);
                                });
                            });

                        } catch (error) {
                            console.error('Error:', error);
                            templateContainer.innerHTML = `
            <div class="alert alert-danger text-center" role="alert">
                <i class="bi bi-x-circle me-2"></i>
                <strong>Gagal memuat template background</strong>
                <p class="mb-0 mt-2">Terjadi kesalahan saat mengambil data dari server.</p>
            </div>
        `;
                        } finally {
                            loadingState.style.display = 'none';
                            templateContainer.style.display = 'block';
                        }
                    }

                    async function selectPresetBackground(imageUrl, photographer, profileUrl) {
                        try {
                            console.log('Selecting image:', imageUrl);
                            templateContainer.style.display = 'none';
                            loadingState.style.display = 'block';

                            const response = await fetch(imageUrl);
                            const blob = await response.blob();

                            // Kirim event download ke Unsplash - ini penting untuk memenuhi persyaratan API
                            fetch(`https://api.unsplash.com/photos/download?client_id=${ACCESS_KEY}`, {
                                method: 'GET',
                                headers: {
                                    'Accept-Version': 'v1'
                                }
                            }).catch(err => console.error('Error triggering download event:', err));

                            const reader = new FileReader();
                            reader.onload = function(e) {
                                console.log('Image loaded successfully');
                                loadingState.style.display = 'none';

                                if (cropper) {
                                    cropper.destroy();
                                }

                                // Pindah ke tab upload
                                const uploadTab = document.querySelector('#upload-tab');
                                uploadTab.click();

                                // Sembunyikan zona upload file
                                const uploadZone = document.querySelector('.upload-zone');
                                if (uploadZone) {
                                    uploadZone.style.display = 'none';
                                }

                                imageElement.src = e.target.result;
                                cropperContainer.style.display = 'block';

                                // Tambahkan atribusi photographer
                                const attributionDiv = document.createElement('div');
                                attributionDiv.className = 'photographer-attribution mt-2 text-center small text-muted';
                                attributionDiv.innerHTML = `Photo by <a href="${profileUrl}" target="_blank">${photographer}</a> on <a href="https://unsplash.com" target="_blank">Unsplash</a>`;

                                // Tambahkan atribusi setelah container cropper
                                cropperContainer.insertAdjacentElement('afterend', attributionDiv);

                                // Simpan atribusi untuk dikirim ke server
                                cropperContainer.dataset.photographer = photographer;
                                cropperContainer.dataset.profileUrl = profileUrl;

                                cropper = new Cropper(imageElement, {
                                    aspectRatio: 16 / 9,
                                    viewMode: 1,
                                    dragMode: 'move',
                                    autoCropArea: 1,
                                    restore: false,
                                    modal: false,
                                    guides: false,
                                    highlight: false,
                                    cropBoxMovable: false,
                                    cropBoxResizable: false,
                                    toggleDragModeOnDblclick: false,
                                });
                            };
                            reader.readAsDataURL(blob);

                        } catch (error) {
                            console.error('Error selecting background:', error);
                            alert('Terjadi kesalahan saat memilih background');
                            loadingState.style.display = 'none';
                            templateContainer.style.display = 'block';

                            // Tampilkan kembali zona upload jika terjadi error
                            const uploadZone = document.querySelector('.upload-zone');
                            if (uploadZone) {
                                uploadZone.style.display = 'block';
                            }
                        }
                    }

                    // Load templates when preset tab is shown
                    document.querySelector('#preset-tab').addEventListener('click', loadBackgroundTemplates);

                    // Save background handler
                    // Save background handler
                    document.getElementById('saveBackground').addEventListener('click', function() {
                        if (cropper) {
                            const canvas = cropper.getCroppedCanvas({
                                width: 1920,
                                height: 1080
                            });

                            canvas.toBlob(function(blob) {
                                const formData = new FormData();
                                formData.append('image', blob, 'background.jpg');
                                formData.append('kelas_id', '<?php echo $kelas_id; ?>');

                                // Tambahkan informasi photographer untuk disimpan di database
                                const photographer = cropperContainer.dataset.photographer || '';
                                const profileUrl = cropperContainer.dataset.profileUrl || '';
                                formData.append('photographer', photographer);
                                formData.append('profile_url', profileUrl);

                                fetch('save_background.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            location.reload();
                                        } else {
                                            alert('Gagal menyimpan background: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Terjadi kesalahan saat menyimpan');
                                    });
                            }, 'image/jpeg');
                        } else {
                            alert('Silakan pilih dan crop gambar terlebih dahulu');
                        }
                    });

                    // File upload handler
                    document.getElementById('imageUpload').addEventListener('change', function(e) {
                        const files = e.target.files;
                        const reader = new FileReader();

                        reader.onload = function() {
                            if (cropper) {
                                cropper.destroy();
                            }

                            imageElement.src = reader.result;
                            cropperContainer.style.display = 'block';

                            cropper = new Cropper(imageElement, {
                                aspectRatio: 16 / 9,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 1,
                                restore: false,
                                modal: false,
                                guides: false,
                                highlight: false,
                                cropBoxMovable: false,
                                cropBoxResizable: false,
                                toggleDragModeOnDblclick: false,
                            });
                        };

                        if (files && files[0]) {
                            reader.readAsDataURL(files[0]);
                        }
                    });

                    // Cropper control handlers
                    document.getElementById('rotateLeft').addEventListener('click', () => cropper.rotate(-90));
                    document.getElementById('rotateRight').addEventListener('click', () => cropper.rotate(90));
                    document.getElementById('zoomIn').addEventListener('click', () => cropper.zoom(0.1));
                    document.getElementById('zoomOut').addEventListener('click', () => cropper.zoom(-0.1));

                    // Tambahkan event listener untuk modal
                    document.getElementById('modalEditBackground').addEventListener('hidden.bs.modal', function() {
                        // Reset tampilan upload zone
                        const uploadZone = document.querySelector('.upload-zone');
                        if (uploadZone) {
                            uploadZone.style.display = 'block';
                        }

                        // Reset cropper jika ada
                        if (cropper) {
                            cropper.destroy();
                            cropperContainer.style.display = 'none';
                        }
                    });
                </script>

                <style>
                    /* Existing styles remain the same */
                    .loading-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(255, 255, 255, 0.8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                    }

                    #wallpaperCategory {
                        border-color: #dee2e6;
                        border-radius: 8px;
                        padding: 0.5rem;
                    }

                    #wallpaperCategory:focus {
                        border-color: rgb(218, 119, 86);
                        box-shadow: 0 0 0 0.2rem rgba(218, 119, 86, 0.25);
                    }
                </style>


                <!-- Modal Tambah Postingan -->
                <div class="modal fade" id="modalTambahPostingan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <style>
                        @media (max-width: 576px) {
                            .modal-dialog {
                                max-width: none;
                            }

                            .modal-content {
                                border-radius: 0.5rem !important;
                                height: auto !important;
                                min-height: auto !important;
                            }
                        }
                    </style>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="tambah_postingan.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <!-- Info Profil -->
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                            alt="Profile"
                                            class="rounded-circle"
                                            width="40"
                                            height="40"
                                            style="object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($guru['namaLengkap']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($guru['jabatan']); ?></small>
                                        </div>
                                    </div>


                                    <!-- Text Area -->
                                    <div class="position-relative mb-3">
                                        <textarea class="form-control bg-light border"
                                            name="konten"
                                            id="postContent"
                                            placeholder="Apa yang ingin Anda bagikan?"
                                            style="height: 150px; resize: none; font-size:1.5rem"
                                            required></textarea>

                                    </div>

                                    <!-- Preview Container -->
                                    <div id="previewContainer" class="mb-3 d-none">
                                        <div class="border rounded-3 p-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Lampiran</small>
                                                <button type="button" class="btn-close" onclick="clearFileInput()"></button>
                                            </div>
                                            <div id="filePreview" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                </div>

                                <!-- Footer Actions -->
                                <div class="modal-footer border-0 position-relative">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <!-- Attachment Button -->
                                        <div class="btn-group">
                                            <!-- Modifikasi di Modal Tambah Postingan di kelas_guru.php -->
                                            <input type="file" class="d-none" id="fileInput" name="lampiran[]" multiple
                                                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,video/mp4,video/webm,video/ogg" onchange="handleFileSelect(this)">

                                            <div class="d-flex justify-content-between gap-2">
                                                <!-- Attachment Button -->
                                                <button type="button"
                                                    class="btn btn-light btn-sm d-flex align-items-center gap-2 px-3"
                                                    onclick="document.getElementById('fileInput').click()"
                                                    title="Tambah Lampiran">
                                                    <i class="bi bi-paperclip"></i>
                                                    <span class="d-none d-md-inline" style="font-size: 12px;">Lampiran</span>
                                                </button>

                                                <!-- Image Button -->
                                                <button type="button"
                                                    class="btn btn-light btn-sm d-flex align-items-center gap-2 px-3"
                                                    onclick="document.getElementById('fileInput').click()"
                                                    title="Tambah Gambar">
                                                    <i class="bi bi-image"></i>
                                                    <span class="d-none d-md-inline" style="font-size: 12px;">Gambar</span>
                                                </button>

                                                <!-- Emoji Button -->
                                                <button type="button"
                                                    class="btn btn-light btn-sm d-flex align-items-center gap-2 px-3"
                                                    id="emojiButton">
                                                    <i class="bi bi-emoji-smile"></i>
                                                    <span class="d-none d-md-inline" style="font-size: 12px;">Emoji</span>
                                                </button>
                                            </div>

                                            <style>
                                                .btn {
                                                    transition: all 0.2s;
                                                    font-size: 14px;
                                                }

                                                .btn:hover {
                                                    background-color: #e9ecef;
                                                    transform: translateY(-1px);
                                                }

                                                .btn:active {
                                                    transform: translateY(0);
                                                }

                                                @media (max-width: 768px) {
                                                    .btn {
                                                        padding: 8px !important;
                                                    }

                                                    .btn i {
                                                        font-size: 1.1rem;
                                                    }
                                                }
                                            </style>

                                            <!-- Emoji Picker Container -->
                                            <div id="emojiPicker" class="emoji-picker shadow" style="display: none;">
                                                <!-- Search and Category Bar -->
                                                <div class="emoji-toolbar border-bottom p-2">
                                                    <div class="search-box mb-2">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text border-0 bg-light">
                                                                <i class="bi bi-search"></i>
                                                            </span>
                                                            <input type="text"
                                                                class="form-control form-control-sm border-0 bg-light"
                                                                placeholder="Cari emoji..."
                                                                id="emojiSearch">
                                                        </div>
                                                    </div>
                                                    <div class="category-nav">
                                                        <select class="form-select form-select-sm border-0 bg-light" id="categoryFilter">
                                                            <option value="all">Semua</option>
                                                            <option value="Senyum">😊 Ekspresi</option>
                                                            <option value="Hewan">🐱 Hewan</option>
                                                            <option value="Simbol">❤️ Simbol</option>
                                                            <option value="Makanan">🍎 Makanan</option>
                                                            <option value="Aktivitas">⚽ Aktivitas</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Emoji Grid Container -->
                                                <div class="emoji-container">
                                                    <div class="emoji-grid p-2"></div>
                                                </div>
                                            </div>

                                            <style>
                                                .emoji-picker {
                                                    position: absolute;
                                                    bottom: 100%;
                                                    left: 0;
                                                    width: 280px;
                                                    background: white;
                                                    border-radius: 12px;
                                                    overflow: hidden;
                                                    z-index: 1000;
                                                    margin-bottom: 8px;
                                                }

                                                .emoji-toolbar {
                                                    background: #ffffff;
                                                }

                                                .search-box .input-group {
                                                    background: #f8f9fa;
                                                    border-radius: 8px;
                                                }

                                                .search-box .input-group-text {
                                                    color: #6c757d;
                                                }

                                                .search-box .form-control:focus {
                                                    box-shadow: none;
                                                }

                                                .category-nav .form-select {
                                                    font-size: 13px;
                                                    cursor: pointer;
                                                    border-radius: 8px;
                                                }

                                                .category-nav .form-select:focus {
                                                    box-shadow: none;
                                                }

                                                .emoji-container {
                                                    max-height: 250px;
                                                    overflow-y: auto;
                                                }

                                                .emoji-grid {
                                                    display: grid;
                                                    grid-template-columns: repeat(6, 1fr);
                                                    gap: 4px;
                                                }

                                                .emoji-item {
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    padding: 8px;
                                                    cursor: pointer;
                                                    font-size: 1.2em;
                                                    transition: all 0.2s ease;
                                                    border-radius: 8px;
                                                }

                                                .emoji-item:hover {
                                                    background: #f8f9fa;
                                                    transform: scale(1.1);
                                                }

                                                /* Custom Scrollbar */
                                                .emoji-container::-webkit-scrollbar {
                                                    width: 6px;
                                                }

                                                .emoji-container::-webkit-scrollbar-track {
                                                    background: transparent;
                                                }

                                                .emoji-container::-webkit-scrollbar-thumb {
                                                    background: #ddd;
                                                    border-radius: 3px;
                                                }

                                                .emoji-container::-webkit-scrollbar-thumb:hover {
                                                    background: #ccc;
                                                }
                                            </style>
                                        </div>
                                    </div>

                                    <!-- Post Button -->
                                    <button type="submit" class="btn flex-fill btnPrimary text-white px-4" onclick="showLoader(this)">
                                        <p class="p-0 m-0">Kirim</p>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <style>
                    /* Emoji Picker Styles */
                    .emoji-picker {
                        position: absolute;
                        bottom: 45px;
                        right: 0;
                        width: 250px;
                        background: white;
                        border-radius: 8px;
                        border: 1px solid rgba(0, 0, 0, 0.1);
                        z-index: 1000;
                    }

                    .emoji-container {
                        max-height: 200px;
                        overflow-y: auto;
                        display: grid;
                        grid-template-columns: repeat(8, 1fr);
                        gap: 5px;
                    }

                    .emoji-item {
                        cursor: pointer;
                        text-align: center;
                        padding: 5px;
                        font-size: 1.2em;
                        transition: transform 0.1s ease;
                    }

                    .emoji-item:hover {
                        transform: scale(1.2);
                        background: #f0f0f0;
                        border-radius: 5px;
                    }

                    /* Preview Styles */
                    #filePreview img {
                        width: 100px;
                        height: 100px;
                        object-fit: cover;
                        border-radius: 8px;
                    }

                    .file-preview-item {
                        position: relative;
                        transition: transform 0.2s ease;
                    }

                    .file-preview-item:hover {
                        transform: translateY(-2px);
                    }

                    /* Custom Scrollbar */
                    .emoji-container::-webkit-scrollbar {
                        width: 6px;
                    }

                    .emoji-container::-webkit-scrollbar-track {
                        background: #f1f1f1;
                    }

                    .emoji-container::-webkit-scrollbar-thumb {
                        background: #888;
                        border-radius: 3px;
                    }

                    .emoji-container::-webkit-scrollbar-thumb:hover {
                        background: #555;
                    }
                </style>
                <style>
                    .file-preview-item {
                        position: relative;
                        /* padding-right: 40px; */
                        /* Ruang untuk tombol hapus */
                        transition: all 0.2s ease;
                        background-color: white;
                        border-left: grey solid 1px;
                    }

                    /* .file-preview-item:hover {
                        background-color: #f8f9fa;
                    } */

                    .file-preview-item button {
                        transition: opacity 0.2s ease;
                        background-color: white;
                    }


                    #fileAlertContainer {
                        transition: all 0.3s ease;
                    }
                </style>

                <script>
                    // Emoji picker functionality
                    document.addEventListener('DOMContentLoaded', function() {
                        // Emoji data with categories
                        const emojiData = {
                            'Senyum': ['😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰'],
                            'Hewan': ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵'],
                            'Simbol': ['❤️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️'],
                            'Makanan': ['🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🍍', '🥝', '🍅', '🍆'],
                            'Aktivitas': ['⚽️', '🏀', '🏈', '⚾️', '🥎', '🎾', '🏐', '🏉', '🎱', '🏓', '🏸', '🏒', '🏑', '🥍', '⛳️']
                        };

                        const emojiButton = document.getElementById('emojiButton');
                        const emojiPicker = document.getElementById('emojiPicker');
                        const emojiContainer = document.querySelector('.emoji-container');
                        const postContent = document.getElementById('postContent');

                        // Create search and filter elements
                        emojiContainer.innerHTML = `
        <div class="emoji-grid p-2"></div>
    `;

                        const emojiGrid = emojiContainer.querySelector('.emoji-grid');
                        const searchInput = document.getElementById('emojiSearch');
                        const categoryFilter = document.getElementById('categoryFilter');

                        function displayEmojis(filterText = '', category = 'all') {
                            emojiGrid.innerHTML = '';

                            Object.entries(emojiData).forEach(([emojiCategory, emojis]) => {
                                if (category === 'all' || category === emojiCategory) {
                                    const filteredEmojis = emojis.filter(emoji =>
                                        emoji.includes(filterText)
                                    );

                                    if (filteredEmojis.length > 0) {
                                        filteredEmojis.forEach(emoji => {
                                            const span = document.createElement('span');
                                            span.className = 'emoji-item';
                                            span.textContent = emoji;
                                            span.onclick = () => {
                                                postContent.value += emoji;
                                                emojiPicker.style.display = 'none';
                                            };
                                            emojiGrid.appendChild(span);
                                        });
                                    }
                                }
                            });
                        }

                        // Event listeners for search and filter
                        searchInput.addEventListener('input', (e) => {
                            displayEmojis(e.target.value, categoryFilter.value);
                        });

                        categoryFilter.addEventListener('change', (e) => {
                            displayEmojis(searchInput.value, e.target.value);
                        });

                        // Toggle emoji picker
                        emojiButton.onclick = () => {
                            emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
                            if (emojiPicker.style.display === 'block') {
                                displayEmojis();
                            }
                        };

                        // Close emoji picker when clicking outside
                        document.addEventListener('click', (e) => {
                            if (!emojiButton.contains(e.target) && !emojiPicker.contains(e.target)) {
                                emojiPicker.style.display = 'none';
                            }
                        });

                        // Initial display
                        displayEmojis();
                    });

                    // Add this CSS
                    const style = document.createElement('style');
                    style.textContent = `
.emoji-container {
    width: 250px;
    max-height: 350px;
    overflow-y: auto;
}

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 5px;
}

.emoji-item {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.emoji-item:hover {
    transform: scale(1.2);
    background: #f0f0f0;
    border-radius: 5px;
}

.emoji-header {
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
    padding: 8px;
}

#emojiSearch, #categoryFilter {
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

#emojiSearch:focus, #categoryFilter:focus {
    border-color: rgb(218, 119, 86);
    box-shadow: 0 0 0 0.2rem rgba(218, 119, 86, 0.25);
}
`;

                    document.head.appendChild(style);

                    function handleFileSelect(input) {
                        const previewContainer = document.getElementById('previewContainer');
                        const filePreview = document.getElementById('filePreview');
                        const files = Array.from(input.files);

                        // Cek apakah elemen-elemen tersebut ada
                        if (!previewContainer || !filePreview) {
                            console.error('Preview elements not found');
                            return;
                        }

                        // Lanjutkan dengan kode yang sudah ada
                        if (files.length > 0) {
                            previewContainer.classList.remove('d-none');
                            filePreview.innerHTML = '';

                            files.forEach((file, index) => {
                                const fileDiv = document.createElement('div');
                                fileDiv.className = 'file-preview-item border rounded p-2 d-flex align-items-center gap-2 mb-2 position-relative';
                                fileDiv.dataset.index = index;

                                // Icon berdasarkan tipe file
                                let icon = 'bi-file-earmark';
                                if (file.type.startsWith('image/')) icon = 'bi-file-image text-success';
                                else if (file.type.startsWith('video/')) icon = 'bi-camera-video text-danger';
                                else if (file.type.includes('pdf')) icon = 'bi-file-pdf text-danger';
                                else if (file.type.includes('doc')) icon = 'bi-file-word text-primary';
                                else if (file.type.includes('ppt')) icon = 'bi-file-ppt text-warning';
                                else if (file.type.includes('xls')) icon = 'bi-file-excel text-success';

                                // Format file size
                                const fileSize = file.size < 1024 * 1024 ?
                                    Math.round(file.size / 1024) + ' KB' :
                                    Math.round(file.size / (1024 * 1024) * 10) / 10 + ' MB';

                                fileDiv.innerHTML = `
                <i class="bi ${icon} me-2" style="font-size: 1.2rem;"></i>
                <div class="flex-grow-1 min-width-0">
                    <div class="text-truncate" style="max-width: 150px;">${file.name}</div>
                    <small class="text-muted">${fileSize}</small>
                    ${file.type.startsWith('video/') ? '<small class="badge bg-info text-white ms-1">Video</small>' : ''}
                </div>
                <button type="button" class="btn btn-sm text-danger position-absolute end-0" onclick="removeFile(${index}, this.parentElement)">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;

                                filePreview.appendChild(fileDiv);
                                console.log('Files selected:', files);
                            });
                        } else {
                            previewContainer.classList.add('d-none');
                        }
                    }

                    // Fungsi untuk membuat container alert
                    function createAlertContainer() {
                        const modalBody = document.querySelector('#modalTambahPostingan .modal-body');
                        const alertContainer = document.createElement('div');
                        alertContainer.id = 'fileAlertContainer';

                        // Tambahkan alertContainer sebelum preview container
                        const previewContainer = document.getElementById('previewContainer');
                        if (modalBody && previewContainer) {
                            modalBody.insertBefore(alertContainer, previewContainer);
                        }

                        return alertContainer;
                    }


                    // Fungsi untuk menghapus file tertentu
                    function removeFile(index, element) {
                        // Hapus item dari DOM
                        element.remove();

                        // Hitung jumlah file yang tersisa
                        const remainingFiles = document.querySelectorAll('#filePreview > div').length;

                        // Jika tidak ada file tersisa, sembunyikan container
                        if (remainingFiles === 0) {
                            document.getElementById('previewContainer').classList.add('d-none');
                        }

                        // Reset input file karena kita tidak bisa langsung memodifikasi FileList
                        const fileInput = document.getElementById('fileInput');
                        fileInput.value = '';

                        // Tampilkan pesan info bahwa user perlu memilih file lagi
                        const alertContainer = document.getElementById('fileAlertContainer') || createAlertContainer();
                        alertContainer.innerHTML = `
        <div class="alert border bg-light mb-4" style="border-radius: 15px;">
            <div class="d-flex">
                <i class="bi bi-info-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                <div>
                    <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Pilihan file tidak sesuai?</p>
                    <p class="p-0 m-0 text-muted" style="font-size: 12px;">Silakan pilih ulang semua file yang ingin diupload.</p>
                </div>
            </div>
        </div>
    `;
                    }

                    // Validasi saat memilih file
                    document.getElementById('fileInput').addEventListener('change', function() {
                        if (this.files.length > 4) {
                            const submitButton = document.querySelector('form[action="tambah_postingan.php"] button[type="submit"]');
                            submitButton.disabled = true;

                            const alertContainer = document.getElementById('fileAlertContainer') || createAlertContainer();
                            alertContainer.innerHTML = `
            <div class="alert border bg-light mb-4" style="border-radius: 15px;">
                <div class="d-flex">
                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                    <div>
                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Upload melebihi batas maksimal</p>
                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Maksimal 4 file yang diperbolehkan untuk diupload. Pilih file Anda kembali.</p>
                    </div>
                </div>
            </div>
        `;
                            this.value = ''; // Reset input file
                        } else {
                            const submitButton = document.querySelector('form[action="tambah_postingan.php"] button[type="submit"]');
                            submitButton.disabled = false;
                        }
                    });
                </script>

                <!-- Script untuk preview file -->
                <script>
                    function handleFileSelect(input) {
                        const previewContainer = document.getElementById('previewContainer');
                        const filePreview = document.getElementById('filePreview');
                        const files = Array.from(input.files);
                        const alertContainer = document.getElementById('fileAlertContainer') || createAlertContainer();

                        // Reset alert container
                        alertContainer.innerHTML = '';

                        // Cek jumlah file
                        if (files.length > 4) {
                            // Tampilkan custom alert
                            alertContainer.innerHTML = `
            <div class="alert border bg-light mb-4" style="border-radius: 15px;">
                <div class="d-flex">
                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                    <div>
                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Upload melebihi batas maksimal</p>
                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Maksimal 4 file yang diperbolehkan untuk diupload. Pilih file Anda kembali.</p>
                    </div>
                </div>
            </div>
        `;

                            // Disable submit button
                            document.querySelector('form[action="tambah_postingan.php"] button[type="submit"]').disabled = true;

                            // Reset input file
                            input.value = '';

                            // Kosongkan div lampiran
                            filePreview.innerHTML = '';
                            previewContainer.classList.add('d-none');

                            return;
                        }

                        if (files.length > 0) {
                            previewContainer.classList.remove('d-none');
                            filePreview.innerHTML = '';

                            files.forEach((file, index) => {
                                const fileDiv = document.createElement('div');
                                fileDiv.className = 'file-preview-item border rounded p-2 d-flex align-items-center gap-2 mb-2 position-relative';
                                fileDiv.dataset.index = index;

                                // Icon berdasarkan tipe file
                                let icon = 'bi-file-earmark';
                                if (file.type.startsWith('image/')) icon = 'bi-file-image';
                                else if (file.type.includes('pdf')) icon = 'bi-file-pdf';
                                else if (file.type.includes('doc')) icon = 'bi-file-word';
                                else if (file.type.includes('ppt')) icon = 'bi-file-ppt';
                                else if (file.type.includes('xls')) icon = 'bi-file-excel';

                                // Konten file tanpa tombol remove (akan ditambahkan secara terpisah)
                                fileDiv.innerHTML = `
                <i class="bi ${icon} me-2" style="font-size: 1.2rem; color: var(--bs-primary);"></i>
                <small class="text-truncate" style="max-width: 150px;">${file.name}</small>
                <button type="button" class="btn btn-sm text-danger position-absolute end-0" onclick="removeFile(${index}, this.parentElement)">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;

                                filePreview.appendChild(fileDiv);
                            });

                            // Enable submit button
                            document.querySelector('form[action="tambah_postingan.php"] button[type="submit"]').disabled = false;
                        } else {
                            previewContainer.classList.add('d-none');
                        }
                    }

                    function clearFileInput() {
                        const fileInput = document.getElementById('fileInput');
                        if (fileInput) {
                            fileInput.value = '';
                            document.getElementById('previewContainer').classList.add('d-none');
                            document.getElementById('filePreview').innerHTML = '';
                        }
                    }
                </script>


                <!-- Konten Utama -->
                <!-- postingan guru -->
                <?php
                if (mysqli_num_rows($result_postingan) > 0) {
                    while ($post = mysqli_fetch_assoc($result_postingan)) {
                        // Format tanggal
                        $tanggal = date("d F Y", strtotime($post['created_at']));

                        // Cek apakah postingan adalah tugas
                        $is_tugas = isset($post['jenis_postingan']) && $post['jenis_postingan'] == 'tugas';
                ?>
                        <div class=" p-md-3 mb-4 rounded-3 bg-white mx-md-0 postingan p-4"
                            style="border: 1px solid rgb(226, 226, 226);">
                            <div class="d-flex gap-3">
                                <div>
                                    <a href="<?php echo $post['user_type'] == 'guru' ? 'profil_guru.php' : '#'; ?>">
                                        <img src="<?php
                                                    if ($post['user_type'] == 'guru') {
                                                        echo !empty($post['foto_profil']) ? 'uploads/profil/' . $post['foto_profil'] : 'assets/pp.png';
                                                    } else {
                                                        // For student posts
                                                        if (!empty($post['photo_url']) && $post['photo_type'] === 'avatar') {
                                                            echo $post['photo_url'];
                                                        } elseif (!empty($post['foto_profil']) && $post['photo_type'] === 'upload') {
                                                            echo 'uploads/profil/' . $post['foto_profil'];
                                                        } else {
                                                            echo 'assets/pp.png';
                                                        }
                                                    }
                                                    ?>" alt="Profile Image" class="profile-img rounded-4 border-0 bg-white" style="width: 40px;">
                                    </a>
                                </div>
                                <div class="">
                                    <h6 class="p-0 m-0">
                                        <?php echo htmlspecialchars($post['nama_pembuat']); ?>
                                        <?php if ($post['user_type'] == 'siswa'): ?>
                                            <span class="badge bg-secondary" style="font-size: 10px;">Siswa</span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="p-0 m-0 text-muted" style="font-size: 12px;">Diposting pada <?php echo $tanggal; ?></p>
                                </div>
                                <div class="flex-fill text-end dropdown <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>">
                                    <button class="btn p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi-three-dots-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end animate slideIn">
                                        <li>
                                            <?php if ($post['user_type'] == 'guru' || ($_SESSION['level'] == 'admin') || ($_SESSION['level'] == 'guru')): ?>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                                                    onclick="showDeleteConfirmation(<?php echo $post['id']; ?>)">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                    Hapus Postingan
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <script>
                                function showDeleteConfirmation(postId) {
                                    // Simpan posisi scroll sekarang
                                    const scrollPosition = window.pageYOffset;

                                    const confirmBtn = document.getElementById('confirmDeleteBtn');
                                    confirmBtn.setAttribute('href', `hapus_postingan.php?id=${postId}&kelas_id=<?php echo $kelas_id; ?>`);

                                    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

                                    // Tambahkan event listener untuk modal shown
                                    document.getElementById('deleteConfirmModal').addEventListener('shown.bs.modal', function() {
                                        // Kembalikan posisi scroll
                                        setTimeout(() => window.scrollTo(0, scrollPosition), 0);
                                    }, {
                                        once: true
                                    });

                                    modal.show();
                                }
                            </script>


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


                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius: 16px;">
                                        <div class="modal-body text-center p-4">
                                            <h5 class="mt-3 fw-bold">Hapus Postingan</h5>
                                            <p class="mb-4">Apakah Anda yakin ingin menghapus postingan ini? Tindakan ini tidak dapat dibatalkan.</p>
                                            <div class="d-flex gap-2 btn-group">
                                                <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                                <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Hapus</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="">
                                <?php if ($is_tugas): ?>
                                    <?php
                                    // Hitung jumlah siswa yang sudah mengumpulkan
                                    $tugas_id = $post['tugas_id'];
                                    $query_jumlah = "SELECT COUNT(*) as total FROM pengumpulan_tugas WHERE tugas_id = '$tugas_id'";
                                    $result_jumlah = mysqli_query($koneksi, $query_jumlah);
                                    $jumlah_mengumpulkan = mysqli_fetch_assoc($result_jumlah)['total'];

                                    // Hitung total siswa di kelas
                                    $query_total = "SELECT COUNT(*) as total FROM kelas_siswa WHERE kelas_id = '$kelas_id'";
                                    $result_total = mysqli_query($koneksi, $query_total);
                                    $total_siswa = mysqli_fetch_assoc($result_total)['total'];
                                    ?>

                                    <!-- UI for Tugas with minimalist iOS style -->
                                    <div class="tugas-container mt-3">
                                        <!-- Badge dan Judul -->
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <span class="badge" style="background: rgb(218, 119, 86); padding: 6px 12px; border-radius: 20px; font-weight: 500;">TUGAS</span>
                                            <h5 class="mb-0" style="font-weight: 600;"><?php echo htmlspecialchars($post['judul_tugas']); ?></h5>
                                        </div>

                                        <!-- Box Info Tugas -->
                                        <div class="tugas-info-box p-4 rounded-4 mb-3" style="background-color: #F9F9F9; border: 1px solid #F0F0F0;">
                                            <!-- Batas Waktu -->
                                            <div class="tugas-deadline d-flex align-items-center mb-3">
                                                <div class="deadline-icon me-3 p-2 rounded-circle" style="background: rgba(218, 119, 86, 0.1);">
                                                    <i class="bi bi-clock" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted" style="font-size: 13px;">Batas Pengumpulan</div>
                                                    <div style="font-weight: 500; font-size: 15px;">
                                                        <?php echo date("d M Y, H:i", strtotime($post['batas_waktu'])); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Progress Pengumpulan -->
                                            <div class="tugas-progress d-flex align-items-center">
                                                <div class="progress-icon me-3 p-2 rounded-circle" style="background: rgba(218, 119, 86, 0.1);">
                                                    <i class="bi bi-people" style="color: rgb(218, 119, 86); font-size: 1.2rem;"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted" style="font-size: 13px;">Pengumpulan</div>
                                                    <div style="font-weight: 500; font-size: 15px;">
                                                        <?php echo $jumlah_mengumpulkan; ?> dari <?php echo $total_siswa; ?> siswa
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tombol Detail -->
                                            <a href="detail_tugas.php?id=<?php echo $tugas_id; ?>"
                                                class="btn w-100 d-flex align-items-center justify-content-center gap-2 mt-4"
                                                style="background: rgb(218, 119, 86); color: white; border-radius: 12px; padding: 12px; font-weight: 500;">
                                                Lihat Detail
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <style>
                                        .tugas-info-box {
                                            transition: all 0.3s ease;
                                        }

                                        .tugas-info-box:hover {
                                            transform: translateY(-2px);
                                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                                        }

                                        .btn {
                                            transition: all 0.2s ease;
                                        }

                                        .btn:hover {
                                            transform: translateY(-1px);
                                        }

                                        .btn:active {
                                            transform: translateY(0);
                                        }

                                        @media (max-width: 768px) {
                                            .tugas-info-box {
                                                padding: 16px !important;
                                            }
                                        }
                                    </style>

                                <?php else: ?>
                                    <!-- jika bukan tugas, maka tampilkanlah ui postingan biasa -->
                                    <div class="mt-3">
                                        <p class="textPostingan"><?php
                                                                    $content = htmlspecialchars($post['konten']);
                                                                    $content = nl2br($content);
                                                                    $content = makeLinksClickable($content);
                                                                    echo $content;
                                                                    ?></p>
                                    </div>

                                <?php endif; ?>
                                <!-- style untk text postingan -->
                                <style>
                                    @media screen and (max-width: 768px) {
                                        .textPostingan {
                                            font-size: 14px;
                                        }
                                    }
                                </style>

                                <?php
                                // Query untuk mengambil lampiran
                                $postingan_id = $post['id'];
                                $query_lampiran = "SELECT * FROM lampiran_postingan WHERE postingan_id = '$postingan_id'";
                                $result_lampiran = mysqli_query($koneksi, $query_lampiran);

                                if (mysqli_num_rows($result_lampiran) > 0) {
                                    echo '<div class="container mt-3 p-2">';

                                    // Array untuk memisahkan gambar dan dokumen
                                    $images = [];
                                    $documents = [];
                                    $videos = [];

                                    while ($lampiran = mysqli_fetch_assoc($result_lampiran)) {
                                        if (strpos($lampiran['tipe_file'], 'image') !== false) {
                                            $images[] = $lampiran;
                                        } else if (strpos($lampiran['tipe_file'], 'video') !== false) {
                                            $videos[] = $lampiran; // Menggunakan $videos[] bukan $video[]
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
                                        echo '<div class="image-container-' . $imageCount . ' mt-2 border rounded">';

                                        switch ($imageCount) {
                                            case 1:
                                                // Single image - full width
                                                echo '<div class="single-image">';
                                                echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '</div>';
                                                break;

                                            case 2:
                                                // Two images side by side
                                                echo '<div class="dual-images">';
                                                foreach ($images as $image) {
                                                    echo '<img src="' . $image['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                }
                                                echo '</div>';
                                                break;

                                            case 3:
                                                // Two images top, one bottom
                                                echo '<div class="triple-images">';
                                                echo '<div class="top-images">';
                                                echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '<img src="' . $images[1]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '</div>';
                                                echo '<div class="bottom-image">';
                                                echo '<img src="' . $images[2]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '</div>';
                                                echo '</div>';
                                                break;

                                            case 4:
                                                // Two rows of two images
                                                echo '<div class="quad-images">';
                                                echo '<div class="image-row">';
                                                echo '<img src="' . $images[0]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '<img src="' . $images[1]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '</div>';
                                                echo '<div class="image-row">';
                                                echo '<img src="' . $images[2]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
                                                echo '<img src="' . $images[3]['path_file'] . '" alt="Lampiran" onclick="showImage(this.src)" loading="lazy">';
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

                                            echo '<div class="doc-item mb-2 p-2 bg-white rounded border">';
                                            echo '<a href="' . $doc['path_file'] . '" class="text-decoration-none text-dark d-flex align-items-center gap-2" target="_blank">';
                                            echo '<i class="bi ' . $icon . ' fs-4"></i>';
                                            echo '<div>';
                                            echo '<div class="doc-name">' . htmlspecialchars($doc['nama_file']) . '</div>';
                                            echo '<small class="text-muted">' . strtoupper($extension) . ' file</small>';
                                            echo '</div>';
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

                                <!-- modal untuk daftar user yang sudah like -->
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
                                    function showReactionUsers(postId) {
                                        // Tampilkan modal
                                        const modal = new bootstrap.Modal(document.getElementById('reactionUsersModal'));
                                        modal.show();

                                        // Ambil daftar pengguna yang memberi reaksi
                                        fetch(`get_reaction_users.php?post_id=${postId}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                const usersList = document.getElementById('reactionUsersList');
                                                usersList.innerHTML = '';

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
                                                let tabsHtml = '<div class="mb-3 border-bottom pb-2"><div class="d-flex gap-2 reaction-tabs">';
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
                    <div class="d-flex align-items-center">
                        <img src="${user.foto_profil || 'assets/pp.png'}" alt="Profile" 
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
                        <div class="d-flex align-items-center">
                            <img src="${user.foto_profil || 'assets/pp.png'}" alt="Profile" 
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
                                                    '<div class="text-center p-4 text-danger">Terjadi kesalahan saat memuat data</div>';
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


                                <div class="d-flex gap-2 justify-content-between mt-3 ps-2 pe-2" style="font-size: 14px;">
                                    <button class="btn btn-light flex-fill py-1 py-md-2 border d-flex align-items-center justify-content-center gap-2 <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>"
                                        id="like-btn-<?php echo $post['id']; ?>"
                                        onclick="toggleLike(<?php echo $post['id']; ?>)">
                                        <?php if ($current_emoji): ?>
                                            <i class="bi bi-hand-thumbs-up-fill text-primary"></i>
                                        <?php else: ?>
                                            <i class="bi bi-hand-thumbs-up"></i>
                                        <?php endif; ?>
                                        <span class="d-none d-md-inline">Suka</span>
                                    </button>

                                    <button class="btn btn-light flex-fill py-1 py-md-2 border d-flex align-items-center justify-content-center gap-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#commentModal-<?php echo $post['id']; ?>">
                                        <i class="bi bi-chat"></i>
                                        <span class="d-none d-md-inline">Komentar</span>
                                    </button>

                                    <button class="btn btn-light flex-fill py-1 py-md-2 border d-flex align-items-center justify-content-center gap-2"
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

                                    function updateReactionDisplay(postId, reactions, currentEmoji) {
                                        const button = document.getElementById(`like-btn-${postId}`);
                                        const countElement = document.getElementById(`reactions-count-${postId}`);

                                        // Set reaction text based on current emoji
                                        let reactionText = 'Suka';
                                        if (currentEmoji) {
                                            switch (currentEmoji) {
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
                                            }
                                            button.innerHTML = `<span>${currentEmoji} ${reactionText}</span>`;
                                        } else {
                                            button.innerHTML = `<i class="bi bi-hand-thumbs-up"></i>`;
                                        }

                                        // Display total reactions count
                                        let displayText = '';
                                        for (const [emoji, count] of Object.entries(reactions)) {
                                            if (count > 0) {
                                                displayText += `${emoji} ${count} `;
                                            }
                                        }
                                        countElement.innerHTML = displayText || `<i class='bi bi-hand-thumbs-up me-1'></i>0`;
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

                                        // Add sound effect when clicking like
                                        const likeSound = new Audio('assets/like_rev.mp3');
                                        likeSound.play();

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
                                                        icon.classList.add('text-primary');
                                                        // Add like animation
                                                        icon.style.transform = 'scale(1.2)';
                                                        setTimeout(() => icon.style.transform = 'scale(1)', 200);
                                                    } else {
                                                        icon.classList.replace('bi-hand-thumbs-up-fill', 'bi-hand-thumbs-up');
                                                        icon.classList.remove('text-primary');
                                                        // Add unlike animation
                                                        icon.style.transform = 'translateX(2px)';
                                                        setTimeout(() => icon.style.transform = 'translateX(-2px)', 50);
                                                        setTimeout(() => icon.style.transform = 'translateX(0)', 100);
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
                                                                s.photo_type, 
                                                                s.photo_url,
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
                                                <div class="komentar-container px-3" style="max-height: 60vh; overflow-y: auto;">
                                                    <?php
                                                    if (mysqli_num_rows($result_komentar) > 0) {
                                                        while ($komentar = mysqli_fetch_assoc($result_komentar)) {
                                                    ?>
                                                            <div class="comment-thread mb-3">
                                                                <!-- Main comment -->
                                                                <div class="d-flex gap-3">
                                                                    <div class="flex-shrink-0">
                                                                        <img src="<?php echo getProfilePhoto($komentar['user_type'], $komentar); ?>"
                                                                            alt=""
                                                                            width="32px"
                                                                            height="32px"
                                                                            class="rounded-circle border"
                                                                            style="object-fit: cover;">
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex justify-content-between align-items-start">
                                                                            <div class="comment-bubble p-2 rounded-3" style="background-color: #f0f2f5;">
                                                                                <div class="fw-semibold" style="font-size: 13px;">
                                                                                    <?php echo htmlspecialchars($komentar['nama_user']); ?>
                                                                                </div>
                                                                                <div style="font-size: 13px;">
                                                                                    <?php echo nl2br(htmlspecialchars($komentar['konten'])); ?>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Add three dots menu -->
                                                                            <?php if ($komentar['user_id'] == $_SESSION['userid']): ?>
                                                                                <div class="dropdown">
                                                                                    <button class="btn btn-sm p-0 px-1 text-muted" type="button" data-bs-toggle="dropdown">
                                                                                        <i class="bi bi-three-dots-vertical"></i>
                                                                                    </button>
                                                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm animate fadeIn">
                                                                                        <li>
                                                                                            <a class="dropdown-item text-danger" href="#"
                                                                                                onclick="showDeleteCommentConfirmation(<?php echo $komentar['id']; ?>, <?php echo $post['id']; ?>)">
                                                                                                <i class="bi bi-trash me-2"></i>Hapus Komentar
                                                                                            </a>
                                                                                        </li>
                                                                                    </ul>

                                                                                </div>

                                                                                <style>
                                                                                    .animate {
                                                                                        animation-duration: 0.2s;
                                                                                        animation-fill-mode: both;
                                                                                    }

                                                                                    @keyframes fadeIn {
                                                                                        from {
                                                                                            opacity: 0;
                                                                                            transform: translateY(-10px);
                                                                                        }

                                                                                        to {
                                                                                            opacity: 1;
                                                                                            transform: translateY(0);
                                                                                        }
                                                                                    }

                                                                                    .fadeIn {
                                                                                        animation-name: fadeIn;
                                                                                    }

                                                                                    .dropdown-menu {
                                                                                        margin-top: 0.5rem;
                                                                                        border: 1px solid rgba(0, 0, 0, 0.08);
                                                                                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
                                                                                    }

                                                                                    .dropdown-item {
                                                                                        padding: 0.5rem 1rem;
                                                                                        font-size: 14px;
                                                                                        transition: all 0.2s;
                                                                                    }

                                                                                    .dropdown-item:hover {
                                                                                        background-color: #f8f9fa;
                                                                                    }

                                                                                    .dropdown-item i {
                                                                                        font-size: 14px;
                                                                                    }
                                                                                </style>
                                                                            <?php endif; ?>
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
                                                                                function showCommentReactionBar(event, commentId) {
                                                                                    event.preventDefault();
                                                                                    event.stopPropagation(); // Prevent click from bubbling up
                                                                                    const reactionBar = document.getElementById(`comment-reaction-bar-${commentId}`);

                                                                                    // Close all other reaction bars first
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

                                                                                        // Add click event listener to document
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

                                                                                // Prevent reaction bar clicks from closing itself
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

                                                                                                button.innerHTML = ` <p class="p-0 m-0" style="font-size: 12px; me-2 "> ${emoji} ${reactionText}</p>`;
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
                                                                                        <img src="<?php echo $reply['foto_profil'] ? 'uploads/profil/' . $reply['foto_profil'] : 'assets/pp.png'; ?>"
                                                                                            alt="" width="24px" height="24px" class="rounded-circle">
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
                                                        echo '<div class="text-center p-5 rounded-4">
                                                            <i class="bi bi-chat fs-1 text-muted mb-3"></i>
                                                            <h6 class="fw-bold mb-2">Belum Ada Komentar</h6>
                                                            <p class="text-muted mb-0" style="font-size: 0.7rem;">
                                                                Jadilah yang pertama memberikan komentar!
                                                            </p>
                                                        </div>';
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
                                                    let currentPostId; // Store current post ID globally

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
                                            </script>
                                            <div class="modal-footer p-2 border-top">
                                                <div class="d-flex gap-2 align-items-end w-100">
                                                    <div class="flex-shrink-0">
                                                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                                            alt="Profile Image"
                                                            class="profile-img rounded-4 border-0 bg-white"
                                                            style="width: 35px;;">
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

                                                    // Remove "No Comments" message if it exists
                                                    const noCommentsMessage = container.querySelector('.text-center.p-5');
                                                    if (noCommentsMessage) {
                                                        noCommentsMessage.remove();
                                                    }

                                                    // Get the correct profile photo URL  
                                                    let photoUrl = data.komentar.foto_profil || 'assets/pp.png';

                                                    const komentarHTML = `
                <div class="d-flex gap-3 mb-3 new-comment">
                    <div class="flex-shrink-0">
                        <img src="${photoUrl}" 
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
                        <small class="text-muted" style="font-size: 11px;">
                            Baru saja
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
                                                        const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]) + 1;
                                                        countElement.textContent = `Total ${currentCount} Komentar`;
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
                    echo '<div class="mt-4 text-center text-muted">Belum ada postingan</div>';
                }
                ?>
            </div>
            <!-- fungsi untuk menghapus postingan di bagian bawah file -->
            <script>
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

                // Check if imageContainer exists
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
                    console.log("Element with ID 'imageContainer' not found");
                }

                // // Add images to the grid
                // images.forEach(src => {
                //     const img = document.createElement("img");
                //     img.src = src;
                //     img.alt = "Image";
                //     img.setAttribute("data-bs-toggle", "modal");
                //     img.setAttribute("data-bs-target", "#imageModal");
                //     img.addEventListener("click", () => {
                //         document.getElementById("modalImage").src = src;
                //     });
                //     imageContainer.appendChild(img);
                // });
            </script>
            <!-- modal gambarnya -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <img src="" id="modalImage" width="100%" class="img-fluid rounded" alt="Modal Preview">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Button download dibawah gambar -->
            <script>
                function downloadImage(imageUrl, fileName) {
                    fetch(imageUrl)
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = fileName || 'download.jpg';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                        })
                        .catch(error => console.error('Error downloading image:', error));
                }

                // Add download button to modal
                document.getElementById('imageModal').querySelector('.modal-body').innerHTML += `
                                <div class="text-end mt-3">
                                    <button class="btn btn-sm border btn-light" onclick="downloadImage(document.getElementById('modalImage').src)">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                                `;
            </script>
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
                                            <textarea class="form-control" name="deskripsi" placeholder="Apa pendapat Anda?" style="height: 100px;"><?php echo htmlspecialchars($data_kelas['deskripsi']); ?></textarea>
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


                <div class="catatanGuru p-4 rounded-3 bg-white border">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text fs-4" style="color: rgb(218, 119, 86);"></i>
                            <h5 class="m-0"><strong>Catatan Guru</strong></h5>
                        </div>
                        <button class="btn border text-black d-flex align-items-center gap-2 px-3 <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#catatanModal">
                            <i class="bi bi-plus-lg"></i>
                        </button>
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
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm animate slideIn">
                                                <li>
                                                    <a class="dropdown-item text-danger d-flex align-items-center" href="#"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus catatan ini?') && 
                                                                       (window.location.href='hapus_catatan.php?id=<?php echo $catatan['id']; ?>&kelas_id=<?php echo $kelas_id; ?>')">
                                                        <i class="bi bi-trash me-2"></i>Hapus Catatan
                                                    </a>
                                                </li>
                                            </ul>
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
                                        <p class="mb-3 text-secondary" style="font-size: 0.95rem; line-height: 1.6;">
                                            <?php echo nl2br(htmlspecialchars($catatan['konten'])); ?>
                                        </p>
                                        <?php if ($catatan['file_lampiran']): ?>
                                            <a href="<?php echo htmlspecialchars($catatan['file_lampiran']); ?>"
                                                class="text-decoration-none d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white border hover-shadow"
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
                                                <span class="text-black">Lihat Lampiran</span>
                                            </a>
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
                                Mulai tambahkan catatan untuk kelas ini
                            </p>
                        </div>
                    <?php endif; ?>
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

                <!-- Daftar Siswa -->
                <div class="daftarSiswa p-4 rounded-3 bg-white mt-3 border">
                    <!-- Header dengan statistik dan pencarian -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stats-icon rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-people fs-5" style="color: rgb(218, 119, 86);"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Daftar Siswa</h6>
                                <p class="m-0 text-muted" style="font-size: 14px;"><?php echo $jumlah_siswa; ?> siswa</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <!-- Search bar -->
                        <div class="position-relative mb-3">
                            <input type="text" class="form-control" id="searchSiswa" placeholder="Cari siswa..."
                                style="border-radius: 20px; padding-left: 35px;">
                            <i class="bi bi-search position-absolute"
                                style="left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                        </div>

                    </div>

                    <!-- Search script -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const searchInput = document.getElementById('searchSiswa');

                            searchInput.addEventListener('input', function() {
                                const searchTerm = this.value.toLowerCase();
                                const studentCards = document.querySelectorAll('.student-list-container .card');

                                studentCards.forEach(card => {
                                    const studentName = card.querySelector('h6').textContent.toLowerCase();
                                    if (studentName.includes(searchTerm)) {
                                        card.style.display = '';
                                    } else {
                                        card.style.display = 'none';
                                    }
                                });

                                // Show message if no results
                                const visibleCards = Array.from(studentCards).filter(card => card.style.display !== 'none');
                                const noResultsMessage = document.getElementById('noResultsMessage');

                                if (visibleCards.length === 0 && searchTerm !== '') {
                                    if (!noResultsMessage) {
                                        const message = document.createElement('div');
                                        message.id = 'noResultsMessage';
                                        message.className = 'text-center py-3 text-muted';
                                        message.innerHTML = `
                                            <i class="bi bi-search me-2"></i>
                                            Tidak ditemukan siswa dengan nama "${searchTerm}"
                                        `;
                                        document.querySelector('.student-list-container').appendChild(message);
                                    }
                                } else if (noResultsMessage) {
                                    noResultsMessage.remove();
                                }
                            });
                        });
                    </script>

                    <?php if (mysqli_num_rows($result_siswa) > 0): ?>
                        <!-- Grid siswa -->
                        <!-- Grid siswa dengan expand/collapse -->
                        <div class="student-list-container border-top border-bottom container-fluid p-0">
                            <div class="row me-2">
                                <?php
                                $query_semua_siswa = "SELECT s.id, s.nama, s.foto_profil, s.photo_type, s.photo_url, s.tingkat, s.nis, s.tahun_masuk, s.no_hp, s.alamat, s.password 
                                                   FROM siswa s 
                                                   JOIN kelas_siswa ks ON s.id = ks.siswa_id 
                                                   WHERE ks.kelas_id = '$kelas_id'
                                                   ORDER BY s.nama ASC";
                                $result_semua_siswa = mysqli_query($koneksi, $query_semua_siswa);

                                while ($siswa = mysqli_fetch_assoc($result_semua_siswa)):
                                ?>
                                    <div class="col-12 mt-2 mb-2">
                                        <div class="card shadow-sm">
                                            <div class="card-body p-3">
                                                <!-- Info Siswa -->
                                                <div class="d-flex align-items-center justify-content-between"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#studentInfo<?php echo $siswa['id']; ?>"
                                                    role="button"
                                                    aria-expanded="false">

                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="<?php
                                                                    if (!empty($siswa['photo_url']) && $siswa['photo_type'] === 'avatar') {
                                                                        // Jika memiliki photo_url dan tipenya avatar (dicebear)
                                                                        echo $siswa['photo_url'];
                                                                    } elseif (!empty($siswa['foto_profil']) && $siswa['photo_type'] === 'upload') {
                                                                        // Jika memiliki foto_profil dan tipenya upload
                                                                        echo 'uploads/profil/' . $siswa['foto_profil'];
                                                                    } else {
                                                                        // Jika tidak ada foto sama sekali
                                                                        echo 'assets/pp.png';
                                                                    }
                                                                    ?>"
                                                            alt="" width="32px" height="32px"
                                                            class="rounded-circle border"
                                                            style="object-fit: cover;">

                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($siswa['nama']); ?></h6>
                                                            <small class="text-muted">Kelas <?php echo htmlspecialchars($siswa['tingkat']); ?></small>
                                                        </div>
                                                    </div>

                                                    <i class="bi bi-chevron-down text-muted"></i>
                                                </div>

                                                <!-- Detail & Actions -->
                                                <div class="collapse mt-3" id="studentInfo<?php echo $siswa['id']; ?>">
                                                    <div class="d-flex gap-2">
                                                        <button type="button"
                                                            class="btn btn-light border flex-fill d-flex align-items-center justify-content-center gap-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#siswaInfoModal-<?php echo $siswa['id']; ?>"
                                                            style="font-size: 12px;"
                                                            onclick="console.log('Trying to open modal: siswaInfoModal-<?php echo $siswa['id']; ?>')">
                                                            <i class="bi bi-info-circle"></i>
                                                            <span>Lihat</span>
                                                        </button>

                                                        <!-- Modal Lihat Info Siswa -->
                                                        <div class="modal fade" id="siswaInfoModal-<?php echo $siswa['id']; ?>" tabindex="-1">
                                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                                <div class="modal-content">
                                                                    <div class="modal-body p-3">
                                                                        <!-- Profile Section -->
                                                                        <div class="text-center mb-3">
                                                                            <img src="<?php
                                                                                        if (!empty($siswa['photo_url']) && $siswa['photo_type'] === 'avatar') {
                                                                                            echo $siswa['photo_url'];
                                                                                        } elseif (!empty($siswa['foto_profil']) && $siswa['photo_type'] === 'upload') {
                                                                                            echo 'uploads/profil/' . $siswa['foto_profil'];
                                                                                        } else {
                                                                                            echo 'assets/pp.png';
                                                                                        }
                                                                                        ?>"
                                                                                class="rounded-circle border"
                                                                                width="64px" height="64px"
                                                                                style="object-fit: cover;">
                                                                            <h6 class="mb-0 mt-2"><?php echo htmlspecialchars($siswa['nama']); ?></h6>
                                                                            <small class="text-muted">Kelas <?php echo htmlspecialchars($siswa['tingkat']); ?></small>
                                                                        </div>

                                                                        <!-- Info List -->
                                                                        <div class="list-group list-group-flush">
                                                                            <!-- First Row -->
                                                                            <div class="row g-2 mb-2">
                                                                                <div class="col-6">
                                                                                    <div class="info-card p-2 rounded">
                                                                                        <small class="text-muted d-block" style="font-size:12px;">NIS</small>
                                                                                        <span class="fw-medium"><?php echo htmlspecialchars($siswa['nis']); ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="info-card p-2 rounded">
                                                                                        <small class="text-muted d-block" style="font-size:12px;">Tahun Masuk</small>
                                                                                        <span class="fw-medium"><?php echo htmlspecialchars($siswa['tahun_masuk']); ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Second Row -->
                                                                            <div class="row g-2 mb-2">
                                                                                <div class="col-12">
                                                                                    <div class="info-card p-2 rounded">
                                                                                        <small class="text-muted d-block" style="font-size:12px;">No. Telepon</small>
                                                                                        <span class="fw-medium"><?php echo htmlspecialchars($siswa['no_hp']); ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Third Row -->
                                                                            <div class="row g-2">
                                                                                <div class="col-12">
                                                                                    <div class="info-card p-2 rounded">
                                                                                        <small class="text-muted d-block" style="font-size:12px;">Alamat</small>
                                                                                        <span class="fw-medium text-break"><?php echo htmlspecialchars($siswa['alamat']); ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <style>
                                                                            .info-card {
                                                                                background-color: #f8f9fa;
                                                                                transition: all 0.2s ease;
                                                                            }

                                                                            .info-card:hover {
                                                                                background-color: #f0f0f0;
                                                                            }

                                                                            .fw-medium {
                                                                                font-weight: 500;
                                                                            }

                                                                            .text-break {
                                                                                word-break: break-word;
                                                                            }

                                                                            @media (max-width: 576px) {
                                                                                .info-card {
                                                                                    padding: 0.75rem !important;
                                                                                }
                                                                            }
                                                                        </style>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button type="button"
                                                            class="btn btn-danger flex-fill d-flex align-items-center justify-content-center gap-2 <?php echo ($_SESSION['level'] == 'admin') ? 'd-none' : ''; ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#removeSiswaModal"
                                                            onclick="setRemovalData(<?php echo $siswa['id']; ?>, '<?php echo htmlspecialchars($siswa['nama']); ?>')"
                                                            style="font-size: 12px;">
                                                            <i class="bi bi-person-x"></i>
                                                            <span>Keluarkan</span>
                                                        </button>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>



                        <style>
                            .info-card {
                                transition: all 0.2s;
                                border: 1px solid rgba(0, 0, 0, 0.05);
                            }

                            .info-card:hover {
                                background-color: #f8f9fa !important;
                                transform: translateY(-1px);
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
                            }

                            .info-card i {
                                color: #6c757d;
                                font-size: 1rem;
                            }

                            @media (max-width: 576px) {
                                .modal-content {
                                    border-radius: 0;
                                    min-height: 100vh;
                                }
                            }
                        </style>


                        <!-- Modal Konfirmasi Keluarkan Siswa -->
                        <div class="modal fade" id="removeSiswaModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius: 16px;">
                                    <div class="modal-body text-center p-4">
                                        <h5 class="mt-3 fw-bold">Keluarkan Siswa</h5>
                                        <p class="mb-4">Apakah Anda yakin ingin mengeluarkan siswa ini dari kelas?</p>
                                        <div class="d-flex gap-2 btn-group">
                                            <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                            <a href="#" id="confirmRemoveBtn" class="btn btn-danger px-4" style="border-radius: 12px; ">Keluarkan</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function setRemovalData(siswaId, siswaName) {
                                // Set the student name in the modal message
                                const modalMessage = document.querySelector('#removeSiswaModal .modal-body p');
                                modalMessage.textContent = `Apakah Anda yakin ingin mengeluarkan ${siswaName} dari kelas? Tindakan ini tidak dapat dibatalkan.`;

                                // Set the confirmation button's action
                                const confirmBtn = document.getElementById('confirmRemoveBtn');
                                confirmBtn.href = `hapus_siswa.php?siswa_id=${siswaId}&kelas_id=<?php echo $kelas_id; ?>`;
                            }
                        </script>


                        <style>
                            .student-card {
                                background: #fff;
                                border: 1px solid #dee2e6;
                                transition: all 0.2s ease;
                            }

                            .student-card:hover {
                                background: #f8f9fa;
                            }

                            .student-info-toggle {
                                cursor: pointer;
                            }

                            .toggle-icon {
                                transition: transform 0.2s ease;
                            }

                            [aria-expanded="true"] .toggle-icon {
                                transform: rotate(180deg);
                            }

                            .btn {
                                padding: 0.5rem 1rem;
                                font-size: 0.9rem;
                            }

                            .btn i {
                                font-size: 1rem;
                            }

                            .student-name {
                                font-size: 0.95rem;
                            }

                            @media (max-width: 768px) {
                                .student-card {
                                    margin-bottom: 0.5rem;
                                }

                                .btn {
                                    padding: 0.4rem 0.8rem;
                                    font-size: 0.85rem;
                                }
                            }
                        </style>
                        <style>
                            .student-list-container {
                                max-height: 400px;
                                overflow-y: auto;
                                overflow-x: hidden;
                            }

                            .student-list-container::-webkit-scrollbar {
                                width: 6px;
                            }

                            .student-list-container::-webkit-scrollbar-track {
                                background: #f1f1f1;
                            }

                            .student-list-container::-webkit-scrollbar-thumb {
                                background: #ddd;
                                border-radius: 3px;
                            }

                            .student-list-container::-webkit-scrollbar-thumb:hover {
                                background: #ccc;
                            }

                            .student-grid {
                                display: flex;
                                flex-direction: column;
                                gap: 2px;
                            }

                            .student-card {
                                background-color: white;
                                transition: all 0.2s ease;
                                cursor: pointer;
                            }

                            .student-card:hover {
                                background-color: #f8f9fa;
                            }

                            .student-info-toggle {
                                width: 100%;
                                cursor: pointer;
                            }

                            .toggle-icon {
                                transition: transform 0.3s ease;
                            }

                            .student-info-toggle[aria-expanded="true"] .toggle-icon {
                                transform: rotate(180deg);
                            }

                            .student-detail {
                                background-color: #f8f9fa;
                                margin-top: -2px;
                                animation: slideDown 0.3s ease-out;
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
                        </style>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Add click event to student cards
                                document.querySelectorAll('.student-info-toggle').forEach(toggle => {
                                    toggle.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const card = this.closest('.student-card');
                                        const detail = card.nextElementSibling;
                                        const isExpanded = this.getAttribute('aria-expanded') === 'true';

                                        // Toggle aria-expanded
                                        this.setAttribute('aria-expanded', !isExpanded);
                                    });
                                });
                            });

                            // Modal info siswa
                            document.addEventListener('DOMContentLoaded', function() {
                                let siswaModal = `
                <div class="modal fade" id="siswaModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title">Info Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="siswaModalContent">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            `;
                                document.body.insertAdjacentHTML('beforeend', siswaModal);
                            });

                            // Fungsi untuk menampilkan info siswa
                            function showSiswaInfo(siswaId) {
                                fetch('get_siswa_info.php?id=' + siswaId)
                                    .then(response => response.json())
                                    .then(data => {
                                        const content = `
                        <div class="text-center mb-4">
                            <img src="${data.foto_profil || 'assets/pp.png'}" 
                                 class="rounded-circle mb-3" 
                                 width="100" 
                                 height="100"
                                 style="object-fit: cover;">
                            <h5 class="mb-0">${data.nama}</h5>
                            <p class="text-muted">Kelas ${data.tingkat}</p>
                        </div>
                        <div class="info-list">
                            <div class="info-item d-flex align-items-center p-2 rounded bg-light mb-2">
                                <i class="bi bi-person me-3"></i>
                                <div>
                                    <small class="text-muted">Username</small>
                                    <p class="mb-0">${data.username}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex align-items-center p-2 rounded bg-light mb-2">
                                <i class="bi bi-envelope me-3"></i>
                                <div>
                                    <small class="text-muted">Email</small>
                                    <p class="mb-0">${data.email}</p>
                                </div>
                            </div>
                        </div>
                    `;
                                        document.getElementById('siswaModalContent').innerHTML = content;
                                        new bootstrap.Modal(document.getElementById('siswaModal')).show();
                                    });
                            }

                            // Fungsi untuk konfirmasi dan hapus siswa
                            function hapusSiswa(siswaId, namaSiswa) {
                                // Show the confirmation modal
                                const confirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

                                // Update modal content with student name
                                document.getElementById('confirmDeleteTitle').textContent = 'Keluarkan Siswa';
                                document.getElementById('confirmDeleteMessage').textContent =
                                    `Apakah Anda yakin ingin mengeluarkan ${namaSiswa} dari kelas ini? Tindakan ini tidak dapat dibatalkan.`;

                                // Set the confirm button's action
                                const confirmBtn = document.getElementById('confirmDeleteBtn');
                                confirmBtn.href = `hapus_siswa.php?siswa_id=${siswaId}&kelas_id=<?php echo $kelas_id; ?>`;

                                // Show the modal
                                confirmModal.show();
                            }

                            // Add the confirmation modal to the page
                            document.addEventListener('DOMContentLoaded', function() {
                                if (!document.getElementById('deleteConfirmModal')) {
                                    const modalHTML = `
                                    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 16px;">
                                                <div class="modal-body text-center p-4">
                                                    <h5 class="mt-3 fw-bold" id="confirmDeleteTitle">Keluarkan Siswa</h5>
                                                    <p class="mb-4" id="confirmDeleteMessage">Apakah Anda yakin ingin mengeluarkan siswa ini dari kelas?</p>
                                                    <div class="d-flex gap-2 btn-group">
                                                        <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                                        <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Keluarkan</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                                }
                            });
                        </script>
                        <?php if ($jumlah_siswa > 1): ?>
                            <div class="d-flex gap-2 mt-4">
                                <button class="btn btn-light border flex-grow-1 text-center"
                                    data-bs-toggle="modal"
                                    data-bs-target="#lihatSemuaSiswaModal">
                                    Lihat Semua Siswa
                                </button>
                                <div class="dropdown">
                                    <button class="btn border btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#keluarkanSiswaModal">
                                                <i class="bi bi-person-x me-2 text-danger"></i>Keluarkan Siswa
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#tambahSiswaFullModal">
                                                <i class="bi bi-person-plus me-2 text-success"></i>Tambah Siswa
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- State kosong -->
                        <div class="text-center py-4">
                            <div class="empty-state mb-3">
                                <i class="bi bi-people fs-1 text-muted"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Belum Ada Siswa</h6>
                            <p class="text-muted mb-3" style="font-size: 14px;">
                                Mulai tambahkan siswa ke dalam kelas ini
                            </p>
                            <button class="btn btnPrimary text-white d-inline-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#tambahSiswaModal">
                                <i class="bi bi-person-plus"></i>
                                Tambah Siswa
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

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
                        <i class="bi bi-gear"></i>
                    </button>

                    <!-- Mini FABs -->
                    <div class="mini-fabs">
                        <!-- Catatan Button -->
                        <button class="btn mini-fab rounded-circle shadow"
                            data-bs-toggle="modal"
                            data-bs-target="#semuaCatatanModal"
                            title="Catatan">
                            <i class="bi bi-journal-text"></i>
                            <span class="fab-label">Catatan</span>
                        </button>

                        <!-- Siswa Button -->
                        <button class="btn mini-fab rounded-circle shadow"
                            data-bs-toggle="modal"
                            data-bs-target="#kelolaSiswaModal"
                            title="Kelola Siswa">
                            <i class="bi bi-people"></i>
                            <span class="fab-label">Kelola Siswa</span>
                        </button>
                    </div>

                    <!-- Backdrop for FAB -->
                    <div class="fab-backdrop"></div>
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
                        opacity: 0;
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
                                            <div class="catatan-item p-3 rounded-3 mb-3 bg-light border">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-2 fw-bold"><?php echo htmlspecialchars($catatan['judul']); ?></h6>
                                                        <div class="d-flex align-items-center text-muted mb-2" style="font-size: 0.85rem;">
                                                            <i class="bi bi-calendar3 me-2"></i>
                                                            <?php echo date('d F Y', strtotime($catatan['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                            <li>
                                                                <a class="dropdown-item text-danger d-flex align-items-center gap-2"
                                                                    href="#"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus catatan ini?') && 
                                                           (window.location.href='hapus_catatan.php?id=<?php echo $catatan['id']; ?>&kelas_id=<?php echo $kelas_id; ?>')">
                                                                    <i class="bi bi-trash"></i>
                                                                    Hapus Catatan
                                                                </a>
                                                            </li>
                                                        </ul>
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
                            <div class="modal-footer border-0">
                                <button type="button"
                                    class="btn btnPrimary text-white w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#catatanModal">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Tambah Catatan Baru
                                </button>
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
                        <label class="form-check-label" for="pilihSemua" style="font-size: 12px;">
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

                <!-- Delete Comment Confirmation Modal -->
                <div class="modal fade" id="deleteCommentConfirmModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 16px;">
                            <div class="modal-body text-center p-4">
                                <h5 class="mt-3 fw-bold">Hapus Komentar</h5>
                                <p class="mb-4">Apakah Anda yakin ingin menghapus komentar ini? Tindakan ini tidak dapat dibatalkan.</p>
                                <div class="d-flex gap-2 btn-group">
                                    <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                    <button type="button" id="confirmDeleteCommentBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


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
                                <div class="modal-footer btn-group">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btnPrimary text-white">Simpan Catatan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- style untuk catatan guru -->
                <style>
                    @media screen and (max-width: 768px) {
                        .catatanGuru {
                            display: none;
                        }
                    }
                </style>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <!-- untuk pause video -->
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

    <!-- untuk link langsung klik -->
    <!-- Modal Konfirmasi Link -->
    <div class="modal fade" id="linkConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <h5 class="mt-3 fw-bold">Buka Link External</h5>
                    <p class="mb-4">Apakah Anda yakin ingin membuka link berikut dan meninggalkan SMAGAEdu?</p>
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

    <!-- Modal untuk Keluarkan Siswa dengan Checkbox -->
    <div class="modal fade" id="keluarkanSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Keluarkan Siswa dari Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Select All Checkbox -->
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllSiswa">
                            <label class="form-check-label fw-bold" for="selectAllSiswa">
                                Pilih Semua Siswa
                            </label>
                        </div>
                    </div>

                    <!-- Student List -->
                    <div class="student-list-checkbox">
                        <?php
                        // Query ulang untuk mendapatkan siswa
                        $query_siswa_keluarkan = "SELECT s.id, s.nama, s.username, s.tingkat, s.foto_profil, s.photo_type, s.photo_url 
                        FROM siswa s 
                        JOIN kelas_siswa ks ON s.id = ks.siswa_id 
                        WHERE ks.kelas_id = '$kelas_id' 
                        ORDER BY s.nama ASC";
                        $result_siswa_keluarkan = mysqli_query($koneksi, $query_siswa_keluarkan);

                        while ($siswa = mysqli_fetch_assoc($result_siswa_keluarkan)):
                        ?>
                            <div class="student-checkbox-card p-3 rounded-3 mb-2 d-flex align-items-center justify-content-between"
                                style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input siswa-checkbox" type="checkbox" value="<?php echo $siswa['id']; ?>" id="siswa_<?php echo $siswa['id']; ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?php
                                                    if (!empty($siswa['photo_url']) && $siswa['photo_type'] === 'avatar') {
                                                        echo $siswa['photo_url'];
                                                    } elseif (!empty($siswa['foto_profil']) && $siswa['photo_type'] === 'upload') {
                                                        echo 'uploads/profil/' . $siswa['foto_profil'];
                                                    } else {
                                                        echo 'assets/pp.png';
                                                    }
                                                    ?>"
                                            alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                                            <small class="text-muted">Kelas <?php echo htmlspecialchars($siswa['tingkat']); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <small class="text-secondary">@<?php echo htmlspecialchars($siswa['username']); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="modal-footer btn-group">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" id="hapusSelectedSiswa" disabled>Hapus Siswa Terpilih</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Siswa Full -->
    <div class="modal fade" id="tambahSiswaFullModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Siswa ke Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formTambahSiswaFull">
                        <!-- Pilih Tingkat -->
                        <div class="mb-4">
                            <label class="form-label">Pilih Tingkat Kelas</label>
                            <select class="form-select" id="tingkatKelasFull" required>
                                <option value="">Pilih tingkat kelas</option>
                                <option value="trial_smp">Trial Class SMP</option>
                                <option value="7">SMP Kelas 7</option>
                                <option value="8">SMP Kelas 8</option>
                                <option value="9">SMP Kelas 9</option>
                                <option value="E">SMA Fase E</option>
                                <option value="F">SMA Fase F</option>
                                <option value="12">SMA Kelas 12</option>
                            </select>
                        </div>

                        <!-- Loader -->
                        <div id="siswaLoader" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat daftar siswa...</p>
                        </div>

                        <!-- Daftar Siswa dengan Checkbox -->
                        <div class="mb-3">
                            <div id="daftarSiswaFull" class="p-3 border rounded-3" style="background-color: #f8f9fa; max-height: 400px; overflow-y: auto;">
                                <p class="text-muted text-center">Pilih tingkat kelas terlebih dahulu</p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer btn-group border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formTambahSiswaFull" class="btn color-web text-white">Tambahkan Siswa</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle Select All Checkbox untuk Keluarkan Siswa
        document.getElementById('selectAllSiswa').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButton();
        });

        // Handle Individual Checkbox Changes
        document.querySelectorAll('.siswa-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateDeleteButton();
                updateSelectAllState();
            });
        });

        // Update Delete Button State
        function updateDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            const deleteButton = document.getElementById('hapusSelectedSiswa');
            deleteButton.disabled = checkedBoxes.length === 0;
        }

        // Update Select All Checkbox State
        function updateSelectAllState() {
            const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            const selectAll = document.getElementById('selectAllSiswa');

            if (checkedBoxes.length === allCheckboxes.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (checkedBoxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }

        // Handle Delete Selected Students
        document.getElementById('hapusSelectedSiswa').addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            const siswaIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (siswaIds.length === 0) return;

            if (confirm(`Apakah Anda yakin ingin mengeluarkan ${siswaIds.length} siswa dari kelas?`)) {
                // Redirect ke hapus_siswa_bulk.php dengan siswa_ids sebagai parameter
                const params = new URLSearchParams({
                    'siswa_ids': JSON.stringify(siswaIds),
                    'kelas_id': '<?php echo $kelas_id; ?>'
                });
                window.location.href = 'hapus_siswa_bulk.php?' + params.toString();
            }
        });

        // Handle Tingkat Kelas Change dengan Loader
        // Ketika tingkat kelas berubah
        document.getElementById('tingkatKelas').addEventListener('change', function() {
            const tingkat = this.value;
            const daftarSiswaDiv = document.querySelector('.daftar-siswa');

            if (tingkat) {
                // Fetch data siswa berdasarkan tingkat
                fetch(`get_siswa.php?tingkat=${tingkat}&kelas_id=<?php echo $kelas_id; ?>`)
                    .then(response => response.text())
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
                            // Pilih hanya checkbox yang tidak disabled
                            const checkboxes = document.querySelectorAll('.siswa-checkbox:not([disabled])');
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

        // Modifikasi pada eventListener untuk tingkatKelasFull
        document.getElementById('tingkatKelasFull').addEventListener('change', function() {
            const tingkat = this.value;
            const daftarSiswaDiv = document.getElementById('daftarSiswaFull');
            const loader = document.getElementById('siswaLoader');

            if (tingkat) {
                // Show loader
                loader.style.display = 'block';
                daftarSiswaDiv.innerHTML = '';

                // Fetch siswa berdasarkan tingkat dan kelas_id
                fetch(`get_siswa.php?tingkat=${tingkat}&kelas_id=<?php echo $kelas_id; ?>`)
                    .then(response => response.text())
                    .then(html => {
                        // Hide loader
                        loader.style.display = 'none';

                        // Add checkbox "Pilih Semua"
                        const pilihSemuaHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="pilihSemuaFull">
                        <label class="form-check-label fw-bold" for="pilihSemuaFull">
                            Pilih Semua Siswa
                        </label>
                    </div>
                    <hr>
                `;

                        daftarSiswaDiv.innerHTML = pilihSemuaHTML + html;

                        // Event listener untuk "Pilih Semua"
                        document.getElementById('pilihSemuaFull').addEventListener('change', function() {
                            // Pilih hanya checkbox yang tidak disabled
                            const checkboxes = document.querySelectorAll('#daftarSiswaFull .siswa-checkbox:not([disabled])');
                            checkboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });
                    })
                    .catch(error => {
                        loader.style.display = 'none';
                        daftarSiswaDiv.innerHTML = '<p class="text-danger">Terjadi kesalahan saat memuat data siswa</p>';
                    });
            } else {
                daftarSiswaDiv.innerHTML = '<p class="text-muted text-center">Pilih tingkat kelas terlebih dahulu</p>';
            }
        });

        // Handle Form Submit untuk Tambah Siswa
        document.getElementById('formTambahSiswaFull').addEventListener('submit', function(e) {
            e.preventDefault();

            const selectedSiswa = Array.from(document.querySelectorAll('#daftarSiswaFull .siswa-checkbox:checked'))
                .map(checkbox => checkbox.value);

            if (selectedSiswa.length === 0) {
                alert('Pilih minimal satu siswa');
                return;
            }

            // Kirim data ke proses_tambah_siswa.php
            fetch('proses_tambah_siswa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `siswa_ids=${JSON.stringify(selectedSiswa)}&kelas_id=<?php echo $kelas_id; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Siswa berhasil ditambahkan ke kelas');
                        document.querySelector('#tambahSiswaFullModal .btn-close').click();
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

</body>

</html>