<?php
/**
 * TipyApp
 *
 * @package tipy
 */

/**
 * Application singleton
 *
 * Initializes application context and executes controller's action
 *
 * Usage:
 * <code>
 * $app = TipyApp::getInstance();
 * </code>
 */
class TipyApp {

    private static $instance = null;

    /**
     * TipyConfig instance
     * @see TipyConfig
     */
    public $config;

    /**
     * TipyView instance
     * @see TipyEnv
     */
    public $env;

    /**
     * TipyCookie instance
     * @see TipyCookie
     */
    public $cookie;

    /**
     * MySQL connection
     */
    public $db;

    /**
     * TipyInput instance
     * @see TipyInput
     */
    public $in;

    /**
     * TipyOutput instance
     * @see TipyOutput
     */
    public $out;

    /**
     * TipyView instance
     * @see TipyView
     */
    public $view;

    /**
     * TipySession instance
     * @see TipySession
     */
    public $session;

    /**
     * Path to your application's <b>public</b> directory
     * @see TipyCookie
     */
    public $documentRoot;

    private function __clone() {}
    private function __wakeup() {}

    /**
     * Hidden from user. Runs only once.
     * Initializes application context.
     * @internal
     */
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

    /**
     * Get application instance
     * 
     * Contruct application if it has not been initialized yet.
     *
     * <b>NOTE:</b> Does not connect to database.
     *
     * @return TipyApp
     * @see TipyApp::connectToDb()
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Connect to database
     */
    public function connectToDb() {
        new TipyDAO();
    }

    /**
     * Initialize controller and run action
     *
     * Requires <b>$app->in('controller')</b> and <b>$app->in('action')</b>
     * parameters to be defined.
     *
     * This method also catches all exceptions thrown in controllers and
     * throws final exception.
     *
     * @todo Different exceptions handling in production and development modes
     */
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
            throw new TipyException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
