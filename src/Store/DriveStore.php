<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use DateTimeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Query\Join;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;

/**
 * @extends AbstractDatabaseStore<Drive>
 */
class DriveStore extends AbstractDatabaseStore
{
    private int $attributeId = 194;

    private ?DateTimeInterface $from;

    private DateTimeInterface $fromTime;

    private ?DateTimeInterface $to;

    private DateTimeInterface $toTime;

    public function __construct(
        #[GetTableName(Drive\Stat::class)]
        private readonly string $driveStatTableName,
        #[GetTableName(Drive\StatAttribute::class)]
        private readonly string $driveStatAttributeTableName,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Drive::class;
    }

    protected function getAlias(): ?string
    {
        return 'd';
    }

    protected function initQuery(): void
    {
        parent::initQuery();

        $this->selectQuery
            ->addJoin(new Join($this->getTable($this->driveStatTableName), 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addJoin(new Join($this->getTable($this->driveStatAttributeTableName), 'dsa', '`ds`.`id`=`dsa`.`stat_id`'))
        ;
    }

    protected function getDefaultOrder(): string
    {
        return '`ds`.`added`';
    }

    protected function setWheres(): void
    {
        $this->addWhere('`dsa`.`attribute_id`)=?', [$this->attributeId]);

        $timeRange = $this->toTime->getTimestamp() - $this->fromTime->getTimestamp();
        $timePoint = 900;

        while ($timeRange > 3600 * 6) {
            $timeRange -= 3600 * 6;
            $timePoint += 900;
        }

        $this->addWhere('UNIX_TIMESTAMP(`ds`.`added`)%? BETWEEN 0 AND 60', [$timePoint]);

        if ($this->from !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`ds`.`added`)>=UNIX_TIMESTAMP(?)',
                [$this->from->format('Y-m-d H:i:s')],
            );
        } else {
            $this->addWhere('UNIX_TIMESTAMP(`ds`.`added`)>=UNIX_TIMESTAMP(NOW())-(3600*4)-840');
        }

        if ($this->to !== null) {
            $this->addWhere(
                'UNIX_TIMESTAMP(`ds`.`added`)<=UNIX_TIMESTAMP(?)+60',
                [$this->to->format('Y-m-d H:i:s')],
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

    /**
     * @throws ClientException
     * @throws RecordException
     *
     * @return array
     */
    public function getList(): iterable
    {
        $this->selectQuery->setSelects([
            'serial' => '`d`.`serial`',
            'raw_value' => '`dsa`.`raw_value`',
            'date' => 'DATE_FORMAT(`ds`.`added`, \'%d.%m.%Y %H:%i\')',
            'timestamp' => 'UNIX_TIMESTAMP(DATE_FORMAT(`ds`.`added`, \'%Y-%m-%d %H:%i\'))',
        ]);

        $result = $this->getDatabaseStoreWrapper()->getClient()->execute($this->selectQuery);
        $data = [];

        foreach ($result->iterateRecords() as $record) {
            $timestamp = (string) $record->get('timestamp')->getValue();
            $data[$timestamp] ??= [
                'date' => $record->get('date')->getValue(),
                'timestamp' => $timestamp,
            ];

            $data[$timestamp][(string) $record->get('serial')->getValue()] = (int) $record->get('raw_value')->getValue();
        }

        return array_values($data);
    }
}
