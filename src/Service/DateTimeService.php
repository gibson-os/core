<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime as PhpDateTime;
use DateTimeZone;
use Exception;
use GibsonOS\Core\Exception\DateTimeError;

class DateTimeService extends AbstractService
{
    /**
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * DateTime constructor.
     *
     * @param DateTimeZone $timezone
     */
    public function __construct(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @throws DateTimeError
     *
     * @return PhpDateTime
     */
    public function new(): PhpDateTime
    {
        return $this->get('now');
    }

    /**
     * @param string $time
     *
     * @throws DateTimeError
     *
     * @return PhpDateTime
     */
    public function get(string $time): PhpDateTime
    {
        try {
            return new PhpDateTime($time, $this->timezone);
        } catch (Exception $e) {
            throw new DateTimeError(sprintf(
                'Es kann keine Datums Objekkt mit "%s" für die Zeitzone "%s" angelegt werden',
                $time,
                $this->timezone->getName()
            ));
        }
    }

    /**
     * @param DateTimeZone $timezone
     *
     * @return DateTimeService
     */
    public function setTimezone(DateTimeZone $timezone): DateTimeService
    {
        $this->timezone = $timezone;

        return $this;
    }
}
