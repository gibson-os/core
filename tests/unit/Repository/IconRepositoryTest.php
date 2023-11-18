<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Repository\IconRepository;
use MDO\Dto\Query\Where;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;

class IconRepositoryTest extends Unit
{
    use RepositoryTrait;

    private IconRepository $iconRepository;

    protected function _before()
    {
        $this->loadRepository('icon');

        $this->iconRepository = new IconRepository($this->repositoryWrapper->reveal(), 'icon');
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Icon::class);
        $icon = $this->iconRepository->getById(42);

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $icon->setAdded($date);

        $this->assertEquals($model, $icon);
    }

    public function testFindByIds(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $selectService = $this->prophesize(SelectService::class);
        $selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $this->repositoryWrapper->getSelectService()
            ->shouldBeCalledOnce()
            ->willReturn($selectService->reveal())
        ;

        $model = $this->loadModel($selectQuery, Icon::class);
        $icon = $this->iconRepository->findByIds([42])[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $icon->setAdded($date);

        $this->assertEquals($model, $icon);
    }

    public function testDeleteByIds(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $this->loadDeleteQuery($deleteQuery);
        $selectService = $this->prophesize(SelectService::class);
        $selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $this->repositoryWrapper->getSelectService()
            ->shouldBeCalledOnce()
            ->willReturn($selectService->reveal())
        ;

        $this->assertTrue($this->iconRepository->deleteByIds([42]));
    }
}
