<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Action;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Action\Permission;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class PermissionRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Permission::class)]
        private readonly string $permissionTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws ReflectionException
     * @throws RecordException
     * @throws JsonException
     *
     * @return Permission[]
     */
    public function findByActionId(int $actionId): array
    {
        return $this->fetchAll('`action_id`=?', [$actionId], Permission::class);
    }

    public function deleteByAction(Action $action): bool
    {
        $deleteQuery = (new DeleteQuery($this->getTable($this->permissionTableName)))
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
