<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use JsonSerializable;

/**
 * @method MapModelParent  getParent()
 * @method MapModel        setParent(MapModelParent $mapModelParent)
 * @method MapModelChild[] getChildObjects()
 * @method MapModel        setChildObjects(MapModelChild[] $mapModelChildren)
 * @method MapModel        addChildObjects(MapModelChild[] $mapModelChildren)
 */
#[Table]
class MapModel extends AbstractModel implements JsonSerializable
{
    #[Column(autoIncrement: true)]
    private ?int $id = null;

    #[Column]
    private ?int $nullableIntValue = null;

    #[Column]
    private StringEnum $stringEnumValue;

    #[Column]
    private int $intValue;

    #[Column]
    private ?int $parentId = null;

    #[Constraint]
    protected ?MapModelParent $parent = null;

    /**
     * @var MapModelChild[]
     */
    #[Constraint('mapModel', MapModelChild::class)]
    protected array $childObjects = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): MapModel
    {
        $this->id = $id;

        return $this;
    }

    public function getNullableIntValue(): ?int
    {
        return $this->nullableIntValue;
    }

    public function setNullableIntValue(?int $nullableIntValue): MapModel
    {
        $this->nullableIntValue = $nullableIntValue;

        return $this;
    }

    public function getStringEnumValue(): StringEnum
    {
        return $this->stringEnumValue;
    }

    public function setStringEnumValue(StringEnum $stringEnumValue): MapModel
    {
        $this->stringEnumValue = $stringEnumValue;

        return $this;
    }

    public function getIntValue(): int
    {
        return $this->intValue;
    }

    public function setIntValue(int $intValue): MapModel
    {
        $this->intValue = $intValue;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): MapModel
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'nullableIntValue' => $this->getNullableIntValue(),
            'stringEnumValue' => $this->getStringEnumValue(),
            'intValue' => $this->getIntValue(),
            'parentId' => $this->getParentId(),
        ];
    }
}
