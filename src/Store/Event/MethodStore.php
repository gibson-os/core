<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Event\Describer\Parameter\AbstractParameter;
use GibsonOS\Core\Service\Event\Describer\DescriberInterface;
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

        // @ todo muss umgebaut werden
        $classNameWithNamespace = '\\GibsonOS\\Module\\Hc\\Service\\Event\\Describer\\' . $this->className;
        $class = new $classNameWithNamespace();
        $methods = [];

        if (!$class instanceof DescriberInterface) {
            $this->list = $methods;

            return;
        }

        foreach ($class->getMethods() as $name => $method) {
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
