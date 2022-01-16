<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\ModelInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class TableAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    private array $tables = [];

    /**
     * @throws ReflectionException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetTableName) {
            return null;
        }

        $modelClassName = $attribute->getModelClassName();

        if (isset($this->tables[$modelClassName])) {
            return $this->tables[$modelClassName];
        }

        $reflectionClass = new ReflectionClass($modelClassName);
        $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($tableAttributes) === 0) {
            return null;
        }

        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $this->tables[$modelClassName] = $model->getTableName();

        return $this->tables[$modelClassName];
    }

    public function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }
}
