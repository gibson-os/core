<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class ModuleRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private ModuleRepository $moduleRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`module`')
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

        $this->moduleRepository = new ModuleRepository('module');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `module`.`name` FROM `marvin`.`module` WHERE `id`=? LIMIT 1',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'galaxy',
            ]])
        ;

        $module = $this->moduleRepository->getById(42);

        $this->assertEquals('galaxy', $module->getName());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `module`.`name` FROM `marvin`.`module` WHERE `name` LIKE ?',
            ['galaxy%'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'galaxy',
            ]])
        ;

        $module = $this->moduleRepository->findByName('galaxy')[0];

        $this->assertEquals('galaxy', $module->getName());
    }

    public function testGetByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `module`.`name` FROM `marvin`.`module` WHERE `name`=? LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'galaxy',
            ]])
        ;

        $module = $this->moduleRepository->getByName('galaxy');

        $this->assertEquals('galaxy', $module->getName());
    }

    public function testGetAll(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `module`.`name` FROM `marvin`.`module`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'galaxy',
            ]])
        ;

        $module = $this->moduleRepository->getAll()[0];

        $this->assertEquals('galaxy', $module->getName());
    }

    public function testDeleteByIdsNot(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `module` FROM `marvin`.`module` WHERE `id` NOT IN (?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->moduleRepository->deleteByIdsNot([42]));
    }
}
