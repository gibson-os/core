<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ServiceAttribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class GetServices implements AttributeInterface
{
    /**
     * @param class-string $instanceOf
     */
    public function __construct(private array $dirs, private ?string $instanceOf)
    {
    }

    public function getAttributeServiceName(): string
    {
        return ServiceAttribute::class;
    }

    public function getDirs(): array
    {
        return $this->dirs;
    }

    /**
     * @return class-string|null
     */
    public function getInstanceOf(): ?string
    {
        return $this->instanceOf;
    }
}
