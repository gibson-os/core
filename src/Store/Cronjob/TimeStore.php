<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Cronjob;

use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Model\Cronjob\Time;
use GibsonOS\Core\Store\AbstractDatabaseStore;

/**
 * @extends AbstractDatabaseStore<Time>
 */
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

    private ?Cronjob $cronjob = null;

    protected function getModelClassName(): string
    {
        return Time::class;
    }

    protected function setWheres(): void
    {
        if ($this->cronjob !== null) {
            $this->addWhere('`cronjob_d`=?', [$this->cronjob->getId() ?? 0]);
        }
    }

    public function getList(): array
    {
        $this->initTable();

        $this->table->selectPrepared(
            false,
            'IF(`from_hour` = `to_hour`, `from_hour`, IF(`from_hour` = 0 AND `to_hour` = 23, "*", CONCAT(`from_hour`, "-", `to_hour`))) AS `hour`, ' .
            'IF(`from_minute` = `to_minute`, `from_minute`, IF(`from_minute` = 0 AND `to_minute` = 59, "*", CONCAT(`from_minute`, "-", `to_minute`))) AS `minute`, ' .
            'IF(`from_second` = `to_second`, `from_second`, IF(`from_second` = 0 AND `to_second` = 59, "*", CONCAT(`from_second`, "-", `to_second`))) AS `second`, ' .
            'IF(`from_day_of_month` = `to_day_of_month`, `from_day_of_month`, IF(`from_day_of_month` = 1 AND `to_day_of_month` = 31, "*", CONCAT(`from_day_of_month`, "-", `to_day_of_month`))) AS `day_of_month`, ' .
            'IF(`from_day_of_week` = `to_day_of_week`, `from_day_of_week`, IF(`from_day_of_week` = 0 AND `to_day_of_week` = 6, "*", CONCAT(`from_day_of_week`, "-", `to_day_of_week`))) AS `day_of_week`, ' .
            'IF(`from_month` = `to_month`, `from_month`, IF(`from_month` = 1 AND `to_month` = 12, "*", CONCAT(`from_month`, "-", `to_month`))) AS `month`, ' .
            'IF(`from_year` = `to_year`, `from_year`, IF(`from_year` = 0 AND `to_year` = 9999, "*", CONCAT(`from_year`, "-", `to_year`))) AS `year`'
        );

        return $this->group($this->table->connection->fetchAssocList());
    }

    public function setCronjob(?Cronjob $cronjob): TimeStore
    {
        $this->cronjob = $cronjob;

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
