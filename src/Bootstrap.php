<?php
/**
 *
 *  Load everything and start application
 *
 */

// Start outbut bufer
ob_start();

// Set autoload classes function for framework
spl_autoload_register(function($className) {
    if (file_exists(__DIR__.'/'.$className.'.php')) {
        require_once $className.'.php';
        return;
    }
});

require_once('ErrorHandler.php');

// load Autoload function
require_once('TipyAutoloader.php');

// Seed rnd
srand((double)microtime()*1000000);

// Run application
$app = TipyApp::getInstance();
$app->run();

// Flush output bufer
ob_end_flush();
