<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event\Parameter;
use GibsonOS\Core\Attribute\Event\Trigger;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Store\AbstractStore;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

class ClassTriggerStore extends AbstractStore
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
    public function setClassName(string $className): ClassTriggerStore
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

        $triggers = [];
        $reflectionClass = new ReflectionClass($this->className);
        $listeners = $this->eventService->getListeners($reflectionClass);

        foreach ($reflectionClass->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $reflectionClassConstant) {
            $triggerAttributes = $reflectionClassConstant->getAttributes(
                Trigger::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if (empty($triggerAttributes)) {
                continue;
            }

            /** @var Trigger $triggerAttribute */
            $triggerAttribute = $triggerAttributes[0]->newInstance();
            $triggers[$triggerAttribute->getTitle()] = [
                'trigger' => $reflectionClassConstant->getValue(),
                'title' => $triggerAttribute->getTitle(),
                'parameters' => $this->getParameters(
                    $reflectionClass,
                    $triggerAttribute,
                    $this->eventService->getListeners($reflectionClassConstant, $listeners)
                ),
            ];
        }

        ksort($triggers);

        $this->list[$this->className] = array_values($triggers);
    }

    /**
     * @throws FactoryError
     * @throws ReflectionException
     *
     * @return array<string, AbstractParameter>
     */
    private function getParameters(
        ReflectionClass $reflectionClass,
        Trigger $triggerAttribute,
        array $listeners = []
    ): array {
        $parameters = [];

        foreach ($triggerAttribute->getParameters() as $parameter) {
            /** @var Parameter $parameterAttribute */
            $parameters[$parameter['key']] = $this->eventService->getParameter(
                $parameter['className'],
                array_merge(
                    $this->eventService->getParameterOptions($reflectionClass, $parameter['key']),
                    $parameter['options'] ?? []
                ),
                $parameter['title'] ?? null,
                $listeners
            );
        }

        return $parameters;
    }
}
