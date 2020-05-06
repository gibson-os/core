<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Cronjob;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Cronjob;

class Time extends AbstractModel
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int
     */
    private $cronjobId;

    /**
     * @var int|null
     */
    private $fromHour;

    /**
     * @var int|null
     */
    private $toHour;

    /**
     * @var int|null
     */
    private $fromMinute;

    /**
     * @var int|null
     */
    private $toMinute;

    /**
     * @var int|null
     */
    private $fromSecond;

    /**
     * @var int|null
     */
    private $toSecond;

    /**
     * @var int|null
     */
    private $fromDayOfMonth;

    /**
     * @var int|null
     */
    private $toDayOfMonth;

    /**
     * @var int|null
     */
    private $fromDayOfWeek;

    /**
     * @var int|null
     */
    private $toDayOfWeek;

    /**
     * @var int|null
     */
    private $fromMonth;

    /**
     * @var int|null
     */
    private $toMonth;

    /**
     * @var int|null
     */
    private $fromYear;

    /**
     * @var int|null
     */
    private $toYear;

    /**
     * @var Cronjob
     */
    private $cronjob;

    public static function getTableName(): string
    {
        return 'cronjob_time';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Time
    {
        $this->id = $id;

        return $this;
    }

    public function getCronjobId(): int
    {
        return $this->cronjobId;
    }

    public function setCronjobId(int $cronjobId): Time
    {
        $this->cronjobId = $cronjobId;

        return $this;
    }

    public function getFromHour(): ?int
    {
        return $this->fromHour;
    }

    public function setFromHour(?int $fromHour): Time
    {
        $this->fromHour = $fromHour;

        return $this;
    }

    public function getToHour(): ?int
    {
        return $this->toHour;
    }

    public function setToHour(?int $toHour): Time
    {
        $this->toHour = $toHour;

        return $this;
    }

    public function getFromMinute(): ?int
    {
        return $this->fromMinute;
    }

    public function setFromMinute(?int $fromMinute): Time
    {
        $this->fromMinute = $fromMinute;

        return $this;
    }

    public function getToMinute(): ?int
    {
        return $this->toMinute;
    }

    public function setToMinute(?int $toMinute): Time
    {
        $this->toMinute = $toMinute;

        return $this;
    }

    public function getFromSecond(): ?int
    {
        return $this->fromSecond;
    }

    public function setFromSecond(?int $fromSecond): Time
    {
        $this->fromSecond = $fromSecond;

        return $this;
    }

    public function getToSecond(): ?int
    {
        return $this->toSecond;
    }

    public function setToSecond(?int $toSecond): Time
    {
        $this->toSecond = $toSecond;

        return $this;
    }

    public function getFromDayOfMonth(): ?int
    {
        return $this->fromDayOfMonth;
    }

    public function setFromDayOfMonth(?int $fromDayOfMonth): Time
    {
        $this->fromDayOfMonth = $fromDayOfMonth;

        return $this;
    }

    public function getToDayOfMonth(): ?int
    {
        return $this->toDayOfMonth;
    }

    public function setToDayOfMonth(?int $toDayOfMonth): Time
    {
        $this->toDayOfMonth = $toDayOfMonth;

        return $this;
    }

    public function getFromDayOfWeek(): ?int
    {
        return $this->fromDayOfWeek;
    }

    public function setFromDayOfWeek(?int $fromDayOfWeek): Time
    {
        $this->fromDayOfWeek = $fromDayOfWeek;

        return $this;
    }

    public function getToDayOfWeek(): ?int
    {
        return $this->toDayOfWeek;
    }

    public function setToDayOfWeek(?int $toDayOfWeek): Time
    {
        $this->toDayOfWeek = $toDayOfWeek;

        return $this;
    }

    public function getFromMonth(): ?int
    {
        return $this->fromMonth;
    }

    public function setFromMonth(?int $fromMonth): Time
    {
        $this->fromMonth = $fromMonth;

        return $this;
    }

    public function getToMonth(): ?int
    {
        return $this->toMonth;
    }

    public function setToMonth(?int $toMonth): Time
    {
        $this->toMonth = $toMonth;

        return $this;
    }

    public function getFromYear(): ?int
    {
        return $this->fromYear;
    }

    public function setFromYear(?int $fromYear): Time
    {
        $this->fromYear = $fromYear;

        return $this;
    }

    public function getToYear(): ?int
    {
        return $this->toYear;
    }

    public function setToYear(?int $toYear): Time
    {
        $this->toYear = $toYear;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getCronjob(): Cronjob
    {
        $this->loadForeignRecord($this->cronjob, $this->getCronjobId());

        return $this->cronjob;
    }

    public function setCronjob(Cronjob $cronjob): Time
    {
        $this->cronjob = $cronjob;
        $this->setCronjobId((int) $cronjob->getId());

        return $this;
    }
}
