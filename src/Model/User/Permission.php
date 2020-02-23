<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use mysqlDatabase;

class Permission extends AbstractModel
{
    /**
     * @var string
     */
    private $module = '';

    /**
     * @var string
     */
    private $task = '';

    /**
     * @var string
     */
    private $action = '';

    /**
     * @var int
     */
    private $userId = 0;

    /**
     * @var int
     */
    private $permission = 1;

    /**
     * @var User
     */
    private $user;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->user = new User();
    }

    public static function getTableName(): string
    {
        return 'user_permission';
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): Permission
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): Permission
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): Permission
    {
        $this->action = $action;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Permission
    {
        $this->userId = $userId;

        return $this;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function setPermission(int $permission): Permission
    {
        $this->permission = $permission;

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

    public function setUser(User $user): Permission
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }
}
