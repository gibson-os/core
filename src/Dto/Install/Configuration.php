<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Install;

class Configuration extends Success
{
    /**
     * @var array<string, string>
     */
    private array $values = [];

    public function setValue(string $key, string $value): Configuration
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
