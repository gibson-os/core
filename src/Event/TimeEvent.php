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

    /**
     * @param int $seconds
     */
    public function sleep($seconds)
    {
        sleep($seconds);
    }

    /**
     * @param int $microseconds
     */
    public function usleep($microseconds)
    {
        usleep($microseconds);
    }
}
