<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use mysqlDatabase;

class Icon extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private string $name = '';

    private string $originalType = '';

    private DateTimeInterface $added;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public static function getTableName(): string
    {
        return 'icon';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Icon
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Icon
    {
        $this->name = $name;

        return $this;
    }

    public function getOriginalType(): string
    {
        return $this->originalType;
    }

    public function setOriginalType(string $originalType): Icon
    {
        $this->originalType = $originalType;

        return $this;
    }

    public function getAdded(): DateTimeImmutable|DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeImmutable|DateTimeInterface $added): Icon
    {
        $this->added = $added;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'originalType' => $this->getOriginalType(),
        ];
    }
}
