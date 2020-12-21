<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

interface CommandInterface
{
    /**
     * @param string[] $arguments
     */
    public function setArguments(array $arguments): CommandInterface;

    public function setOptions(array $options): CommandInterface;

    public function execute(): int;
}
