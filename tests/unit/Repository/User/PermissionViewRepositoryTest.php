<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use stdClass;

class PermissionViewRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private PermissionViewRepository $permissionViewRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`view_user_permission`')
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

        $this->permissionViewRepository = new PermissionViewRepository('view_user_permission');
    }

    public function testGetTaskList(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT DISTINCT `module_name` AS `module`, `task_name` AS `task` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `permission`>? AND `task_id` IS NOT NULL',
            [0, 0, 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $permissionObject = new stdClass();
        $permissionObject->module = 'galaxy';
        $permissionObject->permission = 1;

        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$permissionObject])
        ;

        $this->assertEquals([$permissionObject], $this->permissionViewRepository->getTaskList(null));
    }

    public function testGetTaskListEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT DISTINCT `module_name` AS `module`, `task_name` AS `task` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `permission`>? AND `task_id` IS NOT NULL',
            [0, 0, 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(0)
        ;
        $permissionObject = new stdClass();
        $permissionObject->module = 'galaxy';
        $permissionObject->permission = 1;

        $this->expectException(SelectError::class);
        $this->permissionViewRepository->getTaskList(null);
    }

    public function testGetTaskListWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT DISTINCT `module_name` AS `module`, `task_name` AS `task` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `permission`>? AND `task_id` IS NOT NULL',
            [42, 42, 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $permissionObject = new stdClass();
        $permissionObject->module = 'galaxy';
        $permissionObject->permission = 1;

        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$permissionObject])
        ;

        $this->assertEquals([$permissionObject], $this->permissionViewRepository->getTaskList(42));
    }

    public function testGetTaskListWithModule(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT DISTINCT `module_name` AS `module`, `task_name` AS `task` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `permission`>? AND `task_id` IS NOT NULL AND `module_name`=?',
            [0, 0, 1, 'galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $permissionObject = new stdClass();
        $permissionObject->module = 'galaxy';
        $permissionObject->permission = 1;

        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$permissionObject])
        ;

        $this->assertEquals([$permissionObject], $this->permissionViewRepository->getTaskList(null, 'galaxy'));
    }

    public function testGetTaskListWithUserIdAndModule(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT DISTINCT `module_name` AS `module`, `task_name` AS `task` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `permission`>? AND `task_id` IS NOT NULL AND `module_name`=?',
            [42, 42, 1, 'galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $permissionObject = new stdClass();
        $permissionObject->module = 'galaxy';
        $permissionObject->permission = 1;

        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$permissionObject])
        ;

        $this->assertEquals([$permissionObject], $this->permissionViewRepository->getTaskList(42, 'galaxy'));
    }

    public function testGetPermissionByModule(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name` IS NULL AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_module_id` DESC LIMIT 1',
            [0, 0, 'galaxy'],
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

        $permission = $this->permissionViewRepository->getPermissionByModule('galaxy');

        $this->assertEquals(4, $permission->getPermission());
    }

    public function testGetPermissionByModuleWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name` IS NULL AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionViewRepository->getPermissionByModule('galaxy', 42);

        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetPermissionByModuleEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name` IS NULL AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(0)
        ;

        $this->expectException(SelectError::class);
        $this->permissionViewRepository->getPermissionByModule('galaxy', 42);
    }

    public function testGetPermissionByTask(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [0, 0, 'galaxy', 'ford'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'module' => 'galaxy',
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionViewRepository->getPermissionByTask('galaxy', 'ford');

        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetPermissionByTaskWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy', 'ford'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'module' => 'galaxy',
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionViewRepository->getPermissionByTask('galaxy', 'ford', 42);

        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetPermissionByTaskEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name` IS NULL ORDER BY `user_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy', 'ford'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(0)
        ;

        $this->expectException(SelectError::class);
        $this->permissionViewRepository->getPermissionByTask('galaxy', 'ford', 42);
    }

    public function testGetPermissionByAction(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name`=? AND `action_method`=? ORDER BY `user_id` DESC, `permission_action_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [0, 0, 'galaxy', 'ford', 'prefect', 'HEAD'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'module' => 'galaxy',
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionViewRepository->getPermissionByAction(
            'galaxy',
            'ford',
            'prefect',
            HttpMethod::HEAD,
        );

        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetPermissionByActionWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name`=? AND `action_method`=? ORDER BY `user_id` DESC, `permission_action_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy', 'ford', 'prefect', 'HEAD'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'module' => 'galaxy',
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionViewRepository->getPermissionByAction(
            'galaxy',
            'ford',
            'prefect',
            HttpMethod::HEAD,
            42
        );

        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetPermissionByActionEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `view_user_permission`.`permission` FROM `marvin`.`view_user_permission` WHERE IFNULL(`user_id`, ?)=? AND `module_name`=? AND `task_name`=? AND `action_name`=? AND `action_method`=? ORDER BY `user_id` DESC, `permission_action_id` DESC, `permission_task_id` DESC, `permission_module_id` DESC LIMIT 1',
            [42, 42, 'galaxy', 'ford', 'prefect', 'HEAD'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(0)
        ;

        $this->expectException(SelectError::class);
        $this->permissionViewRepository->getPermissionByAction(
            'galaxy',
            'ford',
            'prefect',
            HttpMethod::HEAD,
            42
        );
    }
}
