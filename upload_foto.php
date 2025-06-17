// upload_foto.php
<?php
session_start();
require "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_SESSION['userid'];
    $target_dir = "uploads/";
    $upload_success = false;
    
    if (isset($_FILES["foto_profil"])) {
        $file = $_FILES["foto_profil"];
        $filename = uniqid() . "_" . basename($file["name"]);
        
        if (move_uploaded_file($file["tmp_name"], $target_dir . $filename)) {
            $query = "UPDATE siswa SET foto_profil = ? WHERE username = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ss", $filename, $userid);
            $upload_success = mysqli_stmt_execute($stmt);
        }
    }

    if (isset($_FILES["foto_latarbelakang"])) {
        // Similar code for background photo
    }

    header("Location: profil.php");
    exit();
}
?>