<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use JsonSerializable;

class Command implements JsonSerializable
{
    /**
     * @param class-string $classString
     */
    public function __construct(
        private string $classString,
        private string $command,
        private string $description,
        private array $arguments = [],
        private array $options = [],
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassString(): string
    {
        return $this->classString;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function jsonSerialize(): array
    {
        return [
            'classString' => $this->getClassString(),
            'command' => $this->getCommand(),
            'description' => $this->getDescription(),
            'arguments' => $this->getArguments(),
            'options' => $this->getOptions(),
        ];
    }
}
