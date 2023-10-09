<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use ReflectionException;

class DriveRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Drive::class)]
        private readonly string $driveTableName,
        #[GetTable(Drive\Stat::class)]
        private readonly Table $driveStatTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Drive[]
     */
    public function getDrivesWithAttributes(int $secondsWithAttributes = 900): array
    {
        $selectQuery = $this->getSelectQuery($this->driveTableName)
            ->addJoin(new Join($this->driveStatTable, 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
                [$secondsWithAttributes],
            ))
        ;

        return $this->getModels($selectQuery, Drive::class);
    }
}
