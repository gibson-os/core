<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use Exception;
use GibsonOS\Core\Attribute\Event\Listener;
use GibsonOS\Core\Attribute\Event\ParameterOption;
use GibsonOS\Core\Attribute\Event\Trigger;
use GibsonOS\Core\Command\Event\RunCommand;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\ElementService;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;

class EventService
{
    private array $events = [];

    public function __construct(
        private readonly ServiceManager $serviceManager,
        private readonly EventRepository $eventRepository,
        private readonly ElementService $elementService,
        private readonly CommandService $commandService,
        private readonly DateTimeService $dateTimeService,
        private readonly ReflectionManager $reflectionManager,
        private readonly ModelManager $modelManager,
        private readonly ProcessService $processService,
        private readonly LoggerInterface $logger,
    ) {
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
    public function fireInCommand(string $className, string $trigger, array $parameters = null): void
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
     * @param class-string $className
     */
    public function fire(string $className, string $trigger, array $parameters = null): void
    {
        try {
            $this->fireInCommand($className, $trigger, $parameters);
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                "%s: %s\n%s",
                get_class($e),
                $e->getMessage(),
                $e->getTraceAsString()
            ));
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
        $this->modelManager->saveWithoutChildren(
            $event
                ->setPid(getmypid())
                ->setLastRun($this->dateTimeService->get())
        );
        $startTime = (int) (microtime(true) * 1000000);
        $this->elementService->runElements($event->getElements(), $event);
        $this->modelManager->saveWithoutChildren(
            $event
                ->setPid(null)
                ->setLastRun($this->dateTimeService->get())
                ->setRuntime(((int) (microtime(true) * 1000000)) - $startTime)
        );
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function stop(Event $event): void
    {
        $pid = $event->getPid();

        if ($pid === null) {
            return;
        }

        $this->processService->kill($pid);

        $this->modelManager->save(
            $event
                ->setPid(null)
                ->setLastRun($this->dateTimeService->get())
                ->setRuntime(null)
        );
    }

    /**
     * @param class-string $className
     *
     * @throws FactoryError
     * @throws ReflectionException
     */
    public function getParameter(
        string $className,
        array $options = [],
        string $title = null,
        array $listeners = []
    ): AbstractParameter {
        $reflectionClass = $this->reflectionManager->getReflectionClass($className);
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
        $parameter = $this->serviceManager->create(
            $className,
            $constructorParameters,
            AbstractParameter::class
        );

        foreach ($options as $optionName => $values) {
            $parameter->{'set' . ucfirst($optionName)}(...$values);
        }

        foreach ($listeners as $field => $listener) {
            $parameter->setListener($field, $listener);
        }

        return $parameter;
    }

    public function getParameterOptions(ReflectionClass $reflectionClass, string $name): array
    {
        $parameterOptionAttributes = $this->reflectionManager->getAttributes(
            $reflectionClass,
            ParameterOption::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        $options = [];

        foreach ($parameterOptionAttributes as $parameterOptionAttribute) {
            if ($parameterOptionAttribute->getParameterKey() !== $name) {
                continue;
            }

            $options[$parameterOptionAttribute->getOptionKey()] = [$parameterOptionAttribute->getOptionValue()];
        }

        return $options;
    }

    public function getListeners(
        ReflectionClass|ReflectionMethod|ReflectionClassConstant $reflectionObject,
        array $listeners = []
    ): array {
        $listenerAttributes = $this->reflectionManager->getAttributes(
            $reflectionObject,
            Listener::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($listenerAttributes as $listener) {
            $forKey = $listener->getForKey();

            if (!isset($listeners[$forKey])) {
                $listeners[$forKey] = [];
            }

            $listeners[$forKey][$listener->getToKey()] = $listener->getOptions();
        }

        return $listeners;
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

        foreach ($event->getTriggers() as $eventTrigger) {
            if (
                $eventTrigger->getTrigger() !== $trigger &&
                $eventTrigger->getClass() !== $className
            ) {
                continue;
            }

            $reflectionClass = $this->reflectionManager->getReflectionClass($className);

            foreach ($reflectionClass->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $reflectionClassConstant) {
                if ($reflectionClassConstant->getValue() !== $eventTrigger->getTrigger()) {
                    continue;
                }

                $triggerAttribute = $this->reflectionManager->getAttribute(
                    $reflectionClassConstant,
                    Trigger::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );

                if ($triggerAttribute === null) {
                    throw new EventException(sprintf(
                        'Constant %s has no %s attribute',
                        $reflectionClassConstant->getName(),
                        Trigger::class
                    ));
                }

                foreach ($triggerAttribute->getParameters() as $parameter) {
                    $cleanParameters = [];

                    if (isset($parameter['title'])) {
                        $cleanParameters['title'] = $parameter['title'];
                    }

                    if (isset($parameter['options'])) {
                        $cleanParameters['options'] = $parameter['options'];
                    }

                    $triggerParameter = $this->serviceManager->create(
                        $parameter['className'],
                        $cleanParameters,
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

            foreach ($eventTrigger->getParameters() as $parameterName => $eventParameter) {
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
