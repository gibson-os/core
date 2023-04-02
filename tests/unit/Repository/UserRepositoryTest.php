<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class UserRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private UserRepository $userRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`user`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['user', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->userRepository = new UserRepository('user');
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user`.`user` FROM `marvin`.`user` WHERE `id`=? LIMIT 1',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user' => 'marvin',
            ]])
        ;

        $user = $this->userRepository->getById(42);

        $this->assertEquals('marvin', $user->getUser());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user`.`user` FROM `marvin`.`user` WHERE `user` LIKE ?',
            ['galaxy%'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user' => 'marvin',
            ]])
        ;

        $user = $this->userRepository->findByName('galaxy')[0];

        $this->assertEquals('marvin', $user->getUser());
    }

    public function testGetByUsername(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user`.`user` FROM `marvin`.`user` WHERE `user`=? LIMIT 1',
            ['galaxy'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user' => 'marvin',
            ]])
        ;

        $user = $this->userRepository->getByUsername('galaxy');

        $this->assertEquals('marvin', $user->getUser());
    }

    public function testGetByUsernameAndPassword(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `user`.`user` FROM `marvin`.`user` WHERE `user`=? AND `password`=MD5(?) LIMIT 1',
            ['galaxy', 'no hope'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'user' => 'marvin',
            ]])
        ;

        $user = $this->userRepository->getByUsernameAndPassword('galaxy', 'no hope');

        $this->assertEquals('marvin', $user->getUser());
    }

    public function testGetCount(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT COUNT(`id`) FROM `marvin`.`user`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['user', 'varchar(42)', 'NO', '', null, ''],
                null,
                [42],
            )
        ;

        $this->assertEquals(42, $this->userRepository->getCount());
    }
}
