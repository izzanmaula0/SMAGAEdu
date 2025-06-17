<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Query untuk mengambil data alumni
$query_alumni = "SELECT a.*, s.username FROM alumni a 
                LEFT JOIN siswa s ON a.siswa_id = s.id 
                ORDER BY a.tahun_lulus DESC, a.nama ASC";
$result_alumni = mysqli_query($koneksi, $query_alumni);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Head content sama seperti manajemen_siswa.php -->
    <title>Data Alumni - SMAGAEdu</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

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

        .col-inti {
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

    <!-- Main Content -->
    <div class="col col-inti p-0 p-md-3">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mt-2 mb-4">
                <h2 class="mb-0 fw-bold">Data Alumni</h2>
            </div>

            <!-- Alumni Table -->
            <div class="card animate-fade-in shadow-none border" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <!-- Search bar iOS style -->
                    <div class="px-4 pt-4 pb-2">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchAlumni" class="form-control bg-light border-0"
                                placeholder="Cari alumni..." style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <div class="table-responsive px-2">
                        <table class="table table-borderless align-middle">
                            <thead class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Nama Lengkap</th>
                                    <th style="width: 15%">NIS</th>
                                    <th style="width: 15%">Tahun Masuk</th>
                                    <th style="width: 15%">Tahun Lulus</th>
                                    <th style="width: 20%">Kontak</th>
                                </tr>
                            </thead>
                            <tbody id="alumniTableBody">
                                <?php if (mysqli_num_rows($result_alumni) > 0): ?>
                                    <?php $no = 1;
                                    while ($alumni = mysqli_fetch_assoc($result_alumni)): ?>
                                        <tr class="alumni-item">
                                            <td><?php echo $no++; ?></td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($alumni['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['nis'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['tahun_masuk'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['tahun_lulus'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['no_hp'] ?: '-'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-4">
                                                <i class="bi bi-mortarboard text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">Belum ada data alumni</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script pencarian alumni
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchAlumni');
            const rows = document.querySelectorAll('.alumni-item');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>