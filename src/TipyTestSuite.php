<?php
/**
 * TipyTestSuite
 * TipyTestRunner
 *
 * @package tipy
 */
require_once(__DIR__.'/../Tipy.php');

/**
 * Raised when asserton is failed
 *
 * When assertion fails we need to stop current test execution.
 * Throw this exception to stop the test.
 *
 * Typically used should not see this exception. All of them
 * should be catched by TipyTestRunner
 */
class AssertionFailedException extends TipyException {}


/**
 * Tipy Test Framework
 */
class TipyTestSuite {

    /**
     * Counters
     */
    private $tests;
    private $assertions;

    /**
     * Arrays
     */
    private $exeptions;
    private $failures;

    /**
     * Set this to **false** to turn off transactional fixtures
     *
     * Every test is wrapped in transaction to restore
     * database state after test is finished.
     *
     * There are some cases when you might want to turn this
     * behaviour off:
     *
     * - To test real transactions
     * - Your database does not support transactions
     */
    public $transactionalFixtures = true;

    public function __construct() {
        $this->tests = 0;
        $this->assertions = 0;
        $this->failures = [];
        $this->exceptions = [];
    }

    /**
     * Run test suite
     *
     * Execute all <b>test*</b> methods from test suite
     * and collect results
     */
    public function run() {
        $className = get_class($this);
        $methods = get_class_methods($className);
        foreach ($methods as $testName) {
            if (!preg_match("/^test/", $testName)) {
                continue;
            }
            $this->tests++;
            $this->clearAppContext();
            $this->beforeTest();
            $testClosure = function() use ($testName) {
                try {
                    $this->$testName();
                    echo TipyCli::green('.');
                } catch (AssertionFailedException $e) {
                    $this->failures[] = $e;
                    echo TipyCli::purple('F');
                } catch (Exception $e) {
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

    private function clearAppContext() {
        $app = TipyApp::getInstance();
        $app->in->clear();
        $app->session->clear();
    }

    /**
     * Hook called before each test execution
     *
     * Override this in your test suite
     */
    public function beforeTest() {
    }

    /**
     * Hook called after each test execution
     *
     * Override this in your test suite
     */
    public function afterTest() {
    }

    /**
     * Execute fixture .sql file
     * @internal
     */
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

    /**
     * Dump variable to string omiting STDOUT
     */
    private function varDump($var) {
        ob_start();
        var_dump($var);
        $var = ob_get_clean();
        $var = preg_replace('/\n$/', '', $var);
        return $var;
    }

    /**
     * Base assertion
     * @param boolean $result
     * @param string $message
     * @throws AssertionFailedException if $result is not true
     */
    private function assertion($result, $message) {
        $this->assertions++;
        if (!$result) {
            $trace = debug_backtrace();
            $args = $trace[1]['args'];
            $actual = $this->varDump($args[0]);
            if (sizeof($args) > 1) {
                $expected = $this->varDump($args[1]);
                $message = $actual." ".$message." ".$expected;
            } else {
                $message = $actual." ".$message;
            }
            $e = new AssertionFailedException($message);
            throw $e;
        }
    }

    /**
     * Report an error if $actual and $expected are not equal
     * @param mixed $actual
     * @param mixed $expected
     * @throws AssertionFailedException
     */
    public function assertEqual($actual, $expected) {
        $this->assertion($actual == $expected, "expected to be equal");
    }

    /**
     * Report an error if $actual and $expected are equal
     * @param mixed $actual
     * @param mixed $expected
     * @throws AssertionFailedException
     */
    public function assertNotEqual($actual, $expected) {
        $this->assertion($actual <> $expected, "expected not be equal");
    }

    /**
     * Report an error if $actual and $expected are not identical
     * @param mixed $actual
     * @param mixed $expected
     * @throws AssertionFailedException
     * @deprecated Use assertIdentical() instead
     */
    public function assertSame($actual, $expected) {
        $this->assertion($actual === $expected, "expected to be the same (===) as");
    }

    /**
     * Report an error if $actual and $expected are not identical
     * @param mixed $actual
     * @param mixed $expected
     * @throws AssertionFailedException
     */
    public function assertIdentical($actual, $expected) {
        $this->assertion($actual === $expected, "expected to be the identical");
    }

    /**
     * Report an error if $actual is not *null*
     * @param mixed $actual
     * @throws AssertionFailedException
     */
    public function assertNull($actual) {
        $this->assertion($actual === null, "expected to be NULL");
    }

    /**
     * Report an error if $actual is *null*
     * @param mixed $actual
     * @throws AssertionFailedException
     */
    public function assertNotNull($actual) {
        $this->assertion($actual !== null, "expected not to be NULL");
    }

    /**
     * Report an error if $actual is *false*
     * @param mixed $actual
     * @throws AssertionFailedException
     */
    public function assertTrue($actual) {
        $this->assertion($actual === true, "expected to be true");
    }

    /**
     * Report an error if $actual is *true*
     * @param mixed $actual
     * @throws AssertionFailedException
     */
    public function assertFalse($actual) {
        $this->assertion($actual === false, "expected to be false");
    }

    /**
     * Report an error if $closure does not throw $exceptionClass with $exceptionMessage
     * @param string $exceptionClass
     * @param string $exceptionMessage
     * @param closure $closure
     * @throws AssertionFailedException
     */
    public function assertThrown($exceptionClass, $exceptionMessage, $closure) {
        $this->assertions++;
        $expected = $exceptionClass.": ".$exceptionMessage;
        try {
            $closure();
            throw new AssertionFailedException('"'.$expected.'" expected but nothing was thrown');
        } catch (AssertionFailedException $e) {
            throw $e;
        } catch (Exception $e) {
            $actual = get_class($e).": ".$e->getMessage();
            if ($expected != $actual) {
                throw new AssertionFailedException('"'.$expected.'" expected but "'.$actual.'" was thrown');
            }
        }
    }

    /**
     * Report an error if $closure does not throw $exceptionClass
     * or $exceptionMessage does not match $expectedMessageRegexp
     * @param string $expectedClassString
     * @param regexp $expectedMessageRegexp
     * @param closure $closure
     * @throws AssertionFailedException
     */
    public function assertThrownRegexp($expectedClassString, $expectedMessageRegexp, $closure) {
        $this->assertions++;
        $expected = $expectedClassString.": ".$expectedMessageRegexp;
        try {
            $closure();
            throw new AssertionFailedException('"'.$expected.'" expected but nothing was thrown');
        } catch (AssertionFailedException $e) {
            throw $e;
        } catch (Exception $e) {
            $actualClass = get_class($e);
            $actualMessage = $e->getMessage();
            if (($expectedClassString != $actualClass) || !preg_match($expectedMessageRegexp, $actualMessage)) {
                $actual = $actualClass.': '.$actualMessage;
                throw new AssertionFailedException('"'.$expected.'" does not match "'.$actual.'"');
            }
        }
    }

    /**
     * Execute controller
     * @internal
     * @todo This is prototype function. It is not production ready
     */
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

    /**
     * Return test suite execution results
     * @return array
     */
    public function getSummary() {
        return [
            "tests"      => $this->tests,
            "assertions" => $this->assertions,
            "failures"   => $this->failures,
            "exceptions" => $this->exceptions
        ];
    }
}


/**
 * Run tests and print summary
 *
 * <code>
 * $runner = new TipyTestRunner();
 * $exitCode = $runner->run();
 * exit($exitCode);
 * </code>
 *
 * @todo Combine {@link TipyTestSuite} and {@link TipyTestRunner} in one class
 */
class TipyTestRunner {

    /**
     * Paths to fixtures
     * @var array
     */
    public $fixtures;

    /**
     * Counters
     */
    private $tests;
    private $assertions;

    /**
     * Arrays
     */
    private $failures;
    private $exeptions;

    /**
     * Config
     */
    private $testNames;
    private $testFiles;
    private $fixtureFiles;
    private $workingDir;
    private $args;

    /**
     * @internal
     */
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

    /**
     * Run tests
     *
     * Returns exit status:
     *
     * - 0 - all tests passed
     * - 1 - there were errors or failures
     *
     * @return integer
     */
    public function run() {
        $this->findFixtures();
        $app = TipyApp::getInstance();
        $app->connectToDb();
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
            // Force to call __destruct()
            unset($test);
        }
        $this->printSummary();
        if (sizeof($this->failures) + sizeof($this->exceptions) == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    private function updateSummary($summary) {
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

    private function printSummary() {
        echo PHP_EOL.PHP_EOL;
        echo "Tests: ".$this->tests;
        echo ", Assertions: ".$this->assertions;
        echo ", Failures: ".sizeof($this->failures);
        echo ", Exceptions: ".sizeof($this->exceptions);
        echo PHP_EOL.PHP_EOL;
        if (sizeof($this->failures) > 0) {
            echo TipyCli::red('Failures:').PHP_EOL;
            $i = 0;
            foreach ($this->failures as $e) {
                $i++;
                echo $i.") ";
                $trace = $e->getTrace();
                if ($trace[2]['function'] == "{closure}") {
                    $test = $trace[1];
                    $testBody = $trace[0];
                } else {
                    $test = $trace[2];
                    $testBody = $trace[1];
                }
                echo TipyCli::yellow($test['function']).": ";
                echo $testBody['file']." at line (".TipyCli::cyan($testBody['line']).")".PHP_EOL;
                echo $e->getMessage();
                echo PHP_EOL.PHP_EOL;
            }
        }
        if (sizeof($this->exceptions) > 0) {
            echo TipyCli::red('Exceptions:').PHP_EOL;
            $i = 0;
            foreach ($this->exceptions as $e) {
                $i++;
                echo $i.") ";
                $trace = $e->getTrace();
                echo TipyCli::yellow($trace[0]['function']).": ";
                echo $e->getFile()." at line (".TipyCli::cyan($e->getLine()).")".PHP_EOL;
                echo get_class($e).": ".$e->getMessage().PHP_EOL;
                echo $e->getTraceAsString();
                echo PHP_EOL.PHP_EOL;
            }
        }
    }

}
