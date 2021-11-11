<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Drive;

use GibsonOS\Core\Model\AbstractModel;

class StatAttribute extends AbstractModel
{
    private int $statId;

    private int $attributeId;

    private int $value;

    private int $worst;

    private int $thresh;

    private int $rawValue;

    public static function getTableName(): string
    {
        return 'system_drive_stat_attribute';
    }

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
