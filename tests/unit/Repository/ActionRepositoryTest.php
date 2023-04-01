<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class ActionRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private ActionRepository $actionRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`action`')
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

        $this->actionRepository = new ActionRepository('action');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `action`.`name` FROM `marvin`.`action` WHERE `id`=? LIMIT 1',
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

        $action = $this->actionRepository->getById(42);

        $this->assertEquals('galaxy', $action->getName());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `action`.`name` FROM `marvin`.`action` WHERE `name` LIKE ?',
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

        $action = $this->actionRepository->findByName('galaxy')[0];

        $this->assertEquals('galaxy', $action->getName());
    }

    public function testFindByNameWithTaskId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `action`.`name` FROM `marvin`.`action` WHERE `name` LIKE ? AND `task_id`=?',
            ['galaxy%', 42],
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

        $action = $this->actionRepository->findByName('galaxy', 42)[0];

        $this->assertEquals('galaxy', $action->getName());
    }

    public function testGetByNameAndTaskId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `action`.`name` FROM `marvin`.`action` WHERE `name`=? AND `task_id`=? LIMIT 1',
            ['galaxy', 42],
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

        $action = $this->actionRepository->getByNameAndTaskId('galaxy', 42);

        $this->assertEquals('galaxy', $action->getName());
    }

    public function testDeleteByIdsNot(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `action` FROM `marvin`.`action` WHERE `id` NOT IN (?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->actionRepository->deleteByIdsNot([42]));
    }
}
