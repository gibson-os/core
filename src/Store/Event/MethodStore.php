<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event\Method;
use GibsonOS\Core\Attribute\Event\Parameter;
use GibsonOS\Core\Attribute\Event\ReturnValue;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Store\AbstractStore;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class MethodStore extends AbstractStore
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var array[]
     */
    private array $list = [];

    public function __construct(private EventService $eventService)
    {
    }

    /**
     * @param class-string $className
     */
    public function setClassName(string $className): MethodStore
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     *
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list[$this->className];
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     */
    public function getCount(): int
    {
        return count($this->getList());
    }

    /**
     * @throws ReflectionException
     * @throws FactoryError
     */
    private function generateList(): void
    {
        if (isset($this->list[$this->className])) {
            return;
        }

        $methods = [];
        $reflectionClass = new ReflectionClass($this->className);
        $listeners = $this->eventService->getListeners($reflectionClass);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            $methodAttributes = $reflectionMethod->getAttributes(Method::class, ReflectionAttribute::IS_INSTANCEOF);

            if (empty($methodAttributes)) {
                continue;
            }

            /** @var Method $methodAttribute */
            $methodAttribute = $methodAttributes[0]->newInstance();

            $methods[$methodAttribute->getTitle()] = [
                'method' => $reflectionMethod->getName(),
                'title' => $methodAttribute->getTitle(),
                'parameters' => $this->getParameters(
                    $reflectionMethod,
                    $this->eventService->getListeners($reflectionMethod, $listeners)
                ),
                'returns' => $this->getReturns($reflectionMethod),
            ];
        }

        ksort($methods);

        $this->list[$this->className] = array_values($methods);
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     */
    private function getParameters(ReflectionMethod $reflectionMethod, array $listeners): array
    {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameterAttributes = $reflectionParameter->getAttributes(
                Parameter::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if (empty($parameterAttributes)) {
                continue;
            }

            /** @var Parameter $parameterAttribute */
            $parameterAttribute = $parameterAttributes[0]->newInstance();
            $parameters[$reflectionParameter->getName()] = $this->getParameter(
                $parameterAttribute,
                $listeners[$reflectionParameter->getName()] ?? []
            );
        }

        return $parameters;
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     */
    private function getReturns(ReflectionMethod $reflectionMethod): array
    {
        $returns = [];
        $returnValueAttributes = $reflectionMethod->getAttributes(ReturnValue::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($returnValueAttributes as $returnValueAttribute) {
            /** @var ReturnValue $returnValue */
            $returnValue = $returnValueAttribute->newInstance();
            $returns[$returnValue->getKey() ?? 'value'] = $this->getParameter($returnValue);
        }

        return $returns;
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     */
    private function getParameter(ReturnValue|Parameter $object, array $listeners = []): AbstractParameter
    {
        return $this->eventService->getParameter(
            $object->getClassName(),
            $object->getOptions(),
            $object->getTitle(),
            $listeners
        );
    }
}
