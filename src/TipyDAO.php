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
     * MySQL database connection
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
     * @var TipyLogger
     */
    public $logger;

    /**
     * Connect to database
     */
    public function __construct() {
        $app = TipyApp::getInstance();
        $this->logger = $app->logger;
        // If not yet connected then connect.
        if (!$app->db) {
            // Get  connection string
            $config = $app->config;
            $dbHost = $config->get('db_host');
            $dbPort = $config->get('db_port') ?: null;
            $dbName = $config->get('db_name');
            $dbUser = $config->get('db_user');
            $dbPassword = $config->get('db_password');

            $this->logger->debug('Connect to database');
            $dbLink = new mysqli('p:'.$dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
            if ($dbLink->connect_error) {
                throw new TipyDaoException('DB connection error (' . $dbLink->connect_errno . ') '
                    . $dbLink->connect_error);
            } else {
                if ($config->get('db_character_set')) {
                    $query = "set names '".$config->get('db_character_set')."'";
                    $this->logger->debug($query);
                    $dbLink->query($query);
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
        $this->logger->debug($query);
        $result = $this->dbLink->query($query);
        self::$queryCount++;
        if (!$result) {
            throw new TipyDaoException($this->dbLink->error);
        } else {
            return $result;
        }
    }


    /**
     * Return the number of rows limited by $limit starting from $offset
     *
     * <code>
     * $dao = new TipyDAO();
     * // Select rows 6-15
     * $result = $dao->imitQuery('select * from users', 5, 10);
     * </code>
     *
     * @param string $sql SQL template with ? placeholders
     * @param integer $offset offset of the first row
     * @param integer $limit number of rows to return
     * @param array $params Array with values to fill placeholders
     * @throws TipyDaoException
     * @return mysqli_result
     */
    public function limitQuery($sql, $offset, $limit, $params = null) {
        $sql = $sql." limit ".$limit." offset ".$offset;
        return $this->query($sql, $params);
    }

    /**
     * Return the number of rows from $result
     *
     * @param mysqli $result
     * @return integer
     */
    public function numRows(&$result) {
        return $result->num_rows;
    }

    /**
     * Return row as associative array from $result
     *
     * @param mysqli $results
     * @return array
     */
    public function fetchRow(&$result) {
        $result->field_seek(0);
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * Return all rows as array of associative arrays from $result
     *
     * @param mysqli $results
     * @return array
     */
    public function fetchAllRows(&$result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Query and fetch one row
     *
     * @param string $sql SQL template with ? placeholders
     * @param array $params Array with values to fill placeholders
     * @return array
     */
    public function queryRow($sql, $params = null) {
        $result = $this->query($sql, $params);
        return $this->fetchRow($result);
    }

    /**
     * Query and fetch all rows
     *
     * @param string $sql SQL template with ? placeholders
     * @param array $params Array with values to fill placeholders
     * @return array
     */
    public function queryAllRows($sql, $params = null) {
        $result = $this->query($sql, $params);
        return $this->fetchAllRows($result);
    }

    /**
     * Query and fetch all rows with paginated query
     *
     * @param string $sql SQL template with ? placeholders
     * @param integer $offset offset of the first row
     * @param integer $limit number of rows to return
     * @param array $params Array with values to fill placeholders
     * @return array|null
     */
    public function limitQueryAllRows($sql, $offset, $limit, $params = null) {
        $result = $this->limitQuery($sql, $offset, $limit, $params);
        if ($result) {
            return $this->fetchAllRows($result);
        } else {
            return null;
        }
    }

    /**
     * Return id auto generated by the last insert query
     *
     * By default returns integer but tf the number is greater
     * than maximal int value then return string
     * @return integer|string
     */
    public function lastInsertId() {
        return $this->dbLink->insert_id;
    }

    /**
     * Return the number of rows affected by the last query
     *
     * By default returns integer but tf the number is greater
     * than maximal int value then return string
     * @return integer|string
     */
    public function affectedRows() {
        return $this->dbLink->affected_rows;
    }

    /**
     * Execute $closure() inside SQL transaction block
     *
     * <code>
     * TipyModel::transaction(function() {
     *     $model = new MyModel();
     *     $model->attribute = 'value';
     *     $model->save();
     * });
     * </code>
     *
     * To rollback transaction call TipyDAO::rollback()
     *
     * <code>
     * TipyModel::transaction(function() {
     *     $model = new MyModel();
     *     $model->attribute = 'value';
     *     $model->save();
     *     TipyModel::rollback();
     * });
     * </code>
     *
     * To use current scope variables inside closure
     *
     * <code>
     * $a = 1;
     * $b = 2;
     * TipyModel::transaction(function() use ($a, $b) {
     *     echo $a + $b;
     * });
     * </code>
     *
     * @param Closure $closure
     */
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

    /**
     * Rollback transaction
     *
     * Throws TipyRollbackException inside TipyDAO::transaction() try-catch statement.
     * If exception is not caught then TipyDAO::rollback() was called outside transaction.
     * @throws TipyRollbackException
     */
    public static function rollback() {
        throw new TipyRollbackException("Uncaught rollback exception. Probably called outside transaction");
    }

    /**
     * Return true if there is transaction in progress
     * @return boolean
     */
    public static function isTransactionInProgress() {
        return self::$openTransactionsCount > 0;
    }

    /**
     * Rollback all opened transaction on fatal errors or
     * script stop
     * @internal
     */
    public function shutdownCheck() {
        if (self::isTransactionInProgress()) {
            self::rollbackTransaction('hard');
        }
    }

    /**
     * Generate savepoint name from number
     * @internal
     * @return string
     */
    protected static function savepointName($number) {
        return 'tipy_savepoint_'.($number - 1);
    }

    /**
     * Savepoing name for current transaction
     * @internal
     * @return string|null
     */
    public static function currentSavepointName() {
        if (self::$openTransactionsCount <= 1) {
            return null;
        } else {
            return self::savepointName(self::$openTransactionsCount);
        }
    }

    /**
     * Savepoing name for next transaction
     * @internal
     * @return string
     */
    protected static function newSavepointName() {
        return self::savepointName(self::$openTransactionsCount + 1);
    }

    /**
     * Start transaction
     * @return mysqli_result
     */
    private static function startTransaction() {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            $app->logger->debug('BEGIN');
            $result = $app->db->query('BEGIN');
        } else {
            $app->logger->debug('SAVEPOINT '.self::newSavepointName());
            $result = $app->db->query('SAVEPOINT '.self::newSavepointName());
        }
        if ($result) {
            self::$openTransactionsCount++;
        }
        return $result;
    }

    /**
     * Commit transaction
     * @throws TipyDaoException if there is no transaction in progress
     * @return mysqli_result
     */
    private static function commit() {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif (self::$openTransactionsCount == 1) {
            $app->logger->debug('COMMIT');
            $result = $app->db->query('COMMIT');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
            return $result;
        } elseif (self::$openTransactionsCount > 1) {
            $app->logger->debug('RELEASE SAVEPOINT '.self::currentSavepointName());
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

    /**
     * Rollback transaction
     * @throws TipyDaoException if there is no transaction in progress
     * @return mysqli_result
     */
    private static function rollbackTransaction($kind = 'soft') {
        $app = TipyApp::getInstance();
        if (self::$openTransactionsCount == 0) {
            throw new TipyDaoException('No transaction in progress');
        } elseif ($kind == 'hard') {
            // rollback parent transaction with all nested savepoints
            $app->logger->debug('ROLLBACK');
            $result = $app->db->query('ROLLBACK');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
        } elseif (self::$openTransactionsCount == 1) {
            $app->logger->debug('ROLLBACK');
            $result = $app->db->query('ROLLBACK');
            if ($result) {
                self::$openTransactionsCount = 0;
            }
        } elseif (self::$openTransactionsCount > 1) {
            $app->logger->debug('ROLLBACK TO SAVEPOINT '.self::currentSavepointName());
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

}
