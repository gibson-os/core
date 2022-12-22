<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Command\AbstractCommand;

class TestCommand extends AbstractCommand
{
    #[Argument('Arthur')]
    private ?string $arthur = null;

    #[Option('Marvin')]
    private bool $marvin = false;

    protected function run(): int
    {
        if ($this->arthur === 'dent') {
            return self::ERROR;
        }

        if ($this->marvin) {
            return 42;
        }

        return self::SUCCESS;
    }

    public function setArthur(?string $arthur): TestCommand
    {
        $this->arthur = $arthur;

        return $this;
    }

    public function setMarvin(bool $marvin): TestCommand
    {
        $this->marvin = $marvin;

        return $this;
    }
}
