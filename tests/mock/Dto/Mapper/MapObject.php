<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

use GibsonOS\Core\Attribute\ObjectMapper;

class MapObject
{
    /**
     * @var MapObjectChild[]
     */
    #[ObjectMapper(MapObjectChild::class)]
    private array $childObjects = [];

    private ?int $nullableIntValue = null;

    public function __construct(
        private StringEnum $stringEnumValue,
        private int $intValue,
        private ?MapObjectParent $parent = null
    ) {
    }

    /**
     * @return MapObjectChild[]
     */
    public function getChildObjects(): array
    {
        return $this->childObjects;
    }

    /**
     * @param MapObjectChild[] $childObjects
     */
    public function setChildObjects(array $childObjects): MapObject
    {
        $this->childObjects = $childObjects;

        return $this;
    }

    public function getNullableIntValue(): ?int
    {
        return $this->nullableIntValue;
    }

    public function setNullableIntValue(?int $nullableIntValue): MapObject
    {
        $this->nullableIntValue = $nullableIntValue;

        return $this;
    }

    public function getStringEnumValue(): StringEnum
    {
        return $this->stringEnumValue;
    }

    public function getIntValue(): int
    {
        return $this->intValue;
    }

    public function getParent(): ?MapObjectParent
    {
        return $this->parent;
    }
}
