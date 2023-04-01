<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\RoleRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class RoleRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private RoleRepository $roleRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`role`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['name', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->roleRepository = new RoleRepository();
    }

    public function testGetByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `role`.`name` FROM `marvin`.`role` WHERE `name`=? LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $role = $this->roleRepository->getByName('galaxy');

        $this->assertEquals('marvin', $role->getName());
    }
}
