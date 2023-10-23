<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Desktop;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\Desktop\ItemRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Dto\Result;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\OrderDirection;
use MDO\Enum\ValueType;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Query\UpdateQuery;
use MDO\Service\SelectService;

class ItemRepositoryTest extends Unit
{
    use RepositoryTrait;

    private ItemRepository $itemRepository;

    protected function _before()
    {
        $this->loadRepository('desktop_item');

        $this->itemRepository = new ItemRepository($this->repositoryWrapper->reveal(), 'desktop_item');
    }

    public function testDeleteIdsNotIn(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`id` NOT IN (?)', [42]))
            ->addWhere(new Where('`user_id`=?', [0]))
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

        $this->assertTrue($this->itemRepository->deleteByIdsNot(new User($this->modelWrapper->reveal()), [42]));
    }

    public function testGetLastPosition(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user_id`=?', [0]))
            ->setOrder('`position`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Item::class),
            $this->itemRepository->getLastPosition(new User($this->modelWrapper->reveal())),
        );
    }

    public function testUpdatePosition(): void
    {
        $updateQuery = (new UpdateQuery($this->table, ['position' => new Value('`position`+2', ValueType::FUNCTION)]))
            ->addWhere(new Where('`user_id`=?', [0]))
            ->addWhere(new Where('`position`>=?', [1]))
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable('desktop_item')
            ->shouldBeCalledOnce()
            ->willReturn(new Table('desktop_item', []))
        ;
        $this->client->execute($updateQuery)
            ->shouldBeCalledOnce()
            ->willReturn(new Result(null))
        ;

        $this->itemRepository->updatePosition(new User($this->modelWrapper->reveal()), 1, 2);
    }

    public function testGetByUser(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user_id`=?', [0]))
            ->setOrder('`position`')
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Item::class, ''),
            $this->itemRepository->getByUser(new User($this->modelWrapper->reveal()))[0],
        );
    }
}
