<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use Override;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetObject implements AttributeInterface
{
    /**
     * @param array<string, string> $mapping
     */
    public function __construct(private array $mapping = [])
    {
    }

    #[Override]
    public function getAttributeServiceName(): string
    {
        return ObjectMapperAttribute::class;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }
}
