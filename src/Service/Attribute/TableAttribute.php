<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use MDO\Exception\ClientException;
use MDO\Manager\TableManager;
use ReflectionException;
use ReflectionParameter;

class TableAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private readonly TableNameAttribute $tableNameAttribute,
        private readonly TableManager $tableManager,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws ClientException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetTable) {
            return null;
        }

        $modelClassName = $attribute->getModelClassName();

        return $this->tableManager->getTable(
            $this->tableNameAttribute->replace(new GetTableName($modelClassName), $parameters, $reflectionParameter),
        );
    }
}
