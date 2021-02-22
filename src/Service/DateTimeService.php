<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

class DateTimeService extends AbstractService
{
    private DateTimeZone $timezone;

    private float $latitude;

    private float $longitude;

    public function __construct(DateTimeZone $timezone, float $latitude, float $longitude)
    {
        $this->timezone = $timezone;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @psalm-suppress InvalidReturnType
     */
    public function get(string $time = 'now', DateTimeZone $timezone = null): DateTime
    {
        try {
            return new DateTime($time, $timezone ?? $this->timezone);
        } catch (Exception $e) {
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
