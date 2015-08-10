<?php

require_once 'autoload.php';

class TipyTestRecord extends TipyModel { }

class TestDAO extends TipyTestSuite {

    // Rewrite beforeTest and afterTest to disable transactional fixtures
    // Also we need separate table to test transactions behaviour without
    // affecting other tests
    public function beforeTest() {
        $this->run = true;
        $app = TipyApp::getInstance();
        $app->db->query("
             CREATE TABLE `tipy_test_records` (
                `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                `value` VARCHAR( 20 ) NULL,
                PRIMARY KEY ( `id` )
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8
        ");
    }

    public function afterTest() {
        $app = TipyApp::getInstance();
        $app->db->query('DROP TABLE tipy_test_records');
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
            try {
                $this->$testName();
            } catch (Exception $e) {
                $this->run = false;
                $this->exceptions[] = $e;
                $colors = new Colors();
                echo $colors->getColoredString("E", 'red');
            }
            $this->afterTest();
        }
    }

    public function testTransactionCommit() {
        $dao = new TipyDAO();
        $this->assertEqual(TipyTestRecord::count(), 0);
        $dao = new TipyDAO();
        $dao->startTransaction();
        $this->createRecord(1);
        $this->createRecord(2);
        $this->createRecord(3);
        $this->assertEqual(TipyTestRecord::count(), 3);
        $dao->commit();
        $this->assertEqual(TipyTestRecord::count(), 3);
    }

    public function testTransactionRollback() {
        $dao = new TipyDAO();
        $this->assertEqual(TipyTestRecord::count(), 0);
        $dao = new TipyDAO();
        $dao->startTransaction();
        $this->createRecord(1);
        $this->createRecord(2);
        $this->createRecord(3);
        $this->assertEqual(TipyTestRecord::count(), 3);
        $dao->rollback();
        $this->assertEqual(TipyTestRecord::count(), 0);
    }

    public function testLockForUpdate() {
        $this->createRecord(1);
        $this->assertEqual(TipyTestRecord::count(), 1);
        $this->assertThrown('TipyDaoException', 'No any transaction in progress', function () {
            $user = TipyTestRecord::findFirst();
            $user->lockForUpdate();
        });
    }

    public function testNestedTransaction() {
        $dao = new TipyDAO();
        $this->assertEqual($dao->currentSavepointName(), null);
        $this->assertEqual(TipyTestRecord::count(), 0);
        $dao->startTransaction();
            $this->createRecord(1);
            $this->createRecord(2);
            $this->createRecord(3);
            $this->assertEqual(TipyTestRecord::count(), 3);
            $this->assertEqual($dao->currentSavepointName(), null);
            $dao->startTransaction();
                $this->createRecord(4);
                $this->createRecord(5);
                $this->assertEqual(TipyTestRecord::count(), 5);
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
            $dao->rollback();
            $this->assertEqual($dao->currentSavepointName(), null);
            $this->assertEqual(TipyTestRecord::count(), 3);
            $this->createRecord(4);
            $this->assertEqual(TipyTestRecord::count(), 4);
            $dao->startTransaction();
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
                $this->createRecord(5);
                $this->createRecord(6);
                $this->assertEqual(TipyTestRecord::count(), 6);
                $dao->startTransaction();
                    $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_2');
                    $this->createRecord(7);
                    $this->createRecord(8);
                    $this->assertEqual(TipyTestRecord::count(), 8);
                $dao->rollback();
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
            $dao->commit();
            $this->assertEqual(TipyTestRecord::count(), 6);
            $this->assertEqual($dao->currentSavepointName(), null);
        $dao->commit();
        $this->assertEqual(TipyTestRecord::count(), 6);
    }

    private function createRecord($i) {
        $user = TipyTestRecord::create([
            'value' => 'value_'.$i
        ]);
    }

}
