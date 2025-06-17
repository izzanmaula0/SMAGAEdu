<?php
include 'includes/session_config.php';

// Log untuk debugging
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt started\n", FILE_APPEND);

// Periksa apakah file koneksi ada
if (!file_exists("koneksi.php")) {
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - koneksi.php file not found\n", FILE_APPEND);
    die("File koneksi.php tidak ditemukan");
}

// Load koneksi database
require "koneksi.php";

// Cek koneksi database
if (!$koneksi) {
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Database connection failed: " . mysqli_connect_error() . "\n", FILE_APPEND);
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Periksa apakah form login sudah disubmit dengan memeriksa field spesifik
if (isset($_POST['userid']) && isset($_POST['password'])) {
    $userid = mysqli_real_escape_string($koneksi, $_POST['userid']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt for user: $userid\n", FILE_APPEND);

    // Cek untuk akun admin
    if (($userid == "fauzinugroho" && $password == "a") || ($userid == "admin" && $password == "admin")) {
        $_SESSION['userid'] = $userid;
        $_SESSION['nama'] = $userid; // Menggunakan userid sebagai nama
        $_SESSION['level'] = 'admin';

        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Admin login successful, redirecting to beranda_admin.php\n", FILE_APPEND);

        // Pastikan tidak ada output sebelum header
        if (headers_sent($file, $line)) {
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Headers already sent in $file:$line\n", FILE_APPEND);
            echo "Headers already sent in $file:$line";
            exit;
        }

        header("Location: beranda_admin.php");
        exit();
    }

    // Cek di tabel siswa
    $query_siswa = "SELECT * FROM siswa WHERE username = '$userid' AND password = '$password'";
    $result_siswa = mysqli_query($koneksi, $query_siswa);

    if (!$result_siswa) {
        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Error query siswa: " . mysqli_error($koneksi) . "\n", FILE_APPEND);
        die("Error query siswa: " . mysqli_error($koneksi));
    }

    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Query siswa returned " . mysqli_num_rows($result_siswa) . " rows\n", FILE_APPEND);

    // Cek di tabel guru 
    $query_guru = "SELECT * FROM guru WHERE username = '$userid' AND password = '$password'";
    $result_guru = mysqli_query($koneksi, $query_guru);

    if (!$result_guru) {
        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Error query guru: " . mysqli_error($koneksi) . "\n", FILE_APPEND);
        die("Error query guru: " . mysqli_error($koneksi));
    }

    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Query guru returned " . mysqli_num_rows($result_guru) . " rows\n", FILE_APPEND);

    if (mysqli_num_rows($result_siswa) == 1) {
        $row = mysqli_fetch_assoc($result_siswa);
        $_SESSION['userid'] = $row['username'];
        $_SESSION['nama'] = $row['nama'];
        $_SESSION['level'] = 'siswa';


        $_SESSION['first_login'] = true;

        if (isset($row['foto_profil'])) $_SESSION['foto_profil'] = $row['foto_profil'];
        if (isset($row['foto_latarbelakang'])) $_SESSION['foto_latarbelakang'] = $row['foto_latarbelakang'];

        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Siswa login successful, redirecting to beranda.php\n", FILE_APPEND);

        // Pastikan tidak ada output sebelum header
        if (headers_sent($file, $line)) {
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Headers already sent in $file:$line\n", FILE_APPEND);
            echo "Headers already sent in $file:$line";
            exit;
        }


        header("Location: beranda.php");
        exit();
    } else if (mysqli_num_rows($result_guru) == 1) {
        $row = mysqli_fetch_assoc($result_guru);
        $_SESSION['userid'] = $row['username'];
        $_SESSION['nama'] = $row['namaLengkap'];
        $_SESSION['level'] = 'guru';

        $_SESSION['first_login'] = true;


        if (isset($row['jabatan'])) $_SESSION['jabatan'] = $row['jabatan'];

        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Guru login successful, redirecting to beranda_guru.php\n", FILE_APPEND);

        // Pastikan tidak ada output sebelum header
        if (headers_sent($file, $line)) {
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Headers already sent in $file:$line\n", FILE_APPEND);
            echo "Headers already sent in $file:$line";
            exit;
        }

        header("Location: beranda_guru.php");
        exit();
    } else {
        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - User tidak ditemukan, redirecting to index.php\n", FILE_APPEND);

        // Pastikan tidak ada output sebelum header
        if (headers_sent($file, $line)) {
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Headers already sent in $file:$line\n", FILE_APPEND);
            echo "Headers already sent in $file:$line";
            exit;
        }

        header("Location: index.php?pesan=user_tidak_ditemukan");
        exit();
    }
} else {
    // Jika form belum disubmit atau tidak lengkap
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Form tidak lengkap atau tidak disubmit\n", FILE_APPEND);

    // Debug POST data
    file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

    header("Location: index.php?pesan=form_tidak_lengkap");
    exit();
}
