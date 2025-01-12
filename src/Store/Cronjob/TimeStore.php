<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Cronjob;

use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Model\Cronjob\Time;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use MDO\Dto\Record;

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
        if ($this->cronjob instanceof Cronjob) {
            $this->addWhere('`cronjob_id`=?', [$this->cronjob->getId() ?? 0]);
        }
    }

    public function getList(): array
    {
        $this->initQuery();
        $this->selectQuery->setSelects([
            'hour' => 'IF(`from_hour` = `to_hour`, `from_hour`, IF(`from_hour` = 0 AND `to_hour` = 23, "*", CONCAT(`from_hour`, "-", `to_hour`)))',
            'minute' => 'IF(`from_minute` = `to_minute`, `from_minute`, IF(`from_minute` = 0 AND `to_minute` = 59, "*", CONCAT(`from_minute`, "-", `to_minute`)))',
            'second' => 'IF(`from_second` = `to_second`, `from_second`, IF(`from_second` = 0 AND `to_second` = 59, "*", CONCAT(`from_second`, "-", `to_second`)))',
            'day_of_month' => 'IF(`from_day_of_month` = `to_day_of_month`, `from_day_of_month`, IF(`from_day_of_month` = 1 AND `to_day_of_month` = 31, "*", CONCAT(`from_day_of_month`, "-", `to_day_of_month`)))',
            'day_of_week' => 'IF(`from_day_of_week` = `to_day_of_week`, `from_day_of_week`, IF(`from_day_of_week` = 0 AND `to_day_of_week` = 6, "*", CONCAT(`from_day_of_week`, "-", `to_day_of_week`)))',
            'month' => 'IF(`from_month` = `to_month`, `from_month`, IF(`from_month` = 1 AND `to_month` = 12, "*", CONCAT(`from_month`, "-", `to_month`)))',
            'year' => 'IF(`from_year` = `to_year`, `from_year`, IF(`from_year` = 0 AND `to_year` = 9999, "*", CONCAT(`from_year`, "-", `to_year`)))',
        ]);

        $result = $this->getDatabaseStoreWrapper()->getClient()->execute($this->selectQuery);
        $records = array_map(
            static fn (Record $record): array => $record->getValuesAsArray(),
            iterator_to_array($result->iterateRecords()),
        );

        return $this->group($records);
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
                $groupedCronjobs === []
                || count($groupedCronjobs) > count($newGroupedCronjobs)
            ) {
                $groupedCronjobs = $newGroupedCronjobs;
                $selectedPart = $part;
            }
        }

        if ($groupedCronjobs === []) {
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
