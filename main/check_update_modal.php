<?php
session_start();
header('Content-Type: application/json');

$response = ['show' => false];

// Cek jika user baru login (menggunakan session flag)
if (isset($_SESSION['show_update_feature_modal']) && $_SESSION['show_update_feature_modal'] === true) {
    $response['show'] = true;
    // Reset flag agar tidak muncul lagi saat refresh
    $_SESSION['show_update_feature_modal'] = false;
}

echo json_encode($response);
exit;