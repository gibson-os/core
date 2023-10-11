<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use Generator;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\PermissionView;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use ReflectionException;

class PermissionViewRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(PermissionView::class)]
        private string $permissionViewName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     *
     * @return Generator<Record>
     */
    public function getTaskList(?int $userId, string $module = null): Generator
    {
        $selectQuery = $this->getSelectQuery($this->permissionViewName)
            ->setDistinct(true)
            ->setSelects(['module' => '`module_name`', 'task' => '`task_name`'])
            ->addWhere(new Where('IFNULL(`user_id`, :userId)=:userId', ['userId' => $userId ?? 0]))
            ->addWhere(new Where('`permission`>:permission', ['permission' => Permission::DENIED->value]))
            ->addWhere(new Where('`task_id` IS NOT NULL', []))
        ;

        if ($module !== null) {
            $selectQuery->addWhere(new Where('`module_name`=:moduleName', ['moduleName' => $module]));
        }

        $result = $this->getRepositoryWrapper()->getClient()->execute($selectQuery);

        return $result?->iterateRecords() ?? new Generator();
    }

    /**
     * @throws ClientException
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    public function getPermissionByModule(string $module, int $userId = null): PermissionView
    {
        return $this->fetchOne(
            'IFNULL(`user_id`, :userId)=:userId AND ' .
            '`module_name`=:moduleName AND ' .
            '`task_name` IS NULL AND ' .
            '`action_name` IS NULL',
            [
                'userId' => $userId,
                'moduleName' => $module,
            ],
            PermissionView::class,
            ['`user_id`' => OrderDirection::DESC, '`permission_module_id`' => OrderDirection::DESC],
        );
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getPermissionByTask(string $module, string $task, int $userId = null): PermissionView
    {
        return $this->fetchOne(
            'IFNULL(`user_id`, :userId)=:userId AND ' .
            '`module_name`=:moduleName AND ' .
            '`task_name`=:taskName AND ' .
            '`action_name` IS NULL',
            [
                'userId' => $userId,
                'moduleName' => $module,
                'taskName' => $task,
            ],
            PermissionView::class,
            [
                '`user_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ],
        );
    }

    /**
     * @throws SelectError
     */
    public function getPermissionByAction(
        string $module,
        string $task,
        string $action,
        HttpMethod $method,
        int $userId = null,
    ): PermissionView {
        return $this->fetchOne(
            'IFNULL(`user_id`, :userId)=:userId AND ' .
            '`module_name`=:moduleName AND ' .
            '`task_name`=:taskName AND ' .
            '`action_name`=:actionName AND ' .
            '`action_method`=:actionMethod',
            [
                'userId' => $userId,
                'moduleName' => $module,
                'taskName' => $task,
                'actionName' => $action,
                'actionMethod' => $method->value,
            ],
            PermissionView::class,
            [
                '`user_id`' => OrderDirection::DESC,
                '`permission_action_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ],
        );
    }
}
