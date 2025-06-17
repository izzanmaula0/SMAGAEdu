<?php
session_start();
require "koneksi.php";

// Reset sesi chat aktif
if (isset($_SESSION['active_chat_session'])) {
    unset($_SESSION['active_chat_session']);
}

// Kembalikan respons sukses
echo json_encode(['success' => true]);
?>