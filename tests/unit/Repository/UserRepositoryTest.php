<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Query\SelectQuery;

class UserRepositoryTest extends Unit
{
    use RepositoryTrait;

    private UserRepository $userRepository;

    protected function _before()
    {
        $this->loadRepository('user');

        $this->userRepository = new UserRepository($this->repositoryWrapper->reveal());
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, User::class);
        $user = $this->userRepository->getById(42);

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $user->setAdded($date);

        $this->assertEquals($model, $user);
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user` LIKE ?', ['galaxy%']))
        ;

        $model = $this->loadModel($selectQuery, User::class, '');
        $user = $this->userRepository->findByName('galaxy')[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $user->setAdded($date);

        $this->assertEquals($model, $user);
    }

    public function testGetByUsername(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user`=?', ['galaxy']))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, User::class);
        $user = $this->userRepository->getByUsername('galaxy');

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $user->setAdded($date);

        $this->assertEquals($model, $user);
    }

    public function testGetByUsernameAndPassword(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`user`=? AND `password`=MD5(?)', ['galaxy', 'no hope']))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, User::class);
        $user = $this->userRepository->getByUsernameAndPassword('galaxy', 'no hope');

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $user->setAdded($date);

        $this->assertEquals($model, $user);
    }

    public function testGetCount(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->setSelects(['count' => 'COUNT(`id`)'])
            ->addWhere(new Where('1', []))
        ;
        $this->loadAggregation(
            $selectQuery,
            new Record(['count' => new Value('42')]),
        );

        $this->assertEquals(42, $this->userRepository->getCount());
    }
}
