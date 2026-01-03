<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use Override;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class GetTableName implements AttributeInterface
{
    /**
     * @param class-string $modelClassName
     */
    public function __construct(private readonly string $modelClassName)
    {
    }

    #[Override]
    public function getAttributeServiceName(): string
    {
        return TableNameAttribute::class;
    }

    /**
     * @return class-string
     */
    public function getModelClassName(): string
    {
        return $this->modelClassName;
    }
}
