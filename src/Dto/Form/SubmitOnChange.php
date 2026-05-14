<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use GibsonOS\Core\Enum\HttpMethod;
use JsonSerializable;
use Override;
use stdClass;

class SubmitOnChange implements JsonSerializable
{
    public function __construct(
        private readonly string $module,
        private readonly string $task,
        private readonly string $action,
        private readonly array $parameters = [],
        private readonly HttpMethod $method = HttpMethod::POST,
    ) {
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'parameters' => $this->getParameters() ?: new stdClass(),
            'method' => $this->getMethod()->value,
        ];
    }
}
