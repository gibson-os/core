<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\JsonUtility;

abstract class AbstractEvent
{
    /**
     * @var DescriberInterface
     */
    private $describer;

    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    /**
     * AbstractEvent constructor.
     */
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
        $parameters = $element->getParameters();

        return empty($parameters) ? [] : array_values(JsonUtility::decode($parameters));
    }
}
