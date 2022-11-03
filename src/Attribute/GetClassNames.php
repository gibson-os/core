<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\ServiceAttribute;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class GetClassNames implements AttributeInterface
{
    public function __construct(private array $dirs)
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
}
