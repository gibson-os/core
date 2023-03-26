<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\User\Device;
use JsonSerializable;
use mysqlDatabase;

/**
 * @method Device[] getDevices()
 * @method User     setDevices(Device[] $devices)
 * @method User     addDevices(Device[] $devices)
 */
#[Table]
class User extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    #[Key(true)]
    private string $user;

    #[Column(length: 64)]
    private ?string $host = null;

    #[Column(length: 15)]
    private ?string $ip = null;

    #[Column(length: 32)]
    private ?string $password = null;

    #[Column]
    private ?DateTimeInterface $lastLogin = null;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    /**
     * @var Device[]
     */
    #[Constraint('user', Device::class)]
    protected array $devices;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): User
    {
        $this->user = $user;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): User
    {
        $this->host = $host;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): User
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeInterface $lastLogin): User
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): User
    {
        $this->added = $added;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser(),
//            'password' => $this->getPassword(),
            'host' => $this->getHost(),
            'ip' => $this->getIp(),
            'lastLogin' => $this->getLastLogin(),
            'added' => $this->getAdded(),
        ];
    }

    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
