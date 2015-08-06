<?php

class TipyException extends Exception {}

// ==================================================================
// Application
// Class to store Application context
// ==================================================================
class Tipy {

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
        $this->env        = new TipyEnv();       // Environment wrapper
        $this->cookie     = new TipyCookie();    // Cookie wrapper
        $this->in         = new TipyInput();     // Input wrapper
        $this->out        = new TipyOutput();    // Output data holder
        $this->view       = new TipyView();      // Template renderer
        $this->session    = new TipySession();   // Session
        $this->db         = null;                       // DB resource

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
            //
            // Get controller and method name
            //
            $controllerName = $this->in->get('controller');
            $methodName     = $this->in->get('method');
            //
            // Some basic checking
            //
            if ( !$controllerName || !$methodName || preg_match("/\W/", $controllerName) || preg_match("/\W/", $methodName) ) {
                throw new TipyException("Wrong or insufficient params.");
            }
            //
            // Create controller and call method
            //
            $controllerFile = $this->config->get('application_path') .'/controllers/'.$controllerName.'.php';
            if(file_exists($controllerFile)) {
                require_once ($controllerFile);
                $controller = new $controllerName();
            } else {
                throw new TipyException('Unknown controller. Sorry.');
            }
            if (in_array($methodName, get_class_methods($controllerName))) {
                    $controller->execute($methodName);
            } else {
                throw new TipyException('Unknown method. Sorry.');
            }
        } catch(Exception $exception) {
            // TODO: implement debug mode output
            throw new TipyException($exception->getMessage());
        }

        unset($controller);
    }
}
