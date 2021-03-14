<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\JsonUtility;

abstract class AbstractEvent
{
    private DescriberInterface $describer;

    private ServiceManagerService $serviceManagerService;

    public function __construct(DescriberInterface $describer, ServiceManagerService $serviceManagerService)
    {
        $this->describer = $describer;
        $this->serviceManagerService = $serviceManagerService;
    }

    public function run(Element $element)
    {
        $method = $element->getMethod();

        if (!isset($this->describer->getMethods()[$method])) {
            // @todo throw exception
        }

        return $this->{$method}(...$this->getParameters($element));
    }

    protected function getParameters(Element $element): array
    {
        /** @var DescriberInterface $describer */
        $describer = $this->serviceManagerService->get($element->getClass());
        $methods = $describer->getMethods();
        $methodParameters = $methods[$element->getMethod()]->getParameters();
        $parameters = JsonUtility::decode($element->getParameters() ?? '[]');

        foreach ($methodParameters as $parameterName => $methodParameter) {
            if (!$methodParameter instanceof AutoCompleteParameter) {
                continue;
            }

            $parameters[$parameterName] = $methodParameter->getAutoComplete()->getById($parameters[$parameterName], []);
        }

        return empty($parameters) ? [] : array_values($parameters);
    }
}
