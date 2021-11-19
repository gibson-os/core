<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Store\AbstractStore;

class MethodStore extends AbstractStore
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
    public function setDescriberClass(string $describerClass): MethodStore
    {
        $this->describerClass = $describerClass;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list[$this->describerClass];
    }

    public function getCount(): int
    {
        return count($this->getList());
    }

    private function generateList(): void
    {
        if (isset($this->list[$this->describerClass])) {
            return;
        }

        $methods = [];

        try {
            $describer = $this->serviceManagerService->get($this->describerClass);
        } catch (FactoryError) {
            $this->list[$this->describerClass] = $methods;

            return;
        }

        if (!$describer instanceof DescriberInterface) {
            $this->list[$this->describerClass] = $methods;

            return;
        }

        foreach ($describer->getMethods() as $name => $method) {
            $methods[$method->getTitle()] = [
                'method' => $name,
                'title' => $method->getTitle(),
                'parameters' => $this->transformParameters($method->getParameters()),
                'returns' => $this->transformParameters($method->getReturns()),
            ];
        }

        ksort($methods);

        $this->list[$this->describerClass] = array_values($methods);
    }

    /**
     * @param AbstractParameter[] $parameters
     */
    private function transformParameters(array $parameters): array
    {
        $parametersArray = [];

        foreach ($parameters as $name => $parameter) {
            $parametersArray[$name] = [
                'title' => $parameter->getTitle(),
                'xtype' => $parameter->getXtype(),
                'allowedOperators' => $parameter->getAllowedOperators(),
                'config' => $parameter->getConfig(),
            ];
        }

        return $parametersArray;
    }
}
