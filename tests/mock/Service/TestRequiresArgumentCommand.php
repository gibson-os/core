<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Command\AbstractCommand;

class TestRequiresArgumentCommand extends AbstractCommand
{
    #[Argument('Arthur')]
    private string $arthur;

    protected function run(): int
    {
        if ($this->arthur === 'dent') {
            return self::ERROR;
        }

        return self::SUCCESS;
    }

    public function setArthur(string $arthur): TestRequiresArgumentCommand
    {
        $this->arthur = $arthur;

        return $this;
    }
}
