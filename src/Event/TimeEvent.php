<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Service\ServiceManagerService;

class TimeEvent extends AbstractEvent
{
    public function __construct(TimeDescriber $describer, ServiceManagerService $serviceManagerService)
    {
        parent::__construct($describer, $serviceManagerService);
    }

    public function sleep(int $seconds)
    {
        sleep($seconds);
    }

    public function usleep(int $microseconds)
    {
        usleep($microseconds);
    }
}
