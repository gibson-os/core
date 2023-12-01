<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\EventService;

class TestEventService extends EventService
{
    public function runEvent(Event $event, bool $async): void
    {
        parent::runEvent($event, false);
    }
}
