<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use Generator;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Service\RepositoryService;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;

class ActionRepository extends AbstractRepository
{
    public function __construct(
        RepositoryService $repositoryService,
        #[GetTable(Action::class)]
        private readonly Table $actionTable,
    ) {
        parent::__construct($repositoryService);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(int $id): Action
    {
        return $this->fetchOne('`id`=?', [$id], Action::class);
    }

    /**
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<Action>
     */
    public function findByName(string $name, int $taskId = null): Generator
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($taskId !== null) {
            $where .= ' AND `task_id`=?';
            $parameters[] = $taskId;
        }

        yield from $this->fetchAll($where, $parameters, Action::class);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByNameAndTaskId(string $name, HttpMethod $method, int $taskId): Action
    {
        return $this->fetchOne(
            '`name`=? AND `method`=? AND `task_id`=?',
            [$name, $method->name, $taskId],
            Action::class,
        );
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $deleteQuery = (new DeleteQuery($this->actionTable))
            ->addWhere(new Where(
                sprintf('`id` NOT IN (%s)', $this->repositoryService->getSelectService()->getParametersString($ids)),
                $ids,
            ))
        ;

        try {
            $this->repositoryService->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
