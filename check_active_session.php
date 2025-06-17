<?php
session_start();
header('Content-Type: application/json');

$response = [
    'has_active_session' => isset($_SESSION['active_chat_session']),
    'session_id' => isset($_SESSION['active_chat_session']) ? $_SESSION['active_chat_session'] : null
];

echo json_encode($response);
?>