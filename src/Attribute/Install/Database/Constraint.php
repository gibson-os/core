<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;
use GibsonOS\Core\Model\AbstractModel;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Constraint
{
    public const RULE_CASCADE = 'CASCADE';

    public const RULE_SET_NULL = 'SET NULL';

    public const RULE_SET_DEFAULT = 'SET DEFAULT';

    /**
     * @param class-string<AbstractModel> $parentModelClassName
     */
    public function __construct(
        private string $parentColumn = 'id',
        private ?string $parentModelClassName = null,
        private ?string $onDelete = self::RULE_CASCADE,
        private ?string $onUpdate = null,
        private ?string $name = null,
        private ?string $ownColumn = null,
    ) {
    }

    /**
     * @return class-string<AbstractModel>|null
     */
    public function getParentModelClassName(): ?string
    {
        return $this->parentModelClassName;
    }

    public function getParentColumn(): string
    {
        return $this->parentColumn;
    }

    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOwnColumn(): ?string
    {
        return $this->ownColumn;
    }
}
