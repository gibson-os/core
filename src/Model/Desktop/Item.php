<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Desktop;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;

/**
 * @method User getUser()
 * @method Item setUser(User $user)
 */
#[Table]
class Item extends AbstractModel implements \JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $text;

    #[Column(length: 64)]
    private string $icon;

    #[Column(length: 32)]
    private string $module;

    #[Column(length: 32)]
    private string $task;

    #[Column(length: 32)]
    private string $action;

    #[Column]
    private array $parameters = [];

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $position = 0;

    #[Constraint]
    protected User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Item
    {
        $this->id = $id;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Item
    {
        $this->text = $text;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): Item
    {
        $this->icon = $icon;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): Item
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): Item
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): Item
    {
        $this->action = $action;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): Item
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Item
    {
        $this->userId = $userId;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): Item
    {
        $this->position = $position;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $parameters = $this->getParameters();

        return [
            'id' => $this->getId(),
            'text' => $this->getText(),
            'icon' => $this->getIcon(),
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'position' => $this->getPosition(),
            'parameters' => count($parameters) === 0 ? null : $parameters,
        ];
    }
}
