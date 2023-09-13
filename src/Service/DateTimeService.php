<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use GibsonOS\Core\Attribute\GetEnv;

class DateTimeService
{
    private ?DateTimeZone $timezone;

    public function __construct(
        #[GetEnv('timezone')]
        ?string $timezone,
        #[GetEnv('date_latitude')]
        private readonly ?float $latitude,
        #[GetEnv('date_longitude')]
        private readonly ?float $longitude,
    ) {
        $this->timezone = $timezone === null ? null : new DateTimeZone($timezone);
    }

    /**
     * @psalm-suppress InvalidReturnType
     */
    public function get(string $time = 'now', DateTimeZone $timezone = null): DateTime
    {
        try {
            return new DateTime($time, $timezone ?? $this->timezone);
        } catch (Exception) {
            error_log(sprintf(
                'Es kann keine Datums Objekt mit "%s" für die Zeitzone "%s" angelegt werden',
                $time,
                $this->timezone?->getName() ?? 'none',
            ));

            return new DateTime();
        }
    }

    public function setTimezone(DateTimeZone $timezone): DateTimeService
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getSunset(DateTimeInterface $dateTime): int
    {
        return (int) date_sunset(
            $dateTime->getTimestamp(),
            SUNFUNCS_RET_TIMESTAMP,
            $this->latitude ?? 0.0,
            $this->longitude ?? 0.0,
        );
    }

    public function getSunrise(DateTimeInterface $dateTime): int
    {
        return (int) date_sunrise(
            $dateTime->getTimestamp(),
            SUNFUNCS_RET_TIMESTAMP,
            $this->latitude ?? 0.0,
            $this->longitude ?? 0.0,
        );
    }
}
