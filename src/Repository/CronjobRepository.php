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

        // @todo was ist wenn es auf sekunde 0 laufen soll aber erst bei sekunde 3 aufgerufen wird?
        $table = $this->getTable($tableName);
        $table->appendJoin($timeTableName, '`' . $tableName . '`.`id`=`' . $timeTableName . '`.`cronjob_id`');
        $table->setWhere(
            '`' . $tableName . '`.`user`=' . $this->escape($user) . ' AND ' .
            '`' . $tableName . '`.`active`=1 AND ' .
            '(`' . $tableName . '`.`last_run` IS NULL OR `' . $tableName . '`.`last_run` < ' . $this->escape($dateTime->format('Y-m-d H:i:s')) . ') AND ' .
            (int) ($dateTime->format('H')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_hour`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_hour`, 23) AND ' .
            (int) ($dateTime->format('i')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_minute`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_minute`, 59) AND ' .
            (int) ($dateTime->format('s')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_second`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_second`, 59) AND ' .
            (int) ($dateTime->format('j')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_day_of_month`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_day_of_month`, 31) AND ' .
            (int) ($dateTime->format('w')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_day_of_week`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_day_of_week`, 6) AND ' .
            (int) ($dateTime->format('n')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_month`, 1) AND IF_NULL(`' . $timeTableName . '`.`to_month`, 12) AND ' .
            (int) ($dateTime->format('Y')) . ' BETWEEN (IF_NULL(`' . $timeTableName . '`.`from_year`, 0) AND IF_NULL(`' . $timeTableName . '`.`to_year`, 9999)'
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
}
