<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event\Method;
use GibsonOS\Core\Attribute\Event\Parameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractEvent
{
    public function __construct(private EventService $eventService)
    {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws EventException
     */
    public function run(Element $element)
    {
        $method = $element->getMethod();

        $reflectionClass = new ReflectionClass($this);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if ($reflectionMethod->getName() !== $method) {
                continue;
            }

            $methodAttributes = $reflectionMethod->getAttributes(
                Method::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if (empty($methodAttributes)) {
                throw new EventException(sprintf(
                    'Method %s has no %s attribute',
                    $reflectionMethod->getName(),
                    Method::class
                ));
            }

            return $this->{$method}(...$this->getParameters($reflectionMethod, $element));
        }

        throw new EventException(sprintf('Class %s has no %s method', $reflectionClass->getName(), $method));
    }

    /**
     * @throws JsonException
     * @throws FactoryError
     */
    protected function getParameters(ReflectionMethod $reflectionMethod, Element $element): array
    {
        $methodParameters = [];

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

            $methodParameters[$reflectionParameter->getName()] = $this->eventService->getParameter(
                $parameterAttribute->getClassName(),
                $parameterAttribute->getOptions(),
                $parameterAttribute->getTitle()
            );
        }

        $parameters = JsonUtility::decode($element->getParameters() ?? '[]');

        foreach ($methodParameters as $parameterName => $methodParameter) {
            if (
                !$methodParameter instanceof AutoCompleteParameter ||
                $parameters[$parameterName] instanceof AutoCompleteModelInterface
            ) {
                continue;
            }

            $parameters[$parameterName] = $methodParameter->getAutoComplete()->getById(
                (string) $parameters[$parameterName],
                []
            );
        }

        return empty($parameters) ? [] : array_values($parameters);
    }
}
