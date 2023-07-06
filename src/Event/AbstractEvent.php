<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use Exception;
use GibsonOS\Core\Attribute\Event\Listener;
use GibsonOS\Core\Attribute\Event\Method;
use GibsonOS\Core\Attribute\Event\Parameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Dto\Parameter\EnumParameter;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\ParameterException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\EventService;
use JsonException;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;

abstract class AbstractEvent
{
    public function __construct(
        protected readonly EventService $eventService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws EventException
     * @throws ReflectionException
     * @throws ParameterException
     */
    public function run(Element $element, Event $event)
    {
        $method = $element->getMethod();
        $reflectionClass = $this->reflectionManager->getReflectionClass($this);

        try {
            $reflectionMethod = $reflectionClass->getMethod($method);
        } catch (ReflectionException) {
            throw new EventException(sprintf('Class "%s" has no "%s" method', $reflectionClass->getName(), $method));
        }

        if (!$this->reflectionManager->hasAttribute(
            $reflectionMethod,
            Method::class,
            ReflectionAttribute::IS_INSTANCEOF
        )) {
            throw new EventException(sprintf(
                'Method "%s" has no "%s" attribute',
                $reflectionMethod->getName(),
                Method::class
            ));
        }

        try {
            return $this->{$method}(...$this->getParameters($reflectionMethod, $element));
        } catch (Exception $exception) {
            if ($event->isExitOnError()) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     * @throws ParameterException
     */
    protected function getParameters(ReflectionMethod $reflectionMethod, Element $element): array
    {
        $methodParameters = [];

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameterAttribute = $this->reflectionManager->getAttribute(
                $reflectionParameter,
                Parameter::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if ($parameterAttribute === null) {
                continue;
            }

            $methodParameters[$reflectionParameter->getName()] = $this->eventService->getParameter(
                $parameterAttribute->getClassName(),
                $parameterAttribute->getOptions(),
                $parameterAttribute->getTitle(),
            );
        }

        $parameters = $element->getParameters();
        $newParameters = [];
        /** @var Listener[] $listenerAttributes */
        $listenerAttributes = $this->reflectionManager->getAttributes(
            $reflectionMethod->getDeclaringClass(),
            Listener::class
        );
        array_push(
            $listenerAttributes,
            ...$this->reflectionManager->getAttributes($reflectionMethod, Listener::class),
        );

        foreach ($methodParameters as $parameterName => $methodParameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            if ($methodParameter instanceof EnumParameter) {
                $newParameters[] = $methodParameter->getEnum($parameters[$parameterName]);

                continue;
            }

            if (
                !$methodParameter instanceof AutoCompleteParameter
                || $parameters[$parameterName] instanceof AutoCompleteModelInterface
            ) {
                $newParameters[] = $parameters[$parameterName];

                continue;
            }

            $extendedParameters = $parameters;

            foreach ($listenerAttributes as $listenerAttribute) {
                $toKey = $listenerAttribute->getToKey();

                if (
                    $listenerAttribute->getForKey() !== $parameterName
                    || !isset($parameters[$toKey])
                ) {
                    continue;
                }

                $listenerParameters = $listenerAttribute->getOptions()['params'];
                $extendedParameters[$listenerParameters['paramKey']] = $parameters[$toKey];
            }

            $newParameters[] = $methodParameter->getAutoComplete()->getById(
                (string) $parameters[$parameterName],
                $extendedParameters
            );
        }

        return $newParameters;
    }
}
