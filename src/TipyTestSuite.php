<?php
require_once(__DIR__.'/../Tipy.php');

//
// Override application session for tests
//
class TipySession extends TipyBinder {
    public function close() {
        $this->binderData = [];
    }
}

// -----------------------------------------------------
// Test suite with assertions kit
// -----------------------------------------------------
class TipyTestSuite {

    protected $exeptions;
    protected $tests;
    protected $assertions;
    protected $failures;
    public $transactionalFixtures = true;

    public function __construct() {
        $this->clear();
    }

    public function clear() {
        $this->tests = 0;
        $this->assertions = 0;
        $this->failures = [];
        $this->exceptions = [];
    }

    public function run() {
        $this->clear();
        $className = get_class($this);
        $methods = get_class_methods($className);
        $dao = new TipyDAO();
        foreach ($methods as $testName) {
            if (!preg_match("/^test/", $testName)) {
                continue;
            }
            $this->tests++;
            $this->beforeTest();
            $testClosure = function() use ($testName) {
                try {
                    $this->$testName();
                } catch (Exception $e) {
                    $this->run = false;
                    $this->exceptions[] = $e;
                    echo TipyCli::red('E');
                }
            };
            if ($this->transactionalFixtures) {
                TipyDAO::transaction(function() use ($testName, $testClosure) {
                    $testClosure();
                    TipyDAO::rollback();
                });
            } else {
                $testClosure();
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
    }

    // do end oprations
    public function afterTest() {
        // nothing here
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

    public function assertion($a, $b) {
        $this->assertions++;
        if ($a == $b) {
            echo TipyCli::green('.');
        } else {
            echo TipyCli::purple('F');
            $e = new Exception();
            $trace = $e->getTrace();
            $test = $trace[2];
            $testBody = $trace[1];
            $this->failures[] = [$a, $b, $test['function'], $testBody['file'], $testBody['line']];
        }
    }

    public function assertEqual($a, $b) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a, $b);
    }

    public function assertNotEqual($a, $b) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a <> $b, true);
    }

    public function assertSame($a, $b) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a === $b, true);
    }

    public function assertNull($a) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a === null, true);
    }

    public function assertNotNull($a) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a !== null, true);
    }

    public function assertTrue($a) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a === true, true);
    }

    public function assertFalse($a) {
        if (!$this->run) {
            return;
        }
        $this->assertion($a === false, true);
    }

    public function assertThrown($exceptionClass, $exceptionMessage, $closure, $messageSubstrLength = 0) {
        try {
            $closure();
            $this->assertion(null, $exceptionClass.': '.$exceptionMessage);
        } catch (Exception $e) {
            $message = $messageSubstrLength ? substr($e->getMessage(), 0, $messageSubstrLength) : $e->getMessage();
            $this->assertion(get_class($e).': '.$message, $exceptionClass.': '.$exceptionMessage);
        }
        $this->run = true;
    }

    public function assertNotThrown($closure) {
        try {
            $closure();
            $this->assertion(true, true);
        } catch (Exception $e) {
            $this->assertion(get_class($e).': '.$e->getMessage(), null);
        }
        $this->run = true;
    }

    public function execute($controllerName, $actionName, &$output) {
        $app = TipyApp::getInstance();
        $app->in->set('controller', $controllerName);
        $app->in->set('action', $actionName);
        $app->out->clear();
        $output = "";
        ob_start();
        $app->run();
        $output = ob_get_clean();
    }

    public function getSummary() {
        return [
            "tests"      => $this->tests,
            "assertions" => $this->assertions,
            "failures"   => $this->failures,
            "exceptions" => $this->exceptions
        ];
    }
}

// -----------------------------------------------------
// Class for running tests
// -----------------------------------------------------
class TestRunner {

    public $fixtures;
    protected $tests;
    protected $assertions;
    protected $failures;
    protected $exeptions;
    protected $testNames;
    protected $testFiles;
    protected $fixtureFiles;
    protected $workingDir;
    protected $args;

    public function __construct() {
        $this->tests          = 0;
        $this->assertions     = 0;
        $this->failures       = [];
        $this->exceptions     = [];
        $this->testNames      = [];
        $this->testFiles      = [];
        $this->fixtures       = [];
        $this->fixtureFiles   = [];
        if (!CLI_MODE) {
            die("Tests should be run from command line.".PHP_EOL);
        }
        $args = $_SERVER['argv'];
        array_shift($args);
        if (sizeof($args) == 0) {
            $args = [getcwd()];
        }
        $this->args = $args;
        $this->findWorkingDir();
        echo '(in '.$this->workingDir.')'.PHP_EOL;
        $this->findConfig();
        foreach ($args as $filename) {
            $this->findTests($filename);
        }
    }

