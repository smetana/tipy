<?php 

// Set autoload classes function
spl_autoload_register(function($class_name) {
    $folders = array('/models', '/controllers', '/helpers');
    $app = TipyApp::getInstance();
    foreach ($folders as $folder) {
        $file = $app->config->get('application_path') . '/' . $folder . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
