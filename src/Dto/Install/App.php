<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Install;

use JsonSerializable;

class App implements JsonSerializable
{
    public function __construct(
        private string $task,
        private string $module,
        private string $action,
        private string $icon,
    ) {
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function jsonSerialize(): array
    {
        return [
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'icon' => $this->getIcon(),
        ];
    }
}
