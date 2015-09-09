<?php
/**
 * TipyLogger
 *
 * @package tipy
 */

/**
 * Simple Logger
 *
 * <code>
 * $logger = new TipyLogger(TipyLogger::WARNING);
 * $logger->error('Hello World');
 *
 * $myLogger = new TipyLogger(TipyLogger::DEBUG, 'project.log');
 * $myLogger->info('Hello World');
 * </code>
 */
class TipyLogger {

    // Severity Levels
    // https://tools.ietf.org/html/rfc5424

    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    private $levelNames = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /**
     * Log level threshold
     * @var integer
     */
    public $threshold = self::DEBUG;

    /**
     * Log format
     *
     * Accept all strftime characters plus *%pid* and *%level*
     * @var string
     */
    public $format = '%c [%pid] [%level]';

    /**
     * Path to the log file
     * @var string
     */
    public $filePath = 'php://stderr';

    /**
     * Log file handler
     * @var resource
     */
    private $fileHandler;

    /**
     * Create new TipyLogger instance
     *
     * @param integer $threshold default {@link DEBUG}
     * @param string $filePath default {@link 'php://stderr'}
     */
    public function __construct($threshold = null, $filePath = null) {
        if ($threshold !== null) {
            $this->threshold = $threshold;
        }
        if ($filePath !== null ) {
            $this->filePath = $filePath;
        }
        $this->fileHandler = fopen($this->filePath, 'w');
    }

    /**
     * Close open log file
     * @internal
     */
    public function __destruct() {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
        }
    }

    /**
     * Add log prefix to mesage
     * @param string $message
     * @return string
     */
    private function formatLogLine($message, $level) {
        $prefix = str_replace('%pid', posix_getpid(), $this->format);
        $prefix = str_replace('%level', $this->levelNames[$level], $prefix);
        $prefix = strftime($prefix);
        return $prefix.' '.$message.PHP_EOL;
    }

    /**
     * Output message to log
     *
     * @param string $message
     * @param integer $level default {@link $threshold}
     */
    public function log($message, $level = null) {
        if (!$level) {
            $level = $this->threshold;
        }
        if ($level <= $this->threshold) {
            fwrite($this->fileHandler, $this->formatLogLine($message, $level));
        }
    }

    /**
     * Output message to log with EMERGENCY severity level
     * @param string $message
     */
    public function emergency($message) {
        $this->log($message, self::EMERGENCY);
    }

    /**
     * Output message to log with ALERT severity level
     * @param string $message
     */
    public function alert($message) {
        $this->log($message, self::ALERT);
    }

    /**
     * Output message to log with CRITICAL severity level
     * @param string $message
     */
    public function critical($message) {
        $this->log($message, self::CRITICAL);
    }

    /**
     * Output message to log with ERROR severity level
     * @param string $message
     */
    public function error($message) {
        $this->log($message, self::ERROR);
    }

    /**
     * Output message to log with WARNING severity level
     * @param string $message
     */
    public function warning($message) {
        $this->log($message, self::WARNING);
    }

    /**
     * Output message to log with NOTICE severity level
     * @param string $message
     */
    public function notice($message) {
        $this->log($message, self::NOTICE);
    }

    /**
     * Output message to log with INFO severity level
     * @param string $message
     */
    public function info($message) {
        $this->log($message, self::INFO);
    }

    /**
     * Output message to log with DEBUG severity level
     * @param string $message
     */
    public function debug($message) {
        $this->log($message, self::DEBUG);
    }

}
