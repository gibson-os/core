<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use Override;

class EventAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->eventRepository->findByName($namePart, (bool) ($parameters['onlyActive'] ?? false));
    }

    #[Override]
    public function getById(string $id, array $parameters): Event
    {
        return $this->eventRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.event.model.Grid';
    }

    #[Override]
    public function getValueField(): string
    {
        return 'id';
    }

    #[Override]
    public function getDisplayField(): string
    {
        return 'name';
    }
}
