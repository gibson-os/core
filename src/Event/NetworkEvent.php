<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\IntParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\NetworkService;

#[Event('Netzwerk')]
class NetworkEvent extends AbstractEvent
{
    #[Event\Trigger('Vor dem pingen', [
        ['key' => 'host', 'className' => StringParameter::class, 'title' => 'Host'],
        ['key' => 'timeout', 'className' => IntParameter::class, 'title' => 'Timeout'],
    ])]
    public const TRIGGER_BEFORE_PING = 'beforePing';

    #[Event\Trigger('Nach dem pingen', [
        ['key' => 'host', 'className' => StringParameter::class, 'title' => 'Host'],
        ['key' => 'timeout', 'className' => IntParameter::class, 'title' => 'Timeout'],
        ['key' => 'result', 'className' => BoolParameter::class, 'title' => 'Erreichbar'],
    ])]
    public const TRIGGER_AFTER_PING = 'afterPing';

    public function __construct(EventService $eventService, private NetworkService $networkService)
    {
        parent::__construct($eventService);
    }

    #[Event\Method('Ping')]
    public function ping(
        #[Event\Parameter(StringParameter::class, 'Host')] string $host,
        #[Event\Parameter(IntParameter::class, 'Timeout')] int $timeout
    ): bool {
        return $this->networkService->ping($host, $timeout);
    }
}
