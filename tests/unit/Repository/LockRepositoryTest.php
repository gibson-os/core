<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\LockRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class LockRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private LockRepository $lockRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`lock`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['pid', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->lockRepository = new LockRepository();
    }

    public function testGetByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `lock`.`pid` FROM `marvin`.`lock` WHERE `name`=? LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'pid' => '42',
            ]])
        ;

        $lock = $this->lockRepository->getByName('galaxy');

        $this->assertEquals(42, $lock->getPid());
    }
}
