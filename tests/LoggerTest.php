<?php

class LoggerTest extends TipyTestCase {

    public function testDefaultLogger() {
        $logger = new TipyLogger();
        $this->assertEqual($logger->threshold, TipyLogger::DEBUG);
        $this->assertEqual($this->getFilePath($logger), 'php://stderr');
    }

    public function testLogOutput() {
        $logger = new TipyLogger('php://memory');
        $logger->format = '[%level]';
        $handle = $this->getHandle($logger);
        $this->assertNotNull($handle);
        $logger->log('Debug message', TipyLogger::DEBUG);
        $logger->log('Info  message', TipyLogger::INFO);
        $logger->log('Warn  message', TipyLogger::WARN);
        $logger->log('Error message', TipyLogger::ERROR);
        $logger->log('Fatal message', TipyLogger::FATAL);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log,
"[DEBUG] Debug message
[INFO] Info  message
[WARN] Warn  message
[ERROR] Error message
[FATAL] Fatal message".PHP_EOL
        );
    }

    public function testLogToFile() {
        $logger = new TipyLogger(__DIR__.'/test.log');
        $logger->format = '[%level]';
        $logger->info("I am in file");
        $this->assertTrue(file_exists($this->getFilePath($logger)));
        $log = file_get_contents($this->getFilePath($logger));
        $this->assertEqual($log, "[INFO] I am in file".PHP_EOL);
    }

    public function testInfoThreshold() {
        $logger = new TipyLogger('php://memory');
        $logger->threshold = TipyLogger::INFO;
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log,
"[INFO] Info message
[WARN] Warn message
[ERROR] Error message
[FATAL] Fatal message".PHP_EOL
        );
    }

    public function testWarnThreshold() {
        $logger = new TipyLogger('php://memory');
        $logger->threshold = TipyLogger::WARN;
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log,
"[WARN] Warn message
[ERROR] Error message
[FATAL] Fatal message".PHP_EOL
        );
    }

    public function testErrorThreshold() {
        $logger = new TipyLogger('php://memory');
        $logger->threshold = TipyLogger::ERROR;
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log,
"[ERROR] Error message
[FATAL] Fatal message".PHP_EOL
        );
    }

    public function testFatalThreshold() {
        $logger = new TipyLogger('php://memory');
        $logger->threshold = TipyLogger::FATAL;
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, "[FATAL] Fatal message".PHP_EOL);
    }

    // Helper functions

    private function logAllLevels($logger) {
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warn('Warn message');
        $logger->error('Error message');
        $logger->fatal('Fatal message');
    }

    /**
     * Get access to private TipyLogger::$handle
     */
    private function getHandle($logger) {
        $ref = new ReflectionClass('TipyLogger');
        $handle = $ref->getProperty('handle');
        $handle->setAccessible(true);
        return $handle->getValue($logger);
    }

    /**
     * Get access to private TipyLogger::$filePath
     */
    private function getFilePath($logger) {
        $ref = new ReflectionClass('TipyLogger');
        $filePath = $ref->getProperty('filePath');
        $filePath->setAccessible(true);
        return $filePath->getValue($logger);
    }


    private function getLogContents($logger) {
        $handle = $this->getHandle($logger);
        fseek($handle, 0);
        return stream_get_contents($handle);
    }

}
