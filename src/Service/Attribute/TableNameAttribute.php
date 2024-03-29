<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Wrapper\ModelWrapper;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;

class TableNameAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    private array $tables = [];

    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

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

        $reflectionClass = $this->reflectionManager->getReflectionClass($modelClassName);

        if (!$this->reflectionManager->hasAttribute($reflectionClass, Table::class, ReflectionAttribute::IS_INSTANCEOF)) {
            return null;
        }

        /** @var AbstractModel $model */
        $model = new $modelClassName($this->modelWrapper);
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
