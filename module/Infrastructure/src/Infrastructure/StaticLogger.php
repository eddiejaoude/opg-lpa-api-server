<?php

namespace Infrastructure;

use RuntimeException;
use Zend\Log\LoggerInterface;

class StaticLogger
{
    ### PUBLIC METHODS

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function emerg(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->emerg($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function alert(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->alert($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function crit(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->crit($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function err(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->err($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function warn(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->warn($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function notice(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->notice($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function info(
        $message,
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->info($message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public static function debug(
        $message, 
        $extra = array()
    )
    {
        $logger = self::getLogger();
        $logger->debug($message, $extra);
    }

    public static function setLogger(
        LoggerInterface $logger
    )
    {
        if (self::$logger
            && $logger != self::$logger) {
            throw new RuntimeException('Logger cannot be changed once set');
        }

        self::$logger = $logger;
    }

    ### PRIVATE MEMBERS

    private static $logger;

    ### PRIVATE METHODS

    private static function getLogger()
    {
        if (!self::$logger) {
            throw new RuntimeException('Logger has not been set');
        }

        return self::$logger;
    }
}
