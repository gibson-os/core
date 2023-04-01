<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\SmartAttributeRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class SmartAttributeRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private SmartAttributeRepository $smartAttributeRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`system_smart_attribute`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['short', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->smartAttributeRepository = new SmartAttributeRepository();
    }

    public function testGetAll(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `system_smart_attribute`.`short` FROM `marvin`.`system_smart_attribute`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'short' => 'galaxy',
            ]])
        ;

        $smartAttribute = $this->smartAttributeRepository->getAll()[0];

        $this->assertEquals('galaxy', $smartAttribute->getShort());
    }
}
