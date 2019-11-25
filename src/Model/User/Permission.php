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
    private $module;

    /**
     * @var string
     */
    private $task;

    /**
     * @var string
     */
    private $action;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $permission;

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
        return 'user_permission';
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @param string $module
     *
     * @return Permission
     */
    public function setModule(string $module): Permission
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @param string $task
     *
     * @return Permission
     */
    public function setTask(string $task): Permission
    {
        $this->task = $task;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Permission
     */
    public function setAction(string $action): Permission
    {
        $this->action = $action;

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
     * @return Permission
     */
    public function setUserId(int $userId): Permission
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPermission(): int
    {
        return $this->permission;
    }

    /**
     * @param int $permission
     *
     * @return Permission
     */
    public function setPermission(int $permission): Permission
    {
        $this->permission = $permission;

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
     * @return Permission
     */
    public function setUser(User $user): Permission
    {
        $this->user = $user;
        $this->setUserId($user->getId());

        return $this;
    }
}
