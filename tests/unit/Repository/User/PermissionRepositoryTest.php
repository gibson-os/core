<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class PermissionRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private PermissionRepository $permissionRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`user_permission`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['permission', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->permissionRepository = new PermissionRepository();
    }

    public function testGetByModuleTaskAndAction(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module_id`=? AND `task_id`=? AND `action_id`=? AND IFNULL(`user_id`, ?)=? LIMIT 1',
            [42, 420, 4242, 0, 0],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'permission' => '4',
            ]])
        ;

        $permission = $this->permissionRepository->getByModuleTaskAndAction(
            (new Module())->setId(42),
            (new Task())->setId(420),
            (new Action())->setId(4242)
        );

        $this->assertEquals(4, $permission->getPermission());
    }

    public function testGetByModuleTaskAndActionWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module_id`=? AND `task_id`=? AND `action_id`=? AND IFNULL(`user_id`, ?)=? LIMIT 1',
            [42, 420, 4242, 0, 4200],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'permission' => '4',
            ]])
        ;

        $permission = $this->permissionRepository->getByModuleTaskAndAction(
            (new Module())->setId(42),
            (new Task())->setId(420),
            (new Action())->setId(4242),
            4200
        );

        $this->assertEquals(4, $permission->getPermission());
    }

    public function testGetByModuleAndTask(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module_id`=? AND `task_id`=? AND `action_id` IS NULL AND IFNULL(`user_id`, ?)=? LIMIT 1',
            [42, 420, 0, 0],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'permission' => '4',
            ]])
        ;

        $permission = $this->permissionRepository->getByModuleAndTask(
            (new Module())->setId(42),
            (new Task())->setId(420),
        );

        $this->assertEquals(4, $permission->getPermission());
    }

    public function testGetByModuleAndTaskWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module_id`=? AND `task_id`=? AND `action_id` IS NULL AND IFNULL(`user_id`, ?)=? LIMIT 1',
            [42, 420, 0, 4242],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'permission' => '4',
            ]])
        ;

        $permission = $this->permissionRepository->getByModuleAndTask(
            (new Module())->setId(42),
            (new Task())->setId(420),
            4242
        );

        $this->assertEquals(4, $permission->getPermission());
    }
}
