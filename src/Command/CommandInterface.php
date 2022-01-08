<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

interface CommandInterface
{
    /**
     * @param bool[] $options
     */
    public function setOptions(array $options): CommandInterface;

    public function execute(): int;
}
