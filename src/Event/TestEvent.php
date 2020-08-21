<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\TestDescriber;

class TestEvent extends AbstractEvent
{
    public function __construct(TestDescriber $describer)
    {
        parent::__construct($describer);
    }

    public function returnParameter($value)
    {
        return $value;
    }

    public function echo($value): void
    {
        echo $value . PHP_EOL;
    }
}
