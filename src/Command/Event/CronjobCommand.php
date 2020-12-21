<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\CodeGeneratorService;
use GibsonOS\Core\Service\EventService;
use Psr\Log\LoggerInterface;

class CronjobCommand extends AbstractCommand
{
    private EventRepository $eventRepository;

    private CodeGeneratorService $codeGeneratorService;

    private EventService $eventService;

    public function __construct(
        EventRepository $eventRepository,
        CodeGeneratorService $codeGeneratorService,
        EventService $eventService,
        LoggerInterface $logger
    ) {
        $this->eventRepository = $eventRepository;
        $this->codeGeneratorService = $codeGeneratorService;
        $this->eventService = $eventService;

        parent::__construct($logger);
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
