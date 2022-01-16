<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Cronjob;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Cronjob;

/**
 * @method Cronjob getCronjob()
 * @method Time    setCronjob(Cronjob $cronjob)
 */
#[Table]
class Time extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $cronjobId;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromHour = 0;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toHour = 23;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromMinute = 0;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toMinute = 59;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromSecond = 0;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toSecond = 59;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromDayOfMonth = 1;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toDayOfMonth = 31;

    #[Column(type: Column::TYPE_TINYINT, length: 1, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromDayOfWeek = 0;

    #[Column(type: Column::TYPE_TINYINT, length: 1, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toDayOfWeek = 6;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromMonth = 1;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toMonth = 12;

    #[Column(type: Column::TYPE_SMALLINT, length: 4, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $fromYear = 0;

    #[Column(type: Column::TYPE_SMALLINT, length: 4, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $toYear = 9999;

    #[Constraint]
    protected Cronjob $cronjob;

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

    public function getFromHour(): int
    {
        return $this->fromHour;
    }

    public function setFromHour(int $fromHour): Time
    {
        $this->fromHour = $fromHour;

        return $this;
    }

    public function getToHour(): int
    {
        return $this->toHour;
    }

    public function setToHour(int $toHour): Time
    {
        $this->toHour = $toHour;

        return $this;
    }

    public function getFromMinute(): int
    {
        return $this->fromMinute;
    }

    public function setFromMinute(int $fromMinute): Time
    {
        $this->fromMinute = $fromMinute;

        return $this;
    }

    public function getToMinute(): int
    {
        return $this->toMinute;
    }

    public function setToMinute(int $toMinute): Time
    {
        $this->toMinute = $toMinute;

        return $this;
    }

    public function getFromSecond(): int
    {
        return $this->fromSecond;
    }

    public function setFromSecond(int $fromSecond): Time
    {
        $this->fromSecond = $fromSecond;

        return $this;
    }

    public function getToSecond(): int
    {
        return $this->toSecond;
    }

    public function setToSecond(int $toSecond): Time
    {
        $this->toSecond = $toSecond;

        return $this;
    }

    public function getFromDayOfMonth(): int
    {
        return $this->fromDayOfMonth;
    }

    public function setFromDayOfMonth(int $fromDayOfMonth): Time
    {
        $this->fromDayOfMonth = $fromDayOfMonth;

        return $this;
    }

    public function getToDayOfMonth(): int
    {
        return $this->toDayOfMonth;
    }

    public function setToDayOfMonth(int $toDayOfMonth): Time
    {
        $this->toDayOfMonth = $toDayOfMonth;

        return $this;
    }

    public function getFromDayOfWeek(): int
    {
        return $this->fromDayOfWeek;
    }

    public function setFromDayOfWeek(int $fromDayOfWeek): Time
    {
        $this->fromDayOfWeek = $fromDayOfWeek;

        return $this;
    }

    public function getToDayOfWeek(): int
    {
        return $this->toDayOfWeek;
    }

    public function setToDayOfWeek(int $toDayOfWeek): Time
    {
        $this->toDayOfWeek = $toDayOfWeek;

        return $this;
    }

    public function getFromMonth(): int
    {
        return $this->fromMonth;
    }

    public function setFromMonth(int $fromMonth): Time
    {
        $this->fromMonth = $fromMonth;

        return $this;
    }

    public function getToMonth(): int
    {
        return $this->toMonth;
    }

    public function setToMonth(int $toMonth): Time
    {
        $this->toMonth = $toMonth;

        return $this;
    }

    public function getFromYear(): int
    {
        return $this->fromYear;
    }

    public function setFromYear(int $fromYear): Time
    {
        $this->fromYear = $fromYear;

        return $this;
    }

    public function getToYear(): int
    {
        return $this->toYear;
    }

    public function setToYear(int $toYear): Time
    {
        $this->toYear = $toYear;

        return $this;
    }
}
