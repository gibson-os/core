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
use JsonException;
use mysqlDatabase;
use ReflectionException;

class TriggerStore extends AbstractDatabaseStore
{
    private Event $event;

    public function __construct(
        private ClassTriggerStore $classTriggerStore,
        private ClassNameStore $classNameStore,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return Trigger::class;
    }

    protected function setWheres(): void
    {
        $this->addWhere('`event_id`=?', [$this->event->getId() ?? 0]);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws SelectError
     * @throws ReflectionException
     */
    public function getList(): iterable
    {
        $this->initTable();
        $this->table->setOrderBy('priority');

        $selectPrepared = $this->table->selectPrepared();

        if ($selectPrepared === false) {
            throw (new SelectError())->setTable($this->table);
        }

        if ($selectPrepared === 0) {
            return [];
        }

        $models = [];
        $classNames = $this->classNameStore->getList();

        do {
            $model = new Trigger();
            $model->loadFromMysqlTable($this->table);

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
        } while ($this->table->next());

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
        foreach ($triggerParameters as $parameterName => $methodParameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $methodParameter->setValue($parameters[$parameterName]['value']);
            $methodParameter->setOperator($parameters[$parameterName]['operator']);
        }

        return $triggerParameters;
    }
}
