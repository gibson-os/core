<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Dto\Value;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use MDO\Query\UpdateQuery;
use ReflectionException;

class DeviceRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Device::class)]
        private readonly string $deviceTableName,
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
    public function getById(string $id): Device
    {
        return $this->fetchOne('`id`=?', [$id], Device::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByToken(string $token): Device
    {
        return $this->fetchOne('`token`=?', [$token], Device::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Device[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->fetchAll('`user_id`=?', [$userId], Device::class);
    }

    public function deleteByIds(array $ids, ?int $userId = null): bool
    {
        $repositoryWrapper = $this->getRepositoryWrapper();
        $deleteQuery = (new DeleteQuery($this->getTable($this->deviceTableName)))
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

        try {
            $repositoryWrapper->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }

    public function removeFcmToken(string $fcmToken): bool
    {
        $updateQuery = (new UpdateQuery($this->getTable($this->deviceTableName), ['fcm_token' => new Value(null)]))
            ->addWhere(new Where('`fcm_token`=?', [$fcmToken]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($updateQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
