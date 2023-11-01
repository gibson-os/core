<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\ActionRepository;
use MDO\Dto\Query\Where;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;

class ActionRepositoryTest extends Unit
{
    use RepositoryTrait;

    private ActionRepository $actionRepository;

    protected function _before()
    {
        $this->loadRepository('action');

        $this->actionRepository = new ActionRepository($this->repositoryWrapper->reveal(), 'action');
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Action::class),
            $this->actionRepository->getById(42),
        );
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name` LIKE ?', ['galaxy%']))
        ;
        $this->assertEquals(
            $this->loadModel($selectQuery, Action::class, ''),
            $this->actionRepository->findByName('galaxy')[0],
        );
    }

    public function testFindByNameWithTaskId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name` LIKE ? AND `task_id`=?', ['galaxy%', 42]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Action::class, ''),
            $this->actionRepository->findByName('galaxy', 42)[0],
        );
    }

    public function testGetByNameAndTaskId(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name`=? AND `method`=? AND `task_id`=?', ['galaxy', 'GET', 42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Action::class),
            $this->actionRepository->getByNameAndTaskId('galaxy', HttpMethod::GET, 42),
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

        $this->assertTrue($this->actionRepository->deleteByIdsNot([42]));
    }
}
