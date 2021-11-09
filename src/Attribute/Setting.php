<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\SettingAttribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Setting implements AttributeInterface
{
    public function __construct(
        private string $key,
        private ?string $name = null,
        private ?string $module = null
    ) {
    }

    public function getAttributeServiceName(): string
    {
        return SettingAttribute::class;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }
}
