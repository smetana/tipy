<?php

// sert include paths
$includePath  = ini_get('include_path');
$includePath .= ':'.__DIR__.'/../../app';
$includePath .= ':'.__DIR__.'/../../vendor/tipy';
ini_set('include_path', $includePath);

//
// Load modules
//
require_once('ErrorHandler.php');
require_once('Tipy.php');
require_once('TipyDAO.php');
require_once('TipyModel.php');
require_once('TipyConfig.php');
require_once('TipyEnv.php');
require_once('TipyCookie.php');
require_once('TipyInput.php');
require_once('TipyOutput.php');
require_once('TipyView.php');
require_once('Inflector.php');
require_once(__DIR__.'/../cliColors/CliColors.php');

//
// Override application session for tests
//
class TipySession extends TipyBinder {
    function close() {
        $this->binderData = array();
    }
}

$request_headers = array();
if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        global $request_headers;
        return $request_headers;
    }
}

//
// Start Application
//
$app = Tipy::getInstance();
$app->initDbConnection();

// load Autoload function
require_once('TipyAutoloader.php');

// -----------------------------------------------------
// Test suite with assertions kit
// -----------------------------------------------------
class TipyTestSuite {

    protected $exeptions;
    protected $tests;
    protected $assertions;
    protected $failures;

    public function __construct() {
        $this->clear();
    }

    public function clear() {
        $this->tests = 0;
        $this->assertions = 0;
        $this->failures = array();
        $this->exceptions = array();
    }

    public function run() {
        $this->clear();
        $className = get_class($this);
        $methods = get_class_methods($className);
        foreach($methods as $testName) {
            if (!preg_match("/^test/", $testName)) continue;
            $this->tests++;
            $this->beforeTest();
            try {
                $this->$testName();
            } catch (Exception $e) {
                $this->run = false;
                array_push($this->exceptions, $e);
                $colors = new Colors();
                echo $colors->getColoredString("E", 'red');
            }
            $this->afterTest();
        }
    }

    // do start test operations
    public function beforeTest() {
        $app = Tipy::getInstance();
        // clear session and input
        $app->in->clear();
        $app->session->clear();
        $this->run = true;
        // start transaction
        $app->db->select_db($app->config->get('db_test_name'));
        $app->db->query('START TRANSACTION');
    }

    // do end oprations
    public function afterTest() {
        $app = Tipy::getInstance();
        // rollback transaction
        $app->db->select_db($app->config->get('db_test_name'));
        $app->db->query('ROLLBACK');
    }

    public static function applyFixture($db, $name) {
        $content = file_get_contents($name.'.sql', 1);
        if ($content) {
            $querys = explode(';', $content);
            foreach ($querys as $query) {
                if (trim($query)) {
                    $db->query($query) or die('Fixture error!'.PHP_EOL.$query.PHP_EOL);
                }
            }
        }
    }

    public function execute($controllerName, $methodName, &$output, $silent = false) {
        $app = Tipy::getInstance();
        $app->out->clear();
        $output = "";
        try {
            ob_start();
            $controllerFile = 'controller/'.$controllerName.'.php';
            include_once($controllerFile);
            $controller = new $controllerName;
            $controller->execute($methodName);
            $output = ob_get_clean();
            return null;
        }  catch (Exception $e) {
            $output = ob_get_clean();
            if (!$silent) {
                $this->run = false;
                array_push($this->exceptions, $e);
                $colors = new Colors();
                echo $colors->getColoredString("E", 'red');
            }
            return $e;
        }
    }

    public function assertion($a, $b) {
        $this->assertions++;
        $colors = new Colors();
        if ($a == $b) {
            echo $colors->getColoredString(".", 'green');
        } else {
            echo $colors->getColoredString("F", 'purple');
            $e = new Exception();
            $trace = $e->getTrace();
            $test = $trace[2];
            $testBody = $trace[1];
            array_push($this->failures, array($a, $b, $test['function'], $testBody['file'], $testBody['line']));
        }
    }

    public function assertEqual($a, $b) {
        if (!$this->run) return;
        $this->assertion($a, $b);
    }

    public function assertNotEqual($a, $b) {
        if (!$this->run) return;
        $this->assertion($a <> $b, true);
    }

    public function assertThrown($exceptionClass, $exceptionMessage, $closure, $messageSubstrLength = 0) {
        try {
            $closure();
            $this->assertion(NULL, $exceptionClass.': '.$exceptionMessage);
        } catch (Exception $e) {
            $message = $messageSubstrLength ? substr($e->getMessage(), 0, $messageSubstrLength) : $e->getMessage();
            $this->assertion(get_class($e).': '.$message , $exceptionClass.': '.$exceptionMessage);
        }
        $this->run = true;
    }

    public function assertNotThrown($closure) {
        try {
            $closure();
            $this->assertion(true, true);
        } catch (Exception $e) {
            $this->assertion(get_class($e).': '.$e->getMessage(), NULL);
        }
        $this->run = true;
    }

