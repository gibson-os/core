<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class IconRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTable(Icon::class)]
        private readonly Table $iconTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(int $id): Icon
    {
        return $this->fetchOne('`id`=?', [$id], Icon::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Icon[]
     */
    public function findByIds(array $ids): array
    {
        return $this->fetchAll(
            sprintf('`id` IN (%s)', $this->getRepositoryWrapper()->getSelectService()->getParametersString($ids)),
            $ids,
            Icon::class,
        );
    }

    public function deleteByIds(array $ids): bool
    {
        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->iconTable))
            ->addWhere(new Where(
                sprintf('`id` IN (%s)', $repositoryWrapper->getSelectService()->getParametersString($ids)),
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
