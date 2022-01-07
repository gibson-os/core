<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Cronjob;

class TimePart
{
    public function __construct(private int $from, private int $to)
    {
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }
}
