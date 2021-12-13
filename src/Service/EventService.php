<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Attribute\Event\Trigger;
use GibsonOS\Core\Command\Event\RunCommand;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\ElementService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

class EventService extends AbstractService
{
    private array $events = [];

    public function __construct(
        private ServiceManagerService $serviceManagerService,
        private EventRepository $eventRepository,
        private ElementService $elementService,
        private CommandService $commandService,
        private DateTimeService $dateTimeService,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param class-string $className
     */
    public function add(string $className, string $trigger, callable $function): void
    {
        $triggerName = $className . '::' . $trigger;
        $this->logger->info('Add event for trigger ' . $className . '::' . $trigger);

        if (!isset($this->events[$trigger])) {
            $this->events[$triggerName] = [];
        }

        $this->events[$triggerName][] = $function;
    }

    /**
     * @param class-string $className
     *
     * @throws DateTimeError
     * @throws EventException
     * @throws FactoryError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function fire(string $className, string $trigger, array $parameters = null): void
    {
        $triggerName = $this->getTriggerName($className, $trigger);
        $this->logger->info('Fire event ' . $triggerName);
        $events = array_merge(
            $this->events[$triggerName] ?? [],
            $this->eventRepository->getTimeControlled($className, $trigger, new DateTime())
        );

        foreach ($events as $event) {
            if ($event instanceof Event) {
                if ($this->checkTriggerParameters($event, $className, $trigger, $parameters ?? [])) {
                    $this->runEvent($event, $event->isAsync());
                }
            } else {
                $event($parameters);
            }
        }
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws JsonException
     * @throws SaveError
     * @throws EventException
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
     * @param class-string $className
     *
     * @throws FactoryError
     * @throws ReflectionException
     */
    public function getParameter(string $className, array $options = [], string $title = null): AbstractParameter
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        $constructorParameters = [];

        if ($title !== null) {
            $options['title'] = [$title];
        }

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $parameter) {
                $parameterName = $parameter->getName();

                if (!isset($options[$parameterName])) {
                    continue;
                }

                $constructorParameters[$parameterName] = $options[$parameterName][0];
                unset($options[$parameterName]);
            }
        }

        /** @var AbstractParameter $parameter */
        $parameter = $this->serviceManagerService->create(
            $className,
            $constructorParameters,
            AbstractParameter::class
        );

        foreach ($options as $name => $values) {
            $parameter->{'set' . ucfirst($name)}(...$values);
        }

        return $parameter;
    }

    /**
     * @param class-string $className
     *
     * @throws EventException
     * @throws FactoryError
     * @throws JsonException
     * @throws ReflectionException
     */
    private function checkTriggerParameters(Event $event, string $className, string $trigger, array $parameters): bool
    {
        $triggerName = $this->getTriggerName($className, $trigger);

        foreach ($event->getTriggers() ?? [] as $eventTrigger) {
            if (
                $eventTrigger->getTrigger() !== $trigger &&
                $eventTrigger->getClass() !== $className
            ) {
                continue;
            }

            $reflectionClass = new ReflectionClass($className);

            foreach ($reflectionClass->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $reflectionClassConstant) {
                if ($reflectionClassConstant->getValue() !== $eventTrigger->getTrigger()) {
                    continue;
                }

                $triggerAttributes = $reflectionClassConstant->getAttributes(
                    Trigger::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );

                if (empty($triggerAttributes)) {
                    throw new EventException(sprintf(
                        'Constant %s has no %s attribute',
                        $reflectionClassConstant->getName(),
                        Trigger::class
                    ));
                }

                /** @var Trigger $triggerAttribute */
                $triggerAttribute = $triggerAttributes[0]->newInstance();

                foreach ($triggerAttribute->getParameters() as $parameter) {
                    $triggerParameter = $this->serviceManagerService->create(
                        $parameter['className'],
                        isset($parameter['title']) ? ['title' => $parameter['title']] : [],
                        AbstractParameter::class
                    );

                    if (!$triggerParameter instanceof AutoCompleteParameter) {
                        continue;
                    }

                    $parameterName = $parameter['key'];

                    if ($parameters[$parameterName] instanceof AutoCompleteModelInterface) {
                        $parameters[$parameterName] = $parameters[$parameterName]->getAutoCompleteId();
                    }
                }
            }

            $eventParameters = JsonUtility::decode($eventTrigger->getParameters() ?? '[]');

            foreach ($eventParameters ?? [] as $parameterName => $eventParameter) {
                if (!isset($parameters[$parameterName])) {
                    continue;
                }

                if (!$this->elementService->getConditionResult($parameters[$parameterName], $eventParameter['operator'], $eventParameter['value'])) {
                    $this->logger->debug(
                        'Trigger parameter for event ' . $event->getId() . ' not true' .
                        '(' . $parameters[$parameterName] . ' ' . $eventParameter['operator'] . ' ' . $eventParameter['value'] . ')'
                    );

                    continue 2;
                }
            }

            $this->logger->debug('Parameter passed for trigger ' . $triggerName);

            return true;
        }

        $this->logger->info('Trigger ' . $triggerName . ' not passed for event ' . $event->getId());

        return false;
    }

    /**
     * @param class-string $className
     */
    private function getTriggerName(string $className, string $trigger): string
    {
        return $className . '::' . $trigger;
    }
}
