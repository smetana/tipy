<?php
/**
 * TipyDAO
 *
 * @package tipy
 */


/**
 * Thown on database-related errors
 */
class TipyDaoException extends Exception {}

/**
 * Throw this to rollback transaction
 */
class TipyRollbackException extends Exception {}


/**
 * Database connection wrapper with basic database functions
 *
 *
 */
class TipyDAO {

    /**
     * @var mysqli|null
     */
    protected $dbLink;
    /**
     * @internal
     */
    public static $openTransactionsCount = 0;
    /**
     * @internal
     */
    public static $queryCount;

    /**
     * Connect to database
     */
    public function __construct() {
        $app = TipyApp::getInstance();
        // If not yet connected then connect.
        if (!$app->db) {
            // Get  connection string
            $config = $app->config;
            $dbHost = $config->get('db_host');
            $dbPort = $config->get('db_port') ?: null;
            $dbName = $config->get('db_name');
            $dbUser = $config->get('db_user');
            $dbPassword = $config->get('db_password');

            $dbLink = new mysqli('p:'.$dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
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

    /**
     * Query database
     *
     * <code>
     * $dao = new TipyDAO();
     * $result = $dao->query('select * from users where first_name=? and last_name=?', ['john', 'doe']);
     * </code>
     *
     * @param string $sql SQL template with ? placeholders
     * @param array $params Array with values to fill placeholders
     * @throws TipyDaoException
     * @return mysqli_result
     */
    public function query($sql, $params = null) {
        if ($params) {
            $sql = str_replace('%', '%%', $sql);
            $sql = str_replace('?', '"%s"', $sql);
            $link = $this->dbLink;
            array_walk($params, function (&$string) use ($link) {
                if ($string === null) {
                    $string = 'TIPY_SQL_NULL_VALUE';
                }
                $string = $link->real_escape_string($string);
            });
            array_unshift($params, $sql);
            $query = call_user_func_array('sprintf', $params);
            $query = str_replace('"TIPY_SQL_NULL_VALUE"', 'NULL', $query);
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
    public function limitQuery($sql, $span, $step, $params = null) {
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
    public function queryRow($sql, $params = null) {
        $result = $this->query($sql, $params);
        return $this->fetchRow($result);
    }

    // ----------------------------------------------------
    // queryRows
    // query and fetch all rows in one flakon
    // ----------------------------------------------------
    public function queryAllRows($sql, $params = null) {
        $result = $this->query($sql, $params);
        return $this->fetchAllRows($result);
    }

    // ----------------------------------------------------
    // limit query and fetch
    // ----------------------------------------------------
    public function limitQueryAllRows($sql, $span, $step, $params = null) {
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

    protected static function newSavepointName() {
        return self::savepointName(self::$openTransactionsCount + 1);
    }

    // ----------------------------------------------------
    // Start transaction with fallback mechanics
    // ----------------------------------------------------
    protected static function startTransaction() {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            $result = $app->db->query('BEGIN');
        } else {
            $result = $app->db->query('SAVEPOINT '.self::newSavepointName());
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
            self::rollbackTransaction('hard');
        }
    }

    // ----------------------------------------------------
    // Commit transaction
    // ----------------------------------------------------
    protected static function commit() {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif (self::$openTransactionsCount == 1) {
            $result = $app->db->query('COMMIT');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
            return $result;
        } elseif (self::$openTransactionsCount > 1) {
            $result = $app->db->query('RELEASE SAVEPOINT '.self::currentSavepointName());
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
    protected static function rollbackTransaction($kind = 'soft') {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif ($kind == 'hard') {
            // rollback parent transaction with all nested savepoints
            $result = $app->db->query('ROLLBACK');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
        } elseif (self::$openTransactionsCount == 1) {
            $result = $app->db->query('ROLLBACK');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
        } elseif (self::$openTransactionsCount > 1) {
            $result = $app->db->query('ROLLBACK TO SAVEPOINT '.self::currentSavepointName());
            if ($result) {
                self::$openTransactionsCount--;
            }
        } else {
            // Just to be sure
            throw new TipyDaoException('Negative open transactions counter. Please contact tipy maintainers');
        }
        return $result;
    }

    // ----------------------------------------------------
    // Public method to force transaction rollback
    // ----------------------------------------------------
    public static function rollback() {
        // Not about exception message:
        // By default this exception is to be caught inside TipyDAO::transaction()
        // If it is not caught this means TipyDAO::rollback() was called outside transaction
        // Then this exception message makes sense
        throw new TipyRollbackException("Uncaught rollback exception. Probably called outside transaction");
    }

    //   TipyDAO::transaction(function() {
    //      $this->save();
    //   });
    //
    //   To rollback transaction call TipyDAO::rollback()
    //
    //   TipyDAO::transaction(function() {
    //      $this->save();
    //      TipyDAO::rollback();
    //   });
    //
    //   To use variable from upper scope
    //
    //   $a = 1;
    //   $b = 2;
    //   TipyDAO::transaction(function() use ($a, $b) {
    //      echo $a + $b;
    //   });
    public static function transaction($closure) {
        try {
            self::startTransaction();
            $result = $closure();
            self::commit();
            return $result;
        } catch (TipyRollbackException $e) {
            self::rollbackTransaction();
        } catch (Exception $e) {
            self::rollbackTransaction();
            throw $e;
        }
    }

    // ----------------------------------------------------
    // Return true if any transaction in progress
    // ----------------------------------------------------
    public static function isTransactionInProgress() {
        return self::$openTransactionsCount > 0;
    }
}
