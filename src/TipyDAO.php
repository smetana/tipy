<?php

class TipyDaoException extends Exception {}

// ---------------------------------------------------------
// ApplicationDAO
// DB wrapper and base DAO class. Provides interface to
// database base functions
// ---------------------------------------------------------
class TipyDAO {

    protected $dbLink;
    public static $openTransactionsCount = 0;
    public static $queryCount;

    // -----------------------------------------------------
    // Constructor
    // -----------------------------------------------------
    public function __construct() {
        $app = TipyApp::getInstance();
        // If not yet connected then connect.
        if (!$app->db) {
            // Get  connection string
            $config = $app->config;
            $dbHost = $config->get('db_host');
            if ($config->get('db_port')) {
                $dbHost .= $dbHost.':'.$config->get('db_port');
            }
            $dbName = $config->get('db_name');
            $dbUser = $config->get('db_user');
            $dbPassword = $config->get('db_password');

            $dbLink = new mysqli('p:'.$dbHost, $dbUser, $dbPassword, $dbName);
            if ($dbLink->connect_error) {
                throw new TipyDaoException('DB connection error (' . $dbLink->connect_errno . ') '
                    . $dbLink->connect_error);
            } else {
                if ($config->get('db_character_set')) {
                    $dbLink->query("set names '".$config->get('db_character_set')."'");
                }
                $app->db = $dbLink;
            }

            register_shutdown_function([$this, "shutdownCheck"]);
        }
        $this->dbLink = $app->db;
    }

    // ----------------------------------------------------
    // query
    // Just db query wrapper with some error handler
    // ----------------------------------------------------
    public function query($sql, $params = null) {
        if ($params) {
            $sql = str_replace('%', '%%', $sql);
            $sql = str_replace('?', '"%s"', $sql);
            $link = $this->dbLink;
            array_walk($params, function (&$string) use ($link) {
                $string = $link->real_escape_string($string);
            });
            array_unshift($params, $sql);
            $query = call_user_func_array('sprintf', $params);
        } else {
            $query = $sql;
        }
        $result = $this->dbLink->query($query);
        self::$queryCount++;
        if (!$result) {
            throw new TipyDaoException($this->dbLink->error);
        } else {
            return $result;
        }
    }

    // ----------------------------------------------------
    // limitQuery
    // ----------------------------------------------------
    public function limitQuery($sql, $span, $step, $params = []) {
        $sql = $sql." limit ".$span.",".$step;
        return $this->query($sql, $params);
    }

    // -----------------------------------------------------
    // numRows
    // -----------------------------------------------------
    public function numRows(&$result) {
        return $result->num_rows;
    }

    // -----------------------------------------------------
    // fetchRow
    // Need this method to do some common operations before
    // returning fetchRow from query result
    // -----------------------------------------------------
    public function fetchRow(&$result) {
        $result->field_seek(0);
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    // -----------------------------------------------------
    // fetchAllRows - return all rows of the DB_result object
    // -----------------------------------------------------
    public function fetchAllRows(&$result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ----------------------------------------------------
    // queryRow
    // query & fetch in one flakon
    // ----------------------------------------------------
    public function queryRow($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $this->fetchRow($result);
    }

    // ----------------------------------------------------
    // queryRows
    // query and fetch all rows in one flakon
    // ----------------------------------------------------
    public function queryAllRows($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $this->fetchAllRows($result);
    }

    // ----------------------------------------------------
    // limit query and fetch
    // ----------------------------------------------------
    public function limitQueryAllRows($sql, $span, $step, $params = []) {
        $result = $this->limitQuery($sql, $span, $step, $params);
        if ($result) {
            return $this->fetchAllRows($result);
        } else {
            return null;
        }
    }

    // ----------------------------------------------------
    // last insert id
    // ----------------------------------------------------
    public function lastInsertId() {
        return $this->dbLink->insert_id;
    }

    // ----------------------------------------------------
    // affected rows
    // ----------------------------------------------------
    public function affectedRows() {
        return $this->dbLink->affected_rows;
    }

    public function queryErrno($sql, $params = []) {
        $this->query($sql, $params, true);
        return $this->dbLink->errno;
    }

    protected static function savepointName($number) {
        return 'tipy_savepoint_'.($number - 1);
    }

    public static function currentSavepointName() {
        if (self::$openTransactionsCount <= 1) {
            return null;
        } else {
            return self::savepointName(self::$openTransactionsCount);
        }
    }

    public static function newSavepointName() {
        return self::savepointName(self::$openTransactionsCount + 1);
    }

    // ----------------------------------------------------
    // Start transaction with fallback mechanics
    // ----------------------------------------------------
    public function startTransaction() {
        if (self::$openTransactionsCount == 0) {
            $result = $this->dbLink->query('BEGIN');
        } else {
            $result = $this->dbLink->query('SAVEPOINT '.self::newSavepointName());
        }
        if ($result) {
            self::$openTransactionsCount++;
        }
        return $result;
    }

    // ----------------------------------------------------
    // Rollback all opened transaction on fatal errors or
    // script stop
    // ----------------------------------------------------
    public function shutdownCheck() {
        if (self::isTransactionInProgress()) {
            $this->rollback('hard');
        }
    }

    // ----------------------------------------------------
    // Commit transaction
    // ----------------------------------------------------
    public function commit() {
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif (self::$openTransactionsCount == 1) {
            $result = $this->dbLink->query('COMMIT');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
            return $result;
        } elseif (self::$openTransactionsCount > 1) {
            $result = $this->dbLink->query('RELEASE SAVEPOINT '.self::currentSavepointName());
            if ($result) {
                self::$openTransactionsCount--;
            }
            return $result;
        } else {
            // Just to be sure
            throw new TipyDaoException('Negative open transactions counter. Please contact tipy maintainers');
        }
    }

    // ----------------------------------------------------
    // Rollback transaction
    // ----------------------------------------------------


    public function rollback($kind = 'soft') {
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif ($kind == 'hard') {
            // rollback parent transaction with all nested savepoints
            $result = $this->dbLink->rollback();
            if ($result) {
                self::$openTransactionsCount = 0;
            }
        } elseif (self::$openTransactionsCount == 1) {
            $result = $this->dbLink->rollback();
            if ($result) {
                self::$openTransactionsCount = 0;
            }
            return $result;
        } elseif (self::$openTransactionsCount > 1) {
            $result = $this->dbLink->query('ROLLBACK TO SAVEPOINT '.self::currentSavepointName());
            if ($result) {
                self::$openTransactionsCount--;
            }
            return $result;
        } else {
            // Just to be sure
            throw new TipyDaoException('Negative open transactions counter. Please contact tipy maintainers');
        }
        return $result;
    }

    // ----------------------------------------------------
    // Return true if any transaction in progress
    // ----------------------------------------------------
    public function isTransactionInProgress() {
        return self::$openTransactionsCount > 0;
    }
}
