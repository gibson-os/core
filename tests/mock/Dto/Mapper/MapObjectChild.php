<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

class MapObjectChild
{
    private ?IntEnum $nullableIntEnumValue = null;

    private ?string $nullableStringValue = null;

    public function __construct(private string $stringValue)
    {
    }

    public function getNullableIntEnumValue(): ?IntEnum
    {
        return $this->nullableIntEnumValue;
    }

    public function setNullableIntEnumValue(?IntEnum $nullableIntEnumValue): MapObjectChild
    {
        $this->nullableIntEnumValue = $nullableIntEnumValue;

        return $this;
    }

    public function getNullableStringValue(): ?string
    {
        return $this->nullableStringValue;
    }

    public function setNullableStringValue(?string $nullableStringValue): MapObjectChild
    {
        $this->nullableStringValue = $nullableStringValue;

        return $this;
    }

    public function getStringValue(): string
    {
        return $this->stringValue;
    }
}
