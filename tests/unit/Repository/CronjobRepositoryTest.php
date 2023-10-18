<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Repository\CronjobRepository;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Query\SelectQuery;

class CronjobRepositoryTest extends Unit
{
    use RepositoryTrait;

    private CronjobRepository $cronjobRepository;

    private Table $cronjobTimeTable;

    protected function _before()
    {
        $this->loadRepository('cronjob');
        $this->cronjobTimeTable = new Table('cronjob_time', []);

        $this->cronjobRepository = new CronjobRepository(
            $this->repositoryWrapper->reveal(),
            $this->table->getTableName(),
            $this->cronjobTimeTable,
        );
    }

    public function testGetRunnableByUser(): void
    {
        $date = new DateTimeImmutable();
        $selectQuery = (new SelectQuery($this->table, 'c'))
            ->addJoin(new Join($this->cronjobTimeTable, 'ct', '`c`.`id`=`ct`.`cronjob_id`'))
            ->addWhere(new Where('`c`.`user`=:user', ['user' => 'marvin']))
            ->addWhere(new Where('`c`.`active`=:active', ['active' => 1]))
            ->addWhere(new Where(
                '`c`.`last_run` IS NULL OR `c`.`last_run`<:now',
                ['now' => $date->format('Y-m-d H:i:s')],
            ))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(CONCAT(' .
                    'IF(? BETWEEN `ct`.`from_year` AND `ct`.`to_year`, :year,IF(`ct`.`from_year` > :year,`ct`.`from_year`,`ct`.`to_year`)), \'-\', ' .
                    'IF(? BETWEEN `ct`.`from_month` AND `ct`.`to_month`, :month,IF(`ct`.`from_month` > :month,`ct`.`from_month`,`ct`.`to_month`)), \'-\', ' .
                    'IF(? BETWEEN `ct`.`from_day_of_month` AND `ct`.`to_day_of_month`, :dayOfMonth,IF(`ct`.`from_day_of_month` > :dayOfMonth,`ct`.`from_day_of_month`,`ct`.`to_day_of_month`)), \' \', ' .
                    'IF(? BETWEEN `ct`.`from_hour` AND `ct`.`to_hour`, :hour,IF(`ct`.`from_hour` > :hour,`ct`.`from_hour`,`ct`.`to_hour`)), \':\', ' .
                    'IF(? BETWEEN `ct`.`from_minute` AND `ct`.`to_minute`, :minute,IF(`ct`.`from_minute` > :minute,`ct`.`from_minute`,`ct`.`to_minute`)), \':\', ' .
                    'IF(? BETWEEN `ct`.`from_second` AND `ct`.`to_second`, :second,IF(`ct`.`from_second` > :second,`ct`.`from_second`,`ct`.`to_second`))' .
                ')) BETWEEN UNIX_TIMESTAMP(COALESCE(`c`.`last_run`, `c`.`added`))',
                [
                    'year' => (int) $date->format('Y'),
                    'month' => (int) $date->format('n'),
                    'dayOfMonth' => (int) $date->format('j'),
                    'hour' => (int) $date->format('H'),
                    'minute' => (int) $date->format('i'),
                    'second' => (int) $date->format('s'),
                ],
            ))
            ->addWhere(new Where(
                ':dayOfWeek BETWEEN `c`.`from_day_of_week` AND `c`.`to_day_of_week`',
                ['dayOfWeek' => (int) $date->format('w')],
            ))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(:timestampDate)',
                [
                    'timestampDate' => ((int) $date->format('Y')) . '-' .
                        ((int) $date->format('n')) . '-' .
                        ((int) $date->format('j')) . ' ' .
                        ((int) $date->format('H')) . ':' .
                        ((int) $date->format('i')) . ':' .
                        ((int) $date->format('s')),
                ],
            ))
        ;

        $model = $this->loadModel($selectQuery, Cronjob::class, '');
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $cronjob = $this->cronjobRepository->getRunnableByUser($date, 'marvin')[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $cronjob->setAdded($date);

        $this->assertEquals($model, $cronjob);
    }
}
