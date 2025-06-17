<?php
include 'includes/session_config.php'; // Include session kita

// Display info session
echo "<h3>Test Session Management</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Created: " . date('Y-m-d H:i:s', $_SESSION['session_created']) . "<br>";
echo "Last Activity: " . date('Y-m-d H:i:s', $_SESSION['last_activity']) . "<br>";
echo "Sisa Waktu Session: " . getSessionRemainingTime() . " detik<br>";
echo "Sisa Waktu dalam Hari: " . round(getSessionRemainingTime()/86400, 2) . " hari<br>";

// Test set session data
if (!isset($_SESSION['test_data'])) {
    $_SESSION['test_data'] = "Session berhasil dibuat pada " . date('Y-m-d H:i:s');
}

echo "<br>Data Test: " . $_SESSION['test_data'] . "<br>";

// Test link untuk refresh
echo "<br><a href='test_session.php'>Refresh halaman ini</a>";
?>