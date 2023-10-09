<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Factory\DateTimeFactory;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

abstract class AbstractModel implements ModelInterface
{
    use ConstraintTrait;

    private DateTimeService $dateTime;

    private ?string $tableName = null;

    public function __construct(private readonly ModelWrapper $modelWrapper)
    {
        $this->dateTime = DateTimeFactory::get();
    }

    public function getModelWrapper(): ModelWrapper
    {
        return $this->modelWrapper;
    }

    public function getTableName(): string
    {
        if ($this->tableName !== null) {
            return $this->tableName;
        }

        try {
            $reflectionClass = new ReflectionClass($this::class);
            $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);
            /** @var Table $tableAttribute */
            $tableAttribute = $tableAttributes[0]->newInstance();
            $this->tableName = $tableAttribute->getName() ?? $this->transformName(str_replace(
                '\\',
                '',
                str_replace(
                    'Core\\',
                    '',
                    preg_replace('/.*\\\\(.+?)\\\\.*Model\\\\/', '$1\\', $this::class),
                ),
            ));
        } catch (ReflectionException) {
        }

        return $this->tableName ?? '';
    }

    private function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }

    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }
}
