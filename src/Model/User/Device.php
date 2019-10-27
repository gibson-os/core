<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use mysqlDatabase;

class Device extends AbstractModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $registrationId;

    /**
     * @var DateTime
     */
    private $lastLogin;

    /**
     * @var DateTime
     */
    private $added;

    /**
     * @var User
     */
    private $user;

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
        return 'user_device';
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Device
     */
    public function setId(string $id): Device
    {
        $this->id = $id;

        return $this;
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
     * @return Device
     */
    public function setUserId(int $userId): Device
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     *
     * @return Device
     */
    public function setModel(string $model): Device
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationId(): string
    {
        return $this->registrationId;
    }

    /**
     * @param string $registrationId
     *
     * @return Device
     */
    public function setRegistrationId(string $registrationId): Device
    {
        $this->registrationId = $registrationId;

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
     *
     * @return Device
     */
    public function setLastLogin(DateTime $lastLogin): Device
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
     *
     * @return Device
     */
    public function setAdded(DateTime $added): Device
    {
        $this->added = $added;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Device
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->setUserId($user->getId());

        return $this;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Device
     */
    public function loadUser()
    {
        $this->loadForeignRecord($this->getUser(), $this->getUserId());

        return $this;
    }
}
