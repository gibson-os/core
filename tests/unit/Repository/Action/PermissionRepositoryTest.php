<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Action;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Field;
use MDO\Dto\Query\Where;
use MDO\Enum\Type;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;

class PermissionRepositoryTest extends Unit
{
    use RepositoryTrait;

    private PermissionRepository $permissionRepository;

    protected function _before()
    {
        $this->loadRepository(
            'action_permission',
            [
                new Field('action_id', false, Type::BIGINT, '', null, '', 20),
                new Field('permission', false, Type::BIGINT, '', null, '', 20),
            ],
        );

        $this->permissionRepository = new PermissionRepository($this->repositoryWrapper->reveal(), $this->table);
    }

    public function testFindByActionId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`action_id`=?', [42]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Action\Permission::class, ''),
            $this->permissionRepository->findByActionId(42)[0],
        );
    }

    public function testDeleteByAction(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`action_id`=?', [42]))
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->client->execute($deleteQuery)
            ->shouldBeCalledOnce()
            ->willReturn(null)
        ;

        $this->assertTrue($this->permissionRepository->deleteByAction(
            (new Action($this->modelWrapper->reveal()))->setId(42),
        ));
    }
}
