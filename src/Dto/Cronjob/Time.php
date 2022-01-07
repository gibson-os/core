<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Cronjob;

class Time
{
    public function __construct(
        private TimePart $hour,
        private TimePart $minute,
        private TimePart $second,
        private TimePart $dayOfMonth,
        private TimePart $dayOfWeek,
        private TimePart $month,
        private TimePart $year,
    ) {
    }

    public function getHour(): TimePart
    {
        return $this->hour;
    }

    public function getMinute(): TimePart
    {
        return $this->minute;
    }

    public function getSecond(): TimePart
    {
        return $this->second;
    }

    public function getDayOfMonth(): TimePart
    {
        return $this->dayOfMonth;
    }

    public function getDayOfWeek(): TimePart
    {
        return $this->dayOfWeek;
    }

    public function getMonth(): TimePart
    {
        return $this->month;
    }

    public function getYear(): TimePart
    {
        return $this->year;
    }
}
