<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Model\AutoCompleteModelInterface;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

abstract class AbstractEvent
{
    public function __construct(private DescriberInterface $describer, private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     */
    public function run(Element $element)
    {
        $method = $element->getMethod();

        if (!isset($this->describer->getMethods()[$method])) {
            // @todo throw exception
        }

        return $this->{$method}(...$this->getParameters($element));
    }

    /**
     * @throws JsonException
     * @throws FactoryError
     */
    protected function getParameters(Element $element): array
    {
        /** @var DescriberInterface $describer */
        $describer = $this->serviceManagerService->get($element->getClass());
        $methods = $describer->getMethods();
        $methodParameters = $methods[$element->getMethod()]->getParameters();
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
