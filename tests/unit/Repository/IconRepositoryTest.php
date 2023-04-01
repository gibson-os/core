<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\IconRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class IconRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private IconRepository $iconRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`icon`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['name', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->iconRepository = new IconRepository('icon');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `icon`.`name` FROM `marvin`.`icon` WHERE `id`=? LIMIT 1',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $icon = $this->iconRepository->getById(42);

        $this->assertEquals('marvin', $icon->getName());
    }

    public function testFindByIds(): void
    {
        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledTimes(2)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `icon`.`name` FROM `marvin`.`icon` WHERE `id` IN (?)',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $icon = $this->iconRepository->findByIds([42])[0];

        $this->assertEquals('marvin', $icon->getName());
    }

    public function testDeleteByIds(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `icon` FROM `marvin`.`icon` WHERE `id` IN (?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->iconRepository->deleteByIds([42]));
    }
}
