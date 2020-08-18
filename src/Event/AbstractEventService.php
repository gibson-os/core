<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Model\Event\Element;

abstract class AbstractEventService
{
    /**
     * @var DescriberInterface
     */
    private $describer;

    /**
     * AbstractEvent constructor.
     */
    public function __construct(DescriberInterface $describer)
    {
        $this->describer = $describer;
    }

    public function run(Element $element)
    {
        $method = $element->getMethod();

        if (!isset($this->describer->getMethods()[$method])) {
            // @todo throw exception
        }

        return $this->{$method}(...$this->getParams($element));
    }

    protected function getParams(Element $element): array
    {
        $params = $element->getParams();

        return empty($params) ? [] : unserialize($params);
    }
}
