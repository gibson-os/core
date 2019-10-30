<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\ProcessError;

class Process extends AbstractService
{
    /**
     * @param string $command
     * @param string $mode
     *
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
     * @param string $command
     *
     * @return string
     */
    public function execute(string $command): string
    {
        return exec($command);
    }
}
