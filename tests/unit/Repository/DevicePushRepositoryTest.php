<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\DevicePushRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class DevicePushRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private DevicePushRepository $devicePushRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->devicePushRepository = new DevicePushRepository();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`device_push`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['module', 'varchar(42)', 'NO', '', null, ''],
                ['task', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;
    }

    public function testGetByDevice(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `device_push`.`module`, `device_push`.`task` FROM `marvin`.`device_push` WHERE `module`=? AND `task`=? AND `action`=? AND `foreign_id`=? AND `device_id`=? LIMIT 1',
            ['marvin', 'arthur', 'dent', 'no hope', 'galaxy'],
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

        $devicePush = $this->devicePushRepository->getByDevice(
            (new Device())->setId('galaxy'),
            'marvin',
            'arthur',
            'dent',
            'no hope',
        );

        $this->assertEquals('ford', $devicePush->getModule());
        $this->assertEquals('prefect', $devicePush->getTask());
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
