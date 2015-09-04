<?php
/**
 * TipyConfig
 *
 * @package tipy
 */

/**
 * Access config variables
 *
 * Config variables are defined in <b>config.ini</b> file in your application root directory
 */
class TipyConfig extends TipyIOWrapper {

    /**
     * Construct TipyConfig instance from parsed config.ini
     */
    public function __construct() {
        parent::__construct();
        defined('INI_FILE') || define('INI_FILE', getcwd().'/../config.ini');
        $iniData = parse_ini_file(INI_FILE);
        $this->bind($iniData);
    }

}
