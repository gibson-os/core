<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Model\AbstractModel;
use ReflectionProperty;

class Children
{
    /**
     * @param ReflectionProperty $reflectionProperty
     * @param Constraint         $constraint
     * @param AbstractModel[]    $models
     */
    public function __construct(
        private readonly ReflectionProperty $reflectionProperty,
        private readonly Constraint $constraint,
        private readonly array $models,
        private readonly int|string|float $parentId,
    ) {
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getConstraint(): Constraint
    {
        return $this->constraint;
    }

    /**
     * @return AbstractModel[]
     */
    public function getModels(): array
    {
        return $this->models;
    }

    public function getParentId(): float|int|string
    {
        return $this->parentId;
    }
}
