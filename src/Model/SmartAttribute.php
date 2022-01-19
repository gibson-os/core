<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use JsonSerializable;

#[Table('system_smart_attribute')]
class SmartAttribute extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $id;

    #[Column(length: 32)]
    private string $short;

    #[Column(type: Column::TYPE_TEXT)]
    private string $description;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): SmartAttribute
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
