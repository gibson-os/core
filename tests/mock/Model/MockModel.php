<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

/**
 * @method MockModel   getParent()
 * @method MockModel   setParent(MockModel $parent)
 * @method MockModel[] getChildren()
 * @method MockModel   setChildren(MockModel[] $children)
 * @method MockModel   addChildren(MockModel[] $children)
 * @method MockModel   unloadChildren()
 */
#[Table('marvin')]
class MockModel extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $parentId = null;

    #[Constraint('parent', MockModel::class)]
    protected array $children = [];

    #[Constraint]
    protected ?MockModel $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): MockModel
    {
        $this->id = $id;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): MockModel
    {
        $this->parentId = $parentId;

        return $this;
    }
}
