<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use DateTimeInterface;
use GibsonOS\Core\Model\Weather;

class WeatherStore extends AbstractDatabaseStore
{
    private ?int $locationId = null;

    private ?DateTimeInterface $date = null;

    protected function getTableName(): string
    {
        return Weather::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    public function getList(): iterable
    {
        $this->table
            ->setWhere('`location_id`=? AND `date`<=?')
            ->setOrderBy('`date` DESC')
            ->setLimit(1)
            ->appendUnion()
            ->setWhere('`location_id`=? AND `date`>?')
            ->setOrderBy('`date` ASC')
            ->setLimit()
            ->appendUnion()
            ->setWhereParameters([])
        ;

        $weathers = [];

        if (!$this->table->selectUnion()) {
            return $weathers;
        }

        do {
            $weather = new Weather();
            $weather->loadFromMysqlTable($this->table);
            $weathers[] = $weather;
        } while ($this->table->next());

        return $weathers;
    }

    public function setLocationId(?int $locationId): WeatherStore
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function setDate(?DateTimeInterface $date): WeatherStore
    {
        $this->date = $date;

        return $this;
    }
}
