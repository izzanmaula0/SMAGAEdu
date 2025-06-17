<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Debugging - hanya tampilkan untuk admin
// if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin') {
//     echo "<pre style='position:block; top:0; right:0; z-index:9999; background:white; padding:10px; font-size:12px;'>";
//     echo "SESSION: "; print_r($_SESSION);
//     echo "</pre>";
// }

// Get admin data
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Query to get all student data
$query_siswa = "SELECT s.*, 
                (SELECT COUNT(*) FROM kelas_siswa WHERE siswa_id = s.id) as jumlah_kelas 
                FROM siswa s ORDER BY s.nama ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);

if (isset($_POST['tambah_siswa'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $auto_assign = isset($_POST['auto_assign_kelas']) ? true : false;

    // Check if username already exists
    $cek_username = mysqli_query($koneksi, "SELECT * FROM siswa WHERE username = '$username'");
    if (mysqli_num_rows($cek_username) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert new student
        $insert_query = "INSERT INTO siswa (username, password, nama, tingkat, kategori, tahun_masuk, nis) 
                         VALUES ('$username', '$password', '$nama', '$tingkat', '$kategori', '$tahun_masuk', '$nis')";
        if (mysqli_query($koneksi, $insert_query)) {
            $siswa_id = mysqli_insert_id($koneksi); // Ambil ID siswa yang baru ditambahkan

            // Jika auto assign kelas dicentang dan tingkat dipilih
            if ($auto_assign && !empty($tingkat)) {
                // Ambil semua kelas yang sesuai dengan tingkat siswa
                $query_kelas = "SELECT id FROM kelas WHERE tingkat = '$tingkat'";
                $result_kelas = mysqli_query($koneksi, $query_kelas);

                // Masukkan siswa ke semua kelas yang ditemukan
                while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                    $insert_kelas_siswa = "INSERT INTO kelas_siswa (kelas_id, siswa_id, status) 
                                          VALUES ('" . $kelas['id'] . "', '$siswa_id', 'active')";
                    mysqli_query($koneksi, $insert_kelas_siswa);
                }
                $success = "Siswa berhasil ditambahkan dan dimasukkan ke " . mysqli_num_rows($result_kelas) . " kelas!";
            } else {
                $success = "Siswa berhasil ditambahkan!";
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }
}

// Fungsi untuk auto-assign siswa ke kelas berdasarkan tingkat
function autoAssignSiswaKeKelas($koneksi, $siswa_id, $tingkat)
{
    // Ambil semua kelas yang sesuai dengan tingkat
    $query_kelas = "SELECT id, nama_kelas FROM kelas WHERE tingkat = '$tingkat'";
    $result_kelas = mysqli_query($koneksi, $query_kelas);

    $jumlah_kelas = 0;
    while ($kelas = mysqli_fetch_assoc($result_kelas)) {
        // Check apakah siswa sudah terdaftar di kelas ini
        $check_existing = "SELECT id FROM kelas_siswa WHERE kelas_id = '" . $kelas['id'] . "' AND siswa_id = '$siswa_id'";
        $existing = mysqli_query($koneksi, $check_existing);

        if (mysqli_num_rows($existing) == 0) {
            // Insert jika belum terdaftar
            $insert_kelas_siswa = "INSERT INTO kelas_siswa (kelas_id, siswa_id, status, tanggal_bergabung) 
                                  VALUES ('" . $kelas['id'] . "', '$siswa_id', 'active', NOW())";
            if (mysqli_query($koneksi, $insert_kelas_siswa)) {
                $jumlah_kelas++;
            }
        }
    }

    return $jumlah_kelas;
}

// Fungsi untuk mendapatkan daftar kelas berdasarkan tingkat
function getKelasByTingkat($koneksi, $tingkat)
{
    $query = "SELECT id, nama_kelas, mata_pelajaran FROM kelas WHERE tingkat = '$tingkat' ORDER BY nama_kelas";
    $result = mysqli_query($koneksi, $query);

    $kelas_list = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $kelas_list[] = $row;
    }

    return $kelas_list;
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // Mulai transaksi database untuk memastikan semua atau tidak ada yang terhapus
    mysqli_begin_transaction($koneksi);

    try {
        // Hapus data kelas_siswa terlebih dahulu
        mysqli_query($koneksi, "DELETE FROM kelas_siswa WHERE siswa_id = '$id'");

        // Hapus data pg jika ada
        mysqli_query($koneksi, "DELETE FROM pg WHERE siswa_id = '$id'");

        // Hapus data pengumpulan_tugas jika ada
        mysqli_query($koneksi, "DELETE FROM pengumpulan_tugas WHERE siswa_id = '$id'");

        // Hapus data jawaban_ujian jika ada
        mysqli_query($koneksi, "DELETE FROM jawaban_ujian WHERE siswa_id = '$id'");

        // Hapus siswa
        mysqli_query($koneksi, "DELETE FROM siswa WHERE id = '$id'");

        // Commit transaksi jika semua berhasil
        mysqli_commit($koneksi);

        $success = "Siswa berhasil dihapus!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($koneksi);
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Manajemen Siswa - SMAGAEdu</title>
    <style>
        body {
            font-family: 'Merriweather', serif;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
        }

        .btn-primary {
            background-color: rgb(218, 119, 86);
            border-color: rgb(218, 119, 86);
        }

        .btn-primary:hover {
            background-color: rgb(190, 100, 70);
            border-color: rgb(190, 100, 70);
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th {
            font-weight: 600;
            color: #444;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
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

        /* Student detail row styling */
        .student-details {
            background-color: #f8f9fa;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.5s ease-out;
            border-radius: 0 0 12px 12px;
        }

        .student-details.show {
            max-height: 800px;
            transition: max-height 0.5s ease-in;
        }

        .student-item {
            cursor: pointer;
        }

        .student-item:hover {
            background-color: rgba(218, 119, 86, 0.05);
        }

        .student-detail-container {
            padding: 20px;
        }

        .detail-section {
            border-left: 3px solid rgb(218, 119, 86);
            padding-left: 15px;
            margin-bottom: 15px;
        }

        /* Styling untuk checkbox */
        .form-check-input {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        /* Highlight baris yang terpilih */
        tr:has(.siswa-checkbox:checked) {
            background-color: rgba(218, 119, 86, 0.1) !important;
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


    <!-- Main Content -->
    <div class="col col-inti p-0 p-md-3">
        <style>
            .col-inti {
                margin-left: 0;
                padding: 1rem;
                max-width: 100%;
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
                    padding: 2rem;
                }
            }

            @media screen and (max-width: 768px) {
                .col-inti {
                    margin-left: 0.5rem;
                    margin-right: 0.5rem;
                    padding: 1rem;
                }
            }
        </style>

        <div class="container-fluid">
            <div class="d-flex judul justify-content-between align-items-center mt-2 mb-4">
                <h2 class="mb-0 fw-bold">Siswa</h2>
                <div>
                    <div class="btn-group me-2" role="group">
                        <button class="btn btn-white border dropdown-toggle" style="border-radius:15px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people me-1"></i> Siswa
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#promoteSiswaModal">
                                    <i class="bi bi-arrow-up-circle me-2"></i> Pindah Tingkat Siswa
                                </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#tambahSiswaModal">
                                    <i class="bi bi-plus-circle me-2"></i> Tambah Siswa
                                </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importSiswaModal">
                                    <i class="bi bi-file-earmark-word me-2"></i> Import Siswa
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#" id="btnDeleteGroup">
                                    <i class="bi bi-trash me-2"></i> Hapus Kelompok
                                </a></li>
                            <li><a class="dropdown-item text-danger" href="#" id="btnDeleteSelected" style="display: none;">
                                    <i class="bi bi-trash me-2"></i> Hapus Pilihan Terpilih
                                </a></li>
                        </ul>
                    </div>

                    <div class="btn-group" role="group">
                        <button class="btn btn-white border dropdown-toggle" style="border-radius:15px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#ubahKategoriModal">
                                    <i class="bi bi-tags me-2"></i> Ubah Kategori
                                </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#ubahTingkatModal">
                                    <i class="bi bi-arrow-up me-2"></i> Ubah Tingkat
                                </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    <i class="bi bi-key me-2"></i> Reset Password
                                </a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success animate-fade-in">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>



            <!-- Statistik Siswa -->
            <div class="row laporan-singkat mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-people-fill" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold"><?php echo mysqli_num_rows($result_siswa); ?></h3>
                                <p class="text-muted mb-0">Total Siswa</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-building" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <?php
                                $query_kelas = "SELECT COUNT(*) as total FROM kelas";
                                $result_kelas = mysqli_query($koneksi, $query_kelas);
                                $total_kelas = mysqli_fetch_assoc($result_kelas)['total'];
                                ?>
                                <h3 class="mb-0 fw-bold"><?php echo $total_kelas; ?></h3>
                                <p class="text-muted mb-0">Total Kelas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-person-check-fill" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <?php
                                $query_aktif = "SELECT COUNT(DISTINCT siswa_id) as total FROM kelas_siswa WHERE status = 'active'";
                                $result_aktif = mysqli_query($koneksi, $query_aktif);
                                $total_aktif = mysqli_fetch_assoc($result_aktif)['total'];
                                ?>
                                <h3 class="mb-0 fw-bold"><?php echo $total_aktif; ?></h3>
                                <p class="text-muted mb-0">Siswa Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- style tabel siswa -->
            <style>
                /* Efek hover pada baris tabel */
                .siswa-item {
                    transition: all 0.2s ease;
                    border-left: 3px solid transparent;
                }

                .siswa-item:hover {
                    background-color: rgba(218, 119, 86, 0.05);
                    cursor: pointer;
                    transform: translateX(3px);
                }

                /* Efek aktif pada baris yang diklik */
                .siswa-item.active {
                    background-color: rgba(218, 119, 86, 0.1);
                    border-left: 3px solid rgb(218, 119, 86);
                }

                /* Animasi untuk baris detail */
                .student-details {
                    background-color: #f8f9fa;
                    overflow: hidden;
                    max-height: 0;
                    transition: max-height 0.5s ease-out, opacity 0.4s ease-out;
                    opacity: 0;
                }

                .student-details.show {
                    max-height: 1500px;
                    /* Nilai ini bisa disesuaikan */
                    opacity: 1;
                    transition: max-height 0.5s ease-in, opacity 0.4s ease-in;
                    box-shadow: inset 0 5px 10px -5px rgba(0, 0, 0, 0.1);
                }
            </style>

            <!-- style untuk print -->
            <!-- CSS untuk styling cetak -->
            <style>
                @media print {

                    /* Sembunyikan elemen yang tidak ingin dicetak */
                    .sidebar,
                    .navbar,
                    nav,
                    .mobile-nav,
                    .btn-group,
                    .btn,
                    .modal,
                    .input-group,
                    #searchSiswa,
                    .delete-checkbox,
                    .editBtn,
                    .deleteBtn,
                    .laporan-singkat,
                    .judul,

                    footer {
                        display: none !important;
                    }

                    /* Tampilkan konten penuh pada saat mencetak */
                    .col-inti {
                        margin-left: 0 !important;
                        width: 100% !important;
                        padding: 0 !important;
                    }

                    /* Atur header cetak */
                    .print-header {
                        text-align: center;
                        margin-bottom: 20px;
                    }

                    /* Fit halaman cetak dengan ukuran kertas */
                    body {
                        width: 100%;
                        margin: 0;
                        padding: 0;
                    }

                    /* Sembunyikan tombol detail/dropdown pada table */
                    .student-details.show {
                        display: none !important;
                    }

                    /* Sembunyikan baris yang tersembunyi oleh filter */
                    .siswa-item[style*="display: none"] {
                        display: none !important;
                    }

                    /* Siswa yang terlihat akan dicetak */
                    .siswa-item:not([style*="display: none"]) {
                        display: table-row !important;
                    }

                    /* Hapus efek hover */
                    .siswa-item:hover {
                        transform: none !important;
                        background-color: transparent !important;
                    }

                    /* Tampilkan semua halaman */
                    @page {
                        size: auto;
                        margin: 0.5cm;
                    }
                }

                /* Konten yang hanya muncul saat mencetak */
                .print-only {
                    display: none;
                }

                @media print {
                    .print-only {
                        display: block;
                    }
                }
            </style>


            <!-- iOS-style Student List Card -->
            <div class="card animate-fade-in shadow-none border" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <!-- Search bar iOS style -->
                    <div class="px-4 pt-4 pb-2">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchSiswa" class="form-control bg-light border-0"
                                placeholder="Cari siswa..." style="border-radius: 0 0 0 0;">
                            <button class="btn bg-light border-0 dropdown-toggle" type="button"
                                style="border-radius: 0 12px 12px 0;"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item filter-tingkat active" href="#" data-tingkat="all">Semua</a></li>
                                <?php
                                // Ambil daftar tingkat unik dari siswa
                                $query_tingkat = "SELECT DISTINCT tingkat FROM siswa WHERE tingkat IS NOT NULL ORDER BY tingkat";
                                $result_tingkat = mysqli_query($koneksi, $query_tingkat);
                                while ($tingkat = mysqli_fetch_assoc($result_tingkat)) {
                                    echo '<li><a class="dropdown-item filter-tingkat" href="#" data-tingkat="' . $tingkat['tingkat'] . '">' . $tingkat['tingkat'] . '</a></li>';
                                }
                                ?>
                                <!-- kategori pondok atau sma -->
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item filter-kategori" href="#" data-kategori="pondok">Pondok</a></li>
                                <li><a class="dropdown-item filter-kategori" href="#" data-kategori="sma">SMA</a></li>
                            </ul>
                            <button id="printButton" class="btn bg-light border-0" type="button" style="border-radius: 0 12px 12px 0;">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>

                    <form id="deleteGroupForm" action="delete_selected_siswa.php" method="POST" style="display: none;">
                        <!-- Checkbox akan ditambahkan di sini secara dinamis oleh JavaScript -->
                        <input type="hidden" name="action" value="delete_selected">
                    </form>

                    <div class="table-responsive px-2">
                        <table class="table table-borderless align-middle">
                            <thead class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                                <tr>
                                    <th class="delete-checkbox" style="width: 5%; display: none;">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </th>
                                    <th class="ps-3" style="width: 10%">Foto</th>
                                    <th style="width: 25%">Nama Lengkap</th>
                                    <th style="width: 20%">NIS</th>
                                    <th style="width: 15%">Username</th>
                                    <th style="width: 15%">Password</th>
                                    <th style="width: 12%">Tingkat</th>
                                    <th style="width: 12%">Kategori</th>
                                    <th style="width: 15%">Kelas</th>
                                    <th style="width: 20%"></th>
                                </tr>
                            </thead>
                            <tbody id="siswaTableBody">
                                <?php if (mysqli_num_rows($result_siswa) > 0): ?>
                                    <?php while ($siswa = mysqli_fetch_assoc($result_siswa)): ?>
                                        <tr class="siswa-item" data-siswa-id="<?php echo $siswa['id']; ?>" data-tingkat="<?php echo htmlspecialchars($siswa['tingkat']); ?>">
                                            <td class="delete-checkbox" style="display: none;">
                                                <input type="checkbox" class="font-check-input siswa-checkbox" name="delete_ids[]" value="<?php echo $siswa['id']; ?>">
                                            </td>
                                            <td class="ps-3">
                                                <div style="width: 48px; height: 48px; overflow: hidden; border-radius: 12px;" class="border">
                                                    <img src="<?php
                                                                if (!empty($siswa['photo_type'])) {
                                                                    if ($siswa['photo_type'] === 'avatar') {
                                                                        echo $siswa['photo_url'];
                                                                    } else if ($siswa['photo_type'] === 'upload') {
                                                                        echo 'uploads/profil/' . $siswa['foto_profil'];
                                                                    }
                                                                } else {
                                                                    echo 'assets/pp.png';
                                                                }
                                                                ?>"
                                                        alt="<?php echo htmlspecialchars($siswa['nama']); ?>"
                                                        class="w-100 h-100" style="object-fit: cover;">
                                                </div>
                                            </td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($siswa['nama']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($siswa['nis']) ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($siswa['username']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($siswa['password']); ?></td>
                                            <td><span class="text-muted"><?php echo htmlspecialchars($siswa['tingkat'] ?: 'Belum diatur'); ?></span></td>
                                            <td>
                                                <span class="badge <?php echo $siswa['kategori'] == 'Pondok' ? 'bg-success' : 'bg-primary'; ?>"
                                                    style="font-weight: 600; padding: 5px 10px; border-radius: 6px;">
                                                    <?php echo htmlspecialchars($siswa['kategori'] ?: 'SMA'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: rgba(218, 119, 86, 0.15); color: rgb(218, 119, 86); font-weight: 600; padding: 5px 10px; border-radius: 6px;">
                                                    <?php echo $siswa['jumlah_kelas']; ?> kelas
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button"
                                                        class="btn btn-sm text-primary editBtn"
                                                        style="background: none;"
                                                        data-id="<?php echo $siswa['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($siswa['nama']); ?>"
                                                        data-username="<?php echo htmlspecialchars($siswa['username']); ?>"
                                                        data-password="<?php echo htmlspecialchars($siswa['password']); ?>"
                                                        data-tingkat="<?php echo htmlspecialchars($siswa['tingkat']); ?>"
                                                        data-kategori="<?php echo htmlspecialchars($siswa['kategori']); ?>"
                                                        data-nis="<?php echo htmlspecialchars($siswa['nis']); ?>"
                                                        data-tahun-masuk="<?php echo htmlspecialchars($siswa['tahun_masuk']); ?>"
                                                        data-no-hp="<?php echo htmlspecialchars($siswa['no_hp']); ?>"
                                                        data-alamat="<?php echo htmlspecialchars($siswa['alamat']); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSiswaModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm text-danger deleteBtn"
                                                        style="background: none;"
                                                        data-id="<?php echo $siswa['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($siswa['nama']); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Expandable Student Details Row -->
                                        <tr>
                                            <td colspan="6" class="p-0">
                                                <div class="student-details" id="details-<?php echo $siswa['id']; ?>">
                                                    <div class="student-detail-container">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="detail-section">
                                                                    <h6 class="fw-bold mb-3">Informasi Pribadi</h6>
                                                                    <div class="mb-2">
                                                                        <span class="text-muted">NIS:</span>
                                                                        <span class="ms-2 fw-medium"><?php echo $siswa['nis'] ?: 'Belum diatur'; ?></span>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <span class="text-muted">Tahun Masuk:</span>
                                                                        <span class="ms-2 fw-medium"><?php echo $siswa['tahun_masuk'] ?: 'Belum diatur'; ?></span>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <span class="text-muted">No. HP:</span>
                                                                        <span class="ms-2 fw-medium"><?php echo $siswa['no_hp'] ?: 'Belum diatur'; ?></span>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <span class="text-muted">Alamat:</span>
                                                                        <span class="ms-2 fw-medium"><?php echo $siswa['alamat'] ?: 'Belum diatur'; ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="detail-section">
                                                                    <h6 class="fw-bold mb-3">Kelas Terdaftar</h6>
                                                                    <?php
                                                                    $query_kelas_siswa = "SELECT k.nama_kelas, k.mata_pelajaran, g.namaLengkap as guru 
                                                                                        FROM kelas_siswa ks 
                                                                                        JOIN kelas k ON ks.kelas_id = k.id 
                                                                                        LEFT JOIN guru g ON k.guru_id = g.username 
                                                                                        WHERE ks.siswa_id = {$siswa['id']}
                                                                                        LIMIT 5";
                                                                    $result_kelas_siswa = mysqli_query($koneksi, $query_kelas_siswa);

                                                                    if (mysqli_num_rows($result_kelas_siswa) > 0) {
                                                                        echo '<div class="list-group list-group-flush">';
                                                                        while ($kelas = mysqli_fetch_assoc($result_kelas_siswa)) {
                                                                            echo '<div class="list-group-item bg-light px-0 py-2 border-0">';
                                                                            echo '<div class="d-flex justify-content-between align-items-center">';
                                                                            echo '<span class="fw-medium">' . htmlspecialchars($kelas['nama_kelas']) . '</span>';
                                                                            echo '</div>';
                                                                            echo '<div class="small text-muted">' . htmlspecialchars($kelas['mata_pelajaran']) . ' • ' . htmlspecialchars($kelas['guru']) . '</div>';
                                                                            echo '</div>';
                                                                        }
                                                                        echo '</div>';
                                                                    } else {
                                                                        echo '<p class="text-muted">Tidak ada kelas terdaftar</p>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Progressive Guidance Stats -->
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <div class="detail-section">
                                                                    <h6 class="fw-bold mb-3">Progressive Guidance</h6>
                                                                    <?php
                                                                    $query_pg = "SELECT * FROM pg WHERE siswa_id = {$siswa['id']} ORDER BY semester DESC, tahun_ajaran DESC LIMIT 1";
                                                                    $result_pg = mysqli_query($koneksi, $query_pg);

                                                                    if (mysqli_num_rows($result_pg) > 0) {
                                                                        $pg = mysqli_fetch_assoc($result_pg);
                                                                    ?>
                                                                        <div class="row g-3">
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Belajar</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null academic values
                                                                                        $query_latest = "SELECT 
                                                                                            COALESCE(MAX(NULLIF(nilai_akademik,0)), 0) as nilai_akademik,
                                                                                            COALESCE(MAX(NULLIF(keaktifan,0)), 0) as keaktifan,
                                                                                            COALESCE(MAX(NULLIF(pemahaman,0)), 0) as pemahaman,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg 
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (nilai_akademik > 0 OR keaktifan > 0 OR pemahaman > 0)";

                                                                                        $result_latest = mysqli_query($koneksi, $query_latest);
                                                                                        $latest_data = mysqli_fetch_assoc($result_latest);

                                                                                        $nilai_akademik = intval($latest_data['nilai_akademik']);
                                                                                        $keaktifan = intval($latest_data['keaktifan']);
                                                                                        $pemahaman = intval($latest_data['pemahaman']);
                                                                                        $belajar = ($nilai_akademik + $keaktifan + $pemahaman) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $belajar; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Akademik:</span>
                                                                                                <span class="fw-medium"><?php echo $nilai_akademik; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Keaktifan:</span>
                                                                                                <span class="fw-medium"><?php echo $keaktifan; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Pemahaman:</span>
                                                                                                <span class="fw-medium"><?php echo $pemahaman; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($belajar); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data['semester']; ?> Th. <?php echo $latest_data['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Ibadah</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null ibadah values
                                                                                        $query_latest_ibadah = "SELECT
                                                                                            COALESCE(MAX(NULLIF(kehadiran_ibadah,0)), 0) AS kehadiran_ibadah,
                                                                                            COALESCE(MAX(NULLIF(kualitas_ibadah,0)), 0) AS kualitas_ibadah,
                                                                                            COALESCE(MAX(NULLIF(pemahaman_agama,0)), 0) AS pemahaman_agama,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (kehadiran_ibadah > 0 OR kualitas_ibadah > 0 OR pemahaman_agama > 0)";

                                                                                        $result_latest_ibadah = mysqli_query($koneksi, $query_latest_ibadah);
                                                                                        $latest_data_ibadah = mysqli_fetch_assoc($result_latest_ibadah);

                                                                                        $kehadiran_ibadah = intval($latest_data_ibadah['kehadiran_ibadah']);
                                                                                        $kualitas_ibadah = intval($latest_data_ibadah['kualitas_ibadah']);
                                                                                        $pemahaman_agama = intval($latest_data_ibadah['pemahaman_agama']);
                                                                                        $ibadah = ($kehadiran_ibadah + $kualitas_ibadah + $pemahaman_agama) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $ibadah; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kehadiran:</span>
                                                                                                <span class="fw-medium"><?php echo $kehadiran_ibadah; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kualitas:</span>
                                                                                                <span class="fw-medium"><?php echo $kualitas_ibadah; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Pemahaman:</span>
                                                                                                <span class="fw-medium"><?php echo $pemahaman_agama; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($ibadah); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data_ibadah['semester']; ?> Th. <?php echo $latest_data_ibadah['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Pengembangan</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null pengembangan values
                                                                                        $query_latest_pengembangan = "SELECT
                                                                                            COALESCE(MAX(NULLIF(minat_bakat,0)), 0) AS minat_bakat,
                                                                                            COALESCE(MAX(NULLIF(prestasi,0)), 0) AS prestasi,
                                                                                            COALESCE(MAX(NULLIF(keaktifan_ekskul,0)), 0) AS keaktifan_ekskul,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (minat_bakat > 0 OR prestasi > 0 OR keaktifan_ekskul > 0)";

                                                                                        $result_latest_pengembangan = mysqli_query($koneksi, $query_latest_pengembangan);
                                                                                        $latest_data_pengembangan = mysqli_fetch_assoc($result_latest_pengembangan);

                                                                                        $minat_bakat = intval($latest_data_pengembangan['minat_bakat']);
                                                                                        $prestasi = intval($latest_data_pengembangan['prestasi']);
                                                                                        $keaktifan_ekskul = intval($latest_data_pengembangan['keaktifan_ekskul']);
                                                                                        $pengembangan = ($minat_bakat + $prestasi + $keaktifan_ekskul) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $pengembangan; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Minat & Bakat:</span>
                                                                                                <span class="fw-medium"><?php echo $minat_bakat; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Prestasi:</span>
                                                                                                <span class="fw-medium"><?php echo $prestasi; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Keaktifan:</span>
                                                                                                <span class="fw-medium"><?php echo $keaktifan_ekskul; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($pengembangan); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data_pengembangan['semester']; ?> Th. <?php echo $latest_data_pengembangan['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Sosial</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null sosial values
                                                                                        $query_latest_sosial = "SELECT
                                                                                            COALESCE(MAX(NULLIF(partisipasi_sosial,0)), 0) AS partisipasi_sosial,
                                                                                            COALESCE(MAX(NULLIF(empati,0)), 0) AS empati,
                                                                                            COALESCE(MAX(NULLIF(kerja_sama,0)), 0) AS kerja_sama,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (partisipasi_sosial > 0 OR empati > 0 OR kerja_sama > 0)";

                                                                                        $result_latest_sosial = mysqli_query($koneksi, $query_latest_sosial);
                                                                                        $latest_data_sosial = mysqli_fetch_assoc($result_latest_sosial);

                                                                                        $partisipasi_sosial = intval($latest_data_sosial['partisipasi_sosial']);
                                                                                        $empati = intval($latest_data_sosial['empati']);
                                                                                        $kerja_sama = intval($latest_data_sosial['kerja_sama']);
                                                                                        $sosial = ($partisipasi_sosial + $empati + $kerja_sama) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $sosial; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Partisipasi:</span>
                                                                                                <span class="fw-medium"><?php echo $partisipasi_sosial; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Empati:</span>
                                                                                                <span class="fw-medium"><?php echo $empati; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kerja Sama:</span>
                                                                                                <span class="fw-medium"><?php echo $kerja_sama; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($sosial); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data_sosial['semester']; ?> Th. <?php echo $latest_data_sosial['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Kesehatan</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null kesehatan values
                                                                                        $query_latest_kesehatan = "SELECT
                                                                                            COALESCE(MAX(NULLIF(kebersihan_diri,0)), 0) AS kebersihan_diri,
                                                                                            COALESCE(MAX(NULLIF(aktivitas_fisik,0)), 0) AS aktivitas_fisik,
                                                                                            COALESCE(MAX(NULLIF(pola_makan,0)), 0) AS pola_makan,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (kebersihan_diri > 0 OR aktivitas_fisik > 0 OR pola_makan > 0)";

                                                                                        $result_latest_kesehatan = mysqli_query($koneksi, $query_latest_kesehatan);
                                                                                        $latest_data_kesehatan = mysqli_fetch_assoc($result_latest_kesehatan);

                                                                                        $kebersihan = intval($latest_data_kesehatan['kebersihan_diri']);
                                                                                        $aktivitas = intval($latest_data_kesehatan['aktivitas_fisik']);
                                                                                        $pola_makan = intval($latest_data_kesehatan['pola_makan']);
                                                                                        $kesehatan = ($kebersihan + $aktivitas + $pola_makan) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $kesehatan; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kebersihan:</span>
                                                                                                <span class="fw-medium"><?php echo $kebersihan; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Aktivitas:</span>
                                                                                                <span class="fw-medium"><?php echo $aktivitas; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Pola Makan:</span>
                                                                                                <span class="fw-medium"><?php echo $pola_makan; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($kesehatan); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data_kesehatan['semester']; ?> Th. <?php echo $latest_data_kesehatan['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="card border bg-light shadow-none">
                                                                                    <div class="card-body p-3">
                                                                                        <h6 class="card-title mb-2">Karakter</h6>
                                                                                        <?php
                                                                                        // Get latest non-zero/non-null karakter values
                                                                                        $query_latest_karakter = "SELECT
                                                                                            COALESCE(MAX(NULLIF(kejujuran,0)), 0) AS kejujuran,
                                                                                            COALESCE(MAX(NULLIF(tanggung_jawab,0)), 0) AS tanggung_jawab,
                                                                                            COALESCE(MAX(NULLIF(kedisiplinan,0)), 0) AS kedisiplinan,
                                                                                            semester,
                                                                                            tahun_ajaran
                                                                                        FROM pg
                                                                                        WHERE siswa_id = {$siswa['id']}
                                                                                        AND (kejujuran > 0 OR tanggung_jawab > 0 OR kedisiplinan > 0)";

                                                                                        $result_latest_karakter = mysqli_query($koneksi, $query_latest_karakter);
                                                                                        $latest_data_karakter = mysqli_fetch_assoc($result_latest_karakter);

                                                                                        $kejujuran = intval($latest_data_karakter['kejujuran']);
                                                                                        $tanggung_jawab = intval($latest_data_karakter['tanggung_jawab']);
                                                                                        $kedisiplinan = intval($latest_data_karakter['kedisiplinan']);
                                                                                        $karakter = ($kejujuran + $tanggung_jawab + $kedisiplinan) / 3;
                                                                                        ?>
                                                                                        <div class="progress mb-3" style="height: 8px;">
                                                                                            <div class="progress-bar color-web" style="width: <?php echo $karakter; ?>%"></div>
                                                                                        </div>

                                                                                        <ul class="list-unstyled mb-2">
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kejujuran:</span>
                                                                                                <span class="fw-medium"><?php echo $kejujuran; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Tanggung Jawab:</span>
                                                                                                <span class="fw-medium"><?php echo $tanggung_jawab; ?>%</span>
                                                                                            </li>
                                                                                            <li class="d-flex justify-content-between small mb-1">
                                                                                                <span class="text-muted">• Kedisiplinan:</span>
                                                                                                <span class="fw-medium"><?php echo $kedisiplinan; ?>%</span>
                                                                                            </li>
                                                                                        </ul>

                                                                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                                                                            <span class="badge bg-light text-dark"><?php echo round($karakter); ?>%</span>
                                                                                            <small class="text-muted">Sem <?php echo $latest_data_karakter['semester']; ?> Th. <?php echo $latest_data_karakter['tahun_ajaran']; ?></small>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php
                                                                    } else {
                                                                        echo '<p class="text-muted">Belum ada data penilaian</p>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-4">
                                                <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">Tidak ada data siswa</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Ubah Kategori -->
            <div class="modal fade" id="ubahKategoriModal" tabindex="-1" aria-labelledby="ubahKategoriModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="ubahKategoriModalLabel">Ubah Kategori Siswa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="update_kategori_siswa.php" method="POST">
                            <div class="modal-body px-4">
                                <div class="row g-4">
                                    <!-- Form Kategori -->
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-medium small mb-2">Filter Berdasarkan Kategori Saat Ini</label>
                                            <select class="form-select bg-light border" id="filterKategori" style="border-radius: 12px; padding: 12px 15px;">
                                                <option value="">Semua Kategori</option>
                                                <option value="SMA">SMA</option>
                                                <option value="Pondok">Pondok</option>
                                            </select>
                                            <small class="text-muted">Filter untuk mempermudah pencarian siswa</small>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label class="form-label fw-medium small mb-2">Ubah Kategori Menjadi</label>
                                            <select class="form-select bg-light border" name="kategori_baru" id="kategoriBaru" style="border-radius: 12px; padding: 12px 15px;" required>
                                                <option value="">Pilih Kategori Baru</option>
                                                <option value="SMA">SMA</option>
                                                <option value="Pondok">Pondok</option>
                                            </select>
                                        </div>

                                        <div class="alert alert-info">
                                            <small>
                                                <i class="bi bi-info-circle me-1"></i>
                                                Pilih siswa yang ingin diubah kategorinya dari daftar di sebelah kanan.
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Daftar Siswa -->
                                    <div class="col-12 col-md-6">
                                        <div class="bg-light border p-3" style="border-radius: 15px;">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <label class="form-label small mb-0 fw-medium">Pilih Siswa</label>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllKategori">Pilih Semua</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="deselectAllKategori">Hapus Semua</button>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm bg-white" id="searchSiswaKategori"
                                                    placeholder="Cari nama siswa..." style="border-radius: 10px;">
                                            </div>

                                            <div id="daftarSiswaKategori" class="overflow-auto" style="max-height: 300px;">
                                                <?php
                                                // Reset query siswa untuk modal
                                                $query_siswa_kategori = "SELECT id, nama, kategori, tingkat FROM siswa ORDER BY nama ASC";
                                                $result_siswa_kategori = mysqli_query($koneksi, $query_siswa_kategori);

                                                if (mysqli_num_rows($result_siswa_kategori) > 0): ?>
                                                    <ul class="list-group list-group-flush">
                                                        <?php while ($siswa = mysqli_fetch_assoc($result_siswa_kategori)): ?>
                                                            <li class="list-group-item bg-transparent d-flex align-items-center py-2 siswa-item-kategori"
                                                                data-kategori="<?php echo $siswa['kategori']; ?>" data-nama="<?php echo strtolower($siswa['nama']); ?>">
                                                                <div class="form-check flex-grow-1">
                                                                    <input class="form-check-input siswa-kategori-checkbox" type="checkbox"
                                                                        name="siswa_ids[]" value="<?php echo $siswa['id']; ?>" id="siswaKat<?php echo $siswa['id']; ?>">
                                                                    <label class="form-check-label w-100" for="siswaKat<?php echo $siswa['id']; ?>">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span><?php echo htmlspecialchars($siswa['nama']); ?></span>
                                                                            <div>
                                                                                <span class="badge <?php echo $siswa['kategori'] == 'Pondok' ? 'bg-success' : 'bg-primary'; ?> me-1"
                                                                                    style="font-size: 0.7rem;">
                                                                                    <?php echo $siswa['kategori'] ?: 'SMA'; ?>
                                                                                </span>
                                                                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                                                                    <?php echo $siswa['tingkat'] ?: '-'; ?>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        <?php endwhile; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <div class="text-center py-4 text-muted">
                                                        <i class="bi bi-people"></i> Tidak ada data siswa
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group border-0 pt-0">
                                <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="update_kategori" class="btn btn-primary"
                                    style="border-radius: 12px; padding: 10px 20px;">Ubah Kategori</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Filter siswa berdasarkan kategori
                    const filterKategori = document.getElementById('filterKategori');
                    const searchSiswaKategori = document.getElementById('searchSiswaKategori');
                    const siswaItems = document.querySelectorAll('.siswa-item-kategori');

                    function filterSiswaKategori() {
                        const filterValue = filterKategori.value;
                        const searchValue = searchSiswaKategori.value.toLowerCase();

                        siswaItems.forEach(item => {
                            const kategori = item.getAttribute('data-kategori');
                            const nama = item.getAttribute('data-nama');

                            const matchKategori = !filterValue || kategori === filterValue;
                            const matchNama = !searchValue || nama.includes(searchValue);

                            if (matchKategori && matchNama) {
                                item.style.display = '';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    }

                    filterKategori.addEventListener('change', filterSiswaKategori);
                    searchSiswaKategori.addEventListener('input', filterSiswaKategori);

                    // Select/deselect all siswa untuk kategori
                    document.getElementById('selectAllKategori').addEventListener('click', function() {
                        document.querySelectorAll('.siswa-kategori-checkbox').forEach(checkbox => {
                            if (checkbox.closest('.siswa-item-kategori').style.display !== 'none') {
                                checkbox.checked = true;
                            }
                        });
                    });

                    document.getElementById('deselectAllKategori').addEventListener('click', function() {
                        document.querySelectorAll('.siswa-kategori-checkbox').forEach(checkbox => {
                            checkbox.checked = false;
                        });
                    });
                });
            </script>

            <!-- Modal Import Siswa dari Word -->
            <div class="modal fade" id="importSiswaModal" tabindex="-1" aria-labelledby="importSiswaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="importSiswaModalLabel">Import Data Siswa dari Word</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="import_siswa.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body px-4">
                                <div class="mb-4">
                                    <label for="file_siswa" class="form-label fw-medium small mb-2">File Word (.docx)</label>
                                    <input type="file" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                        id="file_siswa" name="file_siswa" accept=".docx" required>
                                    <small class="text-muted">Upload file Word yang berisi data siswa sesuai template</small>
                                </div>

                                <div class="alert alert-info">
                                    <h6 class="fw-bold">Format Template</h6>
                                    <p class="small mb-2">Gunakan format tabel dengan kolom berikut:</p>
                                    <ul class="small mb-2">
                                        <li>Username (wajib)</li>
                                        <li>Password (wajib)</li>
                                        <li>Nama Lengkap (wajib)</li>
                                        <li>Tingkat (wajib)</li>
                                        <li>Tahun Masuk (wajib)</li>
                                        <li>NIS (opsional)</li>
                                        <li>No. HP (opsional)</li>
                                        <li>Alamat (opsional)</li>
                                    </ul>
                                    <a href="templates/template_import_siswa.docx" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bi bi-download me-1"></i> Download Template
                                    </a>
                                </div>
                            </div>
                            <div class="modal-footer btn-group border-0 pt-0">
                                <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="import_siswa" class="btn btn-primary"
                                    style="border-radius: 12px; padding: 10px 20px;">Import Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <div class="modal-body px-4 text-center">
                            <div class="mb-4">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="mb-3 fw-bold">Hapus " <strong id="deleteSiswaName"></strong> " dari SMAGAEdu?</h5>
                            <p class="text-muted">Anda akan menghapus data siswa <strong id="deleteSiswaName"></strong> dari database. Pastikan seluruh tindakan Anda telah sesuai</p>
                        </div>
                        <div class="modal-footer border-0 pt-0 btn-group">
                            <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;" data-bs-dismiss="modal">Batal</button>
                            <a href="#" id="deleteSiswaLink" class="btn btn-danger" style="border-radius: 12px; padding: 10px 20px;">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- script untuk unduh daftar siswa -->
            <script>
                // Fungsi print
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('printButton').addEventListener('click', function() {
                        // Tambahkan header print
                        const printHeader = document.createElement('div');
                        printHeader.className = 'print-only print-header';
                        printHeader.innerHTML = `
            <h2>Data Siswa SMAGAEdu</h2>
            <p>Tanggal Cetak: ${new Date().toLocaleDateString()}</p>
        `;

                        // Tambahkan ke awal konten
                        const contentDiv = document.querySelector('.col-inti');
                        contentDiv.insertBefore(printHeader, contentDiv.firstChild);

                        // Simpan status filter saat ini
                        const visibleRows = {};
                        document.querySelectorAll('.siswa-item').forEach(row => {
                            const id = row.getAttribute('data-siswa-id');
                            visibleRows[id] = row.style.display !== 'none';
                        });

                        // Print
                        window.print();

                        // Hapus header print setelah mencetak
                        contentDiv.removeChild(printHeader);

                        // Kembalikan filter ke status sebelumnya
                        document.querySelectorAll('.siswa-item').forEach(row => {
                            const id = row.getAttribute('data-siswa-id');
                            if (!visibleRows[id]) {
                                row.style.display = 'none';
                                // Jika baris detail ada, sembunyikan juga
                                const detailId = row.getAttribute('data-siswa-id');
                                const detailRow = document.getElementById('details-' + detailId);
                                if (detailRow) {
                                    detailRow.parentElement.parentElement.style.display = 'none';
                                }
                            }
                        });
                    });
                });
            </script>

            <!-- script untuk delete siswa semua  -->
            <script>
                // Fitur hapus kelompok siswa
                document.addEventListener('DOMContentLoaded', function() {
                    const btnDeleteGroup = document.getElementById('btnDeleteGroup');
                    const btnDeleteSelected = document.getElementById('btnDeleteSelected');
                    const deleteCheckboxes = document.querySelectorAll('.delete-checkbox');
                    const checkAll = document.getElementById('checkAll');
                    const deleteGroupForm = document.getElementById('deleteGroupForm');

                    // Tampilkan/sembunyikan checkbox dan ubah tombol
                    btnDeleteGroup.addEventListener('click', function() {
                        // Toggle tampilan checkbox
                        deleteCheckboxes.forEach(checkbox => {
                            checkbox.style.display = checkbox.style.display === 'none' ? 'table-cell' : 'none';
                        });

                        // Toggle tampilan tombol
                        btnDeleteGroup.style.display = btnDeleteGroup.style.display === 'none' ? 'inline-block' : 'none';
                        btnDeleteSelected.style.display = btnDeleteSelected.style.display === 'none' ? 'inline-block' : 'none';

                        // Reset checkboxes
                        if (btnDeleteGroup.style.display !== 'none') {
                            document.querySelectorAll('.siswa-checkbox').forEach(cb => {
                                cb.checked = false;
                            });
                            if (checkAll) checkAll.checked = false;
                        }
                    });

                    // Batalkan mode hapus kelompok
                    btnDeleteSelected.addEventListener('dblclick', function() {
                        // Sembunyikan checkbox
                        deleteCheckboxes.forEach(checkbox => {
                            checkbox.style.display = 'none';
                        });

                        // Toggle tampilan tombol
                        btnDeleteGroup.style.display = 'inline-block';
                        btnDeleteSelected.style.display = 'none';

                        // Reset checkboxes
                        document.querySelectorAll('.siswa-checkbox').forEach(cb => {
                            cb.checked = false;
                        });
                        if (checkAll) checkAll.checked = false;
                    });

                    // Select/deselect all checkboxes
                    if (checkAll) {
                        checkAll.addEventListener('change', function() {
                            document.querySelectorAll('.siswa-checkbox').forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });
                    }

                    // Submit form untuk menghapus siswa terpilih
                    btnDeleteSelected.addEventListener('click', function() {
                        const selectedCheckboxes = document.querySelectorAll('.siswa-checkbox:checked');

                        if (selectedCheckboxes.length === 0) {
                            alert('Silakan pilih siswa yang akan dihapus');
                            return;
                        }

                        if (confirm('Anda yakin ingin menghapus ' + selectedCheckboxes.length + ' siswa yang dipilih?')) {
                            // Tambahkan checkboxes yang dipilih ke form
                            selectedCheckboxes.forEach(checkbox => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'delete_ids[]';
                                input.value = checkbox.value;
                                deleteGroupForm.appendChild(input);
                            });

                            // Submit form
                            deleteGroupForm.submit();
                        }
                    });
                });
            </script>

            <script>
                // Set data for delete modal
                document.addEventListener('DOMContentLoaded', function() {
                    const deleteModal = document.getElementById('deleteModal');
                    if (deleteModal) {
                        deleteModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget; // Button that triggered the modal
                            const id = button.getAttribute('data-id');
                            const name = button.getAttribute('data-name');

                            document.querySelectorAll('#deleteSiswaName').forEach(el => {
                                el.textContent = name;
                            });
                            document.getElementById('deleteSiswaLink').href = '?hapus=' + id;
                        });
                    }

                    // Student row expansion functionality
                    const studentRows = document.querySelectorAll('.siswa-item');
                    studentRows.forEach(row => {
                        row.addEventListener('click', function(e) {
                            // Don't expand if clicking on buttons
                            if (e.target.closest('.btn') || e.target.closest('a')) {
                                return;
                            }

                            const studentId = this.getAttribute('data-siswa-id');
                            const detailsElement = document.getElementById('details-' + studentId);

                            // Toggle the current clicked row
                            if (detailsElement) {
                                if (detailsElement.classList.contains('show')) {
                                    detailsElement.classList.remove('show');
                                    this.classList.remove('active');
                                } else {
                                    // First close all other open details
                                    document.querySelectorAll('.student-details.show').forEach(detail => {
                                        detail.classList.remove('show');
                                    });
                                    // First close all other open details
                                    document.querySelectorAll('.student-details.show').forEach(detail => {
                                        detail.classList.remove('show');
                                    });
                                    document.querySelectorAll('.siswa-item.active').forEach(item => {
                                        item.classList.remove('active');
                                    });

                                    // Open the clicked one
                                    detailsElement.classList.add('show');
                                    this.classList.add('active');
                                }
                            }
                        });
                    });
                });

                // Simple search functionality
                // Combined search and filter functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchSiswa');
                    const filterItems = document.querySelectorAll('.filter-tingkat');
                    const rows = document.querySelectorAll('.siswa-item');

                    // Function to apply both filter and search
                    function applyFilters() {
                        const searchTerm = searchInput.value.toLowerCase();
                        const activeTingkat = document.querySelector('.filter-tingkat.active').getAttribute('data-tingkat');

                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            const tingkatValue = row.getAttribute('data-tingkat');
                            const detailId = row.getAttribute('data-siswa-id');
                            const detailRow = document.getElementById('details-' + detailId);

                            const matchesSearch = text.includes(searchTerm);
                            const matchesTingkat = (activeTingkat === 'all' || tingkatValue === activeTingkat);

                            if (matchesSearch && matchesTingkat) {
                                row.style.display = '';
                                if (detailRow) {
                                    detailRow.parentElement.parentElement.style.display = '';
                                }
                            } else {
                                row.style.display = 'none';
                                if (detailRow) {
                                    detailRow.parentElement.parentElement.style.display = 'none';
                                }
                            }
                        });
                    }

                    // Apply filters on search input
                    searchInput.addEventListener('input', applyFilters);

                    // Apply filters on tingkat selection
                    filterItems.forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();

                            // Update active state in UI
                            filterItems.forEach(fi => fi.classList.remove('active'));
                            this.classList.add('active');

                            applyFilters();
                        });
                    });
                });
            </script>
        </div>
    </div>

    <!-- Modal Tambah Siswa -->
    <div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-labelledby="tambahSiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="tambahSiswaModalLabel">Tambah Siswa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body px-4">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-medium small mb-2">Username</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="username" name="username" required>
                            <small class="text-muted">Username akan digunakan untuk login</small>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium small mb-2">Password</label>
                            <input type="password" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="nama" class="form-label fw-medium small mb-2">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="nama" name="nama" required>
                        </div>
                        <div class="mb-4">
                            <label for="tingkat" class="form-label fw-medium small mb-2">Tingkat/Kelas</label>
                            <select class="form-select bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="tingkat" name="tingkat">
                                <option value="">Pilih Tingkat</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="trial_smp">Trial Class SMP</option>
                                <option value="trial_sma">Trial Class SMA</option>
                            </select>
                        </div>

                        <!-- TAMBAHAN BARU: Field Kategori -->
                        <div class="mb-4">
                            <label for="kategori" class="form-label fw-medium small mb-2">Kategori Siswa</label>
                            <select class="form-select bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="kategori" name="kategori" required>
                                <option value="SMA">SMA (Default)</option>
                                <option value="Pondok">Pondok</option>
                            </select>
                            <small class="text-muted">Pilih asal siswa</small>
                        </div>

                        <!-- TAMBAHAN BARU: Auto-assign ke kelas -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_assign_kelas" id="autoAssignKelas">
                                <label class="form-check-label" for="autoAssignKelas">
                                    Masukkan otomatis ke semua kelas sesuai tingkat
                                </label>
                                <div class="form-text small">
                                    Siswa akan dimasukkan ke semua kelas yang tersedia untuk tingkatnya
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="tahun_masuk" class="form-label fw-medium small mb-2">Tahun Masuk</label>
                            <input type="number" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="tahun_masuk" name="tahun_masuk" step="1" value="">
                        </div>
                        <div class="mb-4">
                            <label for="nis" class="form-label fw-medium small mb-2">NIS</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="nis" name="nis">
                        </div>
                    </div>
                    <div class="modal-footer btn-group border-0 pt-0">
                        <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_siswa" class="btn btn-primary"
                            style="border-radius: 12px; padding: 10px 20px;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 1s';
                    setTimeout(() => {
                        alert.remove();
                    }, 1000);
                });
            }, 5000);
        });
    </script>

    <!-- Modal Promote Siswa - Versi Sederhana -->
    <div class="modal fade" id="promoteSiswaModal" tabindex="-1" aria-labelledby="promoteSiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="promote_siswa.php" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h1 class="modal-title fs-5 fw-bold" id="promoteSiswaModalLabel">Naikkan Tingkat Siswa</h1>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="row g-4">
                            <!-- Form Kelas -->
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label small mb-2">Pilih Tingkat Asal</label>
                                    <select class="form-select form-select-lg shadow-sm" id="tingkatAsal" name="tingkat_asal" required>
                                        <option value="">Pilih Tingkat</option>
                                        <?php
                                        // Ambil daftar tingkat unik dari siswa
                                        $query_tingkat = "SELECT DISTINCT tingkat FROM siswa WHERE tingkat IS NOT NULL ORDER BY tingkat";
                                        $result_tingkat = mysqli_query($koneksi, $query_tingkat);
                                        while ($tingkat = mysqli_fetch_assoc($result_tingkat)) {
                                            echo '<option value="' . $tingkat['tingkat'] . '">' . $tingkat['tingkat'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="form-label fw-medium">Pilih Tingkat Tujuan</label>
                                    <select class="form-select bg-light" name="tingkat_tujuan" id="tingkatTujuan" style="border-radius: 12px; padding: 12px 15px;">
                                        <option value="">Pilih Tingkat Tujuan</option>
                                        <?php
                                        // Tingkat numerik
                                        for ($i = 7; $i <= 10; $i++) {
                                            echo '<option value="' . $i . '">' . $i . '</option>';
                                        }
                                        // Tingkat alfabet
                                        for ($c = 'E'; $c <= 'F'; $c++) {
                                            echo '<option value="' . $c . '">' . $c . '</option>';
                                        }
                                        // Tambahkan opsi "lulus"
                                        echo '<option value="lulus">Lulus (Alumni)</option>';
                                        ?>
                                    </select>
                                </div>

                                <!-- tahun kelulusan -->
                                <div id="tahunLulusContainer" style="display: none;" class="mb-3">
                                    <label class="form-label fw-medium">Tahun Kelulusan</label>
                                    <input type="number" name="tahun_lulus" class="form-control bg-light"
                                        style="border-radius: 12px; padding: 12px 15px;"
                                        value="<?php echo date('Y'); ?>" min="2000" max="2050">
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" name="hapus_dari_kelas_lama" id="hapusDariKelasLama">
                                    <label class="form-check-label" for="hapusDariKelasLama">
                                        Keluarkan Siswa Dari Kelas Lama
                                    </label>
                                    <div class="form-text small">
                                        Siswa akan keluarkan dari kelas lama, namun kelas lama akan tetap tersimpan.
                                    </div>
                                </div>
                            </div>

                            <!-- Daftar Siswa -->
                            <div class="col-12 col-md-6 bg-light border" style="border-radius: 15px;">
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label small mb-0">Daftar Siswa</label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllSiswa">Pilih Semua</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="deselectAllSiswa">Hapus Semua</button>
                                        </div>
                                    </div>

                                    <div id="daftarSiswa" class="overflow-auto" style="max-height: 300px;">
                                        <div class="text-center py-4 text-muted small">
                                            Pilih tingkat terlebih dahulu
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer btn-group border-0 px-0 pt-4">
                            <button type="button" class="btn border px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="promote_siswa" class="btn color-web text-white px-4">Naikkan Tingkat</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Siswa -->
    <div class="modal fade" id="editSiswaModal" tabindex="-1" aria-labelledby="editSiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editSiswaModalLabel">Edit Data Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_siswa.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="siswa_id" id="edit_siswa_id">
                    <div class="modal-body px-4">
                        <div class="mb-4 text-center">
                            <div class="position-relative d-inline-block">
                                <div style="width: 100px; height: 100px; overflow: hidden; border-radius: 50%;" class="border mb-3 mx-auto">
                                    <img id="preview_foto" src="assets/pp.png" class="w-100 h-100" style="object-fit: cover;">
                                </div>
                                <label for="foto_siswa" class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm" style="cursor: pointer;">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" id="foto_siswa" name="foto_siswa" class="d-none" accept="image/*">
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_username" class="form-label fw-medium small mb-2">Username</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_username" name="username" required>
                            <small class="text-muted">Username akan digunakan untuk login</small>
                        </div>
                        <div class="mb-4">
                            <label for="edit_password" class="form-label fw-medium small mb-2">Password</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_nama" class="form-label fw-medium small mb-2">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_nama" name="nama" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_tingkat" class="form-label fw-medium small mb-2">Tingkat/Kelas</label>
                            <select class="form-select bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_tingkat" name="tingkat">
                                <option value="">Pilih Tingkat</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="trial_smp">Trial Class SMP</option>
                                <option value="trial_sma">Trial Class SMA</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit_kategori" class="form-label fw-medium small mb-2">Kategori Siswa</label>
                            <select class="form-select bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_kategori" name="kategori" required>
                                <option value="SMA">SMA</option>
                                <option value="Pondok">Pondok</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit_nis" class="form-label fw-medium small mb-2">NIS</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_nis" name="nis">
                        </div>
                        <div class="mb-4">
                            <label for="edit_tahun_masuk" class="form-label fw-medium small mb-2">Tahun Masuk</label>
                            <input type="number" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_tahun_masuk" name="tahun_masuk" step="1" value="">
                        </div>
                        <div class="mb-4">
                            <label for="edit_no_hp" class="form-label fw-medium small mb-2">No. HP</label>
                            <input type="text" class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_no_hp" name="no_hp">
                        </div>
                        <div class="mb-4">
                            <label for="edit_alamat" class="form-label fw-medium small mb-2">Alamat</label>
                            <textarea class="form-control bg-light border" style="border-radius: 12px; padding: 12px 15px;"
                                id="edit_alamat" name="alamat" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer btn-group border-0 pt-0">
                        <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_siswa" class="btn btn-primary"
                            style="border-radius: 12px; padding: 10px 20px;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script untuk modal edit siswa
        // Script untuk modal edit siswa
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editSiswaModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget; // Button yang memicu modal
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-name');
                    const username = button.getAttribute('data-username');
                    const password = button.getAttribute('data-password');
                    const tingkat = button.getAttribute('data-tingkat');
                    const nis = button.getAttribute('data-nis');
                    const tahunMasuk = button.getAttribute('data-tahun-masuk');
                    const noHp = button.getAttribute('data-no-hp');
                    const alamat = button.getAttribute('data-alamat');

                    // Isi form dengan data siswa
                    document.getElementById('edit_siswa_id').value = id;
                    document.getElementById('edit_nama').value = nama;
                    document.getElementById('edit_username').value = username;
                    document.getElementById('edit_password').value = password;
                    document.getElementById('edit_nis').value = nis || '';
                    document.getElementById('edit_tahun_masuk').value = tahunMasuk || ''; // Change this from edit_tahun_masuk to match the id
                    document.getElementById('edit_no_hp').value = noHp || '';
                    document.getElementById('edit_alamat').value = alamat || '';

                    // Set tingkat yang dipilih
                    if (tingkat) {
                        const tingkatSelect = document.getElementById('edit_tingkat');
                        for (let i = 0; i < tingkatSelect.options.length; i++) {
                            if (tingkatSelect.options[i].value === tingkat) {
                                tingkatSelect.options[i].selected = true;
                                break;
                            }
                        }
                    }

                    // TAMBAHAN BARU: Set kategori yang dipilih
                    const kategori = button.getAttribute('data-kategori');
                    if (kategori) {
                        const kategoriSelect = document.getElementById('edit_kategori');
                        for (let i = 0; i < kategoriSelect.options.length; i++) {
                            if (kategoriSelect.options[i].value === kategori) {
                                kategoriSelect.options[i].selected = true;
                                break;
                            }
                        }
                    }

                    // Preview foto saat memilih file baru
                    const fotoInput = document.getElementById('foto_siswa');
                    const previewFoto = document.getElementById('preview_foto');

                    fotoInput.addEventListener('change', function() {
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewFoto.src = e.target.result;
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                });
            }
        });
    </script>

    <script>
        // Script untuk memuat daftar siswa ketika tingkat dipilih
        document.getElementById('tingkatAsal').addEventListener('change', function() {
            var tingkat = this.value;


            if (tingkat) {
                fetch('get_siswa_by_tingkat.php?tingkat=' + tingkat)
                    .then(response => response.json())
                    .then(data => {
                        var daftarSiswa = document.getElementById('daftarSiswa');
                        if (data.length > 0) {
                            var html = '<ul class="list-group list-group-flush">';
                            data.forEach(siswa => {
                                html += `
                    <li class="list-group-item bg-transparent d-flex align-items-center py-2">
                        <div class="form-check">
                            <input class="form-check-input siswa-checkbox" type="checkbox" name="siswa_ids[]" value="${siswa.id}" id="siswa${siswa.id}">
                            <label class="form-check-label" for="siswa${siswa.id}">
                                ${siswa.nama}
                            </label>
                        </div>
                    </li>`;
                            });
                            html += '</ul>';
                            daftarSiswa.innerHTML = html;
                        } else {
                            daftarSiswa.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-exclamation-circle"></i> Tidak ada siswa di tingkat ini</div>';
                        }
                    });
            } else {
                document.getElementById('daftarSiswa').innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-people"></i> Pilih tingkat terlebih dahulu</div>';
            }
        });

        document.getElementById('tingkatTujuan').addEventListener('change', function() {
            if (this.value === 'lulus') {
                document.getElementById('tahunLulusContainer').style.display = 'block';
            } else {
                document.getElementById('tahunLulusContainer').style.display = 'none';
            }
        });

        // Select/deselect all siswa
        document.getElementById('selectAllSiswa').addEventListener('click', function() {
            document.querySelectorAll('.siswa-checkbox').forEach(checkbox => checkbox.checked = true);
        });

        document.getElementById('deselectAllSiswa').addEventListener('click', function() {
            document.querySelectorAll('.siswa-checkbox').forEach(checkbox => checkbox.checked = false);
        });
    </script>

</body>

</html>