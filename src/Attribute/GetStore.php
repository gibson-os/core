<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\StoreAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetStore implements AttributeInterface
{
    public function __construct(
        private readonly string $startParameter = 'start',
        private readonly string $limitParameter = 'limit',
        private readonly string $sortParameter = 'sort',
        private readonly string $filtersParameter = 'filters',
    ) {
    }

    public function getAttributeServiceName(): string
    {
        return StoreAttribute::class;
    }

    public function getStartParameter(): string
    {
        return $this->startParameter;
    }

    public function getLimitParameter(): string
    {
        return $this->limitParameter;
    }

    public function getSortParameter(): string
    {
        return $this->sortParameter;
    }

    public function getFiltersParameter(): string
    {
        return $this->filtersParameter;
    }
}
