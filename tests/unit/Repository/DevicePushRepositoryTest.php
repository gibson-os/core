<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\DevicePushRepository;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class DevicePushRepositoryTest extends Unit
{
    use RepositoryTrait;

    private DevicePushRepository $devicePushRepository;

    protected function _before()
    {
        $this->loadRepository('device_push');

        $this->devicePushRepository = new DevicePushRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByDevice(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where(
                '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=? AND `device_id`=?',
                ['marvin', 'arthur', 'dent', 'no hope', 'galaxy'],
            ))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, DevicePush::class);
        $devicePush = $this->devicePushRepository->getByDevice(
            (new Device($this->modelWrapper->reveal()))->setId('galaxy'),
            'marvin',
            'arthur',
            'dent',
            'no hope',
        );

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $devicePush->setModified($date);

        $this->assertEquals($model, $devicePush);
    }

    public function testGetByAction(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where(
                '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=?',
                ['marvin', 'arthur', 'dent', 'no hope'],
            ))
        ;

        $model = $this->loadModel($selectQuery, DevicePush::class);
        $devicePush = $this->devicePushRepository->getAllByAction(
            'marvin',
            'arthur',
            'dent',
            'no hope',
        )[0];

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $devicePush->setModified($date);

        $this->assertEquals($model, $devicePush);
    }
}
