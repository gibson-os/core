<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeInterface;

class Cronjob extends AbstractModel
{
    private ?int $id = null;

    private string $command;

    private ?string $arguments = null;

    private ?string $options = null;

    private string $user;

    private ?DateTimeInterface $lastRun = null;

    private bool $active;

    private DateTimeInterface $added;

    public static function getTableName(): string
    {
        return 'cronjob';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Cronjob
    {
        $this->id = $id;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): Cronjob
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): Cronjob
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(?string $options): Cronjob
    {
        $this->options = $options;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): Cronjob
    {
        $this->user = $user;

        return $this;
    }

    public function getLastRun(): ?DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?DateTimeInterface $lastRun): Cronjob
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Cronjob
    {
        $this->active = $active;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Cronjob
    {
        $this->added = $added;

        return $this;
    }
}
