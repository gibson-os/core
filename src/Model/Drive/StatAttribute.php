<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Drive;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\SmartAttribute;

/**
 * @method Stat           getStat()
 * @method StatAttribute  setStat(Stat $stat)
 * @method SmartAttribute getAttribute()
 * @method StatAttribute  setAttribute(SmartAttribute $attribute)
 */
#[Table('system_drive_stat_attribute')]
class StatAttribute extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $statId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $attributeId;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $value;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $worst;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $thresh;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $rawValue;

    #[Constraint]
    protected Stat $stat;

    #[Constraint]
    protected SmartAttribute $attribute;

    public function getStatId(): int
    {
        return $this->statId;
    }

    public function setStatId(int $statId): StatAttribute
    {
        $this->statId = $statId;

        return $this;
    }

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    public function setAttributeId(int $attributeId): StatAttribute
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): StatAttribute
    {
        $this->value = $value;

        return $this;
    }

    public function getWorst(): int
    {
        return $this->worst;
    }

    public function setWorst(int $worst): StatAttribute
    {
        $this->worst = $worst;

        return $this;
    }

    public function getThresh(): int
    {
        return $this->thresh;
    }

    public function setThresh(int $thresh): StatAttribute
    {
        $this->thresh = $thresh;

        return $this;
    }

    public function getRawValue(): int
    {
        return $this->rawValue;
    }

    public function setRawValue(int $rawValue): StatAttribute
    {
        $this->rawValue = $rawValue;

        return $this;
    }
}
