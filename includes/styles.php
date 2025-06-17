<?php
// styles.php
?>
<style>
/* Menu Samping Styles */
.menu-samping {
    position: fixed;
    width: 13rem;
    z-index: 1000;
}
.menu-item {
    padding: 10px 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
    margin-bottom: 5px;
}
.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.9);
    transform: translateX(5px);
}
.menu-item.active {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.menu-text {
    font-size: 14px;
    margin: 0;
}
.menu-icon {
    width: 20px;
    margin-right: 12px;
    opacity: 0.8;
}

/* Mobile Navigation Styles */
.navbar.fixed-bottom {
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    height: 60px;
    padding-top: 5px;
}
.navbar.fixed-bottom .nav-link {
    color: #666;
    transition: all 0.3s ease;
}
.navbar.fixed-bottom .nav-link.active {
    color: #da7756;
}
.navbar.fixed-bottom .bi {
    font-size: 1.3rem;
    margin-bottom: 2px;
}
.nav-label {
    font-size: 0.7rem;
    display: block;
    margin-top: -2px;
}



/* Dark Mode Styles */
.dark-mode {
    background-color: #1a1a1a !important;
    color: #ffffff !important;
}
.dark-mode p,
.dark-mode h1,
.dark-mode h2, 
.dark-mode h3,
.dark-mode h4,
.dark-mode h5,
.dark-mode h6,
.dark-mode span {
    color: #ffffff !important;
}
.dark-mode .card,
.dark-mode .modal-content,
.dark-mode .class-card,
.dark-mode .menu-samping {
    background-color: #2d2d2d !important;
    color: #ffffff !important;
}
.dark-mode .text-muted {
    color: #bbbbbb !important;
}
.dark-mode .border {
    border-color: #404040 !important;
}
.dark-mode .btn-light {
    background-color: #404040 !important;
    color: #ffffff !important;
    border-color: #404040 !important;
}

/* Responsive Adjustments */
@media (max-width: 767px) {
    body {
        padding-bottom: 65px;
        padding-top: 0;
    }
}
</style>