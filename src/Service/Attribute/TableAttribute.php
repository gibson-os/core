<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Attribute\Install\Database\Table;
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
            str_replace(
                'Core\\',
                '',
                preg_replace('/.*\\\\(.+?)\\\\.*Model\\\\/', '$1\\', $modelClassName)
            )
        ));
    }

    public function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }
}
