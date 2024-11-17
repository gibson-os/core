<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetModel implements AttributeInterface
{
    /**
     * @param array<string, string> $conditions
     * @param ChildrenMapping[]     $extends
     */
    public function __construct(
        private readonly array $conditions = ['id' => 'id'],
        private readonly array $extends = [],
        private readonly string $alias = 't',
    ) {
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

    public function getExtends(): array
    {
        return $this->extends;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
