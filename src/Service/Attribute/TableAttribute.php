<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\Install\Database\Table;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;

class TableAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetTable) {
            return $parameters;
        }

        $reflectionClass = new ReflectionClass($attribute->getModelClassName());
        $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($tableAttributes) === 0) {
            return null;
        }

        /** @var Table $tableAttribute */
        $tableAttribute = $tableAttributes[0]->newInstance();

        return $this->getTableName($tableAttribute, $attribute->getModelClassName());
    }

    /**
     * @param class-string $modelClassName
     */
    public function getTableName(Table $tableAttribute, string $modelClassName): string
    {
        return $tableAttribute->getName() ?? $this->transformName(str_replace(
            '\\',
            '',
            preg_replace('/.*Model\\\\/', '', $modelClassName)
        ));
    }

    public function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }
}
