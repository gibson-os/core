<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class TaskRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Task::class)]
        private readonly string $taskTableName,
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
    public function getById(int $id): Task
    {
        return $this->fetchOne('`id`=?', [$id], Task::class);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Task[]
     */
    public function findByName(string $name, int $moduleId = null): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($moduleId !== null) {
            $where .= ' AND `module_id`=?';
            $parameters[] = $moduleId;
        }

        return $this->fetchAll($where, $parameters, Task::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByNameAndModuleId(string $name, int $moduleId): Task
    {
        return $this->fetchOne('`name`=? AND `module_id`=?', [$name, $moduleId], Task::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->getTable($this->taskTableName)))
            ->addWhere(new Where(
                sprintf(
                    '`id` NOT IN (%s)',
                    $repositoryWrapper->getSelectService()->getParametersString($ids),
                ),
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
