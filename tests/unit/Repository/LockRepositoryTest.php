<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Lock;
use GibsonOS\Core\Repository\LockRepository;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class LockRepositoryTest extends Unit
{
    use RepositoryTrait;

    private LockRepository $lockRepository;

    protected function _before()
    {
        $this->loadRepository('lock');

        $this->lockRepository = new LockRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name`=?', ['galaxy']))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Lock::class),
            $lock = $this->lockRepository->getByName('galaxy'),
        );
    }
}
