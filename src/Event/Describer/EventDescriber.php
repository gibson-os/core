<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\AutoComplete\EventAutoComplete;
use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Event\Describer\Parameter\EventParameter;
use GibsonOS\Core\Event\EventEvent;

class EventDescriber implements DescriberInterface
{
    private EventParameter $eventParameter;

    public function __construct(EventAutoComplete $eventAutoComplete)
    {
        $this->eventParameter = new EventParameter($eventAutoComplete);
    }

    public function getTitle(): string
    {
        return 'Event';
    }

    public function getTriggers(): array
    {
        return [];
    }

    public function getMethods(): array
    {
        return [
            'start' => (new Method('Starten'))
                ->setParameters(['event' => $this->eventParameter]),
            'activate' => (new Method('Aktivieren'))
                ->setParameters(['event' => $this->eventParameter]),
            'deactivate' => (new Method('Dektivieren'))
                ->setParameters(['event' => $this->eventParameter]),
        ];
    }

    public function getEventClassName(): string
    {
        return EventEvent::class;
    }
}
