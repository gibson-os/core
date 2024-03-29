<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonSerializable;

#[Table]
class Icon extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $name;

    #[Column(length: 4)]
    private string $originalType;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTimeImmutable();
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
