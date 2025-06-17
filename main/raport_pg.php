<?php
session_start();
require "koneksi.php";
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Tambahkan debug info di sini
// echo "Debug seluruh session:<br>";
// var_dump($_SESSION);
// echo "<br><br>";

// Ambil userid dari session
$userid = $_SESSION['userid'];

// Get current student ID from URL or set default
$siswa_id = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : null;
// Ambil parameter dari URL
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : 1;
$selected_tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : date('Y') . '/' . (date('Y') + 1);

// Ambil data statistik siswa
$statistik = null;
if ($siswa_id) {
    $query_statistik = "SELECT * FROM pg 
                       WHERE siswa_id = ? 
                       AND semester = ? 
                       AND tahun_ajaran = ? 
                       ORDER BY created_at DESC 
                       LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query_statistik);
    mysqli_stmt_bind_param($stmt, "iis", $siswa_id, $selected_semester, $selected_tahun_ajaran);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $statistik = mysqli_fetch_assoc($result);
}

// Inisialisasi nilai default
$akademik = 0;
$ibadah = 0;
$akademik = 0;
$ibadah = 0;
$pengembangan = 0;
$sosial = 0;
$kesehatan = 0;
$karakter = 0;

// Hitung rata-rata jika ada data
if ($statistik) {
    $nilai_akademik = $statistik['nilai_akademik'] ?? 0;
    $keaktifan = $statistik['keaktifan'] ?? 0;
    $pemahaman = $statistik['pemahaman'] ?? 0;
    $akademik = ($nilai_akademik + $keaktifan + $pemahaman) / 3;

    $kehadiran_ibadah = $statistik['kehadiran_ibadah'] ?? 0;
    $kualitas_ibadah = $statistik['kualitas_ibadah'] ?? 0;
    $pemahaman_agama = $statistik['pemahaman_agama'] ?? 0;
    $ibadah = ($kehadiran_ibadah + $kualitas_ibadah + $pemahaman_agama) / 3;

    $minat_bakat = $statistik['minat_bakat'] ?? 0;
    $prestasi = $statistik['prestasi'] ?? 0;
    $keaktifan_ekskul = $statistik['keaktifan_ekskul'] ?? 0;
    $pengembangan = ($minat_bakat + $prestasi + $keaktifan_ekskul) / 3;

    $partisipasi_sosial = $statistik['partisipasi_sosial'] ?? 0;
    $empati = $statistik['empati'] ?? 0;
    $kerja_sama = $statistik['kerja_sama'] ?? 0;
    $sosial = ($partisipasi_sosial + $empati + $kerja_sama) / 3;

    $kebersihan_diri = $statistik['kebersihan_diri'] ?? 0;
    $aktivitas_fisik = $statistik['aktivitas_fisik'] ?? 0;
    $pola_makan = $statistik['pola_makan'] ?? 0;
    $kesehatan = ($kebersihan_diri + $aktivitas_fisik + $pola_makan) / 3;

    $kejujuran = $statistik['kejujuran'] ?? 0;
    $tanggung_jawab = $statistik['tanggung_jawab'] ?? 0;
    $kedisiplinan = $statistik['kedisiplinan'] ?? 0;
    $karakter = ($kejujuran + $tanggung_jawab + $kedisiplinan) / 3;
}


if (!isset($_GET['semester']) && !isset($_GET['tahun_ajaran'])) {
    // Default hanya jika tidak ada parameter GET
    $current_month = date('n');
    $selected_semester = ($current_month >= 7 && $current_month <= 12) ? 1 : 2;
    $current_year = date('Y');
    $selected_tahun_ajaran = ($selected_semester == 1) ? $current_year . '/' . ($current_year + 1) : ($current_year - 1) . '/' . $current_year;
}


// Get all students from database
$query_all_students = "SELECT id, nama, tingkat FROM siswa ORDER BY tingkat, nama";
$result_students = mysqli_query($koneksi, $query_all_students);


// Di bagian atas file, setelah mendapatkan data siswa
$current_student = null;
if ($siswa_id) {
    $query_current = "SELECT * FROM siswa WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query_current);
    mysqli_stmt_bind_param($stmt, "i", $siswa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_student = mysqli_fetch_assoc($result);
}

// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// ambil semua data dari tabel siswa 
$query_siswa = "SELECT * FROM siswa WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
$result_siswa = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result_siswa);

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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

    <title>E-Raport Progressive Guidance - SMAGAEdu</title>
</head>
<style>
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

    body::-webkit-scrollbar {
        display: none;
    }

    body {
        -ms-overflow-style: none;
        /* for Internet Explorer, Edge */
        scrollbar-width: none;
        /* for Firefox */
    }
