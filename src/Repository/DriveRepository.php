<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class DriveRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Drive::class)]
        private readonly string $driveTableName,
        #[GetTableName(Drive\Stat::class)]
        private readonly string $driveStatTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Drive[]
     */
    public function getDrivesWithAttributes(int $secondsWithAttributes = 900): array
    {
        $selectQuery = $this->getSelectQuery($this->driveTableName, 'd')
            ->addJoin(new Join($this->getTable($this->driveStatTableName), 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(`ds`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
                [$secondsWithAttributes],
            ))
        ;

        return $this->getModels($selectQuery, Drive::class);
    }
}
