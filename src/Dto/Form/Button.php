<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use JsonSerializable;

class Button implements JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly string $text,
        private readonly ?string $module = null,
        private readonly ?string $task = null,
        private readonly ?string $action = null,
        private readonly array $parameters = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'text' => $this->getText(),
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'parameters' => $this->getParameters(),
        ];
    }
}
