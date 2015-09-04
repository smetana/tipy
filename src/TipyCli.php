<?php
/**
 * TipyCli
 *
 * @package tipy
 */

/**
 * Various helpers for CLI mode
 *
 * For now this class has methods to return colored strings for console output
 *
 * @method static black(string $string)
 * @method static red(string $string)
 * @method static green(string $string)
 * @method static brown(string $string)
 * @method static blue(string $string)
 * @method static purple(string $string)
 * @method static cyan(string $string)
 * @method static lightGray(string $string)
 * @method static darkGray(string $string)
 * @method static lightRed(string $string)
 * @method static lightGreen(string $string)
 * @method static yellow(string $string)
 * @method static lightBlue(string $string)
 * @method static lightPurple(string $string)
 * @method static lightCyan(string $string)
 * @method static white(string $string)
 */
class TipyCli {

    private static $cliColors = [
        'black'       => '0;30',
        'red'         => '0;31',
        'green'       => '0;32',
        'brown'       => '0;33',
        'blue'        => '0;34',
        'purple'      => '0;35',
        'cyan'        => '0;36',
        'lightGray'   => '0;37',
        'darkGray'    => '1;30',
        'lightRed'    => '1;31',
        'lightGreen'  => '1;32',
        'yellow'      => '1;33',
        'lightBlue'   => '1;34',
        'lightPurple' => '1;35',
        'lightCyan'   => '1;36',
        'white'       => '1;37',
    ];

    /**
     * @internal
     */
    public static function __callStatic($name, $args) {
        if (in_array($name, array_keys(self::$cliColors))) {
            $str = $args[0];
            if (!posix_isatty(STDOUT)) {
                return $str;
            }
            return  "\033[".self::$cliColors[$name]."m".$str."\033[0m";
        } else {
            throw new NoMethodException('Call to undefined method TipyCli::'.$name.'()');
        }
    }
}
