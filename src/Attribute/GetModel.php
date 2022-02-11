<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetModel implements AttributeInterface
{
    /**
     * @param array<string, string> $conditions
     */
    public function __construct(private array $conditions = ['id' => 'id'])
    {
    }

    public function getAttributeServiceName(): string
    {
        return ModelFetcherAttribute::class;
    }

    /**
     * @return array|string[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
