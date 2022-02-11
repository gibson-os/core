<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GetObject implements AttributeInterface
{
    public function __construct(private array $mapping = [])
    {
    }

    public function getAttributeServiceName(): string
    {
        return ObjectMapperAttribute::class;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }
}
