<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use JsonSerializable;

/**
 * @method MapModel      getMapModel()
 * @method MapModelChild setMapModel(MapModel $mapModel)
 */
#[Table]
class MapModelChild extends AbstractModel implements JsonSerializable
{
    #[Column(autoIncrement: true)]
    private ?int $id = null;

    #[Column]
    private ?IntEnum $nullableIntEnumValue = null;

    #[Column]
    private ?string $stringValue = null;

    #[Column]
    private ?string $nullableStringValue = null;

    #[Column]
    private int $mapModelId;

    #[Constraint]
    protected MapModel $mapModel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): MapModelChild
    {
        $this->id = $id;

        return $this;
    }

    public function getNullableIntEnumValue(): ?IntEnum
    {
        return $this->nullableIntEnumValue;
    }

    public function setNullableIntEnumValue(?IntEnum $nullableIntEnumValue): MapModelChild
    {
        $this->nullableIntEnumValue = $nullableIntEnumValue;

        return $this;
    }

    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    public function setStringValue(?string $stringValue): MapModelChild
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    public function getNullableStringValue(): ?string
    {
        return $this->nullableStringValue;
    }

    public function setNullableStringValue(?string $nullableStringValue): MapModelChild
    {
        $this->nullableStringValue = $nullableStringValue;

        return $this;
    }

    public function getMapModelId(): int
    {
        return $this->mapModelId;
    }

    public function setMapModelId(int $mapModelId): MapModelChild
    {
        $this->mapModelId = $mapModelId;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'nullableIntEnumValue' => $this->getNullableIntEnumValue(),
            'nullableStringValue' => $this->getNullableStringValue(),
            'stringValue' => $this->getStringValue(),
            'mapModelId' => $this->getMapModelId(),
        ];
    }
}
