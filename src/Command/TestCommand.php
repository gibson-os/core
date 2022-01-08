<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Command\Argument;

class TestCommand extends AbstractCommand
{
    #[Argument]
    private ?string $optionalArgument = null;

    #[Argument]
    private int $intArgument;

    protected function run(): int
    {
        echo $this->optionalArgument . ' ' . $this->intArgument . PHP_EOL;

        return 0;
    }

    public function setOptionalArgument(string $optionalArgument): TestCommand
    {
        $this->optionalArgument = $optionalArgument;

        return $this;
    }

    public function setIntArgument(int $intArgument): TestCommand
    {
        $this->intArgument = $intArgument;

        return $this;
    }
}