</style>

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
    <!-- ini isi kontennya -->
    <div class="col p-2 col-utama mt-1 mt-md-0">
        <h3 class="fw-bold m-4"> Input Progressive Guidance

        </h3>
        <style>
            .col-utama {
                margin-left: 0;
                animation: fadeInUp 0.5s;
                opacity: 1;
                /* Ensures the content stays visible after animation */

            }

            @media (min-width: 768px) {
                .col-utama {
                    margin-left: 13rem;
                }
            }
        </style>

        <div class="container-fluid">
            <!-- Row 1: All Information -->
            <div class="row mb-4">
                <!-- Left Column: Student Profile -->
                <div class="col-md-3">
                    <div class="card mb-3 border" style="border-radius: 15px;">
                        <div class="card-header bg-white" style="border-top-left-radius: 15px; border-top-right-radius:15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg rounded me-2" style="width: 40px;">
                                    <i class="bi bi-person" style="color: rgb(206, 100, 65);"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Identitas Siswa</h6>
                                    <p class="text-muted mb-0" style="font-size: 10px;">Berikut identitas siswa yang telah Anda pilih</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center pt-2">
                            <div class="position-relative mb-3 d-inline-block">
                                <img src="<?php
                                            if ($current_student) { // Gunakan current_student karena sudah berisi data siswa yang dipilih
                                                if (!empty($current_student['photo_type'])) {
                                                    if ($current_student['photo_type'] === 'avatar') {
                                                        echo $current_student['photo_url'];
                                                    } else if ($current_student['photo_type'] === 'upload') {
                                                        echo 'uploads/profil/' . $current_student['foto_profil'];
                                                    }
                                                } else {
                                                    echo 'assets/pp.png';
                                                }
                                            } else {
                                                echo 'assets/pp.png';
                                            }
                                            ?>"
                                    class="rounded-circle border shadow-sm"
                                    width="150px" height="150px"
                                    style="object-fit: cover;">

                                <?php if ($current_student): ?>
                                    <button type="button"
                                        class="btn btn-sm position-absolute bottom-0 end-0 p-1 rounded-circle shadow-sm"
                                        style="background: #fff; border: 2px solid #f8f9fa;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#fotoModal">
                                        <i class="bi bi-pencil text-muted" style="font-size: 12px;"></i>
                                    </button>

                                    <!-- Modal Ganti Foto -->
                                    <div class="modal fade" id="fotoModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="update_foto_siswa.php" method="POST" enctype="multipart/form-data" id="formFoto">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="siswa_id" value="<?php echo $current_student['id']; ?>">
                                                        <input type="hidden" name="croppedImage" id="croppedImage">

                                                        <div class="text-start">
                                                            <h5 class="fw-bold">Gambar Profil</h5>
                                                            <p style="font-size: 12px;">Perbarui foto siswa Anda dengan menekan tambah foto baru di bawah, perubahan foto
                                                                akan berdampak pada akun LMS siswa
                                                            </p>
                                                        </div>

                                                        <!-- Ubah div img-container menjadi seperti ini -->
                                                        <div class="img-container mb-3" style="max-height: 400px;">
                                                            <!-- Gambar untuk preview -->
                                                            <img src="<?php
                                                                        if ($current_student) {
                                                                            if (!empty($current_student['photo_type'])) {
                                                                                if ($current_student['photo_type'] === 'avatar') {
                                                                                    echo $current_student['photo_url'];
                                                                                } else if ($current_student['photo_type'] === 'upload') {
                                                                                    echo 'uploads/profil/' . $current_student['foto_profil'];
                                                                                }
                                                                            } else {
                                                                                echo 'assets/pp.png';
                                                                            }
                                                                        } else {
                                                                            echo 'assets/pp.png';
                                                                        }
                                                                        ?>"
                                                                id="previewFoto"
                                                                class="rounded-circle border shadow-sm"
                                                                width="150px" height="150px"
                                                                style="object-fit: cover;">

                                                            <!-- Gambar untuk cropper (hidden by default) -->
                                                            <img id="image"
                                                                class="d-none"
                                                                style="max-width: 100%; max-height: 400px; border-radius:15px !important;">
                                                        </div>

                                                        <div class="d-flex justify-content-center gap-2">
                                                            <input type="file" name="foto_profil" id="foto_profil" class="d-none" accept="image/*" onchange="loadImage(this)">
                                                            <label for="foto_profil" class="btn btn-ios mb-0 d-flex px-3">
                                                                <span style="font-size: 12px;">Pilih</span>
                                                            </label>
                                                            <button type="button" class="btn btn-ios d-flex px-3" onclick="cropAndSubmit()">
                                                                <span style="font-size: 12px;">Simpan</span>
                                                            </button>
                                                        </div>

                                                        <style>
                                                            .btn-ios {
                                                                background-color: #da7756;
                                                                color: #fff;
                                                                border-radius: 8px;
                                                                font-size: 15px;
                                                                padding: 8px 16px;
                                                                border: none;
                                                                transition: background-color 0.2s;
                                                                font-weight: 500;
                                                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                                            }

                                                            .btn-ios:hover {
                                                                background-color: #f8f9fa;
                                                                color: black;
                                                            }

                                                            .btn-ios:active {
                                                                background-color: #da7756;
                                                                transform: scale(0.98);
                                                            }

                                                            .btn-ios i {
                                                                font-size: 16px;
                                                            }

                                                            .btn-ios span {
                                                                display: inline-block;
                                                                margin-top: 1px;
                                                            }
                                                        </style>
                                                        <div class="form-text mt-2">Format: JPG, PNG (Max. 2MB)</div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        .img-container {
                                            margin: 20px auto;
                                            max-width: 100%;
                                        }

                                        .cropper-view-box,
                                        .cropper-face {
                                            border-radius: 50%;
                                        }
                                    </style>

                                    <script>
                                        let cropper;

                                        function loadImage(input) {
                                            if (input.files && input.files[0]) {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                    const previewFoto = document.getElementById('previewFoto');
                                                    const cropperImage = document.getElementById('image');

                                                    if (previewFoto && cropperImage) {
                                                        // Sembunyikan preview, tampilkan cropper
                                                        previewFoto.classList.add('d-none');
                                                        cropperImage.classList.remove('d-none');

                                                        // Set source image untuk cropper
                                                        cropperImage.src = e.target.result;

                                                        // Destroy cropper lama jika ada
                                                        if (cropper) {
                                                            cropper.destroy();
                                                        }

                                                        // Inisialisasi cropper baru
                                                        cropper = new Cropper(cropperImage, {
                                                            aspectRatio: 1,
                                                            viewMode: 1,
                                                            dragMode: 'move',
                                                            autoCropArea: 1,
                                                            cropBoxResizable: false,
                                                            cropBoxMovable: false,
                                                            guides: false,
                                                            center: true,
                                                            highlight: false
                                                        });
                                                    }
                                                }
                                                reader.readAsDataURL(input.files[0]);
                                            }
                                        }

                                        function cropAndSubmit() {
                                            if (!cropper) {
                                                document.getElementById('formFoto').submit();
                                                return;
                                            }

                                            cropper.getCroppedCanvas({
                                                width: 300,
                                                height: 300
                                            }).toBlob((blob) => {
                                                const formData = new FormData(document.getElementById('formFoto'));

                                                // Tambahkan photo_type
                                                formData.append('photo_type', 'upload');
                                                // Append blob sebagai 'photo'
                                                formData.append('photo', blob, 'photo.jpg');

                                                fetch('update_foto_siswa.php', {
                                                        method: 'POST',
                                                        body: formData
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.success) {
                                                            location.reload();
                                                        } else {
                                                            alert('Gagal mengupload foto: ' + data.message);
                                                        }
                                                    })
                                                    .catch(error => {
                                                        console.error('Error:', error);
                                                        alert('Terjadi kesalahan: ' + error.message);
                                                    });
                                            });
                                        }
                                    </script>

                                    <script>
                                        function previewImage(input) {
                                            if (input.files && input.files[0]) {
                                                var reader = new FileReader();
                                                reader.onload = function(e) {
                                                    document.getElementById('previewFoto').src = e.target.result;
                                                }
                                                reader.readAsDataURL(input.files[0]);
                                            }
                                        }
                                    </script>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-column align-items-center p-2">
                                <h6 class="mb-1 fw-bold" style="font-size: 14px;">
                                    <?php echo $current_student ? htmlspecialchars($current_student['nama']) : '-'; ?>
                                </h6>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-light text-dark border">
                                        Kelas <?php echo $current_student ? htmlspecialchars($current_student['tingkat']) : '-'; ?>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-light text-dark border">
                                        Semester <?php echo $selected_semester; ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        TA. <?php echo $selected_tahun_ajaran; ?>
                                    </span>
                                </div>

                                <!-- Perbarui tombol di card profil siswa -->
                                <div class="d-flex gap-1 w-100" <?php echo !$current_student ? 'style="display:none;"' : ''; ?>>
                                    <button class="btn btn-sm btn-light border flex-fill d-flex align-items-center justify-content-center gap-1 btn-print" style="font-size: 11px;">
                                        <i class="bi bi-printer"></i>
                                        Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tambahkan script ini di bagian bawah halaman, sebelum tag </body> -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Tombol PDF di desktop dan mobile
                            const pdfButtons = document.querySelectorAll('.btn-pdf, [data-action="pdf"]');
                            pdfButtons.forEach(button => {
                                button.addEventListener('click', handleGeneratePDF);
                            });

                            // Tombol Print di desktop dan mobile
                            const printButtons = document.querySelectorAll('.btn-print, [data-action="print"]');
                            printButtons.forEach(button => {
                                button.addEventListener('click', printRaport);
                            });
                        });

                        // Fungsi untuk print halaman
                        function printRaport() {
                            window.print();
                        }

                        // Fungsi untuk mengkonversi ke PDF
                        function generatePDF() {
                            const {
                                jsPDF
                            } = window.jspdf;

                            // Dapatkan data untuk nama file dengan cara yang lebih aman
                            const siswaName = document.querySelector('.fw-bold') ?
                                document.querySelector('.fw-bold').textContent.trim() : 'raport';

                            // Cari elemen badge yang berisi teks "Semester"
                            let semester = "Semester";
                            const semesterBadges = document.querySelectorAll('.badge');
                            for (let badge of semesterBadges) {
                                if (badge.textContent.includes('Semester')) {
                                    semester = badge.textContent.trim();
                                    break;
                                }
                            }

                            // Cari elemen badge yang berisi teks "TA."
                            let tahunAjaran = "TA";
                            for (let badge of semesterBadges) {
                                if (badge.textContent.includes('TA.')) {
                                    tahunAjaran = badge.textContent.trim();
                                    break;
                                }
                            }

                            // Format nama file: Nama-Semester-TahunAjaran.pdf
                            const fileName = `${siswaName} - ${semester} - ${tahunAjaran}.pdf`.replace(/\s+/g, ' ');

                            // Buat instance baru dari jsPDF
                            const doc = new jsPDF('p', 'mm', 'a4');

                            // Element yang akan dikonversi ke PDF
                            const element = document.querySelector('.col-utama');

                            // Buat clone dari element untuk memodifikasi tampilan khusus PDF
                            const printElement = element.cloneNode(true);
                            printElement.style.display = 'block';
                            printElement.style.position = 'absolute';
                            printElement.style.left = '-9999px';
                            // printElement.style.backgroundColor = 'white';
                            printElement.style.width = '1200px'; // Set fixed width untuk layout yang konsisten

                            // Pastikan semua elemen terlihat untuk PDF
                            const collapseElements = printElement.querySelectorAll('.collapse');
                            collapseElements.forEach(el => {
                                el.classList.add('show');
                            });

                            // Tambahkan elemen ke body
                            document.body.appendChild(printElement);

                            // Perbaikan untuk warna/brightness
                            html2canvas(printElement, {
                                scale: 2, // Kualitas lebih tinggi
                                useCORS: true, // Untuk menangani gambar cross-origin
                                logging: true, // Enable logging untuk debugging
                                // backgroundColor: '#ffffff', // Background putih
                                allowTaint: true, // Izinkan modifikasi canvas dengan konten yang mungkin "tainted"
                                letterRendering: true, // Untuk render teks lebih tajam
                                // Perbaikan untuk gambar
                                onclone: function(clonedDoc) {
                                    const images = clonedDoc.querySelectorAll('img');
                                    images.forEach(img => {
                                        // Tambahkan crossOrigin attribute untuk semua gambar
                                        img.crossOrigin = 'anonymous';
                                        // Perbaiki opacity
                                        img.style.opacity = '1';

                                        // Force load gambar
                                        if (img.src.indexOf('data:') !== 0) { // Jika bukan data URL
                                            const originalSrc = img.src;
                                            img.src = originalSrc;
                                        }
                                    });

                                    // Tingkatkan kontras semua elemen
                                    const allElements = clonedDoc.querySelectorAll('*');
                                    allElements.forEach(el => {
                                        if (el.style) {
                                            el.style.color = '#000000';
                                            if (el.style.backgroundColor) {
                                                el.style.backgroundColor = '#ffffff';
                                            }
                                        }
                                    });
                                }
                            }).then(canvas => {
                                // Dapatkan data gambar
                                const imgData = canvas.toDataURL('image/jpeg', 1.0); // Gunakan JPEG dengan kualitas 100%

                                const imgWidth = 210; // A4 width in mm
                                const pageHeight = 295; // A4 height in mm
                                const imgHeight = canvas.height * imgWidth / canvas.width;
                                let heightLeft = imgHeight;
                                let position = 0;

                                // Tambahkan halaman pertama
                                doc.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                                heightLeft -= pageHeight;

                                // Tambahkan halaman tambahan jika konten terlalu panjang
                                while (heightLeft >= 0) {
                                    position = heightLeft - imgHeight;
                                    doc.addPage();
                                    doc.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                                    heightLeft -= pageHeight;
                                }

                                // Hapus elemen clone
                                document.body.removeChild(printElement);

                                // Unduh PDF dengan nama yang sesuai
                                doc.save(fileName);
                            }).catch(error => {
                                console.error('Error generating PDF:', error);
                                // Hapus elemen clone jika terjadi error
                                if (document.body.contains(printElement)) {
                                    document.body.removeChild(printElement);
                                }
                                alert('Terjadi kesalahan saat membuat PDF. Silakan coba lagi.');
                            });
                        }

                        // Fungsi untuk menangani gambar profil
                        async function preloadProfileImage() {
                            return new Promise((resolve, reject) => {
                                const profileImg = document.querySelector('.rounded-circle[width="150px"]');
                                if (!profileImg) {
                                    resolve(); // Tidak ada gambar profil
                                    return;
                                }

                                // Jika gambar sudah dimuat
                                if (profileImg.complete) {
                                    resolve();
                                    return;
                                }

                                // Tunggu gambar dimuat
                                profileImg.onload = resolve;
                                profileImg.onerror = reject;

                                // Set ulang src untuk memastikan onload terpanggil
                                const currentSrc = profileImg.src;
                                profileImg.src = currentSrc;
                            });
                        }

                        // Modifikasi pemanggilan generatePDF untuk menunggu gambar profil
                        async function handleGeneratePDF() {
                            try {
                                await preloadProfileImage();
                                generatePDF();
                            } catch (error) {
                                console.error('Failed to preload profile image:', error);
                                // Tetap generate PDF meskipun ada error dengan gambar
                                generatePDF();
                            }
                        }
                    </script>

                    <style>
                        /* CSS untuk cetak */
                        @media print {

                            /* Menyembunyikan elemen yang tidak perlu diprint */
                            .menu-samping,
                            .navbar,
                            .floating-action-button,
                            .btn-print,
                            .btn-pdf,
                            .accordion-button,
                            button[data-bs-toggle],
                            .offcanvas,
                            .modal,
                            .card-header {
                                display: none !important;
                            }

                            /* Pastikan semua konten terlihat */
                            .col-utama {
                                margin-left: 0 !important;
                                width: 100% !important;
                            }

                            /* Tampilkan semua data */
                            .accordion-collapse {
                                display: block !important;
                            }

                            /* Format halaman */
                            @page {
                                size: A4;
                                margin: 1cm;
                            }

                            /* Reset margin dan padding untuk tampilan yang bersih */
                            body {
                                margin: 0;
                                padding: 0;
                            }

                            /* Warna latar belakang putih untuk semua elemen */
                            * {
                                background-color: white !important;
                                color: black !important;
                            }
                        }
                    </style>

                    <!-- Mobile view - Combined search -->
                    <div class="card mb-3 d-block shadow-none d-md-none border" style="border-radius: 15px;">
                        <div class="card-header bg-white" style="border-top-left-radius: 15px; border-top-right-radius:15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg rounded me-2" style="width: 40px;">
                                    <i class="bi bi-search" style="color: rgb(206, 100, 65);"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Cari Siswa</h6>
                                    <p class="text-muted mb-0" style="font-size: 10px;">Cari siswa dan data siswa</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form method="GET" id="mobileSearchForm">
                                <!-- HTML Select Box -->
                                <div class="mb-2">
                                    <label class="form-label small mb-1">Pilih Siswa</label>
                                    <select name="siswa_id" class="form-select form-select-sm select2-siswa" onchange="this.form.submit()">
                                        <option value="" class="bi bi-search">Pilih Siswa</option>
                                        <?php
                                        mysqli_data_seek($result_students, 0);
                                        $students = array();
                                        while ($student = mysqli_fetch_assoc($result_students)) {
                                            $students[] = $student;
                                        }
                                        usort($students, function ($a, $b) {
                                            return strcmp($a['nama'], $b['nama']);
                                        });

                                        foreach ($students as $student):
                                        ?>
                                            <option value="<?= $student['id'] ?>"
                                                data-tingkat="<?= $student['tingkat'] ?>"
                                                <?= ($siswa_id == $student['id']) ? 'selected' : '' ?>>
                                                <?= $student['nama'] ?> - <?= $student['tingkat'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small mb-1">Semester</label>
                                    <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="1" <?php echo ($selected_semester == 1) ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo ($selected_semester == 2) ? 'selected' : ''; ?>>Semester 2</option>
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small mb-1">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <?php
                                        $current_year = date('Y');
                                        for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
                                            $tahun_option = $i . '/' . ($i + 1);
                                            $selected = ($tahun_option == $selected_tahun_ajaran) ? 'selected' : '';
                                            echo "<option value='$tahun_option' $selected>$tahun_option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Desktop view - Separate cards (hidden on mobile) -->
                    <div class="d-none d-md-block">
                        <!-- cari siswa -->
                        <div class="card border mb-3 shadow-none" style="border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <div class="card-header bg-white" style="border-top-left-radius: 15px; border-top-right-radius:15px;">
                                <div class="d-flex align-items-center">
                                    <div class="ios-icon-bg rounded me-2" style="width: 40px;">
                                        <i class="bi bi-search" style="color: rgb(206, 100, 65);"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">Cari Siswa</h6>
                                        <p class="text-muted mb-0" style="font-size: 10px;">Cari siswa dan data siswa</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Form HTML -->
                                <form action="" method="GET" class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <select name="siswa_id" class="form-select mobile-select2"
                                            onchange="window.location.href = 'raport_pg.php?siswa_id=' + this.value">
                                            <option value="" class="bi bi-search">Pilih Siswa</option>
                                            <?php
                                            mysqli_data_seek($result_students, 0);
                                            $students = array();
                                            while ($student = mysqli_fetch_assoc($result_students)) {
                                                $students[] = $student;
                                            }
                                            usort($students, function ($a, $b) {
                                                return strcmp($a['nama'], $b['nama']);
                                            });

                                            foreach ($students as $student):
                                            ?>
                                                <option value="<?= $student['id'] ?>"
                                                    <?= ($siswa_id == $student['id']) ? 'selected' : '' ?>>
                                                    <?= $student['nama'] ?> - <?= $student['tingkat'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </form>
                                <!-- JavaScript sebelum closing body -->
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
                                <script>
                                    $(document).ready(function() {
                                        $('.mobile-select2').select2({
                                            width: '100%',
                                            placeholder: 'Pilih Siswa',
                                            allowClear: true,
                                            language: {
                                                noResults: function() {
                                                    return "Siswa tidak ditemukan";
                                                },
                                                searching: function() {
                                                    return "Mencari...";
                                                }
                                            },
                                            // Tambahkan event handler untuk memastikan search box tetap fokus
                                            dropdownOpened: function() {
                                                setTimeout(function() {
                                                    $('.select2-search__field').focus();
                                                }, 100);
                                            }
                                        });
                                    });
                                </script>

                                <!-- CSS Kustom -->
                                <style>
                                    /* Basic Select2 Styles */

                                    .select2-selection {
                                        border-radius: 10px !important;
                                        padding: 12px !important;
                                        font-size: 14px !important;
                                        border: 1px solid rgba(0, 0, 0, 0.1) !important;
                                        height: auto !important;
                                        background-color: #fff !important;
                                    }

                                    /* Dropdown Styles */
                                    .select2-dropdown {
                                        border-radius: 10px !important;
                                        border: 1px solid rgba(0, 0, 0, 0.1) !important;
                                        margin-top: 4px !important;
                                    }

                                    .select2-results__option {
                                        padding: 12px !important;
                                        font-size: 14px !important;
                                    }

                                    /* Container search box */
                                    .select2-search {
                                        position: relative;
                                    }

                                    /* Ikon search */
                                    .select2-search:before {
                                        content: "\F52A";
                                        /* Kode ikon search dari Bootstrap Icons */
                                        font-family: "bootstrap-icons";
                                        position: absolute;
                                        left: 25px;
                                        top: 50%;
                                        transform: translateY(-50%);
                                        color: #6c757d;
                                        font-size: 14px;
                                        z-index: 2;
                                    }

                                    /* Menggeser input field untuk memberikan ruang untuk ikon */
                                    .select2-search__field {
                                        padding-left: 45px !important;
                                        border-radius: 8px;
                                    }


                                    /* Mobile Optimization */
                                    @media (max-width: 768px) {

                                        /* Container untuk dropdown */
                                        .select2-container--open .select2-dropdown {
                                            position: fixed !important;
                                            left: 0 !important;
                                            right: 0 !important;
                                            top: auto !important;
                                            bottom: 0 !important;
                                            border-radius: 20px 20px 0 0 !important;
                                            border-bottom: none !important;
                                            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1) !important;
                                            max-height: 80vh !important;
                                            margin: 0 !important;
                                            z-index: 1056 !important;
                                            /* Higher than Bootstrap's modal */
                                            background: white !important;
                                        }

                                        /* Sticky Search Container */
                                        .select2-container--open .select2-dropdown .select2-search {
                                            position: sticky !important;
                                            top: 0 !important;
                                            padding: 15px !important;
                                            background: white !important;
                                            z-index: 1057 !important;
                                            border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
                                            border-radius: 20px 20px 0 0 !important;
                                        }

                                        /* Search Input */
                                        .select2-search__field {
                                            border-radius: 8px !important;
                                            padding: 12px !important;
                                            border: 1px solid rgba(0, 0, 0, 0.1) !important;
                                            font-size: 16px !important;
                                            /* Prevent iOS zoom */
                                            width: 100% !important;
                                        }

                                        /* Results Container */
                                        .select2 .select2-results {
                                            max-height: calc(80vh - 80px) !important;
                                            overflow-y: auto !important;
                                            -webkit-overflow-scrolling: touch !important;
                                            padding-bottom: env(safe-area-inset-bottom) !important;
                                        }

                                        /* Selected Option */
                                        .select2-container--default .select2-results__option--selected {
                                            background-color: rgba(0, 0, 0, 0.05) !important;
                                        }

                                        /* Highlighted Option */
                                        .select2-container--default .select2-results__option--highlighted {
                                            background-color: rgba(0, 0, 0, 0.1) !important;
                                            color: black !important;
                                        }
                                    }

                                    /* Additional iOS-specific optimizations */
                                    @supports (-webkit-touch-callout: none) {
                                        .select2-search__field {
                                            font-size: 16px !important;
                                        }

                                        .select2-container--open .select2-dropdown {
                                            padding-bottom: env(safe-area-inset-bottom) !important;
                                        }
                                    }
                                </style>
                                <form method="GET" id="semesterForm">
                                    <input type="hidden" name="siswa_id" value="<?php echo $siswa_id; ?>">

                                    <div class="mb-3">
                                        <select name="semester" class="form-select"
                                            style="border-radius: 10px; padding: 12px; font-size: 14px; border: 1px solid rgba(0,0,0,0.1); appearance: none; -webkit-appearance: none;"
                                            onchange="this.form.submit()">
                                            <option value="1" <?php echo ($selected_semester == 1) ? 'selected' : ''; ?>>Semester 1</option>
                                            <option value="2" <?php echo ($selected_semester == 2) ? 'selected' : ''; ?>>Semester 2</option>
                                        </select>
                                    </div>

                                    <div class="mb-0">
                                        <select name="tahun_ajaran" class="form-select"
                                            style="border-radius: 10px; padding: 12px; font-size: 14px; border: 1px solid rgba(0,0,0,0.1); appearance: none; -webkit-appearance: none;"
                                            onchange="this.form.submit()">
                                            <?php
                                            $current_year = date('Y');
                                            for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
                                                $tahun_option = $i . '/' . ($i + 1);
                                                $selected = ($tahun_option == $selected_tahun_ajaran) ? 'selected' : '';
                                                echo "<option value='$tahun_option' $selected>$tahun_option</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </form>
                            </div>

                            <style>
                                .form-select:focus {
                                    border-color: #c56647;
                                    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
                                    outline: none;
                                }

                                .form-select option {
                                    padding: 12px;
                                    font-size: 14px;
                                    background-color: white;
                                    color: #1c1c1e;
                                }

                                .form-select option:checked {
                                    background-color: #c56647;
                                    color: white;
                                }

                                @media (hover: hover) {
                                    .form-select option:hover {
                                        background-color: rgba(0, 122, 255, 0.1);
                                    }
                                }
                            </style>
                        </div>


                    </div>


                </div>

                <!-- Right Column: Quick Stats -->
                <!-- Dropdown and Charts Container -->
                <div class="col-md-6">
                    <div class="card h-100 border" style="border-radius:15px;">
                        <div class="card-header bg-white" style="border-top-left-radius: 15px; border-top-right-radius:15px;">
                            <div style="height: 200px">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>

                        <script>
                            // Update to bar chart
                            const ctx = document.getElementById('barChart').getContext('2d');
                            const barChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: ['Akademik', 'Ibadah', 'Pengembangan', 'Sosial', 'Kesehatan', 'Karakter'],
                                    datasets: [{
                                        data: [
                                            <?php echo $akademik; ?>,
                                            <?php echo $ibadah; ?>,
                                            <?php echo $pengembangan; ?>,
                                            <?php echo $sosial; ?>,
                                            <?php echo $kesehatan; ?>,
                                            <?php echo $karakter; ?>
                                        ],
                                        backgroundColor: 'rgba(218, 119, 86, 0.2)',
                                        borderColor: 'rgb(218, 119, 86)',
                                        borderWidth: 1,
                                        borderRadius: 8
                                    }]
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            ticks: {
                                                stepSize: 20
                                            }
                                        }
                                    }
                                }
                            });
                        </script>

                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Akademik -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi bi-book" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Pendampingan Belajar</h6>
                                                <span class="ios-value"><?php echo number_format($akademik, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Nilai Akademik</span>
                                                <span><?php echo number_format($statistik['nilai_akademik'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Keaktifan</span>
                                                <span><?php echo number_format($statistik['keaktifan'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Pemahaman</span>
                                                <span><?php echo number_format($statistik['pemahaman'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ibadah -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi bi-circle" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Pendampingan Ibadah</h6>
                                                <span class="ios-value"><?php echo number_format($ibadah, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Kehadiran Ibadah</span>
                                                <span><?php echo number_format($statistik['kehadiran_ibadah'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Kualitas Ibadah</span>
                                                <span><?php echo number_format($statistik['kualitas_ibadah'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Pemahaman Agama</span>
                                                <span><?php echo number_format($statistik['pemahaman_agama'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- pengembangan diri -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi-person-plus" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Pengembangan Diri</h6>
                                                <span class="ios-value"><?php echo number_format($pengembangan, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Minat Bakat</span>
                                                <span><?php echo number_format($statistik['minat_bakat'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Prestasi</span>
                                                <span><?php echo number_format($statistik['prestasi'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Keaktifan Ekstrakulikuler</span>
                                                <span><?php echo number_format($statistik['keaktifan_eskul'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- sosial -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi-people" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Sosial Kemasyarakatan</h6>
                                                <span class="ios-value"><?php echo number_format($sosial, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Partisipasi Kegiatan Sosial</span>
                                                <span><?php echo number_format($statistik['partisipasi_sosial'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Empati</span>
                                                <span><?php echo number_format($statistik['empati'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Kerja Sama</span>
                                                <span><?php echo number_format($statistik['kerja_sama'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kesehatan -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi-heart-pulse" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Kesehatan</h6>
                                                <span class="ios-value"><?php echo number_format($kesehatan, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Kebersihan dan Kerapihan Diri</span>
                                                <span><?php echo number_format($statistik['kebersihan_diri'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Aktifitas Fisik</span>
                                                <span><?php echo number_format($statistik['aktivitas_fisik'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Pola Makan</span>
                                                <span><?php echo number_format($statistik['pola_makan'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Karakter -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 ios-card">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="ios-icon-bg me-3">
                                                <i class="bi-star" style="color: rgb(206, 100, 65);"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">Karakter</h6>
                                                <span class="ios-value"><?php echo number_format($karakter, 1); ?>%</span>
                                            </div>
                                        </div>
                                        <div class="ios-details">
                                            <div class="ios-detail-item">
                                                <span>Kejujuran</span>
                                                <span><?php echo number_format($statistik['kejujuran'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Tanggung Jawab</span>
                                                <span><?php echo number_format($statistik['tanggung_jawab'] ?? 0, 1); ?>%</span>
                                            </div>
                                            <div class="ios-detail-item">
                                                <span>Kedisiplinan</span>
                                                <span><?php echo number_format($statistik['kedisiplinan'] ?? 0, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Add iOS style CSS -->
                                <style>
                                    .ios-card {
                                        background: #fff;
                                        border: 1px solid rgba(0, 0, 0, 0.1);
                                        box-shadow: 0 2px 10px rgba(206, 100, 65, 0.05);
                                        transition: all 0.3s ease;
                                    }

                                    .ios-card:hover {
                                        transform: translateY(-2px);
                                        box-shadow: 0 4px 15px rgba(206, 100, 65, 0.1);
                                    }

                                    .ios-icon-bg {
                                        width: 40px;
                                        height: 40px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        background: rgba(206, 100, 65, 0.1);
                                        border-radius: 12px;
                                    }

                                    .ios-value {
                                        font-size: 24px;
                                        font-weight: 600;
                                        color: rgb(206, 100, 65);
                                    }

                                    .ios-details {
                                        margin-top: 15px;
                                        padding-top: 15px;
                                        border-top: 1px solid rgba(206, 100, 65, 0.1);
                                    }

                                    .ios-detail-item {
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: center;
                                        padding: 8px 0;
                                        font-size: 14px;
                                    }

                                    .ios-detail-item span:first-child {
                                        color: #666;
                                    }

                                    .ios-detail-item span:last-child {
                                        font-weight: 500;
                                        color: rgb(206, 100, 65);
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAB for mobile
                    <div class=" floating-action-button position-fixed bottom-0 end-0 m-3 d-md-none">
                        <button class="btn btn-lg rounded-circle shadow color-web" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileInput" aria-controls="mobileInput">
                            <i class="bi bi-pencil-square text-white"></i>
                        </button>
                    </div>

                    <style>
                    .floating-action-button {
                        position: fixed;
                        bottom: 60px !important;
                        right: 30px;
                        text-align: right;
                    }

                    </style> -->

                <!-- Floating Action Button -->
                <div class="floating-action-button d-block d-md-none">
                    <!-- Main FAB -->
                    <button class="btn btn-lg main-fab rounded-circle shadow" id="mainFab" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileInput" aria-controls="mobileInput">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </div>

                <style>
                    /* Floating Action Button Styling */
                    .floating-action-button {
                        position: fixed;
                        bottom: 70px !important;
                        right: 30px;
                        text-align: right;
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
                </style>



                <!-- Offcanvas for mobile input -->
                <div class="offcanvas offcanvas-bottom h-75 d-md-none" tabindex="-1" id="mobileInput">
                    <div class="offcanvas-header bg-white">
                        <div class="d-flex align-items-center">
                            <div class="ios-icon-bg rounded me-2" style="width: 40px;">
                                <i class="bi bi-eye" style="color: rgb(206, 100, 65);"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Input Statistik</h6>
                                <p class="text-muted mb-0" style="font-size: 10px;">Masukkan nilai statistik untuk setiap aspek penilaian siswa</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <!-- Copy the entire form content here but remove the card wrapper -->
                        <form action="pg_statistik.php" method="POST">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success py-2 px-3 mb-3" role="alert" style="font-size: 14px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <span>Data berhasil disimpan</span>
                                        <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger py-2 px-3 mb-3" role="alert" style="font-size: 14px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        <span>Gagal menyimpan data</span>
                                        <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <div class="card-body">
                                <input type="hidden" name="siswa_id" value="<?= $siswa_id ?>">
                                <input type="hidden" name="semester" value="<?= $selected_semester ?>">
                                <input type="hidden" name="tahun_ajaran" value="<?= $selected_tahun_ajaran ?>">
                                <input type="hidden" name="nilai_akademik" value="<?= $statistik['nilai_akademik'] ?? '' ?>">
                                <input type="hidden" name="keaktifan" value="<?= $statistik['keaktifan'] ?? '' ?>">
                                <input type="hidden" name="pemahaman" value="<?= $statistik['pemahaman'] ?? '' ?>">
                                <input type="hidden" name="kehadiran_ibadah" value="<?= $statistik['kehadiran_ibadah'] ?? '' ?>">
                                <input type="hidden" name="kualitas_ibadah" value="<?= $statistik['kualitas_ibadah'] ?? '' ?>">
                                <input type="hidden" name="pemahaman_agama" value="<?= $statistik['pemahaman_agama'] ?? '' ?>">
                                <input type="hidden" name="minat_bakat" value="<?= $statistik['minat_bakat'] ?? '' ?>">
                                <input type="hidden" name="prestasi" value="<?= $statistik['prestasi'] ?? '' ?>">
                                <input type="hidden" name="keaktifan_eskul" value="<?= $statistik['keaktifan_eskul'] ?? '' ?>">
                                <input type="hidden" name="partisipasi_sosial" value="<?= $statistik['partisipasi_sosial'] ?? '' ?>">
                                <input type="hidden" name="empati" value="<?= $statistik['empati'] ?? '' ?>">
                                <input type="hidden" name="kerja_sama" value="<?= $statistik['kerja_sama'] ?? '' ?>">
                                <input type="hidden" name="kebersihan_diri" value="<?= $statistik['kebersihan_diri'] ?? '' ?>">
                                <input type="hidden" name="aktivitas_fisik" value="<?= $statistik['aktivitas_fisik'] ?? '' ?>">
                                <input type="hidden" name="pola_makan" value="<?= $statistik['pola_makan'] ?? '' ?>">
                                <input type="hidden" name="kejujuran" value="<?= $statistik['kejujuran'] ?? '' ?>">
                                <input type="hidden" name="tanggung_jawab" value="<?= $statistik['tanggung_jawab'] ?? '' ?>">
                                <input type="hidden" name="kedisiplinan" value="<?= $statistik['kedisiplinan'] ?? '' ?>">
                                <input type="hidden" name="nama" value="<?= $siswa['nama'] ?? '' ?>">
                                <div class="accordion" id="statistikAccordion">

                                    <!-- Identitas Siswa -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#identitas" style="font-size: 12px;">
                                                <i class="bi bi-person me-2"></i> Profil Siswa
                                            </button>
                                        </h2>
                                        <div id="identitas" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">NIS</label>
                                                    <input type="number" name="nis" class="form-control form-control-sm" value="<?php echo $current_student['nis'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Nama Lengkap</label>
                                                    <input type="text" name="nama" class="form-control form-control-sm" value="<?php echo $current_student['nama'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Tahun Masuk</label>
                                                    <input type="number" name="tahun_masuk" class="form-control form-control-sm" value="<?php echo $current_student['tahun_masuk'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Nomor HP</label>
                                                    <input type="number" name="no_hp" class="form-control form-control-sm" value="<?php echo $current_student['no_hp'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Alamat</label>
                                                    <textarea name="alamat" class="form-control form-control-sm" rows="2"><?php echo $current_student['alamat'] ?? ''; ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pendampingan Belajar -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#belajar" style="font-size: 12px;">
                                                <i class="bi bi-book me-2"></i> Pendampingan Belajar
                                            </button>
                                        </h2>
                                        <div id="belajar" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Nilai Akademik (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Hasil evaluasi pembelajaran dalam bentuk nilai/skor</p>
                                                    <input type="number" name="nilai_akademik" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['nilai_akademik'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Keaktifan (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Tingkat partisipasi siswa dalam proses pembelajaran maupun di luar pembelajaran</p>
                                                    <input type="number" name="keaktifan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['keaktifan'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Pemahaman (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kemampuan menangkap dan menerapkan materi yang dipelajari</p>
                                                    <input type="number" name="pemahaman" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pemahaman'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pendampingan Ibadah -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ibadah" style="font-size: 12px;">
                                                <i class="bi bi-circle me-2"></i> Pendampingan Ibadah
                                            </button>
                                        </h2>
                                        <div id="ibadah" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Kehadiran Ibadah (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Tingkat kedisiplinan dalam kegiatan ibadah wajib/sunah seperti shalat dhuha maupun kegiatan religius lainya</p>
                                                    <input type="number" name="kehadiran_ibadah" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kehadiran_ibadah'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label  p-0 m-0" style="font-size: 12px;">Kualitas Ibadah (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kesesuaian dan ketertiban di dalam pelaksanaan tata cara ibadah</p>
                                                    <input type="number" name="kualitas_ibadah" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kualitas_ibadah'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label" style="font-size: 12px;">Pemahaman Agama (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Pengetahuan tentang dasar-dasar agama dan penerapannya menurut Tarjih Muhammadiyah</p>
                                                    <input type="number" name="pemahaman_agama" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pemahaman_agama'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pengembangan Diri -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pengembangan" style="font-size: 12px;">
                                                <i class="bi bi-person-plus me-2"></i> Pengembangan Diri
                                            </button>
                                        </h2>
                                        <div id="pengembangan" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Minat Bakat (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Potensi dan ketertarikan dalam bidang yang diminati siswa</p>
                                                    <input type="number" name="minat_bakat" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['minat_bakat'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Prestasi (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Pencapaian siswa dalam berbagai bidang akademik/non-akademik</p>
                                                    <input type="number" name="prestasi" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['prestasi'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Keaktifan Ekstrakurikuler (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Partisipasi dan keaktifan siswa dalam kegiatan ekstrakurikuler</p>
                                                    <input type="number" name="keaktifan_ekskul" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['keaktifan_ekskul'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sosial -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sosial" style="font-size: 12px;">
                                                <i class="bi bi-people me-2"></i> Sosial Kemasyarakatan
                                            </button>
                                        </h2>
                                        <div id="sosial" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Partisipasi Kegiatan Sosial (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keterlibatan dalam aktivitas kemasyarakatan dalam lingkugan sekolah maupun masyarakat</p>
                                                    <input type="number" name="partisipasi_sosial" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['partisipasi_sosial'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Empati (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kepekaan terhadap kondisi dan kebutuhan orang lain</p>
                                                    <input type="number" name="empati" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['empati'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Kerja Sama (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kemampuan berkolaborasi dalam kelompok</p>
                                                    <input type="number" name="kerja_sama" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kerja_sama'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kesehatan -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kesehatan" style="font-size: 12px;">
                                                <i class="bi bi-heart-pulse me-2"></i> Kesehatan
                                            </button>
                                        </h2>
                                        <div id="kesehatan" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Kebersihan Diri (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Perawatan kebersihan dan kerapihan tubuh dan lingkungan</p>
                                                    <input type="number" name="kebersihan_diri" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kebersihan_diri'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Aktivitas Fisik (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keterlibatan dalam kegiatan olahraga/gerak badan</p>
                                                    <input type="number" name="aktivitas_fisik" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['aktivitas_fisik'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Pola Makan (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keteraturan dan kualitas asupan makanan</p>
                                                    <input type="number" name="pola_makan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pola_makan'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        .color-web {
                                            background-color: rgb(206, 100, 65);
                                            color: white;
                                        }
                                    </style>

                                    <!-- Karakter -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#karakter" style="font-size: 12px;">
                                                <i class="bi bi-star me-2"></i> Karakter
                                            </button>
                                        </h2>
                                        <div id="karakter" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Kejujuran (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;"> Keselarasan antara ucapan dan tindakan</p>
                                                    <input type="number" name="kejujuran" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kejujuran'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Tanggung Jawab (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kesediaan menyelesaikan tugas dan kewajiban</p>
                                                    <input type="number" name="tanggung_jawab" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['tanggung_jawab'] ?? "Belum ada data"; ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label p-0 m-0" style="font-size: 12px;">Disiplin (%)</label>
                                                    <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Ketaatan terhadap aturan dan jadwal yang ditetapkan</p>
                                                    <input type="number" name="kedisiplinan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kedisiplinan'] ?? "Belum ada data"; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-grid">
                                    <button type="submit" class="btn color-web" style="background-color: rgb(206, 100, 65); color: white;">Simpan</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>



            <!-- Original desktop version with d-none d-md-block -->
            <div class="col-md-3 d-none d-md-block">
                <div class="card h-100 border" style="border-radius:15px">
                    <div class="card-header bg-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <div class="d-flex align-items-center">
                            <div class="ios-icon-bg rounded me-2" style="width: 60px;">
                                <i class="bi bi-eye" style="color: rgb(206, 100, 65);"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Input Statistik</h6>
                                <p class="text-muted mb-0" style="font-size: 10px;">Masukkan nilai statistik untuk setiap aspek penilaian siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 pt-1">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success py-2 px-3 mb-3" role="alert" style="font-size: 14px;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <span>Data berhasil disimpan</span>
                                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger py-2 px-3 mb-3" role="alert" style="font-size: 14px;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    <span>Gagal menyimpan data</span>
                                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <form action="pg_statistik.php" method="POST">
                            <input type="hidden" name="siswa_id" value="<?= $siswa_id ?>">
                            <input type="hidden" name="semester" value="<?= $selected_semester ?>">
                            <input type="hidden" name="tahun_ajaran" value="<?= $selected_tahun_ajaran ?>">
                            <input type="hidden" name="nilai_akademik" value="<?= $statistik['nilai_akademik'] ?? '' ?>">
                            <input type="hidden" name="keaktifan" value="<?= $statistik['keaktifan'] ?? '' ?>">
                            <input type="hidden" name="pemahaman" value="<?= $statistik['pemahaman'] ?? '' ?>">
                            <input type="hidden" name="kehadiran_ibadah" value="<?= $statistik['kehadiran_ibadah'] ?? '' ?>">
                            <input type="hidden" name="kualitas_ibadah" value="<?= $statistik['kualitas_ibadah'] ?? '' ?>">
                            <input type="hidden" name="pemahaman_agama" value="<?= $statistik['pemahaman_agama'] ?? '' ?>">
                            <input type="hidden" name="minat_bakat" value="<?= $statistik['minat_bakat'] ?? '' ?>">
                            <input type="hidden" name="prestasi" value="<?= $statistik['prestasi'] ?? '' ?>">
                            <input type="hidden" name="keaktifan_eskul" value="<?= $statistik['keaktifan_eskul'] ?? '' ?>">
                            <input type="hidden" name="partisipasi_sosial" value="<?= $statistik['partisipasi_sosial'] ?? '' ?>">
                            <input type="hidden" name="empati" value="<?= $statistik['empati'] ?? '' ?>">
                            <input type="hidden" name="kerja_sama" value="<?= $statistik['kerja_sama'] ?? '' ?>">
                            <input type="hidden" name="kebersihan_diri" value="<?= $statistik['kebersihan_diri'] ?? '' ?>">
                            <input type="hidden" name="aktivitas_fisik" value="<?= $statistik['aktivitas_fisik'] ?? '' ?>">
                            <input type="hidden" name="pola_makan" value="<?= $statistik['pola_makan'] ?? '' ?>">
                            <input type="hidden" name="kejujuran" value="<?= $statistik['kejujuran'] ?? '' ?>">
                            <input type="hidden" name="tanggung_jawab" value="<?= $statistik['tanggung_jawab'] ?? '' ?>">
                            <input type="hidden" name="kedisiplinan" value="<?= $statistik['kedisiplinan'] ?? '' ?>">
                            <input type="hidden" name="nama" value="<?= $siswa['nama'] ?? '' ?>">


                            <div class="accordion" id="statistikAccordion">

                                <!-- Identitas Siswa -->
                                <div class="accordion-item border">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#identitas" style="font-size: 12px;">
                                            <i class="bi bi-person me-2"></i> Profil Siswa
                                        </button>
                                    </h2>
                                    <div id="identitas" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">NIS</label>
                                                <input type="number" name="nis" class="form-control form-control-sm" value="<?php echo $current_student['nis'] ?? ''; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Nama Lengkap</label>
                                                <input type="text" name="nama" class="form-control form-control-sm" value="<?php echo $current_student['nama'] ?? ''; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Tahun Masuk</label>
                                                <input type="number" name="tahun_masuk" class="form-control form-control-sm" value="<?php echo $current_student['tahun_masuk'] ?? ''; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Nomor HP</label>
                                                <input type="number" name="no_hp" class="form-control form-control-sm" value="<?php echo $current_student['no_hp'] ?? ''; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Alamat</label>
                                                <textarea name="alamat" class="form-control form-control-sm" rows="2"><?php echo $current_student['alamat'] ?? ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pendampingan Belajar -->
                                <div class="accordion-item border shadow-none">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#belajar" style="font-size: 12px;">
                                            <i class="bi bi-book me-2"></i> Pendampingan Belajar
                                        </button>
                                    </h2>
                                    <div id="belajar" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Nilai Akademik (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Hasil evaluasi pembelajaran dalam bentuk nilai/skor</p>
                                                <input type="number" name="nilai_akademik" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['nilai_akademik'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Keaktifan (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Tingkat partisipasi siswa dalam proses pembelajaran maupun di luar pembelajaran</p>
                                                <input type="number" name="keaktifan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['keaktifan'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Pemahaman (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kemampuan menangkap dan menerapkan materi yang dipelajari</p>
                                                <input type="number" name="pemahaman" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pemahaman'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pendampingan Ibadah -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ibadah" style="font-size: 12px;">
                                            <i class="bi bi-circle me-2"></i> Pendampingan Ibadah
                                        </button>
                                    </h2>
                                    <div id="ibadah" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Kehadiran Ibadah (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Tingkat kedisiplinan dalam kegiatan ibadah wajib/sunah seperti shalat dhuha maupun kegiatan religius lainya</p>
                                                <input type="number" name="kehadiran_ibadah" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kehadiran_ibadah'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label  p-0 m-0" style="font-size: 12px;">Kualitas Ibadah (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kesesuaian dan ketertiban di dalam pelaksanaan tata cara ibadah</p>
                                                <input type="number" name="kualitas_ibadah" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kualitas_ibadah'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" style="font-size: 12px;">Pemahaman Agama (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Pengetahuan tentang dasar-dasar agama dan penerapannya menurut Tarjih Muhammadiyah</p>
                                                <input type="number" name="pemahaman_agama" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pemahaman_agama'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pengembangan Diri -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pengembangan" style="font-size: 12px;">
                                            <i class="bi bi-person-plus me-2"></i> Pengembangan Diri
                                        </button>
                                    </h2>
                                    <div id="pengembangan" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Minat Bakat (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Potensi dan ketertarikan dalam bidang yang diminati siswa</p>
                                                <input type="number" name="minat_bakat" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['minat_bakat'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Prestasi (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Pencapaian siswa dalam berbagai bidang akademik/non-akademik</p>
                                                <input type="number" name="prestasi" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['prestasi'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Keaktifan Ekstrakurikuler (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Partisipasi dan keaktifan siswa dalam kegiatan ekstrakurikuler</p>
                                                <input type="number" name="keaktifan_ekskul" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['keaktifan_ekskul'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sosial -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sosial" style="font-size: 12px;">
                                            <i class="bi bi-people me-2"></i> Sosial Kemasyarakatan
                                        </button>
                                    </h2>
                                    <div id="sosial" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Partisipasi Kegiatan Sosial (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keterlibatan dalam aktivitas kemasyarakatan dalam lingkugan sekolah maupun masyarakat</p>
                                                <input type="number" name="partisipasi_sosial" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['partisipasi_sosial'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Empati (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kepekaan terhadap kondisi dan kebutuhan orang lain</p>
                                                <input type="number" name="empati" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['empati'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Kerja Sama (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kemampuan berkolaborasi dalam kelompok</p>
                                                <input type="number" name="kerja_sama" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kerja_sama'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kesehatan -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kesehatan" style="font-size: 12px;">
                                            <i class="bi bi-heart-pulse me-2"></i> Kesehatan
                                        </button>
                                    </h2>
                                    <div id="kesehatan" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Kebersihan Diri (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Perawatan kebersihan dan kerapihan tubuh dan lingkungan</p>
                                                <input type="number" name="kebersihan_diri" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kebersihan_diri'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Aktivitas Fisik (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keterlibatan dalam kegiatan olahraga/gerak badan</p>
                                                <input type="number" name="aktivitas_fisik" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['aktivitas_fisik'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Pola Makan (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Keteraturan dan kualitas asupan makanan</p>
                                                <input type="number" name="pola_makan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['pola_makan'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Karakter -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#karakter" style="font-size: 12px;">
                                            <i class="bi bi-star me-2"></i> Karakter
                                        </button>
                                    </h2>
                                    <div id="karakter" class="accordion-collapse collapse" data-bs-parent="#statistikAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Kejujuran (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;"> Keselarasan antara ucapan dan tindakan</p>
                                                <input type="number" name="kejujuran" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kejujuran'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Tanggung Jawab (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Kesediaan menyelesaikan tugas dan kewajiban</p>
                                                <input type="number" name="tanggung_jawab" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['tanggung_jawab'] ?? "Belum ada data"; ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label p-0 m-0" style="font-size: 12px;">Disiplin (%)</label>
                                                <p class="text-muted p-0 m-0 mb-1" style="font-size: 10px;">Ketaatan terhadap aturan dan jadwal yang ditetapkan</p>
                                                <input type="number" name="kedisiplinan" class="form-control form-control-sm" min="0" max="100" placeholder="<?php echo $statistik['kedisiplinan'] ?? "Belum ada data"; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 d-grid">
                                <button type="submit" class="btn" style="background-color: rgb(206, 100, 65); color: white;">Simpan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-3">

        </div>


    </div>
    </div>

    <!-- style accordion -->
    <style>
        .accordion-button {
            background-color: #ffffff;
            border: none;
            box-shadow: none;
            padding: 0.8rem 1.2rem;
            font-weight: 500;
            color: black;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgb(218, 119, 86);
            color: white;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(206, 206, 206, 0.2);
        }

        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='white'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        .accordion-button:hover {
            background-color: rgba(218, 119, 86, 0.1);
        }

        .accordion-button:not(.collapsed):hover {
            background-color: rgb(206, 100, 65);
        }

        .accordion-item {
            margin-bottom: 0.5rem;
            border-radius: 0.5rem !important;
            overflow: hidden;
            box-shadow: none;
        }

        .accordion-body {
            box-shadow: none;
            padding: 1rem;
        }
    </style>


</body>

</html>