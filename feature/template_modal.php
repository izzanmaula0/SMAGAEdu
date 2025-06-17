    <!-- Modal Fitur Baru dengan Slide -->
    <div class="modal fade d-none" id="updateFeatureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <!-- Close button at the top -->
                <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px; z-index: 1050;" data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Indicators/Dots -->
                <div class="carousel-indicators position-relative mt-3" style="margin-bottom: 0;">
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#featureCarousel" data-bs-slide-to="2" aria-label="Slide 4"></button>
                </div>

                <!-- Carousel Content -->
                <div id="featureCarousel" class="carousel slide" data-bs-interval="false">
                    <div class="carousel-inner">
                        <!-- Slide 1 -->
                        <div class="carousel-item active p-4 pt-0 pb-0">
                            <div class="modal-body p-4 pt-0 pb-0">
                                <div class="border rounded-4 position-relative" style="height: 120px; overflow: hidden;">
                                    <img src="feature/update_header.png" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <h4 class="fw-bold mt-4" style="font-size: 1.8rem;">Pembaharuan Terbaru <span style="font-size: 10px;" class="text-muted p-1 rounded border">v 1.2</span></h4>
                                <p class="p-0 m-0 text-muted" style="font-size: 13px;">Yuk simak apa yang baru di SMAGAEdu, Anda dapat menekan tombol Selanjutnya di bawah</p>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item p-4 pt-0 pb-0">
                            <div class="modal-body p-4 pt-0 pb-0">
                                <div class="border rounded-4 position-relative" style="height: 120px; overflow: hidden;">
                                    <img src="feature/ui_update_ujian.png" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <h4 class="fw-bold mt-4" style="font-size: 1.8rem;">Antarmuka Yang Disederhanakan</h4>
                                <p class="p-0 m-0 text-muted" style="font-size: 13px;">
                                    Antarmuka ujian diperbarui dengan pengelompokan fitur di halaman Buat Soal agar tetap ringkas dan mudah digunakan.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item p-4 pt-0 pb-0">
                            <div class="modal-body p-4 pt-0 pb-0">
                                <div class="border rounded-4 position-relative" style="height: 120px; overflow: hidden;">
                                    <img src="feature/unduh_ujian.png" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <h4 class="fw-bold mt-4" style="font-size: 1.8rem;">Unduh Ujian</h4>
                                <p class="p-0 m-0 text-muted" style="font-size: 13px;">
                                    Kini Anda dapat mengunduh ujian di SMAGAEdu dalam format Word untuk memudahkan pengelolaan soal. <br><br>Buka Halaman Buat Soal Anda, klik tombol Ujian → Unduh Ujian untuk mendapatkan file-nya.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 4 -->
                        <div class="carousel-item p-4 pt-0 pb-0">
                            <div class="modal-body p-4 pt-0 pb-0">
                                <div class="border rounded-4 position-relative" style="height: 120px; overflow: hidden;">
                                    <img src="feature/preview.png" alt="Update Feature Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                                </div>
                                <h4 class="fw-bold mt-4" style="font-size: 1.8rem;">Pratinjau Ujian Anda</h4>
                                <p class="p-0 m-0 text-muted" style="font-size: 13px;">
                                    Fitur pratinjau kini tersedia untuk melihat bagaimana soal ditampilkan kepada siswa. Dapatkan gambaran langsung sebelum ujian dimulai. <br><br> Cukup klik tombol Pratinjau di halaman Buat Soal Anda. Tab baru pratinjau Ujian akan dibuka.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer with Checkbox and Navigation Buttons -->
                <div class="modal-footer border-0 mt-4 px-3">
                    <div class="d-flex align-items-center mb-3 w-100 ms-4">
                        <input class="form-check-input me-2 pt-0 mt-0" type="checkbox" id="dontShowUpdateModal">
                        <label class="form-check-label text-muted" for="dontShowUpdateModal" style="font-size: 13px;">
                            Centang kalau kamu ga mau pesan ini muncul lagi
                        </label>
                    </div>
                    <div class="d-flex w-100 gap-2">
                        <button type="button" class="btn border text-black flex-grow-1" id="prevButton" data-bs-target="#featureCarousel" data-bs-slide="prev" style="border-radius:15px;">Sebelumnya</button>
                        <button type="button" class="btn flex-grow-1" id="nextButton" data-bs-target="#featureCarousel" data-bs-slide="next" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px;">Selanjutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Script untuk menangani modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kunci unik untuk preferensi modal
            const modalKey = 'update_1.2';

            // Check if this is the first login session
            const isFirstLogin = <?php echo (isset($_SESSION['first_login']) && $_SESSION['first_login']) ? 'true' : 'false'; ?>;

            // Only show modal on first login or if explicitly requested by session
            <?php if (isset($_SESSION['show_update_modal']) && $_SESSION['show_update_modal']): ?>
                // Tampilkan modal dan hapus session flag
                showUpdateModal();
                <?php unset($_SESSION['show_update_modal']); ?>
            <?php else: ?>
                // Only check modal preference on first login
                if (isFirstLogin) {
                    checkModalPreference();
                    // Remove first login flag to prevent showing on subsequent refreshes
                    <?php $_SESSION['first_login'] = false; ?>
                }
            <?php endif; ?>

            // Fungsi untuk memeriksa apakah modal harus ditampilkan berdasarkan preferensi di database
            function checkModalPreference() {
                fetch('check_modal_preference.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `key=${modalKey}&userid=<?php echo $userid; ?>`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.hide) {
                            showUpdateModal();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking modal preference:', error);
                    });
            }

            // Fungsi untuk menampilkan modal dan mengatur semua event listeners
            function showUpdateModal() {
                const updateFeatureModal = new bootstrap.Modal(document.getElementById('updateFeatureModal'));
                updateFeatureModal.show();

                // Hide Previous button initially
                document.getElementById('prevButton').style.display = 'none';

                // Handle slide changes
                const carousel = document.getElementById('featureCarousel');
                const prevButton = document.getElementById('prevButton');
                const nextButton = document.getElementById('nextButton');

                carousel.addEventListener('slid.bs.carousel', function() {
                    // Get current active slide
                    const activeSlide = document.querySelector('.carousel-item.active');
                    const activeIndex = Array.from(document.querySelectorAll('.carousel-item')).indexOf(activeSlide);
                    const totalSlides = document.querySelectorAll('.carousel-item').length;

                    // Set Previous button visibility
                    prevButton.style.display = activeIndex === 0 ? 'none' : 'block';

                    // Change Next button to "Saya Mengerti" on last slide
                    if (activeIndex === totalSlides - 1) {
                        nextButton.textContent = 'Saya Mengerti';
                        nextButton.setAttribute('data-bs-dismiss', 'modal');
                        nextButton.removeAttribute('data-bs-target');
                        nextButton.removeAttribute('data-bs-slide');

                        // Add event listener for the last slide's button
                        nextButton.addEventListener('click', savePreference, {
                            once: true
                        });
                    } else {
                        nextButton.textContent = 'Selanjutnya';
                        nextButton.removeAttribute('data-bs-dismiss');
                        nextButton.setAttribute('data-bs-target', '#featureCarousel');
                        nextButton.setAttribute('data-bs-slide', 'next');
                    }
                });

                // Function to save preference
                function savePreference() {
                    const dontShowCheckbox = document.getElementById('dontShowUpdateModal');
                    if (dontShowCheckbox.checked) {
                        // Save preference to database with AJAX
                        fetch('save_modal_preference.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `key=${modalKey}&userid=<?php echo $userid; ?>`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Modal preference saved successfully');
                                }
                            })
                            .catch(error => {
                                console.error('Error saving modal preference:', error);
                            });
                    }
                }

                // Handle close button click
                const closeButton = document.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.addEventListener('click', savePreference);
                }

                // Enable swipe functionality for mobile
                let touchstartX = 0;
                let touchendX = 0;

                carousel.addEventListener('touchstart', function(e) {
                    touchstartX = e.changedTouches[0].screenX;
                }, false);

                carousel.addEventListener('touchend', function(e) {
                    touchendX = e.changedTouches[0].screenX;
                    handleSwipe();
                }, false);

                function handleSwipe() {
                    if (touchendX < touchstartX) {
                        // Swipe left, go to next slide
                        bootstrap.Carousel.getInstance(carousel).next();
                    }
                    if (touchendX > touchstartX) {
                        // Swipe right, go to previous slide
                        bootstrap.Carousel.getInstance(carousel).prev();
                    }
                }
            }
        });
    </script>