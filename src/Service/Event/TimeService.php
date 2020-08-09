<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

class TimeService extends AbstractEventService
{
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
