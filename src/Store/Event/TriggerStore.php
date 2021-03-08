<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Utility\JsonUtility;
use mysqlDatabase;

class TriggerStore extends AbstractDatabaseStore
{
    private int $eventId;

    private ClassTriggerStore $classTriggerStore;

    private ClassNameStore $classNameStore;

    public function __construct(
        ClassTriggerStore $classTriggerStore,
        ClassNameStore $classNameStore,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
        $this->classTriggerStore = $classTriggerStore;
        $this->classNameStore = $classNameStore;
    }

    protected function getTableName(): string
    {
        return Trigger::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     * @throws FactoryError
     * @throws GetError
     */
    public function getList(): iterable
    {
        $this->table
            ->setWhere('`event_id`=?')
            ->addWhereParameter($this->eventId)
            ->setOrderBy('priority')
        ;

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
                if ($className['describerClass'] === $model->getClass()) {
                    $model->setClassTitle($className['title']);

                    break;
                }
            }

            $this->classTriggerStore->setDescriberClass($model->getClass());

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

    public function setEventId(int $eventId): TriggerStore
    {
        $this->eventId = $eventId;

        return $this;
    }

    private function completeParameters(array $triggerParameters, ?string $parameters): ?string
    {
        $parameters = $parameters === null ? [] : JsonUtility::decode($parameters);

        foreach ($triggerParameters as $parameterName => &$methodParameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $methodParameter['value'] = $parameters[$parameterName]['value'];
            $methodParameter['operator'] = $parameters[$parameterName]['operator'];
        }

        return JsonUtility::encode($triggerParameters);
    }
}
