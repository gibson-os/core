<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use ReflectionProperty;

class Children
{
    /**
     * @param AbstractModel[] $models
     */
    public function __construct(
        private readonly ReflectionProperty $reflectionProperty,
        private readonly Constraint $constraint,
        private readonly array $models,
        private readonly ModelInterface $parent,
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

    public function getParent(): ModelInterface
    {
        return $this->parent;
    }

    public function getParentId(): float|int|string
    {
        $getter = 'get' . ucfirst(str_replace(
            ' ',
            '',
            ucwords(str_replace('_', ' ', $this->constraint->getOwnColumn() ?? 'id'))
        ));

        return $this->parent->$getter();
    }
}
