<?php
$is_guru = $_SESSION['level'] == 'guru';

// Define nav items based on role
$nav_items = $is_guru ? [
   ['url' => 'beranda_guru.php', 'icon' => 'house', 'text' => 'Beranda'],
   ['url' => 'ujian_guru.php', 'icon' => 'clipboard', 'text' => 'Ujian'], 
   ['url' => 'ai_guru.php', 'icon' => 'stars', 'text' => 'SAGA'],
   ['url' => '#', 'icon' => 'person', 'text' => 'Profil', 'is_menu' => true, 'modal' => '#pengaturanModal']
] : [
   ['url' => 'beranda.php', 'icon' => 'house', 'text' => 'Beranda'],
   ['url' => 'ujian.php', 'icon' => 'clipboard', 'text' => 'Ujian'],
   ['url' => 'ai.php', 'icon' => 'stars', 'text' => 'SAGA AI'],
   ['url' => '#', 'icon' => 'gear', 'text' => 'Pengaturan', 'is_menu' => true, 'modal' => '#pengaturanModal'],
   ['url' => 'profil.php', 'icon' => 'person-circle', 'text' => 'Profil']
];
?>

<!-- Bottom Navigation -->
<nav class="navbar fixed-bottom d-md-none bg-white border-top" style="padding-bottom: env(safe-area-inset-bottom);">
   <div class="container-fluid px-0">
       <div class="row w-100 mx-0">
           <?php
           $current_page = basename($_SERVER['PHP_SELF']);
           foreach($nav_items as $item) {
               $is_active = ($current_page === $item['url']) ? 'active' : '';
               ?>
               <div class="col text-center">
                   <?php if(isset($item['is_menu'])): ?>
                       <button class="nav-link btn w-100 py-1" type="button" data-bs-toggle="modal" data-bs-target="<?= $item['modal'] ?>">
                           <i class="bi bi-<?= $item['icon'] ?> d-block"></i>
                           <small class="nav-label"><?= $item['text'] ?></small>
                       </button>
                   <?php else: ?>
                       <a href="<?= $item['url'] ?>" class="nav-link <?= $is_active ?> py-1">
                           <i class="bi bi-<?= $item['icon'] ?> d-block"></i>
                           <small class="nav-label"><?= $item['text'] ?></small>
                       </a>
                   <?php endif; ?>
               </div>
           <?php } ?>
       </div>
   </div>
</nav>

<!-- Bottom Sheet Menu (Original) -->
<div class="modal fade" id="bottomSheetMenu" tabindex="-1">
   <div class="modal-dialog modal-dialog-bottom">
       <div class="modal-content">
           <div class="drag-handle">
               <div class="drag-handle-indicator"></div>
           </div>
           
           <div class="modal-header border-0 pb-0">
               <h5 class="modal-title fw-bold">Menu Lainnya</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
           </div>
           
           <div class="modal-body p-0">
               <div class="list-group list-group-flush">
                   <?php if($is_guru): ?>
                       <a href="raport_pg.php" class="list-group-item list-group-item-action">
                           <div class="d-flex align-items-center py-3">
                               <i class="bi bi-journal-text text-muted"></i>
                               <div class="ms-3">
                                   <div class="fw-medium">Raport P. Guidance</div>
                                   <small class="text-muted">Lihat raport perkembangan siswa</small>
                               </div>
                           </div>
                       </a>
                   <?php endif; ?>
                   
                   <a href="bantuan.php" class="list-group-item list-group-item-action">
                       <div class="d-flex align-items-center py-3">
                           <i class="bi bi-question-circle text-muted"></i>
                           <div class="ms-3">
                               <div class="fw-medium">Bantuan</div>
                               <small class="text-muted">Pusat bantuan dan panduan</small>
                           </div>
                       </div>
                   </a>
                   
                   <a href="logout.php" class="list-group-item list-group-item-action">
                       <div class="d-flex align-items-center py-3">
                           <i class="bi bi-box-arrow-right text-danger"></i>
                           <div class="ms-3">
                               <div class="fw-medium text-danger">Keluar</div>
                               <small class="text-danger-emphasis">Keluar dari akun anda</small>
                           </div>
                       </div>
                   </a>
               </div>
           </div>
       </div>
   </div>
</div>

<!-- Pengaturan Modal (iOS Style) -->
<div class="modal fade" id="pengaturanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-top-4">
            <div class="drag-handle mb-2">
                <div class="drag-handle-indicator"></div>
            </div>
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">Pengaturan</h5>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= $is_guru ? 'profil_guru.php' : 'profil.php' ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center py-3">
                            <div class="icon-container rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-person-circle" style="color: rgb(218, 119, 86);"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="fw-medium">Profil</div>
                                <small class="text-secondary">Lihat dan edit profil anda</small>
                            </div>
                            <i class="bi bi-chevron-right text-secondary"></i>
                        </div>
                    </a>
                    <!-- <div class="list-group-item">
                        <div class="d-flex align-items-center py-3">
                            <div class="icon-container bg-secondary-subtle rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-moon-stars text-secondary"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="fw-medium">Mode Gelap <span class="badge ms-2 fs-7 rounded-pill" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">BETA</span></div>
                                <small class="text-secondary">Ubah tampilan aplikasi</small>
                            </div> 
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="darkModeSwitch">
                            </div>
                        </div>
                    </div> -->
                    <a href="logout.php" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center py-3">
                            <div class="icon-container bg-danger-subtle rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="fw-medium text-danger">Keluar</div>
                                <small class="text-danger-emphasis">Keluar dari akun anda</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center mb-2">
                <button type="button" class="btn btn-sm flex-fill py-2 px-3" data-bs-dismiss="modal" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px">Tutup</button>
            </div>
        </div>
    </div>
</div>