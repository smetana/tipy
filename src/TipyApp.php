<?php

// ==================================================================
// Application
// Class to store Application context
// ==================================================================
class TipyApp {

    private static $instance = null;

    public $config;
    public $env;
    public $cookie;
    public $db;
    public $in;
    public $out;
    public $view;
    public $session;
    public $documentRoot;

    private function __clone() {}
    private function __wakeup() {}
    private function __construct() {
        $this->config  = new TipyConfig();
        $this->request = new TipyRequest();
        $this->env     = new TipyEnv();
        $this->cookie  = new TipyCookie();
        $this->in      = new TipyInput();
        $this->out     = new TipyOutput();
        $this->view    = new TipyView();
        $this->db      = null; // Lazy database connection
        if (CLI_MODE) {
            $this->session = new TipyCliSession();
        } else {
            $this->session = new TipySession();
        }
        // dispatcher.php is called by Apache with current working dir set to DocumentRoot
        $cwd = getcwd();
        $this->documentRoot = $cwd;
        $this->view->setTemplatePath(realpath($cwd.'/../app/views'));
    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function connectToDb() {
        new TipyDAO();
    }

    public function run() {
        try {
            // Get controller and action name
            $controllerName = $this->in->get('controller');
            $actionName     = $this->in->get('action');

            // Some basic checking
            if (!$controllerName || preg_match('/^\W*$/', $controllerName)) {
                throw new TipyException('Controller name is missing');
            }
            if (!$actionName || preg_match('/^\W*$/', $actionName)) {
                throw new TipyException('Action name is missing');
            }

            $controllerName = TipyInflector::controllerize($controllerName).'Controller';
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
            } else {
                throw new TipyException('Unable to find '.$controllerName.' class');
            }

            $actionName = TipyInflector::camelCase($actionName);
            if (in_array($actionName, get_class_methods($controllerName))) {
                    $controller->execute($actionName);
            } else {
                throw new TipyException('Undefined action '.$controllerName.'::'.$actionName.'()');
            }
        } catch (Exception $exception) {
            // TODO: implement debug mode output
            throw new TipyException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
