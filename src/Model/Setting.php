<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use mysqlDatabase;

class Setting extends AbstractModel
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @var User
     */
    private $user;

    /**
     * @param mysqlDatabase|null $database
     *
     * @throws GetError
     */
    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->user = new User();
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'setting';
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return Setting
     */
    public function setUserId(int $userId): Setting
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    /**
     * @param int $moduleId
     *
     * @return Setting
     */
    public function setModuleId(int $moduleId): Setting
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Setting
     */
    public function setKey(string $key): Setting
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Setting
     */
    public function setValue(string $value): Setting
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return User
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Setting
     */
    public function setUser(User $user): Setting
    {
        $this->user = $user;
        $this->setUserId($user->getId());

        return $this;
    }
}
