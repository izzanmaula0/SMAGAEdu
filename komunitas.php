<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header('Location: login.php');
    exit;
}

$siswa_id = $_SESSION['userid'];
$query = "SELECT * FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $siswa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <title>Komunitas - SMAGAEdu</title>
    <style>
        body {
            font-family: 'Merriweather', serif;
        }
        .accent-color {
            background-color: rgb(218, 119, 86);
            color: white;
        }
        .group-card:hover {
            background-color: rgba(218, 119, 86, 0.1);
        }
        .nav-link.active {
            color: rgb(218, 119, 86) !important;
            border-bottom: 2px solid rgb(218, 119, 86);
        }
        .sidebar-group {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .sidebar-group:hover {
            background-color: rgba(218, 119, 86, 0.1);
        }
        .post-card {
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        @media screen and (max-width: 992px) {
            .col-utama {
                margin-left: 0;
                padding-top: 56px; /* Tinggi navbar mobile */
            } 
            body {
                padding-top: 0;
            }
        }

        @media screen and (min-width: 993px) {
            .col-utama {
                padding-left: 14rem;
            }
            body {
                padding-top: 0;
            }
        }

        .mobile-nav {
            height: 56px;
            background: white;
            z-index: 1030;
        }
    </style>
</head>
<body>

            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar_siswa.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav siswa.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>

            <?php include 'includes/styles.php'; ?>



<style>
    .menu-samping {
        padding-top: 40px !important;
    }
</style>



<!-- Mobile Navigation -->
<div class="d-lg-none fixed-top mobile-nav border-bottom">
    <div class="container">
        <nav class="nav nav-pills nav-fill h-100">
            <a class="nav-link active d-flex align-items-center justify-content-center" href="#grupmu">Grup Kamu</a>
            <a class="nav-link d-flex align-items-center justify-content-center" href="#postingan">Postingan</a>
            <a class="nav-link d-flex align-items-center justify-content-center" href="#temukan">Temukan</a>
        </nav>
    </div>
</div>

<div class="container-fluid col-utama">
    <div class="row g-3 pt-3">
        <!-- Left Sidebar - Groups List -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card border-1" style="min-height: calc(98vh - 2rem);">
            <div class="card-body">
                <h5 class="card-title mb-3 fw-bold">Komunitasmu</h5>
                <div class="input-group mb-3">
                <input type="text" class="form-control form-control-sm" placeholder="Cari komunitas..." aria-label="Cari komunitas">
                <button class="btn btn-outline-secondary btn-sm" type="button">
                    <i class="bi bi-search"></i>
                </button>
                </div>
                <div id="groupsList">
                <!-- Groups will be listed here -->
                <div class="sidebar-group p-2 rounded-3 mb-2">
                    <div class="d-flex align-items-center gap-3 p-2">
                    <img src="assets/pp.png" class="rounded-circle" width="45" height="45" alt="OSIS SMAGA">
                    <div class="gap-0">
                        <h6 class="mb-0 fw-semibold p-0 m-0" style="font-size: 12px;">OSIS SMAGA</h6>
                        <small class="text-muted" style="font-size: 10px;">3.2k anggota</small>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Main Content - Posts -->
        <div class="col-lg-6">


            <!-- Posts Feed -->
            <div class="posts-container">
                <div class="card shadow-none border mb-3 post-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="assets/pp.png" class="rounded-circle" width="45" height="45" alt="John Doe">
                            <div>
                                <h6 class="mb-0 fw-semibold">John Doe</h6>
                                <small class="text-muted">2 jam yang lalu · OSIS SMAGA</small>
                            </div>
                        </div>
                        <p class="mb-3">Ini adalah contoh postingan komunitas...</p>
                        <img src="assets/post-image.jpg" class="img-fluid rounded mb-3" alt="Post image">
                        <div class="d-flex gap-3">
                            <button class="btn btn-light flex-grow-1 border"><i class="bi bi-hand-thumbs-up me-2"></i>Suka</button>
                            <button class="btn btn-light flex-grow-1 border"><i class="bi bi-chat me-2"></i>Komentar</button>
                            <button class="btn btn-light flex-grow-1 border"><i class="bi bi-share me-2"></i>Bagikan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Group Info -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card border-1 mb-3">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold">Tentang Grup</h5>
                    <p class="card-text text-muted" style="font-size: 12px;">Tidak ada grup yang dipilih</p>
                    
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-globe me-2"></i>
                            <div>
                                <h6 class="mb-0 fw-semibold" style="font-size: 14px;">Status</h6>
                                <small class="text-muted">Grup Publik</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <i class="bi bi-eye me-2"></i>
                            <div>
                                <h6 class="mb-0 fw-semibold" style="font-size: 14px;">Visibilitas</h6>
                                <small class="text-muted">Dapat ditemukan oleh semua orang</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card border-1 mb-3">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold">Media Terbaru</h5>
                    <div class="row g-2 mt-2">
                        <div class="col-4">
                            <img src="assets/bg.jpg" class="img-fluid rounded" alt="Media thumbnail">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>