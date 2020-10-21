<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\TestDescriber;
use GibsonOS\Core\Service\ServiceManagerService;

class TestEvent extends AbstractEvent
{
    public function __construct(TestDescriber $describer, ServiceManagerService $serviceManagerService)
    {
        parent::__construct($describer, $serviceManagerService);
    }

    public function returnParameter(int $value): int
    {
        return $value;
    }

    public function echo(string $value): void
    {
        echo $value . PHP_EOL;
    }
}
