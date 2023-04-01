<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\DriveRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class DriveRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private DriveRepository $driveRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`drive`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['serial', 'varchar(42)', 'NO', '', null, ''],
                ['model', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->driveRepository = new DriveRepository('drive', 'drive_stat');
    }

    public function testGetDrivesWithAttributes(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `drive`.`serial`, `drive`.`model` FROM `marvin`.`drive` JOIN drive_stat ON `system_drive_stat`.`drive_id`=`system_drive`.`id` WHERE UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
            [900],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'serial' => 'ford',
                'model' => 'prefect',
            ]])
        ;

        $drive = $this->driveRepository->getDrivesWithAttributes()[0];

        $this->assertEquals('ford', $drive->getSerial());
        $this->assertEquals('prefect', $drive->getModel());
    }

    public function testGetDrivesWithAttributesChangedSeconds(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `drive`.`serial`, `drive`.`model` FROM `marvin`.`drive` JOIN drive_stat ON `system_drive_stat`.`drive_id`=`system_drive`.`id` WHERE UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'serial' => 'ford',
                'model' => 'prefect',
            ]])
        ;

        $drive = $this->driveRepository->getDrivesWithAttributes(42)[0];

        $this->assertEquals('ford', $drive->getSerial());
        $this->assertEquals('prefect', $drive->getModel());
    }
}
