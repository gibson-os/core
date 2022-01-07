<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install;

use Attribute;
use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Service\Attribute\Install\CronjobInstallAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Cronjob implements AttributeInterface
{
    public function __construct(
        private string $hours = '*',
        private string $minutes = '*',
        private string $seconds = '*',
        private string $daysOfMonth = '*',
        private string $daysOfWeek = '*',
        private string $months = '*',
        private string $years = '*',
        private ?array $arguments = null,
        private ?array $options = null,
        private ?string $user = null,
    ) {
    }

    public function getAttributeServiceName(): string
    {
        return CronjobInstallAttribute::class;
    }

    public function getHours(): string
    {
        return $this->hours;
    }

    public function getMinutes(): string
    {
        return $this->minutes;
    }

    public function getSeconds(): string
    {
        return $this->seconds;
    }

    public function getDaysOfMonth(): string
    {
        return $this->daysOfMonth;
    }

    public function getDaysOfWeek(): string
    {
        return $this->daysOfWeek;
    }

    public function getMonths(): string
    {
        return $this->months;
    }

    public function getYears(): string
    {
        return $this->years;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }
}
