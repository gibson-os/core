<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Store\AbstractStore;

class ClassTriggerStore extends AbstractStore
{
    /**
     * @var class-string
     */
    private string $describerClass;

    /**
     * @var array[]
     */
    private array $list = [];

    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @param class-string $describerClass
     */
    public function setDescriberClass(string $describerClass): ClassTriggerStore
    {
        $this->describerClass = $describerClass;

        return $this;
    }

    /**
     * @throws FactoryError
     *
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list[$this->describerClass];
    }

    /**
     * @throws FactoryError
     */
    public function getCount(): int
    {
        return count($this->getList());
    }

    /**
     * @throws FactoryError
     */
    private function generateList(): void
    {
        if (isset($this->list[$this->describerClass])) {
            return;
        }

        $describer = $this->serviceManagerService->get($this->describerClass);
        $triggers = [];

        if (!$describer instanceof DescriberInterface) {
            $this->list[$this->describerClass] = $triggers;

            return;
        }

        foreach ($describer->getTriggers() as $name => $trigger) {
            $triggers[$trigger->getTitle()] = [
                'trigger' => $name,
                'title' => $trigger->getTitle(),
                'parameters' => $trigger->getParameters(),
            ];
        }

        ksort($triggers);

        $this->list[$this->describerClass] = array_values($triggers);
    }
}
