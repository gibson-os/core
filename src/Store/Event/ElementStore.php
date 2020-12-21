<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Utility\JsonUtility;
use mysqlDatabase;

class ElementStore extends AbstractDatabaseStore
{
    private ClassNameStore $classNameStore;

    private MethodStore $methodStore;

    public function __construct(
        ClassNameStore $classNameStore,
        MethodStore $methodStore,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
        $this->classNameStore = $classNameStore;
        $this->methodStore = $methodStore;
    }

    private ?int $eventId;

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    protected function getTableName(): string
    {
        return Element::getTableName();
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
     * @throws GetError
     *
     * @return Element[]
     */
    public function getList(): array
    {
        $this->where[] = '`event_id`=' . ($this->eventId ?? 0);
        $this->table->setWhere($this->getWhere());
        $this->table->setOrderBy('`parent_id`, `order`');

        $select = $this->table->select();

        if ($select === false) {
            throw (new SelectError())->setTable($this->table);
        }

        if ($select === 0) {
            return [];
        }

        $data = [];
        $models = [];
        $classNames = $this->classNameStore->getList();

        do {
            $element = new Element();
            $element->loadFromMysqlTable($this->table);
            $models[$element->getId() ?? 0] = $element;
            $parentId = $element->getParentId();

            foreach ($classNames as $className) {
                if ($className['describerClass'] === $element->getClass()) {
                    $element->setClassTitle($className['title']);

                    break;
                }
            }

            $this->methodStore->setDescriberClass($element->getClass());

            foreach ($this->methodStore->getList() as $method) {
                if ($method['method'] === $element->getMethod()) {
                    $element
                        ->setMethodTitle($method['title'])
                        ->setParameters($this->completeParameters($method['parameters'], $element->getParameters()))
                        ->setReturns($this->completeParameters($method['returns'], $element->getReturns()))
                    ;

                    break;
                }
            }

            if ($parentId === null) {
                $data[] = $element;
            } else {
                $models[$parentId]->addChildren($element);
            }
        } while ($this->table->next());

        return $data;
    }

    private function completeParameters(array $methodParameters, ?string $parameters): ?string
    {
        $parameters = $parameters === null ? [] : JsonUtility::decode($parameters);

        foreach ($methodParameters as $parameterName => &$methodParameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $methodParameter['value'] = $parameters[$parameterName];
        }

        return JsonUtility::encode($methodParameters);
    }
}
