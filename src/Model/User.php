<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use mysqlDatabase;

class User extends AbstractModel
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var string|null
     */
    private $ip;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var DateTimeInterface|null
     */
    private $lastLogin;

    /**
     * @var DateTimeInterface
     */
    private $added;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public static function getTableName(): string
    {
        return 'user';
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
}
