<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Exception\ClientException;
use MDO\Query\DeleteQuery;
use MDO\Query\UpdateQuery;
use ReflectionException;

class DeviceRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTable(Device::class)]
        private readonly Table $deviceTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(string $id): Device
    {
        return $this->fetchOne('`id`=?', [$id], Device::class);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByToken(string $token): Device
    {
        return $this->fetchOne('`token`=?', [$token], Device::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Device[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->fetchAll('`user_id`=?', [$userId], Device::class);
    }

    /**
     * @throws ClientException
     */
    public function deleteByIds(array $ids, int $userId = null): void
    {
        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->deviceTable))
            ->addWhere(new Where(
                sprintf(
                    '`id` IN (%s)',
                    $repositoryWrapper->getSelectService()->getParametersString($ids),
                ),
                $ids,
            ))
        ;

        if ($userId !== null) {
            $deleteQuery->addWhere(new Where('`user_id`=?', [$userId]));
        }

        $repositoryWrapper->getClient()->execute($deleteQuery);
    }

    /**
     * @throws ClientException
     */
    public function removeFcmToken(string $fcmToken): void
    {
        $updateQuery = (new UpdateQuery($this->deviceTable, ['fcm_token' => new Value(null)]))
            ->addWhere(new Where('`fcm_token`=?', [$fcmToken]))
        ;

        $this->getRepositoryWrapper()->getClient()->execute($updateQuery);
    }
}
