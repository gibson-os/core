<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Model\Event\Element;

class EventService extends AbstractService
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    public function __construct(ServiceManagerService $serviceManagerService)
    {
        $this->serviceManagerService = $serviceManagerService;
    }

    /**
     * @param callable $function
     */
    public function add(string $trigger, $function): void
    {
        if (!isset($this->events[$trigger])) {
            $this->events[$trigger] = [];
        }

        $this->events[$trigger][] = $function;
    }

    public function fire(string $trigger, array $parameters = null): void
    {
        if (!isset($this->events[$trigger])) {
            return;
        }

        foreach ($this->events[$trigger] as $event) {
            $event($parameters);
        }
    }

    /**
     * @throws FileNotFound
     */
    public function runFunction(Element $element)
    {
        $service = $this->serviceManagerService->get($element->getClass());

        return $service->run($element);
    }
}
