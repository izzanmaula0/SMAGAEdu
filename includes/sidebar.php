<?php
$is_guru = $_SESSION['level'] == 'guru';
$is_admin = $_SESSION['level'] == 'admin';

?>

<script src="https://cdn.jsdelivr.net/npm/darkmode-js@1.5.7/lib/darkmode-js.min.js"></script>

<!-- tabler -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />


<style>
    /* CSS untuk tooltip */
    .menu-item {
        position: relative;
    }

    .menu-item .tooltip-text {
        visibility: hidden;
        width: auto;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
        position: absolute;
        z-index: 1;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        white-space: nowrap;
        font-size: 12px;
    }

    .menu-item:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    /* Panah untuk tooltip */
    .menu-item .tooltip-text::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 100%;
        margin-top: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: transparent #333 transparent transparent;
    }
</style>



<div class="col-auto vh-100 p-3 p-md-4 menu-samping d-none d-md-block" style="background-color:rgb(238, 236, 226)">
    <!-- Logo -->
    <div class="ps-2 mb-4">
        <a href="<?php echo $is_admin ? 'beranda_admin.php' : ($is_guru ? 'beranda_guru.php' : 'beranda.php'); ?>" class="text-decoration-none text-dark d-flex align-items-center gap-2">
            <img src="assets/smagaedu.png" alt="" width="28" class="logo_orange">
            <div>
                <h1 class="m-0" style="font-size: 18px;">SMAGAEdu</h1>
                <p class="m-0 text-muted" style="font-size: 11px;"><?php echo $is_admin ? 'ADMINISTRATOR' : 'LMS'; ?></p>
            </div>
        </a>
    </div>

    <!-- Menu Items -->
    <div class="d-flex flex-column gap-1">
        <?php
        if ($is_admin) {
            $menu_items = [
                ['url' => 'beranda_admin.php', 'icon' => 'bi-house-door', 'text' => 'Beranda', 'tooltip' => 'Seluruh kelas di SMAGAEdu'],
                ['url' => 'ujian_admin.php', 'icon' => 'bi-file-text', 'text' => 'Ujian', 'tooltip' => 'Seluruh ujian di SMAGAEdu'],
                ['url' => 'guru_admin.php', 'icon' => 'bi-person-badge', 'text' => 'Guru', 'tooltip' => 'Tambah, edit, atau hapus akun guru dari SMAGAEdu'],
                ['url' => 'siswa_admin.php', 'icon' => 'bi-people', 'text' => 'Siswa', 'tooltip' => 'Tambah, edit, atau hapus akun siswa dari SMAGAEdu'],
                ['url' => 'pg_admin.php', 'icon' => 'bi-journal-text', 'text' => 'Monitor', 'subtitle' => 'P. GUIDENCE', 'tooltip' => 'Monitor progressive guidance siswa yang telah di inputkan guru'],
                ['url' => 'alumni.php', 'icon' => 'bi-people', 'text' => 'Alumni', 'tooltip' => 'Kumpulan akun siswa yang telah lulus'],
                ['url' => 'manage_info.php', 'icon' => 'bi-stars', 'text' => 'SAGA', 'subtitle' => 'KNOWLEDGE', 'tooltip' => 'Kelola pengetahuan SAGA AI'],
            ];
        } elseif ($is_guru) {
            $menu_items = [
                ['url' => 'beranda_guru.php', 'icon' => 'ti-home', 'text' => 'Beranda', 'tooltip' => 'Buat atau atur kelas Anda'],
                ['url' => 'ujian_guru.php', 'icon' => 'ti-file-text', 'text' => 'Ujian', 'tooltip' => 'Buat atau atur ujian Anda'],
                ['url' => 'profil_guru.php', 'icon' => 'ti-user-circle', 'text' => 'Profil', 'tooltip' => 'Pengaturan profil Anda'],
                ['url' => 'ai_guru.php', 'icon' => 'ti-sparkles', 'text' => 'SAGA AI', 'tooltip' => 'Bicara dengan AI milik SMAGA'],
                ['url' => 'raport_pg.php', 'icon' => 'ti-user-screen', 'text' => 'Raport', 'subtitle' => 'P. GUIDENCE', 'tooltip' => 'Input manajemen progressive guidance siswa'],
                ['url' => 'monitor_pg.php', 'icon' => 'ti-search', 'text' => 'Monitor', 'subtitle' => 'P. GUIDENCE', 'tooltip' => 'Monitor progressive guidance yang telah di input'],
            ];
        } else {
            $menu_items = [
                ['url' => 'beranda.php', 'icon' => 'ti-home', 'text' => 'Beranda', 'tooltip' => 'Kumpulan kelas yang kamu ikuti'],
                ['url' => 'ujian.php', 'icon' => 'ti-file-text', 'text' => 'Ujian', 'tooltip' => 'Kumpulan ujian yang akan atau telah kamu kerjakan'],
                ['url' => 'profil.php', 'icon' => 'ti-user-circle', 'text' => 'Profil', 'tooltip' => 'Pengaturan profil kamu'],
                ['url' => 'ai.php', 'icon' => 'ti-sparkles', 'text' => 'SAGA AI', 'tooltip' => 'Bicara dengan AI milik SMAGA'],
                ['url' => 'permainan.php', 'icon' => 'ti ti-device-gamepad-2', 'text' => 'Intermezo', 'tooltip' => 'Game ringan penghilang gabut'],
            ];
        }

        $current_page = basename($_SERVER['PHP_SELF']);

        foreach ($menu_items as $item) {
            $is_active = ($current_page === $item['url']) ? 'active' : '';
        ?>
            <a href="<?= $item['url'] ?>" class="text-decoration-none text-dark">
                <div class="menu-item <?= $is_active ?> d-flex align-items-center">
                    <?php if (isset($item['is_image']) && $item['is_image']): ?>
                        <img src="assets/<?= $item['icon'] ?>" alt="" class="menu-icon">
                    <?php else: ?>
                        <i class="ti <?= $item['icon'] ?> menu-icon" style="font-size: 20px !important;"></i>
                    <?php endif; ?>
                    <div>
                        <span class="menu-text m-0 p-0"><?= $item['text'] ?></span>
                        <?php if (isset($item['subtitle'])): ?>
                            <p class="text-muted m-0 p-0" style="font-size: 10px;"><?= $item['subtitle'] ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($item['tooltip'])): ?>
                        <span class="tooltip-text"><?= $item['tooltip'] ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php } ?>
    </div>

    <!-- Profile Section -->
    <div class="mt-auto position-absolute bottom-0 start-0 p-3 w-100">
        <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 w-100 rounded-3 border bg-white" type="button" data-bs-toggle="dropdown">
                <img src="<?php echo ($is_guru || $is_admin) ?
                                (!empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png') : (!empty($siswa['photo_url']) ? $siswa['photo_url'] : 'assets/pp.png'); ?>"
                    width="32" class="rounded-circle">
                <span class="text-truncate username-text" style="font-size: 13px;">
                    <?php
                    if ($is_admin) {
                        echo $guru['namaLengkap'] ?? ' Halo, Admin';
                    } else {
                        echo ($is_guru && isset($guru['namaLengkap'])) ? $guru['namaLengkap'] : (isset($siswa['nama']) ? $siswa['nama'] : 'Halo, Admin');
                    }
                    ?>
                </span>
            </button>
            <ul class="dropdown-menu w-100 shadow-sm border-0 py-2" style="font-size: 13px;">
                <li>
                    <button class="dropdown-item py-2 px-3" id="darkModeToggle">
                        <i class="bi bi-sun-fill me-2"></i>Mode Terang
                    </button>
                </li>
                <li>
                    <a class="dropdown-item py-2 px-3 text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* Styling kustom untuk dark mode */
    .darkmode--activated {
        /* Gaya dasar untuk body saat dark mode aktif */
        --text-color: #eee;
        --background-color: #222;
        --menu-bg-color: #333;
        --border-color: #444;
        --menu-hover-color: #444;
        --menu-active-color: rgba(219, 106, 68, 0.2);
        --orange-color: rgb(219, 106, 68);
        --card-bg-color: #333;
        --card-border-color: #444;
    }

    /* Base overrides */
    .darkmode--activated {
        background-color: var(--background-color) !important;
    }

    .darkmode--activated .col-utama {
        background-color: var(--background-color) !important;
    }

    /* Text color overrides */
    .darkmode--activated .text-black,
    .darkmode--activated .text-dark,
    .darkmode--activated h1,
    .darkmode--activated h2,
    .darkmode--activated h3,
    .darkmode--activated h4,
    .darkmode--activated h5,
    .darkmode--activated h6,
    .darkmode--activated p:not(.text-muted),
    .darkmode--activated label,
    .darkmode--activated .fw-bold,
    .darkmode--activated .class-title {
        color: var(--text-color) !important;
    }

    .darkmode--activated .text-muted {
        color: #aaa !important;
    }

    /* Background overrides */
    .darkmode--activated .bg-white,
    .darkmode--activated .bg-light,
    .darkmode--activated [style*="background-color:rgb(238, 236, 226)"],
    .darkmode--activated [style*="background-color: #f8f9fa;"],
    .darkmode--activated [style*="background-color: #fff"],
    .darkmode--activated [style*="background-color: #ffffff"],
    .darkmode--activated [style*="background-color: white"] {
        background-color: var(--menu-bg-color) !important;
    }

    /* Card styling */
    .darkmode--activated .card,
    .darkmode--activated .custom-card,
    .darkmode--activated .class-card,
    .darkmode--activated .notification-card,
    .darkmode--activated .card-body,
    .darkmode--activated .kelas-item,
    .darkmode--activated .soal-card,
    .darkmode--activated .notification-item {
        background-color: var(--card-bg-color) !important;
        border-color: var(--card-border-color) !important;
        color: var(--text-color) !important;
    }

    .darkmode--activated .class-content,
    .darkmode--activated .modal-content {
        background-color: var(--card-bg-color) !important;
        color: var(--text-color) !important;
    }

    /* Button styling */
    .darkmode--activated .btn-light,
    .darkmode--activated .btn-white,
    .darkmode--activated .btn-outline-secondary,
    .darkmode--activated .btn-filter,
    .darkmode--activated .btn-more,
    .darkmode--activated .mini-fab {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* untuk navigasi soal */
    .darkmode--activated .soal-number {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .btn-filter.active {
        background-color: var(--orange-color) !important;
        color: black !important;
        border-color: var(--orange-color) !important;
    }

    .darkmode--activated .btn-enter,
    .darkmode--activated .btn-umum,
    .darkmode--activated .main-fab,
    .darkmode--activated .color-web {
        background-color: var(--orange-color) !important;
        color: white !important;
    }

    /* Form elements */
    .darkmode--activated input,
    .darkmode--activated select,
    .darkmode--activated textarea,
    .darkmode--activated .form-control,
    .darkmode--activated .form-select {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Dropdown menu */
    .darkmode--activated .dropdown-menu {
        background-color: var(--card-bg-color) !important;
        border-color: var(--card-border-color) !important;
    }

    .darkmode--activated .dropdown-item {
        color: var(--text-color) !important;
    }

    .darkmode--activated .dropdown-item:hover {
        background-color: var(--menu-hover-color) !important;
    }

    /* Modal styling */
    .darkmode--activated .modal-header,
    .darkmode--activated .modal-footer,
    .darkmode--activated .modal-body {
        background-color: var(--card-bg-color) !important;
        color: var(--text-color) !important;
        /* border-color: var(--border-color) !important; */
    }

    .darkmode--activated .modal-content {
        background-color: var(--card-bg-color) !important;
    }

    /* Custom filter items */
    .darkmode--activated .filter-container,
    .darkmode--activated #addFilterBtn,
    .darkmode--activated .kelas-item {
        background-color: var(--card-bg-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Alerts */
    .darkmode--activated .alert {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .alert-light {
        background-color: var(--card-bg-color) !important;
    }

    /* Fix for border elements */
    .darkmode--activated .border {
        border-color: var(--border-color) !important;
    }

    /* Fix for notification items */
    .darkmode--activated .notification-class {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #aaa !important;
    }

    /* Fix for elements that shouldn't be inverted */
    .darkmode--activated img:not(.logo_orange) {
        mix-blend-mode: normal;
    }

    /* Fix for links */

    .darkmode--activated a {
        color: var(--text-color) !important;
    }

    .darkmode--activated a:not(.btn):not(.dropdown-item):not(.menu-item):not(.btn-enter) {
        color: rgb(219, 106, 68) !important;
    }

    .darkmode--activated a:not(.btn):not(.dropdown-item):not(.menu-item):not(.btn-enter):hover {
        color: rgb(219, 106, 68) !important;
    }

    /* Fix for class-banner & notification dropdown */
    .darkmode--activated .class-banner {
        border-bottom: 1px solid var(--border-color);
    }

    .darkmode--activated .notification-dropdown {
        background-color: var(--card-bg-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Fix for checkboxes */
    .darkmode--activated .form-check-input {
        background-color: var(--menu-bg-color);
        border-color: var(--border-color);
    }

    .darkmode--activated .form-check-input:checked {
        background-color: var(--orange-color);
        border-color: var(--orange-color);
    }

    /* Fix for modal popups */
    .darkmode--activated #zoomWarningModal .modal-content,
    .darkmode--activated #resizeWarningModal .modal-content,
    .darkmode--activated #updateFeatureModal .modal-content,
    .darkmode--activated #deleteFilterModal .modal-content,
    .darkmode--activated #deleteConfirmModal .modal-content {
        background-color: var(--card-bg-color) !important;
    }

    /* Fix for custom class styling */
    .darkmode--activated .custom-info-box {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Fix untuk tab aktif */
    .darkmode--activated .active-tab {
        background-color: var(--orange-color) !important;
        color: white !important;
        border-color: var(--orange-color) !important;
    }

    /* untuk jawaban soal */
    .darkmode--activated .pilihan {
        color: var(--text-color) !important;
    }

    /* untuk jawaban benar */
    .darkmode--activated .correct {
        color: #99ff99 !important;
    }

    /* untuk value dalam soal */
    .darkmode--activated .value {
        color: var(--text-color) !important;
    }

    /* Untuk button edit/trash dalam buat soal */
    .darkmode--activated .edit-button,
    .darkmode--activated .btn-action i.bi-pencil,
    .darkmode--activated .btn-action i.ti-pencil {
        background-color: rgba(0, 255, 0, 0.1) !important;
        color: #4caf50 !important;
        /* Warna hijau */
        border: white !important;
    }

    /* Styling untuk soal-number biasa dalam dark mode */
    .darkmode--activated .soal-number:not(.with-description) {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Styling khusus untuk soal-number dengan with-description dalam dark mode */
    .darkmode--activated .soal-number.with-description {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border: 1px solid var(--orange-color) !important;
        /* Border berwarna orange agar lebih terlihat */
    }

    .darkmode--activated .trash-button,
    .darkmode--activated .btn-action i.bi-trash,
    .darkmode--activated .btn-action i.ti-trash {
        background-color: rgba(255, 0, 0, 0.1) !important;
        color: #f44336 !important;
        /* Warna merah */
        border: white !important;
    }

    /* Override untuk icon dalam button */
    .darkmode--activated .btn-action:hover {
        background-color: var(--menu-hover-color) !important;
        border: white !important;
    }

    .darkmode--activated .btn-action {
        background-color: var(--card-bg-color) !important;
        border-color: white !important;
    }

    /* dark mode untuk filter */
    .darkmode--activated .filter-container {
        background-color: var(--background-color) !important;
        border: var(--background-color) !important;
    }

    /* navigation button di buat_ujian.php */
    .darkmode--activated .navigation-buttons {
        background-color: var(--menu-bg-color) !important;
    }

    /* ikon ujian di buat_ujian */
    .darkmode--activated .icon-ujian {
        background-color: white !important;
    }

    /* kartu pada buat ujian.php */
    .darkmode--activated .kartu-buat-ujian {
        background-color: var(--background-color) !important;
    }

    /* Styling untuk placeholder di dark mode */
    .darkmode--activated input::placeholder,
    .darkmode--activated textarea::placeholder,
    .darkmode--activated select::placeholder,
    .darkmode--activated .form-control::placeholder {
        color: #aaa !important;
        /* Warna abu-abu yang lebih terang */
        opacity: 0.7 !important;
    }

    /* Support untuk browser lain */
    .darkmode--activated input::-webkit-input-placeholder,
    .darkmode--activated textarea::-webkit-input-placeholder,
    .darkmode--activated select::-webkit-input-placeholder,
    .darkmode--activated .form-control::-webkit-input-placeholder {
        color: #aaa !important;
        opacity: 0.7 !important;
    }

    .darkmode--activated input::-moz-placeholder,
    .darkmode--activated textarea::-moz-placeholder,
    .darkmode--activated select::-moz-placeholder,
    .darkmode--activated .form-control::-moz-placeholder {
        color: #aaa !important;
        opacity: 0.7 !important;
    }

    .darkmode--activated input:-ms-input-placeholder,
    .darkmode--activated textarea:-ms-input-placeholder,
    .darkmode--activated select:-ms-input-placeholder,
    .darkmode--activated .form-control:-ms-input-placeholder {
        color: #aaa !important;
        opacity: 0.7 !important;
    }

    /* border error */
    .darkmode--activated .input-group-text {
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* username name di sidebar */
    .darkmode--activated .username-text {
        color: var(--text-color) !important;
    }

    /* btn selain rgb primary */
    .darkmode--activated .btn:not(.colorWeb):not(.btn-primary):not(.btn-umum):not(.color-web) {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* postingan pada kelas */
    .darkmode--activated .postingan,
    .catatanGuru {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* AI_GURU.PHP */
    /* ai guru bagian welcome card */
    .darkmode--activated .welcome-message {
        background-color: var(--background-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .chat-container {
        background-color: var(--background-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .input-wrapper {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .loading-screen {
        background-color: var(--background-color) !important;
    }

    .darkmode--activated .chat-message {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* RAPORT PG */
    .darkmode--activated .ios-card {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .select2-selection,
    .select2-dropdown,
    .accordion-item {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .accordion-button {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .accordion-button:hover {
        background-color: var(--orange-color) !important;
    }

    /* MONITOR PG */
    .darkmode--activated .dashboard-header {
        background-color: var(--background-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Styling khusus untuk DataTables di Dark Mode */
    .darkmode--activated table.dataTable,
    .darkmode--activated .dataTables_wrapper {
        color: var(--text-color) !important;
    }

    /* Header tabel */
    .darkmode--activated table.dataTable thead th,
    .darkmode--activated table.dataTable thead td {
        background-color: var(--card-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Body tabel */
    .darkmode--activated table.dataTable tbody tr {
        background-color: var(--card-bg-color) !important;
        color: var(--text-color) !important;
    }

    /* Hover pada baris tabel */
    .darkmode--activated table.dataTable tbody tr:hover {
        background-color: var(--menu-hover-color) !important;
    }

    /* Paginasi */
    .darkmode--activated .dataTables_paginate .paginate_button {
        background-color: var(--card-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    .darkmode--activated .dataTables_paginate .paginate_button.current {
        background-color: var(--orange-color) !important;
        color: white !important;
        border-color: var(--orange-color) !important;
    }

    .darkmode--activated .dataTables_paginate .paginate_button:hover {
        background-color: var(--menu-hover-color) !important;
        color: var(--orange-color) !important;
    }

    /* Kontrol DataTables */
    .darkmode--activated .dataTables_length select,
    .darkmode--activated .dataTables_filter input {
        background-color: var(--menu-bg-color) !important;
        color: var(--text-color) !important;
        border-color: var(--border-color) !important;
    }

    /* Info pagination */
    .darkmode--activated .dataTables_info {
        color: var(--text-color) !important;
    }

    /* Garis tabel */
    .darkmode--activated table.dataTable.no-footer {
        border-color: var(--border-color) !important;
    }

    .darkmode--activated table.dataTable td,
    .darkmode--activated table.dataTable th {
        border-color: var(--border-color) !important;
    }

    /* Styling untuk select dan input dalam DataTables */
    .darkmode--activated .dataTables_length select:focus,
    .darkmode--activated .dataTables_filter input:focus {
        background-color: var(--menu-bg-color) !important;
        border-color: var(--orange-color) !important;
        box-shadow: 0 0 0 3px rgba(219, 106, 68, 0.25) !important;
    }

    /* Styling untuk progress bar dalam tabel */
    .darkmode--activated .progress {
        background-color: var(--menu-bg-color) !important;
    }

    /* Chart styling */
    .darkmode--activated canvas {
        background-color: var(--card-bg-color) !important;
    }
</style>

<!-- darkmode khusus untuk tabel datatables -->
<script>
    // Modifikasi DataTables untuk Dark Mode
    function updateDataTablesForDarkMode(isDarkMode) {
        // Cek apakah DataTable sudah diinisialisasi
        if ($.fn.dataTable.isDataTable('#studentsTable')) {
            const table = $('#studentsTable').DataTable();

            // Mendapatkan warna berdasarkan mode
            const bgColor = isDarkMode ? '#333' : '#f8f9fa';
            const borderColor = isDarkMode ? '#444' : '#dee2e6';
            const textColor = isDarkMode ? '#eee' : '#212529';
            const primaryColor = 'rgb(218, 119, 86)';

            // Mengubah styling untuk tampilan DataTables
            // Input pencarian
            $('input[type="search"]').css({
                'background-color': isDarkMode ? '#333' : '#f8f9fa',
                'color': textColor,
                'border-color': borderColor
            });

            // Dropdown entri per halaman
            $('.dataTables_length select').css({
                'background-color': isDarkMode ? '#333' : '#f8f9fa',
                'color': textColor,
                'border-color': borderColor
            });

            // Tombol paginasi
            $('.paginate_button').not('.current').css({
                'background-color': isDarkMode ? '#333' : 'transparent',
                'color': isDarkMode ? '#eee' : primaryColor,
                'border-color': isDarkMode ? '#444' : 'transparent'
            });

            // Tombol paginasi aktif
            $('.paginate_button.current').css({
                'background-color': primaryColor,
                'color': 'white',
                'border-color': primaryColor
            });

            // Info tabel (showing X of Y entries)
            $('.dataTables_info').css({
                'color': textColor
            });

            // Perbarui CSS hover untuk tombol paginasi
            $('.paginate_button.previous, .paginate_button.next').hover(
                function() {
                    $(this).css({
                        'background-color': isDarkMode ? '#444' : 'rgba(218, 119, 86, 0.1)',
                        'color': primaryColor
                    });
                },
                function() {
                    $(this).css({
                        'background-color': isDarkMode ? '#333' : 'transparent',
                        'color': isDarkMode ? '#eee' : primaryColor
                    });
                }
            );
        }
    }

    // Fungsi untuk mendeteksi perubahan dark mode
    function observeDarkModeChanges() {
        // MutationObserver untuk mendeteksi perubahan class pada body
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const isDarkMode = document.body.classList.contains('darkmode--activated');
                    updateDataTablesForDarkMode(isDarkMode);
                }
            });
        });

        // Start observing
        observer.observe(document.body, {
            attributes: true
        });

        // Periksa state awal
        const isDarkMode = document.body.classList.contains('darkmode--activated');
        updateDataTablesForDarkMode(isDarkMode);
    }

    // Tambahkan ke event listener yang ada
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi DataTables (kode yang sudah ada)

        // Setelah DataTables diinisialisasi, terapkan observer dark mode
        if (typeof $.fn.dataTable !== 'undefined') {
            observeDarkModeChanges();
        }
    });
</script>

<!-- darkmode -->
<script>
    // Script untuk dark mode utama - tambahkan di bawah script preload
    document.addEventListener('DOMContentLoaded', function() {
        // Function untuk memeriksa apakah dark mode sudah aktif sebelumnya
        function isDarkModeActive() {
            // Cek di localStorage
            return localStorage.getItem('darkmode') === 'true';
        }

        // Function untuk mengatur dark mode ke state tertentu
        function setDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('darkmode--activated');
                localStorage.setItem('darkmode', 'true');
                document.documentElement.classList.add('darkmode-preload');
            } else {
                document.body.classList.remove('darkmode--activated');
                localStorage.setItem('darkmode', 'false');
                document.documentElement.classList.remove('darkmode-preload');
            }
            updateToggleButtonText(isDark);
        }

        // Dapatkan tombol toggle dark mode kustom kita
        const darkModeToggle = document.getElementById('darkModeToggle');

        // Function untuk mengupdate tampilan tombol berdasarkan state dark mode
        function updateToggleButtonText(isDark) {
            if (darkModeToggle) {
                if (isDark) {
                    darkModeToggle.innerHTML = '<i class="bi bi-sun-fill me-2"></i>Mode Terang';
                } else {
                    darkModeToggle.innerHTML = '<i class="bi bi-moon-fill me-2"></i>Mode Gelap';
                }
            }
        }

        // Set dark mode awal berdasarkan localStorage
        setDarkMode(isDarkModeActive());

        // Tambahkan event listener ke tombol
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                // Toggle dark mode (balik state sebelumnya)
                setDarkMode(!isDarkModeActive());
            });
        }

        // Hapus widget darkmode.js jika ada
        if (document.querySelector('.darkmode-toggle')) {
            document.querySelector('.darkmode-toggle').remove();
        }

        // Hapus class preload dan tambahkan transition setelah DOM dimuat
        document.documentElement.classList.remove('darkmode-preload');
        document.documentElement.classList.add('darkmode-transition');

        // Setelah semua transisi selesai, hapus class transition
        setTimeout(function() {
            document.documentElement.classList.remove('darkmode-transition');
        }, 1000);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // deteksi apakah itu mobile atau tablet
        function isMobileOrTablet() {
            const userAgent = navigator.userAgent.toLowerCase();
            return /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/i.test(userAgent);
        }

        function adjustZoomLevel() {
            // Hanya jalankan pada desktop, skip untuk mobile dan tablet
            if (!isMobileOrTablet()) {
                // Deteksi zoom level browser
                const zoomLevel = Math.round(window.devicePixelRatio * 100);

                // Jika zoom level lebih dari 100% (pengguna telah melakukan zoom in)
                if (zoomLevel > 100) {
                    // Cek apakah pengguna telah memilih untuk tidak menampilkan lagi modal ini
                    if (!localStorage.getItem('hideZoomWarning')) {
                        // Tampilkan modal zoom warning alih-alih confirm
                        showZoomModal(zoomLevel);
                    }
                }
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
                            <p class="mb-4" style="font-size:13px;">Beberapa elemen mungkin tidak optimal pada zoom level saat ini (${zoomLevel}%). Kecilkan ukuran layar hingga 90-100% untuk mendapatkan pengalaman terbaik</p>

                            <div class="custom-info-box text-start border bg-light mb-4" style="border-radius: 15px; padding: 12px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Ingin merubah ukuran layar Anda?</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Gunakan kombinasi Keyboard <span class="rounded px-2 mx-1 border" style="">Ctrl</span> + <span class="rounded px-2 mx-1 border" style=""> - </span> untuk memperkecil dan <span class="rounded px-2 mx-1 border" style="">Ctrl</span> + <span class="rounded px-2 mx-1 border" style=""> = </span> untuk memperbesar ukuran layar.
                                        Sesuaikan dengan kebutuhan Anda.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-3 text-start">
                                <input class="form-check-input" type="checkbox" id="doNotShowZoomWarningAgain">
                                <label class="form-check-label" for="doNotShowZoomWarningAgain" style="font-size: 13px;">
                                    Jangan tampilkan pesan ini lagi
                                </label>
                            </div>

                            <div class="d-flex gap-2 btn-group justify-content-center">
                                <button type="button" class="btn px-4 text-white" id="zoomWarningOkBtn" data-bs-dismiss="modal" style="border-radius: 12px; background-color:rgb(219, 106, 68);">Ok</button>
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

                // Tambahkan event listener untuk tombol Ok
                document.getElementById('zoomWarningOkBtn').addEventListener('click', function() {
                    // Cek apakah checkbox "Jangan tampilkan lagi" dicentang
                    if (document.getElementById('doNotShowZoomWarningAgain').checked) {
                        // Simpan preferensi pengguna di localStorage
                        localStorage.setItem('hideZoomWarning', 'true');
                    }
                });

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

        window.addEventListener('resize', function() {
            // Hanya jalankan pada desktop, skip untuk mobile dan tablet
            if (!isMobileOrTablet()) {
                const currentZoom = Math.round(window.devicePixelRatio * 100);

                // Jika zoom level naik di atas 105%
                if (currentZoom > 105) {
                    // Cek apakah pengguna telah memilih untuk tidak menampilkan lagi modal ini
                    if (!localStorage.getItem('hideResizeWarning')) {
                        // Tampilkan modal peringatan alih-alih pesan sederhana
                        showResizeWarningModal();
                    }
                }
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
                            <p class="mb-4">Kami mendeteksi perubahan layar, silahkan gunakan zoom 90-100% dan maksimalkan jendela peramban untuk tampilan web paling optimal</p>
                            <div class="custom-info-box text-start border bg-light mb-4" style="border-radius: 15px; padding: 12px;">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Ingin merubah ukuran layar Anda?</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">Gunakan kombinasi Keyboard <span class="rounded px-2 border mx-1" style="">Ctrl</span> + <span class="rounded px-2 border mx-1" style=""> - </span> untuk memperkecil dan <span class="rounded px-2 mx-1 border" style="">Ctrl</span> + <span class="rounded px-2 mx-1 border" style=""> = </span> untuk memperbesar ukuran layar.
                                        Sesuaikan dengan kebutuhan Anda.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mb-3 text-start">
                                <input class="form-check-input" type="checkbox" id="doNotShowResizeWarningAgain">
                                <label class="form-check-label" for="doNotShowResizeWarningAgain" style="font-size: 13px;">
                                    Jangan tampilkan pesan ini lagi
                                </label>
                            </div>

                            <div class="d-flex gap-2 btn-group justify-content-center">
                                <button type="button" class="btn px-4 text-white" id="resizeWarningOkBtn" data-bs-dismiss="modal" style="border-radius: 12px; background-color:rgb(219, 106, 68);">Mengerti</button>
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

                // Tambahkan event listener untuk tombol Mengerti
                document.getElementById('resizeWarningOkBtn').addEventListener('click', function() {
                    // Cek apakah checkbox "Jangan tampilkan lagi" dicentang
                    if (document.getElementById('doNotShowResizeWarningAgain').checked) {
                        // Simpan preferensi pengguna di localStorage
                        localStorage.setItem('hideResizeWarning', 'true');
                    }
                });

                // Inisialisasi modal
                const resizeModal = new bootstrap.Modal(document.getElementById('resizeWarningModal'));
                resizeModal.show();

                // Otomatis tutup modal setelah 8 detik
                // setTimeout(() => {
                //     // Sebelum menutup modal, periksa status checkbox
                //     if (document.getElementById('doNotShowResizeWarningAgain') &&
                //         document.getElementById('doNotShowResizeWarningAgain').checked) {
                //         // Simpan preferensi pengguna
                //         localStorage.setItem('hideResizeWarning', 'true');
                //     }

                //     resizeModal.hide();

                //     // Hapus modal dari DOM setelah ditutup
                //     const modalElement = document.getElementById('resizeWarningModal');
                //     modalElement.addEventListener('hidden.bs.modal', function() {
                //         if (modalElement && modalElement.parentNode) {
                //             modalElement.parentNode.removeChild(modalElement);
                //         }
                //     });
                // }, 8000);
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
</script>