    private function findWorkingDir() {
        foreach ($this->args as $filename) {
            // check for /tests first
            if (preg_match('/^(.*tests)(\/|$)/', realpath($filename), $matches) && is_dir($matches[1])) {
                $this->workingDir = $matches[1];
                return;
            }
            if (is_dir($filename.'/tests')) {
                return $this->workingDir = realpath($filename.'/tests');
            }
        }
        die('No tests found.'.PHP_EOL);
    }

    private function findConfig() {
        if (defined('INI_FILE')) {
            return;
        }
        if (getenv('CIRCLECI') && file_exists($this->workingDir.'/config.ini.ci')) {
            define('INI_FILE', $this->workingDir.'/config.ini.ci');
            return;
        }
        if (file_exists($this->workingDir.'/config.ini')) {
            define('INI_FILE', $this->workingDir.'/config.ini');
            return;
        }
        if (file_exists($this->workingDir.'/../config.ini')) {
            define('INI_FILE', realpath($this->workingDir.'/../config.ini'));
            return;
        }
    }

    private function findFixtures() {
        if (sizeof($this->fixtures) == 0) {
            $searchDirs = $this->args;
        } else {
            $searchDirs = $this->fixtures;
        }
        foreach($searchDirs as $filename) {
            $this->findFixtureFiles($filename);
        }
        if (sizeof($this->fixtureFiles) == 0) {
             $this->findFixtureFiles($this->workingDir);
        }
    }

    private function findFixtureFiles($filename) {
        if (is_dir($filename)) {
            if ($handle = opendir($filename)) {
                while (false !== ($f = readdir($handle))) {
                    if (!preg_match('/\.$/', $f)) {
                        $this->findFixtureFiles($filename.'/'.$f);
                    }
                }
                closedir($handle);
            }
        } else if (preg_match('/\.sql$/', $filename)) {
            $this->fixtureFiles[] = $filename;
        }
    }

    // Find tests and fixtures recursively
    private function findTests($filename) {
        if (is_dir($filename)) {
            if ($handle = opendir($filename)) {
                while (false !== ($f = readdir($handle))) {
                    if (!preg_match('/\.$/', $f)) {
                        $this->findTests($filename.'/'.$f);
                    }
                }
                closedir($handle);
            }
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
        $this->findFixtures();
        $app = TipyApp::getInstance();
        $app->initDbConnection();
        $app->db->query('DROP DATABASE IF EXISTS '.$app->config->get('db_test_name'));
        $app->db->query('CREATE DATABASE '.$app->config->get('db_test_name'));
        $app->db->select_db($app->config->get('db_test_name'));
        foreach ($this->fixtureFiles as $fixture) {
            TipyTestSuite::applyFixture($app->db, $fixture);
        }
        echo PHP_EOL;
        foreach ($this->testNames as $test) {
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
        foreach ($summary['failures'] as $failure) {
            $this->failures[] = $failure;
        }
        foreach ($summary['exceptions'] as $exception) {
            $this->exceptions[] = $exception;
        }
        return $summary;
    }

    public function printSummary() {
        echo PHP_EOL.PHP_EOL;
        echo "Tests: ".$this->tests;
        echo ", Assertions: ".$this->assertions;
        echo ", Failures: ".sizeof($this->failures);
        echo ", Exceptions: ".sizeof($this->exceptions);
        echo PHP_EOL.PHP_EOL;
        if (sizeof($this->failures) > 0) {
            echo TipyCli::red('Failures:').PHP_EOL;
            $i = 0;
            foreach ($this->failures as $failure) {
                $i++;
                echo "$i) ".TipyCli::yellow($failure[2]).": ";
                echo $failure[3]." at line (".TipyCli::cyan($failure[4]).")".PHP_EOL;
                var_dump($failure[1]);
                echo " expected but was ".PHP_EOL;
                var_dump($failure[0]);
                echo PHP_EOL;
            }
            echo PHP_EOL.PHP_EOL;
        }
        if (sizeof($this->exceptions) > 0) {
            echo TipyCli::red('Exceptions:').PHP_EOL;
            $i = 0;
            foreach ($this->exceptions as $e) {
                $i++;
                echo $i.") ";
                echo TipyCli::yellow($e->getMessage()).PHP_EOL;
                echo $this->printBacktrace($e->getTrace());
                echo PHP_EOL.PHP_EOL;
            }
        }
    }

    private function printBacktrace($trace) {
        foreach ($trace as $call) {
            echo basename($call['file']);
            echo " (".TipyCli::cyan($call['line'])."): ";
            echo $call['function']."(";
            var_export($call['args']);
            echo ")".PHP_EOL;
        }
    }
}
