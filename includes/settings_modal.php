<?php
// settings_modal.php
?>

<style>
.setting-item {
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}
.setting-item:last-child {
    border-bottom: none;
}
.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    background-color: #e9ecef;
    border-color: #e9ecef;
}
.form-switch .form-check-input:checked {
    background-color: #da7756;
    border-color: #da7756;
}
.form-switch .form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(218, 119, 86, 0.25);
}

/* DARK MODE */
/* Style untuk dark mode */
.dark-mode {
    --bg-primary: #121212;
    --bg-secondary: #1e1e1e;
    --bg-tertiary: #2d2d2d;
    --text-primary: #ffffff;
    --text-secondary: #e0e0e0;
    --text-muted: #9e9e9e;
    --border-color: #333333;
    --accent-color: #da7756;
}

/* Background utama */
.dark-mode body, 
.dark-mode .container-fluid,
.dark-mode .col-inti {
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

/* Card dan container */
.dark-mode .bg-white,
.dark-mode .modal-content,
.dark-mode .card,
.dark-mode .navbar,
.dark-mode .dropdown-menu,
.dark-mode .postingan,
.dark-mode .catatanGuru,
.dark-mode .daftarSiswa,
.dark-mode .create-post-card {
    background-color: var(--bg-secondary) !important;
    color: var(--text-primary);
}

/* Border */
.dark-mode .modal-header,
.dark-mode .modal-footer,
.dark-mode .setting-item {
    border-color: var(--border-color);
}

.dark-mode .text-muted {
    color: var(--text-muted) !important;
}

.dark-mode .border,
.dark-mode .border-bottom,
.dark-mode .border-top {
    border-color: var(--border-color) !important;
}

/* Button */
.dark-mode .btn-light {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.dark-mode .btn-light:hover {
    background-color: #3a3a3a;
}

/* Form elements */
.dark-mode .form-control,
.dark-mode .form-select,
.dark-mode .bg-light {
    background-color: var(--bg-tertiary) !important;
    color: var(--text-primary);
    border-color: var(--border-color);
}

.dark-mode .form-control::placeholder {
    color: var(--text-muted);
}

/* Comment bubbles and student cards */
.dark-mode .comment-bubble,
.dark-mode .reply-bubble,
.dark-mode .info-card,
.dark-mode .student-card,
.dark-mode .catatan-item {
    background-color: var(--bg-tertiary) !important;
    border-color: var(--border-color) !important;
}

.dark-mode .dropdown-item:hover {
    background-color: var(--bg-tertiary);
}

/* Text color overrides */
.dark-mode .text-black,
.dark-mode .text-dark,
.dark-mode h1, .dark-mode h2, .dark-mode h3, 
.dark-mode h4, .dark-mode h5, .dark-mode h6 {
    color: var(--text-primary) !important;
}

/* Fixed components */
.dark-mode .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Shadow adjustments */
.dark-mode .shadow,
.dark-mode .shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.5) !important;
}

/* Sidebar */
.dark-mode .sidebar {
    background-color: var(--bg-secondary);
}

.dark-mode .sidebar .nav-link {
    color: var(--text-secondary);
}

.dark-mode .sidebar .nav-link:hover,
.dark-mode .sidebar .nav-link.active {
    background-color: var(--bg-tertiary);
}
</style>



<!-- Modal Pengaturan -->
<div class="modal fade" id="modal_pengaturan" tabindex="-1" aria-labelledby="label_pengaturan" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header d-flex justify-content-between align-items-center border-0 pb-0">
                <h1 class="modal-title fs-5 pb-3 fw-semibold" id="label_pengaturan">Pengaturan</h1>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="setting-item d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-normal">Mode Gelap</h6>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengaktifkan/menonaktifkan dark mode
function toggleDarkMode(isDark) {
    if (isDark) {
        document.documentElement.classList.add('dark-mode');
        document.getElementById('darkModeSwitch').checked = true;
    } else {
        document.documentElement.classList.remove('dark-mode');
        document.getElementById('darkModeSwitch').checked = false;
    }
    // Simpan preferensi ke localStorage
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
}

// Event listener untuk switch dark mode
document.addEventListener('DOMContentLoaded', function() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    
    // Periksa preferensi sebelumnya
    const darkMode = localStorage.getItem('darkMode');
    
    // Jika dark mode sebelumnya diaktifkan, terapkan dark mode
    if (darkMode === 'enabled') {
        toggleDarkMode(true);
    }
    
    // Tambahkan event listener untuk switch
    darkModeSwitch.addEventListener('change', function() {
        toggleDarkMode(this.checked);
    });
});
</script>

