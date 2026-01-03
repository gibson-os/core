<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use ReflectionException;

/**
 * @extends AbstractDatabaseStore<Element>
 */
class ElementStore extends AbstractDatabaseStore
{
    public function __construct(
        private readonly ClassNameStore $classNameStore,
        private readonly MethodStore $methodStore,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    private Event $event;

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    #[Override]
    protected function getModelClassName(): string
    {
        return Element::class;
    }

    #[Override]
    protected function setWheres(): void
    {
        $this->addWhere('`event_id`=?', [$this->event->getId() ?? 0]);
    }

    #[Override]
    protected function initQuery(): void
    {
        parent::initQuery();

        $this->selectQuery->setOrders([
            '`parent_id`' => OrderDirection::ASC,
            '`order`' => OrderDirection::ASC,
        ]);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     *
     * @return Element[]
     */
    #[Override]
    public function getList(): array
    {
        $this->initQuery();
        $data = [];
        $models = [];
        $classNames = $this->classNameStore->getList();

        foreach (parent::getList() as $element) {
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
        }

        return $data;
    }

    /**
     * @param AbstractParameter[] $methodParameters
     *
     * @return AbstractParameter[]
     */
    private function completeParameters(array $methodParameters, array $parameters): array
    {
        $newMethodParameters = [];

        foreach ($methodParameters as $parameterName => $methodParameter) {
            $methodParameter = clone $methodParameter;
            $newMethodParameters[$parameterName] = $methodParameter;

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

        return $newMethodParameters;
    }

    /**
     * @param AbstractParameter[] $methodReturns
     *
     * @return AbstractParameter[]
     */
    private function completeReturns(array $methodReturns, array $returns): array
    {
        $newMethodReturns = [];

        foreach ($methodReturns as $parameterName => $methodReturn) {
            $methodReturn = clone $methodReturn;
            $newMethodReturns[$parameterName] = $methodReturn;

            if (!isset($returns[$parameterName])) {
                continue;
            }

            $parameter = $returns[$parameterName];
            $methodReturn
                ->setValue($parameter['value'])
                ->setOperator($parameter['operator'])
            ;
        }

        return $newMethodReturns;
    }
}
