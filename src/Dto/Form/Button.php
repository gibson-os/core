<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use JsonSerializable;
use stdClass;

class Button implements JsonSerializable
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $module = null,
        private readonly ?string $task = null,
        private readonly ?string $action = null,
        private readonly array $parameters = [],
    ) {
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
            'text' => $this->getText(),
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'parameters' => $this->getParameters() ?: new stdClass(),
        ];
    }
}
