<?php
/**
 *
 * This file contains exceptions classes and tipyErrorHandler
 *
 */

/**
 * Replaced PHP error with E_WARNING severity
 */
class WarningException extends ErrorException {}

/**
 * Replaced PHP error with E_PARSE severity
 */
class ParseException extends ErrorException {}

/**
 * Replaced PHP error with E_NOTICE severity
 */
class NoticeException extends ErrorException {}

/**
 * Replaced PHP error with E_CODE_ERROR severity
 */
class CoreErrorException extends ErrorException {}

/**
 * Replaced PHP error with E_CORE_WARNING severity
 */
class CoreWarningException extends ErrorException {}

/**
 * Replaced PHP error with E_COMPILE_ERROR severity
 */
class CompileErrorException extends ErrorException {}

/**
 * Replaced PHP error with E_COMPILE_WARNING severity
 */
class CompileWarningException extends ErrorException {}

/**
 * Replaced PHP error with E_USER_ERROR severity
 */
class UserErrorException extends ErrorException {}

/**
 * Replaced PHP error with E_USER_WARNING severity
 */
class UserWarningException extends ErrorException {}

/**
 * Replaced PHP error with E_USER_NOTICE severity
 */
class UserNoticeException extends ErrorException {}

/**
 * Replaced PHP error with E_STRICT severity
 */
class StrictException extends ErrorException {}

/**
 * Replaced PHP error with E_RECOVERABLE_ERROR severity
 */
class RecoverableErrorException extends ErrorException {}

/**
 * Replaced PHP error with E_DEPRECATED severity
 */
class DeprecatedException extends ErrorException {}

/**
 * Replaced PHP error with E_USER_DEPRECATED severity
 */
class UserDeprecatedException extends ErrorException {}

/**
 * Base Tipy exception class
 */
class TipyException extends Exception {}

/**
 * Raised when a method is called on a class which doesn't have it defined
 */
class NoMethodException extends TipyException {}

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
