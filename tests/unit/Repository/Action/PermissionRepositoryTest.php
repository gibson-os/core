<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Action;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\Action\PermissionRepository;
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
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`action_permission`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['action_id', 'bigint(42)', 'NO', '', null, ''],
                ['permission', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->permissionRepository = new PermissionRepository('action_permission');
    }

    public function testFindByActionId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `action_permission`.`action_id`, `action_permission`.`permission` FROM `marvin`.`action_permission` WHERE `action_id`=?',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'action_id' => '42',
                'permission' => '1',
            ]])
        ;

        $permission = $this->permissionRepository->findByActionId(42)[0];

        $this->assertEquals(42, $permission->getActionId());
        $this->assertEquals(1, $permission->getPermission());
    }

    public function testDeleteByAction(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `action_permission` FROM `marvin`.`action_permission` WHERE `action_id`=? ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->permissionRepository->deleteByAction(
            (new Action($this->mysqlDatabase->reveal()))->setId(42)
        ));
    }
}
