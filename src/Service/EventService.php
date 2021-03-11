<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use Exception;
use GibsonOS\Core\Command\Event\RunCommand;
use GibsonOS\Core\Dto\Event\Describer\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\ElementService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Psr\Log\LoggerInterface;

class EventService extends AbstractService
{
    private array $events = [];

    private ServiceManagerService $serviceManagerService;

    private EventRepository $eventRepository;

    private ElementService $elementService;

    private CommandService $commandService;

    private DateTimeService $dateTimeService;

    private LoggerInterface $logger;

    public function __construct(
        ServiceManagerService $serviceManagerService,
        EventRepository $eventRepository,
        ElementService $codeGeneratorService,
        CommandService $commandService,
        DateTimeService $dateTimeService,
        LoggerInterface $logger
    ) {
        $this->serviceManagerService = $serviceManagerService;
        $this->eventRepository = $eventRepository;
        $this->elementService = $codeGeneratorService;
        $this->commandService = $commandService;
        $this->dateTimeService = $dateTimeService;
        $this->logger = $logger;
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
        $this->logger->info('Fire event "' . $trigger . '"');
        $events = array_merge(
            $this->events[$trigger] ?? [],
            $this->eventRepository->getTimeControlled($trigger, new DateTime())
        );

        foreach ($events as $event) {
            if ($event instanceof Event) {
                if ($this->checkTriggerParameters($event, $trigger, $parameters ?? [])) {
                    $this->runEvent($event, $event->isAsync());
                }
            } else {
                $event($parameters);
            }
        }
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     * @throws SaveError
     */
    public function runEvent(Event $event, bool $async): void
    {
        if ($async) {
            $this->logger->info('Run async event ' . $event->getId());
            $this->commandService->executeAsync(RunCommand::class, ['eventId' => $event->getId()], []);

            return;
        }

        $this->logger->info('Run event ' . $event->getId());
        $event->setLastRun($this->dateTimeService->get())->save();
        $this->elementService->runElements($event->getElements() ?? []);
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     */
    private function checkTriggerParameters(Event $event, string $trigger, array $parameters): bool
    {
        foreach ($event->getTriggers() ?? [] as $eventTrigger) {
            if ($eventTrigger->getTrigger() !== $trigger) {
                continue;
            }

            /** @var DescriberInterface $describer */
            $describer = $this->serviceManagerService->get($eventTrigger->getClass());
            $triggers = $describer->getTriggers();
            $triggerParameters = $triggers[$eventTrigger->getTrigger()]->getParameters();
            $eventParameters = JsonUtility::decode($eventTrigger->getParameters() ?? '[]');

            foreach ($triggerParameters as $parameterName => $triggerParameter) {
                if (!$triggerParameter instanceof AutoCompleteParameter) {
                    continue;
                }

                $parameters[$parameterName] = $triggerParameter->getAutoComplete()->getIdFromModel($parameters[$parameterName]);
            }

            foreach ($eventParameters ?? [] as $parameterName => $eventParameter) {
                if (!isset($parameters[$parameterName])) {
                    continue;
                }

                if (!$this->elementService->getConditionResult($parameters[$parameterName], $eventParameter['operator'], $eventParameter['value'])) {
                    $this->logger->debug(
                        'Trigger parameter for event ' . $event->getId() . ' not true' .
                        '(' . $parameters[$parameterName] . ' ' . $eventParameter['operator'] . ' ' . $eventParameter['value'] . ')'
                    );

                    return false;
                }
            }

            $this->logger->debug('Parameter passed for trigger "' . $trigger . '"');

            return true;
        }

        $this->logger->info('Trigger "' . $trigger . '" not defined for event ' . $event->getId());

        return false;
    }
}
