<?php
session_start();
session_destroy(); // Menghapus semua data session

unset($_SESSION['first_login']); // Menghapus variabel session userid
header("Location: index.php"); // Kembali ke halaman login
exit();
?>