<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\ProcessError;
use Psr\Log\LoggerInterface;

class ProcessService extends AbstractService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws ProcessError
     *
     * @return resource
     */
    public function open(string $command, string $mode)
    {
        $process = popen($command . ' 2>&1', $mode);

        if (is_bool($process)) {
            throw new ProcessError(sprintf('Kommando "%s" konnte nicht ausgeführt werden!', $command));
        }

        return $process;
    }

    /**
     * @param resource $process
     */
    public function close($process): void
    {
        pclose($process);
    }

    public function execute(string $command): string
    {
        $this->logger->debug(sprintf('Execute proccess "%s"', $command));

        return exec($command);
    }

    public function executeAsync(string $command): void
    {
        $this->logger->debug(sprintf('Execute async proccess "%s"', $command));

        system($command . '> /dev/null 2>/dev/null &');
    }

    public function kill(int $pid): bool
    {
        return posix_kill($pid, 0);
    }

    public function pidExists(int $pid): bool
    {
        return file_exists('/proc/' . $pid);
    }
}
