<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Event\Describer\Parameter\AbstractParameter;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Store\AbstractStore;

class MethodStore extends AbstractStore
{
    /**
     * @var string
     */
    private $className = '';

    /**
     * @var array[]
     */
    private $list = [];

    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    public function __construct(ServiceManagerService $serviceManagerService)
    {
        $this->serviceManagerService = $serviceManagerService;
    }

    public function setClassName(string $className): MethodStore
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list;
    }

    public function getCount(): int
    {
        return count($this->getList());
    }

    private function generateList(): void
    {
        if (count($this->list) !== 0) {
            return;
        }

        $describer = $this->serviceManagerService->get($this->className);
        $methods = [];

        if (!$describer instanceof DescriberInterface) {
            $this->list = $methods;

            return;
        }

        foreach ($describer->getMethods() as $name => $method) {
            $methods[$method->getTitle()] = [
                'method' => $name,
                'title' => $method->getTitle(),
                'parameters' => $this->transformParameters($method->getParameters()),
                'returnType' => $this->transformReturnTypes($method->getReturnTypes()),
            ];
        }

        ksort($methods);

        $this->list = array_values($methods);
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
                'type' => $parameter->getType(),
                'config' => $parameter->getConfig(),
            ];
        }

        return $parametersArray;
    }

    /**
     * @param AbstractParameter[]|AbstractParameter[][] $returnTypes
     */
    private function transformReturnTypes(array $returnTypes): array
    {
        $returnTypesArray = [];

        foreach ($returnTypes as $returnType) {
            if (is_array($returnType)) {
                $returnTypesArray[] = $this->transformReturnTypes($returnType);

                continue;
            }

            $returnTypesArray[] = [
                'title' => $returnType->getTitle(),
                'type' => $returnType->getType(),
                'config' => $returnType->getConfig(),
            ];
        }

        return $returnTypesArray;
    }
}
