<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use DateTimeImmutable;
use DateTimeInterface;
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
    private $id = '';

    /**
     * @var int
     */
    private $userId = 0;

    /**
     * @var string
     */
    private $model = '';

    /**
     * @var string|null
     */
    private $registrationId;

    /**
     * @var DateTimeInterface|null
     */
    private $lastLogin;

    /**
     * @var DateTimeInterface
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
        $this->added = new DateTimeImmutable();
    }

    public static function getTableName(): string
    {
        return 'user_device';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Device
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Device
    {
        $this->userId = $userId;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): Device
    {
        $this->model = $model;

        return $this;
    }

    public function getRegistrationId(): ?string
    {
        return $this->registrationId;
    }

    public function setRegistrationId(?string $registrationId): Device
    {
        $this->registrationId = $registrationId;

        return $this;
    }

    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeInterface $lastLogin): Device
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Device
    {
        $this->added = $added;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    public function setUser(User $user): Device
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }
}
