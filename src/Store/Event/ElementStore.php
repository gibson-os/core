<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use JsonException;
use mysqlDatabase;
use ReflectionException;

class ElementStore extends AbstractDatabaseStore
{
    public function __construct(
        private ClassNameStore $classNameStore,
        private MethodStore $methodStore,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
    }

    private int $eventId;

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    protected function getModelClassName(): string
    {
        return Element::class;
    }

    protected function setWheres(): void
    {
        $this->addWhere('`event_id`=?', [$this->eventId]);
    }

    /**
     * @throws GetError
     * @throws JsonException
     * @throws SelectError
     * @throws FactoryError
     * @throws ReflectionException
     *
     * @return Element[]
     */
    public function getList(): array
    {
        $this->initTable();
        $this->table->setOrderBy('`parent_id`, `order`');

        $select = $this->table->selectPrepared();

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
            $element->setChildren([]);
            $models[$element->getId() ?? 0] = $element;
            $parentId = $element->getParentId();

            foreach ($classNames as $className) {
                if ($className['className'] === $element->getClass()) {
                    $element->setClassTitle($className['title']);

                    break;
                }
            }

            $this->methodStore->setClassName($element->getClass());

            foreach ($this->methodStore->getList() as $method) {
                if ($method['method'] === $element->getMethod()) {
                    $element
                        ->setMethodTitle($method['title'])
                        ->setParameters($this->completeParameters($method['parameters'], $element->getParameters()))
                        ->setReturns($this->completeReturns($method['returns'], $element->getReturns()))
                    ;

                    break;
                }
            }

            if ($parentId === null) {
                $data[] = $element;
            } else {
                $models[$parentId]->addChildren([$element]);
            }
        } while ($this->table->next());

        return $data;
    }

    /**
     * @param AbstractParameter[] $methodParameters
     *
     * @throws JsonException
     *
     * @return AbstractParameter[]
     */
    private function completeParameters(array $methodParameters, array $parameters): array
    {
        foreach ($methodParameters as $parameterName => $methodParameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $parameter = $parameters[$parameterName];

            if (is_array($parameter)) {
                $methodParameter->setValue(array_merge($methodParameter->getValue() ?? [], $parameter));

                continue;
            }

            $methodParameter->setValue($parameter);
        }

        return $methodParameters;
    }

    /**
     * @param AbstractParameter[] $methodReturns
     *
     * @throws JsonException
     *
     * @return AbstractParameter[]
     */
    private function completeReturns(array $methodReturns, array $returns): array
    {
        foreach ($methodReturns as $parameterName => $methodReturn) {
            if (!isset($returns[$parameterName])) {
                continue;
            }

            $parameter = $returns[$parameterName];
            $methodReturn
                ->setValue($parameter['value'])
                ->setOperator($parameter['operator'])
            ;
        }

        return $methodReturns;
    }
}
