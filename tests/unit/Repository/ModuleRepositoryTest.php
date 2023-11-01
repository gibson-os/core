<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Repository\ModuleRepository;
use MDO\Dto\Query\Where;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;

class ModuleRepositoryTest extends Unit
{
    use RepositoryTrait;

    private ModuleRepository $moduleRepository;

    protected function _before()
    {
        $this->loadRepository('module');

        $this->moduleRepository = new ModuleRepository($this->repositoryWrapper->reveal(), 'module');
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Module::class),
            $this->moduleRepository->getById(42),
        );
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name` LIKE ?', ['galaxy%']))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Module::class, ''),
            $this->moduleRepository->findByName('galaxy')[0],
        );
    }

    public function testGetByName(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name`=?', ['galaxy']))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Module::class),
            $this->moduleRepository->getByName('galaxy'),
        );
    }

    public function testGetAll(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('1', []))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Module::class, ''),
            $this->moduleRepository->getAll()[0],
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

        $this->assertTrue($this->moduleRepository->deleteByIdsNot([42]));
    }
}
