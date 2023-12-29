<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\ProcessError;
use Psr\Log\LoggerInterface;

class ProcessService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TracerService $tracerService,
    ) {
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

    /**
     * @throws ProcessError
     */
    public function execute(string $command): string
    {
        $this->tracerService->startSpan('Execute process', ['command' => $command]);
        $this->logger->debug(sprintf('Execute process "%s"', $command));
        $return = exec($command);
        $this->tracerService->stopSpan();

        return $return !== false ? $return : throw new ProcessError('Exec error!');
    }

    public function executeAsync(string $command): void
    {
        $this->tracerService->startSpan('Execute async process', ['command' => $command]);
        $this->logger->debug(sprintf('Execute async process "%s"', $command));

        system($command . '> /dev/null 2>/dev/null &');
        $this->tracerService->stopSpan();
    }

    public function kill(int $pid): bool
    {
        $this->tracerService->startSpan('Kill process', ['pid' => $pid]);
        $this->logger->debug(sprintf('Kill process %d', $pid));

        $return = 0;
        $out = [];
        exec('kill -s 9 ' . $pid, $out, $return);
        $this->tracerService->stopSpan();

        return $return === 0;
    }

    public function pidExists(int $pid): bool
    {
        return function_exists('posix_getpgid')
            ? posix_getpgid($pid) !== false
            : file_exists('/proc/' . $pid)
        ;
    }
}
