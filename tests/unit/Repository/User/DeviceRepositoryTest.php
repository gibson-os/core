<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\User;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class DeviceRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private DeviceRepository $deviceRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`user_device`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['user_id', 'bigint(42)', 'NO', '', null, ''],
                ['model', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->deviceRepository = new DeviceRepository('user_device');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_device`.`user_id`, `user_device`.`model` FROM `marvin`.`user_device` WHERE `id`=? LIMIT 1',
            ['42'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user_id' => '21',
                'model' => 'galaxy',
            ]])
        ;

        $device = $this->deviceRepository->getById('42');

        $this->assertEquals(21, $device->getUserId());
        $this->assertEquals('galaxy', $device->getModel());
    }

    public function testGetByToken(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_device`.`user_id`, `user_device`.`model` FROM `marvin`.`user_device` WHERE `token`=? LIMIT 1',
            ['marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user_id' => '21',
                'model' => 'galaxy',
            ]])
        ;

        $device = $this->deviceRepository->getByToken('marvin');

        $this->assertEquals(21, $device->getUserId());
        $this->assertEquals('galaxy', $device->getModel());
    }

    public function testFindByUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user_device`.`user_id`, `user_device`.`model` FROM `marvin`.`user_device` WHERE `user_id`=?',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user_id' => '21',
                'model' => 'galaxy',
            ]])
        ;

        $device = $this->deviceRepository->findByUserId(42)[0];

        $this->assertEquals(21, $device->getUserId());
        $this->assertEquals('galaxy', $device->getModel());
    }

    public function testDeleteByIds(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `user_device` FROM `marvin`.`user_device` WHERE `id` IN (?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->deviceRepository->deleteByIds([42]);
    }

    public function testDeleteByIdsWithUserId(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `user_device` FROM `marvin`.`user_device` WHERE `id` IN (?) AND `user_id`=? ',
            [42, 21],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->deviceRepository->deleteByIds([42], 21);
    }

    public function testRemoveFcmToken(): void
    {
        $this->mysqlDatabase->execute(
            'UPDATE `marvin`.`user_device` SET `fcm_token`=NULL WHERE `fcm_token`=? ',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->deviceRepository->removeFcmToken('galaxy');
    }
}
