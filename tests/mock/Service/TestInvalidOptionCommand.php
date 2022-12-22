<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Command\AbstractCommand;

class TestInvalidOptionCommand extends AbstractCommand
{
    #[Option('Marvin')]
    private int $marvin = 42;

    protected function run(): int
    {
        return self::SUCCESS;
    }

    public function setMarvin(int $marvin): TestInvalidOptionCommand
    {
        $this->marvin = $marvin;

        return $this;
    }
}
