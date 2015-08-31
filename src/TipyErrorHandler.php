<?php
/**
 * tipy : Tiny PHP MVC framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright     Copyright (c) 2008-2015 Serge Smetana <serge.smetana@gmail.com>
 * @copyright     Copyright (c) 2008-2015 Roman Zhbadynskyi <zhbadynskyi@gmail.com>
 * @link          https://github.com/smetana/tipy
 * @author        Serge Smetana <serge.smetana@gmail.com>
 * @author        Roman Zhbadynskyi <zhbadynskyi@gmail.com>
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

class WarningException extends ErrorException {}
class ParseException extends ErrorException {}
class NoticeException extends ErrorException {}
class CoreErrorException extends ErrorException {}
class CoreWarningException extends ErrorException {}
class CompileErrorException extends ErrorException {}
class CompileWarningException extends ErrorException {}
class UserErrorException extends ErrorException {}
class UserWarningException extends ErrorException {}
class UserNoticeException extends ErrorException {}
class StrictException extends ErrorException {}
class RecoverableErrorException extends ErrorException {}
class DeprecatedException extends ErrorException {}
class UserDeprecatedException extends ErrorException {}
class NoMethodException extends ErrorException {}

/**
 * Error handler function to convert PHP errors to exceptions.
 *
 * User controllers and actions are wrapped in try-catch statements.
 * Also it is good for TipyModel transactions.
 */
function tipyErrorHandler($severity, $msg, $file, $line, array $context) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    switch ($severity) {
        case E_ERROR:
            throw new ErrorException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_WARNING:
            throw new WarningException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_PARSE:
            throw new ParseException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_NOTICE:
            throw new NoticeException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_CORE_ERROR:
            throw new CoreErrorException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_CORE_WARNING:
            throw new CoreWarningException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_COMPILE_ERROR:
            throw new CompileErrorException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_COMPILE_WARNING:
            throw new CompileWarningException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_USER_ERROR:
            throw new UserErrorException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_USER_WARNING:
            throw new UserWarningException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_USER_NOTICE:
            throw new UserNoticeException($msg,
                0, $severity, $file, $line);
            break;
        case E_STRICT:
            throw new StrictException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_RECOVERABLE_ERROR:
            throw new RecoverableErrorException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_DEPRECATED:
            throw new DeprecatedException(
                $msg, 0, $severity, $file, $line);
            break;
        case E_USER_DEPRECATED:
            throw new UserDeprecatedException(
                $msg, 0, $severity, $file, $line);
    }
}
