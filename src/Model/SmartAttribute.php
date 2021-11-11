<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use JsonSerializable;

class SmartAttribute extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private string $short;

    private string $description;

    public static function getTableName(): string
    {
        return 'system_smart_attribute';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): SmartAttribute
    {
        $this->id = $id;

        return $this;
    }

    public function getShort(): string
    {
        return $this->short;
    }

    public function setShort(string $short): SmartAttribute
    {
        $this->short = $short;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): SmartAttribute
    {
        $this->description = $description;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'short' => $this->getShort(),
            'description' => $this->getDescription(),
        ];
    }
}
