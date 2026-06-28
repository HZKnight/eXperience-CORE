<?php
    /**
     * Bootstrap per i test PHPUnit
     */

    // Definisce la root del progetto basandosi sulla posizione di questo file
    $baseDir = dirname(__DIR__);

    // Includi il tuo autoloader personalizzato usando il percorso assoluto calcolato
    if (file_exists($baseDir . '/src/eautoloader.class.php')) {
        require_once $baseDir . '/src/eautoloader.class.php';
        echo "Autoloader personalizzato incluso da: $baseDir/src/eautoloader.class.php\n";
    } else {
        // Se usi anche Composer come fallback/integrazione
        if (file_exists($baseDir . '/vendor/autoload.php')) {
            require_once $baseDir . '/vendor/autoload.php';
            echo "Autoloader di Composer incluso da: $baseDir/vendor/autoload.php\n";
        } else {
            throw new RuntimeException("Impossibile trovare un autoloader valido (eautoloader.class.php o vendor/autoload.php).");
        }
    }
