<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;
use mysqlTable;

/**
 * @method Cronjob[] getModels(mysqlTable $table, string $abstractModelClassName)
 */
class CronjobRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @return Cronjob[]
     */
    public function getRunnableByUser(DateTimeInterface $dateTime, string $user): array
    {
        $tableName = Cronjob::getTableName();
        $timeTableName = Cronjob\Time::getTableName();

        $table = $this->getTable($tableName);
        $table->appendJoin($timeTableName, '`' . $tableName . '`.`id`=`' . $timeTableName . '`.`cronjob_id`');
        $table->setWhere(
            '`' . $tableName . '`.`user`=' . $this->escape($user) . ' AND ' .
            '`' . $tableName . '`.`active`=1 AND ' .
            '(`' . $tableName . '`.`last_run` IS NULL OR `' . $tableName . '`.`last_run` < ' . $this->escape($dateTime->format('Y-m-d H:i:s')) . ') AND ' .
            'UNIX_TIMESTAMP(CONCAT(' .
                $this->getTimePartWhere('year', (int) $dateTime->format('Y')) . ', \'-\', ' .
                $this->getTimePartWhere('month', (int) $dateTime->format('n')) . ', \'-\', ' .
                $this->getTimePartWhere('day_of_month', (int) $dateTime->format('j')) . ', \' \', ' .
                $this->getTimePartWhere('hour', (int) $dateTime->format('H')) . ', \':\', ' .
                $this->getTimePartWhere('minute', (int) $dateTime->format('i')) . ', \':\', ' .
                $this->getTimePartWhere('second', (int) $dateTime->format('s')) .
            ')) BETWEEN UNIX_TIMESTAMP(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`)) AND UNIX_TIMESTAMP(\'' .
                ((int) $dateTime->format('Y')) . '-' . ((int) $dateTime->format('n')) . '-' . ((int) $dateTime->format('j')) . ' ' .
                ((int) $dateTime->format('H')) . ':' . ((int) $dateTime->format('i')) . ':' . ((int) $dateTime->format('s')) . '\'' .
            ') AND ' .
            $this->getTimePartWhere('day_of_week', (int) $dateTime->format('w')) .
            ' BETWEEN ' .
                'IF(' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\') < ' . (int) $dateTime->format('w') . ', ' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\'), ' .
                    (int) $dateTime->format('w') .
                ') AND ' .
                'IF(' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\') > ' . (int) $dateTime->format('w') . ', ' .
                    'DATE_FORMAT(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`), \'%w\'), ' .
                    (int) $dateTime->format('w') .
                ')'
        );

        if (!$table->select()) {
            return [];
        }

        return $this->getModels($table, Cronjob::class);
    }

    private function getTimePartWhere(string $field, int $value): string
    {
        $tableName = Cronjob\Time::getTableName();

        return
            'IF(' .
                $value . ' BETWEEN `' . $tableName . '`.`from_' . $field . '` AND `' . $tableName . '`.`to_' . $field . '`,' .
                $value . ',' .
                'IF(' .
                    '`' . $tableName . '`.`from_' . $field . '` > ' . $value . ',' .
                    '`' . $tableName . '`.`from_' . $field . '`,' .
                    '`' . $tableName . '`.`to_' . $field . '`' .
                ')' .
            ')'
        ;
    }
}
