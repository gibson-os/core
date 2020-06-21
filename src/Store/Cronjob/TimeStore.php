<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Cronjob;

use GibsonOS\Core\Model\Cronjob\Time;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class TimeStore extends AbstractDatabaseStore
{
    private const PARTS = [
        'year',
        'month',
        'day_of_month',
        'day_of_week',
        'hour',
        'minute',
        'second',
    ];

    /**
     * @var int|null
     */
    private $cronjobId;

    protected function getTableName(): string
    {
        return Time::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    public function getList(): array
    {
        if (!empty($this->cronjobId)) {
            $this->table->setWhere('`cronjob_id`=' . $this->cronjobId);
        }

        $this->table->select(
            false,
            'IF(`from_hour` = `to_hour`, `from_hour`, IF(`from_hour` = 0 AND `to_hour` = 23, "*", CONCAT(`from_hour`, "-", `to_hour`))) AS `hour`, ' .
            'IF(`from_minute` = `to_minute`, `from_minute`, IF(`from_minute` = 0 AND `to_minute` = 59, "*", CONCAT(`from_minute`, "-", `to_minute`))) AS `minute`, ' .
            'IF(`from_second` = `to_second`, `from_second`, IF(`from_second` = 0 AND `to_second` = 59, "*", CONCAT(`from_second`, "-", `to_second`))) AS `second`, ' .
            'IF(`from_day_of_month` = `to_day_of_month`, `from_day_of_month`, IF(`from_day_of_month` = 1 AND `to_day_of_month` = 31, "*", CONCAT(`from_day_of_month`, "-", `to_day_of_month`))) AS `day_of_month`, ' .
            'IF(`from_day_of_week` = `to_day_of_week`, `from_day_of_week`, IF(`from_day_of_week` = 0 AND `to_day_of_week` = 6, "*", CONCAT(`from_day_of_week`, "-", `to_day_of_week`))) AS `day_of_week`, ' .
            'IF(`from_month` = `to_month`, `from_month`, IF(`from_month` = 1 AND `to_month` = 12, "*", CONCAT(`from_month`, "-", `to_month`))) AS `month`, ' .
            'IF(`from_year` = `to_year`, `from_year`, IF(`from_year` = 0 AND `to_year` = 9999, "*", CONCAT(`from_year`, "-", `to_year`))) AS `year`'
        );

        $cronjobs = $this->table->connection->fetchAssocList();

        return $this->group($cronjobs);
    }

    public function setCronjobId(?int $cronjobId): TimeStore
    {
        $this->cronjobId = $cronjobId;

        return $this;
    }

    private function group(array $cronjobs, array $disabledParts = []): array
    {
        $groupedCronjobs = [];
        $selectedPart = null;

        foreach (self::PARTS as $part) {
            if (in_array($part, $disabledParts)) {
                continue;
            }

            $newGroupedCronjobs = $this->groupByPart($cronjobs, $part);

            if (
                empty($groupedCronjobs) ||
                count($groupedCronjobs) > count($newGroupedCronjobs)
            ) {
                $groupedCronjobs = $newGroupedCronjobs;
                $selectedPart = $part;
            }
        }

        if (count($groupedCronjobs) === 0) {
            return $cronjobs;
        }

        $disabledParts[] = $selectedPart;

        if (count($groupedCronjobs) === count($cronjobs)) {
            return [
                'part' => $selectedPart,
                'items' => $groupedCronjobs,
            ];
        }

        foreach ($groupedCronjobs as &$groupedCronjobList) {
            $groupedCronjobList = [
                'part' => $selectedPart,
                'items' => $this->group($groupedCronjobList, $disabledParts),
            ];
        }

        return $groupedCronjobs;
    }

    private function groupByPart(array $cronjobs, string $part): array
    {
        $groupedCronjobs = [];

        foreach ($cronjobs as $cronjob) {
            $key = $cronjob[$part];

            if (!isset($groupedCronjobs[$key])) {
                $groupedCronjobs[$key] = [];
            }

            $groupedCronjobs[$key][] = $cronjob;
        }

        return $groupedCronjobs;
    }
}
