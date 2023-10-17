<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Repository\RoleRepository;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class RoleRepositoryTest extends Unit
{
    use RepositoryTrait;

    private RoleRepository $roleRepository;

    protected function _before()
    {
        $this->loadRepository('role');

        $this->roleRepository = new RoleRepository($this->repositoryWrapper->reveal());
    }

    public function testGetByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name`=?', ['galaxy']))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Role::class),
            $this->roleRepository->getByName('galaxy'),
        );
    }
}
