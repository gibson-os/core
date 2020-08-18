<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\TimeDescriber;

class TimeService extends AbstractEventService
{
    public function __construct(TimeDescriber $describer)
    {
        parent::__construct($describer);
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
