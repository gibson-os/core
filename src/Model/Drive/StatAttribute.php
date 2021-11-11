<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Drive;

use GibsonOS\Core\Model\AbstractModel;

class StatAttribute extends AbstractModel
{
    private ?int $id = null;

    private string $short;

    private string $description;

    public static function getTableName(): string
    {
        return 'system_drive_stat_attribute';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): StatAttribute
    {
        $this->id = $id;

        return $this;
    }

    public function getShort(): string
    {
        return $this->short;
    }

    public function setShort(string $short): StatAttribute
    {
        $this->short = $short;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): StatAttribute
    {
        $this->description = $description;

        return $this;
    }
}
