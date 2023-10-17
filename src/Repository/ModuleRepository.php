<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class ModuleRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTable(Module::class)]
        private readonly Table $moduleTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getById(int $id): Module
    {
        return $this->fetchOne('`id`=?', [$id], Module::class);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Module[]
     */
    public function findByName(string $name): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        return $this->fetchAll($where, $parameters, Module::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getByName(string $name): Module
    {
        return $this->fetchOne('`name`=?', [$name], Module::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Module[]
     */
    public function getAll(): array
    {
        return $this->fetchAll('1', [], Module::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->moduleTable))
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
