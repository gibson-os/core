<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\DevicePushRepository;
use MDO\Dto\Field;
use MDO\Dto\Query\Where;
use MDO\Enum\Type;
use MDO\Query\SelectQuery;

class DevicePushRepositoryTest extends Unit
{
    use RepositoryTrait;

    private DevicePushRepository $devicePushRepository;

    protected function _before()
    {
        $this->loadRepository(
            'device_push',
            [
                new Field('module', false, Type::VARCHAR, '', null, '', 42),
                new Field('task', false, Type::VARCHAR, '', null, '', 42),
            ],
        );

        $this->devicePushRepository = new DevicePushRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByDevice(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=? AND `device_id`=?',
                ['marvin', 'arthur', 'dent', 'no hope', 'galaxy'],
            ))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, DevicePush::class),
            $this->devicePushRepository->getByDevice(
                (new Device($this->modelWrapper->reveal()))->setId('galaxy'),
                'marvin',
                'arthur',
                'dent',
                'no hope',
            ),
        );
    }

    public function testGetByAction(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `device_push`.`module`, `device_push`.`task` FROM `marvin`.`device_push` WHERE `module`=? AND `task`=? AND `action`=? AND `foreign_id`=?',
            ['marvin', 'arthur', 'dent', 'no hope'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'module' => 'ford',
                'task' => 'prefect',
            ]])
        ;

        $devicePush = $this->devicePushRepository->getAllByAction(
            'marvin',
            'arthur',
            'dent',
            'no hope',
        )[0];

        $this->assertEquals('ford', $devicePush->getModule());
        $this->assertEquals('prefect', $devicePush->getTask());
    }
}
