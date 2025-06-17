<?php
// Custom Session Management yang lebih robust
$session_timeout = 604800; // 7 hari dalam detik

// Pengaturan session yang lebih kuat
ini_set('session.gc_maxlifetime', $session_timeout);
ini_set('session.cookie_lifetime', $session_timeout);
ini_set('session.use_strict_mode', 1);

session_start();

// Regenerate session ID secara berkala untuk keamanan
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 3600) { // Regenerate tiap 1 jam
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Cek apakah ini session pertama kali
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
    $_SESSION['last_activity'] = time();
} else {
    // Cek apakah session sudah expired
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $session_timeout) {
            // Session expired - destroy semua data
            $_SESSION = array();

            // Hapus cookie session juga
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
            
            // Return JSON jika ini AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Session expired', 'redirect' => 'index.php']);
                exit();
            }
            
            echo "SESSION EXPIRED! Session sudah lebih dari 7 hari.";
            exit();
        }
    }
}

// Update waktu terakhir aktivitas
$_SESSION['time_before_update'] = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : time();
$_SESSION['last_activity'] = time();

// Fungsi untuk cek sisa waktu session (Rolling Session)
// Fungsi untuk cek sisa waktu session (Rolling Session)
function getSessionRemainingTime() {
    global $session_timeout;
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = $session_timeout - $elapsed;
        return $remaining > 0 ? $remaining : 0;
    }
    return 0;
}

// Fungsi baru untuk monitoring session
function getSessionInfo() {
    global $session_timeout;
    
    $created = $_SESSION['session_created'] ?? time();
    $last_activity = $_SESSION['last_activity'] ?? time();
    $now = time();
    
    return [
        'session_age' => $now - $created, // Berapa lama session sudah berjalan
        'last_activity' => $now - $last_activity, // Berapa lama sejak aktivitas terakhir
        'max_idle' => $session_timeout, // Maksimal waktu idle
        'remaining_if_idle' => max(0, $session_timeout - ($now - $last_activity)), // Sisa waktu jika idle
        'is_rolling' => true // Indikator ini rolling session
    ];
}

// Fungsi untuk validasi session yang lebih robust
function validateSession() {
    if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Session not found', 'redirect' => 'index.php']);
            exit();
        }
        header("Location: index.php");
        exit();
    }
}
?>