<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\EnvAttribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class GetEnv implements AttributeInterface
{
    public function __construct(private string $key, private ?string $name = null)
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

    public function getName(): ?string
    {
        return $this->name;
    }
}
