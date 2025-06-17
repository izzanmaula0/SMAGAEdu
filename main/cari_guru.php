<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Cek apakah ada parameter username
if(isset($_GET['username'])) {
    $username = mysqli_real_escape_string($koneksi, $_GET['username']);
} else {
    // Jika tidak ada parameter, gunakan username dari session
    $username = $_SESSION['userid'];
}

// Ambil userid dari session
$userid = $_SESSION['userid'];

// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

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
    <title>Cari - SMAGAEdu</title>
</head>
<style>
        body{ 
            font-family: merriweather;
        }
        .color-web {
            background-color: rgb(218, 119, 86);
        }
        .search-results {
            max-height: 400px;
            overflow-y: auto;
        }

        .list-group-item {
            transition: background-color 0.2s;
            text-align: left;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }
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

</style>
<body>


    <!-- Navbar Mobile -->
    <nav class="navbar navbar-dark d-md-none color-web fixed-top">
        <div class="container-fluid">
            <!-- Logo dan Nama -->
            <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
                <img src="assets/logo_white.png" alt="" width="30px" class="logo_putih">
            <div>
                    <h1 class="p-0 m-0" style="font-size: 20px;">Cari</h1>
                    <p class="p-0 m-0 d-none d-md-block" style="font-size: 12px;">LMS</p>
                </div>
            </a>
            
            <!-- Tombol Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
                <span class="navbar-toggler-icon" style="color:white"></span>
            </button>
            
            <!-- Offcanvas/Sidebar Mobile -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" style="font-size: 30px;">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body d-flex justify-content-between flex-column">
                    <div class="d-flex flex-column gap-2">
                        <!-- Menu Beranda -->
                        <a href="beranda_guru.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center rounded  p-2">
                                <img src="assets/beranda_outfill.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0">Beranda</p>
                            </div>
                        </a>
                        
                        <!-- Menu Cari -->
                        <a href="cari_guru.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center color-web rounded p-2">
                                <img src="assets/pencarian.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0 text-white">Cari</p>
                            </div>
                        </a>
                        
                        <!-- Menu Ujian -->
                        <a href="ujian_guru.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center rounded p-2">
                                <img src="assets/ujian_outfill.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0">Ujian</p>
                            </div>
                        </a>
                        
                        <!-- Menu Profil -->
                        <a href="profil_guru.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center rounded p-2">
                                <img src="assets/profil_outfill.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0">Profil</p>
                            </div>
                        </a>
                        
                        <!-- Menu AI -->
                        <a href="ai_guru.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center rounded p-2">
                                <img src="assets/ai.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0">SMAGA AI</p>
                            </div>
                        </a>
                        
                        <!-- Menu Bantuan -->
                        <a href="bantuan.php" class="text-decoration-none text-black">
                            <div class="d-flex align-items-center rounded p-2">
                                <img src="assets/bantuan_outfill.png" alt="" width="50px" class="pe-4">
                                <p class="p-0 m-0">Bantuan</p>
                            </div>
                        </a>
                    </div>
                    
                <!-- Profile Dropdown -->
                <div class="mt-3 dropup"> <!-- Tambahkan class dropdown di sini -->
                    <button class="btn d-flex align-items-center gap-3 p-2 rounded-3 border w-100" 
                            style="background-color: #F8F8F7;" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                            <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/'.$guru['foto_profil'] : 'assets/pp.png'; ?>"  width="30px" class="rounded-circle" style="background-color: white;">
                            <p class="p-0 m-0 text-truncate" style="font-size: 12px;"><?php echo $guru['namaLengkap']; ?></p>
                    </button>
                    <ul class="dropdown-menu w-100" style="font-size: 12px;"> <!-- Tambahkan w-100 agar lebar sama -->
                        <li><a class="dropdown-item" href="#">Pengaturan</a></li>
                        <li><a class="dropdown-item" href="logout.php" style="color: red;">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    

     <!-- row col untuk halaman utama -->
     <div class="container-fluid">
        <div class="row">
            <div class="col-3 col-md-2 vh-100 p-4 shadow-sm menu-samping" style="background-color:rgb(238, 236, 226)">
                <style>
                    .menu-samping {
                        position: fixed;
                        width: 13rem;
                        z-index: 1000;
                    }
                </style>
                <div class="row gap-0">
                    <div class="ps-3 mb-3">
                        <a href="beranda_guru.php" style="text-decoration: none; color: black;" class="d-flex align-items-center gap-2">
                            <img src="assets/smagaedu.png" alt="" width="30px">
                            <div>
                                <h1 class="display-5  p-0 m-0" style="font-size: 20px; text-decoration: none;">SMAGAEdu</h1>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">LMS</p>
                            </div>
                        </a>
                    </div>  
                    <div class="col">
                        <a href="beranda_guru.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded p-2" style="">
                            <img src="assets/beranda_outfill.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">Beranda</p>
                        </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="cari_guru.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded bg-white shadow-sm p-2" style="">
                            <img src="assets/pencarian.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">Cari</p>
                        </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="ujian_guru.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded p-2" style="">
                            <img src="assets/ujian_outfill.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">Ujian</p>
                        </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="profil_guru.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded p-2" style="">
                            <img src="assets/profil_outfill.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">Profil</p>
                        </div>
                        </a>
                    </div>
                </div>
                <div class="row gap-0" style="margin-bottom: 15rem;">
                    <div class="col">
                        <a href="ai_guru.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded p-2" style="">
                            <img src="assets/ai.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">SMAGA AI</p>
                        </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="bantuan.php" class="text-decoration-none text-black">
                        <div class="d-flex align-items-center rounded p-2" style="">
                            <img src="assets/bantuan_outfill.png" alt="" width="50px" class="pe-4">
                            <p class="p-0 m-0">Bantuan</p>
                        </div>
                        </a>
                    </div>
                </div>
                <div class="row dropdown">
                    <div class="btn d-flex align-items-center gap-3 p-2 rounded-3 border dropdown-toggle" style="background-color: #F8F8F7;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/'.$guru['foto_profil'] : 'assets/pp.png'; ?>"  width="30px" class="rounded-circle" style="background-color: white;">
                        <p class="p-0 m-0 text-truncate" style="font-size: 12px;"><?php echo $guru['namaLengkap']; ?></p>
                    </div>
                    <!-- dropdown menu option with animation -->
                    <ul class="dropdown-menu animate slideIn" style="font-size: 12px;">
                        <style>
                        .animate {
                            animation-duration: 0.2s;
                            animation-fill-mode: both;
                        }
                        
                        @keyframes slideIn {
                            from {
                                transform: translateY(-10px);
                                opacity: 0;
                            }
                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }

                        .slideIn {
                            animation-name: slideIn;
                        }

                        .dropdown-item {
                            padding: 8px 16px;
                            transition: background-color 0.2s;
                        }

                        .dropdown-item:hover {
                            background-color: #f8f9fa;
                        }
                        </style>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>

            <!-- ini isi kontennya -->
            <div class="col p-4 col-utama">
                <style>
                .col-utama {
                    margin-left: 0;
                }
                @media (min-width: 768px) {
                    .col-utama {
                        margin-left: 13rem;
                    }
                }

                /* Animasi untuk hasil pencarian */
                .search-results {
                    max-height: 400px;
                    overflow-y: auto;
                    transition: all 0.3s ease-in-out;
                }

                .search-result-item {
                    opacity: 0;
                    transform: translateY(-20px);
                    animation: slideIn 0.3s ease forwards;
                }

                @keyframes slideIn {
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                /* Memberikan delay untuk setiap item */
                .search-result-item:nth-child(1) { animation-delay: 0.1s; }
                .search-result-item:nth-child(2) { animation-delay: 0.2s; }
                .search-result-item:nth-child(3) { animation-delay: 0.3s; }
                .search-result-item:nth-child(4) { animation-delay: 0.4s; }
                .search-result-item:nth-child(5) { animation-delay: 0.5s; }
                /* dan seterusnya... */

                /* Styling untuk card hasil pencarian */
                .list-group-item {
                    border: none;
                    margin-bottom: 0.5rem;
                    background: #f8f9fa;
                    border-radius: 10px !important;
                    transition: all 0.2s ease;
                }

                .list-group-item:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                </style>
                    <div class="row text-center justify-content-center align-items-center" style="margin-top: 2rem;">
                        <div class="col-12 col-md-8 col-lg-6"> <!-- Tambahkan pembungkus col untuk mengontrol lebar -->
                            <div class="d-flex flex-column align-items-center">
                                <h3 style="font-weight: bold;" class="mb-2">Halo, <?php 
                                    // Kode PHP tetap sama
                                    echo $guru['namaSebutan'] ? htmlspecialchars($guru['namaSebutan']) : $_SESSION['nama']; 
                                ?></h3>            
                                <p class="text-muted mb-4">Siapa yang ingin Anda cari hari ini?</p>
                                
                                <div class="card-footer p-3 rounded-4 shadow-sm w-100" style="background-color: #EEECE2;">
                                    <div class="input-group">
                                        <input type="text" id="user-input" class="form-control border-0 py-2" 
                                            style="background-color: transparent;" placeholder="Cari nama siswa atau guru...">
                                        <button id="send-button" class="btn color-web bi-search rounded text-white px-4"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    </div>


<script>
const userInput = document.getElementById('user-input');
const searchResults = document.createElement('div');
searchResults.className = 'search-results m-0 p-0 mt-3';
document.querySelector('.card-footer').insertAdjacentElement('afterend', searchResults);

let debounceTimer;

userInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const keyword = this.value.trim();
        if(keyword.length > 0) {
            fetch(`cari_user.php?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    displayResults(data);
                });
        } else {
            searchResults.innerHTML = '';
        }
    }, 300); // Debounce 300ms
});

function displayResults(data) {
    let html = '<div class="container px-4">';

    // Menampilkan hasil siswa
    if(data.siswa.length > 0) {
        html += '<h5 class="mt-4 mb-3 text-start fw-bold">Siswa</h5>';
        html += '<div class="list-group">';
        data.siswa.forEach(siswa => {
            // Tentukan tingkat/fase berdasarkan data tingkat
            let tingkatLabel = '';
            if(siswa.tingkat) {
                if(['7','8','9'].includes(siswa.tingkat)) {
                    tingkatLabel = `Kelas ${siswa.tingkat}`;
                } else {
                    tingkatLabel = `Fase ${siswa.tingkat}`;
                }
            }

            html += `
                <div class="list-group-item search-result-item d-flex justify-content-between align-items-start p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-truncate">
                            <strong>${siswa.nama}</strong>
                            <div class="text-muted small">${tingkatLabel || 'Kelas belum ditentukan'}</div>
                        </div>
                    </div>
                    <a href="view_siswa.php?username=${siswa.username}" 
                       class="btn btn-sm color-web text-white px-3 d-flex align-items-center gap-2">
                       Lihat
                    </a>
                </div>
            `;
        });
        html += '</div>';
    }
    
    // Menampilkan hasil guru
    if(data.guru.length > 0) {
        html += '<h5 class="mt-4 mb-3 text-start fw-bold">Guru</h5>';
        html += '<div class="list-group">';
        data.guru.forEach(guru => {
            html += `
                <div class="list-group-item search-result-item d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-truncate">
                            <strong>${guru.namaLengkap}</strong>
                            ${guru.jabatan ? `<div class="text-muted small">${guru.jabatan}</div>` : ''}
                        </div>
                    </div>
                    <a href="profil_guru.php?username=${guru.username}" 
                       class="btn btn-sm color-web text-white px-3 mx-3 d-flex align-items-center gap-2">
                       Lihat
                    </a>
                </div>
            `;
        });
        html += '</div>';
    }
        
    if(data.guru.length === 0 && data.siswa.length === 0) {
        html += `
            <div class="text-center py-5 search-result-item">
                <i class="bi bi-search display-1 mb-3 text-muted"></i>
                <p class="text-muted">Tidak ada hasil yang ditemukan</p>
            </div>
        `;
    }
    
    html += '</div>';
    searchResults.innerHTML = html;
}
</script>

</body>
</html>