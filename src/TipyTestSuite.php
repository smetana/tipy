<?php
require_once(__DIR__.'/../Tipy.php');
require_once(__DIR__.'/../vendor/cliColors/CliColors.php');

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
$app = TipyApp::getInstance();
$app->initDbConnection();

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
        $app = TipyApp::getInstance();
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
        $app = TipyApp::getInstance();
        // rollback transaction
        $app->db->select_db($app->config->get('db_test_name'));
        $app->db->query('ROLLBACK');
    }

    public static function applyFixture($db, $filename) {
        $content = file_get_contents($filename, 1);
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
        $app = TipyApp::getInstance();
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

    protected $tests;
    protected $assertions;
    protected $failures;
    protected $exeptions;
    protected $testNames;
    protected $testFiles;
    protected $fixtureFiles;
    
    public function __construct() {
        $this->tests          = 0;
        $this->assertions     = 0;
        $this->failures       = array();
        $this->exceptions     = array();
        $this->testNames      = array();
        $this->testFiles      = array();
        $this->fixtureFiles   = array();
        if (!isset($_SERVER['argv'])) {
            exit("Tests should be run from command line.");
        }
        $args = $_SERVER['argv'];
        array_shift($args);
        if (sizeof($args) == 0) { $args = array(getcwd()); };
        foreach($args as $filename) {
            $this->findTestsAndFixtures($filename);
        }
    }

    // Find tests and fixtures recursively
    private function findTestsAndFixtures($filename) {
        if (is_dir($filename)) {
            if ($handle = opendir($filename)) {
                while (false !== ($f = readdir($handle))) {
                    if (preg_match('/\.$/', $f)) {
                        // skip . and ..
                    } else {
                        $this->findTestsAndFixtures($filename.'/'.$f);
                    }
                }
                closedir($handle);
            }
        } else if (preg_match('/\.sql$/', $filename)) {
            $this->fixtureFiles[] = $filename;
        } else if (preg_match('/(test\w+)\.php$/', $filename, $matches)) {
            $testName = $matches[1];
            $this->testNames[] = $testName;        
            $this->testFiles[$testName] = $filename;
        }
    }

    // This function also return exit status to use in scripts
    // 0 - if all tests passed
    // 1 - if one of the tests failed
    public function run() {
        $app = TipyApp::getInstance();
        $app->db->query('DROP DATABASE IF EXISTS '.$app->config->get('db_test_name'));
        $app->db->query('CREATE DATABASE '.$app->config->get('db_test_name'));
        $app->db->select_db($app->config->get('db_test_name'));
        foreach($this->fixtureFiles as $fixture) {
            TipyTestSuite::applyFixture($app->db, $fixture);
        }
        echo "\n";
        foreach($this->testNames as $test){
            require_once($this->testFiles[$test]);
            $test = new $test;
            $test->run();
            $this->updateSummary($test->getSummary());
        }
        $app->db->query('DROP DATABASE '.$app->config->get('db_test_name'));
        $this->printSummary();
        if (sizeof($this->failures) + sizeof($this->exceptions) == 0) {
            return 0;
        } else {
            return 1;
        }
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
                echo $this->printBacktrace($e->getTrace());
                echo PHP_EOL.PHP_EOL;
            }
        }
    }

    private function printBacktrace($trace) {
        $colors = new Colors();
        foreach($trace as $call) {
            echo basename($call['file']);
            echo " (".$colors->getColoredString($call['line'], 'cyan')."): ";
            echo $call['function']."(";
            var_export($call['args']);
            echo ")".PHP_EOL;
        }
    }

}
