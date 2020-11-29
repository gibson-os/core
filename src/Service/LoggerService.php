<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Psr\Log\LoggerInterface;

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

    private $level = self::LEVEL_ERROR;

    private $writeOut = false;

    public function emergency($message, array $context = []): void
    {
        $this->log(self::LEVEL_EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(self::LEVEL_ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(self::LEVEL_NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        if ($level > $this->level) {
            return;
        }

        $caller = debug_backtrace();
        $callerPosition = $context['callerPosition'] ?? 0;
        $message =
            $this->getLevelPrefix($level) .
            $caller[$callerPosition]['file'] . '(' . $caller[$callerPosition]['line'] . '): ' .
            var_export($message, true)
        ;

        $this->writeOut($level, $message);
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

    private function getLevelPrefix(int $level): string
    {
        switch ($level) {
            case self::LEVEL_EMERGENCY:
                return '\033[101m EMERGENCY \033[0m ';
            case self::LEVEL_ALERT:
                return '\033[101m ALERT \033[0m ';
            case self::LEVEL_CRITICAL:
                return '\033[101m CRITICAL \033[0m ';
            case self::LEVEL_ERROR:
                return '\033[101m ERROR \033[0m ';
            case self::LEVEL_WARNING:
                return '\033[43m WARNING \033[0m ';
            case self::LEVEL_NOTICE:
                return '\033[44m NOTICE \033[0m ';
            case self::LEVEL_INFO:
                return '\033[44m INFO \033[0m ';
            case self::LEVEL_DEBUG:
                return '\033[43m DEBUG \033[0m ';
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
