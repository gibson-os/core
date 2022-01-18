<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use JsonSerializable;
use mysqlDatabase;

#[Table]
#[Key(unique: true, columns: ['command', 'arguments', 'options', 'user'])]
class Cronjob extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    /**
     * @var class-string
     */
    #[Column(length: 255)]
    private string $command;

    #[Column(length: 255)]
    private string $arguments = '[]';

    #[Column(length: 255)]
    private string $options = '[]';

    #[Column(length: 64)]
    #[Key]
    private string $user;

    #[Column]
    private ?DateTimeInterface $lastRun = null;

    #[Column]
    private bool $active = true;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
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

    /**
     * @return class-string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param class-string $command
     *
     * @return $this
     */
    public function setCommand(string $command): Cronjob
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(string $arguments): Cronjob
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(string $options): Cronjob
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'command' => $this->getCommand(),
            'arguments' => $this->getArguments(),
            'options' => $this->getOptions(),
            'user' => $this->getUser(),
            'active' => $this->isActive(),
            'lastRun' => $this->getLastRun()?->format('Y-m-d H:i:s'),
            'added' => $this->getAdded()->format('Y-m-d H:i:s'),
        ];
    }
}
