<?php
/**
 * This file contains exceptions classes and tipyErrorHandler
 *
 * @package tipy
 */

/**
 * Thrown on E_WARNING error
 */
class WarningException extends ErrorException {}

/**
 * Thrown on E_PARSE error
 */
class ParseException extends ErrorException {}

/**
 * Thrown on E_NOTICE error
 */
class NoticeException extends ErrorException {}

/**
 * Thrown on E_CODE_ERROR error
 */
class CoreErrorException extends ErrorException {}

/**
 * Thrown on E_CORE_WARNING error
 */
class CoreWarningException extends ErrorException {}

/**
 * Thrown on E_COMPILE_ERROR error
 */
class CompileErrorException extends ErrorException {}

/**
 * Thrown on E_COMPILE_WARNING error
 */
class CompileWarningException extends ErrorException {}

/**
 * Thrown on E_USER_ERROR error
 */
class UserErrorException extends ErrorException {}

/**
 * Thrown on E_USER_WARNING error
 */
class UserWarningException extends ErrorException {}

/**
 * Thrown on E_USER_NOTICE error
 */
class UserNoticeException extends ErrorException {}

/**
 * Thrown on E_STRICT error
 */
class StrictException extends ErrorException {}

/**
 * Thrown on E_RECOVERABLE_ERROR error
 */
class RecoverableErrorException extends ErrorException {}

/**
 * Thrown on E_DEPRECATED error
 */
class DeprecatedException extends ErrorException {}

/**
 * Thrown on E_USER_DEPRECATED error
 */
class UserDeprecatedException extends ErrorException {}

/**
 * Base Tipy exception class
 */
class TipyException extends Exception {}

/**
 * Thrown when a method is called on a class which doesn't have it defined
 */
class NoMethodException extends TipyException {}

/**
 * Error handler function to convert PHP errors to exceptions.
 * @internal
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
