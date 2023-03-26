<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use DateTimeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Drive;
use mysqlDatabase;

class DriveStore extends AbstractDatabaseStore
{
    private int $attributeId = 194;

    private ?DateTimeInterface $from;

    private DateTimeInterface $fromTime;

    private ?DateTimeInterface $to;

    private DateTimeInterface $toTime;

    public function __construct(
        #[GetTableName(Drive\Stat::class)] private readonly string $driveStatTableName,
        #[GetTableName(Drive\StatAttribute::class)] private readonly string $driveStatAttributeTableName,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return Drive::class;
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table
            ->appendJoin(
                $this->driveStatTableName,
                '`' . $this->tableName . '`.`id`=`' . $this->driveStatTableName . '`.`drive_id`'
            )
            ->appendJoin(
                $this->driveStatAttributeTableName,
                '`' . $this->driveStatTableName . '`.`id`=`' . $this->driveStatAttributeTableName . '`.`stat_id`'
            )
        ;
    }

    protected function getDefaultOrder(): string
    {
        return '`' . $this->driveStatTableName . '`.`added`';
    }

    protected function setWheres(): void
    {
        $this->addWhere('`' . $this->driveStatAttributeTableName . '`.`attribute_id`)=?', [$this->attributeId]);

        $timeRange = $this->toTime->getTimestamp() - $this->fromTime->getTimestamp();
        $timePoint = 900;

        while ($timeRange > 3600 * 6) {
            $timeRange -= 3600 * 6;
            $timePoint += 900;
        }

        $this->addWhere('UNIX_TIMESTAMP(`' . $this->driveStatTableName . '`.`added`)%? BETWEEN 0 AND 60', [$timePoint]);

        if ($this->from !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`' . $this->driveStatTableName . '`.`added`)>=UNIX_TIMESTAMP(?)',
                [$this->from->format('Y-m-d H:i:s')]
            );
        } else {
            $this->addWhere('UNIX_TIMESTAMP(`' . $this->driveStatTableName . '`.`added`)>=UNIX_TIMESTAMP(NOW())-(3600*4)-840');
        }

        if ($this->to !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`' . $this->driveStatTableName . '`.`added`)<=UNIX_TIMESTAMP(?)+60',
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
            '`' . $this->tableName . '`.`serial`, '
            . '`' . $this->driveStatAttributeTableName . '`.`raw_value`, '
            . "DATE_FORMAT(`' . $this->driveStatTableName . '`.`added`, '%d.%m.%Y %H:%i') AS `date`, "
            . "UNIX_TIMESTAMP(DATE_FORMAT(`' . $this->driveStatTableName . '`.`added`, '%Y-%m-%d %H:%i')) AS `timestamp`"
        );

        $data = [];

        foreach ($this->table->connection->fetchAssocList() as $statAttribute) {
            $timestamp = (string) $statAttribute['timestamp'];
            if (!isset($data[$timestamp])) {
                $data[$timestamp] = [
                    'date' => $statAttribute['date'],
                    'timestamp' => $timestamp,
                ];
            }

            $data[$timestamp][(string) $statAttribute['serial']] = (int) $statAttribute['raw_value'];
        }

        /**
         * @psalm-suppress InvalidScalarArgument
         */
        return array_values($data);
    }
}
