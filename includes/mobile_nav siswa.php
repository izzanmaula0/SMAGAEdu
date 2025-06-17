<!-- Bottom Navigation for Mobile -->
<nav class="navbar fixed-bottom d-md-none bg-white border-top" style="padding-bottom: env(safe-area-inset-bottom);">
    <div class="container-fluid px-0">
        <div class="row w-100 mx-0">
            <?php
            $nav_items = [
                ['url' => 'beranda.php', 'icon' => 'house', 'text' => ''],
                ['url' => 'ujian.php', 'icon' => 'clipboard', 'text' => ''],
                ['url' => 'ai.php', 'icon' => 'stars', 'text' => ''],
                ['url' => 'profil.php', 'icon' => 'person-circle', 'text' => '']
            ];

            $current_page = basename($_SERVER['PHP_SELF']);

            foreach($nav_items as $item) {
                $is_active = ($current_page === $item['url']) ? 'active' : '';
                ?>
                <div class="col text-center">
                    <?php if(isset($item['is_menu'])): ?>
                        <button class="nav-link btn w-100 py-1" type="button" data-bs-toggle="modal" data-bs-target="#bottomSheetMenu">
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

<!-- Bottom Sheet Menu Modal -->
<div class="modal fade" id="bottomSheetMenu" tabindex="-1" aria-labelledby="bottomSheetMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-bottom">
        <div class="modal-content">
            <div class="drag-handle">
                <div class="drag-handle-indicator"></div>
            </div>
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="bottomSheetMenuLabel">Menu Lainnya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="raport_pg.php" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center py-3">
                            <i class="bi bi-journal-text text-muted"></i>
                            <div class="ms-3">
                                <div class="fw-medium">Raport P. Guidance</div>
                                <small class="text-muted">Lihat raport perkembangan siswa</small>
                            </div>
                        </div>
                    </a>
                    
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

