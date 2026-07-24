<?php
/**
 * Bootstrap per i test PHPUnit in /test/
 */

// La root del progetto è un livello sopra questa cartella
$baseDir = dirname(__DIR__);

// Carica l'autoloader del tuo framework
if (file_exists($baseDir . '/src/eautoloader.class.php')) {
    echo "Caricamento autoloader di eXperience-CORE...\n";
    require_once $baseDir . '/src/eautoloader.class.php';
}

// Carica l'autoloader di Composer (fondamentale per PHPUnit e i Mock)
if (file_exists($baseDir . '/vendor/autoload.php')) {
    echo "Caricamento autoloader di Composer...\n";
    require_once $baseDir . '/vendor/autoload.php';
}
