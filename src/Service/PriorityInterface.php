<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

interface PriorityInterface
{
    public function getPriority(): int;
}
