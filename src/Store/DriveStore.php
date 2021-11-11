<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use DateTimeInterface;
use GibsonOS\Core\Model\Drive;

class DriveStore extends AbstractDatabaseStore
{
    private int $attributeId = 194;

    private ?DateTimeInterface $from;

    private DateTimeInterface $fromTime;

    private ?DateTimeInterface $to;

    private DateTimeInterface $toTime;

    protected function getModelClassName(): string
    {
        return Drive::class;
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table
            ->appendJoin(Drive\Stat::getTableName(), '`system_drive`.`id`=`system_drive_stat`.`drive_id`')
            ->appendJoin(Drive\StatAttribute::getTableName(), '`system_drive_stat`.`id`=`system_drive_stat_attribute`.`stat_id`')
        ;
    }

    protected function getDefaultOrder(): string
    {
        return '`system_drive_stat`.`added`';
    }

    protected function setWheres(): void
    {
        $this->addWhere('`system_drive_stat_attribute`.`attribute_id`)=?', [$this->attributeId]);

        $timeRange = $this->toTime->getTimestamp() - $this->fromTime->getTimestamp();
        $timePoint = 900;

        while ($timeRange > 3600 * 6) {
            $timeRange -= 3600 * 6;
            $timePoint += 900;
        }

        $this->addWhere('UNIX_TIMESTAMP(`system_drive_stat`.`added`)%? BETWEEN 0 AND 60', [$timePoint]);

        if ($this->from !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(?)',
                [$this->from->format('Y-m-d H:i:s')]
            );
        } else {
            $this->addWhere('UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-(3600*4)-840');
        }

        if ($this->to !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`system_drive_stat`.`added`)<=UNIX_TIMESTAMP(?)+60',
                [$this->to->format('Y-m-d H:i:s')]
            );
        }
    }

    public function setAttributeId(int $attributeId): DriveStore
    {
        $this->attributeId = $attributeId;

        return $this;
    }

    public function setFrom(?DateTimeInterface $from): DriveStore
    {
        $this->from = $from;

        return $this;
    }

    public function setFromTime(DateTimeInterface $fromTime): DriveStore
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    public function setTo(?DateTimeInterface $to): DriveStore
    {
        $this->to = $to;

        return $this;
    }

    public function setToTime(DateTimeInterface $toTime): DriveStore
    {
        $this->toTime = $toTime;

        return $this;
    }

    public function getList(): iterable
    {
        $this->table->select(
            false,
            '`system_drive`.`serial`, '
            . '`system_drive_stat_attribute`.`raw_value`, '
            . "DATE_FORMAT(`system_drive_stat`.`added`, '%d.%m.%Y %H:%i') AS `date`, "
            . "UNIX_TIMESTAMP(DATE_FORMAT(`system_drive_stat`.`added`, '%Y-%m-%d %H:%i')) AS `timestamp`"
        );

        $data = [];

        foreach ($this->table->connection->fetchAssocList() as $statAttribute) {
            if (!isset($data[$statAttribute['timestamp']])) {
                $data[$statAttribute['timestamp']] = [
                    'date' => $statAttribute['date'],
                    'timestamp' => $statAttribute['timestamp'],
                ];
            }

            $data[$statAttribute['timestamp']][$statAttribute['serial']] = (int) $statAttribute['raw_value'];
        }

        /**
         * @psalm-suppress InvalidScalarArgument
         */
        return array_values($data);
    }
}
