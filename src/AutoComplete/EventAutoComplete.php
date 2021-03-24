<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;

class EventAutoComplete implements AutoCompleteInterface
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->eventRepository->findByName($namePart, (bool) ($parameters['onlyActive'] ?? false));
    }

    public function getById(string $id, array $parameters): Event
    {
        return $this->eventRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.event.model.Grid';
    }
}
