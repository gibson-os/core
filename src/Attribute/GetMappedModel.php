<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ModelMapperAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetMappedModel extends GetObject
{
    /**
     * @param array<string, string> $conditions
     * @param array<string, string> $mapping
     */
    public function __construct(private array $conditions = ['id' => 'id'], array $mapping = [])
    {
        parent::__construct($mapping);
    }

    public function getAttributeServiceName(): string
    {
        return ModelMapperAttribute::class;
    }

    /**
     * @return array|string[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
