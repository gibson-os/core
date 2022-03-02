<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

/**
 * @method MapModel[]     getObjects()
 * @method MapModelParent setObjects(MapModel[] $mapModels)
 * @method MapModelParent addObjects(MapModel[] $mapModels)
 */
#[Table]
class MapModelParent extends AbstractModel
{
    #[Column(autoIncrement: true)]
    private ?int $id = null;

    #[Column]
    private bool $default = false;

    #[Column]
    private array $options = [];

    /**
     * @var MapModel[]
     */
    #[Constraint('parent', MapModel::class)]
    protected array $objects = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): MapModelParent
    {
        $this->id = $id;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): MapModelParent
    {
        $this->default = $default;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): MapModelParent
    {
        $this->options = $options;

        return $this;
    }
}
