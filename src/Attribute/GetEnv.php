<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\EnvAttribute;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class GetEnv implements AttributeInterface
{
    public function __construct(private string $key)
    {
    }

    public function getAttributeServiceName(): string
    {
        return EnvAttribute::class;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
