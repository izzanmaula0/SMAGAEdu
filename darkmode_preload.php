<?php
// darkmode-preload.php - include file ini di bagian <head> setiap halaman
?>
<!-- Preload Dark Mode CSS -->
<style>
/* CSS untuk preload dark mode */
.darkmode-preload {
  background-color: #222 !important;
  color: #eee !important;
}

.darkmode-preload * {
  transition: none !important;
}

/* Hapus class preload saat DOM selesai dimuat */
html.darkmode-transition * {
  transition: background-color 0.2s ease, color 0.2s ease !important;
}

/* Beberapa aturan dasar agar pengalaman dark mode konsisten */
.darkmode-preload body,
.darkmode-preload .menu-samping,
.darkmode-preload .col-utama,
.darkmode-preload .card,
.darkmode-preload .modal-content,
.darkmode-preload .class-card,
.darkmode-preload .notification-card,
.darkmode-preload .custom-card,
.darkmode-preload .bg-white,
.darkmode-preload .bg-light {
  background-color: #222 !important;
}

.darkmode-preload .text-dark,
.darkmode-preload .text-black,
.darkmode-preload h1,
.darkmode-preload h2,
.darkmode-preload h3,
.darkmode-preload h4,
.darkmode-preload h5,
.darkmode-preload h6,
.darkmode-preload p:not(.text-muted),
.darkmode-preload label,
.darkmode-preload .fw-bold,
.darkmode-preload .class-title {
  color: #eee !important;
}

.darkmode-preload .text-muted {
  color: #aaa !important;
}

.darkmode-preload .border,
.darkmode-preload .card,
.darkmode-preload .btn {
  border-color: #444 !important;
}

.darkmode-preload .loading-screen {
  background-color: #222 !important;
}
</style>

<!-- Preload Dark Mode Script -->
<script>
// Preload script - dijalankan sebelum DOM dibangun
(function() {
  // Periksa localStorage untuk status dark mode
  if (localStorage.getItem('darkmode') === 'true') {
    // Jika dark mode aktif, segera tambahkan class
    document.documentElement.classList.add('darkmode-preload');
  }
})();
</script>