    public function assertResponse($controllerName, $methodName, &$output, $response) {
        $e = $this->execute($controllerName, $methodName, $output, 'silent');
        if (get_class($e) != 'Exception') {
            $this->assertion(true, false);
        } else {
            $this->assertion($e->getMessage(), $response);
        }
        $this->run = true;
    }

    public function getSummary() {
        return array(
            "tests"      => $this->tests,
            "assertions" => $this->assertions,
            "failures"   => $this->failures,
            "exceptions" => $this->exceptions
        );
    }
}

// -----------------------------------------------------
// Class for running tests
// -----------------------------------------------------
class TestRunner {

    public $args;
    public $dir;
    public $fixtures;
    public $useDumper;
    protected $exeptions;
    protected $tests;
    protected $testFilepaths;
    protected $assertions;
    protected $failures;

    public function __construct($args) {
        $this->args           = $args;
        $this->useDumper      = true;
        $this->tests          = 0;
        $this->assertions     = 0;
        $this->failures       = array();
        $this->exceptions     = array();
    }

    public function run() {
        $app = Tipy::getInstance();
        $tests          = array();
        $testFilepaths  = array();
        if(sizeof($this->args)) {
            foreach($this->args as $file) {
                $testName                 = basename($file, '.php');
                $tests[]                  = $testName;
                $testFilepaths[$testName] = $file;
            }
        } else {
            if ($this->dirs) {
                if(!is_array($this->dirs)) {
                    $this->dirs = array($this->dirs);
                }
                foreach($this->dirs as $dir) {
                    if ($handle = opendir($dir)) {
                        while (false !== ($file = readdir($handle))) {
                            if(substr($file, 0, 4) == 'test' && is_file($dir.'/'.$file)) {
                                $testName                 = basename($file, '.php');
                                $tests[]                  = $testName;
                                $testFilepaths[$testName] = $dir.'/'.$file;
                            }
                        }
                        closedir($handle);
                    }
                }
            }
        }

        $app->db->query('DROP DATABASE IF EXISTS '.$app->config->get('db_test_name'));
        if ($this->useDumper) {
            require_once(__DIR__.'/../MysqlCloneDb/MysqlCloneDb.php');
            new MysqlCloneDb($app->db, $app->config->get('db_name'), $app->config->get('db_test_name'), 'InnoDB');
        } else {
            $app->db->query('CREATE DATABASE '.$app->config->get('db_test_name'));
        }

        $app->db->select_db($app->config->get('db_test_name'));
        if ($this->fixtures) {
            if(!is_array($this->fixtures)) {
                $this->fixtures = array($this->fixtures);
            }
            foreach($this->fixtures as $fixture) {
                TipyTestSuite::applyFixture($app->db, $fixture);
            }
        }

        echo "\n";

        foreach($tests as $test){
            require_once($testFilepaths[$test]);
            $test = new $test;
            $test->run();
            $this->updateSummary($test->getSummary());
        }
        $app->db->query('DROP DATABASE '.$app->config->get('db_test_name'));

        $this->printSummary();
    }

    public function updateSummary($summary) {
        $this->tests += $summary['tests'];
        $this->assertions += $summary['assertions'];
        foreach($summary['failures'] as $failure) {
            array_push($this->failures, $failure);
        }
        foreach($summary['exceptions'] as $exception) {
            array_push($this->exceptions, $exception);
        }
        return $summary;
    }

    public function printSummary() {
        $colors = new Colors();
        echo PHP_EOL.PHP_EOL;
        echo "Tests: ".$this->tests;
        echo ", Assertions: ".$this->assertions;
        echo ", Failures: ".sizeof($this->failures);
        echo ", Exceptions: ".sizeof($this->exceptions);
        echo PHP_EOL.PHP_EOL;
        if (sizeof($this->failures) > 0) {
            echo $colors->getColoredString('Failures:', 'red').PHP_EOL;
            $i = 0;
            foreach($this->failures as $failure) {
                $i++;
                echo "$i) ".$colors->getColoredString($failure[2], 'yellow').": ";
                echo $failure[3]." at line (".$colors->getColoredString($failure[4], 'cyan').")".PHP_EOL;
                var_dump($failure[1]);
                echo " expected but was ".PHP_EOL;
                var_dump($failure[0]);
                echo PHP_EOL;
            }
            echo PHP_EOL.PHP_EOL;
        }
        if (sizeof($this->exceptions) > 0) {
            echo $colors->getColoredString('Exceptions:', 'red').PHP_EOL;
            $i = 0;
            foreach($this->exceptions as $e) {
                $i++;
                echo $i.") ";
                echo $colors->getColoredString($e->getMessage(), 'yellow').PHP_EOL;
                echo self::printBacktrace($e->getTrace());
                echo PHP_EOL.PHP_EOL;
            }
        }
    }

    public static function printBacktrace($trace) {
        $colors = new Colors();
        foreach($trace as $call) {
            echo basename($call['file']);
            echo " (".$colors->getColoredString($call['line'], 'cyan')."): ";
            echo $call['function']."(";
            var_export($call['args']);
            echo ")".PHP_EOL;
        }
    }

    public function allTestsPassed() {
        return sizeof($this->failures) + sizeof($this->exceptions) == 0;
    }

}




