<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Desktop;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Value;
use MDO\Enum\OrderDirection;
use MDO\Enum\ValueType;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use MDO\Query\UpdateQuery;
use ReflectionException;

class ItemRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Item::class)]
        private readonly string $itemTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    public function deleteByIdsNot(User $user, array $ids): bool
    {
        $deleteQuery = (new DeleteQuery($this->getTable($this->itemTableName)))
            ->addWhere(new Where(
                sprintf(
                    '`id` NOT IN (%s)',
                    $this->getRepositoryWrapper()->getSelectService()->getParametersString($ids),
                ),
                $ids,
            ))
            ->addWhere(new Where('`user_id`=?', [$user->getId() ?? 0]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getLastPosition(User $user): Item
    {
        return $this->fetchOne(
            '`user_id`=?',
            [$user->getId() ?? 0],
            Item::class,
            ['`position`' => OrderDirection::DESC],
        );
    }

    public function updatePosition(User $user, int $fromPosition, int $increase): bool
    {
        $updateQuery = (new UpdateQuery(
            $this->getTable($this->itemTableName),
            ['position' => new Value(sprintf('`position`+%d', $increase), ValueType::FUNCTION)],
        ))
            ->addWhere(new Where('`user_id`=?', [$user->getId() ?? 0]))
            ->addWhere(new Where('`position`>=?', [$fromPosition]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($updateQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Item[]
     */
    public function getByUser(User $user): array
    {
        return $this->fetchAll(
            '`user_id`=?',
            [$user->getId() ?? 0],
            Item::class,
            orderBy: ['`position`' => OrderDirection::ASC],
        );
    }
}
