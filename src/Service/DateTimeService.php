<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use GibsonOS\Core\Attribute\GetEnv;

class DateTimeService extends AbstractService
{
    private DateTimeZone $timezone;

    #[GetEnv('timezone')]
    #[GetEnv('date_latitude', 'latitude')]
    #[GetEnv('date_longitude', 'longitude')]
    public function __construct(string $timezone, private float $latitude, private float $longitude)
    {
        $this->timezone = new DateTimeZone($timezone);
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
                $this->timezone->getName()
            ));
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
            $this->latitude,
            $this->longitude
        );
    }

    public function getSunrise(DateTimeInterface $dateTime): int
    {
        return (int) date_sunrise(
            $dateTime->getTimestamp(),
            SUNFUNCS_RET_TIMESTAMP,
            $this->latitude,
            $this->longitude
        );
    }
}
