<?php
/**
 *
 *  Load tipy classes and start application
 *
 */

ob_start();

require_once('src/ErrorHandler.php');
require_once('src/TipyBinder.php');
require_once('src/TipyConfig.php');
require_once('src/TipyEnv.php');
require_once('src/TipyCookie.php');
require_once('src/TipyInput.php');
require_once('src/TipyOutput.php');
require_once('src/TipySession.php');
require_once('src/TipyMailer.php');
require_once('src/TipyDAO.php');
require_once('src/Inflector.php');
require_once('src/TipyModel.php');
require_once('src/TipyView.php');
require_once('src/TipyFlash.php');
require_once('src/TipyController.php');
require_once('src/TipyRouter.php');
require_once('src/TipyApp.php');
require_once('src/TipyAutoloader.php');

srand((double)microtime()*1000000);
$app = TipyApp::getInstance();
$app->run();

ob_end_flush();
