<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\TaskRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class TaskRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private TaskRepository $taskRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`task`')
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

        $this->taskRepository = new TaskRepository('task');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `task`.`name` FROM `marvin`.`task` WHERE `id`=? LIMIT 1',
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

        $task = $this->taskRepository->getById(42);

        $this->assertEquals('galaxy', $task->getName());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `task`.`name` FROM `marvin`.`task` WHERE `name` LIKE ?',
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

        $task = $this->taskRepository->findByName('galaxy')[0];

        $this->assertEquals('galaxy', $task->getName());
    }

    public function testFindByNameWithModuleId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `task`.`name` FROM `marvin`.`task` WHERE `name` LIKE ? AND `module_id`=?',
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

        $task = $this->taskRepository->findByName('galaxy', 42)[0];

        $this->assertEquals('galaxy', $task->getName());
    }

    public function testGetByNameAndModuleId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `task`.`name` FROM `marvin`.`task` WHERE `name`=? AND `module_id`=? LIMIT 1',
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

        $task = $this->taskRepository->getByNameAndModuleId('galaxy', 42);

        $this->assertEquals('galaxy', $task->getName());
    }

    public function testDeleteByIdsNot(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `task` FROM `marvin`.`task` WHERE `id` NOT IN (?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->taskRepository->deleteByIdsNot([42]));
    }
}
