<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Manager;

use Codeception\Test\Unit;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Mock\Model\MockModel;
use mysqlDatabase;
use mysqlRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ModelManagerTest extends Unit
{
    use ProphecyTrait;

    private ModelManager $modelManager;

    private mysqlDatabase|ObjectProphecy $mysqlDatabase;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private JsonUtility|ObjectProphecy $jsonUtility;

    protected function _before()
    {
        $this->mysqlDatabase = $this->prophesize(mysqlDatabase::class);
        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->jsonUtility = $this->prophesize(JsonUtility::class);
        $reflectionManager = new ReflectionManager();

        mysqlRegistry::getInstance()->reset();
        mysqlRegistry::getInstance()->set('database', $this->mysqlDatabase->reveal());

        $this->mysqlDatabase->getDatabaseName()
            ->willReturn('galaxy')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `galaxy`.`marvin`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['id', 'bigint(20) unsigned', 'NO', 'PRI', null, 'auto_increment'],
                ['parent_id', 'bigint(20) unsigned', 'YES', 'MUL', null, ''],
                null,
            )
        ;

        $this->modelManager = new ModelManager(
            $this->mysqlDatabase->reveal(),
            $this->dateTimeService->reveal(),
            $this->jsonUtility->reveal(),
            $reflectionManager,
            new TableNameAttribute($reflectionManager),
        );
    }

    public function testSaveWithoutChildren(): void
    {
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([['id' => 42]])
        ;

        $model = new MockModel($this->mysqlDatabase->reveal());
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithoutChildrenWithSetChildren(): void
    {
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([['id' => 42]])
        ;

        $children = new MockModel($this->mysqlDatabase->reveal());
        $model = (new MockModel($this->mysqlDatabase->reveal()))
            ->setChildren([$children])
        ;
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
        $this->assertEquals(42, $children->getParentId());
        $this->assertEquals($model, $children->getParent());
    }

    public function testSaveWithoutChildrenWithAddChildren(): void
    {
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([['id' => 42]])
        ;

        $children = new MockModel($this->mysqlDatabase->reveal());
        $model = (new MockModel($this->mysqlDatabase->reveal()))
            ->addChildren([$children])
        ;
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
        $this->assertEquals(42, $children->getParentId());
        $this->assertEquals($model, $children->getParent());
    }

    public function testSave(): void
    {
        $this->mysqlDatabase->isTransaction()
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->mysqlDatabase->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([['id' => 42]])
        ;
        $this->mysqlDatabase->execute(
            'DELETE `marvin` FROM `galaxy`.`marvin` WHERE (`parent_id`=?) ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->commit()
            ->shouldBeCalledOnce()
        ;

        $model = new MockModel($this->mysqlDatabase->reveal());
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithSetChildren(): void
    {
        $this->mysqlDatabase->isTransaction()
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->mysqlDatabase->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledTimes(2)
            ->willReturn([['id' => 42]], [['id' => 24]])
        ;
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=? ON DUPLICATE KEY UPDATE `parent_id`=?',
            [42, 42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id`=?',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'DELETE `marvin` FROM `galaxy`.`marvin` WHERE (`parent_id`=?) ',
            [24],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'DELETE `marvin` FROM `galaxy`.`marvin` WHERE (`parent_id`=?) AND ((`id`!=?)) ',
            [42, 24],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->commit()
            ->shouldBeCalledOnce()
        ;

        $children = new MockModel($this->mysqlDatabase->reveal());
        $model = (new MockModel($this->mysqlDatabase->reveal()))
            ->setChildren([$children])
        ;
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithAddChildren(): void
    {
        $this->mysqlDatabase->isTransaction()
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->mysqlDatabase->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=NULL ON DUPLICATE KEY UPDATE `parent_id`=NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id` IS NULL',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledTimes(2)
            ->willReturn([['id' => 42]], [['id' => 24]])
        ;
        $this->mysqlDatabase->execute(
            'INSERT INTO `galaxy`.`marvin` SET `parent_id`=? ON DUPLICATE KEY UPDATE `parent_id`=?',
            [42, 42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `marvin`.`id`, `marvin`.`parent_id` FROM `galaxy`.`marvin` WHERE `parent_id`=?',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'DELETE `marvin` FROM `galaxy`.`marvin` WHERE (`parent_id`=?) ',
            [24],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'DELETE `marvin` FROM `galaxy`.`marvin` WHERE (`parent_id`=?) AND ((`id`!=?)) ',
            [42, 24],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->commit()
            ->shouldBeCalledOnce()
        ;

        $children = new MockModel($this->mysqlDatabase->reveal());
        $model = (new MockModel($this->mysqlDatabase->reveal()))
            ->addChildren([$children])
        ;
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }
}
