<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Attribute\Event\Trigger;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Store\AbstractStore;
use Override;
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

    public function __construct(private EventService $eventService, private ReflectionManager $reflectionManager)
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
    #[Override]
    public function getList(): array
    {
        $this->generateList();

        return $this->list[$this->className];
    }

    /**
     * @throws FactoryError
     */
    #[Override]
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
        $reflectionClass = $this->reflectionManager->getReflectionClass($this->className);
        $listeners = $this->eventService->getListeners($reflectionClass);

        foreach ($reflectionClass->getReflectionConstants(ReflectionClassConstant::IS_PUBLIC) as $reflectionClassConstant) {
            $triggerAttribute = $this->reflectionManager->getAttribute(
                $reflectionClassConstant,
                Trigger::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if ($triggerAttribute === null) {
                continue;
            }

            $triggers[$triggerAttribute->getTitle()] = [
                'trigger' => $reflectionClassConstant->getValue(),
                'title' => $triggerAttribute->getTitle(),
                'parameters' => $this->getParameters(
                    $reflectionClass,
                    $triggerAttribute,
                    $this->eventService->getListeners($reflectionClassConstant, $listeners),
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
        array $listeners = [],
    ): array {
        $parameters = [];

        foreach ($triggerAttribute->getParameters() as $parameter) {
            $parameters[$parameter['key']] = $this->eventService->getParameter(
                $parameter['className'],
                array_merge(
                    $this->eventService->getParameterOptions($reflectionClass, $parameter['key']),
                    $parameter['options'] ?? [],
                ),
                $parameter['title'] ?? null,
                $listeners[$parameter['key']] ?? [],
            );
        }

        return $parameters;
    }
}
