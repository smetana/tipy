<?php

class TipyDaoException extends Exception {}

// ---------------------------------------------------------
// ApplicationDAO
// DB wrapper and base DAO class. Provides interface to
// database base functions
// ---------------------------------------------------------
class TipyDAO {

    protected $dbLink;
    protected static $isTransactionInProgress = false;
    public static $queryCount;

    // -----------------------------------------------------
    // Constructor
    // -----------------------------------------------------
    public function __construct() {
        $app = Tipy::getInstance();
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

            $dbLink = @new mysqli('p:'.$dbHost, $dbUser, $dbPassword, $dbName);
            if ($dbLink->connect_error) {
                throw new TipyDaoException('DB connection error (' . $dbLink->connect_errno . ') '
                    . $dbLink->connect_error);
            } else {
                if ($config->get('db_character_set')) {
                    $dbLink->query("set names '".$config->get('db_character_set')."'");
                }
                $dbLink->autocommit(true);
                $app->db = $dbLink;
            }
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
            array_walk($params, function(&$string) use ($link) { 
                $string = $link->real_escape_string($string);
            });
            array_unshift($params,$sql);
            $query = call_user_func_array('sprintf',$params);
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
    public function limitQuery($sql, $span, $step, $params = array()) {
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
    public function queryRow($sql, $params = array()) {
        $result = $this->query($sql, $params);
        return $this->fetchRow($result);
    }

    // ----------------------------------------------------
    // queryRows
    // query and fetch all rows in one flakon
    // ----------------------------------------------------
    public function queryAllRows($sql, $params = array()) {
        $result = $this->query($sql, $params);
        return $this->fetchAllRows($result);
    }

    // ----------------------------------------------------
    // limit query and fetch
    // ----------------------------------------------------
    public function limitQueryAllRows($sql, $span, $step, $params = array()) {
        $result = $this->limitQuery($sql, $span, $step,  $params);
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

    public function queryErrno($sql, $params = array()) {
        $this->query($sql, $params, true);
        return $this->dbLink->errno;
    }

    // ----------------------------------------------------
    // autocommit on and off
    // ----------------------------------------------------
    public function autocommit($mode) {
        return $this->dbLink->autocommit($mode);
    }

    // ----------------------------------------------------
    // Start transaction with fallback mechanics
    // ----------------------------------------------------
    public function startTransaction() {
        if(self::$isTransactionInProgress) {
            throw new TipyDaoException('Transaction alredy in progress');
        }
        self::$isTransactionInProgress = true;
        register_shutdown_function(array($this, "shutdownCheck"));
        return $this->dbLink->begin_transaction();
    }

    // ----------------------------------------------------
    // Falback method for prevent locking DB
    // ----------------------------------------------------
    public function shutdownCheck() {
        if (self::$isTransactionInProgress) {
            $this->rollback();
        }
    }

    // ----------------------------------------------------
    // Commit transaction
    // ----------------------------------------------------
    public function commit() {
        if(!self::$isTransactionInProgress) {
            throw new TipyDaoException('No any transaction in progress');
        }
        $result = $this->dbLink->commit();
        self::$isTransactionInProgress = false;
        return $result;
    }

    // ----------------------------------------------------
    // Rollback transaction
    // ----------------------------------------------------
    public function rollback() {
        if(!self::$isTransactionInProgress) {
            throw new TipyDaoException('No any transaction in progress');
        }
        $result = $this->dbLink->rollback();
        self::$isTransactionInProgress = false;
        return $result;
    }

    // ----------------------------------------------------
    // Return true if any transaction in progress
    // ----------------------------------------------------
    public function isTransactionInProgress() {
        return self::$isTransactionInProgress;
    }

}

