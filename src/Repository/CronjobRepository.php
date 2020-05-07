<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Model\Cronjob;

class CronjobRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     *
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
            'UNIX_TIMESTAMP(CONCAT(' .
                $this->getTimePartWhere('year', (int) $dateTime->format('Y'), 9999) . ', \'-\', ' .
                $this->getTimePartWhere('month', (int) $dateTime->format('n'), 12) . ', \'-\', ' .
                $this->getTimePartWhere('day_of_month', (int) $dateTime->format('j'), 31) . ', \' \', ' .
                $this->getTimePartWhere('hour', (int) $dateTime->format('H'), 23) . ', \':\', ' .
                $this->getTimePartWhere('minute', (int) $dateTime->format('i'), 59) . ', \':\', ' .
                $this->getTimePartWhere('second', (int) $dateTime->format('s'), 59) .
            ')) BETWEEN UNIX_TIMESTAMP(COALESCE(`' . $tableName . '`.`last_run`, `' . $tableName . '`.`added`)) AND UNIX_TIMESTAMP(\'' .
                ((int) $dateTime->format('Y')) . '-' . ((int) $dateTime->format('n')) . '-' . ((int) $dateTime->format('j')) . ' ' .
                ((int) $dateTime->format('H')) . ':' . ((int) $dateTime->format('i')) . ':' . ((int) $dateTime->format('s')) . '\'' .
            ')'
        );

        if (!$table->select()) {
            return [];
        }

        $models = [];

        do {
            $model = new Cronjob();
            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }

    private function getTimePartWhere(string $field, int $value, int $maxValue): string
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
