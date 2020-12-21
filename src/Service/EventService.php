<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use Exception;
use GibsonOS\Core\Command\Event\RunCommand;
use GibsonOS\Core\Event\AbstractEvent;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\CodeGeneratorService;

class EventService extends AbstractService
{
    private array $events = [];

    private ServiceManagerService $serviceManagerService;

    private EventRepository $eventRepository;

    private CodeGeneratorService $codeGeneratorService;

    private CommandService $commandService;

    public function __construct(
        ServiceManagerService $serviceManagerService,
        EventRepository $eventRepository,
        CodeGeneratorService $codeGeneratorService,
        CommandService $commandService
    ) {
        $this->serviceManagerService = $serviceManagerService;
        $this->eventRepository = $eventRepository;
        $this->codeGeneratorService = $codeGeneratorService;
        $this->commandService = $commandService;
    }

    public function add(string $trigger, callable $function): void
    {
        if (!isset($this->events[$trigger])) {
            $this->events[$trigger] = [];
        }

        $this->events[$trigger][] = $function;
    }

    /**
     * @throws DateTimeError
     * @throws Exception
     */
    public function fire(string $trigger, array $parameters = null): void
    {
        // @todo Parameter müssen irgendwie noch im Trigger abgeglichen werden oder an die Methoden weiter gegeben werden
        $events = array_merge(
            $this->events[$trigger] ?? [],
            $this->eventRepository->getTimeControlled($trigger, new DateTime())
        );

        foreach ($events as $event) {
            if ($event instanceof Event) {
                $this->runEvent($event, $event->isAsync());
            } else {
                $event($parameters);
            }
        }
    }

    /**
     * @throws DateTimeError
     */
    public function runEvent(Event $event, bool $async): void
    {
        if ($async) {
            $this->commandService->executeAsync(RunCommand::class, ['eventId' => $event->getId()], []);

            return;
        }

        eval($this->codeGeneratorService->generateByElements($event->getElements() ?? []));
    }

    /**
     * @throws FactoryError
     *
     * @return
     */
    public function runFunction(Element $element)
    {
        /** @var DescriberInterface $describer */
        $describer = $this->serviceManagerService->get($element->getClass());
        /** @var AbstractEvent $service */
        $service = $this->serviceManagerService->get($describer->getEventClassName());

        return $service->run($element);
    }
}
