<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Dto\Result;
use MDO\Dto\Value;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Query\UpdateQuery;
use MDO\Service\SelectService;

class DeviceRepositoryTest extends Unit
{
    use RepositoryTrait;

    private DeviceRepository $deviceRepository;

    protected function _before()
    {
        $this->loadRepository('user_device');

        $this->deviceRepository = new DeviceRepository($this->repositoryWrapper->reveal(), 'user_device');
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', ['42']))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Device::class);
        $device = $this->deviceRepository->getById('42');

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $device->setAdded($date);

        $this->assertEquals($model, $device);
    }

    public function testGetByToken(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`token`=?', ['marvin']))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Device::class);
        $device = $this->deviceRepository->getByToken('marvin');

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $device->setAdded($date);

        $this->assertEquals($model, $device);
    }

    public function testFindByUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user_id`=?', [42]))
        ;

        $model = $this->loadModel($selectQuery, Device::class, '');
        $device = $this->deviceRepository->findByUserId(42)[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $device->setAdded($date);

        $this->assertEquals($model, $device);
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
            ->willReturn($selectService)
        ;

        $this->assertTrue($this->deviceRepository->deleteByIds([42]));
    }

    public function testDeleteByIdsWithUserId(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`id` IN (?)', [42]))
            ->addWhere(new Where('`user_id`=?', [21]))
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

        $this->assertTrue($this->deviceRepository->deleteByIds([42], 21));
    }

    public function testRemoveFcmToken(): void
    {
        $updateQuery = (new UpdateQuery($this->table, ['fcm_token' => new Value(null)]))
            ->addWhere(new Where('`fcm_token`=?', ['galaxy']))
        ;
        $this->client->execute($updateQuery)
            ->shouldBeCalledOnce()
            ->willReturn(new Result(null))
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertTrue($this->deviceRepository->removeFcmToken('galaxy'));
    }
}
