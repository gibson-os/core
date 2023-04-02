<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class SettingRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private SettingRepository $settingRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`setting`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['key', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->settingRepository = new SettingRepository('setting');
    }

    public function testGetAll(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` WHERE `module_id`=? AND (`user_id` IS NULL OR `user_id`=?)',
            [42, 24],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getAll(42, 24)[0];

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetAllUserIdEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` WHERE `module_id`=? AND (`user_id` IS NULL)',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getAll(42, null)[0];

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetAllByModuleName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` JOIN module ON `setting`.`module_id`=`module`.`id` WHERE `module`.`name`=? AND (`user_id` IS NULL OR `user_id`=?)',
            ['marvin', 42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getAllByModuleName('marvin', 42)[0];

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetAllByModuleNameEmptyUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` JOIN module ON `setting`.`module_id`=`module`.`id` WHERE `module`.`name`=? AND (`user_id` IS NULL)',
            ['marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getAllByModuleName('marvin', null)[0];

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetByKey(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` WHERE `module_id`=? AND (`user_id` IS NULL OR `user_id`=?) AND `key`=? LIMIT 1',
            [42, 24, 'marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getByKey(42, 24, 'marvin');

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetByKeyEmptyUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` WHERE `module_id`=? AND (`user_id` IS NULL) AND `key`=? LIMIT 1',
            [42, 'marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getByKey(42, null, 'marvin');

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetByKeyAndModuleName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` JOIN module ON `setting`.`module_id`=`module`.`id` WHERE `module`.`name`=? AND (`setting`.`user_id` IS NULL OR `setting`.`user_id`=?) AND . `setting`.`key`=? ORDER BY `user_id` DESC LIMIT 1',
            ['galaxy', 42, 'marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getByKeyAndModuleName('galaxy', 42, 'marvin');

        $this->assertEquals('galaxy', $setting->getKey());
    }

    public function testGetByKeyAndModuleNameError(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` JOIN module ON `setting`.`module_id`=`module`.`id` WHERE `module`.`name`=? AND (`setting`.`user_id` IS NULL OR `setting`.`user_id`=?) AND . `setting`.`key`=? ORDER BY `user_id` DESC LIMIT 1',
            ['galaxy', 42, 'marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(SelectError::class);
        $this->settingRepository->getByKeyAndModuleName('galaxy', 42, 'marvin');
    }

    public function testGetByKeyAndModuleNameEmptyUserId(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `setting`.`key` FROM `marvin`.`setting` JOIN module ON `setting`.`module_id`=`module`.`id` WHERE `module`.`name`=? AND (`setting`.`user_id` IS NULL) AND . `setting`.`key`=? ORDER BY `user_id` DESC LIMIT 1',
            ['galaxy', 'marvin'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'key' => 'galaxy',
            ]])
        ;

        $setting = $this->settingRepository->getByKeyAndModuleName('galaxy', null, 'marvin');

        $this->assertEquals('galaxy', $setting->getKey());
    }
}
