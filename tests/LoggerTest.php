<?php

class LoggerTest extends TipyTestCase {
    
    public function testDefaultLogger() {
        $logger = new TipyLogger();
        $this->assertEqual($logger->threshold, TipyLogger::DEBUG);
        $this->assertEqual($logger->filePath, 'php://stderr');
    }

    public function testLogOutput() {
        $logger = new TipyLogger(TipyLogger::DEBUG, 'php://memory');
        $logger->format = '[%level]';
        $handler = $this->getFileHandler($logger);
        $this->assertNotNull($handler);
        $logger->log('Debug message', TipyLogger::DEBUG);
        $logger->log('Info  message', TipyLogger::INFO);
        $logger->log('Notic message', TipyLogger::NOTICE);
        $logger->log('Warni message', TipyLogger::WARNING);
        $logger->log('Error message', TipyLogger::ERROR);
        $logger->log('Criti message', TipyLogger::CRITICAL);
        $logger->log('Alert message', TipyLogger::ALERT);
        $logger->log('Emerg message', TipyLogger::EMERGENCY);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[debug] Debug message
[info] Info  message
[notice] Notic message
[warning] Warni message
[error] Error message
[critical] Criti message
[alert] Alert message
[emergency] Emerg message".PHP_EOL
        );
    }

    public function testLogToFile() {
        $logger = new TipyLogger(TipyLogger::DEBUG, __DIR__.'/test.log');
        $logger->format = '[%level]';
        $logger->info("I am in file");
        $this->assertTrue(file_exists($logger->filePath));
        $log = file_get_contents($logger->filePath);
        $this->assertEqual($log, "[info] I am in file".PHP_EOL);
    }

    public function testInfoThreshold() {
        $logger = new TipyLogger(TipyLogger::INFO, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[info] Info message
[notice] Notice message
[warning] Warning message
[error] Error message
[critical] Critical message
[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testNoticeThreshold() {
        $logger = new TipyLogger(TipyLogger::NOTICE, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[notice] Notice message
[warning] Warning message
[error] Error message
[critical] Critical message
[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testWarningThreshold() {
        $logger = new TipyLogger(TipyLogger::WARNING, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[warning] Warning message
[error] Error message
[critical] Critical message
[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testErrorThreshold() {
        $logger = new TipyLogger(TipyLogger::ERROR, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[error] Error message
[critical] Critical message
[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testCriticalThreshold() {
        $logger = new TipyLogger(TipyLogger::CRITICAL, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[critical] Critical message
[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testAlertThreshold() {
        $logger = new TipyLogger(TipyLogger::ALERT, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, 
"[alert] Alert message
[emergency] Emergency message".PHP_EOL
        );
    }

    public function testEmergencyThreshold() {
        $logger = new TipyLogger(TipyLogger::EMERGENCY, 'php://memory');
        $logger->format = '[%level]';
        $this->logAllLevels($logger);
        $log = $this->getLogContents($logger);
        $this->assertEqual($log, "[emergency] Emergency message".PHP_EOL);
    }

    // Helper functions

    private function logAllLevels($logger) {
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->notice('Notice message');
        $logger->warning('Warning message');
        $logger->error('Error message');
        $logger->critical('Critical message');
        $logger->alert('Alert message');
        $logger->emergency('Emergency message');
    }

    /**
     * Get access to private TipyLogger::$fileHandler
     */
    private function getFileHandler($logger) {
        $ref = new ReflectionClass('TipyLogger');
        $handler = $ref->getProperty('fileHandler');
        $handler->setAccessible(true);
        return $handler->getValue($logger);
    }

    private function getLogContents($logger) {
        $handler = $this->getFileHandler($logger);
        fseek($handler, 0);
        return stream_get_contents($handler);
    }

}
