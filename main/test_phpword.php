<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "<br>";
echo "Testing PhpWord Library<br>";

// Cek autoload.php
$autoloadPath = 'vendor/autoload.php';
echo "Autoload path exists: " . (file_exists($autoloadPath) ? "YES" : "NO") . "<br>";

if (file_exists($autoloadPath)) {
    require $autoloadPath;
    
    // Cek file library utama
    $paths = [
        'vendor/phpoffice/phpword/src/PhpWord/IOFactory.php',
        'vendor/phpoffice/phpword/src/PhpWord/Element/TextRun.php',
        'vendor/phpoffice/phpword/src/PhpWord/Element/Text.php'
    ];
    
    foreach ($paths as $path) {
        echo "File $path exists: " . (file_exists($path) ? "YES" : "NO") . "<br>";
    }
    
    // Periksa namespace dan class
    $classes = [
        'PhpOffice\PhpWord\PhpWord',
        'PhpOffice\PhpWord\IOFactory',
        'PhpOffice\PhpWord\Element\TextRun',
        'PhpOffice\PhpWord\Element\Text'
    ];
    
    foreach ($classes as $class) {
        echo "Class $class exists: " . (class_exists($class) ? "YES" : "NO") . "<br>";
    }
    
    // Periksa autoloader
    echo "<br>Registered Autoloaders:<br>";
    $autoloaders = spl_autoload_functions();
    foreach ($autoloaders as $index => $autoloader) {
        if (is_array($autoloader)) {
            if (is_object($autoloader[0])) {
                echo "Autoloader " . ($index+1) . ": " . get_class($autoloader[0]) . "->" . $autoloader[1] . "<br>";
            } else {
                echo "Autoloader " . ($index+1) . ": " . $autoloader[0] . "::" . $autoloader[1] . "<br>";
            }
        } else {
            echo "Autoloader " . ($index+1) . ": " . $autoloader . "<br>";
        }
    }
} else {
    echo "Vendor autoload file tidak ditemukan!";
}