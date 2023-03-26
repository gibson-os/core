<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\TableAttribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class GetTableName implements AttributeInterface
{
    /**
     * @param class-string $modelClassName
     */
    public function __construct(private string $modelClassName)
    {
    }

    public function getAttributeServiceName(): string
    {
        return TableAttribute::class;
    }

    /**
     * @return class-string
     */
    public function getModelClassName(): string
    {
        return $this->modelClassName;
    }
}
