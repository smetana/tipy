<?php

//
//  +---------------------------------------------------------------+
//  | Main execution script. 
//  +---------------------------------------------------------------+
//  | Performs basic inital application tasks:
//  | - Creates application context (input, output, view, db, etc...)
//  | - Takes controller and controller method names from params
//  | - Creates controller object and executes it
//  +---------------------------------------------------------------+
//

// Start outbut bufer
//
ob_start();

// Set autoload classes function for framework
spl_autoload_register(function($className) {
    if (file_exists(__DIR__.'/'.$className.'.php')) {
        require_once $className.'.php';
        return;
    }
});

// Start Application
$app = Tipy::getInstance();

// read debug mode from config and load ErrorHendler
defined('DEBUG_MODE') || define('DEBUG_MODE', $app->config->get('debug_mode'));
require_once('ErrorHandler.php');

// load Autoload function
require_once('TipyAutoloader.php');

// Seed rnd
srand((double)microtime()*1000000);

// Run application
$app->run();

//
// Flush output bufer
//
ob_end_flush();

