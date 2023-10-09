<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Action;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Action\Permission;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class PermissionRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTable(Permission::class)]
        private readonly Table $permissionTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws JsonException
     * @throws ClientException
     * @throws ReflectionException
     *
     * @return Permission[]
     */
    public function findByActionId(int $actionId): array
    {
        return $this->fetchAll('`action_id`=?', [$actionId], Permission::class);
    }

    public function deleteByAction(Action $action): bool
    {
        $deleteQuery = (new DeleteQuery($this->permissionTable))
            ->addWhere(new Where('`action_id`=?', [$action->getId()]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
