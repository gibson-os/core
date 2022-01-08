<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

interface CommandInterface
{
    public function execute(): int;
}
