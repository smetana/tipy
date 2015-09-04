<?php

require_once 'autoload.php';

class TipyTestRecord extends TipyModel { }


// WARNING: This testcase does not use transactional fixtures automatically

class TestDAO extends TipyTestSuite {

    // Do not use transactional fixtures
    // to test native transactions
    public $transactionalFixtures = false;

    // transactional fixtures are disable so we need separate 
    // table to test transactions behaviour without affecting 
    // other tests
    public function afterTest() {
        $app = TipyApp::getInstance();
        $app->db->query('TRUNCATE TABLE tipy_test_records');
    }

    public function testQuery() {
        $this->createRecord(1);
        $this->createRecord(2);
        $this->createRecord(3);
        $dao = new TipyDAO();
        $result = $dao->query("select * from tipy_test_records where value = ?", ['value_2']);
        $this->assertTrue(is_a($result, 'mysqli_result'));
        $this->assertEqual($dao->numRows($result), 1);
        $result = $dao->query("select * from tipy_test_records");
        $this->assertTrue(is_a($result, 'mysqli_result'));
        $this->assertEqual($dao->numRows($result), 3);
    }

    public function testTransactionCommit() {
        $this->assertEqual(TipyTestRecord::count(), 0);
        TipyModel::transaction(function() {
            $this->createRecord(1);
            $this->createRecord(2);
            $this->createRecord(3);
            $this->assertEqual(TipyTestRecord::count(), 3);
        });
        $this->assertEqual(TipyTestRecord::count(), 3);
    }

    public function testTransactionRollback() {
        $this->assertEqual(TipyTestRecord::count(), 0);
        TipyModel::transaction(function() {
            $this->createRecord(1);
            $this->createRecord(2);
            $this->createRecord(3);
            $this->assertEqual(TipyTestRecord::count(), 3);
            TipyModel::rollback();
        });
        $this->assertEqual(TipyTestRecord::count(), 0);
    }

    public function testRollbackWithoutTransaction() {
        $this->assertThrown('TipyRollbackException', "Uncaught rollback exception. Probably called outside transaction", function () {
            TipyModel::rollback();
        });
    }

    public function testDoubleRollback() {
        $this->assertEqual(TipyTestRecord::count(), 0);
        TipyModel::transaction(function() {
            $this->createRecord(1);
            TipyModel::transaction(function() {
                $this->createRecord(2);
                $this->createRecord(3);
                $this->createRecord(4);
                TipyModel::rollback();
                echo "This line and below should never be executed!";
                TipyModel::rollback();
                TipyModel::rollback();
            });
        });
        $this->assertEqual(TipyTestRecord::count(), 1);
    }

    public function testReturnFromTransaction() {
        $this->assertEqual(TipyTestRecord::count(), 0);
        $result = TipyModel::transaction(function() {
            $this->createRecord(1);
            $this->createRecord(2);
            $this->createRecord(3);
            $this->assertEqual(TipyTestRecord::count(), 3);
            return "Three records created";
        });
        $this->assertEqual(TipyTestRecord::count(), 3);
        $this->assertSame($result, "Three records created");
    }

    public function testExceptionIsTrownThrough() {
        $this->assertEqual(TipyTestRecord::count(), 0);
        # escaped from TipyModel::transaction()
        $this->assertThrown("Exception", "Kolobok!", function() {
            TipyModel::transaction(function() {
                $this->createRecord(1);
                throw new Exception("Kolobok!");
            });
        });
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
        TipyModel::transaction(function() use ($dao){
            $this->createRecord(1);
            $this->createRecord(2);
            $this->createRecord(3);
            $this->assertEqual(TipyTestRecord::count(), 3);
            $this->assertEqual($dao->currentSavepointName(), null);
            TipyModel::transaction(function() use ($dao){
                $this->createRecord(4);
                $this->createRecord(5);
                $this->assertEqual(TipyTestRecord::count(), 5);
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
                TipyModel::rollback();
            });
            $this->assertEqual($dao->currentSavepointName(), null);
            $this->assertEqual(TipyTestRecord::count(), 3);
            $this->createRecord(4);
            $this->assertEqual(TipyTestRecord::count(), 4);
            TipyModel::transaction(function() use ($dao) {
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
                $this->createRecord(5);
                $this->createRecord(6);
                $this->assertEqual(TipyTestRecord::count(), 6);
                TipyModel::transaction(function() use ($dao) {
                    $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_2');
                    $this->createRecord(7);
                    $this->createRecord(8);
                    $this->assertEqual(TipyTestRecord::count(), 8);
                    TipyModel::rollback();
                });
                $this->assertEqual($dao->currentSavepointName(), 'tipy_savepoint_1');
            });
            $this->assertEqual(TipyTestRecord::count(), 6);
            $this->assertEqual($dao->currentSavepointName(), null);
        });
        $this->assertEqual(TipyTestRecord::count(), 6);
    }

    public function testNull() {
        TipyModel::transaction(function() {
            $profile = TipyTestProfile::create([
                'userId' => 1,
                'sign' => null
            ]);
            $this->assertNull($profile->sign);
            $profile->sign = 'sign';
            $profile->save();
            $this->assertNotNull($profile->sign);
            $profile->sign = null;
            $profile->save();
            $this->assertNull($profile->sign);
            TipyModel::rollback();
        });
    }

    private function createRecord($i) {
        $user = TipyTestRecord::create([
            'value' => 'value_'.$i
        ]);
    }

}
