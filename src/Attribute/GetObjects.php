<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ObjectsMapperAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class GetObjects implements AttributeInterface
{
    /**
     * @param class-string          $className
     * @param array<string, string> $mapping
     */
    public function __construct(private readonly string $className, private readonly array $mapping = [])
    {
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAttributeServiceName(): string
    {
        return ObjectsMapperAttribute::class;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }
}
