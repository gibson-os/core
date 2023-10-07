<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use Generator;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Service\RepositoryService;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;

class DriveRepository extends AbstractRepository
{
    public function __construct(
        RepositoryService $repositoryService,
        #[GetTableName(Drive::class)]
        private readonly string $driveTableName,
        #[GetTable(Drive\Stat::class)]
        private readonly Table $driveStatTable,
    ) {
        parent::__construct($repositoryService);
    }

    /**
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<Drive>
     */
    public function getDrivesWithAttributes(int $secondsWithAttributes = 900): Generator
    {
        $selectQuery = $this->getSelectQuery($this->driveTableName)
            ->addJoin(new Join($this->driveStatTable, 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
                [$secondsWithAttributes],
            ))
        ;

        yield from $this->getModels($selectQuery, Drive::class);
    }
}
