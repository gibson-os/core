<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class CronjobRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private CronjobRepository $cronjobRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`cronjob`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['command', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->cronjobRepository = new CronjobRepository('cronjob', 'cronjob_time');
    }

    public function testGetRunnableByUser(): void
    {
        $date = new DateTimeImmutable();

        $this->mysqlDatabase->execute(
            "SELECT `cronjob`.`command` FROM `marvin`.`cronjob` JOIN cronjob_time ON `cronjob`.`id`=`cronjob_time`.`cronjob_id` WHERE `cronjob`.`user`=? AND `cronjob`.`active`=1 AND (`cronjob`.`last_run` IS NULL OR `cronjob`.`last_run` < ?) AND UNIX_TIMESTAMP(CONCAT(IF(? BETWEEN `cronjob_time`.`from_year` AND `cronjob_time`.`to_year`, ?,IF(`cronjob_time`.`from_year` > ?,`cronjob_time`.`from_year`,`cronjob_time`.`to_year`)), '-', IF(? BETWEEN `cronjob_time`.`from_month` AND `cronjob_time`.`to_month`, ?,IF(`cronjob_time`.`from_month` > ?,`cronjob_time`.`from_month`,`cronjob_time`.`to_month`)), '-', IF(? BETWEEN `cronjob_time`.`from_day_of_month` AND `cronjob_time`.`to_day_of_month`, ?,IF(`cronjob_time`.`from_day_of_month` > ?,`cronjob_time`.`from_day_of_month`,`cronjob_time`.`to_day_of_month`)), ' ', IF(? BETWEEN `cronjob_time`.`from_hour` AND `cronjob_time`.`to_hour`, ?,IF(`cronjob_time`.`from_hour` > ?,`cronjob_time`.`from_hour`,`cronjob_time`.`to_hour`)), ':', IF(? BETWEEN `cronjob_time`.`from_minute` AND `cronjob_time`.`to_minute`, ?,IF(`cronjob_time`.`from_minute` > ?,`cronjob_time`.`from_minute`,`cronjob_time`.`to_minute`)), ':', IF(? BETWEEN `cronjob_time`.`from_second` AND `cronjob_time`.`to_second`, ?,IF(`cronjob_time`.`from_second` > ?,`cronjob_time`.`from_second`,`cronjob_time`.`to_second`)))) BETWEEN UNIX_TIMESTAMP(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`)) AND  UNIX_TIMESTAMP(?) AND IF(? BETWEEN `cronjob_time`.`from_day_of_week` AND `cronjob_time`.`to_day_of_week`, ?,IF(`cronjob_time`.`from_day_of_week` > ?,`cronjob_time`.`from_day_of_week`,`cronjob_time`.`to_day_of_week`)) BETWEEN IF(DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w') < ?, DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w'), ?) AND IF(DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w') > ?, DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w'), ?)",
            [
                'marvin',
                $date->format('Y-m-d H:i:s'),
                (int) $date->format('Y'),
                (int) $date->format('Y'),
                (int) $date->format('Y'),
                (int) $date->format('n'),
                (int) $date->format('n'),
                (int) $date->format('n'),
                (int) $date->format('j'),
                (int) $date->format('j'),
                (int) $date->format('j'),
                (int) $date->format('H'),
                (int) $date->format('H'),
                (int) $date->format('H'),
                (int) $date->format('i'),
                (int) $date->format('i'),
                (int) $date->format('i'),
                (int) $date->format('s'),
                (int) $date->format('s'),
                (int) $date->format('s'),
                ((int) $date->format('Y')) . '-' . ((int) $date->format('n')) . '-' . ((int) $date->format('j')) . ' ' .
                ((int) $date->format('H')) . ':' . ((int) $date->format('i')) . ':' . ((int) $date->format('s')),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'command' => 'galaxy',
            ]])
        ;

        $cronjob = $this->cronjobRepository->getRunnableByUser($date, 'marvin')[0];

        $this->assertEquals('galaxy', $cronjob->getCommand());
    }

    public function testGetRunnableByUserError(): void
    {
        $date = new DateTimeImmutable();

        $this->mysqlDatabase->execute(
            "SELECT `cronjob`.`command` FROM `marvin`.`cronjob` JOIN cronjob_time ON `cronjob`.`id`=`cronjob_time`.`cronjob_id` WHERE `cronjob`.`user`=? AND `cronjob`.`active`=1 AND (`cronjob`.`last_run` IS NULL OR `cronjob`.`last_run` < ?) AND UNIX_TIMESTAMP(CONCAT(IF(? BETWEEN `cronjob_time`.`from_year` AND `cronjob_time`.`to_year`, ?,IF(`cronjob_time`.`from_year` > ?,`cronjob_time`.`from_year`,`cronjob_time`.`to_year`)), '-', IF(? BETWEEN `cronjob_time`.`from_month` AND `cronjob_time`.`to_month`, ?,IF(`cronjob_time`.`from_month` > ?,`cronjob_time`.`from_month`,`cronjob_time`.`to_month`)), '-', IF(? BETWEEN `cronjob_time`.`from_day_of_month` AND `cronjob_time`.`to_day_of_month`, ?,IF(`cronjob_time`.`from_day_of_month` > ?,`cronjob_time`.`from_day_of_month`,`cronjob_time`.`to_day_of_month`)), ' ', IF(? BETWEEN `cronjob_time`.`from_hour` AND `cronjob_time`.`to_hour`, ?,IF(`cronjob_time`.`from_hour` > ?,`cronjob_time`.`from_hour`,`cronjob_time`.`to_hour`)), ':', IF(? BETWEEN `cronjob_time`.`from_minute` AND `cronjob_time`.`to_minute`, ?,IF(`cronjob_time`.`from_minute` > ?,`cronjob_time`.`from_minute`,`cronjob_time`.`to_minute`)), ':', IF(? BETWEEN `cronjob_time`.`from_second` AND `cronjob_time`.`to_second`, ?,IF(`cronjob_time`.`from_second` > ?,`cronjob_time`.`from_second`,`cronjob_time`.`to_second`)))) BETWEEN UNIX_TIMESTAMP(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`)) AND  UNIX_TIMESTAMP(?) AND IF(? BETWEEN `cronjob_time`.`from_day_of_week` AND `cronjob_time`.`to_day_of_week`, ?,IF(`cronjob_time`.`from_day_of_week` > ?,`cronjob_time`.`from_day_of_week`,`cronjob_time`.`to_day_of_week`)) BETWEEN IF(DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w') < ?, DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w'), ?) AND IF(DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w') > ?, DATE_FORMAT(COALESCE(`cronjob`.`last_run`, `cronjob`.`added`), '%w'), ?)",
            [
                'marvin',
                $date->format('Y-m-d H:i:s'),
                (int) $date->format('Y'),
                (int) $date->format('Y'),
                (int) $date->format('Y'),
                (int) $date->format('n'),
                (int) $date->format('n'),
                (int) $date->format('n'),
                (int) $date->format('j'),
                (int) $date->format('j'),
                (int) $date->format('j'),
                (int) $date->format('H'),
                (int) $date->format('H'),
                (int) $date->format('H'),
                (int) $date->format('i'),
                (int) $date->format('i'),
                (int) $date->format('i'),
                (int) $date->format('s'),
                (int) $date->format('s'),
                (int) $date->format('s'),
                ((int) $date->format('Y')) . '-' . ((int) $date->format('n')) . '-' . ((int) $date->format('j')) . ' ' .
                ((int) $date->format('H')) . ':' . ((int) $date->format('i')) . ':' . ((int) $date->format('s')),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
                (int) $date->format('w'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->mysqlDatabase->error()
            ->shouldBeCalledOnce()
            ->willReturn('no hope')
        ;

        $this->assertEquals([], $this->cronjobRepository->getRunnableByUser($date, 'marvin'));
    }
}
