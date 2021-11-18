<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;
use mysqlTable;

class CronjobRepository extends AbstractRepository
{
    /**
     * @return Cronjob[]
     */
    public function getRunnableByUser(DateTimeInterface $dateTime, string $user): array
    {
        $tableName = Cronjob::getTableName();
        $timeTableName = Cronjob\Time::getTableName();

        $table = $this->getTable($tableName);
        $table
            ->appendJoin($timeTableName, '`' . $tableName . '`.`id`=`' . $timeTableName . '`.`cronjob_id`')
            ->setWhereParameters([$user, $dateTime->format('Y-m-d H:i:s')])
            ->setWhere(
                '`' . $tableName . '`.`user`=? AND ' .
                '`' . $tableName . '`.`active`=1 AND ' .
                '(`' . $tableName . '`.`last_run` IS NULL OR `' . $tableName . '`.`last_run` < ?) AND ' .
                'UNIX_TIMESTAMP(CONCAT(' .
                    $this->getTimePart($table, 'year', (int) $dateTime->format('Y')) . ', \'-\', ' .
                    $this->getTimePart($table, 'month', (int) $dateTime->format('n')) . ', \'-\', ' .
                    $this->getTimePart($table, 'day_of_month', (int) $dateTime->format('j')) . ', \' \', ' .
                    $this->getTimePart($table, 'hour', (int) $dateTime->format('H')) . ', \':\', ' .
                    $this->getTimePart($table, 'minute', (int) $dateTime->format('i')) . ', \':\', ' .
                    $this->getTimePart($table, 'second', (int) $dateTime->format('s')) .
                ')) BETWEEN UNIX_TIMESTAMP(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`)) AND  ' .
                $this->getTimeAsUnixTimestampFunction($table, $dateTime) . ' AND ' .
                $this->getTimePart($table, 'day_of_week', (int) $dateTime->format('w')) .
                $this->getFirstRunBetweenPart($table, $dateTime)
            )
        ;

        try {
            return $this->getModels($table, Cronjob::class);
        } catch (SelectError) {
            return [];
        }
    }

    private function getFirstRunBetweenPart(mysqlTable $table, DateTimeInterface $dateTime): string
    {
        $tableName = Cronjob::getTableName();
        $table
            ->addWhereParameter((int) $dateTime->format('w'))
            ->addWhereParameter((int) $dateTime->format('w'))
            ->addWhereParameter((int) $dateTime->format('w'))
            ->addWhereParameter((int) $dateTime->format('w'))
        ;

        return
            ' BETWEEN ' .
                'IF(' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\') < ?, ' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\'), ' .
                    '?' .
                ') AND ' .
                    'IF(' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\') > ?, ' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\'), ' .
                    '?' .
                ')'
        ;
    }

    private function getTimeAsUnixTimestampFunction(mysqlTable $table, DateTimeInterface $dateTime): string
    {
        $table->addWhereParameter(
            ((int) $dateTime->format('Y')) . '-' . ((int) $dateTime->format('n')) . '-' . ((int) $dateTime->format('j')) . ' ' .
            ((int) $dateTime->format('H')) . ':' . ((int) $dateTime->format('i')) . ':' . ((int) $dateTime->format('s'))
        );

        return 'UNIX_TIMESTAMP(?)';
    }

    private function getTimePart(mysqlTable $table, string $field, int $value): string
    {
        $tableName = Cronjob\Time::getTableName();
        $table
            ->addWhereParameter($value)
            ->addWhereParameter($value)
            ->addWhereParameter($value)
        ;

        return
            'IF(' .
                '? BETWEEN `' . $tableName . '`.`from_' . $field . '` AND `' . $tableName . '`.`to_' . $field . '`, ?,' .
                'IF(' .
                    '`' . $tableName . '`.`from_' . $field . '` > ?,' .
                    '`' . $tableName . '`.`from_' . $field . '`,' .
                    '`' . $tableName . '`.`to_' . $field . '`' .
                ')' .
            ')'
        ;
    }
}
