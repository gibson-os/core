<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class PermissionRepositoryTest extends Unit
{
    use RepositoryTrait;

    private PermissionRepository $permissionRepository;

    protected function _before()
    {
        $this->loadRepository('user_permission');

        $this->permissionRepository = new PermissionRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByModuleTaskAndAction(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND `task_id`=? AND `action_id`=? AND IFNULL(`user_id`, ?)=?',
                [42, 420, 4242, 0, 0],
            ))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Permission::class);
        $permission = $this->permissionRepository->getByModuleTaskAndAction(
            (new Module($this->modelWrapper->reveal()))->setId(42),
            (new Task($this->modelWrapper->reveal()))->setId(420),
            (new Action($this->modelWrapper->reveal()))->setId(4242),
        );

        $this->assertEquals($model, $permission);
    }

    public function testGetByModuleTaskAndActionWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND `task_id`=? AND `action_id`=? AND IFNULL(`user_id`, ?)=?',
                [42, 420, 4242, 0, 4200],
            ))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Permission::class);
        $permission = $this->permissionRepository->getByModuleTaskAndAction(
            (new Module($this->modelWrapper->reveal()))->setId(42),
            (new Task($this->modelWrapper->reveal()))->setId(420),
            (new Action($this->modelWrapper->reveal()))->setId(4242),
            4200,
        );

        $this->assertEquals($model, $permission);
    }

    public function testGetByModuleAndTask(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND `task_id`=? AND `action_id` IS NULL AND IFNULL(`user_id`, ?)=?',
                [42, 420, 0, 0],
            ))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Permission::class);
        $permission = $this->permissionRepository->getByModuleAndTask(
            (new Module($this->modelWrapper->reveal()))->setId(42),
            (new Task($this->modelWrapper->reveal()))->setId(420),
        );

        $this->assertEquals($model, $permission);
    }

    public function testGetByModuleAndTaskWithUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND `task_id`=? AND `action_id` IS NULL AND IFNULL(`user_id`, ?)=?',
                [42, 420, 0, 4200],
            ))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Permission::class);
        $permission = $this->permissionRepository->getByModuleAndTask(
            (new Module($this->modelWrapper->reveal()))->setId(42),
            (new Task($this->modelWrapper->reveal()))->setId(420),
            4200,
        );

        $this->assertEquals($model, $permission);
    }
}
