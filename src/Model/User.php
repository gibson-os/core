<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTime;

class User extends AbstractModel
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $password;

    /**
     * @var DateTime
     */
    private $lastLogin;

    /**
     * @var DateTime
     */
    private $added;

    public static function getTableName(): string
    {
        return 'user';
    }

    public function getId(): int
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

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): User
    {
        $this->host = $host;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): User
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getLastLogin(): DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(DateTime $lastLogin): User
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getAdded(): DateTime
    {
        return $this->added;
    }

    public function setAdded(DateTime $added): User
    {
        $this->added = $added;

        return $this;
    }
}
