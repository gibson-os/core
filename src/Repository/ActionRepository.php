<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class ActionRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Action::class)]
        private readonly string $actionTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getById(int $id): Action
    {
        return $this->fetchOne('`id`=?', [$id], Action::class);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Action[]
     */
    public function findByName(string $name, int $taskId = null): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($taskId !== null) {
            $where .= ' AND `task_id`=?';
            $parameters[] = $taskId;
        }

        return $this->fetchAll($where, $parameters, Action::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
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
        if (count($ids) === 0) {
            return true;
        }

        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->getTable($this->actionTableName)))
            ->addWhere(new Where(
                sprintf('`id` NOT IN (%s)', $repositoryWrapper->getSelectService()->getParametersString($ids)),
                $ids,
            ))
        ;

        try {
            $repositoryWrapper->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
