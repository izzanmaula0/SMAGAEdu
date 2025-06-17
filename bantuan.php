<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi - SMAGAEdu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Merriweather', serif;
            background-color: white;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        /* Header Styles */
        .header {
            padding: 1rem 2rem;
            background-color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Hamburger Menu Button */
        .hamburger-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: rgb(218, 119, 86);
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hamburger-btn:hover {
            background-color: rgba(218, 119, 86, 0.1);
            border-radius: 6px;
        }

        /* Logo Container */
        .logo-container {}

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: rgb(218, 119, 86);
            text-decoration: none;
        }

        .search-container {
            position: relative;
            /* max-width: 400px; */
            flex: 1;
            margin: 0 2rem;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            font-family: 'Merriweather', serif;
            font-size: 0.9rem;
        }

        .search-input:focus {
            outline: none;
            border-color: rgb(218, 119, 86);
            box-shadow: 0 0 0 3px rgba(218, 119, 86, 0.1);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e4e8;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            z-index: 1001;
        }

        .search-result-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .search-result-item:hover {
            background-color: white;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        /* Container */
        .container {
            display: flex;
            margin-top: 80px;
            min-height: calc(100vh - 80px);
        }

        /* Sidebar Styles */
        /* Sidebar Styles - Default Desktop */
        .sidebar {
            width: 280px;
            background-color: white;
            border-right: 1px solid #e1e4e8;
            padding: 2rem 0;
            padding-top: 0;
            position: fixed;
            left: 0;
            top: 80px;
            bottom: 0;
            overflow-y: auto;
        }

        /* Smooth transitions for better UX */
        .hamburger-btn i {
            transition: transform 0.2s ease;
        }

        .hamburger-btn.active i {
            transform: rotate(90deg);
        }

        /* Prevent text selection on mobile menu */
        .nav-title,
        .hamburger-btn {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .sidebar.show {
            transform: translateX(100%);
        }

        .nav-section {
            margin-bottom: 1rem;
        }

        .nav-title {
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            color: rgb(218, 119, 86);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: 'Merriweather', serif;
            font-size: 1rem;
        }

        .nav-title:hover {
            background-color: white;
        }

        .nav-arrow {
            transition: transform 0.2s;
            font-size: 0.8rem;
        }

        .nav-section.active .nav-arrow {
            transform: rotate(90deg);
        }

        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-section.active .nav-submenu {
            max-height: 500px;
        }

        .nav-submenu a {
            display: block;
            padding: 0.5rem 2.5rem;
            color: #586069;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-submenu a:hover,
        .nav-submenu a.active {
            color: rgb(218, 119, 86);
            background-color: rgba(218, 119, 86, 0.1);
            border-left-color: rgb(218, 119, 86);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 150px;
            padding: 2rem 3rem;
            max-width: calc(100vw - 280px);
        }

        .content-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .content-section.active {
            display: block;
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

        .content-section h1 {
            color: rgb(218, 119, 86);
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .content-section h2 {
            margin: 2rem 0 1rem 0;
            font-size: 1.8rem;
            border-bottom: 1px solid #e1e4e8;
            padding-bottom: 0.5rem;
        }

        .content-section h3 {
            margin: 1.5rem 0 0.75rem 0;
            font-size: 1.3rem;
        }

        .content-section p {
            margin-bottom: 1rem;
        }

        .content-section ul,
        .content-section ol {
            margin: 1rem 0 1rem 2rem;
        }

        .content-section li {
            margin-bottom: 0.5rem;
        }

        .highlight-box {
            background: rgba(218, 119, 86, 0.1);
            border-left: 4px solid rgb(218, 119, 86);
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 0 6px 6px 0;
        }

        .code-block {
            background: #f6f8fa;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }

        .sidebar-overlay {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            display: none;
        }

        /* Responsive Mobile */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                justify-content: flex-start;
                gap: 1rem;
            }

            .search-container {
                margin: 0 1rem;
            }

            /* Logo ke tengah di mobile */
            .logo-container {
                justify-content: center;
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
            }

            /* Mobile Sidebar - Override desktop styles */
            .sidebar {
                width: 280px;
                position: fixed;
                left: 0;
                top: 80px;
                bottom: 0;
                background-color: white;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 999;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
                border-right: none;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            /* Show overlay on mobile */
            .sidebar-overlay {
                display: block;
            }

            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 1rem;
            }

            .container {
                flex-direction: column;
            }

            .gambar-siswa-content {
                object-fit: cover;
                object-position: -150px 0px !important;
            }
        }

        .gambar-siswa-content {
            max-width: 100%;
            width: 100%;
            border-radius: 10px;
            object-fit: cover;
            object-position: 0px -30px;
            height: 250px;
        }

        /* Desktop mode - ensure sidebar overlay is hidden */
        @media (min-width: 769px) {
            .sidebar-overlay {
                display: none !important;
            }

            .hamburger-btn {
                display: none !important;
            }

            .sidebar {
                transform: translateX(0) !important;
            }
        }

        p {
            color: black;
        }

        h3 {
            color: black !important;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header shadow-sm border-bottom-1">
        <!-- Hamburger Menu Button (hanya tampil di mobile) -->
        <button class="hamburger-btn d-md-none" id="sidebarToggle">
            <i class="ti ti-menu-2"></i>
        </button>

        <!-- Logo Container -->
        <div class="logo-container">
            <img src="assets/smagaedu.png" style="width:3rem" alt="">
        </div>

        <!-- Search Container (hidden di mobile, tampil di desktop) -->
        <div class="search-container d-none d-md-block">
            <input type="text" class="search-input" placeholder="Cari dokumentasi..." id="searchInput">
            <div class="search-results" id="searchResults"></div>
        </div>

        <!-- Version -->
        <div style="color: rgb(218, 119, 86); font-weight: 600;" class="d-none d-md-block">v1.0</div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar pt-3" id="sidebar">
            <div class="nav-section" data-section="siswa">
                <button class="nav-title">
                    Saya adalah Siswa
                    <span class="nav-arrow">▶</span>
                </button>
                <div class="nav-submenu">
                    <a href="#" data-content="siswa-mulai">Mulai Menggunakan LMS</a>
                    <a href="#" data-content="siswa-kelas">Mengakses Kelas</a>
                    <a href="#" data-content="siswa-tugas">Mengerjakan Tugas</a>
                    <a href="#" data-content="siswa-quiz">Mengikuti Quiz</a>
                    <a href="#" data-content="siswa-nilai">Melihat Nilai</a>
                    <a href="#" data-content="siswa-diskusi">Forum Diskusi</a>
                    <a href="#" data-content="siswa-profil">Mengelola Profil</a>
                </div>
            </div>

            <div class="nav-section" data-section="guru">
                <button class="nav-title">
                    Saya adalah Guru
                    <span class="nav-arrow">▶</span>
                </button>
                <div class="nav-submenu">
                    <a href="#" data-content="guru-mulai">Memulai sebagai Guru</a>
                    <a href="#" data-content="guru-kelas">Membuat & Mengelola Kelas</a>
                    <a href="#" data-content="guru-materi">Upload Materi</a>
                    <a href="#" data-content="guru-tugas">Membuat Tugas</a>
                    <a href="#" data-content="guru-quiz">Membuat Quiz</a>
                    <a href="#" data-content="guru-penilaian">Sistem Penilaian</a>
                    <a href="#" data-content="guru-laporan">Laporan & Analytics</a>
                </div>
            </div>

            <div class="nav-section" data-section="admin">
                <button class="nav-title">
                    Saya adalah Admin
                    <span class="nav-arrow">▶</span>
                </button>
                <div class="nav-submenu">
                    <a href="#" data-content="admin-dashboard">Dashboard Admin</a>
                    <a href="#" data-content="admin-user">Manajemen User</a>
                    <a href="#" data-content="admin-kelas">Manajemen Kelas</a>
                    <a href="#" data-content="admin-sistem">Pengaturan Sistem</a>
                    <a href="#" data-content="admin-backup">Backup & Restore</a>
                    <a href="#" data-content="admin-laporan">Laporan Sistem</a>
                </div>
            </div>

            <div class="nav-section" data-section="developer">
                <button class="nav-title">
                    Saya adalah Developer
                    <span class="nav-arrow">▶</span>
                </button>
                <div class="nav-submenu">
                    <a href="#" data-content="dev-instalasi">Instalasi & Setup</a>
                    <a href="#" data-content="dev-api">API Documentation</a>
                    <a href="#" data-content="dev-database">Database Schema</a>
                    <a href="#" data-content="dev-frontend">Frontend Guide</a>
                    <a href="#" data-content="dev-backend">Backend Guide</a>
                    <a href="#" data-content="dev-deployment">Deployment</a>
                    <a href="#" data-content="dev-troubleshooting">Troubleshooting</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Siswa Content -->
            <div class="content-section active" id="siswa-mulai">
                <img src="feature/welcome-saga.png" class="gambar-siswa-content border mb-3" alt="">
                <h1>Mulai Menggunakan SMAGAEdu sebagai Siswa</h1>
                <p>Selamat datang di dokumentasi SMAGAEdu LMS. Halaman ini kamu akan di kami pandu langkah awal dan fundamental dalam menggunakan SMAGAEdu</p>

                <div class="alert border bg-light" style="border-radius: 15px;">
                    <div class="d-flex">
                        <i class="ti ti-info-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                        <div>
                            <p class="fw- p-0 m-0 fw-bold" style="font-size: 14px;">Apa saja yang saya pelajari di halaman ini?</p>
                            <ul style="font-size: 12px;" class="ms-0 ps-2">
                                <li class="ms-0 ps-0">Masuk akun SMAGAEdu</li>
                                <li class="ms-0 ps-0">Mengenal sekilas halaman beranda</li>
                                <li class="ms-0 ps-0">Mengenal sekilas halaman ujian</li>
                                <li class="ms-0 ps-0">Mengenal sekilas halaman profil</li>
                                <li class="ms-0 ps-0">Mengenal sekilas halaman SAGAAI</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <h2>Langkah Pertama</h2>
                <div class="alert border bg-light" style="border-radius: 15px;">
                    <div class="d-flex">
                        <i class="ti ti-exclamation-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                        <div>
                            <p style="font-size:14px" class="fw-bold p-0 m-0 text-black">Pastikan kamu sudah mempunyai akun SMAGAEdu</p>
                            <p style="font-size:12px" class="text-black p-0 m-0">Sebelum memulai, pastikan kamu sudah mempunyai akun SMAGAEdu diberikan oleh Tim IT atau TU.</p>
                        </div>
                    </div>
                </div>

                <h3>1. Login ke SMAGAEdu</h3>
                <ul>
                    <li>Buka browser
                        <p class="p-0 m-0" style="font-size: 12px;">Kamu bebas menggunakan Google Chrome, Mozilla Firefox, Safari, atau apapun peramban favoritmu</p>
                    </li>

                    <li>Tuliskan di kotak alamat atau url <a href="smagaedu.com">smagaedu.com</a>
                        <p class="p-0 m-0" style="font-size: 12px;">Pada bagian ini, sebenarnya kamu dengan bebas menulis langsung melalui kotak pencarian browser (Address Box) ataupun mencarinya di mesin pencarian</p>
                        <div class="d-flex flex-column flex-md-row justify-content-between p-3 border rounded-4 mt-3 gap-3">
                            <div class="text-start">
                                <p class="p-0 m-0 fw-md-bold" style="font-size: 12px;">Pencarian melalui address box</p>
                                <img src="panduan/urlbar.png" alt="" class="rounded-4 img-fluid" style="max-width: 100%; width: 300px;">
                            </div>
                            <div class="text-center text-md-start">
                                <p class="p-0 m-0 fw-md-bold" style="font-size: 12px;">Pencarian melalui Google Search</p>
                                <img src="panduan/googlebar.png" alt="" class="rounded-4 img-fluid" style="max-width: 100%; width: 300px;">
                            </div>
                        </div>
                    </li>

                    <li>Masukkan username dan password yang diberikan
                        <p class="p-0 m-0" style="font-size: 12px;">Masukkan username atau password yang telah di berikan oleh Tim IT atau TU. Atau jika kamu telah merubahnya, masukkan password yang telah kamu rubah tsb.</p>
                        <div class="alert border bg-light mt-2" style="border-radius: 15px;">
                            <div class="d-flex">
                                <i class="ti ti-exclamation-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                <div>
                                    <p style="font-size:14px" class="fw-bold p-0 m-0 text-black">Lupa username atau kata sandi kamu?</p>
                                    <p style="font-size:12px" class="text-black p-0 m-0">Silahkan untuk menghubungi Tim IT dan TU untuk mereset ulang kata sandimu.</p>
                                </div>
                            </div>
                        </div>

                    </li>
                    <li>Klik tombol "Masuk"</li>
                </ul>

                <h3>2. Mengenal Dashboard</h3>
                <p>Setelah login, kamu akan langsung melihat kelasmu. Kamu tidak melihat kelas apapun? Gapapa, itu berarti kamu belum di masukkan oleh guru ke dalam kelas mereka, berikut penampilan jika kamu masuk jika mempunyai kelas :</p>
                <img src="panduan/beranda_siswa.png" class="border" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;">
                <div class="alert border bg-light mt-2" style="border-radius: 15px;">
                    <div class="d-flex">
                        <i class="ti ti-exclamation-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                        <div>
                            <p style="font-size:14px" class="fw-bold p-0 m-0 text-black">Bagaimana cara saya masuk ke kelas?</p>
                            <p style="font-size:12px" class="text-black p-0 m-0">Secara default saat akunmu di buat, kamu tidak mengikuti kelas apapun. Kamu bisa meminta guru, TU, atau Tim IT untuk membantumu masuk ke dalam kelas.</p>
                        </div>
                    </div>
                </div>

                <p>Untuk menu lainya, kamu bisa cek navigasi samping (sidebar) atau jika kamu menggunakan smartphone kamu bisa cek navigasi bawah (navbar).</p>
                <p>Kemudian untuk menu navbar bentuk rinciannya berikut :</p>

                <ul>
                    <li><strong>Ujian</strong>
                        <p style="font-size: 12px;">Menu ujian ini berisi seluruh ujian yang telah di buat oleh guru. Di dalamnya, kamu bisa memilih ujian yang di laksanakan pada hari ini (ujian formal seperti ASAS, ASAT, dan lainya) atau ujian dengan instruksi khusus guru (ulangan harian, dan lainya)</p>
                        <img src="panduan/ujian_siswa.png" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border" alt="">
                    </li>
                    <li><strong>Profil</strong>
                        <p style="font-size: 12px;">Menu profil ini adalah tempat kamu menkonfigurasi akunmu. Tidak hanya akun, kamu pun bisa melihat dari hasil penilaian guru terhadap karaktermu sesuai dengan program Progressive Guidance.</p>
                        <img src="panduan/profil.png" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border" alt="">

                    </li>
                    <li><strong>SAGA AI</strong>
                        <p style="font-size: 12px;">SAGA AI adalah AI yang akan membantumu dalam belajar! Tanyakan apapun, SAGA akan merespon dengan cepat, sesuai dengan apa yang kamu butuhkan.</p>
                        <img src="panduan/ai_siswa.png" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border" alt="">

                    </li>
                </ul>
            </div>

            <div class="content-section" id="siswa-kelas">
                <h1>Mengakses Kelas</h1>
                <p>Sebelum kita lebih jauh, dalam SMAGAEdu ada dua macam kelas yang telah disediakan :</p>
                <img src="panduan/kelas-private-umum.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">

                <ul>
                    <li>Kelas Umum
                        <p style="font-size: 12px;">Kelas ini tersedia bebas, maksudnya kelas ini tidak sifatnya bebas di ikuti oleh siapapun dari jenjang apapun. Biasanya kelas ini berisi kelas-kelas kursus, non-formal, kelas yang tidak berkaitan dengan mata pelajaran sekolah.</p>
                    </li>
                    <li>
                        Kelas Khusus
                        <p style="font-size: 12px;">Kelas ini bersifat private, maksudnya kelas ini tidak bisa siapapun masuk. Jadi siswa harus di masukkan oleh Guru, TU, atau Tim IT agar bisa mengakses kelas bersangkutan. Biasanya kelas ini berisi
                            kelas-kelas mata pelajaran sekolah.</p>
                    </li>
                </ul>
                <p>Kamu bisa mengakses kelas khusus dan umum dari switch bar di beranda, bentuknya seperti gambar di atas</p>

                <h2>Cara Masuk ke Kelas</h2>
                <ol>
                    <li>Dari halaman beranda, cari kelas yang ingin kamu masuki
                        <p class="p-0 m-0" style="font-size: 12px;">Kita asumsikan kamu masuk ke kelas di bawah :</p>
                        <img src="panduan/kelas_siswa.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">
                    </li>
                    <li>Klik tombol masuk</li>
                    <li>Taraa~, kamu sudah masuk di dalam kelas
                        <img src="panduan/kelas.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">
                    </li>
                </ol>

                <h2>Fungsi dalam Kelas</h2>
                <p>Di dalam kelas terdapat berbagai macam fungsi yang akan di gunakan oleh guru mapelmu, yaitu :</p>
                <ul>
                    <li><strong>Postingan</strong>
                        Postingan adalah tempat guru mengirimkan pengumuman, tugas, atau materi. Kamu bisa berinteraksi dengan postingan ini dengan cara memberikan komentar atau reaksi.</li>
                    <img src="panduan/kelas-postingan.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">

                    <div class="alert border bg-light mt-0" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="ti ti-exclamation-circle fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p style="font-size:14px" class="fw-bold p-0 m-0 text-black">Batasan postingan berlaku</p>
                                <p style="font-size:12px" class="text-black p-0 m-0">Di kelas khusus kamu tidak bisa memposting apapun, hak postingan hanya diberikan kepada
                                    Guru, namun pada kelas umum kamu bisa memposting apapun yang kamu mau.</p>
                            </div>
                        </div>
                    </div>

                    </li>
                    <li><strong>Tugas:</strong> Ini adalah kamu ngumpulin tugas secara terstruktur ke guru. Seperti yang kamu lihat di bawah, nanti kalau kamu mau kumpul akan ada tombol <span class="border rounded-2 mx-2 p-1 text-muted" style="font-size: 12px;">kumpulkan tugas</span>, nanti akan tampil jendela dan kamu isi semua input yang di minta.
                        <img src="panduan/kelas-tugas-kumpulkan.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">
                        <p>Kalau tombol <span class="border rounded-2 mx-2 p-1 text-muted" style="font-size: 12px;">kumpulkan tugas</span> di klik, bakal muncul jendela atau modal seperti di bawah :</p>
                        <img src="panduan/kelas-tugas-kumpulkan-modal.png" alt="" style="max-width: 100%; width: 100%; border-radius: 10px;" class="border my-4">
                        <p>Jangan lupa buat upload tugasmu di kotak yang sudah di sediakan, berkas file yang bisa di upload disini cuman PDF, DOC, DOCX, JPG, PNG. Trus jangan lupa buat centang pernyataannya, pernyataan ini maksudnya agar kamu paham kalau tugas yang telah di kumpulkan tidak bisa di tarik atau di batalkan. Kalau udah klik <span class="border rounded-2 mx-2 p-1 text-muted" style="font-size: 12px;">Kumpulkan</span> buat ngirim ke guru.</p>

                    </li>
                    <li><strong>Quiz:</strong> Ujian online dan latihan</li>
                    <li><strong>Diskusi:</strong> Forum untuk bertanya dan berdiskusi</li>
                    <li><strong>Nilai:</strong> Riwayat nilai dan feedback</li>
                </ul>
            </div>

            <!-- Guru Content -->
            <div class="content-section" id="guru-mulai">
                <h1>Memulai sebagai Guru</h1>
                <p>Panduan komprehensif untuk guru dalam menggunakan LMS untuk mengajar dan mengelola pembelajaran.</p>

                <h2>Persiapan Awal</h2>
                <div class="highlight-box">
                    <strong>🎯 Tujuan:</strong> Membantu guru memahami dasar-dasar penggunaan LMS untuk proses pembelajaran yang efektif.
                </div>

                <h3>1. Setup Profil Guru</h3>
                <ul>
                    <li>Lengkapi informasi profil personal</li>
                    <li>Upload foto profil profesional</li>
                    <li>Atur preferensi notifikasi</li>
                    <li>Verifikasi informasi kontak</li>
                </ul>

                <h3>2. Memahami Dashboard Guru</h3>
                <p>Dashboard guru memiliki fitur khusus:</p>
                <ul>
                    <li><strong>Ringkasan Kelas:</strong> Statistik semua kelas yang diampu</li>
                    <li><strong>Tugas Perlu Review:</strong> Tugas siswa yang menunggu penilaian</li>
                    <li><strong>Kalender Mengajar:</strong> Jadwal kelas dan deadline</li>
                    <li><strong>Quick Actions:</strong> Shortcut untuk membuat tugas, quiz, atau pengumuman</li>
                </ul>
            </div>

            <div class="content-section" id="guru-kelas">
                <h1>Membuat & Mengelola Kelas</h1>
                <p>Panduan detail untuk membuat kelas baru dan mengelola kelas yang sudah ada.</p>

                <h2>Membuat Kelas Baru</h2>
                <ol>
                    <li>Klik tombol "Buat Kelas Baru" di dashboard</li>
                    <li>Isi informasi dasar:
                        <ul>
                            <li>Nama kelas</li>
                            <li>Deskripsi singkat</li>
                            <li>Kode kelas (opsional)</li>
                            <li>Tingkat/Level</li>
                        </ul>
                    </li>
                    <li>Atur pengaturan kelas:
                        <ul>
                            <li>Mode akses (terbuka/tertutup)</li>
                            <li>Periode aktif kelas</li>
                            <li>Kapasitas maksimal siswa</li>
                        </ul>
                    </li>
                    <li>Klik "Buat Kelas"</li>
                </ol>

                <h2>Mengelola Kelas Existing</h2>
                <h3>Mengundang Siswa</h3>
                <p>Ada beberapa cara untuk mengundang siswa:</p>
                <ul>
                    <li><strong>Kode Kelas:</strong> Bagikan kode kelas untuk self-enrollment</li>
                    <li><strong>Email Invitation:</strong> Kirim undangan langsung via email</li>
                    <li><strong>Manual Add:</strong> Tambahkan siswa dari database sistem</li>
                </ul>
            </div>

            <!-- Admin Content -->
            <div class="content-section" id="admin-dashboard">
                <h1>Dashboard Admin</h1>
                <p>Control center untuk mengelola seluruh sistem LMS dengan efisien.</p>

                <h2>Overview Dashboard</h2>
                <p>Dashboard admin memberikan pandangan menyeluruh tentang:</p>

                <h3>Statistik Utama</h3>
                <ul>
                    <li><strong>Total Users:</strong> Jumlah siswa, guru, dan admin aktif</li>
                    <li><strong>Total Kelas:</strong> Kelas aktif dan arsip</li>
                    <li><strong>Server Status:</strong> Monitoring kesehatan sistem</li>
                    <li><strong>Storage Usage:</strong> Penggunaan ruang penyimpanan</li>
                </ul>

                <h3>Quick Actions</h3>
                <div class="code-block">
                    Admin Quick Menu:
                    - Tambah User Baru
                    - Backup Database
                    - Lihat Log Sistem
                    - Pengaturan Global
                    - Report Generator
                </div>

                <h3>Monitoring Real-time</h3>
                <ul>
                    <li>User online saat ini</li>
                    <li>Aktivitas login terbaru</li>
                    <li>Error logs dan warnings</li>
                    <li>Performance metrics</li>
                </ul>
            </div>

            <!-- Developer Content -->
            <div class="content-section" id="dev-instalasi">
                <h1>Instalasi & Setup</h1>
                <p>Panduan teknis untuk developer dalam menginstall dan mengkonfigurasi LMS.</p>

                <h2>Requirements</h2>
                <h3>Server Requirements</h3>
                <div class="code-block">
                    - PHP >= 7.4
                    - MySQL >= 5.7 atau MariaDB >= 10.2
                    - Apache atau Nginx
                    - Node.js >= 14.x (untuk frontend build)
                    - Composer (PHP package manager)
                </div>

                <h3>Development Tools</h3>
                <ul>
                    <li><strong>Backend:</strong> Laravel Framework</li>
                    <li><strong>Frontend:</strong> Vue.js atau React (sesuai pilihan)</li>
                    <li><strong>Database:</strong> MySQL dengan Eloquent ORM</li>
                    <li><strong>Build Tools:</strong> Webpack, Vite</li>
                </ul>

                <h2>Installation Steps</h2>
                <h3>1. Clone Repository</h3>
                <div class="code-block">
                    git clone https://github.com/your-org/lms-system.git
                    cd lms-system
                </div>

                <h3>2. Install Dependencies</h3>
                <div class="code-block">
                    # Backend dependencies
                    composer install

                    # Frontend dependencies
                    npm install
                </div>

                <h3>3. Environment Setup</h3>
                <div class="code-block">
                    # Copy environment file
                    cp .env.example .env

                    # Generate application key
                    php artisan key:generate
                </div>

                <div class="highlight-box">
                    <strong>⚠️ Penting:</strong> Jangan lupa untuk mengkonfigurasi database credentials di file .env sebelum menjalankan migration.
                </div>
            </div>

            <div class="content-section" id="dev-api">
                <h1>API Documentation</h1>
                <p>Dokumentasi lengkap REST API untuk integrasi dengan sistem LMS.</p>

                <h2>Authentication</h2>
                <p>LMS menggunakan JWT (JSON Web Token) untuk authentication.</p>

                <h3>Login Endpoint</h3>
                <div class="code-block">
                    POST /api/auth/login
                    Content-Type: application/json

                    {
                    "email": "user@example.com",
                    "password": "password123"
                    }

                    Response:
                    {
                    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
                    "user": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "user@example.com",
                    "role": "student"
                    }
                    }
                </div>

                <h3>Using Token</h3>
                <div class="code-block">
                    Authorization: Bearer {your-jwt-token}
                </div>

                <h2>Core Endpoints</h2>
                <h3>Users API</h3>
                <div class="code-block">
                    GET /api/users # List all users
                    POST /api/users # Create new user
                    GET /api/users/{id} # Get user details
                    PUT /api/users/{id} # Update user
                    DELETE /api/users/{id} # Delete user
                </div>

                <h3>Classes API</h3>
                <div class="code-block">
                    GET /api/classes # List classes
                    POST /api/classes # Create class
                    GET /api/classes/{id} # Get class details
                    PUT /api/classes/{id} # Update class
                    DELETE /api/classes/{id} # Delete class

                    # Class enrollment
                    POST /api/classes/{id}/enroll
                    DELETE /api/classes/{id}/unenroll
                </div>
            </div>
        </main>
    </div>

    <script>
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar navigation
            const navSections = document.querySelectorAll('.nav-section');
            const navLinks = document.querySelectorAll('.nav-submenu a');
            const contentSections = document.querySelectorAll('.content-section');
        });

        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar navigation
            const navSections = document.querySelectorAll('.nav-section');
            const navLinks = document.querySelectorAll('.nav-submenu a');
            const contentSections = document.querySelectorAll('.content-section');

            // Mobile sidebar toggle elements
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Function to show sidebar
            function showSidebar() {
                sidebar.classList.add('show');
                sidebarOverlay.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent scrolling when sidebar open
            }

            // Function to hide sidebar
            function hideSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }

            // Toggle sidebar when hamburger clicked
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (sidebar.classList.contains('show')) {
                        hideSidebar();
                        sidebarToggle.classList.remove('active');
                    } else {
                        showSidebar();
                        sidebarToggle.classList.add('active');
                    }
                });
            }

            // Hide sidebar when overlay clicked
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', hideSidebar);
            }

            // Hide sidebar when menu item clicked (mobile only)
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Check if we're in mobile view
                    if (window.innerWidth <= 768) {
                        hideSidebar();
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Desktop mode
                    hideSidebar();
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    sidebarToggle.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Toggle sidebar sections
            navSections.forEach(section => {
                const button = section.querySelector('.nav-title');
                button.addEventListener('click', () => {
                    // Close other sections
                    navSections.forEach(otherSection => {
                        if (otherSection !== section) {
                            otherSection.classList.remove('active');
                        }
                    });
                    // Toggle current section
                    section.classList.toggle('active');
                });
            });

            // Handle content switching
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const contentId = link.getAttribute('data-content');

                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    link.classList.add('active');

                    // Hide all content sections
                    contentSections.forEach(section => {
                        section.classList.remove('active');
                    });

                    // Show selected content
                    const targetSection = document.getElementById(contentId);
                    if (targetSection) {
                        targetSection.classList.add('active');
                    }
                });
            });

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');

            // Create searchable content index
            const searchIndex = [];
            navLinks.forEach(link => {
                const contentId = link.getAttribute('data-content');
                const contentSection = document.getElementById(contentId);
                if (contentSection) {
                    const title = contentSection.querySelector('h1')?.textContent || '';
                    const content = contentSection.textContent || '';
                    searchIndex.push({
                        title: title,
                        content: content.toLowerCase(),
                        link: link,
                        id: contentId
                    });
                }
            });

            // Search input handler
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();

                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }

                const results = searchIndex.filter(item =>
                    item.title.toLowerCase().includes(query) ||
                    item.content.includes(query)
                ).slice(0, 8);

                if (results.length > 0) {
                    searchResults.innerHTML = results.map(result =>
                        `<div class="search-result-item" data-content="${result.id}">
                            <strong>${result.title}</strong>
                        </div>`
                    ).join('');
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="search-result-item">Tidak ada hasil ditemukan</div>';
                    searchResults.style.display = 'block';
                }
            });

            // Handle search result clicks
            searchResults.addEventListener('click', function(e) {
                const resultItem = e.target.closest('.search-result-item');
                if (resultItem && resultItem.dataset.content) {
                    const contentId = resultItem.dataset.content;
                    const targetLink = document.querySelector(`[data-content="${contentId}"]`);
                    if (targetLink) {
                        targetLink.click();
                        searchInput.value = '';
                        searchResults.style.display = 'none';
                    }
                }
            });

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });

            // Initialize proper state on load
            function initializeSidebar() {
                if (window.innerWidth > 768) {
                    // Desktop mode
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    sidebarToggle.classList.remove('active');
                    document.body.style.overflow = '';
                } else {
                    // Mobile mode
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    sidebarToggle.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            // Call on load
            initializeSidebar();

            // Open first section by default
            if (navSections.length > 0) {
                navSections[0].classList.add('active');
            }
        });
    </script>
</body>

</html>