<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

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

    private int $level = self::LEVEL_ERROR;

    private bool $writeOut = false;

    private bool $debug = false;

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
            $this->getLevelColor($level) . ' ' . $this->getLevelPrefix($level) .
            ($this->debug ? $caller[$callerPosition]['file'] . ':' . $caller[$callerPosition]['line'] . ' ' : '') . "\033[0m " .
            var_export($message, true)
        ;

        if (
            $this->debug &&
            isset($context['exception']) &&
            $context['exception'] instanceof Throwable
        ) {
            $message .=
                PHP_EOL . $this->getLevelColor($level) . PHP_EOL . PHP_EOL .
                "\t" . $context['exception']::class . ': ' . $context['exception']->getMessage() . PHP_EOL
            ;

            foreach ($context['exception']->getTrace() as $trace) {
                $message .= "\t" . $trace['file'] . ':' . $trace['line'] . ($trace['type'] ?? ' ') . $trace['function'] . '()' . PHP_EOL;
            }

            $message .= "\033[0m " . PHP_EOL;
        }

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
        return match ($level) {
            self::LEVEL_EMERGENCY => 'EMERGENCY ',
            self::LEVEL_ALERT => 'ALERT ',
            self::LEVEL_CRITICAL => 'CRITICAL ',
            self::LEVEL_ERROR => 'ERROR ',
            self::LEVEL_WARNING => 'WARNING ',
            self::LEVEL_NOTICE => 'NOTICE ',
            self::LEVEL_INFO => 'INFO ',
            self::LEVEL_DEBUG => 'DEBUG ',
            default => '',
        };
    }

    private function getLevelColor(int $level): string
    {
        return match ($level) {
            self::LEVEL_EMERGENCY, self::LEVEL_ALERT, self::LEVEL_CRITICAL, self::LEVEL_ERROR => "\033[101m",
            self::LEVEL_WARNING, self::LEVEL_DEBUG => "\033[43m",
            self::LEVEL_NOTICE, self::LEVEL_INFO => "\033[44m",
            default => '',
        };
    }

    private function writeOut(int $level, string $message): void
    {
        if (!$this->writeOut) {
            return;
        }

        $handle = match ($level) {
            self::LEVEL_EMERGENCY, self::LEVEL_ALERT, self::LEVEL_CRITICAL, self::LEVEL_ERROR, self::LEVEL_WARNING => STDERR,
            default => STDOUT,
        };

        fwrite($handle, $message);
    }
}
