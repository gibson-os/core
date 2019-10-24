<?php
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

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'user';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return User
     */
    public function setUser(string $user): User
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return User
     */
    public function setHost(string $host): User
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return User
     */
    public function setIp(string $ip): User
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastLogin(): DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime $lastLogin
     * @return User
     */
    public function setLastLogin(DateTime $lastLogin): User
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAdded(): DateTime
    {
        return $this->added;
    }

    /**
     * @param DateTime $added
     * @return User
     */
    public function setAdded(DateTime $added): User
    {
        $this->added = $added;
        return $this;
    }
}