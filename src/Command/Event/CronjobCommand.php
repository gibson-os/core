<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\CodeGeneratorService;
use GibsonOS\Core\Service\EventService;

class CronjobCommand extends AbstractCommand
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var CodeGeneratorService
     */
    private $codeGeneratorService;

    /**
     * @var EventService
     */
    private $eventService;

    public function __construct(
        EventRepository $eventRepository,
        CodeGeneratorService $codeGeneratorService,
        EventService $eventService
    ) {
        $this->eventRepository = $eventRepository;
        $this->codeGeneratorService = $codeGeneratorService;
        $this->eventService = $eventService;
    }

    /**
     * @throws DateTimeError
     */
    protected function run(): int
    {
        $this->eventService->fire(TimeDescriber::TRIGGER_CRONJOB);

        return 0;
    }
}
