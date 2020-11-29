<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerService implements LoggerInterface
{
    public const LEVEL_EMERGENCY = 1;

    public const LEVEL_ALERT = 2;

    public const LEVEL_CRITICAL = 3;

    public const LEVEL_ERROR = 4;

    public const LEVEL_WARNING = 5;

    public const LEVEL_NOTICE = 6;

    public const LEVEL_INFO = 7;

    public const LEVEL_DEBUG = 8;

    private const LOG_LEVELS = [
        LogLevel::EMERGENCY => self::LEVEL_EMERGENCY,
        LogLevel::ALERT => self::LEVEL_ALERT,
        LogLevel::CRITICAL => self::LEVEL_CRITICAL,
        LogLevel::ERROR => self::LEVEL_ERROR,
        LogLevel::WARNING => self::LEVEL_WARNING,
        LogLevel::NOTICE => self::LEVEL_NOTICE,
        LogLevel::INFO => self::LEVEL_INFO,
        LogLevel::DEBUG => self::LEVEL_DEBUG,
    ];

    private $level = self::LEVEL_ERROR;

    private $writeOut = false;

    private $debug = false;

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $level = self::LOG_LEVELS[$level];

        if ($level > $this->level) {
            return;
        }

        $caller = debug_backtrace();
        $callerPosition = $context['callerPosition'] ?? 1;
        $message =
            $this->getLevelPrefix($level) .
            ($this->debug ? $caller[$callerPosition]['file'] . '(' . $caller[$callerPosition]['line'] . ') ' : '') . "\033[0m " .
            var_export($message, true)
        ;

        //$this->writeOut($level, $message);
        error_log($message);
    }

    public function setLevel(int $level): LoggerService
    {
        $this->level = $level;

        return $this;
    }

    public function setWriteOut(bool $writeOut): LoggerService
    {
        $this->writeOut = $writeOut;

        return $this;
    }

    public function setDebug(bool $debug): LoggerService
    {
        $this->debug = $debug;

        return $this;
    }

    private function getLevelPrefix(int $level): string
    {
        switch ($level) {
            case self::LEVEL_EMERGENCY:
                return "\033[101m EMERGENCY ";
            case self::LEVEL_ALERT:
                return "\033[101m ALERT ";
            case self::LEVEL_CRITICAL:
                return "\033[101m CRITICAL ";
            case self::LEVEL_ERROR:
                return "\033[101m ERROR ";
            case self::LEVEL_WARNING:
                return "\033[43m WARNING ";
            case self::LEVEL_NOTICE:
                return "\033[44m NOTICE ";
            case self::LEVEL_INFO:
                return "\033[44m INFO ";
            case self::LEVEL_DEBUG:
                return "\033[43m DEBUG ";
        }

        return '';
    }

    private function writeOut(int $level, string $message): void
    {
        if (!$this->writeOut) {
            return;
        }

        $handle = STDOUT;

        switch ($level) {
            case self::LEVEL_EMERGENCY:
            case self::LEVEL_ALERT:
            case self::LEVEL_CRITICAL:
            case self::LEVEL_ERROR:
            case self::LEVEL_WARNING:
                $handle = STDERR;
        }

        fwrite($handle, $message);
    }
}
