<?php

// Set autoload classes function
spl_autoload_register(function ($className) {
    $fileName = __DIR__.'/models/'.$className.'.php';
    if (file_exists($fileName)) {
        require_once $fileName;
        return;
    }
});
