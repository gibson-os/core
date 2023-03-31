<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Desktop;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\Desktop\ItemRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class ItemRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private ItemRepository $itemRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`desktop_item`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['text', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->itemRepository = new ItemRepository('desktop_item');
    }

    public function testDeleteIdsNotIn(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `desktop_item` FROM `marvin`.`desktop_item` WHERE `id` NOT IN (?) AND `user_id`=? ',
            [42, 0],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->itemRepository->deleteByIdsNot(
            new User(),
            [42],
        ));
    }

    public function testGetLastPosition(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `desktop_item`.`text` FROM `marvin`.`desktop_item` WHERE `user_id`=? ORDER BY `position` DESC LIMIT 1',
            [0],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'text' => 'galaxy',
            ]])
        ;

        $this->assertEquals(
            'galaxy',
            $this->itemRepository->getLastPosition(new User($this->mysqlDatabase->reveal()))->getText(),
        );
    }

    public function testUpdatePosition(): void
    {
        $this->mysqlDatabase->execute(
            'UPDATE `marvin`.`desktop_item` SET `position`=`position`+? WHERE `user_id`=? AND `position`>=? ',
            [2, 0, 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->itemRepository->updatePosition(new User(), 1, 2);
    }

    public function testGetByUser(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `desktop_item`.`text` FROM `marvin`.`desktop_item` WHERE `user_id`=? ORDER BY `position` ASC',
            [0],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'text' => 'galaxy',
            ]])
        ;

        $this->assertEquals(
            'galaxy',
            $this->itemRepository->getByUser(new User($this->mysqlDatabase->reveal()))[0]->getText(),
        );
    }
}
