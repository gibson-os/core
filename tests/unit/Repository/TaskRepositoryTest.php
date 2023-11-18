<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\TaskRepository;
use MDO\Dto\Query\Where;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;

class TaskRepositoryTest extends Unit
{
    use RepositoryTrait;

    private TaskRepository $taskRepository;

    protected function _before()
    {
        $this->loadRepository('task');

        $this->taskRepository = new TaskRepository($this->repositoryWrapper->reveal(), 'task');
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Task::class),
            $this->taskRepository->getById(42),
        );
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name` LIKE ?', ['galaxy%']))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Task::class),
            $this->taskRepository->findByName('galaxy')[0],
        );
    }

    public function testFindByNameWithModuleId(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name` LIKE ? AND `module_id`=?', ['galaxy%', 42]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Task::class),
            $this->taskRepository->findByName('galaxy', 42)[0],
        );
    }

    public function testGetByNameAndModuleId(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name`=? AND `module_id`=?', ['galaxy', 42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Task::class),
            $this->taskRepository->getByNameAndModuleId('galaxy', 42),
        );
    }

    public function testDeleteByIdsNot(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`id` NOT IN (?)', [42]))
        ;
        $this->loadDeleteQuery($deleteQuery);
        $selectService = $this->prophesize(SelectService::class);
        $selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $this->repositoryWrapper->getSelectService()
            ->shouldBeCalledOnce()
            ->willReturn($selectService)
        ;

        $this->assertTrue($this->taskRepository->deleteByIdsNot([42]));
    }
}
