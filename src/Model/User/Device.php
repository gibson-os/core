<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Wrapper\ModelWrapper;

/**
 * @method User   getUser()
 * @method Device setUser(User $user)
 */
#[Table]
class Device extends AbstractModel
{
    #[Column(length: 16, primary: true)]
    private string $id;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Column(length: 32)]
    private string $model;

    #[Column(length: 255)]
    #[Key(true)]
    private ?string $registrationId = null;

    #[Column(length: 64)]
    private ?string $token = null;

    #[Column(length: 512)]
    private ?string $fcmToken = null;

    #[Column]
    private ?DateTimeInterface $lastLogin = null;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    #[Constraint]
    protected User $user;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTimeImmutable();
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): Device
    {
        $this->token = $token;

        return $this;
    }

    public function getFcmToken(): ?string
    {
        return $this->fcmToken;
    }

    public function setFcmToken(?string $fcmToken): Device
    {
        $this->fcmToken = $fcmToken;

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
}
