<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Form;

use JsonSerializable;

class Button implements JsonSerializable
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $url = null,
        private readonly array $parameters = [],
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function jsonSerialize(): array
    {
        return [
            'text' => $this->getText(),
            'url' => $this->getUrl(),
            'parameters' => $this->getParameters(),
        ];
    }
}
