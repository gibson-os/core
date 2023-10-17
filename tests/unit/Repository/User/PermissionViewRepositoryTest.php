<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Model\User\PermissionView;
use GibsonOS\Core\Repository\User\PermissionViewRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Result;
use MDO\Enum\OrderDirection;
use MDO\Query\SelectQuery;

class PermissionViewRepositoryTest extends Unit
{
    use RepositoryTrait;

    private PermissionViewRepository $permissionViewRepository;

    protected function _before()
    {
        $this->loadRepository('view_user_permission');

        $this->permissionViewRepository = new PermissionViewRepository(
            $this->repositoryWrapper->reveal(),
            $this->table->getTableName(),
        );
    }

    public function testGetTaskList(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->setDistinct(true)
            ->setSelects(['module' => '`module_name`', 'task' => '`task_name`'])
            ->addWhere(new Where('IFNULL(`user_id`, :userId)=:userId', ['userId' => 0]))
            ->addWhere(new Where('`permission`>:permission', ['permission' => Permission::DENIED->value]))
            ->addWhere(new Where('`task_id` IS NOT NULL', []))
        ;
        $result = $this->prophesize(Result::class);
        $record = new Record(['module' => 'galaxy', 'permission' => 1]);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertEquals($record, $this->permissionViewRepository->getTaskList(null)->current());
    }

    public function testGetTaskListWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->setDistinct(true)
            ->setSelects(['module' => '`module_name`', 'task' => '`task_name`'])
            ->addWhere(new Where('IFNULL(`user_id`, :userId)=:userId', ['userId' => 42]))
            ->addWhere(new Where('`permission`>:permission', ['permission' => Permission::DENIED->value]))
            ->addWhere(new Where('`task_id` IS NOT NULL', []))
        ;
        $result = $this->prophesize(Result::class);
        $record = new Record(['module' => 'galaxy', 'permission' => 1]);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertEquals($record, $this->permissionViewRepository->getTaskList(42)->current());
    }

    public function testGetTaskListWithModule(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->setDistinct(true)
            ->setSelects(['module' => '`module_name`', 'task' => '`task_name`'])
            ->addWhere(new Where('IFNULL(`user_id`, :userId)=:userId', ['userId' => 0]))
            ->addWhere(new Where('`permission`>:permission', ['permission' => Permission::DENIED->value]))
            ->addWhere(new Where('`task_id` IS NOT NULL', []))
            ->addWhere(new Where('`module_name`=:moduleName', ['moduleName' => 'galaxy']))
        ;
        $result = $this->prophesize(Result::class);
        $record = new Record(['module' => 'galaxy', 'permission' => 1]);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertEquals($record, $this->permissionViewRepository->getTaskList(null, 'galaxy')->current());
    }

    public function testGetTaskListWithUserIdAndModule(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->setDistinct(true)
            ->setSelects(['module' => '`module_name`', 'task' => '`task_name`'])
            ->addWhere(new Where('IFNULL(`user_id`, :userId)=:userId', ['userId' => 42]))
            ->addWhere(new Where('`permission`>:permission', ['permission' => Permission::DENIED->value]))
            ->addWhere(new Where('`task_id` IS NOT NULL', []))
            ->addWhere(new Where('`module_name`=:moduleName', ['moduleName' => 'galaxy']))
        ;
        $result = $this->prophesize(Result::class);
        $record = new Record(['module' => 'galaxy', 'permission' => 1]);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertEquals($record, $this->permissionViewRepository->getTaskList(42, 'galaxy')->current());
    }

    public function testGetPermissionByModule(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name` IS NULL AND `action_name` IS NULL',
                ['userIdNull' => 0, 'userId' => 0, 'moduleName' => 'galaxy'],
            ))
            ->setOrders(['`user_id`' => OrderDirection::DESC, '`permission_module_id`' => OrderDirection::DESC])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByModule('galaxy'),
        );
    }

    public function testGetPermissionByModuleWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name` IS NULL AND `action_name` IS NULL',
                ['userIdNull' => 0, 'userId' => 42, 'moduleName' => 'galaxy'],
            ))
            ->setOrders(['`user_id`' => OrderDirection::DESC, '`permission_module_id`' => OrderDirection::DESC])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByModule('galaxy', 42),
        );
    }

    public function testGetPermissionByTask(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name`=:taskName AND `action_name` IS NULL',
                ['userIdNull' => 0, 'userId' => 0, 'moduleName' => 'galaxy', 'taskName' => 'ford'],
            ))
            ->setOrders([
                '`user_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByTask('galaxy', 'ford'),
        );
    }

    public function testGetPermissionByTaskWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name`=:taskName AND `action_name` IS NULL',
                ['userIdNull' => 0, 'userId' => 42, 'moduleName' => 'galaxy', 'taskName' => 'ford'],
            ))
            ->setOrders([
                '`user_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByTask('galaxy', 'ford', 42),
        );
    }

    public function testGetPermissionByAction(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name`=:taskName AND `action_name`=:actionName AND `action_method`=:actionMethod',
                [
                    'userIdNull' => 0,
                    'userId' => 0,
                    'moduleName' => 'galaxy',
                    'taskName' => 'ford',
                    'actionName' => 'prefect',
                    'actionMethod' => 'HEAD',
                ],
            ))
            ->setOrders([
                '`user_id`' => OrderDirection::DESC,
                '`permission_action_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByAction('galaxy', 'ford', 'prefect', HttpMethod::HEAD),
        );
    }

    public function testGetPermissionByActionWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                'IFNULL(`user_id`, :userIdNull)=:userId AND `module_name`=:moduleName AND `task_name`=:taskName AND `action_name`=:actionName AND `action_method`=:actionMethod',
                [
                    'userIdNull' => 0,
                    'userId' => 42,
                    'moduleName' => 'galaxy',
                    'taskName' => 'ford',
                    'actionName' => 'prefect',
                    'actionMethod' => 'HEAD',
                ],
            ))
            ->setOrders([
                '`user_id`' => OrderDirection::DESC,
                '`permission_action_id`' => OrderDirection::DESC,
                '`permission_task_id`' => OrderDirection::DESC,
                '`permission_module_id`' => OrderDirection::DESC,
            ])
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, PermissionView::class),
            $this->permissionViewRepository->getPermissionByAction('galaxy', 'ford', 'prefect', HttpMethod::HEAD, 42),
        );
    }
}
