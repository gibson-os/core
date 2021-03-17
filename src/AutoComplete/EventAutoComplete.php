<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\AutoCompleteException;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
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

    public function getById($id, array $parameters): Event
    {
        return $this->eventRepository->getById($id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.event.model.Grid';
    }

    /**
     * @throws AutoCompleteException
     */
    public function getIdFromModel(AutoCompleteModelInterface $model): int
    {
        if (!$model instanceof Event) {
            throw new AutoCompleteException(sprintf('Model is not instance of %s', Event::class));
        }

        return $model->getId() ?? 0;
    }
}
