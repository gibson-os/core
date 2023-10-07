<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use Generator;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Service\RepositoryService;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Exception\ClientException;

class CronjobRepository extends AbstractRepository
{
    public function __construct(
        RepositoryService $repositoryService,
        #[GetTableName(Cronjob::class)]
        private readonly string $cronjobTableName,
        #[GetTable(Cronjob\Time::class)]
        private readonly Table $cronjobTimeTable,
    ) {
        parent::__construct($repositoryService);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByCommandAndUser(string $classname, string $user): Cronjob
    {
        return $this->fetchOne(
            '`command`=? AND `user`=?',
            [$classname, $user],
            Cronjob::class,
        );
    }

    /**
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<Cronjob>
     */
    public function getRunnableByUser(DateTimeInterface $dateTime, string $user): Generator
    {
        $selectQuery = $this->getSelectQuery($this->cronjobTableName, 'c')
            ->addJoin(new Join($this->cronjobTimeTable, 'ct', '`c`.`id`=`ct`.`cronjob_id`'))
            ->addWhere(new Where('`c`.`user`=:user', ['user' => $user]))
            ->addWhere(new Where('`c`.`active`=:active', ['active' => 1]))
            ->addWhere(new Where(
                '`c`.`last_run` IS NULL OR `c`.`last_run`<:now',
                ['now' => $dateTime->format('Y-m-d H:i:s')],
            ))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(CONCAT(' .
                    $this->getTimePart('year', 'year') . ', \'-\', ' .
                    $this->getTimePart('month', 'month') . ', \'-\', ' .
                    $this->getTimePart('day_of_month', 'dayOfMonth') . ', \' \', ' .
                    $this->getTimePart('hour', 'hour') . ', \':\', ' .
                    $this->getTimePart('minute', 'minute') . ', \':\', ' .
                    $this->getTimePart('second', 'second') .
                ')) BETWEEN UNIX_TIMESTAMP(COALESCE(`c`.`last_run`, `c`.`added`))',
                [
                    'year' => (int) $dateTime->format('Y'),
                    'month' => (int) $dateTime->format('n'),
                    'dayOfMonth' => (int) $dateTime->format('j'),
                    'hour' => (int) $dateTime->format('H'),
                    'minute' => (int) $dateTime->format('i'),
                    'second' => (int) $dateTime->format('s'),
                ],
            ))
            ->addWhere(new Where(
                ':dayOfWeek BETWEEN `c`.`from_day_of_week` AND `c`.`to_day_of_week`',
                ['dayOfWeek' => (int) $dateTime->format('w')],
            ))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(:timestampDate)',
                [
                    'timestampDate' => ((int) $dateTime->format('Y')) . '-' .
                        ((int) $dateTime->format('n')) . '-' .
                        ((int) $dateTime->format('j')) . ' ' .
                        ((int) $dateTime->format('H')) . ':' .
                        ((int) $dateTime->format('i')) . ':' .
                        ((int) $dateTime->format('s')),
                ],
            ))
        ;

        yield from $this->getModels($selectQuery, Cronjob::class);
    }

    private function getTimePart(string $field, $parameterName): string
    {
        return sprintf(
            'IF(' .
                '? BETWEEN `ct`.`from_%s` AND `ct`.`to_%s`, :%s,' .
                'IF(' .
                    '`ct`.`from_%s` > :%s,' .
                    '`ct`.`from_%s`,' .
                    '`ct`.`to_%s`' .
                ')' .
            ')',
            $field,
            $field,
            $parameterName,
            $field,
            $parameterName,
            $field,
            $field,
        );
    }
}
