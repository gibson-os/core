<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use JsonSerializable;
use Override;

/**
 * @method Module getModule()
 * @method Task   setModule(Module $module)
 */
#[Table]
#[Key(unique: true, columns: ['name', 'module_id'])]
class Task extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 32)]
    private string $name;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $moduleId;

    #[Constraint]
    protected Module $module;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Task
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Task
    {
        $this->name = $name;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Task
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'moduleId' => $this->getModuleId(),
        ];
    }

    #[Override]
    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
