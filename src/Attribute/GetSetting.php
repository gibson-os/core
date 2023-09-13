<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\SettingAttribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class GetSetting implements AttributeInterface
{
    public function __construct(
        private readonly string $key,
        private readonly ?string $module = null,
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

    public function getModule(): ?string
    {
        return $this->module;
    }
}
