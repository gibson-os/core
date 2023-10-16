<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

/**
 * @extends AbstractDatabaseStore<Trigger>
 */
class TriggerStore extends AbstractDatabaseStore
{
    private Event $event;

    public function __construct(
        private readonly ClassTriggerStore $classTriggerStore,
        private readonly ClassNameStore $classNameStore,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Trigger::class;
    }

    protected function setWheres(): void
    {
        $this->addWhere('`event_id`=?', [$this->event->getId() ?? 0]);
    }

    protected function initQuery(): void
    {
        parent::initQuery();

        $this->selectQuery->setOrder('`priority`');
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    public function getList(): iterable
    {
        $models = [];
        $classNames = $this->classNameStore->getList();

        foreach (parent::getList() as $model) {
            foreach ($classNames as $className) {
                if ($className['className'] === $model->getClass()) {
                    $model->setClassTitle($className['title']);

                    break;
                }
            }

            $this->classTriggerStore->setClassName($model->getClass());

            foreach ($this->classTriggerStore->getList() as $trigger) {
                if ($trigger['trigger'] === $model->getTrigger()) {
                    $model
                        ->setTriggerTitle($trigger['title'])
                        ->setParameters($this->completeParameters($trigger['parameters'], $model->getParameters()))
                    ;

                    break;
                }
            }

            $models[] = $model;
        }

        return $models;
    }

    public function setEvent(Event $event): TriggerStore
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @param array<string, AbstractParameter> $triggerParameters
     */
    private function completeParameters(array $triggerParameters, array $parameters): array
    {
        $newTriggerParameters = [];

        foreach ($triggerParameters as $parameterName => $triggerParameter) {
            $triggerParameter = clone $triggerParameter;
            $newTriggerParameters[$parameterName] = $triggerParameter;

            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $triggerParameter->setValue($parameters[$parameterName]['value']);
            $triggerParameter->setOperator($parameters[$parameterName]['operator']);
        }

        return $newTriggerParameters;
    }
}
