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
    /**
     * @throws ReflectionException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetTableName) {
            return $parameters;
        }

        $reflectionClass = new ReflectionClass($attribute->getModelClassName());
        $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($tableAttributes) === 0) {
            return null;
        }

        /** @var Table $tableAttribute */
        $tableAttribute = $tableAttributes[0]->newInstance();

        $modelClassName = $attribute->getModelClassName();
        /** @var ModelInterface $model */
        $model = new $modelClassName();

        return $model->getTableName();
    }

    public function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }
}
