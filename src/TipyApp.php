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
 * <code>
 * $app = TipyApp::getInstance();
 * </code>
 *
 * ### Lazy database connection
 *
 * Application instance does not immediately connect to the database.
 * Database connection is established on first TipyModel or TipyDAO object creation.
 *
 * @see TipyController
 * @see TipyModel
 * @see TipyDAO
 */
class TipyApp {

    private static $instance = null;

    /**
     * @var TipyConfig
     */
    public $config;

    /**
     * @var TipyView
     */
    public $env;

    /**
     * @var TipyCookie
     */
    public $cookie;

    /**
     * MySQL database connection
     * @var mysqli|null
     */
    public $db;

    /**
     * @var TipyInput
     */
    public $in;

    /**
     * @var TipyOutput
     */
    public $out;

    /**
     * @var TipyView
     */
    public $view;

    /**
     * @var TipySession
     */
    public $session;

    /**
     * @var TipyLogger
     */
    public $logger;

    /**
     * Path to your application's **public** directory
     * @var string
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
        $this->setupLogger();
    }

    /**
     * Get application instance
     * 
     * Contruct application if it has not been initialized yet.
     *
     * **NOTE** Does not connect to database.
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
     * Open log stream specified in config.ini and set threshold
     */
    private function setupLogger() {
        $logFile = $this->config->get('log_file');
        if (!$logFile || preg_match('/^\W*$/', $logFile)) {
            $logFile = 'php://stderr';
        }
        $threshold = $this->config->get('log_level_threshold');
        if (!$threshold || preg_match('/^\W*$/', $threshold)) {
            $threshold = 'OFF';
        }
        $this->logger = new TipyLogger($logFile);
        $this->logger->setThreshold($threshold);
    }

    /**
     * Initialize controller and run action
     *
     * Requires **$app->in('controller')** and **$app->in('action')**
     * parameters to be defined.
     *
     * @see TipyController
     * @todo Different exceptions handling in production and development modes
     */
    public function run() {
        $this->logger->debug($this->request->method().': '.$this->request->uri());
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
                    $this->logger->debug('Executing '.$controllerName.'::'.$actionName.'();');
                    $controller->execute($actionName);
            } else {
                throw new TipyException('Undefined action '.$controllerName.'::'.$actionName.'()');
            }
        } catch (Exception $exception) {
            throw new TipyException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
