<?php

class MysqlCloneDb {

    var $database = null;
    var $engine;

    function __construct($db, $oldDbName, $newDbName, $engine = false){
        $this->engine   = $engine;
        $this->database = $db;
        $this->cloneStructure($oldDbName, $newDbName);
    }

    function setDatabase($db, $oldDbName){
        $this->database = $db;
        if ( !@mysql_select_db($this->database) )
            return false;
        return true;
    }

    function cloneStructure($oldDbName, $newDbName){
        $this->database->select_db($oldDbName);
        $result = $this->database->query('SHOW TABLES');
        if ( $result->num_rows == 0 ) {
            return false;
        }

        // get table structure for every table
        $structure = '';
        while ( $record = $result->fetch_row() ) {
            $structure .= $this->getTableStructure($record[0], $this->engine);
        }
        $result->close();

        // switch to new DB
        if ( !$this->database->select_db($newDbName) ) {
            $this->database->query("CREATE DATABASE ".$newDbName) or die("Can't create a new one: ".$this->database->error.PHP_EOL);
        }
        $this->database->select_db($newDbName)  or die ("Invalid database:".$this->database->error);

        // apply table structure on new DB
        $dump = explode(';', $structure);
        foreach($dump as $query) {
            $query = trim($query);
            if ($query) {
                $result = $this->database->query($query) or die ("Invalid query:".$this->database->error.PHP_EOL);
            }
        }

        return true;
    }

    function getTableStructure($table, $engine = false, $autoincrement = false){
        $structure = "";
        // Dump Structure
        $structure .= 'DROP TABLE IF EXISTS `'.$table.'`;'."\n";
        $structure .= "CREATE TABLE `".$table."` (\n";

        $result = $this->database->query('SHOW FIELDS FROM `'.$table.'`');
        if ( $result->num_rows == 0 ) {
            return false;
        }
        while ( $record = $result->fetch_assoc() ) {
            $structure .= '`'.$record['Field'].'` '.$record['Type'];
            if ( !empty($record['Default']) )
                $structure .= ' DEFAULT \''.$record['Default'].'\'';
            if ( @strcmp($record['Null'],'YES') != 0 )
                $structure .= ' NOT NULL';
            if ( !empty($record['Extra']) )
                $structure .= ' '.$record['Extra'];
            $structure .= ",\n";
        }
        $structure = preg_replace("/,\n$/", null, $structure);

        // Save all Column Indexes
        $structure .= $this->getSqlKeysTable($table);
        $structure .= "\n)";

        //Save table engine
        $result = $this->database->query("SHOW TABLE STATUS LIKE '".$table."'");

        if ( $record = $result->fetch_assoc() ) {
            if ($engine) {
                $structure .= ' ENGINE='.$engine;
            } else {
                if ( !empty($record['Engine']) )
                    $structure .= ' ENGINE='.$record['Engine'];
            }
 
            if ( $autoincrement && !empty($record['Auto_increment']) ) {
                $structure .= ' AUTO_INCREMENT='.$record['Auto_increment'];
            }
        }
        $structure .= ";\n";

        return $structure;
    }

    function getSqlKeysTable ($table) {
        $primary  = "";
        $unique   = array();
        $index    = array();
        $fulltext = array();
        $result = $this->database->query("SHOW KEYS FROM `{$table}`");
        if ( $result->num_rows == 0 )
            return false;
        while($row = $result->fetch_object()) {
            if (($row->Key_name == 'PRIMARY') AND ($row->Index_type == 'BTREE')) {
                if ( $primary == "" )
                    $primary = "  PRIMARY KEY  (`{$row->Column_name}`";
                else
                    $primary .= ", `{$row->Column_name}`";
            }
            if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '0') AND ($row->Index_type == 'BTREE')) {
                if ( !$unique OR $unique[$row->Key_name] == "" )
                    $unique[$row->Key_name] = "  UNIQUE KEY `{$row->Key_name}` (`{$row->Column_name}`";
                else
                    $unique[$row->Key_name] .= ", `{$row->Column_name}`";
            }
            if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'BTREE')) {
                if ( !$index OR $index[$row->Key_name] == "" )
                    $index[$row->Key_name] = "  KEY `{$row->Key_name}` (`{$row->Column_name}`";
                else
                    $index[$row->Key_name] .= ", `{$row->Column_name}`";
            }
            if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'FULLTEXT')) {
                if ( !$fulltext OR $fulltext[$row->Key_name]=="" )
                    $fulltext[$row->Key_name] = "  FULLTEXT `{$row->Key_name}` (`{$row->Column_name}`";
                else
                    $fulltext[$row->Key_name] .= ", `{$row->Column_name}`";
            }
        }
        $sqlKeyStatement = '';
        // generate primary, unique, key and fulltext
        if ( $primary != "" ) {
            $sqlKeyStatement .= ",\n";
            $primary .= ")";
            $sqlKeyStatement .= $primary;
        }
        foreach ($unique as $keyName => $keyDef) {
            $sqlKeyStatement .= ",\n";
            $keyDef .= ")";
            $sqlKeyStatement .= $keyDef;

        }
        foreach ($index as $keyName => $keyDef) {
            $sqlKeyStatement .= ",\n";
            $keyDef .= ")";
            $sqlKeyStatement .= $keyDef;
        }
        foreach ($fulltext as $keyName => $keyDef) {
            $sqlKeyStatement .= ",\n";
            $keyDef .= ")";
            $sqlKeyStatement .= $keyDef;
        }
        return $sqlKeyStatement;
    }

}