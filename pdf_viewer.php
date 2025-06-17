<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    die("Unauthorized");
}

// Ambil parameter
$file = isset($_GET['file']) ? $_GET['file'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$role = isset($_GET['role']) ? $_GET['role'] : 'student'; // 'teacher' atau 'student'

if (empty($file)) {
    die("File not specified");
}

// Dapatkan URL lengkap file
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$file_url = $protocol . "://" . $host . "/" . $file;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <title>PDF Viewer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        #pdfContainer {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #525659;
            position: relative;
        }

        #viewportContainer {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        #pdfCanvas {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            max-height: 95%;
            max-width: 95%;
            position: absolute;
        }

        #annotationCanvas {
            position: absolute;
            pointer-events: none;
            /* Hanya aktif saat drawing mode */
            z-index: 10;
        }

        #pageInfo {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            z-index: 20;
        }

        #toolbar {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            z-index: 20;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .tool-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .tool-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .tool-button.active {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .separator {
            width: 1px;
            height: 20px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        #loadingIndicator {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 100;
            color: white;
        }

        .spinner {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        #colorPicker {
            display: flex;
            gap: 5px;
        }

        .color-option {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .color-option.active {
            border-color: white;
        }

        .pen-size-option {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pen-size-option.active {
            background-color: #4dabf7;
        }

        .pen-size-circle {
            border-radius: 50%;
            background-color: black;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        <?php if ($role === 'student'): ?>

        /* Sembunyikan kontrol khusus guru untuk siswa */
        .teacher-only {
            display: none !important;
        }

        #annotationCanvas.view-only {
            pointer-events: none;
        }

        <?php endif; ?>
    </style>
</head>

<body>
    <div id="loadingIndicator">
        <div class="spinner"></div>
        <p>Loading PDF...</p>
    </div>

    <div id="pdfContainer">
        <div id="viewportContainer">
            <canvas id="pdfCanvas"></canvas>
            <canvas id="annotationCanvas" class="<?php echo $role === 'student' ? 'view-only' : ''; ?>"></canvas>
        </div>

        <div id="toolbar">
            <!-- Zoom controls -->
            <div style="display: none;">
                <button id="zoomOut" class="tool-button" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                <button id="zoomReset" class="tool-button" title="Reset Zoom"><i class="fas fa-sync-alt"></i></button>
                <button id="zoomIn" class="tool-button" title="Zoom In"><i class="fas fa-search-plus"></i></button>

                <div class="separator"></div>
            </div>


            <!-- Drawing tools - hanya untuk guru -->
            <button id="toggleDrawing" class="tool-button teacher-only" title="Toggle Drawing"><i class="fas fa-pencil-alt"></i></button>
            <button id="eraser" class="tool-button teacher-only" title="Eraser"><i class="fas fa-eraser"></i></button>
            <button id="clearAnnotations" class="tool-button teacher-only" title="Clear Annotations"><i class="fas fa-trash-alt"></i></button>

            <div class="separator teacher-only"></div>

            <!-- Color picker - hanya untuk guru -->
            <div id="colorPicker" class="teacher-only">
                <div class="color-option active" data-color="#FF0000" style="background-color: #FF0000;"></div>
                <div class="color-option" data-color="#00FF00" style="background-color: #00FF00;"></div>
                <div class="color-option" data-color="#0000FF" style="background-color: #0000FF;"></div>
                <div class="color-option" data-color="#FFFF00" style="background-color: #FFFF00;"></div>
                <div class="color-option" data-color="#FF00FF" style="background-color: #FF00FF;"></div>
            </div>

            <div class="separator teacher-only"></div>

            <!-- Pen size - hanya untuk guru -->
            <div id="penSizePicker" class="teacher-only">
                <div class="pen-size-option active" data-size="2">
                    <div class="pen-size-circle" style="width: 2px; height: 2px;"></div>
                </div>
                <div class="pen-size-option" data-size="5">
                    <div class="pen-size-circle" style="width: 5px; height: 5px;"></div>
                </div>
                <div class="pen-size-option" data-size="10">
                    <div class="pen-size-circle" style="width: 10px; height: 10px;"></div>
                </div>
            </div>
        </div>

        <div id="pageInfo">Page 1 of 1</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.5.141/pdf.min.js"></script>
    <script>
        // Konfigurasi PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.5.141/pdf.worker.min.js';

        // Parameter dari URL
        const pdfUrl = '<?php echo $file_url; ?>';
        let currentPage = <?php echo $page; ?>;
        let totalPages = 0;
        let pdfDoc = null;
        let rendering = false;
        let scale = 1.5;
        const originalScale = 1.5;
        const role = '<?php echo $role; ?>';

        // Drawing state
        let isDrawing = false;
        let isDrawingEnabled = false;
        let isErasing = false;
        let currentColor = '#FF0000';
        let currentPenSize = 2;
        let annotations = {}; // Object to store annotations for each page
        let currentAnnotations = []; // Current page annotations
        let lastX, lastY;

        // Elemen DOM
        const pdfCanvas = document.getElementById('pdfCanvas');
        const pdfCtx = pdfCanvas.getContext('2d');
        const annotationCanvas = document.getElementById('annotationCanvas');
        const annotCtx = annotationCanvas.getContext('2d');
        const pageInfo = document.getElementById('pageInfo');
        const loadingIndicator = document.getElementById('loadingIndicator');

        // Zoom Controls
        const zoomInBtn = document.getElementById('zoomIn');
        const zoomOutBtn = document.getElementById('zoomOut');
        const zoomResetBtn = document.getElementById('zoomReset');

        // Drawing Controls
        const toggleDrawingBtn = document.getElementById('toggleDrawing');
        const eraserBtn = document.getElementById('eraser');
        const clearAnnotationsBtn = document.getElementById('clearAnnotations');
        const colorOptions = document.querySelectorAll('.color-option');
        const penSizeOptions = document.querySelectorAll('.pen-size-option');

        // Inisialisasi
        function init() {
            // Set up zoom controls
            zoomInBtn.addEventListener('click', zoomIn);
            zoomOutBtn.addEventListener('click', zoomOut);
            zoomResetBtn.addEventListener('click', resetZoom);

            if (role === 'teacher') {
                // Set up drawing controls
                toggleDrawingBtn.addEventListener('click', toggleDrawing);
                eraserBtn.addEventListener('click', toggleEraser);
                clearAnnotationsBtn.addEventListener('click', clearAnnotations);

                // Set up color picker
                colorOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        currentColor = this.getAttribute('data-color');

                        // Update active state
                        colorOptions.forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');

                        // Disable eraser mode
                        isErasing = false;
                        eraserBtn.classList.remove('active');
                    });
                });

                // Set up pen size picker
                penSizeOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        currentPenSize = parseInt(this.getAttribute('data-size'));

                        // Update active state
                        penSizeOptions.forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                    });
                });

                // Set up drawing events
                annotationCanvas.addEventListener('mousedown', startDrawing);
                annotationCanvas.addEventListener('mousemove', draw);
                annotationCanvas.addEventListener('mouseup', stopDrawing);
                annotationCanvas.addEventListener('mouseout', stopDrawing);

                // Touch support for drawing
                annotationCanvas.addEventListener('touchstart', handleTouchStart);
                annotationCanvas.addEventListener('touchmove', handleTouchMove);
                annotationCanvas.addEventListener('touchend', handleTouchEnd);
            }

            // Load PDF
            loadPDF();
        }

        // Load PDF document
        function loadPDF() {
            pdfjsLib.getDocument(pdfUrl).promise.then(function(doc) {
                pdfDoc = doc;
                totalPages = pdfDoc.numPages;

                if (currentPage > totalPages) {
                    currentPage = 1;
                }

                renderPage(currentPage);

                // Beri tahu parent bahwa viewer siap
                window.parent.postMessage({
                    type: 'viewerReady',
                    totalPages: totalPages,
                    currentPage: currentPage
                }, '*');

            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                loadingIndicator.innerHTML = '<p>Error loading PDF: ' + error.message + '</p>';
            });
        }

        // Render halaman PDF
        function renderPage(pageNumber) {
            rendering = true;

            pdfDoc.getPage(pageNumber).then(function(page) {
                // Sesuaikan skala berdasarkan viewport
                const viewport = page.getViewport({
                    scale: scale
                });

                // Ukuran canvas
                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;

                // Sesuaikan canvas anotasi
                annotationCanvas.width = viewport.width;
                annotationCanvas.height = viewport.height;

                // Render PDF ke canvas
                const renderContext = {
                    canvasContext: pdfCtx,
                    viewport: viewport
                };

                const renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    rendering = false;
                    loadingIndicator.style.display = 'none';

                    // Update info halaman
                    pageInfo.textContent = `Page ${pageNumber} of ${totalPages}`;

                    // Muat anotasi untuk halaman ini jika ada
                    loadAnnotations(pageNumber);

                    // Beri tahu parent bahwa halaman sudah dirender dan kirimkan skala saat ini
                    window.parent.postMessage({
                        type: 'pageRendered',
                        currentPage: pageNumber,
                        totalPages: totalPages,
                        scale: scale
                    }, '*');
                });
            });
        }



        // Tambahkan listener khusus untuk zoom controls
        document.addEventListener('DOMContentLoaded', function() {
            // Setup zoom controls
            const zoomInBtn = document.getElementById('zoomIn');
            const zoomOutBtn = document.getElementById('zoomOut');
            const zoomResetBtn = document.getElementById('zoomReset');

            if (zoomInBtn) {
                zoomInBtn.addEventListener('click', function() {
                    zoomIn();
                    logToConsole("Zoom in clicked, new scale: " + scale);
                });
            }

            if (zoomOutBtn) {
                zoomOutBtn.addEventListener('click', function() {
                    zoomOut();
                    logToConsole("Zoom out clicked, new scale: " + scale);
                });
            }

            if (zoomResetBtn) {
                zoomResetBtn.addEventListener('click', function() {
                    resetZoom();
                    logToConsole("Zoom reset clicked, new scale: " + scale);
                });
            }
        });

        // Perbaiki fungsi zoomIn, zoomOut, resetZoom
        function zoomIn() {
            if (scale < 3.0) {
                scale += 0.25;
                renderPage(currentPage);
                logToConsole("Zoom changed to " + scale);
                broadcastZoomChange();
            }
        }

        function zoomOut() {
            if (scale > 0.5) {
                scale -= 0.25;
                renderPage(currentPage);
                logToConsole("Zoom changed to " + scale);
                broadcastZoomChange();
            }
        }

        function resetZoom() {
            scale = originalScale;
            renderPage(currentPage);
            logToConsole("Zoom reset to " + scale);
            broadcastZoomChange();
        }

        // Drawing functions
        function toggleDrawing() {
            isDrawingEnabled = !isDrawingEnabled;
            toggleDrawingBtn.classList.toggle('active', isDrawingEnabled);

            // Update cursor and annotation canvas
            if (isDrawingEnabled) {
                annotationCanvas.style.pointerEvents = 'auto';
                annotationCanvas.style.cursor = 'crosshair';
                isErasing = false;
                eraserBtn.classList.remove('active');
            } else {
                annotationCanvas.style.pointerEvents = 'none';
                annotationCanvas.style.cursor = 'default';
            }
        }

        function toggleEraser() {
            if (isDrawingEnabled) {
                isErasing = !isErasing;
                eraserBtn.classList.toggle('active', isErasing);
            } else {
                isDrawingEnabled = true;
                isErasing = true;
                toggleDrawingBtn.classList.add('active');
                eraserBtn.classList.add('active');
                annotationCanvas.style.pointerEvents = 'auto';
                annotationCanvas.style.cursor = 'crosshair';
            }
        }

        function startDrawing(e) {
            if (!isDrawingEnabled) return;

            isDrawing = true;
            const {
                offsetX,
                offsetY
            } = getCoordinates(e);
            lastX = offsetX;
            lastY = offsetY;
        }

        // Update untuk menyimpan anotasi relatif terhadap lebar/tinggi canvas, bukan koordinat absolut
        function draw(e) {
            if (!isDrawing || !isDrawingEnabled) return;

            const {
                offsetX,
                offsetY
            } = getCoordinates(e);

            annotCtx.beginPath();
            annotCtx.moveTo(lastX, lastY);
            annotCtx.lineTo(offsetX, offsetY);

            if (isErasing) {
                annotCtx.globalCompositeOperation = 'destination-out';
                annotCtx.lineWidth = currentPenSize * 5; // Thicker for eraser
            } else {
                annotCtx.globalCompositeOperation = 'source-over';
                annotCtx.strokeStyle = currentColor;
                annotCtx.lineWidth = currentPenSize;
            }

            annotCtx.lineCap = 'round';
            annotCtx.lineJoin = 'round';
            annotCtx.stroke();

            // Simpan stroke dengan koordinat relatif
            const relativeStartX = lastX / annotationCanvas.width;
            const relativeStartY = lastY / annotationCanvas.height;
            const relativeEndX = offsetX / annotationCanvas.width;
            const relativeEndY = offsetY / annotationCanvas.height;

            currentAnnotations.push({
                type: isErasing ? 'eraser' : 'stroke',
                startX: relativeStartX,
                startY: relativeStartY,
                endX: relativeEndX,
                endY: relativeEndY,
                color: currentColor,
                lineWidth: isErasing ? currentPenSize * 5 : currentPenSize,
                currentScale: scale
            });

            lastX = offsetX;
            lastY = offsetY;

            // Broadcast perubahan anotasi
            if (role === 'teacher') {
                broadcastAnnotationChange();
            }
        }

        function stopDrawing() {
            isDrawing = false;

            // Save annotations for current page
            if (currentAnnotations.length > 0) {
                annotations[currentPage] = [...currentAnnotations];
            }
        }

        function clearAnnotations() {
            // Clear canvas
            annotCtx.clearRect(0, 0, annotationCanvas.width, annotationCanvas.height);

            // Clear annotations for current page
            currentAnnotations = [];
            annotations[currentPage] = [];

            // Broadcast cleared annotations
            if (role === 'teacher') {
                console.log("Mengirim array anotasi kosong untuk clearAnnotations");
                broadcastAnnotationChange();
            }
        }

        // Touch handling
        function handleTouchStart(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            annotationCanvas.dispatchEvent(mouseEvent);
        }

        function handleTouchMove(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            annotationCanvas.dispatchEvent(mouseEvent);
        }

        function handleTouchEnd(e) {
            e.preventDefault();
            const mouseEvent = new MouseEvent('mouseup', {});
            annotationCanvas.dispatchEvent(mouseEvent);
        }

        // Helper to get coordinates for both mouse and touch
        function getCoordinates(e) {
            const rect = annotationCanvas.getBoundingClientRect();
            let offsetX, offsetY;

            if (e.type.startsWith('touch')) {
                offsetX = e.touches[0].clientX - rect.left;
                offsetY = e.touches[0].clientY - rect.top;
            } else {
                offsetX = e.offsetX;
                offsetY = e.offsetY;
            }

            return {
                offsetX,
                offsetY
            };
        }

        // Update fungsi loadAnnotations untuk menangani koordinat relatif
        function loadAnnotations(pageNumber) {
            // Bersihkan canvas
            annotCtx.clearRect(0, 0, annotationCanvas.width, annotationCanvas.height);

            // Muat anotasi untuk halaman ini jika ada
            currentAnnotations = annotations[pageNumber] || [];

            // Gambar semua anotasi
            currentAnnotations.forEach(anno => {
                annotCtx.beginPath();

                let startX, startY, endX, endY;

                // Periksa apakah ini koordinat relatif atau absolut
                if (anno.startX <= 1 && anno.endX <= 1) {
                    // Koordinat relatif - konversi ke koordinat absolut
                    startX = anno.startX * annotationCanvas.width;
                    startY = anno.startY * annotationCanvas.height;
                    endX = anno.endX * annotationCanvas.width;
                    endY = anno.endY * annotationCanvas.height;
                } else {
                    // Koordinat absolut (Untuk kompatibilitas dengan anotasi lama)
                    startX = anno.startX;
                    startY = anno.startY;
                    endX = anno.endX;
                    endY = anno.endY;
                }

                annotCtx.moveTo(startX, startY);
                annotCtx.lineTo(endX, endY);

                if (anno.type === 'eraser') {
                    annotCtx.globalCompositeOperation = 'destination-out';
                } else {
                    annotCtx.globalCompositeOperation = 'source-over';
                    annotCtx.strokeStyle = anno.color;
                }

                // Sesuaikan ketebalan garis jika ada informasi skala
                let lineWidth = anno.lineWidth;
                if (anno.currentScale) {
                    const scaleRatio = scale / anno.currentScale;
                    lineWidth = lineWidth * scaleRatio;
                }

                annotCtx.lineWidth = lineWidth;
                annotCtx.lineCap = 'round';
                annotCtx.lineJoin = 'round';
                annotCtx.stroke();
            });

            // Reset composite operation
            annotCtx.globalCompositeOperation = 'source-over';
        }

        // Perbaiki fungsi broadcastZoomChange
        function broadcastZoomChange() {
            if (role === 'teacher') {
                logToConsole("Broadcasting zoom change: " + scale);
                window.parent.postMessage({
                    type: 'zoomChanged',
                    scale: scale
                }, '*');
            }
        }

        function broadcastAnnotationChange() {
            if (role === 'teacher') {
                window.parent.postMessage({
                    type: 'annotationChanged',
                    page: currentPage,
                    annotations: currentAnnotations
                }, '*');
            }
        }

        // Receive and apply annotations from teacher
        function applyTeacherAnnotations(annotationsData) {
            if (role === 'student') {
                currentAnnotations = annotationsData;
                annotations[currentPage] = annotationsData;
                loadAnnotations(currentPage);
            }
        }
        // Dan pindahkan log newScale ke dalam fungsi applyTeacherZoom
        function applyTeacherZoom(newScale) {
            if (role === 'student' && newScale !== scale) {
                logToConsole(`Applying received zoom: ${newScale}`); // Pindahkan ke sini
                scale = newScale;
                renderPage(currentPage);
            }
        }

        // Terima pesan dari parent window
        // Perbaiki event listener untuk menerima perintah zoom dari parent
        window.addEventListener('message', function(event) {
            if (event.data && event.data.action === 'setPage') {
                const pageNumber = event.data.pageNumber;
                if (pageNumber >= 1 && pageNumber <= totalPages && pageNumber !== currentPage) {
                    currentPage = pageNumber;
                    loadingIndicator.style.display = 'flex';
                    renderPage(currentPage);
                }
            } else if (event.data && event.data.action === 'setZoom') {
                const zoomScale = parseFloat(event.data.scale);
                logToConsole("Menerima permintaan zoom: " + zoomScale);

                if (!isNaN(zoomScale) && zoomScale > 0) {
                    scale = zoomScale;
                    logToConsole("Menerapkan zoom: " + scale);
                    loadingIndicator.style.display = 'flex';
                    renderPage(currentPage);
                }
            } else if (event.data && event.data.action === 'setAnnotations') {
                if (event.data.annotations) {
                    logToConsole("Menerima anotasi baru");
                    applyTeacherAnnotations(event.data.annotations);
                }
            }
        });

        // Tambahkan fungsi logging ini untuk debugging
        // Modifikasi fungsi logToConsole di pdf_viewer.php
        function logToConsole(message) {
            if (window.console && console.log) {
                console.log('[PDF VIEWER] ' + message);
            }

            // Kirim log ke parent window
            try {
                window.parent.postMessage({
                    type: 'debugLog',
                    message: message
                }, '*');
            } catch (e) {
                console.error('Error sending log to parent:', e);
            }
        }

        // Panggil di tempat-tempat penting
        logToConsole(`Zoom changed to ${scale}`);

        // Inisialisasi saat DOM loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>

</html>