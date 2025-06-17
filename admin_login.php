<?php
session_start();

// Admin credentials
$admin_username = 'admin';
$admin_password = 'admin';

if(isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    
    if($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SMAGAEdu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<style>
    .color-web {
        background-color: rgb(218, 119, 86);
        transition: background-color 0.3s ease;
    }
    .color-web:active {
        background-color: rgb(188, 89, 56);
    }
    
</style>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <!-- Logo container - Add your logo image here -->
                <div class="text-center mb-4 rounded-2" style="background-color: rgb(218, 119, 86);">
                    <img src="assets/logo_white.png" alt="SMAGAEdu Logo" class="img-fluid" style="max-height: 100px;">
                </div>

                <div class="card shadow">
                    <!-- Greeting and welcome message section -->
                    <div class="card-header color-web text-white text-center py-3">
                        <h4 class="mb-2">Halo, Admin</h4>
                        <!-- Add your brief description here -->
                        <p class="mb-0 small">Kami harap Anda kemari bukan karena masalah</p>
                    </div>

                    <div class="card-body p-4">
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn color-web text-white w-100 py-2 mt-3">Masuk</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>