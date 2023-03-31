<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
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
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['module', 'varchar(42)', 'NO', '', null, ''],
                ['permission', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->permissionRepository = new PermissionRepository();
    }

    public function testGetByModuleTaskAndAction(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`module`, `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module`=? AND `task`=? AND `action`=? AND IFNULL(`user_id`, ?)=? LIMIT 1',
            ['marvin', 'arthur', 'dent', 0, 0],
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

        $permission = $this->permissionRepository->getByModuleTaskAndAction('marvin', 'arthur', 'dent');

        $this->assertEquals('galaxy', $permission->getModule());
        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetByModuleTaskAndActionWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`module`, `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module`=? AND `task`=? AND `action`=? AND IFNULL(`user_id`, ?)=? LIMIT 1',
            ['marvin', 'arthur', 'dent', 0, 42],
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

        $permission = $this->permissionRepository->getByModuleTaskAndAction('marvin', 'arthur', 'dent', 42);

        $this->assertEquals('galaxy', $permission->getModule());
        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetByModuleAndTask(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`module`, `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module`=? AND `task`=? AND `action` IS NULL AND IFNULL(`user_id`, ?)=? LIMIT 1',
            ['marvin', 'arthur', 0, 0],
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

        $permission = $this->permissionRepository->getByModuleAndTask('marvin', 'arthur');

        $this->assertEquals('galaxy', $permission->getModule());
        $this->assertEquals(1, $permission->getPermission());
    }

    public function testGetByModuleAndTaskWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_permission`.`module`, `user_permission`.`permission` FROM `marvin`.`user_permission` WHERE `module`=? AND `task`=? AND `action` IS NULL AND IFNULL(`user_id`, ?)=? LIMIT 1',
            ['marvin', 'arthur', 0, 42],
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

        $permission = $this->permissionRepository->getByModuleAndTask('marvin', 'arthur', 42);

        $this->assertEquals('galaxy', $permission->getModule());
        $this->assertEquals(1, $permission->getPermission());
    }
}
