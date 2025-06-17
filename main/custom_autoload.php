<?php
// Custom autoloader untuk PhpOffice/PhpWord
spl_autoload_register(function ($class) {
    // Cek jika class termasuk namespace PhpOffice\PhpWord
    if (strpos($class, 'PhpOffice\\PhpWord\\') === 0) {
        // Ubah namespace ke path file
        $path = str_replace('\\', '/', $class);
        $path = 'vendor/' . strtolower($path) . '.php';
        
        // Ubah path untuk folder src
        $path = str_replace('vendor/phpoffice/phpword/', 'vendor/phpoffice/phpword/src/', $path);
        
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    return false;
});

// Load autoloader asli jika ada
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}