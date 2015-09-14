<?php
/**
 * TipyLogger
 *
 * @package tipy
 */

/**
 * Simple Logger
 *
 * By default TipyLogger writes to *STDERR*
 * <code>
 * // This will output to apache's error.log  something like
 * // Fri Sep 13 12:22:20 2015 [23685] [INFO] Hello World! 
 * $logger = new TipyLogger();
 * $logger->info('Hello World');
 * </code>
 *
 * But it is possible to create a logger to write to the file
 * <code>
 * $logger = new TipyLogger('/var/log/project.log');
 * $logger->info('Hello World');
 * </code>
 *
 * ## Threshold Level
 *
 * Default threshold level for new TipyLogger is {@link DEBUG}.
 * This means that all messages are logged. 
 *
 * You can change {@link $threshold} to different threshold level 
 * and then only messages with lower or equal severity level will
 * be logged.
 * <code>
 * $logger = new TipyLogger('/var/log/project.log');
 * $logger->threshold = TipyLogger::WARN;
 * $logger->warn('I am logged :)'); // logged
 * $logger->info('I am not :('); // ignored
 * </code>

 */
class TipyLogger {

    // Severity Levels

    /**
     * An unhandleable error that results in a program crash
     */
    const FATAL = 1;

    /**
     * A handleable error
     */
    const ERROR = 2;

    /**
     * A warning
     */
    const WARN = 3;

    /**
     * Useful information
     */
    const INFO = 4;

    /**
     * Low-level information for developers
     */
    const DEBUG = 5;

    /**
     * Logging is turned off
     */
    const OFF = 0;

    /**
     * Level names array for quick *level=>name* conversion
     * @var array
     */
    private $levelNames = ['OFF', 'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG'];

    /**
     * Log level threshold
     * @var integer
     */
    public $threshold = self::DEBUG;

    /**
     * Log format. Accepts all strftime characters plus *%pid* and *%level*
     * @var string
     */
    public $format = '%c [%pid] [%level]';

    /**
     * Path to the log file
     * @var string
     */
    private $filePath;

    /**
     * Log resource handler
     * @var resource
     */
    private $handle;

    /**
     * Create new TipyLogger instance
     *
     * @param string $filePath default {@link 'php://stderr'}
     */
    public function __construct($filePath = 'php://stderr') {
        $this->filePath = $filePath;
        $this->handle = fopen($this->filePath, 'a');
    }

    /**
     * Close open log file
     * @internal
     */
    public function __destruct() {
        fclose($this->handle);
    }

    /**
     * Set threshold level
     * @param string|integer $level
     */
    public function setThreshold($level) {
        if (is_int($level)) {
            if ($level < TipyLogger::OFF or $level > TipyLogger::DEBUG) {
                throw new TipyException("Unknown log level ".$level);
            }
            $this->threshold = $level;
        } else {
            $level = strtoupper($level);
            if (!in_array($level, $this->levelNames)) {
                throw new TipyException("Uknown log level ".$level);
            }
            $this->threshold = array_flip($this->levelNames)[$level];
        }
    }

    /**
     * Add log prefix to mesage
     * @param string $message
     * @param integer $level
     * @return string
     */
    private function formatMessage($message, $level) {
        $prefix = str_replace('%pid', posix_getpid(), $this->format);
        $prefix = str_replace('%level', $this->levelNames[$level], $prefix);
        $prefix = strftime($prefix);
        return $prefix.' '.$message.PHP_EOL;
    }

    /**
     * Log message
     * @param string $message
     * @param integer $level
     */
    public function log($message, $level) {
        if ($level <= $this->threshold) {
            fwrite($this->handle, $this->formatMessage($message, $level));
        }
    }

    /**
     * Log FATAL message
     * @param string $message
     */
    public function fatal($message) {
        $this->log($message, self::FATAL);
    }

    /**
     * Log ERROR message
     * @param string $message
     */
    public function error($message) {
        $this->log($message, self::ERROR);
    }

    /**
     * Log WARN message
     * @param string $message
     */
    public function warn($message) {
        $this->log($message, self::WARN);
    }

    /**
     * Log INFO message
     * @param string $message
     */
    public function info($message) {
        $this->log($message, self::INFO);
    }

    /**
     * Log DEBUG message
     * @param string $message
     */
    public function debug($message) {
        $this->log($message, self::DEBUG);
    }

}
