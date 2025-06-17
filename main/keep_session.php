<?php
include 'includes/session_config.php';

header('Content-Type: application/json');

// Prevent any unwanted output
ob_start();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ping') {
        if (isset($_SESSION['userid']) && !empty($_SESSION['userid'])) {
            // Clear any buffer and send clean JSON
            ob_clean();
            echo json_encode(['success' => true, 'time' => time(), 'userid' => $_SESSION['userid']]);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Session not found', 'redirect' => 'index.php']);
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
exit();
?>