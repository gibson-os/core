<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Attribute\Install\Cronjob;
use JsonSerializable;
use Override;

class Command implements JsonSerializable
{
    /**
     * @param class-string $classString
     * @param Cronjob[]    $cronjobs
     */
    public function __construct(
        private string $classString,
        private string $command,
        private string $description,
        private array $arguments = [],
        private array $options = [],
        private array $cronjobs = [],
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

    /**
     * @return Cronjob[]
     */
    public function getCronjobs(): array
    {
        return $this->cronjobs;
    }

    #[Override]
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
