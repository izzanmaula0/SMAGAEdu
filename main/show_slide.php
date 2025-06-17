<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    die("Unauthorized");
}

// Ambil parameter
$file = isset($_GET['file']) ? $_GET['file'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$slide = isset($_GET['slide']) ? intval($_GET['slide']) : 1;

if (empty($file)) {
    die("File not specified");
}

// Gunakan page jika ada, atau slide sebagai fallback
$current_page = $page > 0 ? $page : $slide;

// Cek ekstensi file
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// Redirect ke pdf_viewer.php jika file PDF
if ($ext === 'pdf') {
    header("Location: pdf_viewer.php?file=" . urlencode($file) . "&page=" . $current_page);
    exit;
}

// Dapatkan URL lengkap file
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$full_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/" . $file;

// Untuk PPT/PPTX, gunakan fallback ke Microsoft Office Online Viewer
header('Content-Type: text/html');
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerPoint Viewer</title>
    <style>
        body, html { 
            margin: 0; 
            padding: 0; 
            height: 100%; 
            overflow: hidden;
            position: relative;
        }
        #ppt-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-family: Arial, sans-serif;
            z-index: 10;
        }
        .slide-notification {
            position: absolute;
            top: 10px;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 100;
        }
        .slide-badge {
            display: inline-block;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            transition: opacity 0.5s;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading" id="loadingIndicator">
        <div class="spinner"></div>
        <p>Memuat presentasi...</p>
    </div>
    
    <div class="slide-notification">
        <span class="slide-badge" id="slideNotification">Slide ' . $current_page . '</span>
    </div>
    
    <iframe id="ppt-viewer" 
            src="https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($full_url) . '" 
            frameborder="0" 
            onload="document.getElementById(\'loadingIndicator\').style.display=\'none\';">
    </iframe>
    
    <script>
        let currentPosition = ' . $current_page . ';
        
        // Mendengarkan pesan dari halaman induk
        window.addEventListener("message", function(event) {
            if (event.data && event.data.action === "setSlide") {
                currentPosition = event.data.slideNumber;
                document.getElementById("slideNotification").textContent = "Slide " + currentPosition;
                
                // Efek animasi saat ganti slide
                const notification = document.getElementById("slideNotification");
                notification.style.opacity = "1";
                notification.style.background = "rgba(0,0,0,0.9)";
                setTimeout(() => {
                    notification.style.background = "rgba(0,0,0,0.7)";
                }, 500);
            }
        });
        
        // Beri tahu parent bahwa viewer siap
        window.onload = function() {
            document.getElementById("loadingIndicator").style.display = "none";
            window.parent.postMessage({
                type: "viewerReady",
                currentSlide: currentPosition
            }, "*");
        };
    </script>
</body>
</html>';
?>