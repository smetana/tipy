<?php

class TipyException extends Exception {}

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

    private function __clone()     {}
    private function __wakeup()    {}
    private function __construct() {
        $this->config     = new TipyConfig();    // Config
        $this->request    = new TipyRequest();   // Request wrapper
        $this->env        = new TipyEnv();       // Environment wrapper
        $this->cookie     = new TipyCookie();    // Cookie wrapper
        $this->in         = new TipyInput();     // Input wrapper
        $this->out        = new TipyOutput();    // Output data holder
        $this->view       = new TipyView();      // Template renderer
        $this->session    = new TipySession();   // Session
        $this->db         = null;                // DB resource

        // dispatcher.php is called by Apache with current working dir
        // set to DocumentRoot. Use it to get all paths needed
        $cwd = getcwd();
        // Set path to document_root
        $this->config->set('document_root', $cwd);
        // Set path to application
        $this->config->set('application_path', realpath($cwd.'/../app'));
        // Set path to templates
        $this->view->setTemplatePath(realpath($cwd.'/../app/views'));
    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // We don't init DB connection by default
    public function initDbConnection() {
        new TipyDAO();
    }

    public function run() {
        try {
            // Get controller and action name
            $controllerName = $this->in->get('controller');
            $actionName     = $this->in->get('action');

            // Some basic checking
            if (!$controllerName || preg_match('/^\W*$/', $controllerName)) {
                // If we did not get controller and action from param try match some defaults
                if (TipyRouter::match($this->request, $controllerName, $actionName, $id)) {
                    if ($id && !$this->in->get('id')) {
                        $this->in->set('id', $id);
                    }
                } else {
                    throw new TipyException('Controller name is missing');
                }
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
            throw new TipyException($exception->getMessage());
        }
    }
}
